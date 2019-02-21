<?php

namespace Drupal\startup_admin\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\Language;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\startup_admin\Event\FormBuildEvent;
use Drupal\startup_admin\Plugin\StartupAdminBase;
use Drupal\startup_admin\StartupAdminSettingsService as AdminService;

/**
 * {@inheritDoc}
 */
class StartupAdminSettingsForm extends FormBase {

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'startup_admin_settings_form';
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
    $language_manager = \Drupal::getContainer()->get('language_manager');
    /** @var Language[] $languages */
    $languages = $language_manager->getLanguages();
    $current_language = $language_manager->getCurrentLanguage();
    // Get the language for the form, or the current language.
    $setting_language = (isset($_GET['lang'])) ? $_GET['lang'] : $current_language->getId();

    // Validate the language ID is legit. If not set to the current language.
    if ($setting_language && !isset($languages[$setting_language])) {
      $setting_language = $current_language->getId();
    }

    $system_config = $this->config('system.site');

    $form_intro = '<h2>' . $system_config->get('name') . ' settings</h2>';
    $form_intro .= <<<EOT
<p>Here you can configure site specific settings<br>
<small>If you are a developer and want to add to this settings form please see: <code>startup_admin/examples</code></small></p>
EOT;

    $form['intro'] = [
      '#type' => '#markup',
      '#markup' => $form_intro,
      '#weight' => -99,
    ];

    $form['setting_language'] = [
      '#type' => 'hidden',
      '#value' => $setting_language,
    ];

    // If there is more than one language then provide the ability to "switch"
    // settings forms for each language.
    if (count($languages) > 1) {
      $language_links = '<span class="language-switch-title">Language specific settings: </span><ul class="language-switch">';
      foreach ($languages as $language) {
        if ($language->getId() == $setting_language) {
          $language_links .= '<li class="active">' . $language->getName() . '</li>';
        }
        else {
          // Print a link to direct the user to the specific language settings.
          $url = Url::fromRoute('startup_admin.settings_form', ['lang' => $language->getId()]);
          $language_links .= '<li>' . Link::fromTextAndUrl($language->getName(), $url)->toString() . '</li>';
        }
      }
      $language_links .= '</ul>';
      $form['language_switcher'] = [
        '#type' => '#markup',
        '#markup' => $language_links,
      ];
    }

    $form['startup_admin'] = [
      '#type' => 'vertical_tabs',
    ];

    $form['#attached']['library'][] = 'startup_admin/form';

    /** @var \Drupal\startup_admin\Plugin\StartupAdminManager $admin_plugin_manager */
    $admin_plugin_manager = \Drupal::service('plugin.manager.startup_admin');
    $plugin_definitions = $admin_plugin_manager->getDefinitions();
    foreach ($plugin_definitions as $plugin_definition) {
      /** @var \Drupal\startup_admin\Plugin\StartupAdminInterface $plugin */
      $plugin = $admin_plugin_manager->createInstance($plugin_definition['id']);

      $helper_services = \Drupal::service('startup_admin.service');
      $group_machine_name = $helper_services::transform($plugin->label());
      if (!isset($form[$group_machine_name])) {
        $form[$group_machine_name] = [
          '#type' => 'details',
          '#title' => $plugin->label(),
          '#group' => 'startup_admin',
          '#tree' => TRUE,
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
    $defaults = array_flip(['submit', 'form_build_id', 'form_token', 'form_id', 'op']);
    $values = array_diff_key($form_state->getValues(), $defaults);

    $language = $values['setting_language'];
    unset($values['setting_language']);

    foreach ($values as $key => $sub_values) {
      if (!isset($form[$key])) {
        continue;
      }

      if (!array($sub_values)) {
        continue;
      }

      foreach ($sub_values as $name => $value) {
        if (empty($form[$key][$name]['#storage_type'])) {
          continue;
        }

        $storage_type = $form[$key][$name]['#storage_type'];

        if ($storage_type == StartupAdminBase::CONFIG) {
          $setting_key = AdminService::CONFIG_PREFIX . '.' . AdminService::CONFIG_GROUP;
          $startup_admin_config = $this->configFactory->getEditable($setting_key);
          $startup_admin_config->set($name, $value);
          $startup_admin_config->save();
        }

        if ($storage_type == StartupAdminBase::STATE) {
          $setting_key = AdminService::CONFIG_PREFIX . '.';
          $setting_key .= ($language) ? $language . '.' . $name : $name;
          \Drupal::state()->set($setting_key, $value);
        }
      }
    }
  }
}
