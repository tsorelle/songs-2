<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 1/23/2015
 * Time: 2:56 PM
 */

namespace Tops\mail;

use Tops\sys\TConfiguration;
use Tops\sys\TObjectContainer;
// use Tops\mail\TContentType;

/**
 * Manages email operations
 * Class TPostOffice
 * @package Tops\sys
 */
class TPostOffice {
    // system mailbox names
    const AdminMailbox = 'admin';
    const BounceMailbox = 'bounce';
    const VendorMailbox = 'two-quakers-support';
    const ContactMailbox = 'contact-form';
    const SupportMailbox = 'support';
    const DefaultMailbox = 'admin';
    const DirectoryAdminMailbox = 'directory-admin';
    const CalendarMailbox = 'calendar';

    const SystemMailboxes = [
        self::AdminMailbox ,
        self::BounceMailbox,
        self::VendorMailbox,
        self::ContactMailbox,
        self::SupportMailbox
    ];


    public static $instance = null;
    public static $mailEnabled = true;

    /**
     * @return TPostOffice
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new TPostOffice();
        }
        return self::$instance;
    }

    public static function setInstance($instance)
    {
        self::$instance = $instance;
    }


    private $mailerClass;
    /**
     * @var IMailboxManager
     */
    private $mailboxes;

    /**
     * @return IMailer
     */
    private function getMailer()
    {
        if (isset($this->mailerClass)) {
            return new $this->mailerClass();
        }
        if (TObjectContainer::HasDefinition('tops.mailer')) {
            $mailer = TObjectContainer::Get('tops.mailer');
        }
        else {
            $mailer = new TNullMailer();
        }
        $this->mailerClass = get_class($mailer);
        $mailer->setSendEnabled(self::$mailEnabled);
        return $mailer;
    }

    public function __construct(IMailer $mailer = null, IMailboxManager $mailboxes = null) {
        if ($mailboxes == null) {
            if (TObjectContainer::HasDefinition('tops.mailboxes')) {
                $mailboxes = TObjectContainer::Get('tops.mailboxes');
            }
            else {
                $mailboxes = new TDbMailboxManager();
            }
        }
        $this->mailboxes = $mailboxes;
    }

    /**
     * @param string $recipientAddressId
     * @return TEMailMessage
     * @throws \Exception
     */
    public static function CreateMessageToUs($recipientAddressId=self::SupportMailbox)
    {
        return self::getInstance()->_createMessageToUs($recipientAddressId);
    }

    
    private $bounceAddress;
    public static function GetBounceAddress() {
        return self::getInstance()->_getBounceAddress();
    }
    public function _getBounceAddress() {
        if (!isset($this->bounceAddress)) {
            $this->bounceAddress = TConfiguration::getValue('mail','bounce',null);
        }
        return $this->bounceAddress;
    }

    /**
     * @param string $recipientAddressId
     * @return TEMailMessage
     * @throws \Exception
     */
    private function _createMessageToUs($recipientAddressId=self::SupportMailbox)
    {
        $result = new TEMailMessage();

        $recipients = explode(',',$recipientAddressId);
        $count = 0;
        foreach ($recipients as $addressId) {
            $mailbox = self::GetMailboxAddress($addressId);
            if ($mailbox != null) {
                $result->addRecipient($mailbox->getAddress(),$mailbox->getName());
                $count++;
            }
        }
        if ($count == 0) {
            throw new \Exception('No mailboxes found.');
        }

        $result->setReturnAddress($this->_getBounceAddress());

        return $result;
    }


    /**
     * @param $addressId
     * @return false|TEmailAddress
     */
    public static function GetMailboxAddress($addressId)
    {
        $mailbox = self::GetMailbox($addressId);
        if ($mailbox === false) {
            return false;
        }
        return new TEmailAddress($mailbox->getEmail(),$mailbox->getName());
    }
    
    public static function GetDefaultMailboxAddress() {
        return self::GetMailboxAddress(self::DefaultMailbox);
    }


    /**
     * @param $code
     * @return bool|IMailbox
     */
    public static function GetMailbox($code) {
        $repository = self::getInstance()->mailboxes;
        $mailbox = $repository->findByCode($code);
        if (empty($mailbox)) {
            return false;
        }
        return $mailbox;

    }

    public static function CreateMessageFromUs($addressId=self::SupportMailbox,$subject=null,$body=null,$contentType=TContentType::Text) {
        return self::getInstance()->_createMessageFromUs($addressId,$subject,$body,$contentType);
    }

    /**
     * @param string $addressId
     * @param null $subject
     * @param null $body
     * @param string $contentType -  'html' | 'text' |  text content part of multipart
     * @param null $bounce
     * @return bool|TEMailMessage
     */
    private function _createMessageFromUs($addressId=self::SupportMailbox,$subject=null,$body=null,$contentType=TContentType::Text,$bounce=null)
    {
        // TTracer::Trace("CreateMessageFromUs($addressId) address: $address; name: $identity");
        $result = new TEMailMessage();

        $mailbox = self::GetMailboxAddress($addressId);
        if (empty($mailbox)) {
            return false;
        }
        $result->setFromAddress($mailbox);
        if ($bounce===null) {
            $result->setReturnAddress($this->_getBounceAddress());
        }
        else {
            $result->setReturnAddress(self::GetMailboxAddress($bounce));
        }
        if (!empty($subject))
            $result->setSubject($subject);
        if (!empty($body)) {
            $result->setMessageBody($body,$contentType);
        }
        return $result;
    }  //  newEmailMessageFromUs


    public static function Send($message) {
        // TTracer::ShowArray($message);
        // TTracer::Trace('Send to: '.htmlentities($message->getRecipients()));
        return self::getInstance()->_send($message);
    }
    private function _send($message) {
        try {
            return $this->getMailer()->send($message);
        }
        catch(\Exception $ex) {
            return false;
        }
    }


    /**
     * @param $to
     * @param $senderId
     * @param $senderAlias
     * @param $subject
     * @param $bodyText
     * @param string $contentType -  'html' | 'text' |  text content part of multipart
     * @param null $replyTo
     * @param null $bounce
     * @return TEMailMessage
     */
    public static function CreateMessage($to, $senderId, $senderAlias, $subject, $content, $contentTypeOrTextPart=TContentType::Text, $replyTo=null,  $bounce = null)
    {
        //TTracer::Trace('SendMessage');
        return self::getInstance()->_createMessage($to, $senderId, $senderAlias, $subject, $content, $contentTypeOrTextPart, $replyTo, $bounce);
    }
    private function _createMessage($to, $senderId, $senderAlias, $subject, $content, $contentTypeOrTextPart=TContentType::Text, $replyTo=null, $bounce = null) {
        $message = new TEMailMessage();
        $message->setRecipient($to);
        if (empty($senderId)) {
            $senderId = self::DefaultMailbox;
        }
        if (strpos($senderId,'@') === false) {
            $senderAddress = self::GetMailboxAddress($senderId);
        }
        else {
            $senderAddress = TEmailAddress::FromString($senderId);
        }
        if (!empty($senderAlias)) {
            $senderAddress->setName($senderAlias);
        }
        $message->setFromAddress($senderAddress);
        $message->setSubject($subject);
        $message->setMessageBody($content,$contentTypeOrTextPart);
        if ($replyTo) {
            $message->setReplyTo($replyTo);
        }
        if ($bounce) {
            $message->setReturnAddress($bounce);
        }
        return $message;
    }


    /**
     * @param $to
     * @param $senderId
     * @param $senderAlias
     * @param $subject
     * @param $bodyText
     * @param string $contentType -  'html' | 'text' |  text content part of multipart
     * @param null $replyTo
     * @param null $bounce
     * @return bool|string
     */
    public static function SendMessage($to, $senderId, $senderAlias, $subject, $bodyText, $contentType=TContentType::Text, $replyTo=null,  $bounce = null)
    {
        return self::getInstance()->_sendMessage($to, $senderId, $senderAlias, $subject, $bodyText, $contentType, $replyTo, $bounce);
    }
    private function _sendMessage($to, $senderId, $senderAlias, $subject, $bodyText, $contentType=TContentType::Text, $replyTo=null, $bounce = null) {
        $message = $this->_createMessage($to, $senderId, $senderAlias, $subject, $bodyText, $contentType, $replyTo, $bounce);
        return $this->getMailer()->send($message);
    }

    /**
     * @param $fromAddress
     * @param $subject
     * @param $bodyText
     * @param string $senderId
     * @param string $addressId
     * @return bool|string
     * @throws \Exception
     */
    public static function SendMessageToUs($fromAddress, $subject, $bodyText, $contentType='html',
                                           $recipientAddressId=self::DefaultMailbox, $senderId=self::DefaultMailbox)
    {
        return self::getInstance()->_sendMessageToUs($fromAddress, $subject, $bodyText,$contentType,
            $recipientAddressId,$senderId);
    }

    /**
     * @param $fromAddress
     * @param $subject
     * @param $bodyText
     * @param string $senderId
     * @param string $addressId
     * @return bool|string
     * @throws \Exception
     */
    private function _sendMessageToUs($fromAddress, $subject, $bodyText, $contentType='html',
                                      $recipientAddressId=self::DefaultMailbox, $senderId=self::DefaultMailbox)
    {
        $message = $this->_createMessageToUs($recipientAddressId);
        $senderAddress = self::GetMailboxAddress($senderId);
        $message->setFromAddress($senderAddress);
        $message->setReplyTo($fromAddress);
        $message->setSubject($subject);
        $message->setMessageBody($bodyText,$contentType);
        return $this->getMailer()->send($message);
    }

    public static function SendTextMessageFromUs($recipients, $subject, $bodyText, $addressId=self::DefaultMailbox) {
        return self::getInstance()->_sendMessageFromUs($recipients, $subject, $bodyText, $addressId, TContentType::Text);
    }


    public static function SendMessageFromUs($recipients, $subject, $bodyText, $addressId=self::DefaultMailbox, $contentType=TContentType::Html ) {
        return self::getInstance()->_sendMessageFromUs($recipients, $subject, $bodyText, $addressId, $contentType);
    }

    private function _sendMessageFromUs($recipient, $subject, $bodyText, $addressId=self::DefaultMailbox, $contentType=TContentType::Html,$bounce=null  ) {
        // TTracer::Trace('SendMessageFromUs');
        $message = $this->_createMessageFromUs($addressId, $subject, $bodyText, $contentType,$bounce);
        $message->setRecipient($recipient);
        return $this->getMailer()->send($message);
    }

    public static function SendHtmlMessageFromUs($recipients, $subject, $bodyText, $addressId=self::SupportMailbox,$bounce=null ) {
        return self::getInstance()->_sendHtmlMessageFromUs($recipients, $subject, $bodyText, $addressId,$bounce);
    }
    private function _sendHtmlMessageFromUs($recipients, $subject, $bodyText, $addressId=self::SupportMailbox,$bounce=null ) {
        $message = $this->_createMessageFromUs($addressId, $subject, $bodyText,TContentType::Html,$bounce);
        $message->setRecipient($recipients);
        return $this->getMailer()->send($message);
    }

    public static function SendMultiPartMessageFromUs($recipients, $subject, $bodyText, $textPart, $addressId=self::SupportMailbox, $bounce=null ) {
        return self::getInstance()->_sendMultiPartMessageFromUs($recipients, $subject, $bodyText, $textPart, $addressId,$bounce);
    }
    private function _sendMultiPartMessageFromUs($recipients, $subject, $bodyText, $textPart, $addressId=self::SupportMailbox,$bounce=null ) {
        $message = $this->_createMessageFromUs($addressId, $subject, $bodyText, $textPart, $bounce);
        $message->setRecipient($recipients);
        return $this->getMailer()->send($message);
    }

    public static function disableSend() {
        self::$mailEnabled = false;
    }

    public static function GetMailboxManager() {
        return self::getInstance()->mailboxes;
    }

}