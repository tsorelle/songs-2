<?php

namespace Peanut\songs\services;

use Peanut\songs\SongsManager;

class GetSongLyricsCommand extends \Tops\services\TServiceCommand
{

    protected function run()
    {
        $id = $this->getRequest();
        if (!$id) {
            $this->addErrorMessage('No song id');
            return;
        }
        $manager = new SongsManager();
        $song = $manager->getSong($id);
        $response = new \stdClass();
        $response->lyrics = $song->lyrics;
        $response->notes = $song->notes;
        $this->setReturnValue($response);
    }
}