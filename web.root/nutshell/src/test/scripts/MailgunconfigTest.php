<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 9/12/2017
 * Time: 10:01 AM
 */

namespace PeanutTest\scripts;


use Tops\mail\TMailgunConfiguration;

class MailgunconfigTest extends TestScript
{

    public function execute()
    {
        $mailgunSettings = TMailgunConfiguration::GetSettings();
        $actual = $mailgunSettings->sendEnabled;
        print $actual ? 'Enabled' : 'Disabled';
        $siteDomain = \Tops\sys\TWebSite::GetDomain();
        print " for $siteDomain\n";
        switch ($siteDomain) {
            case 'austinquakers.org' :
                $expected = true;
                break;
            case 'staging.austinquakers.org' :
                $expected = true;
                break;
            case 'testing.austinquakers.org' :
                $expected = false;
                break;
            case 'local.austinquakers.org' :
                $expected = true; // no effect since a different provider is in use.
                break;
            default :
                $expected = true;
        }
        $this->assert($actual === $expected,'Should be disabled');
    }
}