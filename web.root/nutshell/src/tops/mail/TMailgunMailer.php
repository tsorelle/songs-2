<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 3/6/2019
 * Time: 6:26 AM
 */

namespace Tops\mail;
use Mailgun\Mailgun;
use Tops\sys\TConfiguration;
use Tops\sys\TIniSettings;
use Tops\sys\TPath;
use Tops\sys\TWebSite;


class TMailgunMailer implements IMailer
{
    public static $SendLog = [];
    public static function getSendLog() {
        return self::$SendLog;
    }
    private function logSendResult($domain,$parameters,$result) {
        $entry = new \stdClass();
        $entry->domain = $domain;
        $entry->time = date(DATE_ISO8601);
        $entry->result = $result;

        if (array_key_exists('to',$parameters)) {
            $parameters['to'] = htmlspecialchars($parameters['to']);
        }
        if (array_key_exists('from',$parameters)) {
            $parameters['from'] = htmlspecialchars($parameters['from']);
        }
        $entry->parameters = $parameters;
        self::$SendLog[] = $entry;

        // debug
/*
        $to = @$parameters['to'] ?? 'No to address';
        if (is_string($result)) {
            mail('terry.sorelle@outlook.com','Message queue test -mailgun message log.',"$to:\n".$result);
        }
        else {
            mail('terry.sorelle@outlook.com','Message queue test -mailgun message log.',"$to:\n".(print_r($result,true)));
        }*/

    }

    public static $sendEnabled = true;
    private $settingsError;
    private $sendOptions;

    private function getSettings(TEMailMessage $message)
    {

        $mailgunSettings = TMailgunConfiguration::GetSettings();
        if (!$mailgunSettings->valid) {
            $this->settingsError = $mailgunSettings->error;
            return false;
        }
        if (empty($mailgunSettings->apikey)) {
            $this->settingsError = 'No apikey in mailgun settings';
            return false;
        }

        $this->sendOptions = [];
        $options = $mailgunSettings->options;
        $messageOptions = $message->getOptions();
        if ($options) {
            foreach ($options as $option => $value) {
                if (array_key_exists($option, $messageOptions)) {
                    $value = $messageOptions[$option];
                }
                if (!empty($value)) {
                    $this->sendOptions['o:' . $option] = true;
                }
            }
        }
        if ($mailgunSettings->sendEnabled !== true) {
            $this->setSendEnabled($mailgunSettings->sendEnabled);
        }
        return $mailgunSettings;
    }

    /**
     * @param TEMailMessage $message
     * @return bool | string
     *
     * Return true if successfull for error message e.g.
     * $result = $mailer->send($message);
     * if ($result !== true) {
     *      logError($result);
     * }
     */
    public function send(TEMailMessage $message)
    {
        if (!class_exists('Mailgun\Mailgun')) {
            return 'Mailgun API is not installed.';
        }
        $settings = $this->getSettings($message);
        if ($settings === false) {
            return $this->settingsError;
        }
        try {
            $mg = Mailgun::create($settings->apikey); // For US servers
            if (empty($mg)) {
                exit ("Failed to create message");
            }
            $sendProperties = $message->getSendProperties();
            if ($sendProperties === false) {
                return $message->getLastValidationError();
            }
           $parameters = [
                'from' => $sendProperties->from,
                'to' => $sendProperties->to,
                'subject' => $sendProperties->subject
            ];

            if (!empty($sendProperties->text)) {
                $parameters['text'] = $sendProperties->text;
            }
            if (!empty($sendProperties->html)) {
                $parameters['html'] = $sendProperties->html;
            }

            if (!empty($this->sendOptions)) {
                $parameters = array_merge($parameters, $this->sendOptions);
            }

            $tags = $message->getTags();
            if ($tags) {
                $parameters['o:tag'] = $tags;
            }
            $delivery = $message->getDeliveryTime();
            if ($delivery) {
                $parameters['o:deliverytime'] =  $delivery->format(\DateTime::RFC2822);
            }

            foreach ($message->getHeaders() as $key => $value) {
                $parameters["h:$key"] = $value;
            }

/*
            $dump = print_r($parameters,true);
            mail('terry.sorelle@outlook.com','Debug mail queue parameters',
                $dump);
*/
            /*

                        $dump = print_r($message,true);
                        mail('terry.sorelle@outlook.com','Debug mail queue message',
                            $dump);
            */

            /*
                        echo '<pre>';
                        echo "\n******\nsettings\n************\n";
                        var_dump($settings);
                        echo "\n******\nparameters\n************\n";
                        var_dump($parameters);
                        echo "\n***********************\n";
                        exit('end mail test');
            */
            $messages = $mg->messages();
            if (self::$sendEnabled && $settings->sendEnabled) {
                $result = $messages->send($settings->domain, $parameters);
                $this->logSendResult($settings->domain,$parameters,$result);
/*
                $dump = print_r($result,true);
                $params = print_r($parameters,true);
                mail('terry.sorelle@outlook.com','Message sent',"Message was sent:\n$dump\n\n$params");
*/
            }
            else {
                $this->logSendResult($settings->domain,$parameters,'send disabled');
                // mail('terry.sorelle@outlook.com','Debug mail queue message','sending was disabled. 3');
            }
        } catch (\Exception $ex) {
            // mail('terry.sorelle@outlook.com',$ex->getMessage(), $ex->getTraceAsString());
            $this->logSendResult($settings->domain,$parameters,'Exception: '.$ex->getMessage());
            return "Mailgun send failed: " . $ex->getMessage();
        }
        return true;
    }

    public function setSendEnabled($value = true)
    {
        self::$sendEnabled = $value;
    }
}