/// <reference path="../../../../pnut/core/ViewModelBase.ts" />
/// <reference path='../../../../typings/knockout/knockout.d.ts' />
/// <reference path='../../../../pnut/core/peanut.d.ts' />
/// <reference path="../js/peanutcontent.d.ts" />
namespace PeanutContent {
    export class contentBlockComponent implements PeanutContent.IContentComponent {
        contentId: string;
        contentSource: KnockoutObservable<string>;
        controller: IContentController = null;

        isHtml = ko.observable(true);
        state = ko.observable('readonly');
        canedit : KnockoutObservable<boolean>;

        editorModal : any;
        editorTitle = ko.observable('');
        textBuffer = ko.observable('');


        // include constructor if any params used
        constructor(params: any) {
            let me = this;

            if (!params) {
                console.error('contentBlockComponent: Params not defined');
                return;
            }
            if (!params.source) {
                console.error('contentBlockComponent: Parameter "source" is required');
                return;
            }
            if (!ko.isObservable(params.source)) {
                console.error('contentBlockComponent: Parameter "source" must be a knowckout obscrvable');
                return;
            }
            me.contentSource = params.source;

            if ((params.canedit) && ko.isObservable(params.canedit)) {
                me.canedit = params.canedit;
            }
            else {
                me.canedit = ko.observable(false);
            }

            if (!params.id) {
                console.error('contentBlockComponent: Parameter "id" is required');
                return;
            }
            me.contentId = params.id;

            if (params.controller) {
                me.controller = params.controller;
                me.controller.register(me.contentId,me);
            }

            if (params.contenttype) {
                me.isHtml(params.contenttype === 'html')
            }
            if (params.title) {
                me.editorTitle(params.title)
            }
        }

        edit = () => {
            this.loadContent();
            if (this.controller) {
                this.controller.sendNotification(this.contentId,'edit');
            }
            this.state('edit');
            this.showModal();
        }

        cancel = () => {
            if (this.state() !== 'readonly') {
                if (this.controller) {
                    this.controller.sendNotification(this.contentId, 'cancelled');
                }
                this.editorModal.hide();
                this.state('readonly');
            }
        }

        open = (contentObservable: KnockoutObservable<string>) => {
            this.contentSource = contentObservable;
            if (this.controller) {
                this.controller.sendNotification(this.contentId,'opened');
            }
        }

        save = () => {
            this.postContent();
            if (this.controller) {
                this.controller.sendNotification(this.contentId,'saved');
            }
            this.editorModal.hide()
            this.state('readonly');
        }

        loadContent = () => {
            if (this.isHtml) {

                // observable to editor
            }
            else {
                let text = this.contentSource();
                this.textBuffer(text);
            }
        }
        postContent = () => {
            if (this.isHtml) {
                // editor  to observable
            }
            else {
                let text = this.textBuffer();
                this.contentSource(text);
            }
        }

        showModal = ()  => {
            if (!this.editorModal) {
                let modalElement = document.getElementById(this.contentId);
                modalElement.addEventListener('hidden.bs.modal',this.cancel);
                this.editorModal = new bootstrap.Modal(document.getElementById(this.contentId));
            }
            this.editorModal.show();
        }

        hideModal = () => {
            this.editorModal.hide()
        }
    }

}