<?php
/**
 * Created by PhpStorm.
 * User: xxt
 * Date: 18-6-19
 * Time: 上午10:05
 */

namespace Drupal\service_suggestion\Controller;

use Drupal\service_suggestion\Entity\Suggestion;
use Gregwar\Captcha\CaptchaBuilder;
use Symfony\Component\HttpFoundation\Response;

class CaptchaController
{
    protected $NUM;
    const NUM_RANDOM = 5;
    const DELAY_TIME = 7200; // 两次提交时间间隔seconds

    public function __construct()
    {
        $this->NUM = range(0,9);
        $this->NUM = array_merge($this->NUM, range('a','z'));
    }

    public function generate()
    {
        $builder = new CaptchaBuilder();
        $builder->build();
        $_SESSION['drupal']['captcha'] = $builder->getPhrase();
        return new Response($builder->inline());
    }

    public function check()
    {
        if (isset($_SESSION['drupal']['last_time']) && time()-$_SESSION['drupal']['last_time'] < self::DELAY_TIME) {
            return new Response(self::DELAY_TIME-(time()-$_SESSION['drupal']['last_time']));
        }
        if (strtolower($_GET['captcha'])!=strtolower($_SESSION['drupal']['captcha'])) return new Response('false');
        else return new Response('true');
    }

    public function add_suggestion()
    {
        $data = $_POST['data'];
        $data = json_decode($data);
        $data = object_array($data->data);
        if (strtolower($data['captcha']) == strtolower($_SESSION['drupal']['captcha'])) {
            $entity = Suggestion::create((array)$data['attributes']);
            $entity->save();
            $_SESSION['drupal']['last_time'] = time();
            return new Response('服务建议提交成功',200);
        } else {
            return new Response('验证码错误',403);
        }
    }

    public function get_rand() {
        return new Response($_SESSION['drupal']['captcha']);
    }
}

//PHP stdClass Object转array
function object_array($array) {
    if(is_object($array)) {
        $array = (array)$array;
    } if(is_array($array)) {
        foreach($array as $key=>$value) {
            $array[$key] = object_array($value);
        }
    }
    return $array;
}
