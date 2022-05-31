<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 12/26/2017
 * Time: 4:25 AM
 */

namespace Peanut\contacts\services\messaging;


use Peanut\contacts\db\EMailQueue;
use Tops\services\TServiceCommand;

class ProcessMessageQueueCommand extends TServiceCommand
{

    protected function run()
    {
        if (!$this->getUser()->isAdmin()) {
            $this->addErrorMessage('Administrator permissions are required to run this service.');
            return;
        }
        $request = $this->getRequest();
        $sendlimit = @$request->sendLimit ?? 0;
        $count = EMailQueue::ProcessMessageQueue($sendlimit);
        $this->addInfoMessage("Sent $count messages.");
    }
}