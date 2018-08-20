<?php

namespace Drupal\resource_pool\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Base Resource Entity entities.
 */
class BaseResourceEntityViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Additional information for Views integration, such as table joins, can be
    // put here.

    return $data;
  }

}
