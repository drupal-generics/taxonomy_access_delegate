<?php

namespace Drupal\taxonomy_access_delegate\TaxonomyAccessDelegate;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\taxonomy\TermInterface;

/**
 * Defines an interface for TaxonomyAccessDelegate plugin type.
 */
interface TaxonomyAccessDelegatePluginInspectionInterface extends PluginInspectionInterface {

  /**
   * Checks access to an operation on a given node or node translation.
   *
   * @param \Drupal\taxonomy\TermInterface $term
   *   The term entity for which to check access.
   * @param string $operation
   *   The operation access should be checked for.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   (optional) The user session for which to check access, or NULL to check
   *   access for the current user. Defaults to NULL.
   * @param bool $isTranslation
   *   Whether the operation is for translation.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   The access result.
   *
   * @see \Drupal\Core\Entity\EntityAccessControlHandlerInterface
   */
  public function access(TermInterface $term, $operation, AccountInterface $account, $isTranslation);

  /**
   * Determines whether this handler should apply.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user account.
   *
   * @return bool
   *   Apply or not.
   */
  public function appliesForAccount(AccountInterface $account);

  /**
   * Returns an array of roles for which to bypass the access rules.
   *
   * @return mixed
   *   The roles.
   */
  public function getBypassRoles();

}
