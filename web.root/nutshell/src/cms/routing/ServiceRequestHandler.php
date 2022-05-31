<?php

namespace Nutshell\cms;

use Peanut\PeanutTasks\TaskManager;
use Peanut\users\AccountManager;
use Tops\services\ServiceFactory;
use Tops\sys\TConfiguration;
use Tops\sys\TUser;

class ServiceRequestHandler
{
    public function executeService()
    {
        $response = ServiceFactory::Execute();
        print json_encode($response);
    }

    public function signout() {
        $referrer = preg_replace("/(^\/)|(\/$)/","",$_SERVER['HTTP_REFERER']);
        TUser::SignOut();
        header('Location: '.$referrer);
        exit();
    }

    public function runtest($testname) {
        print "<pre>";
        print "Running $testname\n";
        if (empty($testname)) {
            exit("No test name!");
        }
        $testname = strtoupper(substr($testname,0,1)).substr($testname,1);
        $className = "\\PeanutTest\\scripts\\$testname".'Test';
        $test = new $className();
        $test->run();

        print "\n</pre>";
        print "<a href='/' target='_blank'>Home</a>";
        exit;
    }

    public function getSettings() {
        include(DIR_CONFIG_SITE.'/settings.php');
    }

    public function runScheduledTasks($taskId = 0) {
        set_exception_handler('Nutshell\cms\ServiceRequestHandler::exceptionHandler');
        (new TaskManager())->runJobs($taskId);
        exit;
    }

    public static function exceptionHandler($ex) {
        $msg = $ex->getMessage();
        $content ="$msg\n\n".$ex->getTraceAsString();
        $to = TConfiguration::getValue('notifyemail','site','webadmin@nutshell.org');
        mail($to,'Exception in site tasks',$content);
        exit ($msg);
    }

}