<?php
/**
 * Created by PhpStorm.
 * User: xxt
 * Date: 18-8-20
 * Time: 下午1:47
 */

namespace Drupal\webform_node_rest_with_cover\EventSubscriber;

use Drupal\Core\Session\AccountInterface;
use Drupal\user\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class CustomSubscriber implements EventSubscriberInterface {

    /**
     * Redirect pattern based url
     * @param GetResponseEvent $event
     */
    public function customRedirection() {
        $request = \Drupal::request();
        $requestUrl = $request->server->get('REQUEST_URI', null);
//        exit();
        /**
         * Here i am redirecting the about-us.html to respective /about-us node.
         * Here you can implement your logic and search the URL in the DB
         * and redirect them on the respective node.
         */
        if (strpos($requestUrl, '/api/webform')!==false) {
            // Call the account switcher service
//            $accountSwitcher = \Drupal::service('account_switcher');
//// Switch to the admin user
//            $session = new \Drupal\Core\Session\UserSession([
//                    'uid' => 1,
//                    'roles' => ['authenticated']
//                ]
//            );
//            $user = User::load(1);
//            $accountSwitcher->switchTo($user);
//            \Drupal::currentUser()->setAccount($session);
//
// Your Code Hear...
//
// Switch back to old session.
            //$accountSwitcher->switchBack();
            //dump($request);
            //$user = User::load(\Drupal::currentUser()->id());
            //dump(\Drupal::currentUser()->getAccount());
            //$account = User::load('24467')->
            //$account = \Drupal::entityTypeManager()->getStorage('');
        }
    }
    public function customRedirection2(){

    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        // TODO: Implement getSubscribedEvents() method.
        $events[KernelEvents::REQUEST][] = array('customRedirection', 299);
        return $events;
    }
}