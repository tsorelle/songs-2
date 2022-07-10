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
        $lyrics = $manager->getSongLyrics($id);
        $this->setReturnValue($lyrics);
    }
}