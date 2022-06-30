/// <reference path='../../../../nutshell/pnut/core/ViewModelBase.ts' />
/// <reference path='../../../../nutshell/typings/knockout/knockout.d.ts' />
/// <reference path='../js/songs.d.ts' />
/// <reference path='../../../../nutshell/pnut/core/peanut.d.ts' />
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
            me.introductionLength = ko.computed(() => {
                return me.introduction().trim().length;
            })

        }

        introductionLength: KnockoutComputed<any>;
        userCanEdit : boolean = false;
        typesController : multiSelectObservable;
        editMode = ko.observable(false);

        songid = ko.observable();
        newSong = ko.observable(false);
        newPage = ko.observable(false);
        pageid = ko.observable();
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
        hasicon = ko.observable(true);
        publicDomain = ko.observable(true);
        active = ko.observable(true);
        errorMessage = ko.observable('');
        defaultImageName = ko.observable('');
        thumbnailImageName = ko.observable('');

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

            this.assignSong(page.song);
            
            this.introduction(page.introduction);
            this.commentary(page.commentary);
            this.postedDate(page.postedDate);
            this.pageimage(page.pageimage);
            this.imagecaption(page.imagecaption ?? '');
            this.youtubeId(page.youtubeId);
            this.hasicon(page.hasicon == 1);
            if (page.song.contentid) {
                this.defaultImageName(page.song.contentid+ '.jpg');
            }
            else {
                this.defaultImageName('default.jpg');
            }
            this.active(page.active == 1);
        }
        
        assignSong = (song: ISong) => {
            let songid = parseInt(song.id);
            this.errorMessage('');
            this.newSong(songid === 0);
            this.songid(songid);
            this.contentId(song.contentid);
            this.description(song.description);
            this.title(song.title);
            this.lyrics(song.lyrics);
            this.publicDomain(song.publicdomain == 1);
        }

        clear = () => {
            this.editMode(false);
            this.errorMessage('');
            this.pageid(0);
            this.clearSong();

            this.typesController.setValues([]);
            this.introduction('');
            this.commentary('');
            this.postedDate('');
            this.pageimage('');
            this.imagecaption('');
            this.youtubeId('');
            this.hasicon(false);
            this.active(true);
        }

        clearSong = () => {
            this.songid(0);
            this.contentId('');
            this.description('');
            this.title('');
            this.lyrics('');
        }

        validate = () => {
            this.errorMessage('');
            let request: ISongPage = {
                id : this.pageid(),
                active: this.active(),
                youtubeId: this.youtubeId(),
                song: this.getSongObject(),
                imagecaption: this.imagecaption().trim(),
                pageimage: this.pageimage(),
                postedDate: this.postedDate(),
                commentary: this.commentary().trim(),
                types: this.typesController.getValues(),
                introduction: this.introduction().trim()
            };

            if (!request.introduction) {
                this.errorMessage('Introduction required');
                return false;
            }

            if (!request.song.title) {
                this.errorMessage('Song title is required');
                return false;
            }
            if (!request.song.description) {
                this.errorMessage('Song description is required');
                return false;
            }

            return request;

        }

        getSongObject = () => {
            return <ISong> {
                lyrics: (this.lyrics() === null) ? '' :  this.lyrics().trim(),
                title: (this.title() === null) ? '' : this.title().trim(),
                description: (this.description() === null) ? '' : this.description().trim(),
                contentid: (this.contentId() === null) ? '' : this.contentId().trim(),
                id: this.songid(),
                publicdomain: this.publicDomain() ? 1 : 0
            }
        }
        edit = () => {
            if (this.userCanEdit) {
                this.editMode(true)
            }
        }

        cancel = () => {
            this.revert();
            this.editMode(false);
        }
    }

    export class SongpageViewModel extends Peanut.ViewModelBase
        implements IContentOwner, IImageComponentOwner {
        contentid = ko.observable('Mars');
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
                else {
                    this.songsLink(returnlink);
                }
            }

            let songid = me.getPageVarialble('songid');
            if (!songid) {
                alert('Song id not found');
                return;
            }
            me.contentid(songid);
            me.application.loadResources([
                '@pnut/multiSelectObservable',
                '@pkg/peanut-content/contentController.js',
                '@lib:tinymce',
                '@pnut/ViewModelHelpers.js',
                '@pkg/peanut-youtube/YTFrameController.js'
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
                    '@pnut/selected-list'
                ], () => {
                    me.contentController = new PeanutContent.contentController(me);
                    me.services.executeService('Peanut.songs::GetSongPage',songid,
                        (serviceResponse: Peanut.IServiceResponse) => {
                            if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                                let response = <IGetSongPageInitResponse>serviceResponse.Value;
                                me.songTypeLinks(response.songTypeLinks);
                                me.latestSongs(response.latest);
                                me.songform = new SongPageObservable(response.canedit,
                                    response.types,response.page);
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
            this.confirmSaveModal.hide();
            let request = this.songform.validate();
            if (request === false) {
                return;
            }
            this.songform.editMode(false)

            let me = this;
            me.application.hideServiceMessages();
            me.application.showWaiter('Message here...');
            me.services.executeService('Peanut.songs::UpdateSongPage',request,
                (serviceResponse: Peanut.IServiceResponse) => {
                    if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                        me.application.hideWaiter();

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
    }

}
