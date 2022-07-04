<?php

namespace Peanut\songs;

use Peanut\songs\db\model\repository\SongsRepository;
use PHPUnit\Framework\TestCase;

class SongsManagerTest extends TestCase
{

    public function testGetFeaturedSongsList()
    {
        $manager = new SongsManager();
        $selected = $manager->getFeaturedSongsList();
        $actual = count($selected);
        $expected = 12;
        $this->assertEquals($actual,$expected);

        $selected = $manager->getFeaturedSongsList();
        $actual = count($selected);
        $expected = 12;
        $this->assertEquals($actual,$expected);


    }
    public function testValidateNewSong() {
        $repo = new SongsRepository();
        $song = $repo->get(6);

        $manager = new SongsManager();
        $actual = $manager->validateNewSong($song);
        $this->assertNotEquals($actual,'ok');
    }
}
