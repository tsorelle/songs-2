<?php

namespace PeanutTest\scripts;

use Tops\db\TQuery;
use Tops\sys\TIdentifier;

class AssignuidsTest extends TestScript
{

    public function execute()
    {
        $query = new TQuery();
        $sql = 'SELECT id FROM qnut_persons where UID is NULL';
        $list = $query->getAllValues($sql);
        $sql = 'UPDATE `qnut_persons` SET uid = ? WHERE id = ?';
        foreach ($list as $id) {
            $uid = TIdentifier::NewId();
            $query->execute($sql,[$uid, $id]);
        }



    }
}