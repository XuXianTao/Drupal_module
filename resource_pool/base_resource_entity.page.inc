<?php

/**
 * @file
 * Contains base_resource_entity.page.inc.
 *
 * Page callback for Base Resource Entity entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Base Resource Entity templates.
 *
 * Default template: base_resource_entity.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_base_resource_entity(array &$variables) {
  // Fetch BaseResourceEntity Entity Object.
  $base_resource_entity = $variables['elements']['#base_resource_entity'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
