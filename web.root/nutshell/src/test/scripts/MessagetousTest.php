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
use PHPMailer\PHPMailer\PHPMailer;
use Tops\concrete5\Concrete5Mailer;
use Tops\mail\TMailgunMailer;
use Tops\mail\TNullMailer;
use Tops\mail\TPhpMailer;
use Tops\sys\TConfiguration;

class MessagetousTest extends TestScript
{

    public function execute()
    {
        $mailer = new TMailgunMailer();
        $isMailgun = true;

        print "Testing message to us via mailgun ...\n";
        $serviceUse = TConfiguration::getBoolean('topsmailservice','mail',true) ? 'Using TOPS mail service' : 'Using default mail service';
        print "$serviceUse\n\n";
        $messageNumber = uniqid();
        $body =
        $message = \Tops\mail\TPostOffice::SendMessageToUs(
            'terry.sorelle@outlook.com',
            'Test to FMA '.$messageNumber,
            "<h1>Mailer test $messageNumber </h1>",'html',
            'contact-form',
            //'admin',
            'fma');

        // $message->addRecipient('terry.sorelle@outlook.com','Terry');
        // $message->addRecipient('terry.sorelle@gmail.com','Terry');
        // $message->addRecipient('testing.webhook.bounce@2quakers.net','Terry');
//        print "testing bounce.\n";
        /*
        if ($isMailgun) {
//        $message->setOption('tracking-clicks',false);
            $message->setOption('tracking',true);
            // $message->setDeliveryTime('+3 days');
            $message->addTag('tests');
            // $message->addTag('dogs');
        }
        */

        // $mailer->setSendEnabled(true);
/*        $actual = $mailer->send($message);
        $this->assert($actual === true,$actual);*/

        if ($isMailgun) {
            $log = TMailgunMailer::getSendLog();
            $last = array_pop($log);
            print "\nSend Log\n======================\n";
            var_dump($last);
        }

    }
}