<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 9/12/2017
 * Time: 10:01 AM
 */

namespace PeanutTest\scripts;


// use Peanut\QnutYearlymeeting\db\model\repository\FeetypesRepository;

use Peanut\QnutYearlymeeting\db\ScymRegistrationsManager;

class SmoketestTest extends TestScript
{

    public function execute()
    {
        $ok = ScymRegistrationsManager::WithinDeadline();
        $this->assert(!$ok,'OOPS');
      //  $this->assert(true,'Testing works');
    }
}