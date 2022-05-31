/**
 * Created by Terry on 5/7/2017.
 */

// required for all view models:
/// <reference path='../../../../nutshell/pnut/core/ViewModelBase.ts' />
/// <reference path='../../../../nutshell/typings/knockout/knockout.d.ts' />

// Module
namespace Peanut {
    // SimpleTest view model
    export class SimpleTestViewModel  extends Peanut.ViewModelBase {
        messageText = ko.observable('This is a simple test vm');

        // call this funtions at end of page
        init(successFunction?: () => void) {
            console.log('Init SimpleTest');
            let me = this;
            me.bindDefaultSection();
            successFunction();
        }


        onShowError = () => {
            this.application.showMessage('This is an error.');
           //  this.application.showError('This is an error.');
        };
    }
}
