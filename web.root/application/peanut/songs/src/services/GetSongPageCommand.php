<?php

namespace Peanut\songs\services;

use Peanut\songs\SongsManager;

/**
 * Service contract
 *      Request: songid : string;
 *
 *      Response:
 *         interface IGetSongPageResponse {
 *             page: ISongPage;
 *         }
 *         interface IGetSongPageInitResponse extends IGetSongPageResponse{
 *             types?: ILookupItem[],
 *             songTypeLinks: ILookupItem[],
 *             canedit: any
 *         }
 *         interface ISong {
 *             id : any,
 *             contentId : string,
 *             title : string,
 *             description : string,
 *             lyrics : string,
 *             publicdomain : string,
 *         }
 *         interface ISongPage {
 *             id : any,
 *             song: ISong;
 *             introduction : string,
 *             commentary : string,
 *             active : any,
 *             postedDate : any,
 *             pageimage : string,
 *             imagecaption : string,
 *             youtubeId : string,
 *             hasicon : any,
 *             types? : number[]
 *         }
 *         export interface ILookupItem {
 *             id : any;
 *             name: string;
 *             code?: string;
 *             description? : string;
 *             active? : any;
 *         }
 *         export interface ILinkListItem extends ILookupItem {
 *             url: string;
 *         }
 *
 */
class GetSongPageCommand extends \Tops\services\TServiceCommand
{


    protected function run()
    {
        $contentId = $this->getRequest();
        if (empty($contentId)) {
            $this->addErrorMessage('No song id received.');
            return;
        }
        $manager = new SongsManager();
        $response = new \stdClass();
        if  ($contentId !== 'new') {
            $response->page = $manager->getSongPage($contentId);
            if (!$response->page) {
                $this->addErrorMessage("No song page found for id '$contentId'.");
                return;
            }
        }
        $response->types = $manager->getSongTypesLookup();
        $response->canedit = $this->getUser()->isAdmin(); // maybe replace this with authorization
        $response->songTypeLinks = $manager->getSongTypeLinks();
        $response->latest = $manager->getLatestSongLinks();

        $this->setReturnValue($response);
    }
}