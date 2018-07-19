<?php

namespace Drupal\service_suggestion;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of contact_name entities.
 *
 * @ingroup service_suggestion
 */
class SuggestionListBuilder extends EntityListBuilder {


  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['name'] = $this->t('标题');
    $header['contact_name'] = $this->t('联系人');
    $header['contact_phone'] = $this->t('联系电话');
    $header['suggestion'] = $this->t('服务建议');
    $header['created'] = $this->t('提交时间');

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\service_suggestion\Entity\Suggestion */
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.suggestion.edit_form',
      ['suggestion' => $entity->id()]
    );
    $row['contact_name'] = $entity->getContactName();
    $row['contact_phone'] = $entity->getContactPhone();
    $row['suggestion'] = $entity->getSuggestion();
    $row['created'] = date('Y-m-d H:i:s', $entity->getCreatedTime());
    return $row + parent::buildRow($entity);
  }

}
