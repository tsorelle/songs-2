<?php

namespace PeanutTest\scripts;

use Peanut\QnutDirectory\sys\MailTemplateManager;
use Tops\mail\TEmailAddress;
use Tops\mail\TPostOffice;
use Tops\sys\TTemplateManager;

class NotificationTest  extends TestScript
{

    public function execute()
    {
        $tokens = array(
            'name' => 'Terry SoRelle',
            'dates' => 'Formatted dates',
            'code' => 'CODE',
            'cost' => '100.00'
        );

        $templateManager = new MailTemplateManager();
        $template = $templateManager->getTemplateContent('registration-response.html');
        $messageText =  TTemplateManager::ReplaceContentTokens($template, $tokens);

        $message = TPostOffice::CreateMessageFromUs('registrar', 'SCYM Registration', $messageText);
        if ($message == null) {
            return; // in some unit test scenarios $message is not created. Ignore for these cases.
        }

        $message->setRecipient('websupport@scym.org');

        /**
         * @var $registrarAddress TEmailAddress
         */
        $registrarAddress = TPostOffice::GetMailboxAddress('registrar');
        if ($registrarAddress) {
            $message->addCC( $registrarAddress);
        }

        $sendResult = TPostOffice::Send($message);

        $this->assert($sendResult,'Send failed');

    }
}