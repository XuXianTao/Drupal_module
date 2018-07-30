<?php

namespace Drupal\fd_tags\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Fd tag entities.
 */
class FdTagViewsData extends EntityViewsData {

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
