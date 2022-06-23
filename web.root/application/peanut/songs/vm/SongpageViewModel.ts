/// <reference path='../../../../nutshell/pnut/core/ViewModelBase.ts' />
/// <reference path='../../../../nutshell/typings/knockout/knockout.d.ts' />
/// <reference path='../js/songs.d.ts' />
/// <reference path='../../../../nutshell/pnut/core/peanut.d.ts' />
/// <reference path='../../../../nutshell/pnut/js/multiSelectObservable.ts' />
/// <reference path='../../../../nutshell/pnut/packages/peanut-content/js/contentController.ts' />


// Module
namespace Peanut {
    import IContentOwner = PeanutContent.IContentOwner;

    interface IGetSongPageResponse {
        page: ISongPage;
    }

    interface IGetSongPageInitResponse extends IGetSongPageResponse{
        types?: ILookupItem[],
        songTypeLinks: ILinkListItem[],
        canedit: any
    }

    class SongPageObservable{
        constructor(canEdit: boolean, typesLookup: ILookupItem[],page?: ISongPage) {
            this.typesController = new multiSelectObservable(typesLookup);
            this.userCanEdit = canEdit;
            if (page) {
                this.assign(page);
            }
        }

        userCanEdit : boolean = false;
        typesController : multiSelectObservable;
        editMode = ko.observable(false);

        songid = ko.observable();
        pageid = ko.observable();
        contentid = ko.observable('');
        title = ko.observable('');
        description = ko.observable('');
        lyrics = ko.observable('');
        introduction = ko.observable('');
        commentary = ko.observable('');
        postedDate = ko.observable();
        pageimage = ko.observable('');
        imagecaption = ko.observable('');
        youtubeId = ko.observable('');
        hasicon = ko.observable(true);
        hasthumbnail = ko.observable(false);
        publicDomain = ko.observable(true);
        active = ko.observable(true);
        errorMessage = ko.observable('');

        assign = (page: ISongPage) => {
            this.errorMessage('');
            this.typesController.setValues(page.types);
            this.editMode(false);

            this.pageid(page.id);
            
            this.assignSong(page.song);
            
            this.introduction(page.introduction);
            this.commentary(page.commentary);
            this.postedDate(page.postedDate);
            this.pageimage(page.pageimage);
            this.imagecaption(page.imagecaption);
            this.youtubeId(page.youtubeId);
            this.hasicon(page.hasicon == 1);
            this.hasthumbnail(page.hasthumbnail == 1);
            this.active(page.active == 1);
        }
        
        assignSong = (song: ISong) => {
            this.songid(song.id);
            this.contentid(song.contentid);
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
            this.hasthumbnail(false);
            this.active(true);
        }

        clearSong = () => {
            this.songid(0);
            this.contentid('');
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
                lyrics: this.lyrics().trim(),
                title: this.title().trim(),
                description: this.description().trim(),
                contentid: this.contentid().trim(),
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
            this.editMode(false);
        }
    }

    export class SongpageViewModel extends Peanut.ViewModelBase
        implements IContentOwner {
        contentid = ko.observable('Mars');
        returnLink = ko.observable('');
        songsLink = ko.observable('/songs');
        returnTitle = ko.observable('');
        songTypeLinks = ko.observableArray<ILinkListItem>();
        // typesSelectController : IMultiSelectObservable;
        songform : SongPageObservable;
        canedit = ko.observable(false);

        contentController: PeanutContent.contentController;


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
            me.application.registerComponents('@pnut/pager,@pnut/lookup-select,@pnut/multi-select', () => {
                me.application.loadResources([
                    '@pnut/multiSelectObservable',
                    '@pkg/peanut-content/contentController.js',
                    '@lib:tinymce',
                    '@pnut/ViewModelHelpers.js'
                ], () => {
                    me.application.registerComponents([
                        '@pnut/modal-confirm',
                        '@pnut/clean-html',
                        '@pkg/peanut-content/content-block',
                        '@pkg/peanut-content/image-block',
                        'songs/lyrics-block'
                    ], () => {
                        me.contentController = new PeanutContent.contentController(me);

                        me.services.executeService('Peanut.songs::GetSongPage',songid,
                            (serviceResponse: Peanut.IServiceResponse) => {
                                if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                                    let response = <IGetSongPageInitResponse>serviceResponse.Value;
                                    me.songTypeLinks(response.songTypeLinks);
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
            });

        }

        handleContentNotification(contentId: string, message: string) {
            console.log(contentId = ': '+message);
        }

        mousingOverButton = ko.observable(true);
        toggleMoreSongs = () => {
            let state = this.mousingOverButton();
            this.mousingOverButton(!state);
        }

        afterDatabind = () => {
            this.contentController.initialize();
        }

        edit = () => {
            this.songform.editMode(true)
        }
        cancelEdit = () => {
            this.songform.editMode(false)
        }
        save = () => {
            this.songform.editMode(false)

        }
    }
}
