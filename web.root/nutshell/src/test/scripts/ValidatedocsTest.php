<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 9/12/2017
 * Time: 10:01 AM
 */

namespace PeanutTest\scripts;


use Doctrine\ORM\EntityRepository;
use Peanut\QnutDocuments\db\model\entity\Document;
use Peanut\QnutDocuments\DocumentManager;
use Tops\db\TPdoQueryManager;
use Tops\db\TQuery;
use Tops\sys\TWebSite;

class ValidatedocsTest extends TestScript
{
    static $localDocPath = '/dev/scym2021/web.root/application/documents/';
    static $deploymentDocPath = '/home/scymorg/public_html/dev.scym.org/application/documents/';
    static $errorTable = 'migration_doc_validation';
    static $docTable = 'migration_documents';
    /**
     * @var TQuery
     */
    private $query;
    private $documentPath;
    private $document;
    private $errors = [];
    private $listedCount = 0;
    private $checkedCount = 0;
    private $verifiedCount = 0;

    private $supported  = [
        'xls',
        'xlsx',
        'ppt',
        'pptx',
        'doc',
        'docx'
    ];



    /**
     * @throws /Exception
     */
    public function execute()
    {
        // exit('disabled');
        //
        $env = TWebSite::GetDomain();
        $isLocal = explode('.',$env)[0] == 'local';
        $this->documentPath =
            $isLocal ?
                self::$localDocPath :
                self::$deploymentDocPath;

        try {
            $this->processImportTable();
        }
        catch (\Exception $ex) {
            print "EXCEPTION\n";
            if ($this->document) {
                print sprintf("\nFAILED on or after %d %s\n",
                    $this->document->fid,$this->document->filename);
            }
            throw $ex;
        }
        print "\n********************************\n\n";
        $errorcount = sizeof($this->errors);
        print sprintf(
            "Listed: %d\n".
            "Checked: %d\n".
            "Verified: %d\n".
            "Failed: %d\n",
            $this->listedCount,
            $this->checkedCount,
            $this->verifiedCount,
            $errorcount);

        if (!empty($this->errors)) {
            print "\n---- Errors ------------------------------------------\n";
            print implode("\n",$this->errors);
        }
        print "\n\ndone\n";
        $this->assertEquals(0,$errorcount,'Errors found');
    }

    private function processImportTable() {
        $test = 0;
        $count = 0;
        $this->query = new TQuery();
        $this->query->execute('delete from '.self::$errorTable);
        $sql = 'select fid,filename from '.self::$docTable;
        $stmt = $this->query->executeStatement($sql);
        $docs = $stmt->fetchAll(\PDO::FETCH_OBJ);
        $this->listedCount = sizeof($docs);
        foreach ($docs as $doc) {
            $this->checkedCount++;
            $this->document = $doc;
            $path = $this->documentPath.$doc->filename;

            if (file_exists($path)) {
                $this->verifiedCount++;
            }
            else {
                $this->error("not found");
                continue;
            }

            $count++;

            if ($test) {
                if ($count > $test) {
                    break;
                }
            }
        }
    }

    private function error($message) {
        $this->errors[] = sprintf("%04d: %s Failed: %s",$this->document->fid,$this->document->filename,
            $message);
        $sql = sprintf('insert into %s VALUES (?,?,?)',self::$errorTable);
        $this->query->execute($sql,
            [$this->document->fid,$this->document->filename,$message]);
    }
}