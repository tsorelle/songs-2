<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 10/23/2017
 * Time: 7:22 AM
 */

namespace Peanut\Mailboxes\services;


use Tops\db\model\repository\MailboxesRepository;
use Tops\mail\TContentType;
use Tops\mail\TPostOffice;
use Tops\services\TServiceCommand;
use Tops\sys\TConfiguration;
use Tops\sys\TLanguage;

/**
 * Class SendContactMessageCommand
 * @package Peanut\Mailboxes\services
 *
 * reguest:
 *     export interface IMailMessage {
 *         mailboxCode: string;
 *         fromName : string;
 *         fromAddress : string;
 *         subject : string;
 *         body : string;
 *      }
 */
class SendContactMessageCommand extends TServiceCommand
{

    /**
     * @throws \Exception
     */
    protected function run()
    {
        $message = $this->getRequest();

        $sender = TPostOffice::GetMailboxAddress('contact-form');
        if ($sender === false ) {
            $this->addErrorMessage('Contact form address not found.');
            return;
        }

        $header = TLanguage::text('mailbox-contact-header');
        $contentType = TConfiguration::getBoolean('contact-message-html','mail',true) ?
            TContentType::Html : TContentType::Text;
        if ($contentType   == TContentType::Html) {
            $message->body = str_replace("\n",'<br>',$message->body);
            $body = sprintf('<p>%s<br><a href="mailto:%s">%s (%s)</a><hr><p>%s<p>',
                $header,$message->fromAddress,$message->fromName,$message->fromAddress,$message->body);
        }
        else {
            $body =
                sprintf("%s\n%s (%s)\n\n---------------\n%s",
                    $header,$message->fromName,$message->fromAddress,$message->body);
        }

        $fromAddress = "$message->fromName <$message->fromAddress>";
        TPostOffice::SendMessageToUs(
            $fromAddress,
            $message->subject,$body,$contentType,
            $message->mailboxCode, // recipient
            TPostOffice::ContactMailbox // sender
        );
    }
}