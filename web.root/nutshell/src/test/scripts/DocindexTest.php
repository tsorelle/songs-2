<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 2/27/2019
 * Time: 9:24 AM
 */

namespace PeanutTest\scripts;


use Peanut\QnutDocuments\DocumentManager;

class DocindexTest extends TestScript
{

    public function execute()
    {
        $this->assert(class_exists('Smalot\PdfParser\Document'),'PDF Parser not installed');
        return;
        $client = new ConsoleMessageContainer();
        $manager = new DocumentManager();
        $doc = $manager->getDocument(14);
        $this->assert(!empty($doc),'Failed to load document');
        $result = $manager->indexDocument($doc,$client);
        $this->assert($result,'Indexing failed');
    }
}