<?php

namespace Drupal\the_webform_api_in_wechat\Controller;

use Drupal\mini_program\Controller\MpApi;
use Drupal\node\Entity\Node;
use Drupal\webform\Entity\Webform;
use Drupal\webform\Entity\WebformSubmission;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class WebformNodeApiController.
 */
class WebformNodeApiController extends MpApi
{


    public function getWebformApi($nid)
    {
        return $this->getWebform($nid);
    }
    
    public function getAllWebformApi()
    {
        return $this->getAllWebform();
    }
    /**
     * Getweb. Helper Function
     *
     * @return string
     *   Return Hello string.
     */
    public function getWebform($nid, $renew = false)
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
        _the_webform_api_in_wechat_build_form($data, $webform, $nid);
        $response = $this->responseJson($data, $renew ? 201 : 200);
        return $response;
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
        $category = $params->get('category');
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
        if (!empty($category)) {
            $query->leftJoin('node__webform_type', 'wt', 'wt.entity_id = n.nid');
            $category_query = \Drupal::entityTypeManager()
                ->getStorage('taxonomy_term')
                ->getQuery();
            $category_id = $category_query
                ->condition('name', $category)
                ->condition('vid', 'webform_type')
                ->execute();
            $category_id = array_values($category_id)[0];
            $query->condition('wt.webform_type_target_id', $category_id);
        }
        $query_clone = clone $query;
        $total = $query_clone->countQuery()->execute()->fetchField();
        $query->range($page*$limit, $limit);
        $webform_ids = $query->execute()->fetchCol(0);
        $node_ids = $query->execute()->fetchCol(1);
        $webforms = Webform::loadMultiple($webform_ids);
        $i = 0;
        foreach ($webforms as $id => $webform) {
            _the_webform_api_in_wechat_build_form($result_elements, $webform, $node_ids[$i++]);
            $results[$id] = $result_elements;
        }
        $response = $this->responseJson([
            'total' => (int)$total,
            'page_size' => (int)$limit,
            'page' => (int)$page,
            'list' => $results,
        ]);

        return $response;
    }

    /** 返回开屏调查问卷，根据$done确定是否显示
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getSelectedWebform()
    {
        $category_query = \Drupal::entityTypeManager()
            ->getStorage('taxonomy_term')
            ->getQuery();
        $category_id = $category_query
            ->condition('name', '开屏调研')
            ->condition('vid', 'webform_type')
            ->execute();
        $category_id = array_values($category_id)[0];

        $nid = 0;
        $done = false;
        $skey =  $this->getHttpHeader('x-wx-skey');
        $currentUser = $this->getAccountBySkey($skey);
        $db = \Drupal::database();
        $query = $db->select('node__webform_shown_page', 'nws');
        $query->leftJoin('node__webform_type', 'nwt', 'nwt.entity_id = nws.entity_id');
        $query->leftJoin('node_field_data', 'n', 'n.nid = nws.entity_id');
        // 去掉删除节点
        $query->condition('n._deleted', '0');
        // 选取开屏调研类型的webform
        $query->condition('nwt.webform_type_target_id', $category_id);
        $query->fields('nws', ['entity_id']);
        // 选取被指定为开屏的问卷
        $query->condition('webform_shown_page_value', 1);

        $nids = $query->execute()->fetchCol();

        if (empty($nids)) {
            $nid = null;
            $done = false;
        } else {
            $nid = array_values($nids)[0];
            $node = Node::load($nid);
            if (!$node) {
                throw new NotFoundHttpException('The node ' . $nid . ' was not found.');
            }
            $status = get_exact_value($node, 'webform', 'status');
            switch ($status) {
                case 'scheduled': {
                    $open = strtotime(get_exact_value($node, 'webform', 'open'));
                    $close = strtotime(get_exact_value($node, 'webform', 'close'));
                    if (time() < $open || time() > $close) {
                        $done = true;
                        $nid = null;
                    }
                    break;
                }
                case 'closed': {
                    $done = true;
                    $nid = null;
                    break;
                }
                case 'open': {
                    $db = \Drupal::database();
                    $query = $db->select('webform_submission', 'ws');
                    $query->fields('ws', ['sid']);
                    // 过滤出提交node的结果
                    $query->condition('entity_type', 'node');
                    // 过滤出目标node的提交
                    $query->condition('entity_id', $nid);
                    // 找出目标webform问卷
                    $query->condition('webform_id', get_exact_value($node, 'webform', 'target_id'));
                    // 找出目标人的提交结果
                    $query->condition('uid', $currentUser->id());
                    $sids = $query->execute()->fetchCol();
                    if (!empty($sids)) $done = true;
                }
            }
        }

        $result = [
            'uid' => $currentUser->id(),
            'nid' => $nid,
            'done' => $done
        ];
        return $this->responseJson($result);
    }

    public function getQRCode($nid)
    {
        $wechat_connector_storage = \Drupal::entityTypeManager()->getStorage('wechat_connector');
        /** @var \Drupal\wechat_mp\Entity\Connector $wechat_connector */
        $wechat_connector = $wechat_connector_storage->loadDefault();
        if ($token = $wechat_connector->getAccessToken()) {
            $url ='https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=' . $token;
            $options['http'] = [
                'method' => 'POST',
                'header' => 'Content-type: application/json',
                'content'=> json_encode([
                    'scene' => $nid,
                    'page' => 'pages/webformDetail/webformDetail'
                ])
            ];
            $context = stream_context_create($options);

            $qrcode = file_get_contents($url, false, $context);
        } else {
            return $this->responseJson('Error when trying to get token', 409);
        };
        $result = new Response($qrcode,200, ['Content-Type' => 'image/jpeg']);
        return $result;
    }


}

