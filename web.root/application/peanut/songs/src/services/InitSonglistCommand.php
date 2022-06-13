<?php

namespace Peanut\songs\services;

use Peanut\songs\SongsManager;

/**
 * Service contract:
 *      Response:
 *        interface ISonglistInitResponse{
 *            pages: ISongListItem[];
 *            types: ILookupItem[];
 *            instruments: ILookupItem[];
 *        }
 *
 *        export interface ILookupItem {
 *            id : any;
 *            name: string;
 *            code?: string;
 *            description? : string;
 *            active? : any;
 *        }
 *
 *        interface ISongListItem extends Peanut.ILookupItem {
 *            youtubeId: string;
 *            introduction: string;
 *            iconsrc: string;
 *            thumbnailsrc: string;
 *        }
 *
 */
class InitSonglistCommand extends \Tops\services\TServiceCommand
{

    protected function run()
    {
        $response = new \stdClass();
        $manager = new SongsManager();
        $response->pages = $manager->getSongPages();
        $response->types = $manager->getSongTypesLookup();
        $response->instruments = $manager->getInstrumentsLookup();
        $this->setReturnValue($response);
    }
}