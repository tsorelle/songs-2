/**
 * Created by Terry on 5/4/2017.
 */
/// <reference path="PeanutLoader.ts" />
/// <reference path="../../typings/knockout/knockout.d.ts" />

namespace Peanut {
    export class KnockoutHelper {

        private loadList = {
            css: [],
            templates: [],
            components: [],
            scripts: [],
            bindings: []
        };

        private alreadyLoaded(name: string, type: string = 'component') {
            let list = null;
            let me = this;
            let loaded = false;
            switch(type) {
                case 'css':
                    loaded = me.loadList.css.indexOf(name) > -1;
                    break;
                case 'template' :
                    loaded =  me.loadList.templates.indexOf(name) > -1;
                    break;
                case 'component' :
                    loaded = me.loadList.components.indexOf(name) > -1;
                    break;
                case 'script' :
                    loaded = me.loadList.scripts.indexOf(name) > -1;
                    break;
                case 'binding' :
                    loaded = me.loadList.bindings.indexOf(name) > -1;
                    break;
                default:
                    console.log('Warning invalid resource type ' +name);
                    return false;
            }

            if (loaded) {
                Peanut.logger.write("Skipped, already loaded " + name);
            }
            return loaded;
        }
        

        private toCamelCase(name: string,seperator = '-', casingType = 'pascal') {
            let names = name.split(seperator);
            let result = (casingType == 'camel') ?  names.shift() : '';
            for(let part of names) {
                let initial = part.substr(0,1);
                initial = initial.toUpperCase();
                let remainder = part.substr(1);
                result = result + initial + remainder;
            }
            return result;
        }

        /**
         * Returns IFileParseResult  (see peanut.d.ts)
         * uses prefixes @pnut, @code, @app, replaced by values from Peanut.Config
        *
         * @param name
         * @param defaultPath
         * @returns {{root: string, name: string, namespace: string}}
         */
        public  parseFileName(name: string, defaultPath: string = null) {
            defaultPath = defaultPath || Peanut.Config.values.commonRootPath;


            let result = {root: '', name: '', namespace: 'Peanut'};
            let parts = name.split('/');
            let len = parts.length;
            if (len == 1) {
                result.root = defaultPath;
                result.name = name;
            }
            else {
                if (parts[0] == '') {
                    // assume hard coded path if name starts with '/'
                    result.name = parts.pop();
                    result.root = parts.join('/') + '/';
                }
                else {
                    let pathRoot = defaultPath;
                    switch (parts[0]) {
                        case '@pnut' :
                            pathRoot = Peanut.Config.values.peanutRootPath;
                            parts.shift();
                            break;
                        case '@core' :
                            pathRoot = Peanut.Config.values.corePath;
                            parts.shift();
                            break;
                        case '@app'  :
                            result.namespace = Peanut.Config.values.vmNamespace;
                            pathRoot = Peanut.Config.values.mvvmPath;
                            parts.shift();
                            break;
                        case  '@pkg' :
                            parts.shift();
                            let subDir = parts.shift();
                            result.namespace = this.toCamelCase(subDir);
                            pathRoot = Peanut.Config.values.packagePath + subDir + '/';
                            break;
                        default:
                            pathRoot = defaultPath;
                            break;
                    }


                    result.name = parts.pop();
                    result.root = parts.length == 0 ? pathRoot : pathRoot + parts.join('/') + '/';
                }
            }
            return result;
        }

        /**
         * Used by parseComponentName
         * @param componentName
         * @returns {string}
         */
        private  nameToFileName(componentName: string) {
            let parts = componentName.split('-');
            let fileName = parts[0];
            if (parts.length > 1) {
                fileName += parts[1].charAt(0).toUpperCase() + parts[1].substring(1);
            }
            return fileName;
        }


        /**
         * Returns essentioal information used by other component routines bases on component name and prefixes
         * such as @pnut/ (see parseFileName)
         * Returns IComponentParseResult (see peanut.d.ts)
         *
         * @param componentName
         * @returns {{root: string, className: string, templateFile: string, componentName: string, namespace: string}}
         */
        private parseComponentName(componentName: string) {
            let me = this;
            if (!Peanut.Config.loaded) {
                throw "Peanut Config was not loaded.";
            }

            if (componentName.substr(0,1) !== '@') {
                componentName = '@app/' + componentName;
            }

            let parsed = me.parseFileName(componentName, Peanut.Config.values.mvvmPath);
            let fileName = me.nameToFileName(parsed.name);
            return {
                root: parsed.root,
                className: fileName+'Component',
                templateFile: fileName+'.html',
                componentName: parsed.name,
                namespace: parsed.namespace
            };
        }


        /**
         * Used by loadResources
         * @param fileName
         * @param defaultPath
         * @returns {any}
         */
        private expandFileName(fileName: string, defaultPath = null ) {
            if (!fileName) {
                return '';
            }
            if (fileName.substr(0,1) === '/' || fileName.toLowerCase().substr(0,4) === 'http') {
                return fileName;
            }
            let me = this;
            let fileExtension = 'js';
            let p = fileName.lastIndexOf('.');
            if (p == -1) {
                fileName = fileName + '.js';
            }
            else {
                fileExtension = fileName.substr(p + 1).toLowerCase();
            }
            let parsed = me.parseFileName(fileName,defaultPath);
            return parsed.root + fileExtension + '/' + parsed.name;
        }

        /**
         * Load scripts and css files located under application or core direcories. Use @pnut/ to indicate core.
         * Usually called on init to pre-load component scripts for instance registered components.
         *
         * @param names
         * @param successFunction
         */
        public loadResources(resourceList: string[], successFunction?: () => void) {
            let me = this;
            PeanutLoader.checkConfig();
            PeanutLoader.getConfig((config: IPeanutConfig) => {
                let params = [];
                for (let i = 0; i < resourceList.length; i++) {
                    let name = resourceList[i];
                    if (name && (!me.alreadyLoaded(name,'script'))) {
                        let path = (name.substr(0, 5) == '@lib:') ?
                            me.getLibrary(name, config) :
                            me.expandFileName(name, config.applicationPath);
                        if (path !== false) {
                            me.loadList.scripts.push(name);
                            params.push(path);
                        }
                    }
                }
                PeanutLoader.load(params,successFunction);
            });
        }

        public loadStyleSheets(resourceList: string[]) {
            let me = this;
            PeanutLoader.checkConfig();
            PeanutLoader.getConfig((config: IPeanutConfig) => {
                for (let i = 0; i < resourceList.length; i++) {
                    let resourceName = resourceList[i];
                    if (me.alreadyLoaded(resourceName,'css')) {
                        continue;
                    }
                    me.loadList.css.push(resourceName);
                    let parts =  resourceName.split(' media=');
                    let path = parts.shift().trim();
                    let media = parts.shift();
                    media = media ? media.trim() : null;

                    if (path.substring(0,1) === '/' || path.substring(0,5) === 'http:' || path.substring(0,6) === 'https:') {
                        me.loadCss(path,media);
                        return;
                    }
                    else if (path.substr(0, 6) == '@pnut:') {
                        path = me.getPeanutCss(path,config);
                    }
                    else if (path.substr(0, 5) == '@lib:') {
                        path = me.getLibrary(path, config);
                    }
                    else if (path.substr(0,5) == '@pkg:') {
                        let pathParts =  path.substring(5).split('/');
                        let pkgName = pathParts.shift();
                        let fileName = '/css/styles.css';
                        if (parts.length > 0) {
                            fileName = parts.pop();
                            let subdir = parts.length ? '/' + parts.join('/') + '/' : '/css/';
                            fileName = subdir + fileName;
                        }

                        path = Peanut.Config.values.packagePath + pkgName + fileName;
                    }
                    else if (path.substr(0,1) == '@') {
                        path = me.expandFileName(path, config.applicationPath);
                    }
                    else {
                        path = config.stylesPath + path;
                    }

                    if (path) {
                        me.loadCss(path,media);
                    }
                }
            });

        }

        private loadCss = (path,media=null) => {
            if (path) {
                let fileref = document.createElement("link");
                fileref.setAttribute("rel", "stylesheet");
                fileref.setAttribute("type", "text/css");
                fileref.setAttribute("href", path);
                if (media) {
                    fileref.setAttribute('media', media)
                }
                if (typeof fileref === "undefined") {
                    console.error('Failed to load stylesheet ' + path);
                }
                document.getElementsByTagName("head")[0].appendChild(fileref);
                Peanut.logger.write('Loaded stylesheet: ' + path);
            }
        };

        private getPeanutCss(path: string, config: IPeanutConfig) {
            let name = path.substr(6);
            if (config.cssOverrides.indexOf(name) === -1) {
                return config.peanutRootPath + 'styles/' + name;
            }
            return config.applicationPath + 'assets/styles/pnut/' + name;
        }

        private getLibrary (name: string, config: IPeanutConfig) {
            let key = name.substr(5);

            if (key.substr(0,6) == 'local/') { // deprecated convention but kept for backward compatibility
                return config.applicationPath + '/assets/js/' + key.substr(6);
                // return config.libraryPath + key.substr(6);
            }
            if (key in config.libraries) {
                let path = config.libraries[key];
                if (path === 'installed') {
                    return false; // library is preloaded in CMS or theme
                }
                if (path.substr(0,1) == '/' || path.substr(0,5) == 'http:' || path.substr(0,6) == 'https:') {
                    // absolute path or external CDN
                    return path;
                }
                // located in library directory
                return config.libraryPath + path;
            }
            console.log('Library "' + key + '" not in settings.ini.');
            return false;
        }

        public loadViewModel = (vmName : string, final : (viewModel: IViewModel) => void) => {
            PeanutLoader.checkConfig();
            let me = this;
            if (vmName === null) {
                console.error('No vm name provided in loadViewModel');
                return;
            }
            let context = null;
            let parts = vmName.split('#');
            if (parts.length > 1) {
                context = parts.pop();
            }
            vmName = parts.shift();
            parts = vmName.split('/');
            let prefix = '@app';
            if (vmName.substr(0,1) === '@') {
                prefix = parts.shift();
            }
            vmName = parts.pop();
            let vmClassName = vmName + 'ViewModel';
            let vmFile = prefix + '/' + parts.join('/') + '/vm/' + vmClassName;
            let parseResult = <IFileParseResult>(this.parseFileName(vmFile, Peanut.Config.values.mvvmPath));
            let vmPath = parseResult.root + parseResult.name + '.js';
            let namespace = parseResult.namespace;
            PeanutLoader.loadScript(vmPath,() => {
                Peanut.logger.write("Loading " + namespace + '.' + vmClassName);
                let vm = <IViewModel>(new window[namespace][vmClassName]);
                vm.setVmName(vmName,context);
                final(vm);
            });
        };

        /**
         * Used for html templates that are not necessarily associated with a component
         * For component templates, use loadComponentTemplate()
         *
         * @param name
         * @param successFunction
         */
        public  getHtmlTemplate(name: string, successFunction: (htmlSource: string) => void) {
            let me = this;
            if (me.alreadyLoaded(name,'template')) {
                successFunction(null);
            }
            else {
                PeanutLoader.checkConfig();
                let parsed = me.parseFileName(name, Peanut.Config.values.mvvmPath);
                let parts = parsed.name.split('-');
                let fileName = parts[0] + parts[1].charAt(0).toUpperCase() + parts[1].substring(1);
                let htmlSource = parsed.root + 'templates/' + fileName + '.html';
                PeanutLoader.loadHtml(htmlSource, successFunction);
                me.loadList.templates.push(name);
            }
        }

        public getComponentPrototype = (componentPath: IComponentParseResult) => {
            if ((window[componentPath.namespace]) && (window[componentPath.namespace][componentPath.className])) {
                return window[componentPath.namespace][componentPath.className];
            }
            return null;
        };

        private  loadComponentTemplate = (componentPath : IComponentParseResult, finalFunction : (template: any) => void ) => {
            let me = this;
            PeanutLoader.getConfig((config: IPeanutConfig) => {
                let htmlPath = componentPath.root + 'templates/' + componentPath.templateFile;

                fetch(htmlPath).then((response) => {
                    if (response.ok) {
                        return response.text();
                    }
                    else {
                        console.error('Template not found at '+htmlPath)
                        return '';
                    }
                }).then((template) => {
                    if (finalFunction) {
                        finalFunction(template);
                    }
                });

/*
                jQuery.get(htmlPath, function (template: string) {
                    // JQuery.get returns the entire parent page if the template is not found!
                    if (template.toLowerCase().indexOf('<!doctype') === 0) {
                        console.error('Template not found at '+htmlPath);
                        template = '';
                    }
                    if (finalFunction) {
                        finalFunction(template);
                    }
                });
*/
            });
        };

        private loadComponentPrototype = (componentPath: IComponentParseResult, finalFunction : (vm: any)=> void) => {
            if (window[componentPath.namespace] && window[componentPath.namespace][componentPath.className]) {
                finalFunction(window[componentPath.namespace][componentPath.className]);
            }
            else {
                let me = this;
                let src = componentPath.root +'components/' + componentPath.className + '.js';
                PeanutLoader.load(src, function () {
                    let vm = window[componentPath.namespace][componentPath.className];
                    if (finalFunction) {
                        finalFunction(vm);
                    }
                });
            }
        };

        /**
         * Load and register component prototype
         *
         * @param componentName
         * @param finalFunction
         */
        public loadAndRegisterComponentPrototype = (componentName: string, finalFunction? : (componentPath: IComponentParseResult) => void) => {
            let me = this;
            let componentPath = this.parseComponentName(componentName);
            if (me.alreadyLoaded(componentName,'component')) {
                finalFunction(componentPath);
            }
            this.loadComponentTemplate(componentPath, (template: any) => {
                this.loadComponentPrototype(componentPath,(vm: any) => {
                    me.registerKoComponent(componentName,componentPath.componentName,{
                        viewModel : vm,
                        template: template
                    });
                    // ko.components.register(componentPath.componentName, {
                    //     viewModel : vm,
                    //     template: template
                    // });
                    if (finalFunction) {
                        finalFunction(componentPath);
                    }
                })
            })
        };

        /**
         * Register component prototype
         * Assumes component previously loaded
         *
         * @param componentName
         * @param finalFunction
         */
        public registerComponentPrototype = (componentName: string, finalFunction? : (componentPath: IComponentParseResult) => void) => {
            let me = this;
            let componentPath = this.parseComponentName(componentName);

            this.loadComponentTemplate(componentPath, (template: any) => {
                let vm = this.getComponentPrototype(componentPath);
                me.registerKoComponent(componentName,componentPath.componentName,{
                    viewModel : vm,
                    template: template
                });
                // ko.components.register(componentPath.componentName, {
                //     viewModel : vm,
                //     template: template
                // });
                if (finalFunction) {
                    finalFunction(componentPath);
                }
            })
        };

        private registerKoComponent = (componentAlias: string, componentName: string, parameters: any) => {
            ko.components.register(componentName, parameters);
            this.loadList.components.push(componentAlias)
        };

        /**
         * Called from registerComponentInstance
         * vmObject can be a view model instance, or a vm factory function.
         * If an instance, it is passed directly back to the callback function, returnFunction
         * otherwise the factory function creates the instance and passes it to the callback function.
         *
         * A callback funtion is used here because the factory function might need to do some asynchronous operation
         * such as load a script. In such cases it cannot return the instance directly.
         *
         * Example useage of vm factory:
         *
         * this.application.attachComponent(
         *     'test-form',  // component name
         *
         *     // vm factory function
         *     (returnFuncton: (vm: any) => void) => {
         *         this.application.loadResources('testFormComponent.js', () => {
         *             me.testForm = new Bookstore.testFormComponent();
         *             me.testForm.setMessage('Watch this space.');
         *             returnFuncton(me.testForm);
         *         });
         *     }
         * );
         *
         * @param componentPath
         * @param vmObject
         * @param returnFunction
         */
        private getViewModelInstance(componentPath: IComponentParseResult, vmObject : any, returnFunction : (vm: any) => void) {
            // if vm factory function, call it
            if (vmObject instanceof Function)  {
                vmObject(returnFunction);
            }
            // if instance, return it
            else {
                returnFunction(vmObject);
            }
        }


        public registerComponentInstance = (componentName: string, vmInstance: any,
                                            finalFunction? : (componentPath: IComponentParseResult,vm? : any) => void) => {
            let me = this;
            if (me.alreadyLoaded(componentName, 'component')) {
                finalFunction(null, null);
                return;
            }
            let componentPath = this.parseComponentName(componentName);
            this.loadComponentTemplate(componentPath, (template: any) => {
                this.getViewModelInstance(componentPath, vmInstance, (vm: any) => {
                    me.registerKoComponent(componentName,componentPath.componentName,{
                        viewModel: {instance: vm},
                        template: template
                    });
                    // ko.components.register(componentPath.componentName, {
                    //     viewModel: {instance: vm},
                    //     template: template
                    // });

                    if (finalFunction) {
                        finalFunction(componentPath, vm);

                    }
                })
            })
        };

        /**
         * Used by application.attachComponent
         *
         * @param componentName
         * @param vmInstance
         * @param finalFunction
         */
        public registerAndBindComponentInstance = (componentName: string,  vmInstance: any, finalFunction? : () => void) => {
            if (this.alreadyLoaded(componentName,'component')) {
                finalFunction();
            }
            else {
                this.registerComponentInstance(componentName, vmInstance, (componentPath: IComponentParseResult, vm: any) => {
                    if (componentPath !== null) {
                        this.bindSection(componentPath.componentName + '-container', vm);
                    }
                    if (finalFunction) {
                        finalFunction();
                    }
                });
            }
        };


        /**
         * Recursively load and register a list of component prototypes.
         * @param componentList
         * @param finalFunction
         */
        public registerComponents = (componentList : string[], finalFunction: ()=> void) => {
            let componentName = componentList.shift();
            let me = this;
            if (componentName && !me.alreadyLoaded(componentName,'component')) {
                me.loadAndRegisterComponentPrototype(componentName, () => {
                    me.registerComponents(componentList,() => {
                        finalFunction();
                    } );
                });
            }
            else {
                finalFunction();
            }
        };

        /**
         * Use this to retrieve component view models from the server, and instantiate later
         *
         * @param componentList
         * @param finalFunction
         */
        public loadComponentPrototypes = (componentList : string[], finalFunction: ()=> void) => {
            let me = this;
            let componentName = componentList.shift();
            if (componentName && !me.alreadyLoaded(componentName,'scripts')) {
                let componentPath = this.parseComponentName(componentName);
                let src = componentPath.root + 'components/' + componentPath.className + '.js';
                PeanutLoader.load(src, function () {
                    me.loadComponentPrototypes(componentList, finalFunction);
                });
            }
            else {
                finalFunction();
            }
        };




        /*  Old version, doesn't work for some reason. Fix if new version too inefficient
        public registerComponents = (componentList : string[], finalFunction: ()=> void) => {
            let me = this;
            let scriptsList = [];
            let pathList = [];
            for(let item of componentList) {
                let componentPath = me.parseComponentName(item);
                let src = componentPath.root +'components/' + componentPath.className + '.js';
                scriptsList.push(src);
                pathList.push(componentPath);
            }
            PeanutLoader.loadScripts(scriptsList,() => {
                me.registerComponentsInList(pathList,finalFunction);
            });
        };

        private registerComponentsInList = (pathList : IComponentParseResult[], finalFunction?: ()=> void) => {
            let path = pathList.shift();
            if (path) {
                if (window[path.namespace] && window[path.namespace][path.className]) {
                    this.loadComponentTemplate(path, (template: any) => {
                        let vm = window[path.namespace][path.className];
                        ko.components.register(path.componentName, {
                            viewModel : vm,
                            template: template
                        });
                        Peanut.logger.write('Component ' + path.componentName + ' registered.');

                    });
                }
                else {
                    console.error('Component ' + path.componentName + ' was not loded');
                }
                this.registerComponentsInList(pathList,finalFunction);
            }
            else {
                if (finalFunction) {
                    finalFunction();
                }
            }
        };
*/
        private getContainerNode(containerName: string) {
            let container = document.getElementById(containerName); // messages-component-container
            if (container == null) {
                if (containerName) {
                    console.warn("Error: Container element '" + containerName + "' for section binding not found.");
                }
                else {
                    console.warn("Error: no container name for section binding.");
                }
            }
            return container;
        }

        /**
         * Apply KnockoutJS bindings to a single node without affecting decendent nodes.
         *
         * @param containerName
         * @param context a viewModel
         */
        public bindNode = (containerName: string, context: any) => {
            if (!this.alreadyLoaded(containerName,'binding')) {
                let container = this.getContainerNode(containerName);
                if (container !== null) {
                    ko.applyBindingsToNode(container, null, context);
                }
                this.loadList.bindings.push(containerName);
            }
        };


        /**
         * Apply KnockoutJS bindings to a single node and all it's decendent nodes.
         *
         * @param containerName
         * @param context - a view model
         */
        public bindSection = (containerName: string, context: any) => {
            if (!this.alreadyLoaded(containerName,'binding')) {
                let container = this.getContainerNode(containerName);
                if (container === null) {
                    return;
                }
                Peanut.logger.write('bind section: ' + containerName);
                ko.applyBindings(context, container);
                this.loadList.bindings.push(containerName);
            }
            let element = document.getElementById(containerName);
            if (element) {
                element.style.display = 'block'; // show
            }
            // jQuery("#" + containerName).show();

        };

        public static GetInputValue(oValue : KnockoutObservable<string>) {
            if (oValue !== null) {
                let value = oValue();
                if (value !== null) {
                    return value.trim();
                }
            }
            return '';
        }
    }


} // end namespace