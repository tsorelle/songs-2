/// <reference path="../../../../pnut/core/ViewModelBase.ts" />
/// <reference path='../../../../typings/knockout/knockout.d.ts' />
/// <reference path='../../../../pnut/core/peanut.d.ts' />

namespace PeanutContacts {

    /** Service Contracts  and related interfaces **/
    export interface ISubscriptionListItem extends Peanut.ILookupItem  {
        subscribed: boolean;
    }



        interface IGetSubscriptionsResponse {
            personId: any;
            pageHeading: string;
            addressId: any;
            emailLists: Peanut.ILookupItem[];
            emailSubscriptions: ISubscriptionListItem[];
            // notifications: any;
            translations: string[];
            redirect: string;
        }

    interface IUpdateSubscriptionsRequest {
        emailSubscriptions : any[];
        personId : any;
    }

    /** View Model **/

    export class SubscriptionsViewModel extends Peanut.ViewModelBase {
        personId : any;
        recievesNotifications = true;

        //  *********** Observables ****************/
        personName = ko.observable('');
        emailSubscriptionList : KnockoutObservableArray<ISubscriptionListItem> = ko.observableArray([]);
        emailSubscriptionsView : KnockoutObservableArray<ISubscriptionListItem> = ko.observableArray([]);
        // notifications = ko.observable(true);

        /** Initialization **/

        init(successFunction?: () => void) {
            let me = this;

            Peanut.logger.write('Subscriptions Init');
            me.application.loadResources([
                // Load libraries and core components
                '@lib:lodash',
                // '@pnut/ViewModelHelpers'
            ], () => {
                me.getInitializations(() => {
                    me.bindDefaultSection();
                    successFunction();
                });
            });
        }

        getInitializations(doneFunction?: () => void) {
            let me = this;
            me.application.hideServiceMessages();

            let uid = this.getRequestVar('uid');

            let request = uid ? {
                userId : uid
            } : null;

            me.showLoadWaiter();
            me.services.executeService('peanut.contacts::messaging.GetUserSubscriptions',request,
                function(serviceResponse: Peanut.IServiceResponse) {
                    if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                        let response = <IGetSubscriptionsResponse>serviceResponse.Value;
                        if (response.redirect) {
                            window.location.href = response.redirect;
                            return;
                        }
                        me.setPageHeading(response.pageHeading);
                        me.personId = response.personId;
                        me.createSubscriptionList(me.emailSubscriptionList, response.emailLists);
                        me.assignSubscriptions(me.emailSubscriptionList,me.emailSubscriptionsView, response.emailSubscriptions);
                        // me.recievesNotifications = (response.notifications != 0);
                        // me.notifications(me.recievesNotifications);
                        me.addTranslations(response.translations);
                    }
                })
                .fail(function () {
                    me.services.getErrorInformation();
                })
                .always(function () {
                    me.application.hideWaiter();
                    if (doneFunction) {
                        doneFunction();
                    }
                });
        }

        assignSubscriptions = (
            checkList: KnockoutObservableArray<ISubscriptionListItem>,
            viewList: KnockoutObservableArray<ISubscriptionListItem>,subscriptions) => {

            let me = this;
            let check = checkList();
            checkList([]);
            viewList([]);
            let view = [];
            let newList = [];
            check.forEach((item: ISubscriptionListItem) => {
                item.subscribed = (subscriptions.indexOf(item.id) > -1);
                if (item.subscribed) {
                    view.push(item);
                }
                newList.push(item);
            });
            checkList(newList);
            viewList(view);
        };

        getSelectedSubscriptions = (checkList: KnockoutObservableArray<ISubscriptionListItem>,
                                           viewList: KnockoutObservableArray<ISubscriptionListItem>) => {
            let selected = [];
            let subscriptions = checkList();
            let temp = subscriptions.filter((item: ISubscriptionListItem) => {
                if (item.subscribed) {
                    selected.push(item.id);
                    return true;
                }
            });
            viewList(temp);
            return selected;
        };

        createSubscriptionList = (list: KnockoutObservableArray<ISubscriptionListItem>,items: Peanut.ILookupItem[]) => {
            items.sort((a: Peanut.ILookupItem, b: Peanut.ILookupItem) => {
                if (a.name === b.name) {
                    return 0;
                }
                else if (a.name > b.name) {
                    return 1;
                }
                else {
                    return -1;
                }
            });
            items.forEach((item) => {
                list.push(
                    <ISubscriptionListItem>{
                        code : item.code,
                        id: item.id,
                        name: item.name,
                        description: item.description,
                        subscribed: false
                    });
            });
        };


        updateSubscriptions = () => {
            let me = this;
            let request = <IUpdateSubscriptionsRequest > {
                emailSubscriptions : me.getSelectedSubscriptions(me.emailSubscriptionList,me.emailSubscriptionsView),
                personId : me.personId,
            };
/*
            if (me.recievesNotifications != me.notifications()) {
                me.recievesNotifications = me.notifications();
                request.notifications = me.recievesNotifications ? 1 : 0;
            }
*/
            me.showLoadWaiter();
            me.services.executeService('peanut.contacts::messaging.UpdatePersonSubscriptions',request,
                function(serviceResponse: Peanut.IServiceResponse) {
                    if (serviceResponse.Result == Peanut.serviceResultSuccess) {
                    }
                })
                .fail(function () {
                      me.services.getErrorInformation();
                })
                .always(function () {
                    me.application.hideWaiter();
                });

        };
    }
}