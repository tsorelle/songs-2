/// <reference path='../../../../nutshell/pnut/core/Peanut.d.ts' />
declare namespace Peanut {
    interface ISongListItem extends Peanut.ILookupItem {
        youtubeId: string;
        introduction: string;
        iconsrc: string;
        thumbnailsrc: string;
        songUrl: string;
        active?: any;
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
         *     0 - none (default)
         *     1 - 'title' - title field only
         *     2 - 'text' - Title, description, introduction, comentary
         */
        searchType?: number,
        searchTerms?: string,
        /**
         * Filter : id value from tls_tags
         */
        filter?: number | string,
        /**
         * Order
         *  1=title (default)
         *  2=date desc
         *  3=date asc
         */
        order?: number,
        page?: number,
        pageSize?: number
    }

    interface ISongSearchResponse {
        pages: ISongListItem[],
        pageCount?: number,
        types?: ILookupItem[],
        instruments?: ILookupItem[]
    }
}