<?php

namespace Drupal\fd_tags;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\fd_tags\Entity\FdTagInterface;

/**
 * Defines the storage handler class for Fd tag entities.
 *
 * This extends the base storage class, adding required special handling for
 * Fd tag entities.
 *
 * @ingroup fd_tags
 */
class FdTagStorage extends SqlContentEntityStorage implements FdTagStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(FdTagInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {fd_tag_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {fd_tag_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(FdTagInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {fd_tag_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('fd_tag_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
