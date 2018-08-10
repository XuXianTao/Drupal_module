<?php

use Drupal\node\Entity\Node;
use Drupal\webform\Entity\Webform;

/**
 * @param $nid
 * @return Drupal\webform\Entity\Webform
 */

function get_webform(Node $node)
{
    $wid = $node->get('webform')->getValue()[0]['target_id'];
    //return $node->webform->entity;
    return Webform::load($wid);
}

function get_exact_value(Node $node, $p1, $p2='value')
{
    return $node->get($p1)->getValue()[0][$p2];
}
