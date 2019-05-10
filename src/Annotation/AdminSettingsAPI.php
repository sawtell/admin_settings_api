<?php

namespace Drupal\admin_settings_api\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a admin settings API plugin item annotation object.
 *
 * @see \Drupal\admin_settings_api\Plugin\AdminSettingsAPIManager
 * @see plugin_api
 *
 * @Annotation
 */
class AdminSettingsAPI extends Plugin {


  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The label of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

}
