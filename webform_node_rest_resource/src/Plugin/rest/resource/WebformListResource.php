<?php

namespace Drupal\webform_node_rest_resource\Plugin\rest\resource;

use Drupal\Core\Database\Database;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\webform\Entity\Webform;
use Drupal\webform\WebformSubmissionForm;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "webform_list_resource",
 *   label = @Translation("Webform list resource"),
 *   uri_paths = {
 *     "canonical" = "/api/webform_node"
 *   }
 * )
 */
class WebformListResource extends ResourceBase {

  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Constructs a new WebformListResource object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   A current user instance.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    AccountProxyInterface $current_user) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);

    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('webform_rest_resource'),
      $container->get('current_user')
    );
  }

  /**
   * Responds to OPTIONS requests.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity object.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The HTTP response object.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function get() {

    // You must to implement the logic of your REST Resource here.
    // Use current user after pass authentication to validate access.
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }

    return $this->getAllWebform();
  }

    protected function getAllWebform()
    {
        $params = \Drupal::request()->query;
        $limit = $params->get('limit', 10);
        /** $page int 0~unlimited */
        $page = $params->get('page', 0);
        /** $status   'open'/ 'closed'/ 'scheduled' */
        $status = $params->get('status', '');
        $search =  $params->get('search', '');
        $results = [];
        /** @var \Drupal\Core\Database\Connection $db */
        $db = \Drupal::database();
        $query = $db->select('node__webform', 'wb');
        $query->leftJoin('node_field_data', 'n', 'wb.entity_id = n.nid');
        $query->fields('wb', ['webform_target_id', 'entity_id']);
        $query->condition('n._deleted', 0);
        if (!empty($status)) {
            $query->condition('wb.webform_status', $status);
        }
        if (!empty($search)) {
            $query->condition('n.title', '%'. $query->escapeLike($search) . '%', 'LIKE');
        }
        $query_clone = clone $query;
        $total = $query_clone->countQuery()->execute()->fetchField();
        $query->range($page*$limit, $limit);
        $webform_ids = $query->execute()->fetchCol(0);
        $node_ids = $query->execute()->fetchCol(1);
        $webforms = Webform::loadMultiple($webform_ids);
        $i = 0;
        foreach ($webforms as $id => $webform) {
            _webform_node_rest_resource_build_form($result_elements, $webform, $node_ids[$i++]);
            $results[$id] = $result_elements;
        }
        $response = new ModifiedResourceResponse([
            'total' => (int)$total,
            'page_size' => (int)$limit,
            'page' => (int)$page,
            'list' => $results,
        ]);

        return $response;
    }


}
