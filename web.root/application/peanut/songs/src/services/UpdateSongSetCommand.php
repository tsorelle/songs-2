<?php

namespace Peanut\songs\services;

use Peanut\songs\SongsManager;

class UpdateSongSetCommand extends \Tops\services\TServiceCommand
{

    protected function run()
    {
        $request = $this->getRequest();
        if (!$request) {
            $this->addErrorMessage('No request received');
        }

        $setId = @$request->setId;
        $isNew = (!$setId);
        $setName = @$request->setName;
        if ($isNew) {
            if (!$setName) {
                $this->addErrorMessage('Set name is required');
                return;
            }
        }
        $user = $this->getUser();
        if ($user->isAdmin()) {
            $username = null;
        }
        else {
            $username = $request->user ?? null;
            if (!$username) {
                $username = $this->getUser()->getUserName();
            }
        }

        $manager = new SongsManager();
        if (!$manager->checkUniqueSetName($setName, $setId,$username)) {
            $this->addErrorMessage('Set name is in use. Try another');
            return;
        }

        if ($isNew) {
            $setId = $manager->createSet($setName,$username);
        }
        else {
            $manager->changeSetName($setId,$setName);
        }
        $manager->updateSetSongs($setId, $request->songs);
        $response = new \stdClass();
        $response->setId = $setId;
        $response->songs = $manager->getSongInfoInSet($setId);

        $this->setReturnValue($response);
    }
}