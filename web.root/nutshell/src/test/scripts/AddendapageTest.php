<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 9/12/2017
 * Time: 10:01 AM
 */

namespace PeanutTest\scripts;


use Peanut\QnutDocuments\db\model\entity\Document;
use Peanut\QnutDocuments\db\model\repository\DocumentsRepository;

class AddendapageTest extends TestScript
{

    public function execute()
    {
        $issueDate = '';
        $path = '/home1/austinqu/public_html/staging.root/application/deployment/addendapage.txt';
         // $path = 'D:\dev\fma\austinquakers.new\tmp\addendapage.txt';
        $lines = file($path);

        $items = [];
        $notfound = [];
        $repository = new DocumentsRepository();

        foreach ($lines as $line) {
            $line = trim($line);
            if (substr($line,0,4) == '<h3>') {
                $issueDate = $this->getIssueDate($line);
            }
            else if (substr($line,0,6) == '<file>') {
                $item = new \stdClass();
                $item->addendumDate = $issueDate;
                $line = substr($line, 6);
                @list($item->file, $item->title, $item->comment, $item->abstract) = explode('|', $line);
                if (strlen($item->comment) > 256) {
                    var_dump($item);
                    continue;
                }

                /**
                 * @var $doc Document
                 */
                $doc = null;
                if (is_numeric($item->file)) {

                    $doc = $repository->get($item->file);
                }
                else {
                    $doc = $repository->getByName($item->file);
                }

                if (!$doc) {
                    $notfound[] = $item->file;
                    $item->error = 'NOT FOUND';
                }
                else {
                    $item->currentTitle = $doc->title;
                    $doc->addendumType = 1;
                    $doc->addendumDate = $issueDate;
                    if ($item->comment) {
                        $doc->addendumComment = $item->comment;
                    }
                    if ($item->abstract) {
                        $doc->abstract = $item->abstract;
                    }
                    $repository->update($doc);

                }
                $items[] = $item;
            }
        }

        $errorcount = count($notfound);
        if ($errorcount == 0) {
        }
        else {
            print "$errorcount not found.";
            print_r($notfound);
        }
        print "\n*******************\n";
        var_dump($items);

    }

    private function getIssueDate($s) {
        $months = [
            'none',
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December'
        ];

        $s = substr($s,4);
        list($month,$year) = explode(' ',$s);
        $month = array_search($month,$months);
        return sprintf("%s-%'.02d-01",$year,$month);
    }
}