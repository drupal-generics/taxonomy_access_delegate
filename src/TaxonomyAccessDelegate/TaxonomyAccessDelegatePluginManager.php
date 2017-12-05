<?php

namespace Drupal\taxonomy_access_delegate\TaxonomyAccessDelegate;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * TaxonomyAccessDelegate plugin manager.
 */
class TaxonomyAccessDelegatePluginManager extends DefaultPluginManager implements TaxonomyAccessDelegatePluginManagerInterface {

  /**
   * Store for instantiated access delegates.
   *
   * @var array
   */
  protected $delegates = [];

  /**
   * Store per bundle+operation of delegates.
   *
   * @var array
   */
  protected $bundleOperationDelegates = [];

  /**
   * Constructs an TaxonomyAccessDelegatePluginManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(
    \Traversable $namespaces,
    CacheBackendInterface $cache_backend,
    ModuleHandlerInterface $module_handler) {
    parent::__construct(
      'Plugin/Access/TaxonomyAccessDelegate',
      $namespaces,
      $module_handler,
      'Drupal\taxonomy_access_delegate\TaxonomyAccessDelegate\TaxonomyAccessDelegatePluginInspectionInterface',
      'Drupal\taxonomy_access_delegate\Annotation\TaxonomyAccessDelegate');
    $this->alterInfo('taxonomy_access_delegate_info');
    $this->setCacheBackend($cache_backend, 'taxonomy_access_delegate');
  }

  /**
   * Get access alters for the provided bundle.
   *
   * @param string $bundle
   *   The content type of the node.
   * @param string $operation
   *   The operation to delegate for.
   *
   * @return \Drupal\node_access_delegate\NodeAccessDelegatePluginInterface[]
   *   Form alters for the given bundle.
   */
  public function getDelegates(string $bundle, string $operation) {
    if (
      array_key_exists($bundle, $this->bundleOperationDelegates) &&
      array_key_exists($operation, $this->bundleOperationDelegates[$bundle])
    ) {
      return $this->bundleOperationDelegates[$bundle][$operation];
    }

    $delegates = [];
    // Get the alter definitions for the given bundle.
    foreach ($this->getDefinitions() as $id => $definition) {
      if ($definition['bundle'] == $bundle && (!$definition['operations'] || in_array($operation, $definition['operations']))) {
        $delegates[$id] = $definition;
      }
    }

    // Sort the definitions after priority.
    uasort($delegates, function ($a, $b) {
      return $a['priority'] <=> $b['priority'];
    });

    // Create the alter plugins.
    foreach ($delegates as $id => &$alter) {
      // Prevent multiple instances of same delegate.
      if (array_key_exists($id, $this->delegates)) {
        $alter = $this->delegates[$id];
      }
      else {
        $this->delegates[$id] = $alter = $this->createInstance($id);
      }
    }

    // Store the discovered delegates for the bundle per operation so we don't
    // have to calculate all this stuff every time, especially because the
    // access handlers are called many time.
    $this->bundleOperationDelegates[$bundle][$operation] = $delegates;
    return $delegates;
  }

}
