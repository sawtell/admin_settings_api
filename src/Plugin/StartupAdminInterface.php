<?php

namespace Drupal\startup_admin\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines an interface for Startup admin plugin plugins.
 */
interface StartupAdminInterface extends PluginInspectionInterface {

  /**
   * @return string
   */
  public function label();

  /**
   * @return array
   */
  public function build();
}
