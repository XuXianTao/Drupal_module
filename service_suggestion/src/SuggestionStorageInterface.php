<?php

namespace Drupal\service_suggestion;

use Drupal\Core\Entity\ContentEntityStorageInterface;
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
interface SuggestionStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of contact_name revision IDs for a specific contact_name.
   *
   * @param \Drupal\service_suggestion\Entity\SuggestionInterface $entity
   *   The contact_name entity.
   *
   * @return int[]
   *   contact_name revision IDs (in ascending order).
   */
  public function revisionIds(SuggestionInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as contact_name author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   contact_name revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\service_suggestion\Entity\SuggestionInterface $entity
   *   The contact_name entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(SuggestionInterface $entity);

  /**
   * Unsets the language for all contact_name with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
