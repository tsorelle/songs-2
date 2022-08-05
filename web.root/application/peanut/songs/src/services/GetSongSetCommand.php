<?php

namespace Peanut\songs\services;

use Peanut\songs\SongsManager;

class GetSongSetCommand extends \Tops\services\TServiceCommand
{

    protected function run()
    {
        $setId = $this->getRequest() ?? 0;
        $manager = new SongsManager();
        $response = new \stdClass();
        $response->sets = $manager->getSongSetList();

        $selectedSet = $this->getSelectedSet($setId,$response->sets);
        if (!$selectedSet) {
            $this->addErrorMessage("Set %setId not found.");
            return;
        }

        $response->set = $selectedSet;

        $response->songs = $manager->getSongInfoInSet($selectedSet->id);
        if (empty($response->songs)) {
            $this->addErrorMessage("No songs found in set '$selectedSet->title'");
            return;
        }

        $song = $response->songs[0];

    }
}