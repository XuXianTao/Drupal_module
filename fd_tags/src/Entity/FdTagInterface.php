<?php

namespace Drupal\fd_tags\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Fd tag entities.
 *
 * @ingroup fd_tags
 */
interface FdTagInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Fd tag name.
   *
   * @return string
   *   Name of the Fd tag.
   */
  public function getName();

  /**
   * Sets the Fd tag name.
   *
   * @param string $name
   *   The Fd tag name.
   *
   * @return \Drupal\fd_tags\Entity\FdTagInterface
   *   The called Fd tag entity.
   */
  public function setName($name);

  /**
   * Gets the Fd tag creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Fd tag.
   */
  public function getCreatedTime();

  /**
   * Sets the Fd tag creation timestamp.
   *
   * @param int $timestamp
   *   The Fd tag creation timestamp.
   *
   * @return \Drupal\fd_tags\Entity\FdTagInterface
   *   The called Fd tag entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Fd tag published status indicator.
   *
   * Unpublished Fd tag are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Fd tag is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Fd tag.
   *
   * @param bool $published
   *   TRUE to set this Fd tag to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\fd_tags\Entity\FdTagInterface
   *   The called Fd tag entity.
   */
  public function setPublished($published);

  /**
   * Gets the Fd tag revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Fd tag revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\fd_tags\Entity\FdTagInterface
   *   The called Fd tag entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Fd tag revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Fd tag revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\fd_tags\Entity\FdTagInterface
   *   The called Fd tag entity.
   */
  public function setRevisionUserId($uid);

  public function getGroupID();

  public function setGroupID($gid);

}
