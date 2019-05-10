<?php

namespace Drupal\admin_settings_api\Form;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\Language;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\State\StateInterface;
use Drupal\Core\Url;
use Drupal\admin_settings_api\Plugin\AdminSettingsAPIBase;
use Drupal\admin_settings_api\AdminSettingsAPIService as AdminService;
use Drupal\admin_settings_api\AdminSettingsAPIService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * {@inheritDoc}
 */
class AdminSettingsAPIForm extends FormBase implements ContainerInjectionInterface {

  protected $admin_service;
  protected $language_manager;
  protected $state;

  /**
   * AdminSettingsAPIForm constructor.
   *
   * @param \Drupal\admin_settings_api\AdminSettingsAPIService $adminService
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   * @param \Drupal\Core\State\StateInterface $state
   */
  public function __construct(
    AdminSettingsAPIService $adminService,
    LanguageManagerInterface $languageManager,
    StateInterface $state)
  {
    $this->admin_service = $adminService;
    $this->language_manager = $languageManager;
    $this->state = $state;
  }

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *
   * @return \Drupal\Core\Form\FormBase|\Drupal\admin_settings_api\Form\AdminSettingsAPIForm
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('admin_settings_api.service'),
      $container->get('language_manager'),
      $container->get('state')
    );
  }

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'admin_settings_api_settings_form';
  }

  /**
   * Form constructor.
   *
   * Provides the base form with language options and allows other modules to
   * add new elements.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structure.
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $system_config = $this->config('system.site');

    $form_intro = '<h2>' . $system_config->get('name') . ' settings</h2>';
    $form_intro .= <<<EOT
<p>Here you can configure site specific settings<br>
<small>If you are a developer and want to add to this settings form please see: <code>admin_settings_api/examples</code></small></p>
EOT;

    $form['intro'] = [
      '#type' => '#markup',
      '#markup' => $form_intro,
      '#weight' => -99,
    ];

    // Create links for the different language settings forms.
    $this->buildLanguageLinks($form);

    $form['admin_settings_api'] = [
      '#type' => 'vertical_tabs',
    ];

    $form['#attached']['library'][] = 'admin_settings_api/form';

    // Pull any configuration settings from other modules via Plugin definitions.

    /** @var \Drupal\admin_settings_api\Plugin\AdminSettingsAPIManager $admin_plugin_manager */
    $admin_plugin_manager = \Drupal::service('plugin.manager.admin_settings_api');
    $plugin_definitions = $admin_plugin_manager->getDefinitions();
    foreach ($plugin_definitions as $plugin_definition) {
      /** @var \Drupal\admin_settings_api\Plugin\AdminSettingsAPIInterface $plugin */
      $plugin = $admin_plugin_manager->createInstance($plugin_definition['id']);

      if (!$plugin->checkAccess()) {
        continue;
      }

      $group_machine_name = AdminService::transform($plugin->label());
      if (!isset($form[$group_machine_name])) {
        $form[$group_machine_name] = [
          '#type' => 'details',
          '#title' => $plugin->label(),
          '#group' => 'admin_settings_api',
          '#tree' => TRUE, // Make sure values are nested.
        ];
      }
      $form[$group_machine_name] += $plugin->build();
    }

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save settings'),
    ];

    return $form;
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    // @todo: Validate the storage type on each field.
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Don't process default form elements.
    $defaults = array_flip(['submit', 'form_build_id', 'form_token', 'form_id', 'op']);
    $values = array_diff_key($form_state->getValues(), $defaults);

    $language = $values['setting_language'];
    unset($values['setting_language']);

    foreach ($values as $key => $sub_values) {
      // Don't bother with anything that wasn't in the original form.
      if (!isset($form[$key])) {
        continue;
      }
      // Only deal with children of the group
      if (!array($sub_values)) {
        continue;
      }

      foreach ($sub_values as $name => $value) {
        if (empty($form[$key][$name]['#storage_type'])) {
          continue;
        }

        $storage_type = $form[$key][$name]['#storage_type'];
        // What if the storage type has been changed?
        $this->thereCanBeOnlyOne($storage_type, $name, $language);

        if ($storage_type == AdminSettingsAPIBase::CONFIG) {
          $config_key = $this->admin_service->buildConfigKey($language);

          $admin_settings_api_config = $this->configFactory->getEditable($config_key);
          $admin_settings_api_config->set($name, $value);
          $admin_settings_api_config->save();
        }

        if ($storage_type == AdminSettingsAPIBase::STATE) {
          $state_key = $this->admin_service->buildStateKey($language, $name);

          $this->state->set($state_key, $value);
        }
      }
    }
  }

  /**
   * We should check for the value in the "opposite" storage and delete.
   *
   * @param $storage_type
   * @param $name
   * @param $language
   */
  protected function thereCanBeOnlyOne($storage_type, $name, $language) {
    // If now saving as config.
    if ($storage_type == AdminSettingsAPIBase::CONFIG) {
      // Check for value stored as state.
      if ($value = $this->admin_service->getSettingFromState($name, $language)) {
        $state_key = $this->admin_service->buildStateKey($language, $name);
        $this->state->delete($state_key);
      }
    }
    // If now saving as state.
    if ($storage_type == AdminSettingsAPIBase::STATE) {
      // Check for value stored as config.
      if ($value = $this->admin_service->getSettingFromConfig($name, $language)) {
        $config_key = $this->admin_service->buildConfigKey($language);
        $this->configFactory->getEditable($config_key)->clear($name)->save();
      }
    }
  }

  /**
   * Helper to build out links to allow the user to switch languages.
   *
   * @param $form
   */
  protected function buildLanguageLinks(&$form) {
    /** @var Language[] $languages */
    $languages = $this->language_manager->getLanguages();
    /** @var Language $current_language */
    $current_language = $this->language_manager->getCurrentLanguage();

    $form['setting_language'] = [
      '#type' => 'hidden',
      '#value' => $current_language->getId(),
    ];


    // If there is more than one language then provide the ability to "switch"
    // settings forms for each language.
    if (count($languages) > 1) {
      $language_links = '<span class="language-switch-title">Language specific settings: </span><ul class="language-switch">';
      foreach ($languages as $language) {
        if ($language->getId() == $current_language->getId()) {
          $language_links .= '<li class="active">' . $language->getName() . '</li>';
        }
        else {
          // Print a link to direct the user to the specific language settings.
          $url = Url::fromRoute('admin_settings_api.settings_form', [], ['language' => $language]);
          $language_links .= '<li>' . Link::fromTextAndUrl($language->getName(), $url)->toString() . '</li>';
        }
      }
      $language_links .= '</ul>';

      $form['language_switcher'] = [
        '#type' => '#markup',
        '#markup' => $language_links,
      ];
    }
  }
}
