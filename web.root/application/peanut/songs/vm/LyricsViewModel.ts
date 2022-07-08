/// <reference path='../../../../nutshell/pnut/core/ViewModelBase.ts' />
/// <reference path='../../../../nutshell/typings/knockout/knockout.d.ts' />
/// <reference path='../../../../nutshell/pnut/core/Peanut.d.ts' />
/// <reference path='../../../../nutshell/pnut/js/ViewModelHelpers.ts' />

namespace Peanut {
    interface ISongInfo {
        id: any;
        title: string;
        // user: string;
    }
    interface ISongSet {
        id: any;
        setname: string;
        // user: string;
    }

    interface IGetVersesResponse {
        // title: string;
        lyrics: string;
    }

    interface IGetSongsResponse extends IGetVersesResponse {
        set: ISongSet;
        sets: ISongSet[];
        songs: ISongInfo[];
        catalogSize: number;
        canedit: any;
    }

    export class LyricsViewModel extends Peanut.ViewModelBase {
        // observables
        page = ko.observable('lyrics');
        title = ko.observable('Song of the Silly');
        selectedSet = ko.observable<ISongSet>();
        sets = ko.observableArray<ISongSet>();
        songList : ISongInfo[] = [];
        songs : KnockoutObservableArray<ISongInfo>[] = [];
        allsongs = ko.observableArray<ISongInfo>();
        textSize = ko.observable(2);
        columnDisplay = ko.observable(false);
        selectedSong = ko.observable<ISongInfo>();
        loading = ko.observable('');
        isAdmin = ko.observable(false);
        signedIn = ko.observable(false);
        setForm = {
            id: ko.observable(0),
            setName: ko.observable(''),
            nameError: ko.observable(''),
            lookupValue: ko.observable(''),
            selectedSongs : ko.observableArray<ISongInfo>(),
            avaliableSongs: ko.observableArray<ISongInfo>(),
            searchValue: ko.observable(),
            user: ''
        };

        songForm = {
            id: ko.observable(0),
            title: ko.observable(''),
            lyrics: ko.observable(''),
            public: ko.observable(false),
            errorMessage: ko.observable(''),
            currentSetName: ko.observable(''),
            user: ko.observable(''),
            includeInSet : ko.observable(false),
        };

        canedit = ko.observable(false);
        username = ko.observable('');
        onLogin: any;

        songIndex = 0;
        songCount = 0;
        maxSongColumnItems = 0;

        // drag/drop
        draggedSong = ko.observable<ISongInfo>();
        dragStartIndex = ko.observable<any>();
        dragTargetIndex = ko.observable<any>();
        dragging : KnockoutComputed<boolean>;

        init(successFunction?: () => void) {
            let me = this;
            Peanut.logger.write('Lyrics Init');

            // temp for initial text
            for (let i = 0; i < 4; i++) {
                this.songs[i] = ko.observableArray([]);
            }

            me.dragging = ko.computed(() => {
                return (me.dragStartIndex() >= 0);
            });

            for (let i = 0; i< 4; i++ ) {
                this.songs[i] = ko.observableArray<ISongInfo>();
            }
            me.application.loadResources([
                // Load libraries and core components
                '@pnut/ViewModelHelpers.js'
            ], () => {
                me.application.registerComponents(
                    ['@pnut/modal-confirm',
                        'songs/lyrics-block'
                    ],
                    () => {
                    // let request =  null; // Peanut.HttpRequestVars.Get('set');
                    // todo:replace with router feature
                    let request = Peanut.Helper.getRequestParam('set');
                    me.services.executeService('Peanut.songs::GetSongs', request,
                        (serviceResponse: IServiceResponse) => {
                            if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                                let response = <IGetSongsResponse>serviceResponse.Value;
                                me.maxSongColumnItems = Math.floor(response.catalogSize / 4);
                                me.username('guest');
                                me.canedit(!!response.canedit);
                                me.sets(response.sets);
                                me.selectedSet(response.set);
                                this.loadSongList(response.songs);
                                this.songForm.lyrics(response.lyrics);
                            }
                    })
                        .fail(() => {
                            let trace = me.services.getErrorInformation();
                        })
                        .always(() => {
                            me.bindDefaultSection();
                            successFunction();
                        });
                });
            });
        }

        loadSongList = (songs: ISongInfo[],songIndex=0) => {
            this.songList = songs;
            this.songCount = songs.length;
            for (let i = 0; i < 4; i++) {
                this.songs[i]([]);
            }

            let column = [];
            let columnIndex = 0;
            for(let i = 0; i < songs.length; i++) {
                column.push(songs[i]);
                if (column.length >= this.maxSongColumnItems && columnIndex < 3) {
                    this.songs[columnIndex](column);
                    columnIndex++;
                    column = [];
                }
            }
            this.songs[columnIndex](column);
            this.setSongIndex(songIndex);
        };

        setSongIndex = (value: number) => {
            this.songIndex = value;
            let current = this.songList[this.songIndex];
            this.selectedSong(current);
            this.title(current.title);
        };



        prevSong = () => {

        }

        nextSong = () => {

        }
        showSongList = () => {

        }

        splitColumns = () => {

        }

        reduceFont = () => {

        }

        enlargeFont = () => {

        }

        help = () => {

        }

        onSaveLyrics = () => {

        }

        signIn = () => {

        }

        editSet  = () => {

        }

        newSet = () => {

        };
        newSong = () => {
        }

        home = () => {
            this.page('lyrics');
        };

        saveSetList = () => {

        }

        cancelSetEdit = () => {

        }

        deleteSet  = () => {

        }
        toggleUser  = () => {

        }

        filterByUser  = () => {

        }

    }
}
