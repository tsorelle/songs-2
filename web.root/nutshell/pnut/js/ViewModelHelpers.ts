/**
 * Created by Terry on 4/28/2017.
 */
///<reference path='../../typings/jquery/jquery.d.ts' />
/// <reference path='../core/peanut.d.ts' />

namespace Peanut {

    /**
     * Constants for entity editState
     */
    export class editState {
        public static unchanged : number = 0;
        public static created : number = 1;
        public static updated : number = 2;
        public static deleted : number = 3;
    }


    export class Helper {
        /*
         * Utility routines
         */

        public static getRequestParam(name) {
            // todo: test this
            let found = (new RegExp('[?&]' + encodeURIComponent(name) + '=([^&]*)')).exec(location.search)
            found ? decodeURIComponent(name[1]) : null;
        }

        public static ValidateEmail(email: string) {
            if (!email || email.trim() == '') {
                return false;
            }
            return /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(email);
        }

        public static ValidateCredential(value, minlength = 10, requireLower = false) {
            if (value.length < minlength) {
                return false;
            }
            if (value.replace(' ', '') !== value) {
                return false;
            }
            if (requireLower) {
                return (value.toLowerCase() === value);
            }
            return true;
        };

        public static validatePositiveWholeNumber(text: string, maxValue = null, emptyOk: boolean = true) {
            return Helper.validateWholeNumber(text, maxValue, 0, emptyOk);
        }

        public static validateWholeNumber(numberText: string, maxValue = null, minValue = null, emptyOk: boolean = true) {
            if (numberText == null) {
                numberText = '';
            }

            numberText = numberText + ' '; // convert to string to ensure .trim() works.
            let result = {
                errorMessage: '',
                text: numberText.trim(),
                value: 0,
            };

            let parts = result.text.split('.');
            if (parts.length > 1) {
                let fraction = parseInt(parts[1].trim());
                if (fraction != 0) {
                    result.errorMessage = 'Must be a whole number.';
                    return result;
                } else {
                    result.text = parts[0].trim();
                }
            }

            if (result.text == '') {
                if (!emptyOk) {
                    result.errorMessage = 'A number is required.'
                }
                return result;
            }

            result.value = parseInt(result.text);
            if (isNaN(result.value)) {
                result.errorMessage = 'Must be a valid whole number.';
            } else {
                if (minValue != null && result.value < minValue) {
                    if (minValue == 0) {
                        result.errorMessage = 'Must be a positive number';
                    } else {
                        result.errorMessage = 'Must be greater than ' + minValue;
                    }
                }
                if (maxValue != null && result.value > maxValue) {
                    if (result.errorMessage) {
                        result.errorMessage += ' and less than ' + maxValue;
                    } else {
                        result.errorMessage = 'Must be less than ' + maxValue;
                    }
                }
            }
            return result;
        }

        public static validateCurrency(value: any): any {
            if (!value) {
                return false;
            }
            if (typeof value == 'string') {
                value = value.replace(/\s+/g, '');
                value = value.replace(',', '');
                value = value.replace('$', '');
            } else {
                value = value.toString();
            }
            if (!value) {
                return false;
            }
            let parts = value.split('.');
            if (parts.length > 2) {
                return false;
            }
            // todo: replace with isNaN
            if (!jQuery.isNumeric(parts[0])) {
                return false;
            }
            if (parts.length == 1) {
                return parts[0] + '.00';
            }

            // todo: replace with isNaN
            if (!jQuery.isNumeric(parts[1])) {
                return false;
            }

            let result = Number(parts[0] + '.' + parts[1].substring(0, 2));
            if (isNaN(result)) {
                return false;
            }
            return result;
        };

        public static getSelectedFiles(elementId: string) {
            let element = <any>jQuery(elementId);
            if (element && element.length && element[0].files) {
                return element[0].files;
            }
            return null;
        }

        public static getHostUrl() {
            let protocol = location.protocol;
            let slashes = protocol.concat("//");
            return slashes.concat(window.location.hostname);
        }

        /**
         * Validates a time of day string, Expected hour and minutes delimited by colon
         * optionally appended with case insensitive 'a','am','p' or 'pm'
         *
         *  When valid returns 24 hour time format: hh:mm
         *  If not valid returns FALSE
         *
         * @param ts  time string
         */
        public static parseTimeString(ts: string) {
            if (!ts) {
                // null or undefined
                return '';
            }
            ts = ts.toUpperCase().trim();
            if (ts === '') {
                // blank is valid, check later if required
                return '';
            }

            // special cases
            if (ts.indexOf('NOON') >= 0) {
                return '12:00'
            }
            if (ts.indexOf('MIDNIGHT') >= 0) {
                return '00:00'
            }

            let formatType = 0;  // 1='am', 2='pm', 0=24hour

            let p = ts.indexOf('P');
            if (p === (ts.length - 1) || ts.substring(p) === 'PM') {
                formatType = 2; // pm
            } else {
                p = ts.indexOf('A');
                if (p === (ts.length - 1) || ts.substring(p) === 'AM') {
                    formatType = 1; // am
                }
                // 0 = 24 hour is default
            }

            if (formatType > 0) {
                // trim am pm
                ts = ts.substring(0, p).trim();
                if (ts === '') {
                    // must be something like "  am " or "PM   " with no other value
                    return false;
                }
            }

            let parts = ts.split(':')
            let min: any = 0;

            // get hour
            let hr: any = Number(parts[0].trim());
            if (isNaN(hr) || (formatType > 0 && (hr < 1 || hr > 12))) {
                // unajusted am/pm must be in range of 1 to 12
                return false;
            }
            if (formatType === 1 && hr === 12) {
                // 12am is 00 - midnight
                hr = 0;
            } else if (formatType === 2 && hr !== 12) {
                // 12pm is 12:00 noon otherwise adjust
                hr += 12;
            }

            if (parts.length > 1) {
                let s = parts[1].trim();
                min = Number(s);
                if (isNaN(min) || min > 59 || min < 0 || s.length < 2) {
                    // minute must be 2 digits in 0 to 59 range
                    return false;
                }
            }

            if (min < 0 || hr < 0 || hr > 23 || min > 59) {
                // out of range
                return false;
            }

            if (hr < 10) {
                hr = '0' + hr;
            }

            if (min < 10) {
                min = '0' + min;
            }

            return hr + ':' + min;

        }

        public static toDateObject(ds) {
            if (ds === null) {
                return null;
            }
            ds = ds.trim();
            if (ds === '') {
                return null;
            }
            let d = new Date(ds);
            if (d.toString() === 'Invalid Date') {
                return false;
            }
            return <Date>d;
        }

        public static toISODate(d: Date) {
            return d.toISOString().substring(0, 10)
        }

        public static parseISODate(ds) {
            let d = Helper.toDateObject(ds);
            if (!d) {
                return false;
            }
            return (<Date>d).toISOString().substring(0, 10);
        }

        public static parseMySqlDate(ds: any, ts: any = null) {
            ds = Helper.parseISODate(ds);
            if (ds === false) {
                return 'Invalid date'
            }
            ts = Helper.parseTimeString(ts);
            if (ts === false) {
                return 'Invalid time';
            }
            if (!ts) {
                return ds;
            }
            return (ds.length > 0) ? ds + 'T' + ts : ts;
        }

        public static validateDateRange(ds1: any, ds2: any) {
            let result = [null, null];
            let d1 = Helper.toDateObject(ds1);
            if (d1) {
                result[0] = Helper.toISODate(d1);
                let d2 = Helper.toDateObject(ds2);
                if (d2) {
                    if ((<Date>d2) >= (<Date>d1)) {
                        result[1] = Helper.toISODate(d2);
                    }
                }
                return result
            }
        }

        public static getSelectedLookupItems(items: ILookupItem[], selected: any[]) {
            return items.filter((item: Peanut.ILookupItem) => {
                return selected.indexOf(item.id) !== -1;
            });
        }

        public static getLookupValues(items: ILookupItem[]) {
            let result = [];
            for(let i=0;i<items.length;i++) {
                result.push(items[i].id);
            }
            return result;
        }
        public static getLookupId = (itemObservable : KnockoutObservable<ILookupItem>) => {
            if (itemObservable && itemObservable()) {
                return itemObservable().id;
            }
            return null;
        }

        public static capitalize(s: string) {
            if (typeof s !== 'string') return ''
            return s.charAt(0).toUpperCase() + s.slice(1)
        }

        /**
         * format full name from parts
         */
        public static makeFullName(first: string, last:string, middle: string = null) {
            var result = first? first.trim() : '';
            if (middle) {
                result = result + ' ' + middle.trim();
            }
            if (last){
                result = result + ' ' + last.trim();
            }
            return result;
        }

        public static MonthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];

        public static formatFormalDate(dateString = 'today', includeTime = true) {
            let date =  (dateString === 'today') ?
                new Date() :
                new Date(dateString);
            let year = date.getFullYear();
            let day = date.getDate();
            let month = Peanut.Helper.MonthNames[date.getMonth()];
            let result = month + ' ' + day + ', ' + year ;
            if (includeTime) {
                let hour = date.getHours();
                let min = date.getMinutes();
                let ampm = 'AM';
                if (hour === 0) {
                    hour = 12;
                }
                else if (hour >= 12) {
                    ampm = 'PM';
                    if (hour > 12) {
                        hour -= 12;
                    }
                }
                result += ' ' + hour + ':' + min + ' ' + ampm;
            }
            return result;
        }

        /**
         * Replacement for lodash differenceBy
         * @param listA
         * @param listB
         * @param property
         */
        public static ExcludeValues(listA,listB,property) {
            let array1 = [...listA];
            let array2 = [...listB];
            return array1.filter(a => !array2.some(b => b[property] === a[property]));
        }

        public static SortByAlpha(list, property) {
            let clone = [...list];
            return clone.sort((v1,v2) => {
                let a = v1[property].toLowerCase();
                let b = v2[property].toLowerCase();
                if (a < b) {
                    return -1;
                }
                if (a > b) {
                    return 1;
                }
                return 0;
            });
        }

        public static SortByInt(list, property) {
            let clone = [...list];
            return clone.sort((v1,v2) => {
                let a = parseInt(v1[property]) || 0;
                let b = parseInt(v2[property]) || 0;
                if (a < b) {
                    return -1;
                }
                if (a > b) {
                    return 1;
                }
                return 0;
            });
        }


        // replaces lodash _.findIndex
        public static FindIndex(list, testfunction : (any) => boolean) {
            if (Array.isArray(list)) {
                let len = list.length;
                for(let i = 0;i<len; i++) {
                    if (testfunction(list[i])) {
                        return i;
                    }
                }
            }
            return -1;
        }

        public static SortBy(list, property) {
            let clone = [...list];
            return clone.sort((v1,v2) => {
                let a = v1[property];
                let b = v2[property];
                if (a < b) {
                    return -1;
                }
                if (a > b) {
                    return 1;
                }
                return 0;
            });
        }

        public static ScrollToTop() {
            let pos =  document.getElementById('page-top');
            pos.scrollIntoView({behavior: "smooth"});
        }

        public static ScrollTo(elementId: string) {
            let pos =  document.getElementById(elementId);
            pos.scrollIntoView({behavior: "smooth"});
        }


    } // end class Helper
}  // end namespace
