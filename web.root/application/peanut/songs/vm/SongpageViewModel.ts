// required for all view models:
/// <reference path='../../../../nutshell/pnut/core/ViewModelBase.ts' />
/// <reference path='../../../../nutshell/typings/knockout/knockout.d.ts' />

// Module
namespace Peanut {
    export class SongpageViewModel extends Peanut.ViewModelBase {
        contentid = ko.observable('Mars');

        init(successFunction?: () => void) {
            let me = this;
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
