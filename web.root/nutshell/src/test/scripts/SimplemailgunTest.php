<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 9/12/2017
 * Time: 10:01 AM
 */
namespace PeanutTest\scripts;

// require_once $_SERVER['DOCUMENT_ROOT'].'/../vendor/autoload.php';

use Mailgun\Mailgun;

class SimplemailgunTest extends TestScript
{


    private function sendTestMessage($to) {
        $messageNumber = uniqid();
        // $from = 'fmanotes@austinquakers.org';
        $from = 'fma@austinquakers.org';
        print "Testing mailgun send: $messageNumber ...\n";
        // $apikey = 'key-7f940e77cd9f2dddae9f36ba4d545976';

        try {
            $mg = Mailgun::create('key-7f940e77cd9f2dddae9f36ba4d545976'); // For US servers
            if (empty($mg)) {
                exit ("Failed to create message");
            }
            $parameters = [
                'from' => $from,
                'to' => $to,
                'subject' => "Mailgun test $messageNumber",
                'text' => "Test $messageNumber",
                'html' => "<h1>Test $messageNumber</h1>",
            ];

            $messages = $mg->messages();
            $result = $messages->send('austinquakers.org', $parameters);
            print "Message $messageNumber set to $to\n";
        } catch (\Exception $ex) {
            // mail('terry.sorelle@outlook.com',$ex->getMessage(), $ex->getTraceAsString());
            return "Mailgun send failed: " . $ex->getMessage();
        }

    }

    public function execute()
    {
        $this->sendTestMessage('webadmin@austinquakers.org');
        $this->sendTestMessage('admin@austinquakers.org');
        $this->sendTestMessage('batchlog@austinquakers.org');
        $this->sendTestMessage('billing@austinquakers.org');
        $this->sendTestMessage('calendar@austinquakers.org');

        // $this->sendTestMessage('terry.sorelle.fma@outlook.com');
//        $this->sendTestMessage('tls@2quakers.net');


    }
}