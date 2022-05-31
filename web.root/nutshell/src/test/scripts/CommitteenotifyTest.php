<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 9/12/2017
 * Time: 10:01 AM
 */

namespace PeanutTest\scripts;


use Peanut\QnutCalendar\services\SendCommitteeNotificationsCommand;


class CommitteenotifyTest extends TestScript
{

    public function execute()
    {
        $cmd = new SendCommitteeNotificationsCommand();
        // $cmd->execute(null,false);
        $cmd->execute('2019-05-11',false);
    }
}