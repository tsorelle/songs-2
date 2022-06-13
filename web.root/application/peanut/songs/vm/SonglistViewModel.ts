// required for all view models:
/// <reference path='../../../../nutshell/pnut/core/ViewModelBase.ts' />
/// <reference path='../../../../nutshell/typings/knockout/knockout.d.ts' />
/// <reference path='../js/songs.d.ts' />

// Module
namespace Peanut {
    interface ISonglistInitResponse{
        pages: ISongListItem[];
        types: ILookupItem[];
        instruments: ILookupItem[];
    }
    export class SonglistViewModel extends Peanut.ViewModelBase {
        planet = ko.observable('Mars');

        songlist = ko.observableArray<Peanut.ISongListItem>();


        init(successFunction?: () => void) {
            let me = this;
            Peanut.logger.write('Songlist Init');
            let request = null; // assign request, e.g. {'this' : 1, 'that' : 2}
            me.application.hideServiceMessages();
            me.application.showWaiter('Message here...');
            me.services.executeService('Peanut.songs::InitSongList',request,
                (serviceResponse: Peanut.IServiceResponse) => {
                    if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                        me.application.hideWaiter();
                    }
                }
            ).fail(() => {
                let trace = me.services.getErrorInformation();
                me.application.hideWaiter();
            });


            me.bindDefaultSection();
            successFunction();
        }
    }
}
