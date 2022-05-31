<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 2/27/2019
 * Time: 7:44 AM
 */

namespace PeanutTest\scripts;


class ClassloaderTest extends TestScript
{

    public function execute()
    {
        $classes = [
            'Smalot\PdfParser\PDFObject',
            'Mailgun\Mailgun',
            'Tops\concrete5\TConcrete5User',
            'Peanut\sys\PeanutTranslator',
            'Tops\sys\TDates',
            'Peanut\Mailboxes\services\GetMailboxListCommand'
        ];

        foreach ($classes as $class) {
            $ok = class_exists($class);
            $this->assert($ok,$class.' not loaded');
        }
    }
}