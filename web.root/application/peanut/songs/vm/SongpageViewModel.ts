/// <reference path='../../../../nutshell/pnut/core/ViewModelBase.ts' />
/// <reference path='../../../../nutshell/typings/knockout/knockout.d.ts' />
/// <reference path='../js/songs.d.ts' />
/// <reference path='../../../../nutshell/pnut/core/peanut.d.ts' />
/// <reference path='../../../../nutshell/pnut/js/ViewModelHelpers.ts' />
/// <reference path='../../../../nutshell/pnut/js/multiSelectObservable.ts' />
/// <reference path='../../../../nutshell/pnut/packages/peanut-content/js/contentController.ts' />
/// <reference path='../../../../nutshell/pnut/packages/peanut-youtube/js/YTFrameController.ts' />
/// <reference path='../../../../nutshell/typings/bootstrap-5/index.d.ts' />
/// <reference path='../../../../nutshell/pnut/packages/peanut-content/js/peanutcontent.d.ts' />

// Module
namespace Peanut {
    import IContentOwner = PeanutContent.IContentOwner;
    import IImageComponentOwner = PeanutContent.IImageComponentOwner;

    interface IGetSongPageResponse {
        page: ISongPage;
    }

    interface IGetSongPageInitResponse extends IGetSongPageResponse{
        types?: ILookupItem[],
        songTypeLinks: ILinkListItem[],
        latest : ISongListItem[],
        canedit: any
    }

    class SongPageObservable{
        constructor(canEdit: boolean, typesLookup: ILookupItem[],page?: ISongPage) {
            let me = this;
            me.typesController = new multiSelectObservable(typesLookup);
            me.userCanEdit = canEdit;
            if (page) {
                this.assign(page);
            }
            else {
                if (me.userCanEdit) {
                    me.assignNewPage();
                }
                else {
                    alert("User does not have permission to create a new song page.");
                    window.location.replace("/");
                }
            }
            me.introductionLength = ko.computed(() => {
                return me.introduction().trim().length;
            })

            me.showLyricsHeading = ko.computed(() => {
                return me.newPage() || me.lyrics().length > 0;
            })

            me.showFormattedLyrics = ko.computed(() => {
                return me.editMode() || (me.lyricsformatted().trim().length > 0)
            })

            me.showLyrics = ko.computed(() => {
                return me.editMode() || (me.lyricsformatted().trim().length == 0)
            })
        }
        introductionLength: KnockoutComputed<any>;
        userCanEdit : boolean = false;
        typesController : multiSelectObservable;
        editMode = ko.observable(false);
        showLyricsHeading : KnockoutComputed<boolean>
        showLyrics : KnockoutComputed<boolean>
        showFormattedLyrics : KnockoutComputed<boolean>
        songid = ko.observable(0);
        newSong = ko.observable(false);
        newPage = ko.observable(false);
        pageid = ko.observable(0);
        contentId = ko.observable('');
        title = ko.observable('');
        description = ko.observable('');
        lyrics = ko.observable('');
        introduction = ko.observable('');
        commentary = ko.observable('');
        postedDate = ko.observable('');
        pageimage = ko.observable('');
        imagecaption = ko.observable('');
        youtubeId = ko.observable('');
        lyricsformatted = ko.observable('');
        notes = ko.observable('');
        hasicon = ko.observable(true);
        publicDomain = ko.observable(true);
        active = ko.observable(true);
        errorMessage = ko.observable('');
        defaultImageName = ko.observable('');
        thumbnailImageName = ko.observable('');
        songAssigned = ko.observable(false);

        original : ISongPage;

        revert = ()  => {
            this.assign(this.original);
        }

        assign = (page: ISongPage) => {

            this.original = page;
            this.errorMessage('');
            this.typesController.setValues(page.types);
            this.editMode(false);

            let pageId = parseInt(page.id);
            this.pageid(pageId);
            this.newPage(pageId === 0);
            this.contentId(page.contentId);
            this.description(page.description);

            this.assignSong(page.song);
            
            this.introduction(page.introduction);
            this.commentary(page.commentary);
            this.postedDate(page.postedDate);
            this.pageimage(page.pageimage);
            this.imagecaption(page.imagecaption ?? '');
            this.youtubeId(page.youtubeId);
            this.lyricsformatted(page.lyricsformatted ?? '');
            this.hasicon(page.hasicon == 1);
            if (page.contentId) {
                this.defaultImageName(page.contentId+ '.jpg');
            }
            else {
                this.defaultImageName('default.jpg');
            }
            this.active(page.active == 1);
        }
        
        assignSong = (song: ISong) => {
            let songid = parseInt(song.id);
            this.songAssigned(songid !== 0);
            this.errorMessage('');
            this.newSong(songid === 0);
            this.songid(songid);
            this.title(song.title);
            this.lyrics(song.lyrics);
            this.notes(song.notes ?? '');
            this.publicDomain(song.publicdomain == 1);
        }

        assignNewPage = () => {
            this.newSong(true);
            this.newPage(true);
            this.editMode(true);
            this.errorMessage('');
            this.pageid(0);
            this.contentId('');
            this.description('');
            this.clearSong();
            this.typesController.setValues([]);
            this.introduction('');
            this.commentary('');
            this.postedDate(Peanut.Helper.todayToISODate());
            this.pageimage('');
            this.imagecaption('');
            this.youtubeId('');
            this.hasicon(false);
            this.songAssigned(false);
            this.active(true);
        }

        clearSong = () => {
            this.songid(0);
            this.songAssigned(false);
            this.title('');
            this.lyrics('');
        }

        validate = () => {
            this.errorMessage('');
            let request: ISongPageUpdateRequest = {
                id : this.pageid(),
                active: this.active(),
                youtubeId: this.youtubeId(),
                description: (this.description() === null) ? '' : this.description().trim(),
                contentId: (this.contentId() === null) ? '' : this.contentId().trim(),
                song: this.getSongObject(),
                imagecaption: this.imagecaption().trim(),
                pageimage: this.pageimage(),
                postedDate: this.postedDate(),
                commentary: this.commentary().trim(),
                types: this.typesController.getValues(),
                introduction: this.introduction().trim(),
                lyricsformatted: this.lyricsformatted().trim()
            };

            if (!request.introduction) {
                this.errorMessage('Introduction required');
                return false;
            }

            if (!request.song.title) {
                this.errorMessage('Song title is required');
                return false;
            }

            if (!request.contentId) {
                this.errorMessage('Song contentId is required');
                return false;
            }

            if (!request.description) {
                this.errorMessage('Song description is required');
                return false;
            }

            return request;

        }

        getSongObject = () => {
            let notes = this.notes();
            return <ISong> {
                id: this.songid(),
                lyrics: (this.lyrics() === null) ? '' :  this.lyrics().trim(),
                title: (this.title() === null) ? '' : this.title().trim(),
                notes: (this.notes() === null) ? '' : this.notes().trim(),
                publicdomain: this.publicDomain() ? 1 : 0
            }
        }
        edit = () => {
            if (this.userCanEdit) {
                this.editMode(true)
            }
        }

        cancel = () => {
            if (this.newPage()) {
                window.location.replace("/");
            }
            else {
                this.revert();
                this.editMode(false);
            }
        }
    }

    export class SongpageViewModel extends Peanut.ViewModelBase
        implements IContentOwner, IImageComponentOwner {
        contentId = ko.observable('');
        returnLink = ko.observable('');
        songsLink = ko.observable('/songs');
        returnTitle = ko.observable('');
        songTypeLinks = ko.observableArray<ILinkListItem>();
        latestSongs = ko.observableArray<ISongListItem>();
        // typesSelectController : IMultiSelectObservable;
        songform : SongPageObservable;
        canedit = ko.observable(false);

        contentController: PeanutContent.contentController;
        // youtube
        youtubeController : PeanutYoutube.YTFrameController;
        videoOn = ko.observable(false);
        videoModal : any;
        youTubeEditModal : any;
        captionModal : any;
        dateModal : any;
        player: YT.Player;
        playerElementId = 'video-frame-1';
        editBuffer = ko.observable('');

        confirmSaveModal: any;
        confirmCancelModal: any;
        unassignedSongs = ko.observableArray<ILookupItem>();
        showSongSelection = ko.observable(false);

        init(successFunction?: () => void) {
            let me = this;
            Peanut.logger.write('Songpage Init');

            let ref = this.getLocalReferrer();
            if (ref) {
                let returnlink = this.fetchSessionItem('songsearch');
                let retTitle = this.fetchSessionItem('songsearchtitle');
                if (retTitle) {
                    this.returnTitle(retTitle);
                    this.returnLink(returnlink);
                }
                else if (returnlink) {
                    this.songsLink(returnlink);
                }
            }

            let contentId = me.getPageVarialble('contentId');
            if (!contentId) {
                alert('Content id not found');
                return;
            }
            me.contentId(contentId);
            me.application.loadResources([
                '@pnut/multiSelectObservable',
                '@pkg/peanut-content/contentController.js',
                '@lib:tinymce',
                '@pnut/ViewModelHelpers.js',
                '@pkg/peanut-youtube/YTFrameController.js',
                '@pnut/ViewModelHelpers.js'
            ], () => {
                me.application.registerComponents([
                    '@pnut/pager',
                    '@pnut/lookup-select',
                    '@pnut/multi-select',
                    '@pnut/modal-confirm',
                    '@pnut/clean-html',
                    '@pkg/peanut-content/content-block',
                    '@pkg/peanut-content/image-block',
                    '@pkg/peanut-youtube/youtube-frame',
                    'songs/lyrics-block',
                    '@pnut/selected-list',
                    '@pnut/incremental-select'
                ], () => {
                    me.contentController = new PeanutContent.contentController(me);
                    me.services.executeService('Peanut.songs::GetSongPage',contentId,
                        (serviceResponse: Peanut.IServiceResponse) => {
                            if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                                let response = <IGetSongPageInitResponse>serviceResponse.Value;
                                me.songTypeLinks(response.songTypeLinks);
                                me.latestSongs(response.latest);
                                me.songform = new SongPageObservable(response.canedit,
                                    response.types,response.page ?? null);
                                me.canedit(response.canedit);

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

        handleContentNotification(contentId: string, message: string) {
            // console.log(contentId = ': '+message);
        }

        mousingOverButton = ko.observable(true);
        toggleMoreSongs = () => {
            let state = this.mousingOverButton();
            this.mousingOverButton(!state);
        }

        afterDatabind = () => {
            this.contentController.initialize();
            PeanutYoutube.YTFrameController.initApi();
            this.youtubeController = PeanutYoutube.YTFrameController.instance;
        }

        edit = () => {
            this.songform.editMode(true)
        }

        save = () => {
            let me = this;
            this.confirmSaveModal.hide();
            let request = this.songform.validate();
            if (request === false) {
                return;
            }
            if (this.songform.newSong()) {
                me.services.executeService('Peanut.songs::ValidateNewSong',
                    <INewSongValidationRequest> {
                        title: request.song.title,
                        contentId: request.contentId
                    },
                    (serviceResponse: Peanut.IServiceResponse) => {
                        if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                            me.application.hideWaiter();
                            if (serviceResponse.Value === 'ok') {
                                this.uploadChanges(<ISongPageUpdateRequest>request);
                            }
                            else {
                                me.songform.errorMessage(serviceResponse.Value)
                            }
                        }
                    }
                ).fail(() => {
                    // let trace = me.services.getErrorInformation();
                    me.application.hideWaiter();
                });
            }
            else {
                this.uploadChanges(request);
            }
        }

        uploadChanges = (request: ISongPageUpdateRequest) => {
            this.songform.editMode(false)
            let me = this;
            me.application.hideServiceMessages();
            me.application.showWaiter('Message here...');
            me.services.executeService('Peanut.songs::UpdateSongPage',request,
                (serviceResponse: Peanut.IServiceResponse) => {
                    if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                        // refresh page
                        window.location.replace("/song/"+request.contentId);
                    }
                }
            ).fail(() => {
                // let trace = me.services.getErrorInformation();
                me.application.hideWaiter();
            });
        }

        // youtube routines
        showVideo  = () => {
            if (!this.videoModal) {
                let modalElement = document.getElementById('songPlayer1');
                modalElement.addEventListener('hidden.bs.modal',this.closeVideo);
                this.videoModal = new bootstrap.Modal(modalElement);
            }
            this.videoModal.show();
            this.videoOn(true);
        }

        showYoutubeEdit = () => {
            this.editBuffer(this.songform.youtubeId());
            if (!this.youTubeEditModal) {
                let modalElement = document.getElementById('youtube-edit');
                this.youTubeEditModal = new bootstrap.Modal(modalElement);
            }
            this.youTubeEditModal.show();
        }

        closeVideo  = () => {
            let player = this.getPlayer()
            let state = player.getPlayerState();
            if (state == YT.PlayerState.PLAYING) {
                player.pauseVideo();
            }
            this.videoOn(false);
        }


        getPlayer = () => {
            if (!this.player) {
                this.player = this.youtubeController.getPlayer(this.playerElementId);
            }
            return this.player;
        }

        createPlayers = () => {
            let id = this.playerElementId;
            this.player = new YT.Player(id, {
                events: {
                    'onReady': this.onPlayerReady,
                    'onStateChange': this.onPlayerStateChange
                }
            });
            // this.ready = true;
            return [<PeanutYoutube.IPlayerItem> {
                id: this.playerElementId,
                player: this.player
            }]
        }

        onPlayerReady = (_event) => {
            // this.status('ready');
        }

        onPlayerStateChange = (_event) => {
            // this.status(PeanutYoutube.YTFrameController.getPlayerStatus(event.data));
        }

        saveYoutubeCode = () => {
            let code = this.editBuffer();
            code = code.trim();
            if (code.length) {
                let parts = code.split('/');
                code = parts.pop();
            }
            this.songform.youtubeId(code);
            this.youTubeEditModal.hide();
        }

        saveCaption = () => {
            this.songform.imagecaption(this.editBuffer());
            this.captionModal.hide();
            this.editBuffer('');
        }

        editDate = () => {
            this.editBuffer(this.songform.postedDate())
            if (!this.dateModal) {
                let modalElement = document.getElementById('date-edit');
                this.dateModal = new bootstrap.Modal(modalElement);
            }
            this.dateModal.show();
        }

        saveDate = () => {
            this.songform.postedDate(this.editBuffer());
            this.dateModal.hide();
        }

        onFileSelected(files: any, imagePath: string, imageName: string) {
            // alert('File selected: ' + imagePath + '/' + imageName);
            let me=this;
            let request : PeanutContent.IImageUploadRequest = {
                imageurl: imagePath,
                filename: imageName
            }
            me.showWaitMessage('Uploading image');
            me.services.postForm( 'peanut.content::UploadImage', request, files, null,
                function (serviceResponse: Peanut.IServiceResponse) {
                    if (serviceResponse.Result == Peanut.serviceResultSuccess) {

                    }
                    else {
                    }
                }).fail(() => {
                // let trace = me.services.getErrorInformation();
            }).always(() => {
                me.application.hideWaiter();
            });
        }

        confirmSave = () => {
            if (!this.confirmSaveModal) {
                this.confirmSaveModal = new bootstrap.Modal(document.getElementById('confirm-save-modal'));
            }
            this.confirmSaveModal.show();
        }

        cancelEdit = () => {
            if (!this.confirmCancelModal) {
                this.confirmCancelModal = new bootstrap.Modal(document.getElementById('cancel-edit-modal'));
            }
            this.confirmCancelModal.show();
        }

        onConfirmCancel = () => {
            this.songform.cancel();
            this.confirmCancelModal.hide();
        }

        selectSong = () => {
            let me = this;
            me.showWaitMessage('Getting unassigned song list');
            me.services.executeService('Peanut.songs::GetUnassignedSongs',null,
                (serviceResponse: Peanut.IServiceResponse) => {
                    if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                        let songlist = <ILookupItem[]>serviceResponse.Value;
                        me.unassignedSongs(songlist)
                        me.showSongSelection(true);
                    }
                }
            ).fail(() => {
                // let trace = me.services.getErrorInformation();
            }).always(() => {
                me.application.hideWaiter();
            });
        }

        onSongSelected = (item: ILookupItem) => {
            let me = this;
            me.showSongSelection(false);
            me.showWaitMessage('Getting song...');
            me.services.executeService('Peanut.songs::GetSong',item.id,
                (serviceResponse: Peanut.IServiceResponse) => {
                    if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                        let song = <ISong>serviceResponse.Value;
                        this.songform.assignSong(song);
                        this.songform.songid(song.id);
                    }
                }
            ).fail(() => {
                // let trace = me.services.getErrorInformation();
            }).always(() => {
                me.application.hideWaiter();
            });
        }
    }

}
