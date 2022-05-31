<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 9/12/2017
 * Time: 10:01 AM
 */

namespace PeanutTest\scripts;


class C5nativeemailTest extends TestScript
{

    public function execute()
    {
        $mailService = \Core::make('mail');
        $mailService->setTesting(true);
        $mailService->setSubject('A Test of CMS');
        $mailService->setBody("Hi terry this is a test.");
        $mailService->from('webclerk@austinquakers.org','FMA Web clerk');
        $mailService->to('terry.sorelle@outlook.com');
        $mailService->sendMail();



    }
}