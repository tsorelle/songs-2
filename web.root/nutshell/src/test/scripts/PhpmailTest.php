<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 3/9/2019
 * Time: 5:46 PM
 */

namespace PeanutTest\scripts;


class PhpmailTest extends TestScript
{

    public function execute()
    {
        $result = mail('terry.sorelle@outlook.com','Test message','This is a test');
        $this->assert($result,'Mail not sent');
    }
}