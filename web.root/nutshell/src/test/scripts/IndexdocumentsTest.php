<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 2/27/2019
 * Time: 9:24 AM
 */

namespace PeanutTest\scripts;


use Peanut\QnutDocuments\DocumentManager;
use Tops\db\TQuery;

class IndexdocumentsTest extends TestScript
{

    public function execute()
    {
        $client = new ConsoleMessageContainer();
        $manager = new DocumentManager();
        $timeLimit = '30 minutes';
        $manager->indexDocuments($client,$timeLimit);
    }
}