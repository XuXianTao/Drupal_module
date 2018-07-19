<?php

namespace Drupal\service_suggestion\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining contact_name entities.
 *
 * @ingroup service_suggestion
 */
interface SuggestionInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the contact_name name.
   *
   * @return string
   *   Name of the contact_name.
   */
  public function getTitle();

  /**
   * Sets the contact_name name.
   *
   * @param string $name
   *   The contact_name name.
   *
   * @return \Drupal\service_suggestion\Entity\SuggestionInterface
   *   The called contact_name entity.
   */
  public function setTitle($name);

  /**
   * Gets the contact_name creation timestamp.
   *
   * @return int
   *   Creation timestamp of the contact_name.
   */
  public function getCreatedTime();

  /**
   * Sets the contact_name creation timestamp.
   *
   * @param int $timestamp
   *   The contact_name creation timestamp.
   *
   * @return \Drupal\service_suggestion\Entity\SuggestionInterface
   *   The called contact_name entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the contact_name published status indicator.
   *
   * Unpublished contact_name are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the contact_name is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a contact_name.
   *
   * @param bool $published
   *   TRUE to set this contact_name to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\service_suggestion\Entity\SuggestionInterface
   *   The called contact_name entity.
   */
  public function setPublished($published);

  /**
   * Gets the contact_name revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the contact_name revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\service_suggestion\Entity\SuggestionInterface
   *   The called contact_name entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the contact_name revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the contact_name revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\service_suggestion\Entity\SuggestionInterface
   *   The called contact_name entity.
   */
  public function setRevisionUserId($uid);

  public function getContactName();

  public function setContactName($name);

  public function getContactPhone();

  public function setContactPhone($phone);

  public function getSuggestion();

  public function setSuggestion($sug);
}
