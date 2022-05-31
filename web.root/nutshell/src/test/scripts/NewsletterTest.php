<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 2/21/2019
 * Time: 4:14 PM
 */

namespace PeanutTest\scripts;

include(__DIR__.DIRECTORY_SEPARATOR.'ConsoleMessageContainer.php');

use Peanut\QnutDocuments\db\model\entity\Document;
use Peanut\QnutDocuments\DocumentManager;
use Tops\sys\TDates;
use Tops\sys\TPath;

class NewsletterTest extends TestScript
{

    private function getMonthName() {

    }

    /**
     * @param $fileName
     *
     *     interface IDocumentRecord {
     *         id : any;
     *         title : string;
     *         filename : string;
     *         folder : string;
     *         abstract : string;
     *         protected : any;
     *         publicationDate : string;
     *         properties : Peanut.IKeyValuePair[];
     *         }
     */
    private function createDocument($fileName) {
        @list($ignore,$year,$month,$issue) = explode('-',$fileName);
        $issueNo = substr($year.$month.$issue,0,8);
        $pubdate = "$year-$month-01";
        $monthName = date('F Y',strtotime($pubdate));

        $result = new Document();
        $result->id = 0;
        $result->title = "Friendly Notes #$issueNo - $monthName";
        $result->filename = $fileName;
        $result->folder = 'newsletter';
        $result->abstract = "Friendly Notes monthly newsletter $monthName";
        $result->protected = 1;
        $result->publicationDate = $pubdate;
        return $result;
    }

    public function execute()
    {
        $messages = new ConsoleMessageContainer();
        $documentManager = new DocumentManager();

        $propertyValues = [
            'status' => 4,
            'doctype' => 8
        ];

        $path = TPath::fromFileRoot('application\documents\private\newsletter'); // 'D:\dev\fma\austinquakers.new\web.root\application\documents\private\newsletter';
        $files = scandir($path);
        foreach ($files as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            print $file;
            $document = $this->createDocument($file);
            $response = $documentManager->updateDocument($document,$propertyValues,'system');
            if ($response === false) {
                print " >> Error!! couldn't update.\n";
            }
            else {
                $documentManager->indexDocument($response,$messages);
                print " >> Ok\n";
            }
        }
    }
}