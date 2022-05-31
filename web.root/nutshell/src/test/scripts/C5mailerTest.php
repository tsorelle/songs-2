<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 3/12/2019
 * Time: 4:20 AM
 */

namespace PeanutTest\scripts;


use Tops\concrete5\Concrete5Mailer;
use Tops\mail\IMailer;
use Tops\mail\TPhpMailer;
use Tops\sys\TConfiguration;

class C5MailerTest extends MailerTest
{
    /**
     * @return IMailer
     * @throws \Exception
     */
    protected function getProvider()
    {
        return new Concrete5Mailer();
    }

}