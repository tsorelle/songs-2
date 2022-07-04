<?php

namespace Peanut\songs\db\model\repository;

use PHPUnit\Framework\TestCase;

class SongsRepositoryTest extends TestCase
{

    public function testGetSongBycontentId()
    {
        $cid = 'zebra-dun';
        $repo =new SongsRepository();
        $actual = $repo->getSongBycontentId($cid);
        $this->assertTrue($actual !== false);

        $actual = $repo->get(6);
        $this->assertTrue($actual !== false);
        $this->assertEquals($cid,$actual->contentId);
    }

    public function testGetSongByTitle()
    {
        $title = 'Zebra Dun';
        $repo =new SongsRepository();
        $actual = $repo->getSongByTitle($title);
        $this->assertTrue($actual !== false);

        $actual = $repo->get(6);
        $this->assertTrue($actual !== false);
        $this->assertEquals($title,$actual->title);
    }
}
