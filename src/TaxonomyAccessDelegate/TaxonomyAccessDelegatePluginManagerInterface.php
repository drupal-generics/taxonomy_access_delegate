<?php

namespace Drupal\taxonomy_access_delegate\TaxonomyAccessDelegate;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Component\Plugin\Discovery\CachedDiscoveryInterface;

/**
 * Interface TaxonomyAccessDelegatePluginManagerInterface.
 *
 * @package Drupal\taxonomy_access_delegate\TaxonomyAccessDelegate
 */
interface TaxonomyAccessDelegatePluginManagerInterface extends PluginManagerInterface, CachedDiscoveryInterface {

  /**
   * Gets an array of delegates.
   *
   * @param string $bundle
   *   The name of the bundle.
   * @param string $operation
   *   The name of the operation on which access check is performed.
   *
   * @return array
   *   An array of delegates.
   */
  public function getDelegates(string $bundle, string $operation);

}
