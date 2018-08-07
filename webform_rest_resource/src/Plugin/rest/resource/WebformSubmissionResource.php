<?php

namespace Drupal\webform_rest_resource\Plugin\rest\resource;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\webform\Entity\Webform;
use Drupal\webform\Entity\WebformSubmission;
use Drupal\webform_rest_resource\Controller\CaptchaController;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "webform_submission_resource",
 *   label = @Translation("Webform submission resource"),
 *   uri_paths = {
 *     "canonical" = "/api/webform/submission/{sid}",
 *     "https://www.drupal.org/link-relations/create" = "/api/webform/{id}/submission"
 *   }
 * )
 */
class WebformSubmissionResource extends ResourceBase
{

    const DELAY_TIME = 60; // 两次提交时间间隔seconds
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
     * Constructs a new WebformSubmissionResource object.
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
     * @param \Drupal\Core\Entity\EntityInterface $entity
     *   The entity object.
     *
     * @return
     *   The HTTP response object.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     *   Throws exception expected.
     */
    public function get($sid)
    {

        // You must to implement the logic of your REST Resource here.
        // Use current user after pass authentication to validate access.
        if (!$this->currentUser->hasPermission('access content')) {
            throw new AccessDeniedHttpException();
        }

        $submission = WebformSubmission::load($sid);
        $sub_data = [];
        $basic_data = [];
        webform_rest_submission_data($sid, $submission, $sub_data, $basic_data);
        $result = array_merge($basic_data, $sub_data);
        if (empty($submission)) return new NotFoundHttpException('The submission '.$sid .' was not found.');
        return new ModifiedResourceResponse($result, 200);
    }

    public function post($id)
    {
        $webform = Webform::load($id);
        // 提交表格为空
        if (!$webform) return new ModifiedResourceResponse('The webform '. $id. ' was not found.', 404);
        // 提交表格已经关闭
        if ($webform->isClosed()) return new ModifiedResourceResponse('The webform '. $id . ' is closed.', 412);
        // 两次提交间隔过短
        if (isset($_SESSION['drupal']['webform_posted']) && time() - $_SESSION['drupal']['webform_posted'] < self::DELAY_TIME) {
            return new ModifiedResourceResponse('提交时间间隔过短，请' . (self::DELAY_TIME - (time() - $_SESSION['drupal']['webform_posted'])) . '秒后再提交', 412);
        }
        $input = \Drupal::request()->getContent();
        if ($input) $data['data'] = \GuzzleHttp\json_decode($input, true);
        else return new ModifiedResourceResponse('输入不能为空', 412);
        // 验证码错误
        if (!empty($webform->getElement('captcha')) && !CaptchaController::check($data['data']['captcha'])) return new ModifiedResourceResponse('验证码错误', 412);
        $data['webform_id'] = $id;
        $data['webform'] = $webform;
        WebformSubmission::create($data)->save();
        $_SESSION['drupal']['webform_posted'] = time();
        return new ModifiedResourceResponse('The webform submission in' . $id . 'has submitted successfully.', 201);
    }
}
