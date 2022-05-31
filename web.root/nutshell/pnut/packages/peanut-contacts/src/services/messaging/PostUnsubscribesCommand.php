<?php

namespace Peanut\contacts\services\messaging;

use Peanut\contacts\db\EmailManager;

class PostUnsubscribesCommand extends \Tops\services\TServiceCommand
{

    protected function run()
    {
        $request = $this->getRequest();
        if (empty($request)) {
            $this->addErrorMessage('service-no-request');
            return;
        }

        $listId = $request->listId ?? null;
        if (empty($listId)) {
            $this->addErrorMessage('No list id received');
            return;
        }
        if (!is_numeric($listId)) {
            $this->addErrorMessage('Invalid list id');
            return;
        }
        $unsubs = $request->unsubscribers ?? null;
        if (!is_array($unsubs)) {
            $this->addErrorMessage('No contact list received');
            return;
        }

        $manager = new EmailManager();
        $manager->unsubscribeMultiple($listId,$unsubs);

        $result = $manager->getSubscriberList($listId);
        $this->setReturnValue($result ?? []);
    }
}