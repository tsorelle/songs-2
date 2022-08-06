<?php

namespace Peanut\songs\services;

use Peanut\songs\SongsManager;

class GetSongListsCommand extends \Tops\services\TServiceCommand
{

    protected function run()
    {
        $setId = $this->getRequest() ?? 0;
        $response = new \stdClass();
        $manager = new SongsManager();

        $songList = $manager->getSongInfoInSet($setId);
        if ($setId === 0) {
            $response->setSongs = [];
            $response->availableSongs = $songList;
        }
        else {
            $response->setSongs =  $songList;
            $response->availableSongs =
                $manager->getSongInfoNotInSet($setId);
        }
        $this->setReturnValue($response);
    }
}