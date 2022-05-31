<?php

namespace Peanut\contacts\services\messaging;

use Peanut\contacts\db\EmailManager;

/**
 *
 * Service contract
 *      Request: listId : int
 *      Response:
 *         array of interface IEmailSubscriber {
 *            listId: any;
 *            subscriber: string;
 *            subscriberId : any;
 *        }
 */
class GetEmailSubscribersListCommand extends \Tops\services\TServiceCommand
{
    protected function run()
    {
        $listId = $this->getRequest();
        if (empty($listId)) {
            $this->addErrorMessage('service-no-request');
            return;
        }
        if (!is_numeric($listId)) {
            $this->addErrorMessage('Invalid list id');
            return;
        }

        $manager = new EmailManager();
        $result = $manager->getSubscriberList($listId);
        $this->setReturnValue($result ?? []);
    }
}