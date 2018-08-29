<?php
/**
 * Created by PhpStorm.
 * User: xxt
 * Date: 18-8-22
 * Time: 下午6:33
 */

namespace Drupal\the_webform_api_in_wechat\Controller;


use Drupal;
use Drupal\mini_program\Controller\MpApi;
use Drupal\node\Entity\Node;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\webform\Entity\Webform;
use Drupal\webform\Entity\WebformSubmission;

class WebformSubmissionController extends MpApi
{
    public function postSubmission($id)
    {
        $node = Node::load($id);
        $wid = get_exact_value($node, 'webform', 'target_id');
        $webform = Webform::load($wid);
        $db = Drupal::database();

        //提交是否达到上限
        $query = $db->select('webform_submission', 'ws')
            ->condition('webform_id', $wid);
        $has_submit = $query->countQuery()->execute()->fetchField();
        if (!empty($webform->getSetting('limit_total')) && $has_submit>=$webform->getSetting('limit_total')) {
            return $this->responseJson('The submissions have reached the upper limit. You can\'t submit now.', 412);
        }

        $skey =  $this->getHttpHeader('x-wx-skey');
        $currentUser = $this->getAccountBySkey($skey);
        // 判断是否已经提交过
        $query = $db->select('webform_submission', 'ws')
            ->condition('webform_id', $wid)
            ->condition('uid', $currentUser->id());
        $has_submit = $query->countQuery()->execute()->fetchField();
        if ($has_submit) {
            return $this->responseJson('You have submitted befored, don\'t have to submit again.', 412);
        }


        // 提交表格为空
        if (!$webform) return $this->responseJson('The webform '. $wid. ' was not found.', 404);
        // 提交表格已经关闭
        if ($webform->isClosed()) return $this->responseJson('The webform '. $wid . ' is closed.', 412);

        $input = \Drupal::request()->getContent();
        if ($input) $data['data'] = \GuzzleHttp\json_decode($input, true);
        else return $this->responseJson('输入不能为空', 412);
        // 验证码错误
        if (!empty($webform->getElement('captcha')) && !CaptchaController::check($data['data']['captcha'])) return $this->responseJson('验证码错误', 412);
        $data['webform_id'] = $wid;
        $data['webform'] = $webform;
        WebformSubmission::create($data)
            ->set('entity_type', 'node')
            ->set('entity_id', $id)
            ->set('uid', $currentUser->id())
            ->save();
        $_SESSION['drupal']['webform_posted'] = time();
        return $this->responseJson('The webform submission in' . $wid . 'has submitted successfully.', 201);
    }
}