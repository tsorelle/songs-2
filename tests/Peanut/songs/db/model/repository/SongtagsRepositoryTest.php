<?php

namespace Peanut\songs\db\model\repository;

use PHPUnit\Framework\TestCase;

class SongtagsRepositoryTest extends TestCase
{

    public function testGetTagValues()
    {
        $repo = new SongtagsRepository();
        $songId = 4;
        $type = 'type';
        $actual = $repo->getTagValues($songId,$type);
        $this->assertNotEmpty($actual);
        $typeCount = count($actual);

        $actual = $repo->getTagValues($songId);
        $this->assertNotEmpty($actual);
        $allCount = count($actual);


        $type = 'instrument';
        $actual = $repo->getTagValues($songId,$type);
        $this->assertNotEmpty($actual);
        $instrumentCount = count($actual);

        $this->assertNotEquals($typeCount + $instrumentCount, $allCount);

    }
}
