<?php
/**
 * Created by PhpStorm.
 * User: xxt
 * Date: 18-8-21
 * Time: 上午11:23
 */


$account = \Drupal\user\Entity\User::load('24467');
\Drupal::currentUser()->setAccount();