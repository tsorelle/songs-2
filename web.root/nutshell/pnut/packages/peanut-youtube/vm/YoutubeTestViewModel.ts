/// <reference path="../../../../pnut/core/ViewModelBase.ts" />
/// <reference path='../../../../typings/knockout/knockout.d.ts' />
/// <reference path='../../../../pnut/core/peanut.d.ts' />

namespace PeanutYoutube {


    export class YoutubeTestViewModel extends Peanut.ViewModelBase implements IYouTubeClient{
        // observables

        youtubeUrl = ko.observable('');
        playerElementId = 'video-frame-1';
        status = ko.observable('');
        ready = false;
        player: YT.Player;
        controller : PeanutYoutube.YTFrameController;

        init(successFunction?: () => void) {
            let me = this;
            /**
             * See YTFrameController
             */

            me.application.loadResources('@pkg/peanut-youtube/YTFrameController.js', () => {
                Peanut.logger.write('YoutubeTest Init');
                let youTubeUri =
                    'https://www.youtube.com/embed/' +
                    'IGA1XC0NivE' +
                    '?rel=0&modestbranding=1' +
                    '&enablejsapi=1'

                me.youtubeUrl(youTubeUri);
                this.controller =  PeanutYoutube.YTFrameController.register(me)
                me.bindDefaultSection();
                PeanutYoutube.YTFrameController.initApi();
                successFunction();
            });
        }

        getPlayer = () => {
            return this.controller.getPlayer(this.playerElementId)
        }

        pause = () => {
            this.getPlayer().pauseVideo();
        }

        stop = () => {
            this.getPlayer().stopVideo();
        }

        play = () => {
            this.getPlayer().playVideo();
        }

        initVideo = () => {
        }

        createPlayers = () => {
            let id = this.playerElementId;
            this.player = new YT.Player(id, {
                events: {
                    'onReady': this.onPlayerReady,
                    'onStateChange': this.onPlayerStateChange
                }
            });
            this.ready = true;
            return [<IPlayerItem> {
                id: this.playerElementId,
                player: this.player
            }]
        }

        onPlayerReady = (_event) => {
            this.status('ready');
        }

        onPlayerStateChange = (event) => {
            this.status(YTFrameController.getPlayerStatus(event.data));
        }
    }

}
