<?php

namespace Drupal\startup_admin\Plugin;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\startup_admin\StartupAdminSettingsService;

/**
 * Base class for Startup admin plugin plugins.
 */
abstract class StartupAdminBase extends PluginBase implements StartupAdminInterface {
  use StringTranslationTrait;

  const CONFIG = 'config';
  const STATE = 'state';

  /** @var StartupAdminSettingsService */
  protected $service_helper;

  /**
   * StartupAdminBase constructor.
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->service_helper = \Drupal::service('startup_admin.service');
  }

  /**
   * @param $key
   * @return mixed|string
   */
  protected function getDefaultValue($key) {
    if ($value = $this->service_helper->getSetting($key)) {
      return $value;
    }
    return '';
  }

  /**
   * @return bool
   */
  public function checkAccess() {
    return \Drupal::currentUser()->hasPermission('startup admin administer settings');
  }
}
