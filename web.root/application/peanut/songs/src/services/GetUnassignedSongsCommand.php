<?php

namespace Peanut\songs\services;

use Peanut\songs\SongsManager;

class GetUnassignedSongsCommand extends \Tops\services\TServiceCommand
{

    protected function run()
    {
        $manager = new SongsManager();
        $songlist = $manager->getUnassignedSongsList();
        $this->setReturnValue($songlist);
    }
}