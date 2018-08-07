<?php
/**
 * Created by PhpStorm.
 * User: xxt
 * Date: 18-6-19
 * Time: 上午10:05
 */

namespace Drupal\webform_rest_resource\Controller;

use Drupal;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\Response;

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
        $result = [];
        $result['solution'] = $solution;
        //--------------------------------------
        // 就是第一次访问
        if (empty($_SESSION['drupal']['captcha_sid'])) {
            $user = \Drupal::currentUser();
            // Insert an entry and thankfully receive the value
            // of the autoincrement field 'csid'.
            $captcha_sid = db_insert('captcha_sessions')
                ->fields([
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
            $captcha_token = md5(mt_rand());
            db_update('captcha_sessions')
                ->fields(['token' => $captcha_token])
                ->condition('csid', $captcha_sid)
                ->execute();
        } else {
            _captcha_update_captcha_session($_SESSION['drupal']['captcha_sid'], $solution);
            $captcha_sid = $_SESSION['drupal']['captcha_sid'];
        }
        if (!$is_response) {
            return Url::fromRoute('image_captcha.generator', [
                'session_id' => $captcha_sid,
                'timestamp' => REQUEST_TIME
            ])->toString();
        }
        else {
            return new Response(Url::fromRoute('image_captcha.generator', [
                'session_id' => $captcha_sid,
                'timestamp' => REQUEST_TIME
            ])->toString());
        }
    }

    public static function check($captcha)
    {
        if (strtolower($captcha) != strtolower($_SESSION['drupal']['captcha_solution'])) return false;
        else return true;
    }
}

