<?php

namespace Peanut\songs\services;

use Peanut\songs\SongsManager;

/**
 * Service Contract
 *    Request:
 *		interface ISong {
 *			id : any,
 *			title : string,
 *			lyrics : string,
 *			publicdomain : any,
 *			notes: string
 *		}
 *		interface ISongUpdateRequest extends ISong {
 *			setId: any;
 *			user?: string;
 *		}
 *
 *	Response:
 *	    interface ISongUpdateResponse {
 *			id: any;
 *			songs: ISongInfo[];
 *		}
 *
 */
class UpdateSongCommand extends \Tops\services\TServiceCommand
{

    protected function run()
    {
        $request = $this->getRequest();
        if (!$request) {
            $this->addErrorMessage('No request received');
            return;
        }
        $manager = new SongsManager();

        $response = new \stdClass();
        $response->id = $manager->updateSong($request);
        // todo: current set only?
        $setId = $request->setId ?? 0;
        $response->songs = $manager->getSongInfoInSet($setId);
        $this->setReturnValue($response);
    }
}