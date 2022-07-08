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
 *				page?: number,
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
 *              pageCount?: number,
 *            }
 */
class GetSonglistCommand extends \Tops\services\TServiceCommand
{
    protected function run()
    {
        $request = $this->getRequest();
        $manager = new SongsManager();
        $response = new \stdClass();
        $pageNo = $request->page ?? 1;
        $pageSize = $request->pageSize ?? null;
        if ($pageSize && $pageNo == 1) {
            $response->songCount = $manager->getSongPageCount($request);
            $response->pageCount = (int)ceil($response->songCount / $pageSize);
        }
        $response->pages = $manager->getSongPages($request);;
        $this->setReturnValue($response);
    }
}