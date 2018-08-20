<?php

namespace Drupal\webform_template_rest\Plugin\rest\resource;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\webform\Entity\Webform;
use Drupal\webform_templates\Controller\webform_templatesController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Psr\Log\LoggerInterface;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "webform_template_resource",
 *   label = @Translation("Webform template resource"),
 *   uri_paths = {
 *     "canonical" = "/api/webform/templates"
 *   }
 * )
 */
class WebformTemplateResource extends ResourceBase
{

    /**
     * A current user instance.
     *
     * @var \Drupal\Core\Session\AccountProxyInterface
     */
    protected $currentUser;

    /**
     * Constructs a new webform_templateResource object.
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
        AccountProxyInterface $current_user)
    {
        parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);

        $this->currentUser = $current_user;
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition)
    {
        return new static(
            $configuration,
            $plugin_id,
            $plugin_definition,
            $container->getParameter('serializer.formats'),
            $container->get('logger.factory')->get('webform_template_rest'),
            $container->get('current_user')
        );
    }

    /**
     * Responds to GET requests.
     *
     * Returns a list of bundles for specified entity.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     *   Throws exception expected.
     */
    public function get()
    {

        // You must to implement the logic of your REST Resource here.
        // Use current user after pass authentication to validate access.
        if (!$this->currentUser->hasPermission('access content')) {
            throw new AccessDeniedHttpException();
        }
        //获取参数
        $params = \Drupal::request()->query;
        $keys = $params->get('search');
        $category = $params->get('category');
        $limit = $params->get('limit', 10);
        $page = $params->get('page', 0);
        $only_cover = $params->get('only_cover')=='true' ? true: false;

        $webformStorage = \Drupal::entityTypeManager()->getStorage('webform');
        $query = $webformStorage->getQuery();
        $query->condition('template', TRUE);
        $query->condition('archive', FALSE);
        // Filter by key(word).
        if ($keys) {
            $or = $query->orConditionGroup()
                ->condition('title', $keys, 'CONTAINS')
                ->condition('description', $keys, 'CONTAINS')
                ->condition('category', $keys, 'CONTAINS')
                ->condition('elements', $keys, 'CONTAINS');
            $query->condition($or);
        }

        // Filter by category.
        if ($category) {
            $query->condition('category', $category);
        }
        //$query->range($page*$limit, $limit);

        $query->sort('title');

        $entity_ids = $query->execute();
        if (empty($entity_ids)) {
            return new ModifiedResourceResponse([]);
        }

        /* @var $entities \Drupal\webform\WebformInterface[] */
        $entities = $webformStorage->loadMultiple($entity_ids);

        // If the user is not a webform admin, check view access to each webform.
        if (!$this->currentUser->hasPermission('administer webform') || $this->currentUser->hasPermission('edit any webform')) {
            foreach ($entities as $entity_id => $entity) {
                if (!$entity->access('view')) {
                    unset($entities[$entity_id]);
                }
            }
        }

        $result = _webform_template_rest_construct($entities, $limit, $page, $only_cover);
        return new ResourceResponse($result);
    }

}
