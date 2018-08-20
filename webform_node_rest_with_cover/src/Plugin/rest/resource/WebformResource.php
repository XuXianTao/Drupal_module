<?php

namespace Drupal\webform_node_rest_with_cover\Plugin\rest\resource;

use Drupal\Core\Database\Database;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\node\Entity\Node;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\user\Entity\User;
use Drupal\webform\Entity\Webform;
use Drupal\webform\Entity\WebformSubmission;
use Drupal\webform\WebformSubmissionForm;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "webform_node_cover_resource",
 *   label = @Translation("Webform resource"),
 *   uri_paths = {
 *     "canonical" = "/api/webform_node/{nid}",
 *     "https://www.drupal.org/link-relations/create" = "/api/webform_node"
 *   }
 * )
 */
class WebformResource extends ResourceBase
{

    /**
     * A current user instance.
     *
     * @var \Drupal\Core\Session\AccountProxyInterface
     */
    protected $currentUser;

    protected $storage;

    /**
     * Constructs a new WebformResource object.
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
        AccountProxyInterface $current_user
    )
    {
        parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);

        $this->currentUser = $current_user;
        $this->storage = \Drupal::entityTypeManager()->getStorage('webform');
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
            $container->get('logger.factory')->get('webform_rest_resource'),
            $container->get('current_user'),
            $container->get('request_stack')->getCurrentRequest()
        );
    }

    /**
     * Responds to GET requests.
     *
     * @param \Drupal\Core\Entity\EntityInterface $entity
     *   The entity object.
     *
     * @return ModifiedResourceResponse
     *   The HTTP response object.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     *   Throws exception expected.
     */
    public function get($nid)
    {
        // You must to implement the logic of your REST Resource here.
        // Use current user after pass authentication to validate access.
        if (!$this->currentUser->hasPermission('access content')) {
            throw new AccessDeniedHttpException();
        }

        return $this->getWebform($nid);
    }

    public function post()
    {
        $input = \Drupal::request()->getContent();
        $data = \GuzzleHttp\json_decode($input, true);

        $uuid_servie = \Drupal::service('uuid');
        $uuid = $uuid_servie->generate();
        //由于webform的id限制为32位字符，将uuid整理为32位
        $new_id = str_replace('-', '', $uuid);
        $webform = Webform::create([
            'id' => $new_id,
            'title' => $data['title'],
        ]);
        $node = Node::create([
            'type' => 'webform',
            'title' => $data['title'],
            'body' => $data['description']
        ]);
        $this->checkUpdate($webform, $node, $data);

        $result = [];
        $result['nid'] = $node->id();
        $result['wid'] = $webform->id();
        return new ModifiedResourceResponse($result, 201);
    }


    public function patch($nid)
    {
        /** @var Webform $webform */
        $node = Node::load($nid);
        if (empty($node)) {
            throw new NotFoundHttpException('The node ' . $nid . ' was not found.');
        }
        $webform = get_webform($node);
        if (empty($webform)) {
            throw new NotFoundHttpException('The node ' . $nid . '\'s webform was not found.');
        }
        $raw = \Drupal::request()->getContent();
        $data = \GuzzleHttp\json_decode($raw, TRUE);

        $this->checkUpdate($webform, $node, $data);

        return $this->getWebform($nid, true);
    }

    public function delete($nid)
    {
        $node = Node::load($nid);
        if (empty($node)) {
            throw new NotFoundHttpException('The node ' . $nid . ' was not found.');
        }
        $webform = get_webform($node);
        if (empty($webform)) {
            throw new NotFoundHttpException('The node ' . $nid . '\'s webform was not found.');
        }

        //
        if (!$this->currentUser->hasPermission('restful delete webform_resource')) {
            throw new AccessDeniedHttpException();
        }
        $node->delete();
        $webform->delete();
        /** @var Webform $webform */
        return new ModifiedResourceResponse(null, 204);
    }


    /**Helper Function
     * @param $id string    Webform Node ID(title)
     * @return ModifiedResourceResponse
     */
    protected function getWebform($nid, $renew = false)
    {
        /** @var Webform $webform */
        $node = Node::load($nid);
        if (!$node) {
            throw new NotFoundHttpException('The node ' . $nid . ' was not found.');
        }
        $webform = get_webform($node);
        if (!$webform) {
            throw new NotFoundHttpException('The Webform Node' . $nid . '\'s webform was not found.');
        }
        _webform_node_rest_with_cover_build_form($data, $webform, $nid);
        $response = new ModifiedResourceResponse($data, $renew ? 201 : 200);
        return $response;
    }

    protected function checkUpdate(Webform &$webform, Node &$node, &$data)
    {
        // update title
        if (array_key_exists('title', $data)) {
            $webform->set('title', $data['title']);
            $node->set('title', $data['title']);
        }

        // update node description
        if (array_key_exists('description', $data)) {
            $webform->set('description', $data['description']);
            $node->set('body', $data['description']);
        }
        $tmp_webform = [];
        $tmp_webform['target_id'] = $webform->id();
        // update webform status
        if (array_key_exists('status', $data)) {
            //$status = $data['status'] === TRUE ? Webform::STATUS_OPEN : Webform::STATUS_CLOSED;
            $webform->setStatus($data['status']);
            $tmp_webform['status'] = $data['status'];
        }

        //update webform time
        if (array_key_exists('open_time', $data)) {
            $otime = date('Y-m-d\TH:i:s', substr($data['open_time'], 0, 10));
            $webform->set('open', $otime);
            $tmp_webform['open'] = $otime;
        }
        if (array_key_exists('close_time', $data)) {
            $ctime = date('Y-m-d\TH:i:s', substr($data['close_time'], 0, 10));
            $webform->set('close', $ctime);
            $tmp_webform['close'] = $ctime;
        }
        // update webform elements
        if (array_key_exists('elements', $data)) {
            _webform_node_rest_with_cover_list_decode($data['elements'], $elements);
            $webform->setElements($elements);
        }
        $webform->save();
        $node->webform->setValue($tmp_webform);
        $node->save();
    }

}
