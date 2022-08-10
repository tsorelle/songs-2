<?php

namespace Peanut\songs\services;

use Peanut\songs\SongsManager;

class RemoveSetCommand extends \Tops\services\TServiceCommand
{

    protected function run()
    {
        $setId = $this->getRequest();
        if (!$setId) {
            $this->addErrorMessage('No set id');
            return;
        }
        $manager = new SongsManager();
        $manager->removeSet($setId);
    }
}