/// <reference path="../../../../pnut/core/ViewModelBase.ts" />
/// <reference path='../../../../typings/knockout/knockout.d.ts' />
/// <reference path='../../../../pnut/core/peanut.d.ts' />
/// <reference path='../../../../typings/tinymce/tinymce.d.ts' />
/// <reference path='../../../../pnut/js/ViewModelHelpers.ts' />
/// <reference path="../js/peanutcontent.d.ts" />
namespace PeanutContent {

    /*** Required in owning ViewModel init() ***
     *	me.application.loadResources([
     *		'@pnut/ViewModelHelpers.js'], () => {
     *		me.application.registerComponents([
     *   			'@pkg/peanut-content/image-block'
     *   			], () => {
     *			me.bindDefaultSection();
     *			successFunction();
     *		});
     *	});
     ***/

    export class imageBlockComponent {
        imageSrc = ko.observable('');
        imagePath : string;
        imageName: string;
        editing = ko.observable(false);
        canedit : KnockoutObservable<boolean>;

        editorModal : any;
        editorTitle = ko.observable('Upload or replace');
        editorModalId = ko.observable('modal-image');
        imageUploadId = ko.observable('upload-image');
        selected = ko.observable(false);
        invalidFile = ko.observable(false);
        newimage = ko.observable(false);

        owner: IImageComponentOwner = null;

        constructor(params: any) {
            let me = this;

            if (!params) {
                console.error('imageBlockComponent: Params not defined');
                return;
            }
            if (!params.imagepath) {
                console.error('imageBlockComponent: Parameter "imagepath" is required');
                return;
            }

            if (!params.imagename) {
                console.error('imageBlockComponent: Parameter "imagename" is required');
                return;
            }

            if (params.id) {
                me.editorModalId('modal-'+ params.id);
                me.imageUploadId('upload-'+params.id);

            }

            me.imageName = (ko.isObservable(params.imagename)) ?
                params.imagename() : params.imagename;

            me.imagePath = (ko.isObservable(params.imagepath)) ?
                params.imagepath() : params.imagepath;

            // let src = me.imagePath + '/' + me.imageName;
            me.imageSrc(me.imagePath + '/' + me.imageName);

            if (params.owner) {
                me.owner = params.owner();
            }

            if (params.canedit && params.owner) {
                me.canedit = params.canedit;
            }
            else {
                me.canedit = ko.observable(false);
            }

            if (params.title) {
                me.editorTitle(
                    'Upload or replace: ' + params.title)
            }
        }

        save = () => {
            let filelist = Peanut.Helper.getSelectedFiles(this.imageUploadId());
            if (filelist.length) {
                // let file = filelist[0];
                // let name = file.name;
                this.owner.onFileSelected(filelist,this.imagePath,this.imageName);
            }
            this.editorModal.hide();
            this.newimage(true);
       }

        edit = () => {
            this.showModal();
            this.editing(true);
        }

        cancel = () => {
            this.editorModal.hide();
        }
        showModal = ()  => {
            if (!this.editorModal) {
                let id = this.editorModalId();
                let modalElement = document.getElementById(id);
                modalElement.addEventListener('hidden.bs.modal',this.cancel);
                this.editorModal = new bootstrap.Modal(document.getElementById(id));
            }
            this.editorModal.show();
        }

        validateSelectedFile = () => {
            let element = <HTMLInputElement> document.getElementById(this.imageUploadId());
            if (element && element.value) {
                let ext = element.value.split('.').pop();
                switch (ext.toLowerCase()) {
                    case 'jpg':
                        return 1;
                    case 'jpeg':
                        return 1;
                    case 'png':
                        return 1;
                    case 'gif':
                        return 1;
                    default:
                        return -1;
                }
            }
            return 0;
        }

        onFileSelection = () => {
            let validation = this.validateSelectedFile();
            this.selected(validation === 1);
            this.invalidFile(validation === -1);
        }
    }
}