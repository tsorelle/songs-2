<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 3/18/2019
 * Time: 12:25 PM
 */

namespace Tops\mail;


use Tops\sys\TWebSite;

abstract class TMailgunWebhookHandler
{
    private function handleError($error, $hookRequest = null)
    {
        // todo: log this maybe?
        $message = "$error";
        if ($hookRequest) {
            $message .= "\nRequest:\n" . var_export($hookRequest, true);
        }
        print "Mail hook error: $message\n";
        mail('webadmin@austinquakers.org', 'Error in Mailgun webhook', $message);
    }

    private function verify($signature, $apiKey)
    {
        // check if the timestamp is fresh
        if (abs(time() - $signature->timestamp) > 15) {
            return false;
        }

        // returns true if signature is valid
        return hash_hmac('sha256', $signature->timestamp . $signature->token, $apiKey) === $signature->signature;
    }

    /**
     * @throws \Exception
     */
    public function handleMessage()
    {
        header('X-PHP-Response-Code: 200', true, 200);
        try {
            $mailgunSettings = TMailgunConfiguration::GetSettings();
            if (!$mailgunSettings->valid) {
                throw new \Exception('Mailgun settings not found');
            }
            if (empty($mailgunSettings->apikey)) {
                throw new \Exception('No apikey in mailgun settings');
            }

            $hookRequest = new \stdClass();

            $request_body = file_get_contents('php://input');
            $payload = json_decode($request_body);
            // var_dump($payload);
            $signature = $payload->signature;
            // testing:
            // $verified = $this->verify($signature,$mailgunSettings->apikey);
            // $hookRequest->verified = $verified ? 'Yes' : 'No';

            if (!$this->verify($signature, $mailgunSettings->apikey)) {
                // todo: might be attack, consider strategies.
                $this->handleError("Unverified message", $hookRequest);
                return;
            }

            $eventdata = $payload->{'event-data'};

            $sender = @$eventdata->envelope->sender;
            @list($senderAddress,$senderDomain) = explode('@',$sender);
            $siteDomain = TWebSite::GetDomain();
            $fromUs = strcasecmp ($senderDomain,$siteDomain) == 0;
/*
            $isFromUs = $fromUs ? 'From us' : 'From other';
            $debugInfo = "from: $sender; \nSender domain: $senderDomain;\nSite domain:$siteDomain\nOrigin: $isFromUs\n";
            mail('terry.sorelle@outlook.com','Web hook',$debugInfo);
*/

            // ignore if message did not originate from website.
            // messages recieved via Mailgun lists can cause false bounce reports.
            if ($fromUs) {
                $status = $eventdata->{'delivery-status'};
                // $hookRequest->eventdata    = $eventdata;
                $hookRequest->recipient = @$eventdata->recipient;
                $hookRequest->description = empty($status->description) ? @$status->message : $status->description;
                $hookRequest->event = $eventdata->event;
                $hookRequest->errorLevel = @$eventdata->severity == 'temporary' ? 2 : 1;

                $this->handleEvent($hookRequest);
            }

        } catch (\Exception $ex) {
            $error = $ex->getMessage() . ' ' . "(" . $ex->getCode() . ': ' . $ex->getFile() . ' @ ' . $ex->getLine() . "\n" . $ex->getTraceAsString();
            $this->handleError($error);
            throw $ex;
        }
        exit;
    }

    public abstract function handleEvent($hookRequest);

}