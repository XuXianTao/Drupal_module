<?php
/**
 * Created by PhpStorm.
 * User: xxt
 * Date: 18-6-19
 * Time: 上午10:05
 */

namespace Drupal\the_webform_api_in_wechat\Controller;

use Drupal;
use Drupal\Core\Render\HtmlResponse;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class CaptchaController
{

    public static function generate($is_response = true, $webform_id)
    {
        module_load_include('inc', 'captcha');
        //---------------------------------- Generate a CAPTCHA code.
        $config = \Drupal::config('image_captcha.settings');
        $allowed_chars = _image_captcha_utf8_split($config->get('image_captcha_image_allowed_chars'));
        $code_length = (int)$config->get('image_captcha_code_length');
        $solution = '';
        for ($i = 0; $i < $code_length; $i++) {
            $solution .= $allowed_chars[array_rand($allowed_chars)];
        }
        // Build the result to return.
        $_SESSION['drupal']['captcha_solution'] = $solution;
        //--------------------------------------
        // 就是第一次访问
        $have_exist = false;
        if (!empty($_SESSION['drupal']['captcha_sid'])) {
            $db = \Drupal::database();
            $query = $db->select('captcha_sessions')
                ->condition('csid', $_SESSION['drupal']['captcha_sid']);
            $have_exist = $query->countQuery()->execute()->fetchField()>0? true: false;
        }
        if (!$have_exist) {
            $user = \Drupal::currentUser();
            // Insert an entry and thankfully receive the value
            // of the autoincrement field 'csid'.
            $captcha_token = md5(mt_rand());
            $captcha_sid = db_insert('captcha_sessions')
                ->fields([
                    'token' => $captcha_token,
                    'uid' => $user->id(),
                    'sid' => session_id(),
                    'ip_address' => Drupal::request()->getClientIp(),
                    'timestamp' => REQUEST_TIME,
                    'form_id' => $webform_id,
                    'solution' => $solution,
                    'status' => CAPTCHA_STATUS_UNSOLVED,
                    'attempts' => 0,
                ])
                ->execute();
            $_SESSION['drupal']['captcha_sid'] = $captcha_sid;
        } else {
            _captcha_update_captcha_session($_SESSION['drupal']['captcha_sid'], $solution);
            $captcha_sid = $_SESSION['drupal']['captcha_sid'];
        }
        $captcha_url = Url::fromRoute('image_captcha.generator', [
            'session_id' => $captcha_sid,
            'timestamp' => REQUEST_TIME
        ])->setAbsolute(TRUE)->toString();
        if ($is_response) {
            $content = file_get_contents($captcha_url);
            return new Response($content, 200, ['content-type' => 'image/jpeg']);
        }
        return $captcha_url;
    }

    public static function check($captcha)
    {
        if (strtolower($captcha) != strtolower($_SESSION['drupal']['captcha_solution'])) return false;
        else return true;
    }
//
//    public function test()
//    {
//        $nid = 273;
//        $webform = Node::load($nid)->webform;
//        dump($webform);
//    }
}

