<?php

namespace Drupal\resource_pool\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class BaseResourceEntityTypeForm.
 */
class BaseResourceEntityTypeForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $base_resource_entity_type = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $base_resource_entity_type->label(),
      '#description' => $this->t("Label for the Base Resource Entity type."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $base_resource_entity_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\resource_pool\Entity\BaseResourceEntityType::load',
      ],
      '#disabled' => !$base_resource_entity_type->isNew(),
    ];

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $base_resource_entity_type = $this->entity;
    $status = $base_resource_entity_type->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Base Resource Entity type.', [
          '%label' => $base_resource_entity_type->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Base Resource Entity type.', [
          '%label' => $base_resource_entity_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($base_resource_entity_type->toUrl('collection'));
  }

}
