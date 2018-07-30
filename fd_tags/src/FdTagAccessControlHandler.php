<?php

namespace Drupal\fd_tags;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Fd tag entity.
 *
 * @see \Drupal\fd_tags\Entity\FdTag.
 */
class FdTagAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\fd_tags\Entity\FdTagInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished fd tag entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published fd tag entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit fd tag entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete fd tag entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add fd tag entities');
  }

}
