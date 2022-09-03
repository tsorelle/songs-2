// required for all view models:
/// <reference path='../../../../nutshell/pnut/core/ViewModelBase.ts' />
/// <reference path='../../../../nutshell/typings/knockout/knockout.d.ts' />
/// <reference path='../../../../nutshell/pnut/js/htmlEditorObservable.ts' />

namespace Peanut {

    export class EditorTestViewModel extends Peanut.ViewModelBase {
        // observables
        private htmlEditor : Peanut.htmlEditorObservable;

        init(successFunction?: () => void) {
            let me = this;
            Peanut.logger.write('VmName Init');
            me.application.loadResources([
                '@pnut/htmlEditorObservable'
            ], () => {
                me.htmlEditor =  new Peanut.htmlEditorObservable(me);
                me.htmlEditor.initialize('test-editor');
                me.bindDefaultSection();
                successFunction();
            });
        }

        getHtmlContent = () => {
            let content = this.htmlEditor.getContent();
            alert('Got content!')
        }

        setHtmlContent = () => {
            this.htmlEditor.setContent('<h1>Hello World</h1>');
        }


    }
}
