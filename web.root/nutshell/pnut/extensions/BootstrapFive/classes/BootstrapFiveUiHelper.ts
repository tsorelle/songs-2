/**
 * Created by Terry on 7/9/2017.
 */
///<reference path="../../../../typings/bootstrap-5/index.d.ts"/>

namespace Peanut {
    /**
     *  Implementation class for Bootstrap dependencies
     */
    export class BootstrapFiveUiHelper {
        public showMessage = (message: string, id: string,  container : any, modal=true ) => {
            let span = container.find('#' + id);
            span.text(message);
            this.showModal(container);

        };
        public hideMessage = (container : any) => {
            this.hideModal(container);
        };

        public showModal = (container : any) => {
            if (navigator.appName == 'Microsoft Internet Explorer') {
                container.removeClass('fade');
            }
            /*
            let e = container.get();
            let m = new bootstrap.Modal(e);
            m.show();
        */
            container.modal('show');
        };

        public hideModal = (container: any) => {
            container.modal('hide');
        };

        public getResourceList = () => {
            return ['@lib:fontawesome'];
        };

        public getFramework = () => {
            return 'Bootstrap'
        };

        public getVersion = () => {
            return 5;
        };

        public getFontSet = () => {
            return 'FA';
        }

    }
}