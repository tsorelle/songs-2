/// <reference path="../../../../pnut/core/ViewModelBase.ts" />
/// <reference path='../../../../typings/knockout/knockout.d.ts' />
/// <reference path='../../../../pnut/core/peanut.d.ts' />

namespace PeanutMailings {

    interface IUnsubscribeResponse {
        removed : any;
        personName : string;
        listName : string;
        subscriptionsLink : string;
        message: string;
        translations: any;
    }

    export class UnsubscribeViewModel extends Peanut.ViewModelBase {
        // observables
        message = ko.observable('');
        subscriptionsLink = ko.observable('');
        uid : string = '';
        listId : string = '';
        resubscribed = ko.observable(false);
        init(successFunction?: () => void) {
            let me = this;
            // alert('Helo Init')
            me.uid = this.getRequestVar('uid');
            me.listId = this.getRequestVar('listId');
            me.showLoadWaiter();
            me.services.executeService('peanut.mailings::UnsubscribeList',
                {uid: me.uid,listId: me.listId},
                function(serviceResponse: Peanut.IServiceResponse) {
                    if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                        let response = <IUnsubscribeResponse>serviceResponse.Value;
                        me.addTranslations(response.translations);
                        me.message(response.message);
                        if (response.subscriptionsLink) {
                            me.subscriptionsLink(response.subscriptionsLink + '?uid=' + me.uid);
                        }
                        else {
                            me.subscriptionsLink('');
                        }
                    }
                })
                .fail(function () {
                    let trace = me.services.getErrorInformation();
                })
                .always(function () {
                    me.application.hideWaiter();
                    me.bindDefaultSection();
                });
            successFunction();
        }

        resubscribe = () => {
            let me = this;
            // alert('HELLO resubscribe')
            me.showWaitMessage('Resubscribing...')
            me.services.executeService('peanut.mailings::resubscribe',
                {uid: me.uid,listId: me.listId},
                function(serviceResponse: Peanut.IServiceResponse) {
                    if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                        me.message('We have restored your subscription.');
                        me.resubscribed(true);
                    }
                })
                .fail(function () {
                    let trace = me.services.getErrorInformation();
                })
                .always(function () {
                    me.application.hideWaiter();
                });
        }
    }
}
