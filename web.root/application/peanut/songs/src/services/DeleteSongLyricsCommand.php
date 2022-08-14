<?php

namespace Peanut\songs\services;

use Peanut\songs\SongsManager;

class DeleteSongLyricsCommand extends \Tops\services\TServiceCommand
{

    protected function run()
    {
        $id = $this->getRequest();
        if (!$id) {
            $this->addErrorMessage('No song id received');
            return;
        }
        $manager = new SongsManager();
        if (!$manager->deleteLyrics($id)) {
            $this->addErrorMessage('This song is linked to a page. Cannot delete. Use the song page to edit.');
            return;
        };
        $this->addInfoMessage('Song deleted');
    }
}