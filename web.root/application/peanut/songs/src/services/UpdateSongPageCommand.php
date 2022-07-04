<?php

namespace Peanut\songs\services;

use Peanut\songs\SongsManager;

/**
 * Service Contract:
 *  Request
 *
 *     interface ISong {
 *         id : any,
 *         contentId : string,
 *         title : string,
 *         description : string,
 *         lyrics : string,
 *         publicdomain : any
 *     }
 *
 *     interface ISongPageUpdateRequest {
 *         id : any,
 *         song: ISong;
 *         introduction : string,
 *         commentary : string,
 *         active : any,
 *         postedDate : any,
 *         pageimage : string,
 *         imagecaption : string,
 *         youtubeId : string,
 *     }
 *
 *  Response
 * 		interface ISongPageUpdateResponse {
 * 			pageId : any,
 * 			songId: any;
 * 			hasicon : any,
 * 		}
 */
class UpdateSongPageCommand extends \Tops\services\TServiceCommand
{

    protected function run()
    {
        $request = $this->getRequest();
        if (!$request) {
            $this->addErrorMessage('No request');
            return;
        }
        if (!isset($request->song)) {
            $this->addErrorMessage('No song');
            return;
        }

        if (!isset($request->id)) {
            $this->addErrorMessage('No page ID');
            return;
        }

        if (!isset($request->types)) {
            $this->addErrorMessage('No page types');
            return;
        }

        if (!isset($request->song->id)) {
            $this->addErrorMessage('No song ID');
            return;
        }

        if (!isset($request->contentId)) {
            $this->addErrorMessage('No contentId');
            return;
        }

        if (!isset($request->types)) {
            $this->addErrorMessage('No page types');
            return;
        }

        $manager = new SongsManager();

        $response = $manager->updateSongPage($request);
        if (isset($response->error)) {
            $this->addErrorMessage($response->error);
        }
        else {
            $this->setReturnValue($response);
        }

    }
}