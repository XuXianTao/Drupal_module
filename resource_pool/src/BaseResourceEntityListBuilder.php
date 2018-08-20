<?php

namespace Drupal\resource_pool;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Base Resource Entity entities.
 *
 * @ingroup resource_pool
 */
class BaseResourceEntityListBuilder extends EntityListBuilder {


  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Base Resource Entity ID');
    $header['name'] = $this->t('Name');
    $header['taxonomy'] = $this->t('Taxonomy');
    $header['content_type'] = $this->t('Content Type');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\resource_pool\Entity\BaseResourceEntity */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.base_resource_entity.edit_form',
      ['base_resource_entity' => $entity->id()]
    );
    $row['taxonomy'] = $entity->getTaxonomy();
    $storage = \Drupal::entityTypeManager()->getStorage('base_resource_entity_type');
    $type_name = $storage->load($entity->get('type')->getString())->get('label');
    $row['content_type'] = $type_name;
    return $row + parent::buildRow($entity);
  }

}
