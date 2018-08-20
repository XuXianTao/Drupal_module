<?php

namespace Drupal\resource_pool\Plugin\rest\resource;

use Composer\Installers\MODULEWorkInstaller;
use Drupal\Console\Command\Site\ModeCommand;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\ds\Plugin\DsField\Entity;
use Drupal\resource_pool\Entity\BaseResourceEntityType;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Psr\Log\LoggerInterface;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "resource_pool_rest_resource",
 *   label = @Translation("Resource pool rest resource"),
 *   uri_paths = {
 *     "canonical" = "/api/resource_pool/{type}"
 *   }
 * )
 */
class ResourcePoolRestResource extends ResourceBase
{

    /**
     * A current user instance.
     *
     * @var \Drupal\Core\Session\AccountProxyInterface
     */
    protected $currentUser;

    protected $type_field;

    const TYPE_FIELD = [
        'image' => [
            'db' => 'base_resource_entity__image',
            'field' => 'image_target_id'
        ],
        'rich_text' => [
            'db' => 'base_resource_entity__special_text',
            'field' => 'special_text_value'
        ]
    ];
    const STRING_BOOL = [
        'true' => 1,
        'false' => 0
    ];

    /**
     * Constructs a new ResourcePoolRestResource object.
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
            $container->get('logger.factory')->get('resource_pool'),
            $container->get('current_user')
        );
    }

    /**
     * Responds to GET requests.
     *
     * Returns a list of bundles for specified entity.
     *
     * @var $type = 'tupian' | 'fuwenben'
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     *   Throws exception expected.
     */
    public function get($type)
    {
        // You must to implement the logic of your REST Resource here.
        // Use current user after pass authentication to validate access.
        if (!$this->currentUser->hasPermission('access content')) {
            throw new AccessDeniedHttpException();
        }
        //索引不存在的bundle类型
        if (!array_key_exists($type, self::TYPE_FIELD)) {
            return new ModifiedResourceResponse('The resource type ' . $type . ' was not found.', 404);
        }
        $param = \Drupal::request()->query;
        /** @var $tax = taxonomy_name */
        $tax = $param->get('taxonomy');
        $tax_id = 0;
        /** @var $status = 0 | 1 $status */
        $status = $param->get('published');
        $limit = $param->get('limit', 10);
        $page = $param->get('page', 0);

        if (array_key_exists($status, self::STRING_BOOL)) {
            $status = self::STRING_BOOL[$status];
        } else $status = null;


        //索引不存在的分类
        if (!empty($tax)) {
            $storage_tax = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
            $des_tax = $storage_tax->loadByProperties(['name' => $tax, 'vid'=> 'ziyuanmoban']);
            $tax_id = array_values($des_tax)[0]->id();
            if (empty($des_tax)) {
                return new ModifiedResourceResponse('The taxonomy ' . $tax . ' was not found.', 404);
            }
        }
        $storage = \Drupal::entityTypeManager()->getStorage('base_resource_entity');
        $query_resource = $storage->getQuery();
        $query_resource->condition('type', $type);
        if (!empty($status) || $status === 0) {
            $query_resource->condition('status', $status);
        }
        if (!empty($tax)) {
            $query_resource->condition('taxonomy', $tax_id);
        }
        $entities_id = $query_resource->execute();
        $total = count($entities_id);
        if ($total===0) {
            return new ModifiedResourceResponse('There is not resources.', 404);
        }

        //获取资源信息
        $query = \Drupal::database()->select(self::TYPE_FIELD[$type]['db'], 'tf');
        $query->fields('tf', ['entity_id', self::TYPE_FIELD[$type]['field']]);
        $query->condition('entity_id', $entities_id, 'IN');
        $query->condition('bundle', $type);
        $query->condition('deleted', 0);
        $query->range($page*$limit, $limit);
        $resources = $query->execute()->fetchAllKeyed();

        $list = [];
        foreach ($entities_id as $id) {
            $entity = $storage->load($id);
            $list[$id] = [
                'published' => $entity->get('status')->value?true:false,
                'resource' => $this->solveResult($resources[$id], $type)
            ];
        }
        $result = [
            'total' => $total,
            'page_size' => $limit,
            'page' => $page,
            'list' => $list
        ];

        return new ResourceResponse($result);
    }

    protected function solveResult($input, $type) {
        switch ($type) {
            case 'image': {
                $file = \Drupal::entityTypeManager()->getStorage('file')->load($input);
                return !empty($file)?file_create_url($file->uri->value) : "" ;
                break;
            }
            case 'rich_text': {
                return $input;
                break;
            }
            default: return null;
        }
    }

}
