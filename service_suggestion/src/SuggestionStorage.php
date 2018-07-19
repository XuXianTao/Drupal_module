<?php

namespace Drupal\service_suggestion;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\service_suggestion\Entity\SuggestionInterface;

/**
 * Defines the storage handler class for contact_name entities.
 *
 * This extends the base storage class, adding required special handling for
 * contact_name entities.
 *
 * @ingroup service_suggestion
 */
class SuggestionStorage extends SqlContentEntityStorage implements SuggestionStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(SuggestionInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {suggestion_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {suggestion_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(SuggestionInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {suggestion_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('suggestion_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
