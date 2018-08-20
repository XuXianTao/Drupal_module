<?php

namespace Drupal\resource_pool\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Base Resource Entity type entity.
 *
 * @ConfigEntityType(
 *   id = "base_resource_entity_type",
 *   label = @Translation("Base Resource Entity type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\resource_pool\BaseResourceEntityTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\resource_pool\Form\BaseResourceEntityTypeForm",
 *       "edit" = "Drupal\resource_pool\Form\BaseResourceEntityTypeForm",
 *       "delete" = "Drupal\resource_pool\Form\BaseResourceEntityTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\resource_pool\BaseResourceEntityTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "base_resource_entity_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "base_resource_entity",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/base_resource_entity_type/{base_resource_entity_type}",
 *     "add-form" = "/admin/structure/base_resource_entity_type/add",
 *     "edit-form" = "/admin/structure/base_resource_entity_type/{base_resource_entity_type}/edit",
 *     "delete-form" = "/admin/structure/base_resource_entity_type/{base_resource_entity_type}/delete",
 *     "collection" = "/admin/structure/base_resource_entity_type"
 *   }
 * )
 */
class BaseResourceEntityType extends ConfigEntityBundleBase implements BaseResourceEntityTypeInterface {

  /**
   * The Base Resource Entity type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Base Resource Entity type label.
   *
   * @var string
   */
  protected $label;

}
