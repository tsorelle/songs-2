<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 9/12/2017
 * Time: 10:01 AM
 */

namespace PeanutTest\scripts;


use Peanut\QnutDocuments\db\model\entity\Document;
use Peanut\QnutDocuments\DocumentManager;
use Tops\db\TQuery;

class ImportdocfilesTest extends TestScript
{
    /**
     * @var TQuery
     */
    private $query;

    private $srcRootPath = '/home1/austinqu/public_html/friends/are/';
    private $targetRootPath = '/home1/austinqu/public_html/staging.root/application/documents/';
    private $added = [];
    private $skipped = [];


    private $supported  = [
        'xls',
        'xlsx',
        'ppt',
        'pptx',
        'doc',
        'docx'
    ];

    private $converted = true;
    private $addExisting = true;
    private $importId = 0;

    /**
     * @throws \Exception
     */
    public function execute()
    {
        exit('this script is disabled');
        try {
            $this->processImportTable();
        }
        catch (\Exception $ex) {
            // print "EXCEPTION on line $this->importId";
            throw $ex;
        }

        print "\n---- Added ------------------------------------------\n";
        print implode("\n",$this->added);

        print "\n---- Skipped ------------------------------------------\n";
        print implode("\n",$this->skipped);

        print "\n\ndone\n";
    }

    private function processImportTable()
    {
        $this->importId++;
        $test = 0;
        $count = 0;
        $manager = new DocumentManager();
        $docs = [
//            'ListeningSpiritualitySeriesPlan.pdf' => 'Listening Spirituality Series Plan',
            'ListeningSpiritualitySeriesNotes081008.pdf' => 'Listening Spirituality notes Aug 10 2008',
            'ListeningSpiritualitySeriesNotes081708.pdf' => 'Listening Spirituality Series notes Aug 17 2008',
            'ListeningSpiritualitySeriesChpt1Handout.pdf' => 'Listening Spirituality Chapter 1 Handout',
            'ListeningLoringFJAug97.pdf' => 'The Centrality of Listening by Patricia Loring',
            'ListeningSpiritualitySeriesChpt2Handout.pdf' => 'Listening Spirituality Chapter 2 Handout',
            'ListeningSpiritualityTheExamen.pdf' => 'Listening Spirituality Focus questions',
            'ListeningSpiritualitySeriesChpt3Handout.pdf' => 'Listening Spirituality Chapter 3 Handout',
            'ListeningSpiritualitySeriesChpt4Handout.pdf' => 'Listening Spirituality Chapter 4 Handout',
            'ListeningSpiritualitySeriesChpt4SufiFollowup.pdf' => 'Listening Spirituality: Sufi Practices',
            'ListeningSpiritualitySeriesChpt6Handout.pdf' => 'Listening Spirituality Chapter 6 Handout'
        ];
        foreach ($docs as $filename => $title) {
            $document = new Document;
            $document->title = $title;
            $document->folder = 'are/loring';
            $document->filename = $filename;
            $document->abstract = 'Listening Spirituality';
            $document->active = 1;
            $document->protected = 1;
            $document->publicationDate = '2008-01-01';
            $document->createdby = 'admin';
            $document->changedby = 'admin';
            // $document->changedon =
            // $document->createdon = $doc->uploadDate;

            $properties = [
                'status' => 4
            ];
            $properties['doctype'] = 10;

            $committees = [10];
            if ($test) {
                var_dump($document);
                print "\n";
                print_r($committees);
                print "\n";
                print_r($properties);
                print "\n";
            } else {
                if ($this->copyDocument($document->folder, $this->srcRootPath, $filename)) {
                    if ($manager->updateDocument($document, $properties, $committees,[],'admin')) {
                        $this->add("$document->folder/$document->filename");
                    } else {
                        exit;
                    }
                }
            }
            if ($test) {
                if ($count > $test) {
                    break;
                }
            }
        }
    }

    private function add($message) {
        $this->added[] = sprintf("Added  %04d: %s",$this->importId, $message);
    }

    private function skip($message) {
        $this->skipped[] = sprintf("Skipped %04d: %s",$this->importId, $message);
    }

    private function extToPdf($s) {
        if (strpos($s,'.') === false) {
            return "$s.pdf";
        }
        $parts =  explode('.',$s);
        array_pop($parts);
        return implode('.',$parts).'.pdf';
    }


    private function copyDocument($folder,$filepath,$filename)
    {
        $src =  $this->srcRootPath. $filename;
        $target = $this->targetRootPath . $folder . '/' . $filename;

//        if (!in_array($folder, $this->folders)) {
//            mkdir($targetPath, 0777, true);
//            $folders[] = $folder;
//        }

        if (file_exists($target)) {
            if ($this->addExisting) {
                return true;
            }
            $this->skip("File exists: " . $folder . '/' . $filename);
            return false;
        }

        if (@copy($src, $target)) {
            return true;
        }
        $this->skip("Copy failed: Source: $src; Target: $target;");
        return false;
    }
}