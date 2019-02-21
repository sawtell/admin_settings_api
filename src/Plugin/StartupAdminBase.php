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

  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->service_helper = \Drupal::service('startup_admin.service');
  }

  protected function getDefaultValue($key) {
    if ($value = $this->service_helper->getSetting($key)) {
      return $value;
    }
    return '';
  }
}
