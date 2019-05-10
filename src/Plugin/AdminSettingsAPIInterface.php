<?php

namespace Drupal\admin_settings_api\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines an interface for admin settings API plugin plugins.
 */
interface AdminSettingsAPIInterface extends PluginInspectionInterface {

  /**
   * @return string
   */
  public function label();

  /**
   * @return array
   */
  public function build();

  /**
   * @return boolean
   */
  public function checkAccess();
}
