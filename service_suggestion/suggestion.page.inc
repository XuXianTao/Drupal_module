<?php

/**
 * @file
 * Contains suggestion.page.inc.
 *
 * Page callback for contact_name entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for contact_name templates.
 *
 * Default template: suggestion.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_suggestion(array &$variables) {
  // Fetch Suggestion Entity Object.
  $suggestion = $variables['elements']['#suggestion'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
