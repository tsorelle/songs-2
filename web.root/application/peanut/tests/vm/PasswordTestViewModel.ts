/// <reference path='../../../../nutshell/pnut/core/ViewModelBase.ts' />
/// <reference path='../../../../nutshell/typings/knockout/knockout.d.ts' />

// Module
namespace Peanut {
    // SimpleTest view model
    export class PasswordTestViewModel  extends Peanut.ViewModelBase {
        myPassword = ko.observable('');
        init(successFunction?: () => void) {
            console.log('Init PasswordTest');
            let me = this;
            me.application.registerComponents('@pnut/change-password', () => {
                me.bindDefaultSection();
                successFunction();
            });
        }

        showPassword = () => {
            alert(this.myPassword());
        }
    }
}
