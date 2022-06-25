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
        $filter = $request->filter ?? null;
        $response->types = $manager->getSongTypesLookup();
        if (!is_numeric($filter)) {
            $a = array_filter($response->types, function ($item) use ($filter) {
                return $item->code == $filter;
            });
            $found = array_shift($a);
            if ($found) {
                $request->filter = $found->id;
                $response->filtered = $found;
            }
        }
        $pageNo = $request->page ?? 1;
        $pageSize = $request->pageSize ?? null;
        // if ($pageSize && $pageNo == 1) {
            $response->songCount = $manager->getSongCount($request);
            $response->pageCount = (int)ceil($response->songCount / $pageSize);
        // }
        // $response->allSongsCount = $manager->getAllSongsCount();
        $response->pages = $manager->getSongPages($request);;
        // $response->instruments = $manager->getInstrumentsLookup();

        $this->setReturnValue($response);

    }
}