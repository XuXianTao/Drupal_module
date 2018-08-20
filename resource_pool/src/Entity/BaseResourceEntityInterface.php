<?php

namespace Drupal\resource_pool\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Base Resource Entity entities.
 *
 * @ingroup resource_pool
 */
interface BaseResourceEntityInterface extends  ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Base Resource Entity name.
   *
   * @return string
   *   Name of the Base Resource Entity.
   */
  public function getName();

  /**
   * Sets the Base Resource Entity name.
   *
   * @param string $name
   *   The Base Resource Entity name.
   *
   * @return \Drupal\resource_pool\Entity\BaseResourceEntityInterface
   *   The called Base Resource Entity entity.
   */
  public function setName($name);

  /**
   * Gets the Base Resource Entity creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Base Resource Entity.
   */
  public function getCreatedTime();

  /**
   * Sets the Base Resource Entity creation timestamp.
   *
   * @param int $timestamp
   *   The Base Resource Entity creation timestamp.
   *
   * @return \Drupal\resource_pool\Entity\BaseResourceEntityInterface
   *   The called Base Resource Entity entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Base Resource Entity published status indicator.
   *
   * Unpublished Base Resource Entity are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Base Resource Entity is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Base Resource Entity.
   *
   * @param bool $published
   *   TRUE to set this Base Resource Entity to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\resource_pool\Entity\BaseResourceEntityInterface
   *   The called Base Resource Entity entity.
   */
  public function setPublished($published);

  public function getTaxonomy();
}
