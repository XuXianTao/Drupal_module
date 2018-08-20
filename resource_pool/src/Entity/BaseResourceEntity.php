<?php

namespace Drupal\resource_pool\Entity;

use Drupal\Core\Entity\EntityManager;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\taxonomy\Plugin\migrate\cckfield\TaxonomyTermReference;
use Drupal\taxonomy\Plugin\views\wizard\TaxonomyTerm;
use Drupal\user\UserInterface;

/**
 * Defines the Base Resource Entity entity.
 *
 * @ingroup resource_pool
 *
 * @ContentEntityType(
 *   id = "base_resource_entity",
 *   label = @Translation("Base Resource Entity"),
 *   bundle_label = @Translation("Base Resource Entity type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\resource_pool\BaseResourceEntityListBuilder",
 *     "views_data" = "Drupal\resource_pool\Entity\BaseResourceEntityViewsData",
 *     "translation" = "Drupal\resource_pool\BaseResourceEntityTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\resource_pool\Form\BaseResourceEntityForm",
 *       "add" = "Drupal\resource_pool\Form\BaseResourceEntityForm",
 *       "edit" = "Drupal\resource_pool\Form\BaseResourceEntityForm",
 *       "delete" = "Drupal\resource_pool\Form\BaseResourceEntityDeleteForm",
 *     },
 *     "access" = "Drupal\resource_pool\BaseResourceEntityAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\resource_pool\BaseResourceEntityHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "base_resource_entity",
 *   data_table = "base_resource_entity_field_data",
 *   translatable = TRUE,
 *   admin_permission = "administer base resource entity entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "bundle" = "type",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/base_resource_entity/{base_resource_entity}",
 *     "add-page" = "/admin/structure/base_resource_entity/add",
 *     "add-form" = "/admin/structure/base_resource_entity/add/{base_resource_entity_type}",
 *     "edit-form" = "/admin/structure/base_resource_entity/{base_resource_entity}/edit",
 *     "delete-form" = "/admin/structure/base_resource_entity/{base_resource_entity}/delete",
 *     "collection" = "/admin/structure/base_resource_entity",
 *   },
 *   bundle_entity_type = "base_resource_entity_type",
 *   field_ui_base_route = "entity.base_resource_entity_type.edit_form"
 * )
 */
class BaseResourceEntity extends ContentEntityBase implements BaseResourceEntityInterface
{

    use EntityChangedTrait;

    /**
     * {@inheritdoc}
     */
    public static function preCreate(EntityStorageInterface $storage_controller, array &$values)
    {
        parent::preCreate($storage_controller, $values);
        $values += [
            'user_id' => \Drupal::currentUser()->id(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->get('name')->value;
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        $this->set('name', $name);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedTime()
    {
        return $this->get('created')->value;
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedTime($timestamp)
    {
        $this->set('created', $timestamp);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOwner()
    {
        return $this->get('user_id')->entity;
    }

    /**
     * {@inheritdoc}
     */
    public function getOwnerId()
    {
        return $this->get('user_id')->target_id;
    }

    /**
     * {@inheritdoc}
     */
    public function setOwnerId($uid)
    {
        $this->set('user_id', $uid);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setOwner(UserInterface $account)
    {
        $this->set('user_id', $account->id());
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isPublished()
    {
        return (bool)$this->getEntityKey('status');
    }

    /**
     * {@inheritdoc}
     */
    public function setPublished($published)
    {
        $this->set('status', $published ? TRUE : FALSE);
        return $this;
    }

    public function getTaxonomy()
    {
        $storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
        $taxonomy = $storage->load($this->get('taxonomy')->getString());
        if (!empty($taxonomy)) return $taxonomy->label();
        else return '';
    }

    /**
     * {@inheritdoc}
     */
    public static function baseFieldDefinitions(EntityTypeInterface $entity_type)
    {
        $fields = parent::baseFieldDefinitions($entity_type);

        $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
            ->setLabel(t('Authored by'))
            ->setDescription(t('The user ID of author of the Base Resource Entity entity.'))
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
            ->setDisplayConfigurable('form', TRUE)
            ->setDisplayConfigurable('view', TRUE);

        $fields['name'] = BaseFieldDefinition::create('string')
            ->setLabel(t('Name'))
            ->setDescription(t('The name of the Base Resource Entity entity.'))
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
            ->setDisplayConfigurable('view', TRUE);

        $fields['taxonomy'] = BaseFieldDefinition::create('entity_reference')
            ->setLabel(t('分类'))
            ->setDescription(t('The taxonomy that the entity belongs to.'))
            ->setSetting('target_type', 'taxonomy_term')
            ->setSetting('handler_settings', ['target_bundles' => ['taxonomy_term' => 'resource_template']])
            ->setDisplayOptions('form', [
                'type' => 'options_select',
                'weight' => 10
            ])
            ->setDisplayConfigurable('form', TRUE);

        $fields['status'] = BaseFieldDefinition::create('boolean')
            ->setLabel(t('Publishing status'))
            ->setDescription(t('A boolean indicating whether the Base Resource Entity is published.'))
            ->setDefaultValue(TRUE)
            ->setDisplayOptions('form', [
                'type' => 'boolean_checkbox',
                'weight' => 10
            ])
            ->setDisplayConfigurable('form', TRUE);

        $fields['created'] = BaseFieldDefinition::create('created')
            ->setLabel(t('Created'))
            ->setDescription(t('The time that the entity was created.'));

        $fields['changed'] = BaseFieldDefinition::create('changed')
            ->setLabel(t('Changed'))
            ->setDescription(t('The time that the entity was last edited.'));

        return $fields;
    }

}
