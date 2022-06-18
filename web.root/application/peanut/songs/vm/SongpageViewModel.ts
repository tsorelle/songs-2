// required for all view models:
/// <reference path='../../../../nutshell/pnut/core/ViewModelBase.ts' />
/// <reference path='../../../../nutshell/typings/knockout/knockout.d.ts' />

// Module
namespace Peanut {
    export class SongpageViewModel extends Peanut.ViewModelBase {
        contentid = ko.observable('Mars');
        returnLink = ko.observable('');
        songsLink = ko.observable('/songs');
        returnTitle = ko.observable('');

        init(successFunction?: () => void) {
            let me = this;
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

            Peanut.logger.write('Songpage Init');
            let songid = me.getPageVarialble('songid');
            if (!songid) {
                alert('Content id not found');
                return;
            }
            me.contentid(songid);

            me.bindDefaultSection();
            successFunction();
        }
    }
}
