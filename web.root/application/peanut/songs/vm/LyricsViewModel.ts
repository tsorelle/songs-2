/// <reference path='../../../../nutshell/pnut/core/ViewModelBase.ts' />
/// <reference path='../../../../nutshell/typings/knockout/knockout.d.ts' />
/// <reference path='../../../../nutshell/pnut/core/Peanut.d.ts' />
/// <reference path='../../../../nutshell/pnut/js/ViewModelHelpers.ts' />
/// <reference path='../../../../nutshell/pnut/js/multicolumnListPageLoader.ts' />
/// <reference path='../../../../nutshell/typings/bootstrap-5/index.d.ts' />
/// <reference path='../../../../nutshell/typings/bootstrap-5/js/dist/modal.d.ts' />
/// <reference path='../../../../nutshell/pnut/packages/peanut-content/js/contentController.ts' />

namespace Peanut {
    import IContentOwner = PeanutContent.IContentOwner;

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
        notes: string;
    }

    interface IGetSongsResponse extends IGetVersesResponse {
        set: ISongSet;
        sets: ISongSet[];
        songs: ISongInfo[];
        catalogSize: number;
        canedit: any;
    }

    export class LyricsViewModel
        extends Peanut.ViewModelBase
        implements IContentOwner{
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
        pageController : multicolumnListPageLoader;
        contentController: PeanutContent.contentController;
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
            notes: ko.observable(''),
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

        showInfoButton = ko.observable(true);

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
                '@pnut/ViewModelHelpers.js',
                '@lib:tinymce',
                '@pkg/peanut-content/contentController.js',
                '@pnut/multicolumnListPageLoader'
            ], () => {
                me.pageController = new multicolumnListPageLoader();
                me.application.registerComponents(
                    ['@pnut/modal-confirm',
                        '@pnut/pager',
                        '@pkg/peanut-content/content-block',
                        'songs/lyrics-block'
                    ],
                    () => {
                        me.contentController = new PeanutContent.contentController(me);

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
                                me.loadSongList(response.songs);
                                me.songForm.lyrics(response.lyrics);
                                let notes = (response.notes ??  '').trim();
                                me.songForm.notes(notes);
                                me.showInfoButton(
                                    (response.canedit || notes.length > 0)
                                );
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
            this.pageController.setItems(<[]>songs);
            this.setSongIndex(songIndex);
        };

        setSongIndex = (value: number) => {
            this.songIndex = value;
            let current = this.songList[this.songIndex];
            this.selectedSong(current);
            this.title(current.title);
        };

        selectSong = (item : ISongInfo) => {
            let current = this.selectedSong();
            if (item.id == current.id) {
                this.page('lyrics');
            }
            else {
                let idx = this.songList.findIndex((song: ISongInfo) => {
                    return (song.id === item.id);
                })
                this.setSongIndex(idx);
                this.getLyrics();
            }
        };

        nextSong = () => {
            let songIndex = this.songIndex == this.songCount - 1 ? 0 : ++this.songIndex;
            this.setSongIndex(songIndex);
            this.pageController.gotoIndex(songIndex)
/*
            if (songIndex > this.pageController.pageEnd) {
                this.pageController.changePage(1)
            }
*/
            this.getLyrics();
        };

        prevSong = () => {
            let songIndex = this.songIndex == 0 ? this.songCount-1 : --this.songIndex;
            this.setSongIndex(songIndex);
            this.pageController.gotoIndex(songIndex);
/*
            if (songIndex < this.pageController.pageStart && this.pageController.pageStart > 1) {
                this.pageController.changePage(-1)
            }
*/
            this.getLyrics();
        };

        updateSongForm = () => {

        }
        getLyrics = () => {
            let me = this;
            let song = me.selectedSong();
            me.loading(song.title);
            let request = null;
            me.services.executeService('Peanut.songs::GetSongLyrics', song.id, (serviceResponse: IServiceResponse) => {
                if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                    let response = <IGetVersesResponse>serviceResponse.Value;
                    me.songForm.lyrics(response.lyrics);
                    me.songForm.notes(response.notes);
                    me.page('lyrics');
                }
            })
                .fail(() => {
                    let trace = me.services.getErrorInformation();
                })
                .always(() => {
                    me.loading('');
                });
        };

        showSongList = () => {
            this.page('songs')

        }

        splitColumns = () => {

        }

        reduceFont = () => {

        }

        enlargeFont = () => {

        }

        help = () => {

        }

        confirmSaveModal : any;
        onSaveLyrics = () => {
            if (!this.confirmSaveModal) {
                this.confirmSaveModal = new bootstrap.Modal(document.getElementById('confirm-save-modal'));
            }
            this.confirmSaveModal.show();
        }

        onConfirmSaveOk = () => {
            let me = this;
            me.confirmSaveModal.hide( );
            let song = me.selectedSong();
            alert('save: '+song.title)
            let request = {
                id: song.id,
                lyrics:  me.songForm.lyrics()
            }

            me.services.executeService('Peanut.songs::UpdateSongLyrics', song.id, (serviceResponse: IServiceResponse) => {
                if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                    me.songForm.lyrics(serviceResponse.Value);
                    me.page('lyrics');
                }
            })
                .fail(() => {
                    let trace = me.services.getErrorInformation();
                })
                .always(() => {
                    me.loading('');
                });

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
        handleContentNotification(contentId: string, message: string) {

        }
        afterDatabind = () => {
            this.contentController.initialize();
        }


    }
}
