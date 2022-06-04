/// <reference path="../../../../pnut/core/ViewModelBase.ts" />
/// <reference path='../../../../typings/knockout/knockout.d.ts' />
/// <reference path='../../../../pnut/core/peanut.d.ts' />

namespace PeanutContent {

    interface IContentItem {
        id : any;
        description : string;
        format : string;
        content : string;
        active: any;
    }

    export class ContentManagerViewModel extends Peanut.ViewModelBase {
        // observables
        htmlContent = ko.observable('');

        init(successFunction?: () => void) {
            let me = this;
            Peanut.logger.write('Content Manager Init');

            me.getContent(1,()=> {
                me.bindDefaultSection();
                successFunction();
            });

        }

        getContent =  (id : any, successFunction?: () => void) => {
            let me = this;
            me.application.hideServiceMessages();
            me.application.showWaiter('Getting content...');
            // single statement example
            me.services.executeService('Peanut.content::GetContent',id,
                function(serviceResponse: Peanut.IServiceResponse) {
                    if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                        me.application.hideWaiter();
                        let contentItem = <IContentItem>serviceResponse.Value;
                        me.htmlContent(contentItem.content);
                        if (successFunction) {
                            successFunction()
                        }
                    }
                }
            ).fail(function () {
                me.services.getErrorInformation();
                me.application.hideWaiter();
            });

        }
    }
}
