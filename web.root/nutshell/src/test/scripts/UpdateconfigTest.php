<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 9/12/2017
 * Time: 10:01 AM
 */

namespace PeanutTest\scripts;


use Tops\sys\TIniCompare;
use Tops\sys\TPath;
use Tops\sys\TWebSite;

class UpdateconfigTest extends TestScript
{

    private $testdomain = 'local.austinquakers.org';
    // private $productionDomain = 'staging.austinquakers.org';
    private $productionDomain = 'testing.austinquakers.org';
    private $configPath;
    private $sourcePath;
    private $targetPath;

    public function execute()
    {
        $domain = TWebSite::GetDomain();
        $this->configPath = TPath::getConfigPath();
        $this->targetPath = $this->configPath;
        if ($domain === $this->testdomain) {
            $domain = $this->productionDomain;
            $this->targetPath = TPath::fromFileRoot('../tmp/config-test',true)."/";
        }
        $this->sourcePath = TPath::joinPath($this->configPath,"sites/$domain");
        // $this->fixIni('mailgun.ini');

        $files = scandir($this->sourcePath);
        foreach ($files as $fileName) {
            if (pathinfo($fileName,PATHINFO_EXTENSION) == 'ini') {
                $this->fixIni($fileName);
            }
        }
        $dbconfigSource = TPath::joinPath($this->sourcePath,"database.php");
        $dbconfigTarget = TPath::joinPath($this->targetPath,"database.php");
        copy($dbconfigSource,$dbconfigTarget);
    }

    private function fixIni($fileName) {
        $currentFile = TPath::joinPath($this->configPath,$fileName);
        $correctionsFile = TPath::joinPath($this->sourcePath,$fileName);
        $lines = TIniCompare::Reconcile($currentFile,$correctionsFile);
        $ok = is_array($lines);
        $this->assert($ok,$lines);
        if ($ok) {
            $output = implode('',$lines);
            $this->assert(strlen($output) > 0,'No output');
            if ($output) {
                $path = TPath::joinPath($this->targetPath,$fileName);
                file_put_contents($path, $output);
            }
        }
    }
}