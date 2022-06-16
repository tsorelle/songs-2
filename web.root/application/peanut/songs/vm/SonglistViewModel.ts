// required for all view models:
/// <reference path='../../../../nutshell/pnut/core/ViewModelBase.ts' />
/// <reference path='../../../../nutshell/typings/knockout/knockout.d.ts' />
/// <reference path='../js/songs.d.ts' />
/// <reference path='../../../../nutshell/pnut/components/selectedListComponent.ts' />
/// <reference path='../../../../nutshell/pnut/js/selectListObservable.ts' />

// Module
namespace Peanut {
    interface ISongListResponse {
        pages: ISongListItem[];
        songCount?: number;
        pageCount? : number;
    }

    interface ISonglistInitResponse extends ISongListResponse{
        types: ILookupItem[];
        instruments: ILookupItem[];
        filtered? : ILookupItem;
    }
    export class SonglistViewModel extends Peanut.ViewModelBase {

        songlist = ko.observableArray<Peanut.ISongListItem>();
        songTypes : ILookupItem[] = [];
        instruments: ILookupItem[] = [];
        currentPage = ko.observable(1);
        maxPages = ko.observable();
        searchTerms = ko.observable('');
        searchClear = ko.observable(true);
        showSearchTerms = ko.observable(false);
        showTextInfo = ko.observable(false)
        sortOrder = ko.observable(1);
        filterController : selectListObservable;
        searchTypeController : selectListObservable;
        sortOrderController : selectListObservable;
        filterTitle = ko.observable('');
        songsFound = ko.observable(false);

        currentSearchRequest : ISongSearchRequest = {
            pageSize: 20,
            page: 1
        };

        init(successFunction?: () => void) {
            let me = this;
            Peanut.logger.write('Songlist Init');
            let initFilter = me.getPageVarialble('song-type');
            if (initFilter) {
                me.currentSearchRequest.filter = initFilter;
            }
                me.application.registerComponents('@pnut/pager,@pnut/lookup-select', () => {
                    me.application.loadResources([
                        '@pnut/selectListObservable'
                    ], () => {

                        let request = this.currentSearchRequest;
                    me.services.executeService('Peanut.songs::InitSongList',request,
                        (serviceResponse: Peanut.IServiceResponse) => {
                            if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                                let response = <ISonglistInitResponse>serviceResponse.Value;
                                me.filterController = new selectListObservable(me.onFilterChange,response.types);
                                me.searchTypeController = new selectListObservable(me.onSearchTypeChange,
                                        [<ILookupItem>{
                                            name: 'Title',
                                            id: 1
                                        },
                                        <ILookupItem>{
                                            name: 'Full Text',
                                            id: 2
                                        }]
                                    );

                                me.sortOrderController = new selectListObservable(null,
                                        [<ILookupItem>{
                                            name: 'Title',
                                            id: 1
                                        },
                                        <ILookupItem>{
                                            name: 'Posted date',
                                            id: 2
                                        },
                                        <ILookupItem>{
                                            name: 'Posted date (recent first)',
                                            id: 3
                                        },
                                        ]
                                    );

                                me.songTypes = response.types;
                                me.instruments = response.instruments;
                                if (response.filtered) {
                                    me.currentSearchRequest.filter = response.filtered.id;
                                    me.filterController.setValue(response.filtered.id);
                                    me.filterTitle(response.filtered.description);
                                    me.searchClear(false);
                                }
                                else {
                                    me.filterTitle('All '+response.songCount+' Songs');
                                }

                                me.setList(response);
                                me.searchTypeController.subscribe();
                                me.filterController.subscribe();

                                me.bindDefaultSection();
                                successFunction();
                            }
                        }
                    ).fail(() => {
                        // let trace = me.services.getErrorInformation();
                        me.application.hideWaiter();
                    });
                });
            });

        }

        setList = (response: ISongListResponse) => {
            this.songlist(response.pages);
            if (this.currentSearchRequest.page === 1) {
                this.songsFound(response.songCount > 0);
                this.maxPages(response.pageCount);
            }
            this.currentPage(this.currentSearchRequest.page);
        }

        changePage = (move: number) => {
            let current = this.currentPage() + move;
            let me = this;
            me.currentSearchRequest.page = current;
            me.getSongList()
        }

        submitSearch = () => {
            this.currentSearchRequest.searchType = this.searchTypeController.getValue();
            this.currentSearchRequest.searchTerms = this.searchTerms();
            this.currentSearchRequest.filter = this.filterController.getValue();
            this.currentSearchRequest.order = this.sortOrderController.getValue();
            this.currentSearchRequest.page = 1;
            this.getSongList();
        }

        clearSearch() {
            this.searchTypeController.unsubscribe();
            this.filterController.unsubscribe();
            this.searchTerms('');
            this.currentSearchRequest.page = 1;
            this.currentSearchRequest.filter = null;
            this.filterController.setValue(null);
            this.currentSearchRequest.searchType = null;
            this.searchTypeController.setValue(null);
            this.currentSearchRequest.order = 1;
            this.sortOrder(1)
            this.currentSearchRequest.searchTerms = '';
            this.searchTypeController.subscribe();
            this.filterController.subscribe();
            this.getSongList();
        }

        onFilterChange = (type: ILookupItem) => {
            this.searchTypeController.unsubscribe();
            this.searchTypeController.selected(null);
            this.showTextInfo(false );
            this.showSearchTerms(false);
            this.searchTypeController.subscribe();
        }
        onSearchTypeChange = (type: ILookupItem) => {
            this.filterController.unsubscribe();
            this.showSearchTerms(!!type);
            this.showTextInfo(type && type.id == 2);
            this.filterController.selected(null);
            this.filterController.subscribe();
        }

        getSongList = (onSuccess?: () => void) => {
            let me = this;
            let request = this.currentSearchRequest;
            me.application.hideServiceMessages();
            // me.application.showWaiter('Finding songs...');
            me.services.executeService('Peanut.songs::GetSongList',request,
                (serviceResponse: Peanut.IServiceResponse) => {
                    if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                        me.application.hideWaiter();
                        let response = <ISongListResponse>serviceResponse.Value;


                        me.searchClear(
                            !((me.currentSearchRequest.filter && me.currentSearchRequest.filter > 0) ||
                                (me.currentSearchRequest.searchType && me.currentSearchRequest.searchType > 0) ||
                                (me.currentSearchRequest.order && me.currentSearchRequest.order > 1))
                        )
                        me.setList(response);
                        if (me.currentSearchRequest.page == 1) {
                            me.filterController.setValue(me.currentSearchRequest.filter || null);
                            let filterItem = me.filterController.selected();
                            me.filterTitle(filterItem ? filterItem.description : response.songCount + ' Songs');
                        }
                        if (onSuccess) {
                            onSuccess();
                        }
                    }
                }
            ).fail(() => {
                // let trace = me.services.getErrorInformation();
                me.application.hideWaiter();
            });

        }
    }
}
