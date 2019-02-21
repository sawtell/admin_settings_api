<?php

namespace Drupal\startup_admin\Plugin;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Provides the Startup admin plugin plugin manager.
 */
class StartupAdminManager extends DefaultPluginManager {


  /**
   * Constructs a new StartupAdminPluginManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/StartupAdmin', $namespaces, $module_handler, 'Drupal\startup_admin\Plugin\StartupAdminInterface', 'Drupal\startup_admin\Annotation\StartupAdmin');

    $this->alterInfo('startup_admin_startup_admin_plugin_info');
    $this->setCacheBackend($cache_backend, 'startup_admin_startup_admin_plugin_plugins');
  }
}
