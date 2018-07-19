<?php

namespace Drupal\service_suggestion\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for contact_name entities.
 */
class SuggestionViewsData extends EntityViewsData {

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
