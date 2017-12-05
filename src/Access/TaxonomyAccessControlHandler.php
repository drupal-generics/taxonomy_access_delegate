<?php

namespace Drupal\taxonomy_access_delegate\Access;

use Drupal\Core\Entity\EntityHandlerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\taxonomy_access_delegate\TaxonomyAccessDelegate\TaxonomyAccessDelegatePluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\taxonomy\TermAccessControlHandler as OriginalTaxonomyAccessControlHandler;

/**
 * Replaces to core taxonomy access handler to delegate it to plugins.
 *
 * @package Drupal\node_access_delegate
 */
class TaxonomyAccessControlHandler extends OriginalTaxonomyAccessControlHandler implements EntityHandlerInterface {

  /**
   * The access delegates plugin manager.
   *
   * @var \Drupal\node_access_delegate\NodeAccessDelegateManager
   */
  protected $taxonomyAccessDelegatePluginManager;

  /**
   * TaxonomyAccessControlHandler constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type.
   * @param \Drupal\taxonomy_access_delegate\TaxonomyAccessDelegate\TaxonomyAccessDelegatePluginManager $taxonomyAccessDelegatePluginManager
   *   The access delegates plugin manager.
   */
  public function __construct(
    EntityTypeInterface $entity_type,
    TaxonomyAccessDelegatePluginManager $taxonomyAccessDelegatePluginManager
  ) {
    parent::__construct($entity_type);
    $this->taxonomyAccessDelegatePluginManager = $taxonomyAccessDelegatePluginManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('plugin.manager.taxonomy_access_delegate.taxonomy_access_delegate')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function access(EntityInterface $entity, $operation, AccountInterface $account = NULL, $return_as_object = FALSE) {
    /** @var \Drupal\node\NodeInterface $entity */
    $account = $this->prepareUser($account);

    // Delegate access operation and return as soon as decisive result got.
    foreach ($this->getDelegates($account, $entity->bundle(), $operation) as $accessDelegate) {
      $access = $accessDelegate->access($entity, $operation, $account, FALSE)
        ->cachePerPermissions();

      if (!$access->isNeutral()) {
        return $return_as_object ? $access : $access->isAllowed();
      }
    }

    return parent::access($entity, $operation, $account, $return_as_object);
  }

  /**
   * Get the applying delegates per bundle, operation and account.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user account.
   * @param string $bundle
   *   The node bundle.
   * @param string $operation
   *   The operation.
   *
   * @return \Drupal\node_access_delegate\NodeAccessDelegatePluginInterface[]
   *   The access delegates
   */
  protected function getDelegates(AccountInterface $account, $bundle, $operation) {
    $delegates = $this->taxonomyAccessDelegatePluginManager->getDelegates($bundle, $operation);

    // Filter out delegates that decide to not apply.
    foreach ($delegates as $id => $accessDelegate) {
      if (!$accessDelegate->appliesForAccount($account)) {
        unset($delegates[$id]);
      }
    }

    return $delegates;
  }

}
