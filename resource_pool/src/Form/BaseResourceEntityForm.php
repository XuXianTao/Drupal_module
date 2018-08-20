<?php

namespace Drupal\resource_pool\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Base Resource Entity edit forms.
 *
 * @ingroup resource_pool
 */
class BaseResourceEntityForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\resource_pool\Entity\BaseResourceEntity */
    $form = parent::buildForm($form, $form_state);
    $entity = $this->entity;
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = &$this->entity;

    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Base Resource Entity.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Base Resource Entity.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.base_resource_entity.canonical', ['base_resource_entity' => $entity->id()]);
  }

}
