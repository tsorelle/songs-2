<?php

namespace Peanut\mailings\services;

use Peanut\mailings\db\model\repository\SubscriptionsRepository;
use Tops\services\TServiceCommand;

class ResubscribeCommand extends TServiceCommand
{

    protected function run()
    {
        $request = $this->getRequest();
        if (!$request->uid) {
            $this->addErrorMessage('Error: No UID');
        }
        // $repository = new EventregistrationsRepository();
        $repository = new SubscriptionsRepository();
        $repository->resubscribe($request->uid);
    }
}