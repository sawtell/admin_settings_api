<?php

namespace Drupal\startup_admin\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Startup admin plugin item annotation object.
 *
 * @see \Drupal\startup_admin\Plugin\StartupAdminManager
 * @see plugin_api
 *
 * @Annotation
 */
class StartupAdmin extends Plugin {


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
