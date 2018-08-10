<?php

namespace Drupal\webform_node_rest_resource\Plugin\rest\resource;

use Drupal\Core\Database\Database;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\gwebform\Plugin\rest\WebformSubmissionListBuilderHelper;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\webform\Entity\Webform;
use http\Env\Response;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "webform_submission_list_resource",
 *   label = @Translation("Webform submission list resource"),
 *   uri_paths = {
 *     "canonical" = "/api/webform/{id}/submission"
 *   }
 * )
 */
class WebformSubmissionListResource extends ResourceBase
{

    /**
     * A current user instance.
     *
     * @var \Drupal\Core\Session\AccountProxyInterface
     */
    protected $currentUser;

    /**
     * @var \Drupal\webform\WebformSubmissionStorageInterface
     */
    protected $storage;
    /**
     * Constructs a new WebformSubmissionListResource object.
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
        $this->storage = \Drupal::entityTypeManager()->getStorage('webform_submission');
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
     * @param $id webform ID
     *
     * @return \Drupal\rest\ResourceResponse
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
        return $this->getAllWebformSubmission($id);
    }

    protected function getAllWebformSubmission($id) {
        $webform = Webform::load($id);
        if (empty($webform)) return new ModifiedResourceResponse('The ' . $id . 'was not found.', 404);
        $params = \Drupal::request()->query;
        $limit = $params->get('limit', 10);
        $page = $params->get('page', 0);
        $search = $params->get('search', '');
        $stick = $params->get('sticky');
        $locked = $params->get('locked');
        $in_draft = $params->get('in_draft');
        $only_id = $params->get('only_id');

        $results = [];
        /**
         * TODO
         */
        $query = $this->storage->getQuery();
        $query->condition('webform_id', $id);
        //copy from WebformSubmissionListBuilder.php getQuery()
        if ($search) {
            /**
             * @var $sub_query \Drupal\Core\Database\Query\AlterableInterface
             */
            $sub_query = Database::getConnection()->select('webform_submission_data', 'sd')
                ->fields('sd', ['sid'])
                ->condition('value', '%' . $search . '%', 'LIKE');
            $this->storage->addQueryConditions($sub_query, $webform);
            // Search UUID and Notes.
            $or_condition = $query->orConditionGroup();
            $or_condition->condition('notes', '%' . $search . '%', 'LIKE');
            // Only search UUID if keys is alphanumeric with dashes.
            // @see Issue #2978420: Error SQL with accent mark submissions filter.
            if (preg_match('/^[0-9a-z-]+$/', $search)) {
                $or_condition->condition('uuid', $search);
            }
            $query->condition(
                $query->orConditionGroup()
                    ->condition('sid', $sub_query, 'IN')
                    ->condition($or_condition)
            );
        }
        if ($stick) {
            $query->condition('sticky', $stick=='true'?1:0);
        }
        if ($locked) {
            $query->condition('locked', $locked=='true'?1:0);
        }
        if ($in_draft) {
            $query->condition('in_draft', $in_draft=='true'?1:0);
        }
        if ($limit) {$query->pager($limit);}
        if ($page) {$query->range($page*$limit, $limit);}
        $query_result = $query->execute();
        /**
         * @var $submissions \Drupal\webform\WebformSubmissionInterface[]
         */
        $submissions = $this->storage->loadMultiple($query_result);
        foreach ($submissions as $k => $submission) {
            $sub_data = [];
            $basic_data = [];
            _webform_node_rest_resource_submission_data($k, $submission, $sub_data, $basic_data);
            if ($only_id && $only_id=='true') {
                array_push($results, $basic_data);
            }
            else {
                $sub_data = array_merge($basic_data, $sub_data);
                array_push($results, $sub_data);
            }
        }

        $total = count($results);
        $response = new ModifiedResourceResponse([
            'total' => (int) $total,
            'page' => (int) $page,
            'open' => $webform->isOpen(),
            'start_time' => (string) strtotime($webform->get('open'))?:'',
            'end_time' => (string) strtotime($webform->get('close'))?:'',
            'list' => $results,
        ]);

        return $response;
    }

}
