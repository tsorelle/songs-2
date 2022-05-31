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
use mysql_xdevapi\Exception;
use Tops\concrete5\Concrete5Mailer;
use Tops\mail\TMailgunMailer;

class MailgunTest extends MailerTest
{

    protected function getProvider()
    {
        if (!class_exists('Tops\mail\TMailgunMailer')) {
            exit('Mailgun not loaded.');
        }
        return new TMailgunMailer();
    }
}