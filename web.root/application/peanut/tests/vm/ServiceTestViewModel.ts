/**
 * Created by Terry on 5/7/2017.
 */

// required for all view models:
/// <reference path='../../../../nutshell/pnut/core/ViewModelBase.ts' />
/// <reference path='../../../../nutshell/typings/knockout/knockout.d.ts' />
/// <reference path='../../../../nutshell/typings/bootstrap-5/js/dist/modal.d.ts' />

namespace Peanut {

    export class ServiceTestViewModel  extends Peanut.ViewModelBase {
        itemName = ko.observable('');
        itemId = ko.observable(1);

        init(successFunction?: () => void) {
            console.log('Init ModalTest');
            let me = this;
            me.bindDefaultSection();
            successFunction();
        }

        onService = () => {
            let me = this;
            let testerName = this.getPageVarialble('tester');
            me.application.hideServiceMessages();
            // me.application.showWaiter('Testing service...','spin-waiter');
            // me.application.showWaiter('Testing service...');
            // me.services.executeService('admin.HelloWorld', request,
            let request = {"tester" : testerName};
            me.services.executeService('PeanutTest::HelloWorld', request,
                function (serviceResponse: Peanut.IServiceResponse) {
                    if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                        let response = serviceResponse.Value;
                        // alert(response.message);
                        // me.addTranslations(response.translations);
                        // me.languageA(me.translate('hello','Hello'));
                        // me.languageB(me.translate('world'));
                    }
                }
            )
/*
                .fail(function () {
                let trace = me.services.getErrorInformation();
            }).always(() => {
                me.application.hideWaiter();
            });
*/

        };

/*

        onGetItem() {
            let me = this;
            me.application.showWaiter('Please wait...');
            me.services.getFromService('TestGetService', 3, function (serviceResponse: Peanut.IServiceResponse) {
                    if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                        me.itemName(serviceResponse.Value.name);
                        me.itemId(serviceResponse.Value.id);
                    }
                    else {
                        alert("Service failed");
                    }
                }
            ).always(function () {
                me.application.hideWaiter();
            });

        }

        onPostItem() {
            let me = this;
            let request = {
                testMessageText: me.itemName()
            };

            me.application.showWaiter('Please wait...');
            me.services.executeService('TestService', request)
                .always(function () {
                    me.application.hideWaiter();
                });

        }
*/
    }
}
