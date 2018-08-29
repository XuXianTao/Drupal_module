<?php

use Drupal\node\Entity\Node;
use Drupal\webform\Entity\Webform;

/**
 * @param $nid
 * @return Drupal\webform\Entity\Webform
 */

function get_webform(Node $node)
{
    $wid_value = $node->get('webform')->getValue();
    if (empty($wid_value)) return null;
    $wid = $wid_value[0]['target_id'];
    //return $node->webform->entity;
    return Webform::load($wid);
}

function get_exact_value(Node $node, $p1, $p2='value')
{
    $value = $node->get($p1)->getValue();
    if (empty($value)) return null;
    return $value[0][$p2];
}
