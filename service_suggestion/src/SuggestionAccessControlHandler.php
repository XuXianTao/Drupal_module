<?php

namespace Drupal\service_suggestion;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the suggestion entity.
 *
 * @see \Drupal\service_suggestion\Entity\Suggestion.
 */
class SuggestionAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\service_suggestion\Entity\SuggestionInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished suggestion entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published suggestion entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit suggestion entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete suggestion entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add suggestion entities');
  }

}
