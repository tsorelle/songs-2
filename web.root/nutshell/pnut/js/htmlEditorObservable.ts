/// <reference path='../../pnut/core/peanut.d.ts' />
/// <reference path='../../typings/knockout/knockout.d.ts' />
/// <reference path='../../typings/knockout/knockout.d.ts' />
/// <reference path='../../pnut/js/ViewModelHelpers.ts' />
/// <reference path='../../typings/tinymce/tinymce.d.ts' />

namespace Peanut {
    export class htmlEditorObservable {
        private application : IPeanutClient;
        private selector : string;
        constructor(owner: any)
        {
            let me = this;
            me.application = owner.getApplication();
        }

        content = ko.observable('');

        initialize = (selector: string) => {
            let me = this;
            me.selector = selector
            me.application.loadResources([
                '@lib:tinymce',
                '@pnut/ViewModelHelpers.js'
            ], () => {
                me.initEditor(selector);
            });
        }

        initEditor = (selector: string) => {
            let me = this;
            let host = Peanut.Helper.getHostUrl() + '/';
            tinymce.init({
                selector: '#' + selector,
                toolbar: "undo redo | styleselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link | image | code",
                plugins: "image imagetools link lists code paste",
                // default_link_target: "_blank",
                relative_urls : false,
                convert_urls: false,
                remove_script_host : false,
                document_base_url : host,
                branding: false,
                paste_word_valid_elements: "b,strong,i,em,h1,h2,h3,p,a,ul,li"
            });
        };

        setContent = (content: string) => {
            let me = this;
            let editor = tinymce.get(me.selector);
            editor.setContent(content);
        }

        getContent = () => {
            let me = this;
            tinymce.triggerSave();
            let editor = tinymce.get(me.selector);
            return editor.getContent();
            // let content = editor.getContent();
            // return <string>jQuery('#messagehtml').val();
        }

        cleanHtml = () => {
            let me = this;
            let request = {
                blanks: true,
                headings: 'h2'
            };
            let editor = tinymce.get(me.selector);
            let content = editor.getContent();
            if (content) {
                let lines = content.split("\n");
                if (request.blanks) {
                    lines = lines.filter((item: string) => {
                        if (item == '<p>&nbsp;</p>' || item == '<p><strong>&nbsp;</strong></p>') {
                            return false;
                        }
                        return true;
                    });
                }
                if (request.headings) {
                    let hStart = '<'+request.headings+'>';
                    let hEnd = '</'+request.headings+'>';
                    let pStart = '<p><strong>';
                    let pEnd = '</strong></p>';
                    let count = lines.length;
                    for (let i = 0;i<count;i++) {
                        let item = lines[i];
                        if (item.substr(0,11) == pStart && item.substr(-13) == pEnd) {
                            lines[i] = item.replace(pStart,hStart).replace(pEnd,hEnd);
                        }
                    }
                }

                content = lines.join("\n")
                editor.setContent(content);
                return content;
            }
        };

    }
}