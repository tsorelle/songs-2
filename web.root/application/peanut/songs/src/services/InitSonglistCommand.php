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
        $request = $this->getRequest();
        $manager = new SongsManager();
        $response = new \stdClass();
        $pageNo = $request->page ?? 1;
        $pageSize = $request->pageSize ?? null;
        if ($pageSize && $pageNo == 1) {
            $response->songCount = $manager->getSongCount($request);
            $response->pageCount = (int)ceil($response->songCount / $pageSize);
        }

        $response->pages = $manager->getSongPages($request);;
        $response->types = $manager->getSongTypesLookup();
        $response->instruments = $manager->getInstrumentsLookup();

        $this->setReturnValue($response);

    }
}