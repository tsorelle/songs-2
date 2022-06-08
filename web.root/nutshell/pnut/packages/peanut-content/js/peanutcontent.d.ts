declare namespace PeanutContent {

    export interface IContentItem {
        id: any;
        description: string;
        format: string;
        content: string;
        active: any;
    }

    interface IContentOwner {
        handleContentNotification(contentId: string, message: string)
    }

    interface IContentComponent {
        contentId: string;
        save();
        cancel();
        open(contentObservable: KnockoutObservable<string>);
        initEditor();
    }

    interface IContentController {
        contentOwner: IContentOwner;
        register(contentId: string, component: IContentComponent);
        initialize();
        save(contentId: string);
        saveAll();
        cancel(contentId: string);
        cancelAll();
        getComponent(contentId);
        sendNotification(contentId: string, message: string)
    }
}