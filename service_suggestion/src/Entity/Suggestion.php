<?php

namespace Drupal\service_suggestion\Entity;

use Drupal\Core\Config\Entity\Exception\ConfigEntityIdLengthException;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\jsonapi\Exception\EntityAccessDeniedHttpException;
use Drupal\user\UserInterface;
use Exception;

/**
 * Defines the contact_name entity.
 *
 * @ingroup 服务建议
 *
 * @ContentEntityType(
 *   id = "suggestion",
 *   label = @Translation("服务建议"),
 *   handlers = {
 *     "storage" = "Drupal\service_suggestion\SuggestionStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\service_suggestion\SuggestionListBuilder",
 *     "views_data" = "Drupal\service_suggestion\Entity\SuggestionViewsData",
 *     "translation" = "Drupal\service_suggestion\SuggestionTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\service_suggestion\Form\SuggestionForm",
 *       "add" = "Drupal\service_suggestion\Form\SuggestionForm",
 *       "edit" = "Drupal\service_suggestion\Form\SuggestionForm",
 *       "delete" = "Drupal\service_suggestion\Form\SuggestionDeleteForm",
 *     },
 *     "access" = "Drupal\service_suggestion\SuggestionAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\service_suggestion\SuggestionHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "suggestion",
 *   data_table = "suggestion_field_data",
 *   revision_table = "suggestion_revision",
 *   revision_data_table = "suggestion_field_revision",
 *   translatable = TRUE,
 *   admin_permission = "administer suggestion entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "label" = "title",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *     "contact_name" = "contact_name",
 *     "contact_phone" = "contact_phone",
 *     "suggestion" = "suggestion",
 *     "created" = "created",
 *   },
 *   links = {
 *     "canonical" = "/suggestion/{suggestion}",
 *     "add-form" = "/admin/structure/suggestion/add",
 *     "edit-form" = "/admin/structure/suggestion/{suggestion}/edit",
 *     "delete-form" = "/admin/structure/suggestion/{suggestion}/delete",
 *     "version-history" = "/admin/structure/suggestion/{suggestion}/revisions",
 *     "revision" = "/admin/structure/suggestion/{suggestion}/revisions/{suggestion_revision}/view",
 *     "revision_revert" = "/admin/structure/suggestion/{suggestion}/revisions/{suggestion_revision}/revert",
 *     "revision_delete" = "/admin/structure/suggestion/{suggestion}/revisions/{suggestion_revision}/delete",
 *     "translation_revert" = "/admin/structure/suggestion/{suggestion}/revisions/{suggestion_revision}/revert/{langcode}",
 *     "collection" = "/admin/structure/suggestion",
 *   },
 *   field_ui_base_route = "suggestion.settings"
 * )
 */
class Suggestion extends RevisionableContentEntityBase implements SuggestionInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    /*
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
    */
  }


  /**
   * {@inheritdoc}
   */
  protected function urlRouteParameters($rel) {
    $uri_route_parameters = parent::urlRouteParameters($rel);

    if ($rel === 'revision_revert' && $this instanceof RevisionableInterface) {
      $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
    }
    elseif ($rel === 'revision_delete' && $this instanceof RevisionableInterface) {
      $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
    }

    return $uri_route_parameters;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {

    parent::preSave($storage);

    foreach (array_keys($this->getTranslationLanguages()) as $langcode) {
      $translation = $this->getTranslation($langcode);

      // If no owner has been set explicitly, make the anonymous user the owner.
      /*if (!$translation->getOwner()) {
        $translation->setOwnerId(0);
      }*/
    }

    // If no revision author has been set explicitly, make the suggestion owner the
    // revision author.
    /*
    if (!$this->getRevisionUser()) {
      $this->setRevisionUserId($this->getOwnerId());
    }*/
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->get('title')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setTitle($name) {
    $this->set('title', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isPublished() {
    return (bool) $this->getEntityKey('status');
  }

  /**
   * {@inheritdoc}
   */
  public function setPublished($published) {
    $this->set('status', $published ? TRUE : FALSE);
    return $this;
  }

  /**
   * {@inheritdic}
   */
  public function getContactName() {
    return (string) $this->getEntityKey('contact_name');
  }

  public function setContactName($name) {
    $this->set('contact_name', $name);
    return $this;
  }

  public function getContactPhone() {
    return (string) $this->getEntityKey('contact_phone');
  }

  public function setContactPhone($phone) {
    $this->set('contact_phone', $phone);
    return $this;
  }

  public function getSuggestion() {
    return (string) $this->getEntityKey('suggestion');
  }

  public function setSuggestion($sug) {
    $this->set('suggestion', $sug);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    /*
    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the contact_name entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', FALSE);
      */

    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('标题'))
      ->setDescription(t('服务建议的标题'))
      ->setRevisionable(TRUE)
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['contact_name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('联系人姓名'))
      ->setDescription(t('提出改建议的联系人'))
      ->setRevisionable(TRUE)
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 3,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['contact_phone'] = BaseFieldDefinition::create('string')
      ->setLabel(t('联系人电话'))
      ->setDescription(t('建议提出者的电话'))
      ->setRevisionable(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 3,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['suggestion'] = BaseFieldDefinition::create('string')
      ->setLabel(t('建议内容'))
      ->setDescription(t('具体建议内容'))
      ->setRevisionable(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the suggestion is published.'))
      ->setRevisionable(TRUE)
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => -3,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'))
      ->setDisplayConfigurable('view', TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['revision_translation_affected'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Revision translation affected'))
      ->setDescription(t('Indicates if the last edit of a translation belongs to current revision.'))
      ->setReadOnly(TRUE)
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE);

    return $fields;
  }

}
