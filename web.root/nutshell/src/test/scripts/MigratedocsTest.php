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

class MigratedocsTest extends TestScript
{
    /**
     * @var TQuery
     */
    private $query;
    private $srcRootPath = '/home1/austinqu/public_html/austinquakers.net.root/';
    private $targetRootPath = '/home1/austinqu/public_html/testing.root/application/documents/';
    private $convertedSrcRootPath = '/home1/austinqu/deploy/tmp/converted';
    private $folders = [];
    private $importId = 0;
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


    /**
     * @throws \Exception
     */
    public function execute()
    {
        exit('disabled');
        try {
            $this->processImportTable();
        }
        catch (\Exception $ex) {
            print "EXCEPTION on line $this->importId";
            throw $ex;
        }

        print "\n---- Added ------------------------------------------\n";
        print implode("\n",$this->added);

        print "\n---- Skipped ------------------------------------------\n";
        print implode("\n",$this->skipped);

        print "\n\ndone\n";
    }

    private function processImportTable() {
        $test = 0;
        $count = 0;
        $startId = 0;
        $started = ($startId < 1);
        $this->query = new TQuery();
        $stmt = $this->query->executeStatement('select * from migrate_documents');
        $docs = $stmt->fetchAll(\PDO::FETCH_OBJ);
        $manager = new DocumentManager();
        foreach ($docs as $doc) {
            $this->importId = $doc->id;
            $path = strtolower($doc->filepath);
            $ext = pathinfo($path, PATHINFO_EXTENSION);
            $parts = explode('/', $path);
            $folder = count($parts) > 5 ? $parts[4] : 'archive';
            if ($this->converted) {
                if ($ext == 'pdf') {
                    continue;
                }
                if (!in_array($ext,$this->supported)) {
                    $this->skip("Unsupported: $doc->filename");
                    continue;
                };
                $ext = 'pdf';
                $doc->filename = $this->extToPdf($doc->filename);
                $doc->fileSourcePath = "$this->convertedSrcRootPath/$doc->filename";
            }
            else {
                $doc->fileSourcePath = $this->srcRootPath.$doc->filepath;
            }

            if (!file_exists($doc->fileSourcePath)) {
                $this->skip("Not in source dir: $doc->fileSourcePath");
                continue;
            }
            $count++;


//            if ($this->importId == $startId) {
//                $started = true;
//                continue;
//            }
//            if (!$started) {
//                continue;
//            }
            if ($ext === 'pdf') {
                $document = new Document;
                $document->title = $doc->title;
                $document->folder = $folder;
                $document->filename = $doc->filename;
                $document->abstract = $doc->description;
                $document->active = 1;
                $document->protected = 1;
                $document->publicationDate = $doc->publicationDate;
                $document->createdby = 'admin';
                $document->changedby = 'admin';
                $document->changedon = $doc->uploadDate;
                $document->createdon = $doc->uploadDate;

                $properties = [
                    'status' => 4
                ];
                if ($doc->typeId) {
                    $properties['doctype'] = $doc->typeId;
                }

                $committees = [];
                $stmt2 = $this->query->executeStatement('select committeeId from migrate_document_committees where fileId = ?', [$doc->fileId]);
                $cmtes = $stmt2->fetchAll(\PDO::FETCH_OBJ);
                foreach ($cmtes as $c) {
                    $committees[] = $c->committeeId;
                }
                if ($test) {
                    var_dump($document);
                    print "\n";
                    print_r($committees);
                    print "\n";
                    print_r($properties);
                    print "\n";
                }
                else {
                    if ($this->copyDocument($folder, $doc->fileSourcePath,$doc->filename)) {
                        if ($manager->updateDocument($document, $properties, $committees, 'admin')) {
                            $this->add("$document->folder/$document->filename");
                        }
                        else {
                            exit;
                        }
                    }
                }
            }
            else {
                $folder = 'draft';
                $this->copyDocument($folder, $doc->filepath, $doc->filename);
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
        $src = $filepath;
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