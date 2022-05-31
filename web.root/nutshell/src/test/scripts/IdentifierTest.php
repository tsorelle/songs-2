<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 9/12/2017
 * Time: 10:01 AM
 */

namespace PeanutTest\scripts;


use Tops\sys\TIdentifier;

class IdentifierTest extends TestScript
{

    public function execute()
    {
        for($i=0;$i<50;$i++) {
            print TIdentifier::NewId()."\n";
        }
    }
}