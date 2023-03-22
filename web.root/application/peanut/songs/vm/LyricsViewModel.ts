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

    interface IGetSongListsReponse {
        setSongs: ISongInfo[];
        availableSongs: ISongInfo[];
    }

    interface IGetSongsRequest {
        setId : any;
        songId?: any;
        initializing? : any;
    }
    interface IGetLyricsResponse {
        // title: string;
        lyrics: string;
        notes: string;
        public?: any;
    }

    interface IGetSongSetResponse extends IGetLyricsResponse {
        songs: ISongInfo[];
        catalogSize: number;
    }
    
    interface IGetSongsResponse extends IGetSongSetResponse {
        set: ISongSet;
        sets: ISongSet[];
        canedit: any;
        username: string;
        songIndex? : any;
    }

    interface ISaveSetRequest {
        setId: any;
        songs: ISongSetItem[];
        setName: string;
        user?: string;
    }

    interface ISongSetItem {
        songId: any,
        sequence: number
    }

    interface ISaveSetRequest {
        setId: any;
        songs: ISongSetItem[];
        setName: string;
        user?: string;
    }

    interface ISaveSetResponse {
        setId: any,
        songs: ISongInfo[];
    }

    interface ISongUpdateResponse {
        id: any;
        songs: ISongInfo[];
    }

    interface ISongUpdateRequest extends ISong {
        setId: any;
        user?: string;
    }

    export class LyricsViewModel
        extends Peanut.ViewModelBase
        implements IContentOwner{
        // observables
        page = ko.observable('lyrics');
        title = ko.observable('Song not loaded');
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
        availableSongs: ISongInfo[];
        searchSubscription : any = null;

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
        editMode = ko.observable(false);
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
        showEditButton : KnockoutComputed<boolean>;

        showInfoButton = ko.observable(true);
        canDelete = ko.computed(() => {
            return this.editMode() && !!this.songForm.id();
        })

        savedSong : any = null;
        init(successFunction?: () => void) {
            let me = this;
            Peanut.logger.write('Lyrics Init');
            let songid = me.getPageVarialble('songid');


            // temp for initial text
            for (let i = 0; i < 4; i++) {
                this.songs[i] = ko.observableArray([]);
            }

            me.dragging = ko.computed(() => {
                return (me.dragStartIndex() >= 0);
            });

            me.showEditButton = ko.computed(() => {
                return (
                    this.canedit() && !this.editMode()
                );
            })

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
                        let setId = Peanut.Helper.getRequestParam('set');

                    let request = <IGetSongsRequest> {
                        setId: setId === null ? 0 : setId,
                        songId: songid === null ? 0 : songid,
                        initializing: 1
                    }
                    me.services.executeService('Peanut.songs::GetSongs', request,
                        (serviceResponse: IServiceResponse) => {
                            if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                                // todo: deal with empty song list
                                let response = <IGetSongsResponse>serviceResponse.Value;
                                me.maxSongColumnItems = Math.floor(response.catalogSize / 4);
                                me.username(response.username);
                                me.signedIn(response.username !== 'guest');
                                me.canedit(!!response.canedit);
                                me.sets(response.sets);
                                me.selectedSet(response.set);
                                me.loadSongList(response.songs,response.songIndex);
                                me.loadSongLyrics(response);
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
        getAvailableSongsList = () => {
/*
            let me = this;
            if (me.filterByUser()) {
                let user = me.username();
                return _.filter(this.availableSongs,(item: ISongInfo) => {
                    return (item.user == user);
                });
            }
*/
            return this.availableSongs;
        };


        loadSongSet = () => {
            
        }
        
        loadSongLyrics = (response: IGetLyricsResponse) => {
            this.songForm.lyrics(response.lyrics);
            let notes = (response.notes ??  '').trim();
            this.songForm.notes(notes);
            this.showInfoButton(
                (this.canedit() || notes.length > 0)
            );
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
            this.title(current.title + ' - ' + current.id);
            this.songForm.title(current.title);
            this.songForm.id(current.id);
        };

        goPage = (pageName: string) => {
            if (this.editMode()) {
                // todo: confirm lose changes?
                this.editMode(pageName == 'lyrics')
            }
            this.page(pageName);
        }

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
                    let response = <IGetLyricsResponse>serviceResponse.Value;
                    // todo: set public domain
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
            let current = this.columnDisplay();
            this.columnDisplay(!current)
        }

        reduceFont = () => {
            let size = this.textSize();
            if (size > 1) {
                this.textSize(--size)
            }
        }

        enlargeFont = () => {
            let size = this.textSize();
            if (size < 6) {
                this.textSize(++size)
            }
        }

        help = () => {
            alert('to be implemented')
        }

        confirmSaveModal : any;
        onSaveLyrics = () => {
            if (!this.confirmSaveModal) {
                this.confirmSaveModal = new bootstrap.Modal(document.getElementById('confirm-save-modal'));
            }
            this.confirmSaveModal.show();
        }

        selectSet = (set: ISongSet) => {
            let me = this;
            me.selectedSet(set);
            me.loadSelectedSet();
        };

        loadSelectedSet = () => {
            let me = this;
            let set = me.selectedSet();
            let request = <IGetSongsRequest>{
                setId: set.id ? set.id : 0
            }

            me.loading(set.setname);
            me.services.executeService('Peanut.songs::GetSongs',
                request, (serviceResponse: IServiceResponse) => {
                if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                    // todo: deal with empty set
                    let response = <IGetSongSetResponse>serviceResponse.Value;
                    this.loadSongList(response.songs);
                    this.loadSongLyrics(response);
                }
            })
                .fail(() => {
                    let trace = me.services.getErrorInformation();
                })
                .always(() => {
                    // me.page('lyrics');
                    // me.page('songs');
                    me.loading('');
                });


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

        // set editing

        editSet   = (set: ISongSet) => {
            let me = this;
            me.setForm.id(set.id);
            me.setForm.setName(set.setname);
            me.setForm.nameError('');
            // me.setForm.user = set.user;
            me.initSetLists(set.id);

        }
        moveSongUp = (song: ISongInfo) => {
            this.moveSong(song,-1);
        };

        moveSongDown = (song: ISongInfo) => {
            this.moveSong(song,1);
        };

        insertSong = (song: ISongInfo) => {
            let list = [song].concat(this.setForm.selectedSongs());
            this.setForm.selectedSongs(list);
        }

        moveSong = (song: ISongInfo, offset: number) => {
            let list = this.setForm.selectedSongs();
            //let oldI = _.findIndex(list, {id: song.id});
            // let oldI =  Peanut.Helper.FindIndex(list, (s: ISongInfo) =>
            let oldI =  list.findIndex((s: ISongInfo) =>
                            {
                                return s.id === song.id
                            });

            let newI = oldI + offset;
            if (newI < 0) {
                newI = list.length - 1;
            }
            else if (newI == list.length) {
                newI = 0;
            }
            let swapped = list[newI];
            list[newI] = song;
            list[oldI] = swapped;
            this.setForm.selectedSongs(list);
        };

        reorderSong = (song : ISongInfo,swapped: ISongInfo) => {
            let list = this.setForm.selectedSongs();
            // let start = _.findIndex(list, {id: song.id});
            let start = Peanut.Helper.FindIndex(list, (s: ISongInfo) =>
                {
                    return s.id === song.id
                });

            for (let i= start; i< list.length; i++) {
                song = list[i];
                list[i] = swapped;
                swapped = song;
            }
            this.setForm.selectedSongs(list);
        }

        confirmSongDeleteModal : any;
        deleteSong = () => {
            if (!this.confirmSongDeleteModal) {
                this.confirmSongDeleteModal = new bootstrap.Modal(document.getElementById('confirm-song-delete-modal'));
            }
            this.confirmSongDeleteModal.show();
        }

        doDeleteSong = () => {
            let me = this;
            me.confirmSongDeleteModal.hide();
            let id = me.songForm.id();
            me.services.executeService('DeleteSongLyrics', id, (serviceResponse: IServiceResponse) => {
                if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                    // todo: restore state:
                    /**
                     * Refresh current set and list
                     * select inital song
                     */
                    let songs = this.songList.filter((s: ISongInfo) => {
                        return s.id !== id;
                    })
                    me.loadSongList(songs);
                    me.page('songs')
                }
            })
                .fail(() => {
                    let trace = me.services.getErrorInformation();
                    if (1){} // set breakpoint here
                })
                .always(() => {
                    me.page('songs');
                });

        }

        addToSetList = (song: ISongInfo) => {
            let me=this;
            // me.setForm.selectedSongs.push(song);
            me.insertSong(song);
            me.availableSongs = me.availableSongs.filter((s: ISongInfo) => {
                return s.id !== song.id
            })
            me.setForm.avaliableSongs(me.availableSongs);
            me.setForm.searchValue('');
        };

        removeFromSetList = (song: ISongInfo) => {
            let me = this;
            me.availableSongs.push(song);
            // me.availableSongs = _.sortBy(me.availableSongs,['title']);
            me.availableSongs = Peanut.Helper.SortByAlpha(me.availableSongs,'title')

            let selected = this.setForm.selectedSongs();
            selected = selected.filter((item: ISongInfo) => {
                    return item.id !== song.id
                }
            );
            me.setForm.selectedSongs(selected);
            me.setForm.avaliableSongs(me.availableSongs);
            me.setForm.searchValue('');
        };

        clearSearch = () => {
            if (this.searchSubscription !== null) {
                this.searchSubscription.dispose();
                this.searchSubscription = null;
            }
            this.setForm.searchValue('');
            let available = this.getAvailableSongsList();
            this.setForm.avaliableSongs(available);
            this.searchSubscription = this.setForm.searchValue.subscribe(this.filterAvailable)
        };



        filterAvailable = (value: string) => {
            value = value.trim();
            if (value) {
                let songs = this.setForm.avaliableSongs();
                let list = songs.filter((item: ISongInfo) => {
                    return item.title.toLowerCase().indexOf(value.toLowerCase()) == 0;
                });
                this.setForm.avaliableSongs(list);
            }
            else {
                let available = this.getAvailableSongsList();
                this.setForm.avaliableSongs(available);
            }
        };

        initSetLists(setId) {
            let me = this;
            me.setForm.selectedSongs([]);
            me.setForm.avaliableSongs([]);

            me.services.executeService( 'GetSongLists', setId, (serviceResponse: IServiceResponse) => {
                if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                    let response = <IGetSongListsReponse>serviceResponse.Value;
                    let selected = [];
                    if (setId) {
                        me.availableSongs = response.availableSongs;
/*
                            Peanut.Helper.ExcludeValues(response.availableSongs,
                                response.setSongs,'id');
*/
                        me.setForm.selectedSongs(response.setSongs);
                    }
                    else {
                        me.availableSongs = response.availableSongs;
                        me.setForm.selectedSongs([]);
                    }
                    me.setForm.avaliableSongs(me.availableSongs);
                    me.clearSearch();
                    me.page('editset');
                }
                else {
                }
            })
                .fail(() => {
                    let trace = me.services.getErrorInformation();
                });

        };


        newSet = () => {
            this.setForm.id (null);
            this.setForm.setName('');
            this.setForm.user = this.username();
            this.initSetLists([]);
        };

        saveSongData = () => {
            this.savedSong = {
                id: this.songForm.id(),
                title: this.title(),
                lyrics: this.songForm.lyrics(),
                public: this.songForm.public(),
                user: this.songForm.user()
            }
        }
         newSong = () => {
            this.saveSongData();
            this.title('New Song')
            this.songForm.id(0);
            this.songForm.title('');
            this.songForm.lyrics('');
            this.songForm.public(false);
            this.songForm.errorMessage('');
            this.songForm.user(this.username());
            this.songForm.notes('');
            let currentSetName = this.selectedSet().id > 0 ? this.selectedSet().setname : '';
            this.songForm.currentSetName(currentSetName);
            this.songForm.includeInSet(currentSetName != '');
            this.editMode(true);
            this.page('lyrics');
        };

        home = () => {
            this.goPage('lyrics');
        };

        saveSetList = () => {
            let me = this;
            let request = <ISaveSetRequest> {
                setId: this.setForm.id(),
                songs: [],
                setName: this.setForm.setName().trim(),
                user: this.setForm.user
            };

            if (!request.setId) {
                request.setId = 0;
            }

            if (!request.setName) {
                this.setForm.nameError('A name for the set is required.');
                return;
            }

            let selected  = this.setForm.selectedSongs();
            for (let i = 0; i < selected.length; i++) {
                request.songs.push(
                    <ISongSetItem>{songId: selected[i].id, sequence: i}
                )
            }

            this.setForm.nameError('');

            // me.loading(set.setname);
            me.services.executeService('UpdateSongSet', request, (serviceResponse: IServiceResponse) => {
                if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                    let response = <ISaveSetResponse>serviceResponse.Value;
                    let setList = me.sets();
                    me.sets([]);
                    let set : ISongSet = null;
                    if (request.setId > 0) {
                        let setIndex =
                            setList.findIndex(
                                (s: ISongSet) => {
                                    return s.id === request.setId
                                });
                        set = setList[setIndex];
                        set.setname = request.setName;
                        setList[setIndex] = set;
                    }
                    else {
                        set = <ISongSet>{
                            id: response.setId,
                            setname: request.setName,
                            user: request.user
                        };
                        setList.push(set);
                    }
                    me.sets(setList);
                    me.selectedSet(set);
                    me.loadSongList(response.songs);
                }
            })
                .fail(() => {
                    let trace = me.services.getErrorInformation();
                    if (1){} // set breakpoint here
                })
                .always(() => {
                    me.page('songs');
                });
        };

        cancelSetEdit = () => {
            this.page('songs');
        };

        confirmSetDeleteModal : any;
        deleteSet = () => {
            if (!this.confirmSetDeleteModal) {
                this.confirmSetDeleteModal = new bootstrap.Modal(document.getElementById('confirm-set-delete-modal'));
            }
            this.confirmSetDeleteModal.show();
        };

        doDeleteSet = () => {
            let me = this;
            me.confirmSetDeleteModal.hide();
            let setId = me.setForm.id();
            me.services.executeService('RemoveSet', setId, (serviceResponse: IServiceResponse) => {
                if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                    // let response = <ISongUpdateResponse>serviceResponse.Value;
                    let invalidateSelected = me.selectedSet().id == setId;
                    let setList = me.sets();
                    setList = setList.filter((item: ISongSet) => {
                        return item.id !== setId;
                    })
                    if (me.selectedSet().id == setId) {
                        me.selectedSet(setList[0]);
                    }
                    me.sets(setList);
                }
            })
                .fail(() => {
                    let trace = me.services.getErrorInformation();
                    if (1){} // set breakpoint here
                })
                .always(() => {
                    me.page('songs');
                });
        };

        toggleUser  = () => {

        }

        filterByUser  = () => {

        }
        handleContentNotification(contentId: string, message: string) {

        }
        afterDatabind = () => {
            this.contentController.initialize();
        }

        editSong = () => {
            this.saveSongData();
            this.editMode(true);
        }

        saveSong = () => {
            // todo: confirm save?
            this.doSaveSong();
        }

        doSaveSong = () => {
            let test = this.songForm.lyrics();
            let me = this;

            let request = <ISongUpdateRequest> {
                id: me.songForm.id(),
                title: me.songForm.title().trim(),
                publicdomain: me.songForm.public() ? 1 : 0,
                user: me.songForm.user(),
                notes: me.songForm.notes(),
                lyrics: me.songForm.lyrics().trim(),
                setId: me.songForm.includeInSet() ? me.selectedSet().id : 0,
            }

            if (this.songForm.includeInSet() && this.selectedSet().id > 0) {
                request.setId = this.selectedSet().id;
            }

            if (!request.title) {
                me.songForm.errorMessage('Title is required');
                return;
            }
            if (!request.lyrics) {
                me.songForm.errorMessage('Add some lyrics please.');
                return;
            }
            me.services.executeService('UpdateSong', request, (serviceResponse: IServiceResponse) => {
                if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                    let response = <ISongUpdateResponse>serviceResponse.Value;
                    //todo: recover state:
                    /**
                     * refresh current set or full list
                     * if updated
                     *      select index of updated song, show lyrics
                     * if inserted
                     *      select index of current song, show lyrics
                     */

                    // let songIndex = _.findIndex(response.songs, {id: response.id});
                    let songIndex = response.songs.findIndex((song: ISongInfo) => {
                        return (song.id === response.id);
                    })
                    if (songIndex < 0) {
                        songIndex = 0;
                    }
                    me.loadSongList(response.songs,songIndex);
                    if (request.setId === 0) {
                        me.selectedSet(me.sets()[0]);
                    }
                    // me.songIndex = -1;
                }
            })
                .fail(() => {
                    let trace = me.services.getErrorInformation();
                    if (1){} // set breakpoint here
                })
                .always(() => {
                    this.editMode(false);
                    me.page('songs');
                });
        };

        cancelSongEdit = () => {
            if (this.editMode()) {
                // todo: confirm cancel?
                this.doCancelEdit();
            }
        }

        doCancelEdit = () => {
            // todo: cancel any field edits in progress.
            this.editMode(false);
            this.restoreSavedSong();
        }

        restoreSavedSong = () => {
            if (this.savedSong) {
                this.songForm.id(this.savedSong.id),
                this.songForm.title(this.savedSong.title),
                this.songForm.lyrics(this.savedSong.lyrics),
                this.songForm.public(this.savedSong.public),
                this.songForm.user(this.savedSong.user)
                this.title(this.savedSong.title)
                this.savedSong = null;
            }
            this.setSongIndex(this.songIndex);
        }

        onDragstart = (data: ISongInfo, event) => {
            this.draggedSong(data);
            event.target.classList.add('dragSource');
            // console.log('drag started');
            return true;
        };

        onDragend = (data, event) => {
            // console.log('drag ended');
            event.target.classList.remove('dragSource');
            this.draggedSong(null);
            return true;

        };

        onDragover =(data, event) =>{
            // console.log('drag over');
            event.preventDefault();
        };

        onDragenter =(data, event) => {
            // console.log('drag enter');
            event.preventDefault();
        };

        onDragleave = (data, event, index) => {
            // console.log('drag leave');
            event.preventDefault();
        };

        onDrop = (target:ISongInfo, event) => {
            // console.log('dropped')
            let source =  this.draggedSong();
            this.draggedSong(null);
            if (target != null && target.id != source.id) {
                let list = this.setForm.selectedSongs();
                let targetIdx = list.findIndex((s: ISongInfo) => {
                    return s.id === target.id;
                });
                let sourceIdx =  list.findIndex((s: ISongInfo) => {
                    return s.id === source.id;
                });
                if (targetIdx > sourceIdx) {
                    for (let i= targetIdx; i >= sourceIdx; i--) {
                        let song = list[i];
                        list[i] = source;
                        source = song;
                    }
                }
                else {
                    for (let i= targetIdx; i <= sourceIdx; i++) {
                        let song = list[i];
                        list[i] = source;
                        source = song;
                    }
                }

                this.setForm.selectedSongs(list);
            }

            event.target.classList.remove('dragover');

            // console.log('drag dropped');

            return true;
        };
    }
}
