<?php

namespace Drupal\admin_settings_api\Plugin;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\admin_settings_api\AdminSettingsAPIService;

/**
 * Base class for admin settings API plugin plugins.
 */
abstract class AdminSettingsAPIBase extends PluginBase implements AdminSettingsAPIInterface {
  use StringTranslationTrait;

  const CONFIG = 'config';
  const STATE = 'state';

  /** @var AdminSettingsAPIService */
  protected $service_helper;

  /**
   * AdminSettingsAPIBase constructor.
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->service_helper = \Drupal::service('admin_settings_api.service');
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
    return \Drupal::currentUser()->hasPermission('admin_settings_api administer settings');
  }
}
