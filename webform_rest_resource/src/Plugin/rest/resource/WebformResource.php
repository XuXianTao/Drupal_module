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
class WebformResource extends ResourceBase {

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
    AccountProxyInterface $current_user) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);

    $this->currentUser = $current_user;
    $this->storage = \Drupal::entityTypeManager()->getStorage('webform');
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
  public function get($id) {

    // You must to implement the logic of your REST Resource here.
    // Use current user after pass authentication to validate access.
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }

    return $this->getForm($id);
  }

    public function post()
    {
        $input = \Drupal::request()->getContent();
        $data = \GuzzleHttp\json_decode($input, true);

        if ($this->is_repeat($data['title'])) return new ModifiedResourceResponse('The title has been defined', 409);

        $webform = Webform::create([
            'id' => $data['title'],
            'title' => $data['title'],
        ]);
        // prepare elements, prepend '#' to element attribute key

        $result = [];
        webform_rest_list_decode($data['elements'], $result);
        $data['elements'] = $result;
        $webform->setElements($result);
        $webform->save();

        return new ModifiedResourceResponse($data, 201);
    }


    public function patch($id)
    {
        /** @var Webform $webform */
        $webform = Webform::load($id);

        if (empty($webform)) return new ModifiedResourceResponse('The ' . $id . 'was not found.', 404);

        if ($webform->hasSubmissions()) return new ModifiedResourceResponse('The ' . $id . 'has submissions, can`t be updated.', 409);

        $raw = \Drupal::request()->getContent();
        $data = \GuzzleHttp\json_decode($raw, TRUE);

        /**
         * 定义了新标题并且新标题重复了
         */
        if (array_key_exists('title', $data) && $data['title'] != $id && $this->is_repeat($data['title'])) return new ModifiedResourceResponse('The title has been defined', 409);

        /** update title
         * 如果出现新标题则直接删除旧webform，重新创建，避免id占用
         */
        if (array_key_exists('title', $data)) {
            if ($data['title'] != $id) {
                $webform->delete();
                $webform = Webform::create([
                    'id' => $data['title'],
                    'title' => $data['title']
                ]);
            }
        }

        // update webform elements
        webform_rest_list_decode($data['elements'], $elements);
        $webform->setElements($elements);

        // update webform status
        if (array_key_exists('open', $data)) {
            $status = $data['open'] === TRUE ? Webform::STATUS_OPEN : Webform::STATUS_CLOSED;
            $webform->setStatus($status);
        }

        // update node description
        if (array_key_exists('description', $data)) {
            $webform->set('description', $data['description']);
        }

        $webform->save();

        return new ModifiedResourceResponse($webform);
    }

    public function delete($id)
    {
        $webform = Webform::load($id);
        if (empty($webform)) {
            throw new NotFoundHttpException('The webform '. $id . ' is not found.');
        }

        //这里提示无效
        if (!$this->currentUser->hasPermission('restful delete group_webform')) {
            throw new AccessDeniedHttpException();
        }

        /** @var Webform $webform */
        $webform->delete();
        return new ModifiedResourceResponse('The webform ' . $id . ' deleted successfully.', 200);
    }



    /**Helper Function
     * @param $id string    Webform ID(title)
     * @return ModifiedResourceResponse
     */
    protected function getForm($id)
    {
        /** @var Webform $webform */
        $webform = Webform::load($id);
        if (!$webform) return new ModifiedResourceResponse('The Webform '. $id . ' is not found.', 404);
        $elements = $webform->getElementsDecoded();
        $data = [
            'title' => $id,
            'description' => $webform->getDescription(),
            'open' => FALSE,
            'message' => '',
            'elements' => []
        ];

        webform_rest_list_encode($elements, $data['elements']);

        $open_status = WebformSubmissionForm::isOpen($webform);
        if ($open_status === TRUE) {
            $data['open'] = TRUE;
        } else {
            $data['message'] = $open_status['#markup'];
        }

        $response = new ModifiedResourceResponse($data);
        return $response;
    }

    /**Helper Function
     * @param $title
     * @return array
     */
    protected function is_repeat($title)
    {
        $webforms_title = array_keys(Webform::loadMultiple());
        return preg_grep('/^' . $title . '$/', $webforms_title);
    }

}
