/// <reference path='../../../../nutshell/pnut/core/ViewModelBase.ts' />
/// <reference path='../../../../nutshell/typings/knockout/knockout.d.ts' />

namespace Peanut {
    interface ISongInfo {
        id: any;
        title: string;
        user: string;
    }
    interface ISongSet {
        id: any;
        setname: string;
        user: string;
    }

    export class LyricsViewModel extends Peanut.ViewModelBase {
        // observables
        page = ko.observable('lyrics');
        title = ko.observable('Song of the Silly');
        selectedSet = ko.observable<ISongSet>();
        sets = ko.observableArray<ISongSet>();
        songList : ISongInfo[] = [];
        songs : KnockoutObservableArray<ISongInfo>[] = [];
        allsongs = ko.observableArray<ISongInfo>();
        textSize = ko.observable(2);
        columnDisplay = ko.observable(false);
        selectedSong = ko.observable<ISongInfo>();
        loading = ko.observable('');
        isAdmin = ko.observable(false);
        signedIn = ko.observable(false);
        setForm = {
            id: ko.observable(0),
            setName: ko.observable(''),
            nameError: ko.observable(''),
            lookupValue: ko.observable(''),
            selectedSongs : ko.observableArray<ISongInfo>(),
            avaliableSongs: ko.observableArray<ISongInfo>(),
            searchValue: ko.observable(),
            user: ''
        };

        songForm = {
            id: ko.observable(0),
            title: ko.observable(''),
            lyrics: ko.observable(''),
            public: ko.observable(false),
            errorMessage: ko.observable(''),
            currentSetName: ko.observable(''),
            user: ko.observable(''),
            includeInSet : ko.observable(false),
            canedit: ko.observable(true)
        };


        init(successFunction?: () => void) {
            let me = this;
            Peanut.logger.write('Lyrics Init');


            // temp for initial text
            for (let i = 0; i < 4; i++) {
                this.songs[i] = ko.observableArray([]);
            }

            me.bindDefaultSection();
            successFunction();
        }

        prevSong = () => {

        }

        nextSong = () => {

        }
        showSongList = () => {

        }

        splitColumns = () => {

        }

        reduceFont = () => {

        }

        enlargeFont = () => {

        }

        help = () => {

        }

        onSaveLyrics = () => {

        }

        signIn = () => {

        }

        editSet  = () => {

        }

        newSet = () => {

        };
        newSong = () => {
        }

        home = () => {
            this.page('lyrics');
        };

        saveSetList = () => {

        }

        cancelSetEdit = () => {

        }

        deleteSet  = () => {

        }
        toggleUser  = () => {

        }

        filterByUser  = () => {

        }

    }
}
