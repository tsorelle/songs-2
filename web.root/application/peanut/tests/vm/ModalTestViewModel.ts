/**
 * Created by Terry on 5/7/2017.
 */

// required for all view models:
/// <reference path='../../../../nutshell/pnut/core/ViewModelBase.ts' />
/// <reference path='../../../../nutshell/typings/knockout/knockout.d.ts' />
/// <reference path='../../../../nutshell/typings/bootstrap-5/js/dist/modal.d.ts' />

namespace Peanut {

    export class ModalTestViewModel  extends Peanut.ViewModelBase {
        showing = ko.observable(false);
        testModal : any;
        confirmModel: any;

        // call this funtions at end of page
        init(successFunction?: () => void) {
            console.log('Init ModalTest');
            let me = this;
            me.application.registerComponents(['@pnut/modal-confirm'], () => {
                me.bindDefaultSection();
                successFunction();
            });
        }

        onShowClick = () => {
            if (!this.testModal) {
                this.testModal = new bootstrap.Modal(document.getElementById('test-modal'));
            }
            this.testModal.show();
        };

        onSaveChanges = () => {
            this.testModal.hide();
            alert('Save changes');
        }

        confirmTest = () => {
            if (!this.confirmModel) {
                this.confirmModel = new bootstrap.Modal(document.getElementById('confirm-modal-test'));
            }
            this.confirmModel.show();

        }

        onConfirmTestOk = () => {
            this.confirmModel.hide();
            alert('OK Clicked');
        }



    }
}
