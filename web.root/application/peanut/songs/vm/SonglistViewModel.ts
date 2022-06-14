// required for all view models:
/// <reference path='../../../../nutshell/pnut/core/ViewModelBase.ts' />
/// <reference path='../../../../nutshell/typings/knockout/knockout.d.ts' />
/// <reference path='../js/songs.d.ts' />

// Module
namespace Peanut {
    interface ISongListResponse {
        pages: ISongListItem[];
        pageCount? : number;
    }

    interface ISonglistInitResponse extends ISongListResponse{
        types: ILookupItem[];
        instruments: ILookupItem[];
    }
    export class SonglistViewModel extends Peanut.ViewModelBase {
        planet = ko.observable('Mars');

        songlist = ko.observableArray<Peanut.ISongListItem>();
        songTypes : ILookupItem[] = [];
        instruments: ILookupItem[] = [];
        currentPage = ko.observable(1);
        maxPages = ko.observable();

        currentSearchRequest : ISongSearchRequest = {
            pageSize: 20,
            page: 1
        };

        init(successFunction?: () => void) {
            let me = this;
            Peanut.logger.write('Songlist Init');

            me.application.registerComponents('@pnut/pager', () => {
                let request = this.currentSearchRequest;
                me.services.executeService('Peanut.songs::InitSongList',request,
                    (serviceResponse: Peanut.IServiceResponse) => {
                        if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                            let response = <ISonglistInitResponse>serviceResponse.Value;
                            me.songTypes = response.types;
                            me.instruments = response.instruments;
                            me.setList(response);
                            me.bindDefaultSection();
                            successFunction();
                        }
                    }
                ).fail(() => {
                    // let trace = me.services.getErrorInformation();
                    me.application.hideWaiter();
                });
            });

        }

        setList = (response: ISongListResponse) => {
            this.songlist(response.pages);
            if (this.currentSearchRequest.page === 1) {
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

        applyFilter = (filter: ILookupItem) => {
            let me = this;
            me.currentSearchRequest.filter = filter.id;
            me.currentSearchRequest.page = 1;
            me.getSongList();
        }

        getSongList = (onSuccess?: () => void) => {
            let me = this;
            let request = this.currentSearchRequest;
            me.application.hideServiceMessages();
            me.application.showWaiter('Finding songs...');
            me.services.executeService('Peanut.songs::GetSongList',request,
                (serviceResponse: Peanut.IServiceResponse) => {
                    if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                        me.application.hideWaiter();
                        let response = <ISongListResponse>serviceResponse.Value;
                        this.setList(response);
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
