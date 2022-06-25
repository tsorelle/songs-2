<?php

namespace Peanut\songs;

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
}
