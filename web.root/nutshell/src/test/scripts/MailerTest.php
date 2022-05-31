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

class MailerTest extends TestScript
{

    protected function getProvider() {
        return new TNullMailer();
    }

    public function execute()
    {
        $mailer = $this->getProvider();
        $className = get_class($mailer);
        $provider = array_pop(explode('\\',$className));
        $isMailgun = $provider == 'TMailgunMailer';

        print "Testing $provider mailer ...\n";
        $serviceUse = TConfiguration::getBoolean('topsmailservice','mail',true) ? 'Using TOPS mail service' : 'Using default mail service';
        print "$serviceUse\n\n";
        $messageNumber = uniqid();
        $message = \Tops\mail\TPostOffice::CreateMessageFromUs('admin',
            "Testing  $provider mailer $messageNumber",
            "<h1>Mailer test $messageNumber </h1>".
            "<p>Mailer type: $className</p>".
            "<p>Service: $serviceUse</p>",
            'html');
        // $message->addRecipient('webadmin@austinquakers.org','Terry');
        // $message->addRecipient('tls@2quakers.net','Terry');
        // $message->addRecipient('terry.sorelle@outlook.com','Terry');
        $message->addRecipient('terry.sorelle@gmail.com','Terry');
        // $message->addRecipient('testing.webhook.bounce@2quakers.net','Terry');
//        print "testing bounce.\n";
        if ($isMailgun) {
//        $message->setOption('tracking-clicks',false);
            $message->setOption('tracking',true);
            // $message->setDeliveryTime('+3 days');
            $message->addTag('tests');
            // $message->addTag('dogs');
        }

        // $mailer->setSendEnabled(true);
        $actual = $mailer->send($message);
        $this->assert($actual === true,'SEND ERROR:'.$actual);

        if ($isMailgun) {
            $log = TMailgunMailer::getSendLog();
            $last = array_pop($log);
            print "\nSend Log\n======================\n";
            var_dump($last);
        }

    }
}