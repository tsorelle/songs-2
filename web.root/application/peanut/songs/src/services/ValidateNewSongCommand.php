<?php

namespace Peanut\songs\services;

use Peanut\songs\db\model\entity\Song;
use Peanut\songs\SongsManager;

/**
 * Service contract
 *      Request:
 *          interface INewSongValidationRequest {
 *              title: string;
 *              contentId: string;
 *
 *           }
 *      Response:
 *          string = 'ok' or error message
 */
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