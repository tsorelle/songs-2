<?php

namespace Peanut\songs\services;

use Peanut\songs\SongsManager;

/***
 * Service contract:
 *      Request:
 *			interface ISongSearchRequest {
 *				searchType?: string,
 *				searchTerms?: string,
 *				filter?: number,
 *				order?: number,
 *				pageNo?: number,
 *				pageSize?: number,
 *				returnLookups? : any
 *			}
 *
 *			Filter :
 *				id value from tls_tags
 *			Search type:
 *				  none (default)
 *				 'title' - title field only
 *				 'text' - Title, description, introduction, comentary
 *			Order:
 *			 0=title (default)
 *			 1=date desc
 *			 2=date asc
 *
 *        Response:
 *            interface ISongSearchResponse {
 *            	pages: ISongListItem[],
 *            	types?: ILookupItem[],
 *            	instruments?: ILookupItem[]
 *            }
 */
class GetSonglistCommand extends \Tops\services\TServiceCommand
{

    protected function run()
    {
        $request = $this->getRequest();
        $manager = new SongsManager();
        $response = new \stdClass();
        $response->pages = $manager->getSongPages($request);
        if (!empty($request->returnLookups)) {
            $response->types = $manager->getSongTypesLookup();
            $response->instruments = $manager->getInstrumentsLookup();
        }
        $this->setReturnValue($response);
    }
}