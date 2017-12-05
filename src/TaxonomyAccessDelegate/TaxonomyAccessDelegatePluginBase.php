<?php

namespace Drupal\taxonomy_access_delegate\TaxonomyAccessDelegate;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\taxonomy\TermInterface;

/**
 * Class TaxonomyAccessDelegatePluginBase.
 *
 * Provides common functionality for TaxonomyAccessDelegate plugins.
 *
 * @package Drupal\dcc_multistep
 */
abstract class TaxonomyAccessDelegatePluginBase extends PluginBase implements TaxonomyAccessDelegatePluginInspectionInterface {
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function getBypassRoles() {
    if (isset($this->pluginDefinition['bypassRoles'])) {
      return $this->pluginDefinition['bypassRoles'];
    }
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function access(TermInterface $node, $operation, AccountInterface $account, $isTranslation) {
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  public function appliesForAccount(AccountInterface $account) {
    if (!($bypassRoles = $this->getBypassRoles())) {
      return TRUE;
    }

    return !array_intersect($bypassRoles, $account->getRoles());
  }

}
