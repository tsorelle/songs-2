<?php


use Peanut\songs\SongsManager;
use PHPUnit\Framework\TestCase;

class SongsManagerTest extends TestCase
{

    public function testGetInstrumentsLookup()
    {
        $manager = new SongsManager();
        $actual = $manager->getInstrumentsLookup();
        $this->assertNotEmpty($actual);

    }

    public function testGetSongTypesLookup()
    {
        $manager = new SongsManager();
        $actual = $manager->getSongTypesLookup();
        $this->assertNotEmpty($actual);

    }

    public function testGetSongPages()
    {
        $manager = new SongsManager();
        $actual = $manager->getSongPages();
        $this->assertNotEmpty($actual);

    }
    public function testGetSongPagesPage()
    {
        $manager = new SongsManager();
        $list = $manager->getSongPages();
        $actual = count($list);
        $this->assertNotEmpty($actual);

        $items = 15;
        $page = 1;
        $list = $manager->getSongPages(null,$page,$items);
        $actual = count($list);
        $this->assertEquals($items, $actual);

        $page = 2;
        $list = $manager->getSongPages(null,$page,$items);
        $actual = count($list);
        $this->assertEquals($items, $actual);


    }
}
