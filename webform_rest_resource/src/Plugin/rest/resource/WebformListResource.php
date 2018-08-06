<?php

namespace Drupal\webform_rest_resource\Plugin\rest\resource;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\webform\Entity\Webform;
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
 *     "canonical" = "/api/webform"
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

    return $this->get_all_webform();
  }

    protected function get_all_webform()
    {
        $params = \Drupal::request()->query;
        $limit = $params->get('limit', 10);
        /**
         * $page int 0~unlimited
         */
        $page = $params->get('page', 0);
        /**
         * $status   'open'/ 'closed'
         */
        $status = $params->get('status', '');
        $search =  $params->get('search', '');
        $results = [];
        /**
         * 争取换用node或者其他迂回方式辅助进行查询、过滤？
         */
        $webforms = Webform::loadMultiple();
        foreach ($webforms as $title => $webform) {
            //return new ModifiedResourceResponse(strpos('contact', $search));
            if (($status==='' || $webform->get('status') === $status) && ($search==='' || is_numeric(strpos(strtolower($webform->get('title')), strtolower($search))))) {
                $elements = $webform->getElementsDecoded();
                webform_rest_list_encode($elements, $result_elements);
                $results[$title] = $result_elements;
            }
        }
        $chunk_result = array_chunk($results, $limit, true);
        $result_final = $chunk_result[$page];
        $total = count($result_final);

        $response = new ModifiedResourceResponse([
            'total' => (int)$total,
            'page_size' => (int)$limit,
            'page' => (int)$page,
            'list' => $result_final,
        ]);

        return $response;
    }


}
