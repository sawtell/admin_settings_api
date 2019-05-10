<?php

namespace Drupal\example\Plugin\AdminSettingsAPI;


use Drupal\Core\Annotation\Translation;
use Drupal\admin_settings_api\Annotation\AdminSettingsAPI;
use Drupal\admin_settings_api\Plugin\AdminSettingsAPIBase;

/**
 * Adds component to admin form
 *
 * @AdminSettingsAPI(
 *   id = "another_example_admin_plugin",
 *   label = @Translation("Another example plugin"),
 * )
 */
class AnotherExample extends AdminSettingsAPIBase {

  /**
   * The form group label
   */
  public function label() {
    return $this->t('Another example');
  }

  /**
   * Build the form component.
   */
  public function build() {
    $formComponent = [];

    $formComponent['setting_one'] = [
      '#type' => 'textarea',
      '#title' => t('Ts&Cs'),
      '#storage_type' => 'config',
      '#default_value' => $this->getDefaultValue('setting_one'),
    ];

    $formComponent['setting_two'] = [
      '#type' => 'managed_file',
      '#title' => t('Logo'),
      '#storage_type' => 'state',
      '#default_value' => $this->getDefaultValue('setting_two'),
    ];

    return $formComponent;
  }

  public function checkAccess() {
    return \Drupal::currentUser()->hasPermission('custom permission to wreak havoc');
  }
}
