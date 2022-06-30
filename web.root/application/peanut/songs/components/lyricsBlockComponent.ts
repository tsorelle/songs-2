/// <reference path='../../../../nutshell/typings/knockout/knockout.d.ts' />
/// <reference path='../../../../nutshell/typings/bootstrap-5/index.d.ts' />

namespace Peanut {
    export interface IVerse {
        lines: string[];
    }

    export class lyricsBlockComponent {
        // observables
        lyrics: KnockoutObservable<string>;
        textBuffer = ko.observable('');

        // verses: any[] = [];
        verses1 = ko.observableArray<IVerse>();
        verses2 = ko.observableArray<IVerse>();
        canedit : KnockoutObservable<boolean>;
        editmode = ko.observable(false);
        twoColumns : KnockoutObservable<boolean>;
        textStyle = ko.observable('inherit');
        textSize : KnockoutObservable<any>;
        componentId = 'lyrics'
        editorModalId = ko.observable('lyrics-modal');
        textEditorId = ko.observable('lyrics-editor');
        editorModal : any;
        editorTitle = ko.observable('Lyrics');
        verses : IVerse[] = [];
        onSave: (id: string) => void;


        // include constructor if any params used
        constructor(params: any) {
            let me = this;

            if (!params) {
                console.error('lyricsBlockComponent: Params not defined in translateComponent');
                return;
            }

            if (!params.lyrics) {
                console.error('lyricsBlockComponent: Parameter "lyrics" is required');
                return;
            }
            if (!ko.isObservable(params.lyrics)) {
                console.error('lyricsBlockComponent: Parameter "lyrics" must be an observable');
                return;
            }

            if (params.id) {
                this.componentId = params.id;
                this.editorModalId(params.id + '-modal');
                this.textEditorId(params.id + '-editor');
            }

            if (params.save) {
                this.onSave = params.save;
            }

            if (params.columns) {
                if (ko.isObservable(params.columns)) {
                    this.twoColumns = params.columns;
                }
                else (
                    this.twoColumns(params.columns > 1)
                )
            }
            else {
                this.twoColumns = ko.observable(false);
            }

            if (params.textsize) {
                if (ko.isObservable(params.textsize)) {
                    this.textSize = params.textsize;
                }
                else {
                    this.textSize = ko.observable(params.textsize);
                }
                this.setTextSize();
                this.textSize.subscribe(this.setTextSize);
            }

            if ((params.canedit) && ko.isObservable(params.canedit)) {
                me.canedit = params.canedit;
            }
            else {
                me.canedit = ko.observable(false);
            }

            if (params.title) {
                me.editorTitle(params.titld);
            }

            this.lyrics = params.lyrics;
            this.parseVerses();
            this.formatLyrics();
            this.lyrics.subscribe(this.onLyricsChange);
            this.twoColumns.subscribe(this.onColumnsChange);
        }

        onLyricsChange = () => {
            this.parseVerses();
            this.formatLyrics();
        };

       onColumnsChange = () => {
            this.formatLyrics();
       };

       formatLyrics = () => {
            let split = this.twoColumns();
            this.verses1([]);
            this.verses2([]);
            if (split) {
                let colA = [];
                let colB = [];
                let verseCount = this.verses.length;
                let colsize = verseCount / 2;
                for (let i= 0; i<verseCount; i++) {
                    if (i < colsize) {
                        colA.push(this.verses[i]);
                    }
                    else {
                        colB.push(this.verses[i]);
                    }
                }
                this.verses1(colA);
                this.verses2(colB);
            }
            else {
                this.verses1(this.verses);
            }
        }
        
        parseVerses = () => {
            this.verses = [];
            let lyrics = this.lyrics();
            if (!lyrics) {
                return;
            }
            let text = lyrics.split("\n");
            let verse : IVerse = {
                lines: []
            }
            text.forEach((line: string) => {
                line = line.trim();
                if (line == '') {
                    if (verse.lines.length > 0) {
                        this.verses.push(verse);
                        verse = {
                            lines: []
                        }
                    }
                }
                else {
                    verse.lines.push(line);
                }
            });
            if (verse.lines.length > 0) {
                this.verses.push(verse)
            }
        }
        edit = () => {
            if (this.canedit()) {
                this.editmode(true);
                this.textBuffer(this.lyrics());
                this.showModal();
            }
        }
        cancelEdit = () => {
            this.editmode(false);
            this.editorModal.hide();
        }

        save = () => {
            this.lyrics(this.textBuffer())
            this.editmode(false);
            this.editorModal.hide();
            if (this.onSave) {
                this.onSave(this.componentId);
            }
        }

        setTextSize = () => {
            let size = this.textSize();
            this.textStyle(
                size === 0 ? 'inherit' : size + 'rem'
            );
        }

        showModal = ()  => {
            if (!this.editorModal) {
                let id = this.editorModalId();
                let modalElement = document.getElementById(id);
                modalElement.addEventListener('hidden.bs.modal',this.cancelEdit);
                this.editorModal = new bootstrap.Modal(document.getElementById(id));
            }
            this.editorModal.show();
        }

    }

}