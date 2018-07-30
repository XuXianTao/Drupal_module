<?php

/**
 * @file
 * Contains fd_tag.page.inc.
 *
 * Page callback for Fd tag entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Fd tag templates.
 *
 * Default template: fd_tag.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_fd_tag(array &$variables) {
  // Fetch FdTag Entity Object.
  $fd_tag = $variables['elements']['#fd_tag'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
