<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 3/9/2019
 * Time: 4:56 AM
 */

namespace Tops\mail;


use Tops\sys\TConfiguration;
use Tops\sys\TIniSettings;
use Tops\sys\TWebSite;

class TMailgunConfiguration
{
    /**
     * @var TMailgunConfiguration
     */
    private static $instance;

    public $valid;
    public $domain;
    public $apikey;
    public $validationKey;
    public $error;
    public $sendEnabled;
    public $validationsEnabled;
    public $smtpEnabled;
    public $smtpUser;
    public $smtpPwd;
    public $options;

    public static function GetSettings()
    {
        if (!isset(self::$instance)) {
            $config = self::readIni();
            if (!$config->valid) {
                return $config; // try again later
            }
            self::$instance =  $config;
        }
        return self::$instance;
    }

    /**
     * @return TMailgunConfiguration
     */
    private static function readIni() {
        $result = new TMailgunConfiguration;
        $result->options = [];
        $result->valid = false;
        if (!class_exists('Mailgun\Mailgun')) {
            $result->error = 'Mailgun API is not installed.';
            return $result;
        }

        $config = TIniSettings::Create('mailgun.ini');
        if (!$config) {
            $result->error = 'Mailgun settings not found';
            return $result;
        }

        $result->sendEnabled = $config->getBoolean('settings','send',true);
        $result->validationEnabled = $config->getBoolean('settings','validate',true);

        $value = $config->getValue('domain','settings');
        if (!$value) {
            $result->error = 'No domain in Mailgun settings.';
            return $result;
        }
        $result->domain = $value;

        $apikey = $config->getValue('apikey','settings');
        $validationKey = $config->getValue('validationkey','settings');
        if (!($apikey || $validationKey)) {
            $result->error = 'No authentication keys in Mailgun settings';
            return $result;
        }
        $result->valid = true;
        if ($apikey)  {
            $result->apikey = $apikey;
            $result->sendEnabled = $config->getBoolean('send','settings',true);
        }
        if ($validationKey) {
            $result->validationKey = $validationKey;
            $result->validationsEnabled = $config->getBoolean('validate','settings',true);
        }

        $smtpUser = $config->getValue('smtp-user','settings');
        $smtpPwd  = $config->getValue('smtp-pwd','settings');
        if ($smtpUser && $smtpPwd) {
            $result->smtpUser = $smtpUser;
            $result->smtpPwd = $smtpPwd;
            $result->smtpEnabled = true;
        }

        $options = $config->getSection('options');
        $result->options = $options ? $options : [];

        return $result;
    }
}