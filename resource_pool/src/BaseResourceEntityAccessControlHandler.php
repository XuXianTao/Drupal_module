<?php

namespace Drupal\resource_pool;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Base Resource Entity entity.
 *
 * @see \Drupal\resource_pool\Entity\BaseResourceEntity.
 */
class BaseResourceEntityAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\resource_pool\Entity\BaseResourceEntityInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished base resource entity entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published base resource entity entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit base resource entity entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete base resource entity entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add base resource entity entities');
  }

}
