/// <reference path='../../../../nutshell/pnut/core/Peanut.d.ts' />
declare namespace Peanut {
    interface ISongListItem extends Peanut.ILookupItem {
        youtubeId: string;
        introduction: string;
        iconsrc: string;
        thumbnailsrc: string;
    }

    interface ISong {
        id : any,
        contentid : string,
        title : string,
        description : string,
        lyrics : string,
        publicdomain : string,
        contenttype : string,
    }

    interface ISongPage {
        id : any,
        song: ISong;
        introduction : string,
        commentary : string,
        active : any,
        postedDate : any,
        contenttype : string,
        pageimage : string,
        imagecaption : string,
        youtubeId : string,
        hasicon : any,
        hasthumbnail : any,
        instruments? : number[],
        types? : number[]
    }

    interface ISongSearchRequest {
        /**
         * Search type:
         *       none (default)
         *      'title' - title field only
         *      'text' - Title, description, introduction, comentary
         */
        searchType?: string,
        searchTerms?: string,
        /**
         * Filter : id value from tls_tags
         */
        filter?: number,
        /**
         * Order
         *  0=title (default)
         *  1=date desc
         *  2=date asc
         */
        order?: number,
        pageNo?: number,
        pageSize?: number,
        returnLookups? : any
    }

    interface ISongSearchResponse {
        pages: ISongListItem[],
        types?: ILookupItem[],
        instruments?: ILookupItem[]
    }
}