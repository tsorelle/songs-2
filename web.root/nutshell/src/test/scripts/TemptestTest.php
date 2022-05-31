<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 9/12/2017
 * Time: 10:01 AM
 */

namespace PeanutTest\scripts;


use Tops\db\TQuery;
use Tops\sys\TEncryption;
use Tops\sys\TIdentifier;

class TemptestTest extends TestScript
{


    public function execute()
    {
        $encriptor = new TEncryption('4e12d8e8-4a4d-4098-9202-5d5ff25ccb6e','62729f5f-bcce-4562-8964-72c788b71514');
        $p = $encriptor->encrypt('St5hIwAdE_L?@biRaC72');
        print "$p\n";
        $d = $encriptor->decrypt($p);
        print "$d\n";

    }
}