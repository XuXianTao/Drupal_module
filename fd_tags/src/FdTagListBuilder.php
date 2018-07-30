<?php

namespace Drupal\fd_tags;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Fd tag entities.
 *
 * @ingroup fd_tags
 */
class FdTagListBuilder extends EntityListBuilder {


  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Fd tag ID');
    $header['name'] = $this->t('Name');
    $header['group_id'] = $this->t('Group ID');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\fd_tags\Entity\FdTag */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.fd_tag.edit_form',
      ['fd_tag' => $entity->id()]
    );
    $row['group_id'] = $entity->getGroupID();
    return $row + parent::buildRow($entity);
  }

}
