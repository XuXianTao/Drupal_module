<?php

namespace Drupal\fd_tags;

use Drupal\Core\Entity\ContentEntityStorageInterface;
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
interface FdTagStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Fd tag revision IDs for a specific Fd tag.
   *
   * @param \Drupal\fd_tags\Entity\FdTagInterface $entity
   *   The Fd tag entity.
   *
   * @return int[]
   *   Fd tag revision IDs (in ascending order).
   */
  public function revisionIds(FdTagInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Fd tag author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Fd tag revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\fd_tags\Entity\FdTagInterface $entity
   *   The Fd tag entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(FdTagInterface $entity);

  /**
   * Unsets the language for all Fd tag with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
