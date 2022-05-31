<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 9/12/2017
 * Time: 10:01 AM
 */

namespace PeanutTest\scripts;


use Application\mailgun\WebhookHandler;

class WebhookTest extends TestScript
{

    public function execute()
    {
        $request = new \stdClass();
        $request->recipient = 'buychicken@verizon.net';
        $request->description = 'test';
        $request->event = 'failed'; // 'failed' | 'complained'
        $request->errorLevel = 1; // : permanent=1, temporary=2
        $handler = new WebhookHandler();
        $handler->handleEvent($request);
    }
}