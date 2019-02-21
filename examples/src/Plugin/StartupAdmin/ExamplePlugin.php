<?php

namespace Drupal\example_module\Plugin\StartupAdmin;


use Drupal\Core\Annotation\Translation;
use Drupal\startup_admin\Annotation\StartupAdmin;
use Drupal\startup_admin\Plugin\StartupAdminBase;

/**
 * Adds component to admin form
 *
 * @StartupAdmin(
 *   id = "example_admin_plugin",
 *   label = @Translation("Example plugin"),
 * )
 */
class ExamplePlugin extends StartupAdminBase {

  /**
   * The form group label
   */
  public function label() {
    return $this->t('Example module');
  }

  /**
   * Build the form component.
   */
  public function build() {
    $formComponent = [];

    $formComponent['help'] = [
      '#type' => 'markup',
      '#markup' => $this->t('This is some help text'),
    ];

    $formComponent['some_setting'] = [
      '#type' => 'textfield',
      '#title' => t('Some setting'),
      '#storage_type' => 'config',
      '#default_value' => $this->getDefaultValue('some_setting'),
    ];

    $formComponent['another_setting'] = [
      '#type' => 'checkboxes',
      '#title' => t('Another setting'),
      '#options' => [
        'option1' => 'Option 1',
        'option2' => 'Option 2',
        'option3' => 'Option 3',
      ],
      '#storage_type' => 'state',
      '#default_value' => $this->getDefaultValue('another_setting'),
    ];

    return $formComponent;
  }
}
