<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 9/12/2017
 * Time: 10:01 AM
 */

namespace PeanutTest\scripts;


use Peanut\QnutCalendar\services\SendCommitteeNotificationsCommand;
use Peanut\QnutCalendar\services\SendEventNotificationsCommand;
use Peanut\QnutDirectory\services\messaging\ProcessMessageQueueCommand;


class ProcessmessagequeueTest extends TestScript
{

    public function execute()
    {
        $cmd = new ProcessMessageQueueCommand();
        // $cmd->execute(null,false);
        $cmd->execute('2019-05-17',false);
    }
}