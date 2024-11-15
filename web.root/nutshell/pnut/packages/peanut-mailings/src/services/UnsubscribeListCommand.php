<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 3/1/2019
 * Time: 9:50 AM
 */

namespace Peanut\mailings\services;


use Peanut\contacts\db\model\ISubscriptionsManager;
use Peanut\mailings\services\SubscriptionsManager;
use Tops\services\TServiceCommand;
use Tops\sys\TConfiguration;
use Tops\sys\TL;
use Tops\sys\TLanguage;
use Tops\sys\TObjectContainer;

class UnsubscribeListCommand extends TServiceCommand
{

    protected function run()
    {
        $request = $this->getRequest();
        $response = new \stdClass();

        $response->translations = TLanguage::getTranslations([
            'manage-subscriptions'
        ]);
        $response->subscriptionsLink = TConfiguration::getValue('subscriptionsUrl','pages');
        $response->message = TLanguage::text('unsubscribe-failed');


        if (empty($request)) {
            $this->addErrorMessage('service-no-request');
            return;
        }
        if (empty($request->uid)) {
            $this->addErrorMessage('Error, no person uid');
            return;
        }
        if (empty($request->listId)) {
            $this->addErrorMessage('Error no list id');
            return;
        }

        /**
         * @var $subscriptionManager ISubscriptionsManager
         */
        $subscriptionManager = TObjectContainer::Get('peanut.emailingsmanager');
        // $subscriptionManager = new SubscriptionsManager();
        $result = $subscriptionManager->unsubscribeEmail($request->uid,$request->listId);
        if (empty($result->personName)) {
            $this->addErrorMessage('dir-no-person-found');
            return;
        }
        if (empty($result->listName)) {
            $this->addErrorMessage('unsubscribe-error-no-list');
            return;
        }

        if (empty($result->removed)) {
            $format = TLanguage::text('already-unsubscribed-message');
        }
        else {
            $format = TLanguage::text('unsubscribed-message');
        }
        $response->message = sprintf($format,$result->personName, $result->listName);
        $this->setReturnValue($response);
    }
}