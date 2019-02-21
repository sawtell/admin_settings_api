<?php

namespace Drupal\startup_example\Plugin\StartupAdmin;


use Drupal\Core\Annotation\Translation;
use Drupal\startup_admin\Annotation\StartupAdmin;
use Drupal\startup_admin\Plugin\StartupAdminBase;

/**
 * Adds component to admin form
 *
 * @StartupAdmin(
 *   id = "another_example_admin_plugin",
 *   label = @Translation("Example plugin"),
 * )
 */
class AnotherExample extends StartupAdminBase {

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
}
