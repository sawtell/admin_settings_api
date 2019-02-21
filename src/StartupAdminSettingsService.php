<?php

namespace Drupal\startup_admin;

use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Drupal\Core\Config\ConfigFactory;

/**
 * Class StartupAdminSettingsService
 *
 * @package Drupal\startup_admin
 */
class StartupAdminSettingsService extends ServiceProviderBase {
  const CONFIG_PREFIX = 'startup_admin';
  const CONFIG_GROUP = 'settings';

  /**
   * @var \Drupal\Core\Config\ConfigFactory
   */
  private $config;

  /**
   * @var \Drupal\Core\Config\Config|\Drupal\Core\Config\ImmutableConfig
   */
  private $systemSite;

  /**
   * StartupAdminSettingsService constructor.
   *
   * Used to setup class variables.
   *
   * @param \Drupal\Core\Config\ConfigFactory $config
   */
  public function __construct(ConfigFactory $config) {
    $this->config = $config;
    $this->systemSite = $config->get('system.site');
  }

  /**
   * Get a single setting field that were saved by custom states.
   *
   * @param $name
   * @param string $default
   * @param string $language
   *
   * @return mixed|string
   */
  public function getSetting($name, $default = '', $language = '') {
    //@todo: use static variable.

    // First try in state
    // @todo: what if the storage type changes?
    $setting = $this->getStateSetting($name, $language);
    if (!$setting) {
      $setting = $this->getConfigSetting($name, $language);
    }
    if (!$setting) {
      $setting = $default;
    }

    return $setting;
  }

  private function getConfigSetting($name, $language) {
    return $this->config->get(self::CONFIG_PREFIX . '.' . self::CONFIG_GROUP)->get($name);
  }

  /**
   * Get settings saved in state.
   *
   * @param $name
   * @param $language
   * @return bool|mixed
   */
  private function getStateSetting($name, $language) {
    $setting_key = self::CONFIG_PREFIX . '.';

    // If language argument is not set, use the current language.
    if (!$language) {
      $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
    }

    $setting_key .= $language . '.' . $name;
    if ($setting = \Drupal::state()->get($setting_key)) {
      return $setting;
    }
    return FALSE;
  }

  /**
   * Returns the site name.
   *
   * @return array|mixed|null
   */
  public function getSiteName() {
    return $this->systemSite->get('name');
  }

  /**
   * Convert human readable string to machine name.
   *
   * @param $string
   * @return mixed
   */
  public static function transform($string) {
    $new_value = strtolower($string);
    $new_value = preg_replace('/[^a-z0-9_]+/', '_', $new_value);
    return preg_replace('/_+/', '_', $new_value);
  }
}
