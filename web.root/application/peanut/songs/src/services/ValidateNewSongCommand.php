<?php

namespace Peanut\songs\services;

use Peanut\songs\db\model\entity\Song;
use Peanut\songs\SongsManager;

class ValidateNewSongCommand extends \Tops\services\TServiceCommand
{

    protected function run()
    {
        $request = $this->getRequest();
        if (!$request) {
            $this->addErrorMessage('No request received');
            return;
        }
        $manager = new SongsManager();
        $response = $manager->validateNewSong($request);
        $this->setReturnValue($response);
    }
}