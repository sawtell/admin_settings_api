<?php

namespace Drupal\admin_settings_api;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\State\StateInterface;

/**
 * Class AdminSettingsAPIService
 *
 * @package Drupal\admin_settings_api
 */
class AdminSettingsAPIService extends ServiceProviderBase {
  const CONFIG_PREFIX = 'admin_settings_api';
  const CONFIG_GROUP = 'settings';

  /** @var \Drupal\Core\Config\ConfigFactory */
  protected $config_factory;

  /** @var \Drupal\Core\State\StateInterface  */
  protected $state;

  /** @var \Drupal\Core\Language\LanguageManagerInterface */
  protected $language_manager;

  /**
   * AdminSettingsAPIService constructor.
   *
   * Used to setup class variables.
   *
   * @param \Drupal\Core\Config\ConfigFactory $configFactory
   * @param \Drupal\Core\State\StateInterface $state
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   */
  public function __construct(ConfigFactory $configFactory, StateInterface $state, LanguageManagerInterface $languageManager) {
    $this->config_factory = $configFactory;
    $this->state = $state;
    $this->language_manager = $languageManager;
  }

  /**
   * Get a single setting field that were saved by custom states.
   * Although a bit unwieldy, use a static function for ease of use elsewhere.
   *
   * @param $name
   * @param string $default
   * @param string $language
   *
   * @return mixed|string
   */
  public function getSetting($name, $default = '', $language = '') {
    // @todo: use static variable.

    // First try in state
    $setting = $this->getSettingFromState($name, $language);
    // Then try config
    if (!$setting) {
      $setting = $this->getSettingFromConfig($name, $language);
    }
    // Default to default.
    if (!$setting) {
      $setting = $default;
    }

    return $setting;
  }

  /**
   * @param $name
   * @param string $language
   * @return mixed
   */
  public function getSettingFromState($name, $language = '') {
    $current_language = $this->language_manager->getCurrentLanguage()->getId();
    $language = ($language) ? $language : $current_language;

    // First try in state
    $state_key = $this->buildStateKey($language, $name);
    $setting = $this->state->get($state_key);

    // Default language
    if (!$setting) {
      $state_key = $this->buildStateKey('', $name);
      $setting = $setting = $this->state->get($state_key);
    }

    return $setting;
  }

  /**
   * @param $name
   * @param string $language
   * @return array|mixed|null
   */
  public function getSettingFromConfig($name, $language = '') {
    $current_language = $this->language_manager->getCurrentLanguage()->getId();
    $language = ($language) ? $language : $current_language;

    $config_key = $this->buildConfigKey($language);
    $setting = $this->config_factory->get($config_key)->get($name);

    // Config (default language).
    if (!$setting) {
      $config_key = $this->buildConfigKey('');
      $setting = $this->config_factory->get($config_key)->get($name);
    }

    return $setting;
  }

  /**
   * @param $language
   * @return string
   */
  public function buildConfigKey($language) {
    $config_key = self::CONFIG_PREFIX;
    $config_key .= ($language) ? '.' . $language : '.' . $this->language_manager->getDefaultLanguage()->getId();
    return $config_key;
  }

  /**
   * @param $language
   * @param $name
   * @return string
   */
  public function buildStateKey($language, $name) {
    $state_key = [
      self::CONFIG_PREFIX,
      ($language) ? $language : $this->language_manager->getDefaultLanguage()->getId(),
      $name,
    ];

    return implode('.', $state_key);
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
