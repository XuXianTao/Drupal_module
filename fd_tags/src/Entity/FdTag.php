<?php

namespace Drupal\fd_tags\Entity;

use Drupal\Component\Plugin\Definition\PluginDefinition;
use Drupal\Console\Test\DataProvider\PluginFieldTypeDataProviderTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Fd tag entity.
 *
 * @ingroup fd_tags
 *
 * @ContentEntityType(
 *   id = "fd_tag",
 *   label = @Translation("Fd tag"),
 *   handlers = {
 *     "storage" = "Drupal\fd_tags\FdTagStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\fd_tags\FdTagListBuilder",
 *     "views_data" = "Drupal\fd_tags\Entity\FdTagViewsData",
 *     "translation" = "Drupal\fd_tags\FdTagTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\fd_tags\Form\FdTagForm",
 *       "add" = "Drupal\fd_tags\Form\FdTagForm",
 *       "edit" = "Drupal\fd_tags\Form\FdTagForm",
 *       "delete" = "Drupal\fd_tags\Form\FdTagDeleteForm",
 *     },
 *     "access" = "Drupal\fd_tags\FdTagAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\fd_tags\FdTagHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "fd_tag",
 *   data_table = "fd_tag_field_data",
 *   revision_table = "fd_tag_revision",
 *   revision_data_table = "fd_tag_field_revision",
 *   translatable = TRUE,
 *   admin_permission = "administer fd tag entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/fd_tag/{fd_tag}",
 *     "add-form" = "/admin/structure/fd_tag/add",
 *     "edit-form" = "/admin/structure/fd_tag/{fd_tag}/edit",
 *     "delete-form" = "/admin/structure/fd_tag/{fd_tag}/delete",
 *     "version-history" = "/admin/structure/fd_tag/{fd_tag}/revisions",
 *     "revision" = "/admin/structure/fd_tag/{fd_tag}/revisions/{fd_tag_revision}/view",
 *     "revision_revert" = "/admin/structure/fd_tag/{fd_tag}/revisions/{fd_tag_revision}/revert",
 *     "revision_delete" = "/admin/structure/fd_tag/{fd_tag}/revisions/{fd_tag_revision}/delete",
 *     "translation_revert" = "/admin/structure/fd_tag/{fd_tag}/revisions/{fd_tag_revision}/revert/{langcode}",
 *     "collection" = "/admin/structure/fd_tag",
 *   },
 *   field_ui_base_route = "fd_tag.settings"
 * )
 */
class FdTag extends RevisionableContentEntityBase implements FdTagInterface
{

    use EntityChangedTrait;

    /**
     * {@inheritdoc}
     */
    public static function preCreate(EntityStorageInterface $storage_controller, array &$values)
    {
        parent::preCreate($storage_controller, $values);
    }

    /**
     * {@inheritdoc}
     */
    protected function urlRouteParameters($rel)
    {
        $uri_route_parameters = parent::urlRouteParameters($rel);

        if ($rel === 'revision_revert' && $this instanceof RevisionableInterface) {
            $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
        } elseif ($rel === 'revision_delete' && $this instanceof RevisionableInterface) {
            $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
        }

        return $uri_route_parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function preSave(EntityStorageInterface $storage)
    {
        parent::preSave($storage);
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

    public function getGroupID()
    {
        return $this->get('group_id')->value;
    }

    public function setGroupID($gid)
    {
        $this->set('group_id', $gid);
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

    /**
     * {@inheritdoc}
     */
    public static function baseFieldDefinitions(EntityTypeInterface $entity_type)
    {
        $fields = parent::baseFieldDefinitions($entity_type);
        $fields['name'] = BaseFieldDefinition::create('string')
            ->setLabel(t('Name'))
            ->setDescription(t('The name of the Fd tag entity.'))
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

        $fields['group_id'] = BaseFieldDefinition::create('integer')
            ->setLabel(t('所属平台编号'))
            ->setDescription(t('标签所在的平台的编号'))
            ->setRevisionable(TRUE)
            ->setDefaultValue(1)
            ->setDisplayOptions('form', [
                'type' => 'number',
                'weight' => -3
            ]);

        $fields['status'] = BaseFieldDefinition::create('boolean')
            ->setLabel(t('Publishing status'))
            ->setDescription(t('A boolean indicating whether the Fd tag is published.'))
            ->setRevisionable(TRUE)
            ->setDefaultValue(TRUE)
            ->setDisplayOptions('form', [
                'type' => 'boolean_checkbox',
                'weight' => -3,
            ]);

        $fields['created'] = BaseFieldDefinition::create('created')
            ->setLabel(t('Created'))
            ->setDescription(t('The time that the entity was created.'));

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
