<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 9/12/2017
 * Time: 10:01 AM
 */

namespace PeanutTest\scripts;


use Peanut\QnutCalendar\services\SendUserGroupNotificationsCommand;

class GroupnotifyTest extends TestScript
{

    public function execute()
    {
        $cmd = new SendUserGroupNotificationsCommand();
        $cmd->execute(null,false);
    }
}