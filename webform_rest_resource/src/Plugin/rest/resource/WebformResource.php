<?php

namespace Drupal\webform_rest_resource\Plugin\rest\resource;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\webform\Entity\Webform;
use Drupal\webform\WebformSubmissionForm;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "webform_resource",
 *   label = @Translation("Webform resource"),
 *   uri_paths = {
 *     "canonical" = "/api/webform/{id}",
 *     "https://www.drupal.org/link-relations/create" = "/api/webform"
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
        AccountProxyInterface $current_user)
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
            $container->get('current_user')
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
    public function get($id)
    {
        // You must to implement the logic of your REST Resource here.
        // Use current user after pass authentication to validate access.
        if (!$this->currentUser->hasPermission('access content')) {
            throw new AccessDeniedHttpException();
        }

        return $this->get_webform($id);
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
        $this->check_update($webform, $data);

        return new ModifiedResourceResponse($new_id, 201);
    }


    public function patch($id)
    {
        /** @var Webform $webform */
        $webform = Webform::load($id);

        if (empty($webform)) return new ModifiedResourceResponse('The ' . $id . 'was not found.', 404);

//        if ($webform->hasSubmissions()) return new ModifiedResourceResponse('The ' . $id . 'has submissions, can`t be updated.', 409);

        $raw = \Drupal::request()->getContent();
        $data = \GuzzleHttp\json_decode($raw, TRUE);

        $this->check_update($webform, $data);

        return $this->get_webform($id, true);
    }

    public function delete($id)
    {
        $webform = Webform::load($id);
        if (empty($webform)) {
            throw new NotFoundHttpException('The webform ' . $id . ' is not found.');
        }

        //
        if (!$this->currentUser->hasPermission('restful delete webform_resource')) {
            throw new AccessDeniedHttpException();
        }
        $webform->delete();
        /** @var Webform $webform */
        return new ModifiedResourceResponse(null, 204);
    }


    /**Helper Function
     * @param $id string    Webform ID(title)
     * @return ModifiedResourceResponse
     */
    protected function get_webform($id, $renew = false)
    {
        /** @var Webform $webform */
        $webform = Webform::load($id);
        if (!$webform) return new ModifiedResourceResponse('The Webform ' . $id . ' is not found.', 404);
        $elements = $webform->getElementsDecoded();
        $data = [
            'id' => $id,
            'title' => $webform->get('title'),
            'description' => $webform->getDescription(),
            'status' => $webform->get('status'),
            'open_time' => strtotime($webform->get('open'))*1000,
            'close_time' => strtotime($webform->get('close'))*1000,
            'elements' => []
        ];

        webform_rest_list_encode($elements, $data['elements'], $webform->id());

        $response = new ModifiedResourceResponse($data, $renew? 201 : 200);
        return $response;
    }

    protected function check_update(Webform &$webform, &$data)
    {
        // update title
        if (array_key_exists('title', $data)) $webform->set('title', $data['title']);

        // update node description
        if (array_key_exists('description', $data)) {
            $webform->set('description', $data['description']);
        }

        // update webform status
        if (array_key_exists('status', $data)) {
            //$status = $data['status'] === TRUE ? Webform::STATUS_OPEN : Webform::STATUS_CLOSED;
            $webform->setStatus($data['status']);
        }

        //update webform time
        if (array_key_exists('open_time', $data)) {
            $webform->set('open', date('Y-m-d\TH:i:s', substr($data['open_time'], 0, 10)));
        }
        if (array_key_exists('close_time', $data)) {
            $webform->set('close', date('Y-m-d\TH:i:s', substr($data['close_time'], 0, 10)));
        }
        // update webform elements
        if (array_key_exists('elements', $data)) {
            webform_rest_list_decode($data['elements'], $elements);
            $webform->setElements($elements);
        }
        $webform->save();
    }

}
