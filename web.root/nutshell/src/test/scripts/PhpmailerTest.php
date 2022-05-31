<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 3/12/2019
 * Time: 4:20 AM
 */

namespace PeanutTest\scripts;


use Tops\mail\TPhpMailer;

class PhpmailerTest extends MailerTest
{
    protected function getProvider()
    {
        return new TPhpMailer();
    }

}