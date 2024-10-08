/******/ (function() { // webpackBootstrap
/******/ 	var __webpack_modules__ = ([
/* 0 */,
/* 1 */
/***/ (function(module, exports) {

var __WEBPACK_AMD_DEFINE_ARRAY__, __WEBPACK_AMD_DEFINE_RESULT__;/**
 * alertifyjs 1.14.0 http://alertifyjs.com
 * AlertifyJS is a javascript framework for developing pretty browser dialogs and notifications.
 * Copyright 2024 Mohammad Younes <Mohammad@alertifyjs.com> (http://alertifyjs.com) 
 * Licensed under GPL 3 <https://opensource.org/licenses/gpl-3.0>*/
( function ( window ) {
    'use strict';
    var NOT_DISABLED_NOT_RESET = ':not(:disabled):not(.ajs-reset)';
    /**
     * Keys enum
     * @type {Object}
     */
    var keys = {
        ENTER: 13,
        ESC: 27,
        F1: 112,
        F12: 123,
        LEFT: 37,
        RIGHT: 39,
        TAB: 9
    };
    /**
     * Default options 
     * @type {Object}
     */
    var defaults = {
        autoReset:true,
        basic:false,
        closable:true,
        closableByDimmer:true,
        invokeOnCloseOff:false,
        frameless:false,
        defaultFocusOff:false,
        maintainFocus:true, //global default not per instance, applies to all dialogs
        maximizable:true,
        modal:true,
        movable:true,
        moveBounded:false,
        overflow:true,
        padding: true,
        pinnable:true,
        pinned:true,
        preventBodyShift:false, //global default not per instance, applies to all dialogs
        resizable:true,
        startMaximized:false,
        transition:'pulse',
        transitionOff:false,
        tabbable:['button', '[href]', 'input', 'select', 'textarea', '[tabindex]:not([tabindex^="-"])'+NOT_DISABLED_NOT_RESET].join(NOT_DISABLED_NOT_RESET+','),//global
        notifier:{
            delay:5,
            position:'bottom-right',
            closeButton:false,
            classes: {
                base: 'alertify-notifier',
                prefix:'ajs-',
                message: 'ajs-message',
                top: 'ajs-top',
                right: 'ajs-right',
                bottom: 'ajs-bottom',
                left: 'ajs-left',
                center: 'ajs-center',
                visible: 'ajs-visible',
                hidden: 'ajs-hidden',
                close: 'ajs-close'
            }
        },
        glossary:{
            title:'AlertifyJS',
            ok: 'OK',
            cancel: 'Cancel',
            acccpt: 'Accept',
            deny: 'Deny',
            confirm: 'Confirm',
            decline: 'Decline',
            close: 'Close',
            maximize: 'Maximize',
            restore: 'Restore',
        },
        theme:{
            input:'ajs-input',
            ok:'ajs-ok',
            cancel:'ajs-cancel',
        },
        hooks:{
            preinit:function(){},
            postinit:function(){}
        }
    };
    
    //holds open dialogs instances
    var openDialogs = [];

    /**
     * [Helper]  Adds the specified class(es) to the element.
     *
     * @element {node}      The element
     * @className {string}  One or more space-separated classes to be added to the class attribute of the element.
     * 
     * @return {undefined}
     */
    function addClass(element,classNames){
        element.className += ' ' + classNames;
    }
    
    /**
     * [Helper]  Removes the specified class(es) from the element.
     *
     * @element {node}      The element
     * @className {string}  One or more space-separated classes to be removed from the class attribute of the element.
     * 
     * @return {undefined}
     */
    function removeClass(element, classNames) {
        var original = element.className.split(' ');
        var toBeRemoved = classNames.split(' ');
        for (var x = 0; x < toBeRemoved.length; x += 1) {
            var index = original.indexOf(toBeRemoved[x]);
            if (index > -1){
                original.splice(index,1);
            }
        }
        element.className = original.join(' ');
    }

    /**
     * [Helper]  Checks if the document is RTL
     *
     * @return {Boolean} True if the document is RTL, false otherwise.
     */
    function isRightToLeft(){
        return window.getComputedStyle(document.body).direction === 'rtl';
    }
    /**
     * [Helper]  Get the document current scrollTop
     *
     * @return {Number} current document scrollTop value
     */
    function getScrollTop(){
        return ((document.documentElement && document.documentElement.scrollTop) || document.body.scrollTop);
    }

    /**
     * [Helper]  Get the document current scrollLeft
     *
     * @return {Number} current document scrollLeft value
     */
    function getScrollLeft(){
        return ((document.documentElement && document.documentElement.scrollLeft) || document.body.scrollLeft);
    }

    /**
    * Helper: clear contents
    *
    */
    function clearContents(element){
        while (element.lastChild) {
            element.removeChild(element.lastChild);
        }
    }

    /**
     * detects strings, checks for both string and String instances
     * this is unlike typeof(x) === 'string' which only accepts primitive strings
     *
     */
    function isString(thing) {
        return Object.prototype.toString.call(thing) === '[object String]';
    }

    /**
     * Extends a given prototype by merging properties from base into sub.
     *
     * @sub {Object} sub The prototype being overwritten.
     * @base {Object} base The prototype being written.
     *
     * @return {Object} The extended prototype.
     */
    function copy(src) {
        if(null === src){
            return src;
        }
        var cpy;
        if(Array.isArray(src)){
            cpy = [];
            for(var x=0;x<src.length;x+=1){
                cpy.push(copy(src[x]));
            }
            return cpy;
        }
      
        if(src instanceof Date){
            return new Date(src.getTime());
        }
      
        if(src instanceof RegExp){
            cpy = new RegExp(src.source);
            cpy.global = src.global;
            cpy.ignoreCase = src.ignoreCase;
            cpy.multiline = src.multiline;
            cpy.lastIndex = src.lastIndex;
            return cpy;
        }
        
        if(typeof src === 'object'){
            cpy = {};
            // copy dialog pototype over definition.
            for (var prop in src) {
                if (src.hasOwnProperty(prop)) {
                    cpy[prop] = copy(src[prop]);
                }
            }
            return cpy;
        }
        return src;
    }
    /**
      * Helper: destruct the dialog
      *
      */
    function destruct(instance, initialize){
        if(instance.elements){
            //delete the dom and it's references.
            var root = instance.elements.root;
            root.parentNode.removeChild(root);
            delete instance.elements;
            //copy back initial settings.
            instance.settings = copy(instance.__settings);
            //re-reference init function.
            instance.__init = initialize;
            //delete __internal variable to allow re-initialization.
            delete instance.__internal;
        }
    }

    /**
     * Test to check if passive event listeners are supported.
     */
    var IsPassiveSupported = false;
    try {
        var options = Object.defineProperty({}, 'passive', {
            get: function () {
                IsPassiveSupported = true;
            }
        });
        window.addEventListener('test', options, options);
        window.removeEventListener('test', options, options);
    } catch (e) {}

     /**
     * Removes an event listener
     *
     * @param {HTMLElement} el The EventTarget to register the listenr on.
     * @param {string} event The event type to listen for.
     * @param {Function} handler The function to handle the event.
     * @param {boolean} useCapture Specifices if the event to be dispatched to the registered listener before being dispatched to any EventTarget beneath it in the DOM tree.
     * @param {boolean} passive A Boolean which, if true, indicates that the function specified by listener will never call preventDefault().
     */
    var on = function (el, event, fn, useCapture, passive) {
        el.addEventListener(event, fn, IsPassiveSupported ? { capture: useCapture, passive: passive } : useCapture === true);
    };

    /**
     * Removes an event listener
     *
     * @param {HTMLElement} el The EventTarget to unregister the listenr from.
     * @param {string} event The event type to remove.
     * @param {Function} fn The event handler to remove.
     * @param {boolean} useCapture Specifices if the event to be dispatched to the registered listener before being dispatched to any EventTarget beneath it in the DOM tree.
     * @param {boolean} passive A Boolean which, if true, indicates that the function specified by listener will never call preventDefault().
     */
    var off = function (el, event, fn, useCapture, passive) {
        el.removeEventListener(event, fn, IsPassiveSupported ? { capture: useCapture, passive: passive } : useCapture === true);
    };

    /**
     * Prevent default event from firing
     *
     * @param  {Event} event Event object
     * @return {undefined}

    function prevent ( event ) {
        if ( event ) {
            if ( event.preventDefault ) {
                event.preventDefault();
            } else {
                event.returnValue = false;
            }
        }
    }
    */
    var transition = (function () {
        var t, type;
        var supported = false;
        var transitions = {
            'animation'        : 'animationend',
            'OAnimation'       : 'oAnimationEnd oanimationend',
            'msAnimation'      : 'MSAnimationEnd',
            'MozAnimation'     : 'animationend',
            'WebkitAnimation'  : 'webkitAnimationEnd'
        };

        for (t in transitions) {
            if (document.documentElement.style[t] !== undefined) {
                type = transitions[t];
                supported = true;
                break;
            }
        }

        return {
            type: type,
            supported: supported
        };
    }());

    /**
    * Creates event handler delegate that sends the instance as last argument.
    * 
    * @return {Function}    a function wrapper which sends the instance as last argument.
    */
    function delegate(context, method) {
        return function () {
            if (arguments.length > 0) {
                var args = [];
                for (var x = 0; x < arguments.length; x += 1) {
                    args.push(arguments[x]);
                }
                args.push(context);
                return method.apply(context, args);
            }
            return method.apply(context, [null, context]);
        };
    }
    /**
    * Helper for creating a dialog close event.
    * 
    * @return {object}
    */
    function createCloseEvent(index, button) {
        return {
            index: index,
            button: button,
            cancel: false
        };
    }
    /**
    * Helper for dispatching events.
    *
    * @param  {string} evenType The type of the event to disptach.
    * @param  {object} instance The dialog instance disptaching the event.
    *
    * @return   {any}   The result of the invoked function.
    */
    function dispatchEvent(eventType, instance) {
        if ( typeof instance.get(eventType) === 'function' ) {
            return instance.get(eventType).call(instance);
        }
    }


    /**
     * Super class for all dialogs
     *
     * @return {Object}		base dialog prototype
     */
    var dialog = (function () {
        var //holds the list of used keys.
            usedKeys = [],
            //dummy variable, used to trigger dom reflow.
            reflow = null,
            //holds body tab index in case it has any.
            tabindex = false,
            //condition for detecting safari
            isSafari = window.navigator.userAgent.indexOf('Safari') > -1 && window.navigator.userAgent.indexOf('Chrome') < 0,
            //dialog building blocks
            templates = {
                dimmer:'<div class="ajs-dimmer"></div>',
                /*tab index required to fire click event before body focus*/
                modal: '<div class="ajs-modal" tabindex="0"></div>',
                dialog: '<div class="ajs-dialog" tabindex="0"></div>',
                reset: '<button class="ajs-reset"></button>',
                commands: '<div class="ajs-commands"><button class="ajs-pin"></button><button class="ajs-maximize"></button><button class="ajs-close"></button></div>',
                header: '<div class="ajs-header"></div>',
                body: '<div class="ajs-body"></div>',
                content: '<div class="ajs-content"></div>',
                footer: '<div class="ajs-footer"></div>',
                buttons: { primary: '<div class="ajs-primary ajs-buttons"></div>', auxiliary: '<div class="ajs-auxiliary ajs-buttons"></div>' },
                button: '<button class="ajs-button"></button>',
                resizeHandle: '<div class="ajs-handle"></div>',
            },
            //common class names
            classes = {
                animationIn: 'ajs-in',
                animationOut: 'ajs-out',
                base: 'alertify',
                basic:'ajs-basic',
                capture: 'ajs-capture',
                closable:'ajs-closable',
                fixed: 'ajs-fixed',
                frameless:'ajs-frameless',
                hidden: 'ajs-hidden',
                maximize: 'ajs-maximize',
                maximized: 'ajs-maximized',
                maximizable:'ajs-maximizable',
                modeless: 'ajs-modeless',
                movable: 'ajs-movable',
                noSelection: 'ajs-no-selection',
                noOverflow: 'ajs-no-overflow',
                noPadding:'ajs-no-padding',
                pin:'ajs-pin',
                pinnable:'ajs-pinnable',
                prefix: 'ajs-',
                resizable: 'ajs-resizable',
                restore: 'ajs-restore',
                shake:'ajs-shake',
                unpinned:'ajs-unpinned',
                noTransition:'ajs-no-transition'
            };

        /**
         * Helper: initializes the dialog instance
         * 
         * @return	{Number}	The total count of currently open modals.
         */
        function initialize(instance){
            
            if(!instance.__internal){
                //invoke preinit global hook
                alertify.defaults.hooks.preinit(instance);
                //no need to expose init after this.
                delete instance.__init;
              
                //keep a copy of initial dialog settings
                if(!instance.__settings){
                    instance.__settings = copy(instance.settings);
                }
                
                //get dialog buttons/focus setup
                var setup;
                if(typeof instance.setup === 'function'){
                    setup = instance.setup();
                    setup.options = setup.options  || {};
                    setup.focus = setup.focus  || {};
                }else{
                    setup = {
                        buttons:[],
                        focus:{
                            element:null,
                            select:false
                        },
                        options:{
                        }
                    };
                }
                
                //initialize hooks object.
                if(typeof instance.hooks !== 'object'){
                    instance.hooks = {};
                }

                //copy buttons defintion
                var buttonsDefinition = [];
                if(Array.isArray(setup.buttons)){
                    for(var b=0;b<setup.buttons.length;b+=1){
                        var ref  = setup.buttons[b],
                            cpy = {};
                        for (var i in ref) {
                            if (ref.hasOwnProperty(i)) {
                                cpy[i] = ref[i];
                            }
                        }
                        buttonsDefinition.push(cpy);
                    }
                }

                var internal = instance.__internal = {
                    /**
                     * Flag holding the open state of the dialog
                     * 
                     * @type {Boolean}
                     */
                    isOpen:false,
                    /**
                     * Active element is the element that will receive focus after
                     * closing the dialog. It defaults as the body tag, but gets updated
                     * to the last focused element before the dialog was opened.
                     *
                     * @type {Node}
                     */
                    activeElement:document.body,
                    timerIn:undefined,
                    timerOut:undefined,
                    buttons: buttonsDefinition,
                    focus: setup.focus,
                    options: {
                        title: undefined,
                        modal: undefined,
                        basic:undefined,
                        frameless:undefined,
                        defaultFocusOff:undefined,
                        pinned: undefined,
                        movable: undefined,
                        moveBounded:undefined,
                        resizable: undefined,
                        autoReset: undefined,
                        closable: undefined,
                        closableByDimmer: undefined,
                        invokeOnCloseOff:undefined,
                        maximizable: undefined,
                        startMaximized: undefined,
                        pinnable: undefined,
                        transition: undefined,
                        transitionOff: undefined,
                        padding:undefined,
                        overflow:undefined,
                        onshow:undefined,
                        onclosing:undefined,
                        onclose:undefined,
                        onfocus:undefined,
                        onmove:undefined,
                        onmoved:undefined,
                        onresize:undefined,
                        onresized:undefined,
                        onmaximize:undefined,
                        onmaximized:undefined,
                        onrestore:undefined,
                        onrestored:undefined
                    },
                    resetHandler:undefined,
                    beginMoveHandler:undefined,
                    beginResizeHandler:undefined,
                    bringToFrontHandler:undefined,
                    modalClickHandler:undefined,
                    buttonsClickHandler:undefined,
                    commandsClickHandler:undefined,
                    transitionInHandler:undefined,
                    transitionOutHandler:undefined,
                    destroy:undefined
                };

                var elements = {};
                //root node
                elements.root = document.createElement('div');
                //prevent FOUC in case of async styles loading.
                elements.root.style.display = 'none';
                elements.root.className = classes.base + ' ' + classes.hidden + ' ';

                elements.root.innerHTML = templates.dimmer + templates.modal;
                
                //dimmer
                elements.dimmer = elements.root.firstChild;

                //dialog
                elements.modal = elements.root.lastChild;
                elements.modal.innerHTML = templates.dialog;
                elements.dialog = elements.modal.firstChild;
                elements.dialog.innerHTML = templates.reset + templates.commands + templates.header + templates.body + templates.footer + templates.resizeHandle + templates.reset;

                //reset links
                elements.reset = [];
                elements.reset.push(elements.dialog.firstChild);
                elements.reset.push(elements.dialog.lastChild);
                
                //commands
                elements.commands = {};
                elements.commands.container = elements.reset[0].nextSibling;
                elements.commands.pin = elements.commands.container.firstChild;
                elements.commands.maximize = elements.commands.pin.nextSibling;
                elements.commands.close = elements.commands.maximize.nextSibling;
                
                //header
                elements.header = elements.commands.container.nextSibling;

                //body
                elements.body = elements.header.nextSibling;
                elements.body.innerHTML = templates.content;
                elements.content = elements.body.firstChild;

                //footer
                elements.footer = elements.body.nextSibling;
                elements.footer.innerHTML = templates.buttons.auxiliary + templates.buttons.primary;
                
                //resize handle
                elements.resizeHandle = elements.footer.nextSibling;

                //buttons
                elements.buttons = {};
                elements.buttons.auxiliary = elements.footer.firstChild;
                elements.buttons.primary = elements.buttons.auxiliary.nextSibling;
                elements.buttons.primary.innerHTML = templates.button;
                elements.buttonTemplate = elements.buttons.primary.firstChild;
                //remove button template
                elements.buttons.primary.removeChild(elements.buttonTemplate);
                               
                for(var x=0; x < instance.__internal.buttons.length; x+=1) {
                    var button = instance.__internal.buttons[x];
                    
                    // add to the list of used keys.
                    if(usedKeys.indexOf(button.key) < 0){
                        usedKeys.push(button.key);
                    }

                    button.element = elements.buttonTemplate.cloneNode();
                    button.element.innerHTML = button.text;
                    if(typeof button.className === 'string' &&  button.className !== ''){
                        addClass(button.element, button.className);
                    }
                    for(var key in button.attrs){
                        if(key !== 'className' && button.attrs.hasOwnProperty(key)){
                            button.element.setAttribute(key, button.attrs[key]);
                        }
                    }
                    if(button.scope === 'auxiliary'){
                        elements.buttons.auxiliary.appendChild(button.element);
                    }else{
                        elements.buttons.primary.appendChild(button.element);
                    }
                }
                //make elements pubic
                instance.elements = elements;
                
                //save event handlers delegates
                internal.resetHandler = delegate(instance, onReset);
                internal.beginMoveHandler = delegate(instance, beginMove);
                internal.beginResizeHandler = delegate(instance, beginResize);
                internal.bringToFrontHandler = delegate(instance, bringToFront);
                internal.modalClickHandler = delegate(instance, modalClickHandler);
                internal.buttonsClickHandler = delegate(instance, buttonsClickHandler);
                internal.commandsClickHandler = delegate(instance, commandsClickHandler);
                internal.transitionInHandler = delegate(instance, handleTransitionInEvent);
                internal.transitionOutHandler = delegate(instance, handleTransitionOutEvent);

                //settings
                for(var opKey in internal.options){
                    if(setup.options[opKey] !== undefined){
                        // if found in user options
                        instance.set(opKey, setup.options[opKey]);
                    }else if(alertify.defaults.hasOwnProperty(opKey)) {
                        // else if found in defaults options
                        instance.set(opKey, alertify.defaults[opKey]);
                    }else if(opKey === 'title' ) {
                        // else if title key, use alertify.defaults.glossary
                        instance.set(opKey, alertify.defaults.glossary[opKey]);
                    }
                }

                // allow dom customization
                if(typeof instance.build === 'function'){
                    instance.build();
                }

                //invoke postinit global hook
                alertify.defaults.hooks.postinit(instance);
            }

            //add to the end of the DOM tree.
            document.body.appendChild(instance.elements.root);
        }

        /**
         * Helper: maintains scroll position
         *
         */
        var scrollX, scrollY;
        function saveScrollPosition(){
            scrollX = getScrollLeft();
            scrollY = getScrollTop();
        }
        function restoreScrollPosition(){
            window.scrollTo(scrollX, scrollY);
        }

        /**
         * Helper: adds/removes no-overflow class from body
         *
         */
        function ensureNoOverflow(){
            var requiresNoOverflow = 0;
            for(var x=0;x<openDialogs.length;x+=1){
                var instance = openDialogs[x];
                if(instance.isModal() || instance.isMaximized()){
                    requiresNoOverflow+=1;
                }
            }
            if(requiresNoOverflow === 0 && document.body.className.indexOf(classes.noOverflow) >= 0){
                //last open modal or last maximized one
                removeClass(document.body, classes.noOverflow);
                preventBodyShift(false);
            }else if(requiresNoOverflow > 0 && document.body.className.indexOf(classes.noOverflow) < 0){
                //first open modal or first maximized one
                preventBodyShift(true);
                addClass(document.body, classes.noOverflow);
            }
        }
        var top = '', topScroll = 0;
        /**
         * Helper: prevents body shift.
         *
         */
        function preventBodyShift(add){
            if(alertify.defaults.preventBodyShift){
                if(add && document.documentElement.scrollHeight > document.documentElement.clientHeight ){//&& openDialogs[openDialogs.length-1].elements.dialog.clientHeight <= document.documentElement.clientHeight){
                    topScroll = scrollY;
                    top = window.getComputedStyle(document.body).top;
                    addClass(document.body, classes.fixed);
                    document.body.style.top = -scrollY + 'px';
                } else if(!add) {
                    scrollY = topScroll;
                    document.body.style.top = top;
                    removeClass(document.body, classes.fixed);
                    restoreScrollPosition();
                }
            }
        }

        /**
         * Sets the name of the transition used to show/hide the dialog
         * 
         * @param {Object} instance The dilog instance.
         *
         */
        function updateTransition(instance, value, oldValue){
            if(isString(oldValue)){
                removeClass(instance.elements.root,classes.prefix +  oldValue);
            }
            addClass(instance.elements.root, classes.prefix + value);
            reflow = instance.elements.root.offsetWidth;
        }

        /**
         * Toggles the dialog no transition 
         *
         * @param {Object} instance The dilog instance.
         *
         * @return {undefined}
         */
        function updateTransitionOff(instance){
            if (instance.get('transitionOff')) {
                // add class
                addClass(instance.elements.root, classes.noTransition);
            } else {
                // remove class
                removeClass(instance.elements.root, classes.noTransition);
            }
        }

        /**
         * Toggles the dialog display mode
         *
         * @param {Object} instance The dilog instance.
         *
         * @return {undefined}
         */
        function updateDisplayMode(instance){
            if(instance.get('modal')){

                //make modal
                removeClass(instance.elements.root, classes.modeless);

                //only if open
                if(instance.isOpen()){
                    unbindModelessEvents(instance);

                    //in case a pinned modless dialog was made modal while open.
                    updateAbsPositionFix(instance);

                    ensureNoOverflow();
                }
            }else{
                //make modelss
                addClass(instance.elements.root, classes.modeless);

                //only if open
                if(instance.isOpen()){
                    bindModelessEvents(instance);

                    //in case pin/unpin was called while a modal is open
                    updateAbsPositionFix(instance);

                    ensureNoOverflow();
                }
            }
        }

        /**
         * Toggles the dialog basic view mode 
         *
         * @param {Object} instance The dilog instance.
         *
         * @return {undefined}
         */
        function updateBasicMode(instance){
            if (instance.get('basic')) {
                // add class
                addClass(instance.elements.root, classes.basic);
            } else {
                // remove class
                removeClass(instance.elements.root, classes.basic);
            }
        }

        /**
         * Toggles the dialog frameless view mode 
         *
         * @param {Object} instance The dilog instance.
         *
         * @return {undefined}
         */
        function updateFramelessMode(instance){
            if (instance.get('frameless')) {
                // add class
                addClass(instance.elements.root, classes.frameless);
            } else {
                // remove class
                removeClass(instance.elements.root, classes.frameless);
            }
        }
		
        /**
         * Helper: Brings the modeless dialog to front, attached to modeless dialogs.
         *
         * @param {Event} event Focus event
         * @param {Object} instance The dilog instance.
         *
         * @return {undefined}
         */
        function bringToFront(event, instance){
            
            // Do not bring to front if preceeded by an open modal
            var index = openDialogs.indexOf(instance);
            for(var x=index+1;x<openDialogs.length;x+=1){
                if(openDialogs[x].isModal()){
                    return;
                }
            }
			
            // Bring to front by making it the last child.
            if(document.body.lastChild !== instance.elements.root){
                document.body.appendChild(instance.elements.root);
                //also make sure its at the end of the list
                openDialogs.splice(openDialogs.indexOf(instance),1);
                openDialogs.push(instance);
                setFocus(instance);
            }
			
            return false;
        }
		
        /**
         * Helper: reflects dialogs options updates
         *
         * @param {Object} instance The dilog instance.
         * @param {String} option The updated option name.
         *
         * @return	{undefined}	
         */
        function optionUpdated(instance, option, oldValue, newValue){
            switch(option){
            case 'title':
                instance.setHeader(newValue);
                break;
            case 'modal':
                updateDisplayMode(instance);
                break;
            case 'basic':
                updateBasicMode(instance);
                break;
            case 'frameless':
                updateFramelessMode(instance);
                break;
            case 'pinned':
                updatePinned(instance);
                break;
            case 'closable':
                updateClosable(instance);
                break;
            case 'maximizable':
                updateMaximizable(instance);
                break;
            case 'pinnable':
                updatePinnable(instance);
                break;
            case 'movable':
                updateMovable(instance);
                break;
            case 'resizable':
                updateResizable(instance);
                break;
            case 'padding':
                if(newValue){
                    removeClass(instance.elements.root, classes.noPadding);
                }else if(instance.elements.root.className.indexOf(classes.noPadding) < 0){
                    addClass(instance.elements.root, classes.noPadding);
                }
                break;
            case 'overflow':
                if(newValue){
                    removeClass(instance.elements.root, classes.noOverflow);
                }else if(instance.elements.root.className.indexOf(classes.noOverflow) < 0){
                    addClass(instance.elements.root, classes.noOverflow);
                }
                break;
            case 'transition':
                updateTransition(instance,newValue, oldValue);
                break;
            case 'transitionOff':
                updateTransitionOff(instance);
                break;
            }

            // internal on option updated event
            if(typeof instance.hooks.onupdate === 'function'){
                instance.hooks.onupdate.call(instance, option, oldValue, newValue);
            }
        }
		
        /**
         * Helper: reflects dialogs options updates
         *
         * @param {Object} instance The dilog instance.
         * @param {Object} obj The object to set/get a value on/from.
         * @param {Function} callback The callback function to call if the key was found.
         * @param {String|Object} key A string specifying a propery name or a collection of key value pairs.
         * @param {Object} value Optional, the value associated with the key (in case it was a string).
         * @param {String} option The updated option name.
         *
         * @return	{Object} result object 
         *	The result objects has an 'op' property, indicating of this is a SET or GET operation.
         *		GET: 
         *		- found: a flag indicating if the key was found or not.
         *		- value: the property value.
         *		SET:
         *		- items: a list of key value pairs of the properties being set.
         *				each contains:
         *					- found: a flag indicating if the key was found or not.
         *					- key: the property key.
         *					- value: the property value.
         */
        function update(instance, obj, callback, key, value){
            var result = {op:undefined, items: [] };
            if(typeof value === 'undefined' && typeof key === 'string') {
                //get
                result.op = 'get';
                if(obj.hasOwnProperty(key)){
                    result.found = true;
                    result.value = obj[key];
                }else{
                    result.found = false;
                    result.value = undefined;
                }
            }
            else
            {
                var old;
                //set
                result.op = 'set';
                if(typeof key === 'object'){
                    //set multiple
                    var args = key;
                    for (var prop in args) {
                        if (obj.hasOwnProperty(prop)) {
                            if(obj[prop] !== args[prop]){
                                old = obj[prop];
                                obj[prop] = args[prop];
                                callback.call(instance,prop, old, args[prop]);
                            }
                            result.items.push({ 'key': prop, 'value': args[prop], 'found':true});
                        }else{
                            result.items.push({ 'key': prop, 'value': args[prop], 'found':false});
                        }
                    }
                } else if (typeof key === 'string'){
                    //set single
                    if (obj.hasOwnProperty(key)) {
                        if(obj[key] !== value){
                            old  = obj[key];
                            obj[key] = value;
                            callback.call(instance,key, old, value);
                        }
                        result.items.push({'key': key, 'value': value , 'found':true});

                    }else{
                        result.items.push({'key': key, 'value': value , 'found':false});
                    }
                } else {
                    //invalid params
                    throw new Error('args must be a string or object');
                }
            }
            return result;
        }


        /**
         * Triggers a close event.
         *
         * @param {Object} instance	The dilog instance.
         * 
         * @return {undefined}
         */
        function triggerClose(instance) {
            var found;
            triggerCallback(instance, function (button) {
                return found = instance.get('invokeOnCloseOff') !== true && (button.invokeOnClose === true);
            });
            //none of the buttons registered as onclose callback
            //close the dialog
            if (!found && instance.isOpen()) {
                instance.close();
            }
        }

        /**
         * Dialogs commands event handler, attached to the dialog commands element.
         *
         * @param {Event} event	DOM event object.
         * @param {Object} instance	The dilog instance.
         * 
         * @return {undefined}
         */
        function commandsClickHandler(event, instance) {
            var target = event.srcElement || event.target;
            switch (target) {
            case instance.elements.commands.pin:
                if (!instance.isPinned()) {
                    pin(instance);
                } else {
                    unpin(instance);
                }
                break;
            case instance.elements.commands.maximize:
                if (!instance.isMaximized()) {
                    maximize(instance);
                } else {
                    restore(instance);
                }
                break;
            case instance.elements.commands.close:
                triggerClose(instance);
                break;
            }
            return false;
        }

        /**
         * Helper: pins the modeless dialog.
         *
         * @param {Object} instance	The dialog instance.
         * 
         * @return {undefined}
         */
        function pin(instance) {
            //pin the dialog
            instance.set('pinned', true);
        }

        /**
         * Helper: unpins the modeless dialog.
         *
         * @param {Object} instance	The dilog instance.
         * 
         * @return {undefined}
         */
        function unpin(instance) {
            //unpin the dialog 
            instance.set('pinned', false);
        }


        /**
         * Helper: enlarges the dialog to fill the entire screen.
         *
         * @param {Object} instance	The dilog instance.
         * 
         * @return {undefined}
         */
        function maximize(instance) {
            // allow custom `onmaximize` method
            dispatchEvent('onmaximize', instance);
            //maximize the dialog 
            addClass(instance.elements.root, classes.maximized);
            if (instance.isOpen()) {
                ensureNoOverflow();
            }
            // allow custom `onmaximized` method
            dispatchEvent('onmaximized', instance);
        }

        /**
         * Helper: returns the dialog to its former size.
         *
         * @param {Object} instance	The dilog instance.
         * 
         * @return {undefined}
         */
        function restore(instance) {
            // allow custom `onrestore` method
            dispatchEvent('onrestore', instance);
            //maximize the dialog 
            removeClass(instance.elements.root, classes.maximized);
            if (instance.isOpen()) {
                ensureNoOverflow();
            }
            // allow custom `onrestored` method
            dispatchEvent('onrestored', instance);
        }

        /**
         * Show or hide the maximize box.
         *
         * @param {Object} instance The dilog instance.
         * @param {Boolean} on True to add the behavior, removes it otherwise.
         *
         * @return {undefined}
         */
        function updatePinnable(instance) {
            if (instance.get('pinnable')) {
                // add class
                addClass(instance.elements.root, classes.pinnable);
            } else {
                // remove class
                removeClass(instance.elements.root, classes.pinnable);
            }
        }

        /**
         * Helper: Fixes the absolutly positioned modal div position.
         *
         * @param {Object} instance The dialog instance.
         *
         * @return {undefined}
         */
        function addAbsPositionFix(instance) {
            var scrollLeft = getScrollLeft();
            instance.elements.modal.style.marginTop = getScrollTop() + 'px';
            instance.elements.modal.style.marginLeft = scrollLeft + 'px';
            instance.elements.modal.style.marginRight = (-scrollLeft) + 'px';
        }

        /**
         * Helper: Removes the absolutly positioned modal div position fix.
         *
         * @param {Object} instance The dialog instance.
         *
         * @return {undefined}
         */
        function removeAbsPositionFix(instance) {
            var marginTop = parseInt(instance.elements.modal.style.marginTop, 10);
            var marginLeft = parseInt(instance.elements.modal.style.marginLeft, 10);
            instance.elements.modal.style.marginTop = '';
            instance.elements.modal.style.marginLeft = '';
            instance.elements.modal.style.marginRight = '';

            if (instance.isOpen()) {
                var top = 0,
                    left = 0
                ;
                if (instance.elements.dialog.style.top !== '') {
                    top = parseInt(instance.elements.dialog.style.top, 10);
                }
                instance.elements.dialog.style.top = (top + (marginTop - getScrollTop())) + 'px';

                if (instance.elements.dialog.style.left !== '') {
                    left = parseInt(instance.elements.dialog.style.left, 10);
                }
                instance.elements.dialog.style.left = (left + (marginLeft - getScrollLeft())) + 'px';
            }
        }
        /**
         * Helper: Adds/Removes the absolutly positioned modal div position fix based on its pinned setting.
         *
         * @param {Object} instance The dialog instance.
         *
         * @return {undefined}
         */
        function updateAbsPositionFix(instance) {
            // if modeless and unpinned add fix
            if (!instance.get('modal') && !instance.get('pinned')) {
                addAbsPositionFix(instance);
            } else {
                removeAbsPositionFix(instance);
            }
        }
        /**
         * Toggles the dialog position lock | modeless only.
         *
         * @param {Object} instance The dilog instance.
         * @param {Boolean} on True to make it modal, false otherwise.
         *
         * @return {undefined}
         */
        function updatePinned(instance) {
            if (instance.get('pinned')) {
                removeClass(instance.elements.root, classes.unpinned);
                if (instance.isOpen()) {
                    removeAbsPositionFix(instance);
                }
            } else {
                addClass(instance.elements.root, classes.unpinned);
                if (instance.isOpen() && !instance.isModal()) {
                    addAbsPositionFix(instance);
                }
            }
        }

        /**
         * Show or hide the maximize box.
         *
         * @param {Object} instance The dilog instance.
         * @param {Boolean} on True to add the behavior, removes it otherwise.
         *
         * @return {undefined}
         */
        function updateMaximizable(instance) {
            if (instance.get('maximizable')) {
                // add class
                addClass(instance.elements.root, classes.maximizable);
            } else {
                // remove class
                removeClass(instance.elements.root, classes.maximizable);
            }
        }

        /**
         * Show or hide the close box.
         *
         * @param {Object} instance The dilog instance.
         * @param {Boolean} on True to add the behavior, removes it otherwise.
         *
         * @return {undefined}
         */
        function updateClosable(instance) {
            if (instance.get('closable')) {
                // add class
                addClass(instance.elements.root, classes.closable);
                bindClosableEvents(instance);
            } else {
                // remove class
                removeClass(instance.elements.root, classes.closable);
                unbindClosableEvents(instance);
            }
        }

        
        var cancelClick = false,// flag to cancel click event if already handled by end resize event (the mousedown, mousemove, mouseup sequence fires a click event.).
            modalClickHandlerTS=0 // stores last click timestamp to prevent executing the handler twice on double click.
            ;

        /**
         * Helper: closes the modal dialog when clicking the modal
         *
         * @param {Event} event	DOM event object.
         * @param {Object} instance The dilog instance.
         *
         * @return {undefined}
         */
        function modalClickHandler(event, instance) {
            if(event.timeStamp - modalClickHandlerTS > 200 && (modalClickHandlerTS = event.timeStamp) && !cancelClick){
                var target = event.srcElement || event.target;
                if (instance.get('closableByDimmer') === true && target === instance.elements.modal) {
                    triggerClose(instance);
                }
            }
            cancelClick = false;
        }

        // stores last call timestamp to prevent triggering the callback twice.
        var callbackTS = 0;
        // flag to cancel keyup event if already handled by click event (pressing Enter on a focusted button).
        var cancelKeyup = false;
        /** 
         * Helper: triggers a button callback
         *
         * @param {Object}		The dilog instance.
         * @param {Function}	Callback to check which button triggered the event.
         *
         * @return {undefined}
         */
        function triggerCallback(instance, check) {
            if(Date.now() - callbackTS > 200 && (callbackTS = Date.now())){
                for (var idx = 0; idx < instance.__internal.buttons.length; idx += 1) {
                    var button = instance.__internal.buttons[idx];
                    if (!button.element.disabled && check(button)) {
                        var closeEvent = createCloseEvent(idx, button);
                        if (typeof instance.callback === 'function') {
                            instance.callback.apply(instance, [closeEvent]);
                        }
                        //close the dialog only if not canceled.
                        if (closeEvent.cancel === false) {
                            instance.close();
                        }
                        break;
                    }
                }
            }
        }

        /**
         * Clicks event handler, attached to the dialog footer.
         *
         * @param {Event}		DOM event object.
         * @param {Object}		The dilog instance.
         * 
         * @return {undefined}
         */
        function buttonsClickHandler(event, instance) {
            var target = event.srcElement || event.target;
            triggerCallback(instance, function (button) {
                // if this button caused the click, cancel keyup event
                return button.element.contains(target) && (cancelKeyup = true);
            });
        }

        /**
         * Keyup event handler, attached to the document.body
         *
         * @param {Event}		DOM event object.
         * @param {Object}		The dilog instance.
         * 
         * @return {undefined}
         */
        function keyupHandler(event) {
            //hitting enter while button has focus will trigger keyup too.
            //ignore if handled by clickHandler
            if (cancelKeyup) {
                cancelKeyup = false;
                return;
            }
            var instance = openDialogs[openDialogs.length - 1];
            var keyCode = event.keyCode;
            if (instance.__internal.buttons.length === 0 && keyCode === keys.ESC && instance.get('closable') === true) {
                triggerClose(instance);
                return false;
            }else if (usedKeys.indexOf(keyCode) > -1) {
                triggerCallback(instance, function (button) {
                    return button.key === keyCode;
                });
                return false;
            }
        }
        /**
        * Keydown event handler, attached to the document.body
        *
        * @param {Event}		DOM event object.
        * @param {Object}		The dilog instance.
        * 
        * @return {undefined}
        */
        function keydownHandler(event) {
            var instance = openDialogs[openDialogs.length - 1];
            var keyCode = event.keyCode;
            if (keyCode === keys.LEFT || keyCode === keys.RIGHT) {
                var buttons = instance.__internal.buttons;
                for (var x = 0; x < buttons.length; x += 1) {
                    if (document.activeElement === buttons[x].element) {
                        switch (keyCode) {
                        case keys.LEFT:
                            buttons[(x || buttons.length) - 1].element.focus();
                            return;
                        case keys.RIGHT:
                            buttons[(x + 1) % buttons.length].element.focus();
                            return;
                        }
                    }
                }
            }else if (keyCode < keys.F12 + 1 && keyCode > keys.F1 - 1 && usedKeys.indexOf(keyCode) > -1) {
                event.preventDefault();
                event.stopPropagation();
                triggerCallback(instance, function (button) {
                    return button.key === keyCode;
                });
                return false;
            }
        }


        /**
         * Sets focus to proper dialog element
         *
         * @param {Object} instance The dilog instance.
         * @param {Node} [resetTarget=undefined] DOM element to reset focus to.
         *
         * @return {undefined}
         */
        function setFocus(instance, resetTarget) {
            // reset target has already been determined.
            if (resetTarget) {
                resetTarget.focus();
            } else {
                // current instance focus settings
                var focus = instance.__internal.focus;
                // the focus element.
                var element = focus.element;

                switch (typeof focus.element) {
                // a number means a button index
                case 'number':
                    if (instance.__internal.buttons.length > focus.element) {
                        //in basic view, skip focusing the buttons.
                        if (instance.get('basic') === true) {
                            element = instance.elements.reset[0];
                        } else {
                            element = instance.__internal.buttons[focus.element].element;
                        }
                    }
                    break;
                // a string means querySelector to select from dialog body contents.
                case 'string':
                    element = instance.elements.body.querySelector(focus.element);
                    break;
                // a function should return the focus element.
                case 'function':
                    element = focus.element.call(instance);
                    break;
                }

                // if no focus element, default to first reset element.
                if (instance.get('defaultFocusOff') === true || ((typeof element === 'undefined' || element === null) && instance.__internal.buttons.length === 0)) {
                    element = instance.elements.reset[0];
                }
                // focus
                if (element && element.focus) {
                    element.focus();
                    // if selectable
                    if (focus.select && element.select) {
                        element.select();
                    }
                }
            }
        }

        /**
         * Focus event handler, attached to document.body and dialogs own reset links.
         * handles the focus for modal dialogs only.
         *
         * @param {Event} event DOM focus event object.
         * @param {Object} instance The dilog instance.
         *
         * @return {undefined}
         */
        function onReset(event, instance) {

            // should work on last modal if triggered from document.body 
            if (!instance) {
                for (var x = openDialogs.length - 1; x > -1; x -= 1) {
                    if (openDialogs[x].isModal()) {
                        instance = openDialogs[x];
                        break;
                    }
                }
            }

            if(instance) {
                // if modal
                if (instance.isModal()) {
                    // determine reset target to enable forward/backward tab cycle.
                    var firstReset = instance.elements.reset[0],
                        lastReset = instance.elements.reset[1],
                        lastFocusedElement = event.relatedTarget,
                        within = instance.elements.root.contains(lastFocusedElement),
                        target = event.srcElement || event.target,
                        resetTarget;

                    //if the previous focused element element was outside the modal do nthing
                    if(  /*first show */
                        (target === firstReset && !within) ||
                         /*focus cycle */
                        (target === lastReset && lastFocusedElement === firstReset)){
                        return;
                    }else if(target === lastReset || target === document.body){
                        resetTarget = firstReset;
                    }else if(target === firstReset && lastFocusedElement === lastReset){
                        resetTarget = findTabbable(instance);
                    }else if(target === firstReset && within){
                        resetTarget = findTabbable(instance, true);
                    }
                    // focus
                    setFocus(instance, resetTarget);
                }
            }
        }
        function findTabbable(instance, last){
            var tabbables = [].slice.call(instance.elements.dialog.querySelectorAll(defaults.tabbable));
            if(last){
                tabbables.reverse();
            }
            for(var x=0;x<tabbables.length;x+=1){
                var tabbable = tabbables[x];
                //check if visible
                if(!!(tabbable.offsetParent || tabbable.offsetWidth || tabbable.offsetHeight || tabbable.getClientRects().length)){
                    return tabbable;
                }
            }
        }
        function recycleTab(event) {
            var instance = openDialogs[openDialogs.length - 1];
            if (instance && event.shiftKey && event.keyCode === keys.TAB) {
                instance.elements.reset[1].focus();
            }
        }
        /**
         * Transition in transitionend event handler. 
         *
         * @param {Event}		TransitionEnd event object.
         * @param {Object}		The dilog instance.
         *
         * @return {undefined}
         */
        function handleTransitionInEvent(event, instance) {
            // clear the timer
            clearTimeout(instance.__internal.timerIn);

            // once transition is complete, set focus
            setFocus(instance);

            // allow handling key up after transition ended.
            cancelKeyup = false;

            // allow custom `onfocus` method
            dispatchEvent('onfocus', instance);

            // unbind the event
            off(instance.elements.dialog, transition.type, instance.__internal.transitionInHandler);

            removeClass(instance.elements.root, classes.animationIn);
        }

        /**
         * Transition out transitionend event handler. 
         *
         * @param {Event}		TransitionEnd event object.
         * @param {Object}		The dilog instance.
         *
         * @return {undefined}
         */
        function handleTransitionOutEvent(event, instance) {
            // clear the timer
            clearTimeout(instance.__internal.timerOut);
            // unbind the event
            off(instance.elements.dialog, transition.type, instance.__internal.transitionOutHandler);

            // reset move updates
            resetMove(instance);
            // reset resize updates
            resetResize(instance);

            // restore if maximized
            if (instance.isMaximized() && !instance.get('startMaximized')) {
                restore(instance);
            }

            //destory the instance
            if (typeof instance.__internal.destroy === 'function') {
                instance.__internal.destroy.apply(instance);
            }
        }
        /* Controls moving a dialog around */
        //holde the current moving instance
        var movable = null,
            //holds the current X offset when move starts
            offsetX = 0,
            //holds the current Y offset when move starts
            offsetY = 0,
            xProp = 'pageX',
            yProp = 'pageY',
            bounds = null,
            refreshTop = false,
            moveDelegate = null
        ;

        /**
         * Helper: sets the element top/left coordinates
         *
         * @param {Event} event	DOM event object.
         * @param {Node} element The element being moved.
         * 
         * @return {undefined}
         */
        function moveElement(event, element) {
            var left = (event[xProp] - offsetX),
                top  = (event[yProp] - offsetY);

            if(refreshTop){
                top -= document.body.scrollTop;
            }
           
            element.style.left = left + 'px';
            element.style.top = top + 'px';
           
        }
        /**
         * Helper: sets the element top/left coordinates within screen bounds
         *
         * @param {Event} event	DOM event object.
         * @param {Node} element The element being moved.
         * 
         * @return {undefined}
         */
        function moveElementBounded(event, element) {
            var left = (event[xProp] - offsetX),
                top  = (event[yProp] - offsetY);

            if(refreshTop){
                top -= document.body.scrollTop;
            }
            
            element.style.left = Math.min(bounds.maxLeft, Math.max(bounds.minLeft, left)) + 'px';
            if(refreshTop){
                element.style.top = Math.min(bounds.maxTop, Math.max(bounds.minTop, top)) + 'px';
            }else{
                element.style.top = Math.max(bounds.minTop, top) + 'px';
            }
        }
            

        /**
         * Triggers the start of a move event, attached to the header element mouse down event.
         * Adds no-selection class to the body, disabling selection while moving.
         *
         * @param {Event} event	DOM event object.
         * @param {Object} instance The dilog instance.
         * 
         * @return {Boolean} false
         */
        function beginMove(event, instance) {
            if (resizable === null && !instance.isMaximized() && instance.get('movable')) {
                var eventSrc, left=0, top=0;
                if (event.type === 'touchstart') {
                    event.preventDefault();
                    eventSrc = event.targetTouches[0];
                    xProp = 'clientX';
                    yProp = 'clientY';
                } else if (event.button === 0) {
                    eventSrc = event;
                }

                if (eventSrc) {

                    var element = instance.elements.dialog;
                    addClass(element, classes.capture);

                    if (element.style.left) {
                        left = parseInt(element.style.left, 10);
                    }

                    if (element.style.top) {
                        top = parseInt(element.style.top, 10);
                    }
                    
                    offsetX = eventSrc[xProp] - left;
                    offsetY = eventSrc[yProp] - top;

                    if(instance.isModal()){
                        offsetY += instance.elements.modal.scrollTop;
                    }else if(instance.isPinned()){
                        offsetY -= document.body.scrollTop;
                    }
                    
                    if(instance.get('moveBounded')){
                        var current = element,
                            offsetLeft = -left,
                            offsetTop = -top;
                        
                        //calc offset
                        do {
                            offsetLeft += current.offsetLeft;
                            offsetTop += current.offsetTop;
                        } while (current = current.offsetParent);
                        
                        bounds = {
                            maxLeft : offsetLeft,
                            minLeft : -offsetLeft,
                            maxTop  : document.documentElement.clientHeight - element.clientHeight - offsetTop,
                            minTop  : -offsetTop
                        };
                        moveDelegate = moveElementBounded;
                    }else{
                        bounds = null;
                        moveDelegate = moveElement;
                    }
                    
                    // allow custom `onmove` method
                    dispatchEvent('onmove', instance);

                    refreshTop = !instance.isModal() && instance.isPinned();
                    movable = instance;
                    moveDelegate(eventSrc, element);
                    addClass(document.body, classes.noSelection);
                    return false;
                }
            }
        }

        /**
         * The actual move handler,  attached to document.body mousemove event.
         *
         * @param {Event} event	DOM event object.
         * 
         * @return {undefined}
         */
        function move(event) {
            if (movable) {
                var eventSrc;
                if (event.type === 'touchmove') {
                    event.preventDefault();
                    eventSrc = event.targetTouches[0];
                } else if (event.button === 0) {
                    eventSrc = event;
                }
                if (eventSrc) {
                    moveDelegate(eventSrc, movable.elements.dialog);
                }
            }
        }

        /**
         * Triggers the end of a move event,  attached to document.body mouseup event.
         * Removes no-selection class from document.body, allowing selection.
         *
         * @return {undefined}
         */
        function endMove() {
            if (movable) {
                var instance = movable;
                movable = bounds = null;
                removeClass(document.body, classes.noSelection);
                removeClass(instance.elements.dialog, classes.capture);
                // allow custom `onmoved` method
                dispatchEvent('onmoved', instance);
            }
        }

        /**
         * Resets any changes made by moving the element to its original state,
         *
         * @param {Object} instance The dilog instance.
         *
         * @return {undefined}
         */
        function resetMove(instance) {
            movable = null;
            var element = instance.elements.dialog;
            element.style.left = element.style.top = '';
        }

        /**
         * Updates the dialog move behavior.
         *
         * @param {Object} instance The dilog instance.
         * @param {Boolean} on True to add the behavior, removes it otherwise.
         *
         * @return {undefined}
         */
        function updateMovable(instance) {
            if (instance.get('movable')) {
                // add class
                addClass(instance.elements.root, classes.movable);
                if (instance.isOpen()) {
                    bindMovableEvents(instance);
                }
            } else {

                //reset
                resetMove(instance);
                // remove class
                removeClass(instance.elements.root, classes.movable);
                if (instance.isOpen()) {
                    unbindMovableEvents(instance);
                }
            }
        }

        /* Controls moving a dialog around */
        //holde the current instance being resized		
        var resizable = null,
            //holds the staring left offset when resize starts.
            startingLeft = Number.Nan,
            //holds the staring width when resize starts.
            startingWidth = 0,
            //holds the initial width when resized for the first time.
            minWidth = 0,
            //holds the offset of the resize handle.
            handleOffset = 0
        ;

        /**
         * Helper: sets the element width/height and updates left coordinate if neccessary.
         *
         * @param {Event} event	DOM mousemove event object.
         * @param {Node} element The element being moved.
         * @param {Boolean} pinned A flag indicating if the element being resized is pinned to the screen.
         * 
         * @return {undefined}
         */
        function resizeElement(event, element, pageRelative) {

            //calculate offsets from 0,0
            var current = element;
            var offsetLeft = 0;
            var offsetTop = 0;
            do {
                offsetLeft += current.offsetLeft;
                offsetTop += current.offsetTop;
            } while (current = current.offsetParent);

            // determine X,Y coordinates.
            var X, Y;
            if (pageRelative === true) {
                X = event.pageX;
                Y = event.pageY;
            } else {
                X = event.clientX;
                Y = event.clientY;
            }
            // rtl handling
            var isRTL = isRightToLeft();
            if (isRTL) {
                // reverse X 
                X = document.body.offsetWidth - X;
                // if has a starting left, calculate offsetRight
                if (!isNaN(startingLeft)) {
                    offsetLeft = document.body.offsetWidth - offsetLeft - element.offsetWidth;
                }
            }

            // set width/height
            element.style.height = (Y - offsetTop + handleOffset) + 'px';
            element.style.width = (X - offsetLeft + handleOffset) + 'px';

            // if the element being resized has a starting left, maintain it.
            // the dialog is centered, divide by half the offset to maintain the margins.
            if (!isNaN(startingLeft)) {
                var diff = Math.abs(element.offsetWidth - startingWidth) * 0.5;
                if (isRTL) {
                    //negate the diff, why?
                    //when growing it should decrease left
                    //when shrinking it should increase left
                    diff *= -1;
                }
                if (element.offsetWidth > startingWidth) {
                    //growing
                    element.style.left = (startingLeft + diff) + 'px';
                } else if (element.offsetWidth >= minWidth) {
                    //shrinking
                    element.style.left = (startingLeft - diff) + 'px';
                }
            }
        }

        /**
         * Triggers the start of a resize event, attached to the resize handle element mouse down event.
         * Adds no-selection class to the body, disabling selection while moving.
         *
         * @param {Event} event	DOM event object.
         * @param {Object} instance The dilog instance.
         * 
         * @return {Boolean} false
         */
        function beginResize(event, instance) {
            if (!instance.isMaximized()) {
                var eventSrc;
                if (event.type === 'touchstart') {
                    event.preventDefault();
                    eventSrc = event.targetTouches[0];
                } else if (event.button === 0) {
                    eventSrc = event;
                }
                if (eventSrc) {
                    // allow custom `onresize` method
                    dispatchEvent('onresize', instance);
                    
                    resizable = instance;
                    handleOffset = instance.elements.resizeHandle.offsetHeight / 2;
                    var element = instance.elements.dialog;
                    addClass(element, classes.capture);
                    startingLeft = parseInt(element.style.left, 10);
                    element.style.height = element.offsetHeight + 'px';
                    element.style.minHeight = instance.elements.header.offsetHeight + instance.elements.footer.offsetHeight + 'px';
                    element.style.width = (startingWidth = element.offsetWidth) + 'px';

                    if (element.style.maxWidth !== 'none') {
                        element.style.minWidth = (minWidth = element.offsetWidth) + 'px';
                    }
                    element.style.maxWidth = 'none';
                    addClass(document.body, classes.noSelection);
                    return false;
                }
            }
        }

        /**
         * The actual resize handler,  attached to document.body mousemove event.
         *
         * @param {Event} event	DOM event object.
         * 
         * @return {undefined}
         */
        function resize(event) {
            if (resizable) {
                var eventSrc;
                if (event.type === 'touchmove') {
                    event.preventDefault();
                    eventSrc = event.targetTouches[0];
                } else if (event.button === 0) {
                    eventSrc = event;
                }
                if (eventSrc) {
                    resizeElement(eventSrc, resizable.elements.dialog, !resizable.get('modal') && !resizable.get('pinned'));
                }
            }
        }

        /**
         * Triggers the end of a resize event,  attached to document.body mouseup event.
         * Removes no-selection class from document.body, allowing selection.
         *
         * @return {undefined}
         */
        function endResize() {
            if (resizable) {
                var instance = resizable;
                resizable = null;
                removeClass(document.body, classes.noSelection);
                removeClass(instance.elements.dialog, classes.capture);
                cancelClick = true;
                // allow custom `onresized` method
                dispatchEvent('onresized', instance);
            }
        }

        /**
         * Resets any changes made by resizing the element to its original state.
         *
         * @param {Object} instance The dilog instance.
         *
         * @return {undefined}
         */
        function resetResize(instance) {
            resizable = null;
            var element = instance.elements.dialog;
            if (element.style.maxWidth === 'none') {
                //clear inline styles.
                element.style.maxWidth = element.style.minWidth = element.style.width = element.style.height = element.style.minHeight = element.style.left = '';
                //reset variables.
                startingLeft = Number.Nan;
                startingWidth = minWidth = handleOffset = 0;
            }
        }


        /**
         * Updates the dialog move behavior.
         *
         * @param {Object} instance The dilog instance.
         * @param {Boolean} on True to add the behavior, removes it otherwise.
         *
         * @return {undefined}
         */
        function updateResizable(instance) {
            if (instance.get('resizable')) {
                // add class
                addClass(instance.elements.root, classes.resizable);
                if (instance.isOpen()) {
                    bindResizableEvents(instance);
                }
            } else {
                //reset
                resetResize(instance);
                // remove class
                removeClass(instance.elements.root, classes.resizable);
                if (instance.isOpen()) {
                    unbindResizableEvents(instance);
                }
            }
        }

        /**
         * Reset move/resize on window resize.
         *
         * @param {Event} event	window resize event object.
         *
         * @return {undefined}
         */
        function windowResize(/*event*/) {
            for (var x = 0; x < openDialogs.length; x += 1) {
                var instance = openDialogs[x];
                if (instance.get('autoReset')) {
                    resetMove(instance);
                    resetResize(instance);
                }
            }
        }
        /**
         * Bind dialogs events
         *
         * @param {Object} instance The dilog instance.
         *
         * @return {undefined}
         */
        function bindEvents(instance) {
            // if first dialog, hook global handlers
            if (openDialogs.length === 1) {
                //global
                on(window, 'resize', windowResize);
                on(document.body, 'keyup', keyupHandler);
                on(document.body, 'keydown', keydownHandler);
                on(document.body, 'focus', onReset);

                //move
                on(document.documentElement, 'mousemove', move);
                on(document.documentElement, 'touchmove', move, false, false);
                on(document.documentElement, 'mouseup', endMove);
                on(document.documentElement, 'touchend', endMove);
                //resize
                on(document.documentElement, 'mousemove', resize);
                on(document.documentElement, 'touchmove', resize, false, false);
                on(document.documentElement, 'mouseup', endResize);
                on(document.documentElement, 'touchend', endResize);
            }

            // common events
            on(instance.elements.commands.container, 'click', instance.__internal.commandsClickHandler);
            on(instance.elements.footer, 'click', instance.__internal.buttonsClickHandler);
            on(instance.elements.reset[0], 'focusin', instance.__internal.resetHandler);
            on(instance.elements.reset[0], 'keydown', recycleTab);
            on(instance.elements.reset[1], 'focusin', instance.__internal.resetHandler);

            //prevent handling key up when dialog is being opened by a key stroke.
            cancelKeyup = true;
            // hook in transition handler
            on(instance.elements.dialog, transition.type, instance.__internal.transitionInHandler);

            // modelss only events
            if (!instance.get('modal')) {
                bindModelessEvents(instance);
            }

            // resizable
            if (instance.get('resizable')) {
                bindResizableEvents(instance);
            }

            // movable
            if (instance.get('movable')) {
                bindMovableEvents(instance);
            }
        }

        /**
         * Unbind dialogs events
         *
         * @param {Object} instance The dilog instance.
         *
         * @return {undefined}
         */
        function unbindEvents(instance) {
            // if last dialog, remove global handlers
            if (openDialogs.length === 1) {
                //global
                off(window, 'resize', windowResize);
                off(document.body, 'keyup', keyupHandler);
                off(document.body, 'keydown', keydownHandler);
                off(document.body, 'focus', onReset);
                //move
                off(document.documentElement, 'mousemove', move);
                off(document.documentElement, 'mouseup', endMove);
                //resize
                off(document.documentElement, 'mousemove', resize);
                off(document.documentElement, 'mouseup', endResize);
            }

            // common events
            off(instance.elements.commands.container, 'click', instance.__internal.commandsClickHandler);
            off(instance.elements.footer, 'click', instance.__internal.buttonsClickHandler);
            off(instance.elements.reset[0], 'focusin', instance.__internal.resetHandler);
            off(instance.elements.reset[0], 'keydown', recycleTab);
            off(instance.elements.reset[1], 'focusin', instance.__internal.resetHandler);

            // hook out transition handler
            on(instance.elements.dialog, transition.type, instance.__internal.transitionOutHandler);

            // modelss only events
            if (!instance.get('modal')) {
                unbindModelessEvents(instance);
            }

            // movable
            if (instance.get('movable')) {
                unbindMovableEvents(instance);
            }

            // resizable
            if (instance.get('resizable')) {
                unbindResizableEvents(instance);
            }

        }

        /**
         * Bind modeless specific events
         *
         * @param {Object} instance The dilog instance.
         *
         * @return {undefined}
         */
        function bindModelessEvents(instance) {
            on(instance.elements.dialog, 'focus', instance.__internal.bringToFrontHandler, true);
        }

        /**
         * Unbind modeless specific events
         *
         * @param {Object} instance The dilog instance.
         *
         * @return {undefined}
         */
        function unbindModelessEvents(instance) {
            off(instance.elements.dialog, 'focus', instance.__internal.bringToFrontHandler, true);
        }



        /**
         * Bind movable specific events
         *
         * @param {Object} instance The dilog instance.
         *
         * @return {undefined}
         */
        function bindMovableEvents(instance) {
            on(instance.elements.header, 'mousedown', instance.__internal.beginMoveHandler);
            on(instance.elements.header, 'touchstart', instance.__internal.beginMoveHandler, false, false);
        }

        /**
         * Unbind movable specific events
         *
         * @param {Object} instance The dilog instance.
         *
         * @return {undefined}
         */
        function unbindMovableEvents(instance) {
            off(instance.elements.header, 'mousedown', instance.__internal.beginMoveHandler);
            off(instance.elements.header, 'touchstart', instance.__internal.beginMoveHandler, false, false);
        }



        /**
         * Bind resizable specific events
         *
         * @param {Object} instance The dilog instance.
         *
         * @return {undefined}
         */
        function bindResizableEvents(instance) {
            on(instance.elements.resizeHandle, 'mousedown', instance.__internal.beginResizeHandler);
            on(instance.elements.resizeHandle, 'touchstart', instance.__internal.beginResizeHandler, false, false);
        }

        /**
         * Unbind resizable specific events
         *
         * @param {Object} instance The dilog instance.
         *
         * @return {undefined}
         */
        function unbindResizableEvents(instance) {
            off(instance.elements.resizeHandle, 'mousedown', instance.__internal.beginResizeHandler);
            off(instance.elements.resizeHandle, 'touchstart', instance.__internal.beginResizeHandler, false, false);
        }

        /**
         * Bind closable events
         *
         * @param {Object} instance The dilog instance.
         *
         * @return {undefined}
         */
        function bindClosableEvents(instance) {
            on(instance.elements.modal, 'click', instance.__internal.modalClickHandler);
        }

        /**
         * Unbind closable specific events
         *
         * @param {Object} instance The dilog instance.
         *
         * @return {undefined}
         */
        function unbindClosableEvents(instance) {
            off(instance.elements.modal, 'click', instance.__internal.modalClickHandler);
        }
        // dialog API
        return {
            __init:initialize,
            /**
             * Check if dialog is currently open
             *
             * @return {Boolean}
             */
            isOpen: function () {
                return this.__internal.isOpen;
            },
            isModal: function (){
                return this.elements.root.className.indexOf(classes.modeless) < 0;
            },
            isMaximized:function(){
                return this.elements.root.className.indexOf(classes.maximized) > -1;
            },
            isPinned:function(){
                return this.elements.root.className.indexOf(classes.unpinned) < 0;
            },
            maximize:function(){
                if(!this.isMaximized()){
                    maximize(this);
                }
                return this;
            },
            restore:function(){
                if(this.isMaximized()){
                    restore(this);
                }
                return this;
            },
            pin:function(){
                if(!this.isPinned()){
                    pin(this);
                }
                return this;
            },
            unpin:function(){
                if(this.isPinned()){
                    unpin(this);
                }
                return this;
            },
            bringToFront:function(){
                bringToFront(null, this);
                return this;
            },
            /**
             * Move the dialog to a specific x/y coordinates
             *
             * @param {Number} x    The new dialog x coordinate in pixels.
             * @param {Number} y    The new dialog y coordinate in pixels.
             *
             * @return {Object} The dialog instance.
             */
            moveTo:function(x,y){
                if(!isNaN(x) && !isNaN(y)){
                    // allow custom `onmove` method
                    dispatchEvent('onmove', this);
                    
                    var element = this.elements.dialog,
                        current = element,
                        offsetLeft = 0,
                        offsetTop = 0;
                    
                    //subtract existing left,top
                    if (element.style.left) {
                        offsetLeft -= parseInt(element.style.left, 10);
                    }
                    if (element.style.top) {
                        offsetTop -= parseInt(element.style.top, 10);
                    }
                    //calc offset
                    do {
                        offsetLeft += current.offsetLeft;
                        offsetTop += current.offsetTop;
                    } while (current = current.offsetParent);

                    //calc left, top
                    var left = (x - offsetLeft);
                    var top  = (y - offsetTop);

                    //// rtl handling
                    if (isRightToLeft()) {
                        left *= -1;
                    }

                    element.style.left = left + 'px';
                    element.style.top = top + 'px';
                    
                    // allow custom `onmoved` method
                    dispatchEvent('onmoved', this);
                }
                return this;
            },
            /**
             * Resize the dialog to a specific width/height (the dialog must be 'resizable').
             * The dialog can be resized to:
             *  A minimum width equal to the initial display width
             *  A minimum height equal to the sum of header/footer heights.
             *
             *
             * @param {Number or String} width    The new dialog width in pixels or in percent.
             * @param {Number or String} height   The new dialog height in pixels or in percent.
             *
             * @return {Object} The dialog instance.
             */
            resizeTo:function(width,height){
                var w = parseFloat(width),
                    h = parseFloat(height),
                    regex = /(\d*\.\d+|\d+)%/
                ;

                if(!isNaN(w) && !isNaN(h) && this.get('resizable') === true){
                    
                    // allow custom `onresize` method
                    dispatchEvent('onresize', this);
                    
                    if(('' + width).match(regex)){
                        w = w / 100 * document.documentElement.clientWidth ;
                    }

                    if(('' + height).match(regex)){
                        h = h / 100 * document.documentElement.clientHeight;
                    }

                    var element = this.elements.dialog;
                    if (element.style.maxWidth !== 'none') {
                        element.style.minWidth = (minWidth = element.offsetWidth) + 'px';
                    }
                    element.style.maxWidth = 'none';
                    element.style.minHeight = this.elements.header.offsetHeight + this.elements.footer.offsetHeight + 'px';
                    element.style.width = w + 'px';
                    element.style.height = h + 'px';
                    
                    // allow custom `onresized` method
                    dispatchEvent('onresized', this);
                }
                return this;
            },
            /**
             * Gets or Sets dialog settings/options 
             *
             * @param {String|Object} key A string specifying a propery name or a collection of key/value pairs.
             * @param {Object} value Optional, the value associated with the key (in case it was a string).
             *
             * @return {undefined}
             */
            setting : function (key, value) {
                var self = this;
                var result = update(this, this.__internal.options, function(k,o,n){ optionUpdated(self,k,o,n); }, key, value);
                if(result.op === 'get'){
                    if(result.found){
                        return result.value;
                    }else if(typeof this.settings !== 'undefined'){
                        return update(this, this.settings, this.settingUpdated || function(){}, key, value).value;
                    }else{
                        return undefined;
                    }
                }else if(result.op === 'set'){
                    if(result.items.length > 0){
                        var callback = this.settingUpdated || function(){};
                        for(var x=0;x<result.items.length;x+=1){
                            var item = result.items[x];
                            if(!item.found && typeof this.settings !== 'undefined'){
                                update(this, this.settings, callback, item.key, item.value);
                            }
                        }
                    }
                    return this;
                }
            },
            /**
             * [Alias] Sets dialog settings/options 
             */
            set:function(key, value){
                this.setting(key,value);
                return this;
            },
            /**
             * [Alias] Gets dialog settings/options 
             */
            get:function(key){
                return this.setting(key);
            },
            /**
            * Sets dialog header
            * @content {string or element}
            *
            * @return {undefined}
            */
            setHeader:function(content){
                if(isString(content)){
                    clearContents(this.elements.header);
                    this.elements.header.innerHTML = content;
                }else if (content instanceof window.HTMLElement && this.elements.header.firstChild !== content){
                    clearContents(this.elements.header);
                    this.elements.header.appendChild(content);
                }
                return this;
            },
            /**
            * Sets dialog contents
            * @content {string or element}
            *
            * @return {undefined}
            */
            setContent:function(content){
                if(isString(content)){
                    clearContents(this.elements.content);
                    this.elements.content.innerHTML = content;
                }else if (content instanceof window.HTMLElement && this.elements.content.firstChild !== content){
                    clearContents(this.elements.content);
                    this.elements.content.appendChild(content);
                }
                return this;
            },
            /**
             * Show the dialog as modal
             *
             * @return {Object} the dialog instance.
             */
            showModal: function(className){
                return this.show(true, className);
            },
            /**
             * Show the dialog
             *
             * @return {Object} the dialog instance.
             */
            show: function (modal, className) {
                
                // ensure initialization
                initialize(this);

                if ( !this.__internal.isOpen ) {

                    // add to open dialogs
                    this.__internal.isOpen = true;
                    openDialogs.push(this);

                    // save last focused element
                    if(alertify.defaults.maintainFocus){
                        this.__internal.activeElement = document.activeElement;
                    }

                    // set tabindex attribute on body element this allows script to give it focusable
                    if(!document.body.hasAttribute('tabindex')) {
                        document.body.setAttribute( 'tabindex', tabindex = '0');
                    }

                    //allow custom dom manipulation updates before showing the dialog.
                    if(typeof this.prepare === 'function'){
                        this.prepare();
                    }

                    bindEvents(this);

                    if(modal !== undefined){
                        this.set('modal', modal);
                    }

                    //save scroll to prevent document jump
                    saveScrollPosition();

                    ensureNoOverflow();

                    // allow custom dialog class on show
                    if(typeof className === 'string' && className !== ''){
                        this.__internal.className = className;
                        addClass(this.elements.root, className);
                    }

                    // maximize if start maximized
                    if ( this.get('startMaximized')) {
                        this.maximize();
                    }else if(this.isMaximized()){
                        restore(this);
                    }

                    updateAbsPositionFix(this);
                    this.elements.root.removeAttribute('style');
                    removeClass(this.elements.root, classes.animationOut);
                    addClass(this.elements.root, classes.animationIn);

                    // set 1s fallback in case transition event doesn't fire
                    clearTimeout( this.__internal.timerIn);
                    this.__internal.timerIn = setTimeout( this.__internal.transitionInHandler, transition.supported ? 1000 : 100 );

                    if(isSafari){
                        // force desktop safari reflow
                        var root = this.elements.root;
                        root.style.display  = 'none';
                        setTimeout(function(){root.style.display  = 'block';}, 0);
                    }

                    //reflow
                    reflow = this.elements.root.offsetWidth;
                  
                    // show dialog
                    removeClass(this.elements.root, classes.hidden);

                    //restore scroll to prevent document jump
                    restoreScrollPosition();

                    // internal on show event
                    if(typeof this.hooks.onshow === 'function'){
                        this.hooks.onshow.call(this);
                    }

                    // allow custom `onshow` method
                    dispatchEvent('onshow', this);

                }else{
                    // reset move updates
                    resetMove(this);
                    // reset resize updates
                    resetResize(this);
                    // shake the dialog to indicate its already open
                    addClass(this.elements.dialog, classes.shake);
                    var self = this;
                    setTimeout(function(){
                        removeClass(self.elements.dialog, classes.shake);
                    },200);
                }
                return this;
            },
            /**
             * Close the dialog
             *
             * @return {Object} The dialog instance
             */
            close: function () {
                if (this.__internal.isOpen ) {
                    // custom `onclosing` event
                    if(dispatchEvent('onclosing', this) !== false){

                        unbindEvents(this);

                        removeClass(this.elements.root, classes.animationIn);
                        addClass(this.elements.root, classes.animationOut);

                        // set 1s fallback in case transition event doesn't fire
                        clearTimeout( this.__internal.timerOut );
                        this.__internal.timerOut = setTimeout( this.__internal.transitionOutHandler, transition.supported ? 1000 : 100 );
                        // hide dialog
                        addClass(this.elements.root, classes.hidden);
                        //reflow
                        reflow = this.elements.modal.offsetWidth;

                        // return focus to the last active element
                        if (alertify.defaults.maintainFocus && this.__internal.activeElement) {
                            this.__internal.activeElement.focus();
                            this.__internal.activeElement = null;
                        }

                        // remove custom dialog class on hide
                        if (typeof this.__internal.className !== 'undefined' && this.__internal.className !== '') {
                            removeClass(this.elements.root, this.__internal.className);
                        }

                        // internal on close event
                        if(typeof this.hooks.onclose === 'function'){
                            this.hooks.onclose.call(this);
                        }

                        // allow custom `onclose` method
                        dispatchEvent('onclose', this);

                        //remove from open dialogs
                        openDialogs.splice(openDialogs.indexOf(this),1);
                        this.__internal.isOpen = false;

                        ensureNoOverflow();
                    }

                }
                // last dialog and tab index was set by us, remove it.
                if(!openDialogs.length && tabindex === '0'){
                    document.body.removeAttribute('tabindex');
                }
                return this;
            },
            /**
             * Close all open dialogs except this.
             *
             * @return {undefined}
             */
            closeOthers:function(){
                alertify.closeAll(this);
                return this;
            },
            /**
             * Destroys this dialog instance
             *
             * @return {undefined}
             */
            destroy:function(){
                if(this.__internal) {
                    if (this.__internal.isOpen ) {
                        //mark dialog for destruction, this will be called on tranistionOut event.
                        this.__internal.destroy = function(){
                            destruct(this, initialize);
                        };
                        //close the dialog to unbind all events.
                        this.close();
                    }else if(!this.__internal.destroy){
                        destruct(this, initialize);
                    }
                }
                return this;
            },
        };

	} () );
    var notifier = (function () {
        var reflow,
            element,
            openInstances = [],
            classes = defaults.notifier.classes,
            baseClass = classes.base;
        /**
         * Helper: initializes the notifier instance
         *
         */
        function initialize(instance) {

            if (!instance.__internal) {
                instance.__internal = {
                    position: alertify.defaults.notifier.position,
                    delay: alertify.defaults.notifier.delay,
                };

                element = document.createElement('DIV');
                var transitionOff = 'transitionOff' in defaults.notifier ? defaults.notifier.transitionOff : defaults.transitionOff;
                if(transitionOff){
                    baseClass = classes.base + ' ajs-no-transition';
                }
                updatePosition(instance);
            }

            //add to DOM tree.
            if (element.parentNode !== document.body) {
                document.body.appendChild(element);
            }
        }

        function pushInstance(instance) {
            instance.__internal.pushed = true;
            openInstances.push(instance);
        }
        function popInstance(instance) {
            openInstances.splice(openInstances.indexOf(instance), 1);
            instance.__internal.pushed = false;
        }
        /**
         * Helper: update the notifier instance position
         *
         */
        function updatePosition(instance) {
            element.className = baseClass;
            switch (instance.__internal.position) {
            case 'top-right':
                addClass(element, classes.top + ' ' + classes.right);
                break;
            case 'top-left':
                addClass(element, classes.top + ' ' + classes.left);
                break;
            case 'top-center':
                addClass(element, classes.top + ' ' + classes.center);
                break;
            case 'bottom-left':
                addClass(element, classes.bottom + ' ' + classes.left);
                break;
            case 'bottom-center':
                addClass(element, classes.bottom + ' ' + classes.center);
                break;

            default:
            case 'bottom-right':
                addClass(element, classes.bottom + ' ' + classes.right);
                break;
            }
        }

        /**
        * creates a new notification message
        *
        * @param  {DOMElement} message	The notifier message element
        * @param  {Number} wait   Time (in ms) to wait before the message is dismissed, a value of 0 means keep open till clicked.
        * @param  {Function} callback A callback function to be invoked when the message is dismissed.
        *
        * @return {undefined}
        */
        function create(div, callback) {

            function clickDelegate(event, instance) {
                if(!instance.__internal.closeButton || event.target.getAttribute('data-close') === 'true'){
                    instance.dismiss(true);
                }
            }

            function transitionDone(event, instance) {
                // unbind event
                off(instance.element, transition.type, transitionDone);
                // remove the message
                element.removeChild(instance.element);
            }

            function initialize(instance) {
                if (!instance.__internal) {
                    instance.__internal = {
                        pushed: false,
                        delay : undefined,
                        timer: undefined,
                        clickHandler: undefined,
                        transitionEndHandler: undefined,
                        transitionTimeout: undefined
                    };
                    instance.__internal.clickHandler = delegate(instance, clickDelegate);
                    instance.__internal.transitionEndHandler = delegate(instance, transitionDone);
                }
                return instance;
            }
            function clearTimers(instance) {
                clearTimeout(instance.__internal.timer);
                clearTimeout(instance.__internal.transitionTimeout);
            }
            return initialize({
                /* notification DOM element*/
                element: div,
                /*
                 * Pushes a notification message
                 * @param {string or DOMElement} content The notification message content
                 * @param {Number} wait The time (in seconds) to wait before the message is dismissed, a value of 0 means keep open till clicked.
                 *
                 */
                push: function (_content, _wait) {
                    if (!this.__internal.pushed) {

                        pushInstance(this);
                        clearTimers(this);

                        var content, wait;
                        switch (arguments.length) {
                        case 0:
                            wait = this.__internal.delay;
                            break;
                        case 1:
                            if (typeof (_content) === 'number') {
                                wait = _content;
                            } else {
                                content = _content;
                                wait = this.__internal.delay;
                            }
                            break;
                        case 2:
                            content = _content;
                            wait = _wait;
                            break;
                        }
                        this.__internal.closeButton = alertify.defaults.notifier.closeButton;
                        // set contents
                        if (typeof content !== 'undefined') {
                            this.setContent(content);
                        }
                        // append or insert
                        if (notifier.__internal.position.indexOf('top') < 0) {
                            element.appendChild(this.element);
                        } else {
                            element.insertBefore(this.element, element.firstChild);
                        }
                        reflow = this.element.offsetWidth;
                        addClass(this.element, classes.visible);
                        // attach click event
                        on(this.element, 'click', this.__internal.clickHandler);
                        return this.delay(wait);
                    }
                    return this;
                },
                /*
                 * {Function} callback function to be invoked before dismissing the notification message.
                 * Remarks: A return value === 'false' will cancel the dismissal
                 *
                 */
                ondismiss: function () { },
                /*
                 * {Function} callback function to be invoked when the message is dismissed.
                 *
                 */
                callback: callback,
                /*
                 * Dismisses the notification message
                 * @param {Boolean} clicked A flag indicating if the dismissal was caused by a click.
                 *
                 */
                dismiss: function (clicked) {
                    if (this.__internal.pushed) {
                        clearTimers(this);
                        if (!(typeof this.ondismiss === 'function' && this.ondismiss.call(this) === false)) {
                            //detach click event
                            off(this.element, 'click', this.__internal.clickHandler);
                            // ensure element exists
                            if (typeof this.element !== 'undefined' && this.element.parentNode === element) {
                                //transition end or fallback
                                this.__internal.transitionTimeout = setTimeout(this.__internal.transitionEndHandler, transition.supported ? 1000 : 100);
                                removeClass(this.element, classes.visible);

                                // custom callback on dismiss
                                if (typeof this.callback === 'function') {
                                    this.callback.call(this, clicked);
                                }
                            }
                            popInstance(this);
                        }
                    }
                    return this;
                },
                /*
                 * Delays the notification message dismissal
                 * @param {Number} wait The time (in seconds) to wait before the message is dismissed, a value of 0 means keep open till clicked.
                 *
                 */
                delay: function (wait) {
                    clearTimers(this);
                    this.__internal.delay = typeof wait !== 'undefined' && !isNaN(+wait) ? +wait : notifier.__internal.delay;
                    if (this.__internal.delay > 0) {
                        var  self = this;
                        this.__internal.timer = setTimeout(function () { self.dismiss(); }, this.__internal.delay * 1000);
                    }
                    return this;
                },
                /*
                 * Sets the notification message contents
                 * @param {string or DOMElement} content The notification message content
                 *
                 */
                setContent: function (content) {
                    if (isString(content)) {
                        clearContents(this.element);
                        this.element.innerHTML = content;
                    } else if (content instanceof window.HTMLElement && this.element.firstChild !== content) {
                        clearContents(this.element);
                        this.element.appendChild(content);
                    }
                    if(this.__internal.closeButton){
                        var close = document.createElement('span');
                        addClass(close, classes.close);
                        close.setAttribute('data-close', true);
                        this.element.appendChild(close);
                    }
                    return this;
                },
                /*
                 * Dismisses all open notifications except this.
                 *
                 */
                dismissOthers: function () {
                    notifier.dismissAll(this);
                    return this;
                }
            });
        }

        //notifier api
        return {
            /**
             * Gets or Sets notifier settings.
             *
             * @param {string} key The setting name
             * @param {Variant} value The setting value.
             *
             * @return {Object}	if the called as a setter, return the notifier instance.
             */
            setting: function (key, value) {
                //ensure init
                initialize(this);

                if (typeof value === 'undefined') {
                    //get
                    return this.__internal[key];
                } else {
                    //set
                    switch (key) {
                    case 'position':
                        this.__internal.position = value;
                        updatePosition(this);
                        break;
                    case 'delay':
                        this.__internal.delay = value;
                        break;
                    }
                }
                return this;
            },
            /**
             * [Alias] Sets dialog settings/options
             */
            set:function(key,value){
                this.setting(key,value);
                return this;
            },
            /**
             * [Alias] Gets dialog settings/options
             */
            get:function(key){
                return this.setting(key);
            },
            /**
             * Creates a new notification message
             *
             * @param {string} type The type of notification message (simply a CSS class name 'ajs-{type}' to be added).
             * @param {Function} callback  A callback function to be invoked when the message is dismissed.
             *
             * @return {undefined}
             */
            create: function (type, callback) {
                //ensure notifier init
                initialize(this);
                //create new notification message
                var div = document.createElement('div');
                div.className = classes.message + ((typeof type === 'string' && type !== '') ? ' ' + classes.prefix + type : '');
                return create(div, callback);
            },
            /**
             * Dismisses all open notifications.
             *
             * @param {Object} excpet [optional] The notification object to exclude from dismissal.
             *
             */
            dismissAll: function (except) {
                var clone = openInstances.slice(0);
                for (var x = 0; x < clone.length; x += 1) {
                    var  instance = clone[x];
                    if (except === undefined || except !== instance) {
                        instance.dismiss();
                    }
                }
            }
        };
    })();

    /**
     * Alertify public API
     * This contains everything that is exposed through the alertify object.
     *
     * @return {Object}
     */
    function Alertify() {

        // holds a references of created dialogs
        var dialogs = {};

        /**
         * Extends a given prototype by merging properties from base into sub.
         *
         * @sub {Object} sub The prototype being overwritten.
         * @base {Object} base The prototype being written.
         *
         * @return {Object} The extended prototype.
         */
        function extend(sub, base) {
            // copy dialog pototype over definition.
            for (var prop in base) {
                if (base.hasOwnProperty(prop)) {
                    sub[prop] = base[prop];
                }
            }
            return sub;
        }


        /**
        * Helper: returns a dialog instance from saved dialogs.
        * and initializes the dialog if its not already initialized.
        *
        * @name {String} name The dialog name.
        *
        * @return {Object} The dialog instance.
        */
        function get_dialog(name) {
            var dialog = dialogs[name].dialog;
            //initialize the dialog if its not already initialized.
            if (dialog && typeof dialog.__init === 'function') {
                dialog.__init(dialog);
            }
            return dialog;
        }

        /**
         * Helper:  registers a new dialog definition.
         *
         * @name {String} name The dialog name.
         * @Factory {Function} Factory a function resposible for creating dialog prototype.
         * @transient {Boolean} transient True to create a new dialog instance each time the dialog is invoked, false otherwise.
         * @base {String} base the name of another dialog to inherit from.
         *
         * @return {Object} The dialog definition.
         */
        function register(name, Factory, transient, base) {
            var definition = {
                dialog: null,
                factory: Factory
            };

            //if this is based on an existing dialog, create a new definition
            //by applying the new protoype over the existing one.
            if (base !== undefined) {
                definition.factory = function () {
                    return extend(new dialogs[base].factory(), new Factory());
                };
            }

            if (!transient) {
                //create a new definition based on dialog
                definition.dialog = extend(new definition.factory(), dialog);
            }
            return dialogs[name] = definition;
        }

        return {
            /**
             * Alertify defaults
             * 
             * @type {Object}
             */
            defaults: defaults,
            /**
             * Dialogs factory 
             *
             * @param {string}      Dialog name.
             * @param {Function}    A Dialog factory function.
             * @param {Boolean}     Indicates whether to create a singleton or transient dialog.
             * @param {String}      The name of the base type to inherit from.
             */
            dialog: function (name, Factory, transient, base) {

                // get request, create a new instance and return it.
                if (typeof Factory !== 'function') {
                    return get_dialog(name);
                }

                if (this.hasOwnProperty(name)) {
                    throw new Error('alertify.dialog: name already exists');
                }

                // register the dialog
                var definition = register(name, Factory, transient, base);

                if (transient) {

                    // make it public
                    this[name] = function () {
                        //if passed with no params, consider it a get request
                        if (arguments.length === 0) {
                            return definition.dialog;
                        } else {
                            var instance = extend(new definition.factory(), dialog);
                            //ensure init
                            if (instance && typeof instance.__init === 'function') {
                                instance.__init(instance);
                            }
                            instance['main'].apply(instance, arguments);
                            return instance['show'].apply(instance);
                        }
                    };
                } else {
                    // make it public
                    this[name] = function () {
                        //ensure init
                        if (definition.dialog && typeof definition.dialog.__init === 'function') {
                            definition.dialog.__init(definition.dialog);
                        }
                        //if passed with no params, consider it a get request
                        if (arguments.length === 0) {
                            return definition.dialog;
                        } else {
                            var dialog = definition.dialog;
                            dialog['main'].apply(definition.dialog, arguments);
                            return dialog['show'].apply(definition.dialog);
                        }
                    };
                }
            },
            /**
             * Close all open dialogs.
             *
             * @param {Object} excpet [optional] The dialog object to exclude from closing.
             *
             * @return {undefined}
             */
            closeAll: function (except) {
                var clone = openDialogs.slice(0);
                for (var x = 0; x < clone.length; x += 1) {
                    var instance = clone[x];
                    if (except === undefined || except !== instance) {
                        instance.close();
                    }
                }
            },
            /**
             * Gets or Sets dialog settings/options. if the dialog is transient, this call does nothing.
             *
             * @param {string} name The dialog name.
             * @param {String|Object} key A string specifying a propery name or a collection of key/value pairs.
             * @param {Variant} value Optional, the value associated with the key (in case it was a string).
             *
             * @return {undefined}
             */
            setting: function (name, key, value) {

                if (name === 'notifier') {
                    return notifier.setting(key, value);
                }

                var dialog = get_dialog(name);
                if (dialog) {
                    return dialog.setting(key, value);
                }
            },
            /**
             * [Alias] Sets dialog settings/options 
             */
            set: function(name,key,value){
                return this.setting(name, key,value);
            },
            /**
             * [Alias] Gets dialog settings/options 
             */
            get: function(name, key){
                return this.setting(name, key);
            },
            /**
             * Creates a new notification message.
             * If a type is passed, a class name "ajs-{type}" will be added.
             * This allows for custom look and feel for various types of notifications.
             *
             * @param  {String | DOMElement}    [message=undefined]		Message text
             * @param  {String}                 [type='']				Type of log message
             * @param  {String}                 [wait='']				Time (in seconds) to wait before auto-close
             * @param  {Function}               [callback=undefined]	A callback function to be invoked when the log is closed.
             *
             * @return {Object} Notification object.
             */
            notify: function (message, type, wait, callback) {
                return notifier.create(type, callback).push(message, wait);
            },
            /**
             * Creates a new notification message.
             *
             * @param  {String}		[message=undefined]		Message text
             * @param  {String}     [wait='']				Time (in seconds) to wait before auto-close
             * @param  {Function}	[callback=undefined]	A callback function to be invoked when the log is closed.
             *
             * @return {Object} Notification object.
             */
            message: function (message, wait, callback) {
                return notifier.create(null, callback).push(message, wait);
            },
            /**
             * Creates a new notification message of type 'success'.
             *
             * @param  {String}		[message=undefined]		Message text
             * @param  {String}     [wait='']				Time (in seconds) to wait before auto-close
             * @param  {Function}	[callback=undefined]	A callback function to be invoked when the log is closed.
             *
             * @return {Object} Notification object.
             */
            success: function (message, wait, callback) {
                return notifier.create('success', callback).push(message, wait);
            },
            /**
             * Creates a new notification message of type 'error'.
             *
             * @param  {String}		[message=undefined]		Message text
             * @param  {String}     [wait='']				Time (in seconds) to wait before auto-close
             * @param  {Function}	[callback=undefined]	A callback function to be invoked when the log is closed.
             *
             * @return {Object} Notification object.
             */
            error: function (message, wait, callback) {
                return notifier.create('error', callback).push(message, wait);
            },
            /**
             * Creates a new notification message of type 'warning'.
             *
             * @param  {String}		[message=undefined]		Message text
             * @param  {String}     [wait='']				Time (in seconds) to wait before auto-close
             * @param  {Function}	[callback=undefined]	A callback function to be invoked when the log is closed.
             *
             * @return {Object} Notification object.
             */
            warning: function (message, wait, callback) {
                return notifier.create('warning', callback).push(message, wait);
            },
            /**
             * Dismisses all open notifications
             *
             * @return {undefined}
             */
            dismissAll: function () {
                notifier.dismissAll();
            }
        };
    }
    var alertify = new Alertify();

    /**
    * Alert dialog definition
    *
    * invoked by:
    *	alertify.alert(message);
    *	alertify.alert(title, message);
    *	alertify.alert(message, onok);
    *	alertify.alert(title, message, onok);
     */
    alertify.dialog('alert', function () {
        return {
            main: function (_title, _message, _onok) {
                var title, message, onok;
                switch (arguments.length) {
                case 1:
                    message = _title;
                    break;
                case 2:
                    if (typeof _message === 'function') {
                        message = _title;
                        onok = _message;
                    } else {
                        title = _title;
                        message = _message;
                    }
                    break;
                case 3:
                    title = _title;
                    message = _message;
                    onok = _onok;
                    break;
                }
                this.set('title', title);
                this.set('message', message);
                this.set('onok', onok);
                return this;
            },
            setup: function () {
                return {
                    buttons: [
                        {
                            text: alertify.defaults.glossary.ok,
                            key: keys.ESC,
                            invokeOnClose: true,
                            className: alertify.defaults.theme.ok,
                        }
                    ],
                    focus: {
                        element: 0,
                        select: false
                    },
                    options: {
                        maximizable: false,
                        resizable: false
                    }
                };
            },
            build: function () {
                // nothing
            },
            prepare: function () {
                //nothing
            },
            setMessage: function (message) {
                this.setContent(message);
            },
            settings: {
                message: undefined,
                onok: undefined,
                label: undefined,
            },
            settingUpdated: function (key, oldValue, newValue) {
                switch (key) {
                case 'message':
                    this.setMessage(newValue);
                    break;
                case 'label':
                    if (this.__internal.buttons[0].element) {
                        this.__internal.buttons[0].element.innerHTML = newValue;
                    }
                    break;
                }
            },
            callback: function (closeEvent) {
                if (typeof this.get('onok') === 'function') {
                    var returnValue = this.get('onok').call(this, closeEvent);
                    if (typeof returnValue !== 'undefined') {
                        closeEvent.cancel = !returnValue;
                    }
                }
            }
        };
    });
    /**
     * Confirm dialog object
     *
     *	alertify.confirm(message);
     *	alertify.confirm(message, onok);
     *	alertify.confirm(message, onok, oncancel);
     *	alertify.confirm(title, message, onok, oncancel);
     */
    alertify.dialog('confirm', function () {

        var autoConfirm = {
            timer: null,
            index: null,
            text: null,
            duration: null,
            task: function (event, self) {
                if (self.isOpen()) {
                    self.__internal.buttons[autoConfirm.index].element.innerHTML = autoConfirm.text + ' (&#8207;' + autoConfirm.duration + '&#8207;) ';
                    autoConfirm.duration -= 1;
                    if (autoConfirm.duration === -1) {
                        clearAutoConfirm(self);
                        var button = self.__internal.buttons[autoConfirm.index];
                        var closeEvent = createCloseEvent(autoConfirm.index, button);

                        if (typeof self.callback === 'function') {
                            self.callback.apply(self, [closeEvent]);
                        }
                        //close the dialog.
                        if (closeEvent.close !== false) {
                            self.close();
                        }
                    }
                } else {
                    clearAutoConfirm(self);
                }
            }
        };

        function clearAutoConfirm(self) {
            if (autoConfirm.timer !== null) {
                clearInterval(autoConfirm.timer);
                autoConfirm.timer = null;
                self.__internal.buttons[autoConfirm.index].element.innerHTML = autoConfirm.text;
            }
        }

        function startAutoConfirm(self, index, duration) {
            clearAutoConfirm(self);
            autoConfirm.duration = duration;
            autoConfirm.index = index;
            autoConfirm.text = self.__internal.buttons[index].element.innerHTML;
            autoConfirm.timer = setInterval(delegate(self, autoConfirm.task), 1000);
            autoConfirm.task(null, self);
        }


        return {
            main: function (_title, _message, _onok, _oncancel) {
                var title, message, onok, oncancel;
                switch (arguments.length) {
                case 1:
                    message = _title;
                    break;
                case 2:
                    message = _title;
                    onok = _message;
                    break;
                case 3:
                    message = _title;
                    onok = _message;
                    oncancel = _onok;
                    break;
                case 4:
                    title = _title;
                    message = _message;
                    onok = _onok;
                    oncancel = _oncancel;
                    break;
                }
                this.set('title', title);
                this.set('message', message);
                this.set('onok', onok);
                this.set('oncancel', oncancel);
                return this;
            },
            setup: function () {
                return {
                    buttons: [
                        {
                            text: alertify.defaults.glossary.ok,
                            key: keys.ENTER,
                            className: alertify.defaults.theme.ok,
                        },
                        {
                            text: alertify.defaults.glossary.cancel,
                            key: keys.ESC,
                            invokeOnClose: true,
                            className: alertify.defaults.theme.cancel,
                        }
                    ],
                    focus: {
                        element: 0,
                        select: false
                    },
                    options: {
                        maximizable: false,
                        resizable: false
                    }
                };
            },
            build: function () {
                //nothing
            },
            prepare: function () {
                //nothing
            },
            setMessage: function (message) {
                this.setContent(message);
            },
            settings: {
                message: null,
                labels: null,
                onok: null,
                oncancel: null,
                defaultFocus: null,
                reverseButtons: null,
            },
            settingUpdated: function (key, oldValue, newValue) {
                switch (key) {
                case 'message':
                    this.setMessage(newValue);
                    break;
                case 'labels':
                    if ('ok' in newValue && this.__internal.buttons[0].element) {
                        this.__internal.buttons[0].text = newValue.ok;
                        this.__internal.buttons[0].element.innerHTML = newValue.ok;
                    }
                    if ('cancel' in newValue && this.__internal.buttons[1].element) {
                        this.__internal.buttons[1].text = newValue.cancel;
                        this.__internal.buttons[1].element.innerHTML = newValue.cancel;
                    }
                    break;
                case 'reverseButtons':
                    if (newValue === true) {
                        this.elements.buttons.primary.appendChild(this.__internal.buttons[0].element);
                    } else {
                        this.elements.buttons.primary.appendChild(this.__internal.buttons[1].element);
                    }
                    break;
                case 'defaultFocus':
                    this.__internal.focus.element = newValue === 'ok' ? 0 : 1;
                    break;
                }
            },
            callback: function (closeEvent) {
                clearAutoConfirm(this);
                var returnValue;
                switch (closeEvent.index) {
                case 0:
                    if (typeof this.get('onok') === 'function') {
                        returnValue = this.get('onok').call(this, closeEvent);
                        if (typeof returnValue !== 'undefined') {
                            closeEvent.cancel = !returnValue;
                        }
                    }
                    break;
                case 1:
                    if (typeof this.get('oncancel') === 'function') {
                        returnValue = this.get('oncancel').call(this, closeEvent);
                        if (typeof returnValue !== 'undefined') {
                            closeEvent.cancel = !returnValue;
                        }
                    }
                    break;
                }
            },
            autoOk: function (duration) {
                startAutoConfirm(this, 0, duration);
                return this;
            },
            autoCancel: function (duration) {
                startAutoConfirm(this, 1, duration);
                return this;
            }
        };
    });
    /**
     * Prompt dialog object
     *
     * invoked by:
     *	alertify.prompt(message);
     *	alertify.prompt(message, value);
     *	alertify.prompt(message, value, onok);
     *	alertify.prompt(message, value, onok, oncancel);
     *	alertify.prompt(title, message, value, onok, oncancel);
     */
    alertify.dialog('prompt', function () {
        var input = document.createElement('INPUT');
        var p = document.createElement('P');
        return {
            main: function (_title, _message, _value, _onok, _oncancel) {
                var title, message, value, onok, oncancel;
                switch (arguments.length) {
                case 1:
                    message = _title;
                    break;
                case 2:
                    message = _title;
                    value = _message;
                    break;
                case 3:
                    message = _title;
                    value = _message;
                    onok = _value;
                    break;
                case 4:
                    message = _title;
                    value = _message;
                    onok = _value;
                    oncancel = _onok;
                    break;
                case 5:
                    title = _title;
                    message = _message;
                    value = _value;
                    onok = _onok;
                    oncancel = _oncancel;
                    break;
                }
                this.set('title', title);
                this.set('message', message);
                this.set('value', value);
                this.set('onok', onok);
                this.set('oncancel', oncancel);
                return this;
            },
            setup: function () {
                return {
                    buttons: [
                        {
                            text: alertify.defaults.glossary.ok,
                            key: keys.ENTER,
                            className: alertify.defaults.theme.ok,
                        },
                        {
                            text: alertify.defaults.glossary.cancel,
                            key: keys.ESC,
                            invokeOnClose: true,
                            className: alertify.defaults.theme.cancel,
                        }
                    ],
                    focus: {
                        element: input,
                        select: true
                    },
                    options: {
                        maximizable: false,
                        resizable: false
                    }
                };
            },
            build: function () {
                input.className = alertify.defaults.theme.input;
                input.setAttribute('type', 'text');
                input.value = this.get('value');
                this.elements.content.appendChild(p);
                this.elements.content.appendChild(input);
            },
            prepare: function () {
                //nothing
            },
            setMessage: function (message) {
                if (isString(message)) {
                    clearContents(p);
                    p.innerHTML = message;
                } else if (message instanceof window.HTMLElement && p.firstChild !== message) {
                    clearContents(p);
                    p.appendChild(message);
                }
            },
            settings: {
                message: undefined,
                labels: undefined,
                onok: undefined,
                oncancel: undefined,
                value: '',
                type:'text',
                reverseButtons: undefined,
            },
            settingUpdated: function (key, oldValue, newValue) {
                switch (key) {
                case 'message':
                    this.setMessage(newValue);
                    break;
                case 'value':
                    input.value = newValue;
                    break;
                case 'type':
                    switch (newValue) {
                    case 'text':
                    case 'color':
                    case 'date':
                    case 'datetime-local':
                    case 'email':
                    case 'month':
                    case 'number':
                    case 'password':
                    case 'search':
                    case 'tel':
                    case 'time':
                    case 'week':
                        input.type = newValue;
                        break;
                    default:
                        input.type = 'text';
                        break;
                    }
                    break;
                case 'labels':
                    if (newValue.ok && this.__internal.buttons[0].element) {
                        this.__internal.buttons[0].element.innerHTML = newValue.ok;
                    }
                    if (newValue.cancel && this.__internal.buttons[1].element) {
                        this.__internal.buttons[1].element.innerHTML = newValue.cancel;
                    }
                    break;
                case 'reverseButtons':
                    if (newValue === true) {
                        this.elements.buttons.primary.appendChild(this.__internal.buttons[0].element);
                    } else {
                        this.elements.buttons.primary.appendChild(this.__internal.buttons[1].element);
                    }
                    break;
                }
            },
            callback: function (closeEvent) {
                var returnValue;
                switch (closeEvent.index) {
                case 0:
                    this.settings.value = input.value;
                    if (typeof this.get('onok') === 'function') {
                        returnValue = this.get('onok').call(this, closeEvent, this.settings.value);
                        if (typeof returnValue !== 'undefined') {
                            closeEvent.cancel = !returnValue;
                        }
                    }
                    break;
                case 1:
                    if (typeof this.get('oncancel') === 'function') {
                        returnValue = this.get('oncancel').call(this, closeEvent);
                        if (typeof returnValue !== 'undefined') {
                            closeEvent.cancel = !returnValue;
                        }
                    }
                    if(!closeEvent.cancel){
                        input.value = this.settings.value;
                    }
                    break;
                }
            }
        };
    });

    // CommonJS
    if (  true && typeof module.exports === 'object' ) {
        module.exports = alertify;
    // AMD
    } else if ( true) {
        !(__WEBPACK_AMD_DEFINE_ARRAY__ = [], __WEBPACK_AMD_DEFINE_RESULT__ = (function () {
            return alertify;
        }).apply(exports, __WEBPACK_AMD_DEFINE_ARRAY__),
		__WEBPACK_AMD_DEFINE_RESULT__ !== undefined && (module.exports = __WEBPACK_AMD_DEFINE_RESULT__));
    // window
    } else {}

} ( typeof window !== 'undefined' ? window : this ) );


/***/ }),
/* 2 */
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(3);
/* harmony import */ var _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_styleDomAPI_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(4);
/* harmony import */ var _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_styleDomAPI_js__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_styleDomAPI_js__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_insertBySelector_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(5);
/* harmony import */ var _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_insertBySelector_js__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_insertBySelector_js__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_setAttributesWithoutAttributes_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(6);
/* harmony import */ var _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_setAttributesWithoutAttributes_js__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_setAttributesWithoutAttributes_js__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_insertStyleElement_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(7);
/* harmony import */ var _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_insertStyleElement_js__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_insertStyleElement_js__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_styleTagTransform_js__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(8);
/* harmony import */ var _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_styleTagTransform_js__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(_Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_styleTagTransform_js__WEBPACK_IMPORTED_MODULE_5__);
/* harmony import */ var _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_css_loader_dist_cjs_js_alertify_css__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(9);

      
      
      
      
      
      
      
      
      

var options = {};

options.styleTagTransform = (_Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_styleTagTransform_js__WEBPACK_IMPORTED_MODULE_5___default());
options.setAttributes = (_Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_setAttributesWithoutAttributes_js__WEBPACK_IMPORTED_MODULE_3___default());

      options.insert = _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_insertBySelector_js__WEBPACK_IMPORTED_MODULE_2___default().bind(null, "head");
    
options.domAPI = (_Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_styleDomAPI_js__WEBPACK_IMPORTED_MODULE_1___default());
options.insertStyleElement = (_Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_insertStyleElement_js__WEBPACK_IMPORTED_MODULE_4___default());

var update = _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default()(_Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_css_loader_dist_cjs_js_alertify_css__WEBPACK_IMPORTED_MODULE_6__["default"], options);




       /* harmony default export */ __webpack_exports__["default"] = (_Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_css_loader_dist_cjs_js_alertify_css__WEBPACK_IMPORTED_MODULE_6__["default"] && _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_css_loader_dist_cjs_js_alertify_css__WEBPACK_IMPORTED_MODULE_6__["default"].locals ? _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_css_loader_dist_cjs_js_alertify_css__WEBPACK_IMPORTED_MODULE_6__["default"].locals : undefined);


/***/ }),
/* 3 */
/***/ (function(module) {

"use strict";


var stylesInDOM = [];
function getIndexByIdentifier(identifier) {
  var result = -1;
  for (var i = 0; i < stylesInDOM.length; i++) {
    if (stylesInDOM[i].identifier === identifier) {
      result = i;
      break;
    }
  }
  return result;
}
function modulesToDom(list, options) {
  var idCountMap = {};
  var identifiers = [];
  for (var i = 0; i < list.length; i++) {
    var item = list[i];
    var id = options.base ? item[0] + options.base : item[0];
    var count = idCountMap[id] || 0;
    var identifier = "".concat(id, " ").concat(count);
    idCountMap[id] = count + 1;
    var indexByIdentifier = getIndexByIdentifier(identifier);
    var obj = {
      css: item[1],
      media: item[2],
      sourceMap: item[3],
      supports: item[4],
      layer: item[5]
    };
    if (indexByIdentifier !== -1) {
      stylesInDOM[indexByIdentifier].references++;
      stylesInDOM[indexByIdentifier].updater(obj);
    } else {
      var updater = addElementStyle(obj, options);
      options.byIndex = i;
      stylesInDOM.splice(i, 0, {
        identifier: identifier,
        updater: updater,
        references: 1
      });
    }
    identifiers.push(identifier);
  }
  return identifiers;
}
function addElementStyle(obj, options) {
  var api = options.domAPI(options);
  api.update(obj);
  var updater = function updater(newObj) {
    if (newObj) {
      if (newObj.css === obj.css && newObj.media === obj.media && newObj.sourceMap === obj.sourceMap && newObj.supports === obj.supports && newObj.layer === obj.layer) {
        return;
      }
      api.update(obj = newObj);
    } else {
      api.remove();
    }
  };
  return updater;
}
module.exports = function (list, options) {
  options = options || {};
  list = list || [];
  var lastIdentifiers = modulesToDom(list, options);
  return function update(newList) {
    newList = newList || [];
    for (var i = 0; i < lastIdentifiers.length; i++) {
      var identifier = lastIdentifiers[i];
      var index = getIndexByIdentifier(identifier);
      stylesInDOM[index].references--;
    }
    var newLastIdentifiers = modulesToDom(newList, options);
    for (var _i = 0; _i < lastIdentifiers.length; _i++) {
      var _identifier = lastIdentifiers[_i];
      var _index = getIndexByIdentifier(_identifier);
      if (stylesInDOM[_index].references === 0) {
        stylesInDOM[_index].updater();
        stylesInDOM.splice(_index, 1);
      }
    }
    lastIdentifiers = newLastIdentifiers;
  };
};

/***/ }),
/* 4 */
/***/ (function(module) {

"use strict";


/* istanbul ignore next  */
function apply(styleElement, options, obj) {
  var css = "";
  if (obj.supports) {
    css += "@supports (".concat(obj.supports, ") {");
  }
  if (obj.media) {
    css += "@media ".concat(obj.media, " {");
  }
  var needLayer = typeof obj.layer !== "undefined";
  if (needLayer) {
    css += "@layer".concat(obj.layer.length > 0 ? " ".concat(obj.layer) : "", " {");
  }
  css += obj.css;
  if (needLayer) {
    css += "}";
  }
  if (obj.media) {
    css += "}";
  }
  if (obj.supports) {
    css += "}";
  }
  var sourceMap = obj.sourceMap;
  if (sourceMap && typeof btoa !== "undefined") {
    css += "\n/*# sourceMappingURL=data:application/json;base64,".concat(btoa(unescape(encodeURIComponent(JSON.stringify(sourceMap)))), " */");
  }

  // For old IE
  /* istanbul ignore if  */
  options.styleTagTransform(css, styleElement, options.options);
}
function removeStyleElement(styleElement) {
  // istanbul ignore if
  if (styleElement.parentNode === null) {
    return false;
  }
  styleElement.parentNode.removeChild(styleElement);
}

/* istanbul ignore next  */
function domAPI(options) {
  if (typeof document === "undefined") {
    return {
      update: function update() {},
      remove: function remove() {}
    };
  }
  var styleElement = options.insertStyleElement(options);
  return {
    update: function update(obj) {
      apply(styleElement, options, obj);
    },
    remove: function remove() {
      removeStyleElement(styleElement);
    }
  };
}
module.exports = domAPI;

/***/ }),
/* 5 */
/***/ (function(module) {

"use strict";


var memo = {};

/* istanbul ignore next  */
function getTarget(target) {
  if (typeof memo[target] === "undefined") {
    var styleTarget = document.querySelector(target);

    // Special case to return head of iframe instead of iframe itself
    if (window.HTMLIFrameElement && styleTarget instanceof window.HTMLIFrameElement) {
      try {
        // This will throw an exception if access to iframe is blocked
        // due to cross-origin restrictions
        styleTarget = styleTarget.contentDocument.head;
      } catch (e) {
        // istanbul ignore next
        styleTarget = null;
      }
    }
    memo[target] = styleTarget;
  }
  return memo[target];
}

/* istanbul ignore next  */
function insertBySelector(insert, style) {
  var target = getTarget(insert);
  if (!target) {
    throw new Error("Couldn't find a style target. This probably means that the value for the 'insert' parameter is invalid.");
  }
  target.appendChild(style);
}
module.exports = insertBySelector;

/***/ }),
/* 6 */
/***/ (function(module, __unused_webpack_exports, __webpack_require__) {

"use strict";


/* istanbul ignore next  */
function setAttributesWithoutAttributes(styleElement) {
  var nonce =  true ? __webpack_require__.nc : 0;
  if (nonce) {
    styleElement.setAttribute("nonce", nonce);
  }
}
module.exports = setAttributesWithoutAttributes;

/***/ }),
/* 7 */
/***/ (function(module) {

"use strict";


/* istanbul ignore next  */
function insertStyleElement(options) {
  var element = document.createElement("style");
  options.setAttributes(element, options.attributes);
  options.insert(element, options.options);
  return element;
}
module.exports = insertStyleElement;

/***/ }),
/* 8 */
/***/ (function(module) {

"use strict";


/* istanbul ignore next  */
function styleTagTransform(css, styleElement) {
  if (styleElement.styleSheet) {
    styleElement.styleSheet.cssText = css;
  } else {
    while (styleElement.firstChild) {
      styleElement.removeChild(styleElement.firstChild);
    }
    styleElement.appendChild(document.createTextNode(css));
  }
}
module.exports = styleTagTransform;

/***/ }),
/* 9 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_css_loader_dist_runtime_noSourceMaps_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(10);
/* harmony import */ var _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_css_loader_dist_runtime_noSourceMaps_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_css_loader_dist_runtime_noSourceMaps_js__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(11);
/* harmony import */ var _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_css_loader_dist_runtime_getUrl_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(12);
/* harmony import */ var _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_css_loader_dist_runtime_getUrl_js__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_css_loader_dist_runtime_getUrl_js__WEBPACK_IMPORTED_MODULE_2__);
// Imports



var ___CSS_LOADER_URL_IMPORT_0___ = new URL(/* asset import */ __webpack_require__(13), __webpack_require__.b);
var ___CSS_LOADER_URL_IMPORT_1___ = new URL(/* asset import */ __webpack_require__(14), __webpack_require__.b);
var ___CSS_LOADER_URL_IMPORT_2___ = new URL(/* asset import */ __webpack_require__(15), __webpack_require__.b);
var ___CSS_LOADER_URL_IMPORT_3___ = new URL(/* asset import */ __webpack_require__(16), __webpack_require__.b);
var ___CSS_LOADER_URL_IMPORT_4___ = new URL(/* asset import */ __webpack_require__(17), __webpack_require__.b);
var ___CSS_LOADER_URL_IMPORT_5___ = new URL(/* asset import */ __webpack_require__(18), __webpack_require__.b);
var ___CSS_LOADER_URL_IMPORT_6___ = new URL(/* asset import */ __webpack_require__(19), __webpack_require__.b);
var ___CSS_LOADER_EXPORT___ = _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_1___default()((_Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_css_loader_dist_runtime_noSourceMaps_js__WEBPACK_IMPORTED_MODULE_0___default()));
var ___CSS_LOADER_URL_REPLACEMENT_0___ = _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_css_loader_dist_runtime_getUrl_js__WEBPACK_IMPORTED_MODULE_2___default()(___CSS_LOADER_URL_IMPORT_0___);
var ___CSS_LOADER_URL_REPLACEMENT_1___ = _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_css_loader_dist_runtime_getUrl_js__WEBPACK_IMPORTED_MODULE_2___default()(___CSS_LOADER_URL_IMPORT_1___);
var ___CSS_LOADER_URL_REPLACEMENT_2___ = _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_css_loader_dist_runtime_getUrl_js__WEBPACK_IMPORTED_MODULE_2___default()(___CSS_LOADER_URL_IMPORT_2___);
var ___CSS_LOADER_URL_REPLACEMENT_3___ = _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_css_loader_dist_runtime_getUrl_js__WEBPACK_IMPORTED_MODULE_2___default()(___CSS_LOADER_URL_IMPORT_3___);
var ___CSS_LOADER_URL_REPLACEMENT_4___ = _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_css_loader_dist_runtime_getUrl_js__WEBPACK_IMPORTED_MODULE_2___default()(___CSS_LOADER_URL_IMPORT_4___);
var ___CSS_LOADER_URL_REPLACEMENT_5___ = _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_css_loader_dist_runtime_getUrl_js__WEBPACK_IMPORTED_MODULE_2___default()(___CSS_LOADER_URL_IMPORT_5___);
var ___CSS_LOADER_URL_REPLACEMENT_6___ = _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_css_loader_dist_runtime_getUrl_js__WEBPACK_IMPORTED_MODULE_2___default()(___CSS_LOADER_URL_IMPORT_6___);
// Module
___CSS_LOADER_EXPORT___.push([module.id, "/**\r\n * alertifyjs 1.14.0 http://alertifyjs.com\r\n * AlertifyJS is a javascript framework for developing pretty browser dialogs and notifications.\r\n * Copyright 2024 Mohammad Younes <Mohammad@alertifyjs.com> (http://alertifyjs.com) \r\n * Licensed under GPL 3 <https://opensource.org/licenses/gpl-3.0>*/\r\n.alertify .ajs-dimmer {\n  position: fixed;\n  z-index: 1981;\n  top: 0;\n  right: 0;\n  bottom: 0;\n  left: 0;\n  padding: 0;\n  margin: 0;\n  background-color: #252525;\n  opacity: 0.5;\n}\n.alertify .ajs-modal {\n  position: fixed;\n  top: 0;\n  right: 0;\n  left: 0;\n  bottom: 0;\n  padding: 0;\n  overflow-y: auto;\n  z-index: 1981;\n}\n.alertify .ajs-dialog {\n  position: relative;\n  margin: 5% auto;\n  min-height: 110px;\n  max-width: 500px;\n  padding: 24px 24px 0 24px;\n  outline: 0;\n  background-color: #fff;\n}\n.alertify .ajs-dialog.ajs-capture:before {\n  content: '';\n  position: absolute;\n  top: 0;\n  right: 0;\n  bottom: 0;\n  left: 0;\n  display: block;\n  z-index: 1;\n}\n.alertify .ajs-reset {\n  position: absolute !important;\n  display: inline !important;\n  width: 0 !important;\n  height: 0 !important;\n  opacity: 0 !important;\n}\n.alertify .ajs-commands {\n  position: absolute;\n  right: 4px;\n  margin: -14px 24px 0 0;\n  z-index: 2;\n}\n.alertify .ajs-commands button {\n  display: none;\n  width: 10px;\n  height: 10px;\n  margin-left: 10px;\n  padding: 10px;\n  border: 0;\n  background-color: transparent;\n  background-repeat: no-repeat;\n  background-position: center;\n  cursor: pointer;\n}\n.alertify .ajs-commands button.ajs-close {\n  background-image: url(" + ___CSS_LOADER_URL_REPLACEMENT_0___ + ");\n}\n.alertify .ajs-commands button.ajs-maximize {\n  background-image: url(" + ___CSS_LOADER_URL_REPLACEMENT_1___ + ");\n}\n.alertify .ajs-header {\n  margin: -24px;\n  margin-bottom: 0;\n  padding: 16px 24px;\n  background-color: #fff;\n}\n.alertify .ajs-body {\n  min-height: 56px;\n}\n.alertify .ajs-body .ajs-content {\n  padding: 16px 24px 16px 16px;\n}\n.alertify .ajs-footer {\n  padding: 4px;\n  margin-left: -24px;\n  margin-right: -24px;\n  min-height: 43px;\n  background-color: #fff;\n}\n.alertify .ajs-footer .ajs-buttons.ajs-primary {\n  text-align: right;\n}\n.alertify .ajs-footer .ajs-buttons.ajs-primary .ajs-button {\n  margin: 4px;\n}\n.alertify .ajs-footer .ajs-buttons.ajs-auxiliary {\n  float: left;\n  clear: none;\n  text-align: left;\n}\n.alertify .ajs-footer .ajs-buttons.ajs-auxiliary .ajs-button {\n  margin: 4px;\n}\n.alertify .ajs-footer .ajs-buttons .ajs-button {\n  min-width: 88px;\n  min-height: 35px;\n}\n.alertify .ajs-handle {\n  position: absolute;\n  display: none;\n  width: 10px;\n  height: 10px;\n  right: 0;\n  bottom: 0;\n  z-index: 1;\n  background-image: url(" + ___CSS_LOADER_URL_REPLACEMENT_2___ + ");\n  -webkit-transform: scaleX(1) /*rtl:scaleX(-1)*/;\n          transform: scaleX(1) /*rtl:scaleX(-1)*/;\n  cursor: se-resize;\n}\n.alertify.ajs-no-overflow .ajs-body .ajs-content {\n  overflow: hidden !important;\n}\n.alertify.ajs-no-padding.ajs-maximized .ajs-body .ajs-content {\n  left: 0;\n  right: 0;\n  padding: 0;\n}\n.alertify.ajs-no-padding:not(.ajs-maximized) .ajs-body {\n  margin-left: -24px;\n  margin-right: -24px;\n}\n.alertify.ajs-no-padding:not(.ajs-maximized) .ajs-body .ajs-content {\n  padding: 0;\n}\n.alertify.ajs-no-padding.ajs-resizable .ajs-body .ajs-content {\n  left: 0;\n  right: 0;\n}\n.alertify.ajs-maximizable .ajs-commands button.ajs-maximize,\n.alertify.ajs-maximizable .ajs-commands button.ajs-restore {\n  display: inline-block;\n}\n.alertify.ajs-closable .ajs-commands button.ajs-close {\n  display: inline-block;\n}\n.alertify.ajs-maximized .ajs-dialog {\n  width: 100% !important;\n  height: 100% !important;\n  max-width: none !important;\n  margin: 0 auto !important;\n  top: 0 !important;\n  left: 0 !important;\n}\n.alertify.ajs-maximized.ajs-modeless .ajs-modal {\n  position: fixed !important;\n  min-height: 100% !important;\n  max-height: none !important;\n  margin: 0 !important;\n}\n.alertify.ajs-maximized .ajs-commands button.ajs-maximize {\n  background-image: url(" + ___CSS_LOADER_URL_REPLACEMENT_3___ + ");\n}\n.alertify.ajs-resizable .ajs-dialog,\n.alertify.ajs-maximized .ajs-dialog {\n  padding: 0;\n}\n.alertify.ajs-resizable .ajs-commands,\n.alertify.ajs-maximized .ajs-commands {\n  margin: 14px 24px 0 0;\n}\n.alertify.ajs-resizable .ajs-header,\n.alertify.ajs-maximized .ajs-header {\n  position: absolute;\n  top: 0;\n  left: 0;\n  right: 0;\n  margin: 0;\n  padding: 16px 24px;\n}\n.alertify.ajs-resizable .ajs-body,\n.alertify.ajs-maximized .ajs-body {\n  min-height: 224px;\n  display: inline-block;\n}\n.alertify.ajs-resizable .ajs-body .ajs-content,\n.alertify.ajs-maximized .ajs-body .ajs-content {\n  position: absolute;\n  top: 50px;\n  right: 24px;\n  bottom: 50px;\n  left: 24px;\n  overflow: auto;\n}\n.alertify.ajs-resizable .ajs-footer,\n.alertify.ajs-maximized .ajs-footer {\n  position: absolute;\n  left: 0;\n  right: 0;\n  bottom: 0;\n  margin: 0;\n}\n.alertify.ajs-resizable:not(.ajs-maximized) .ajs-dialog {\n  min-width: 548px;\n}\n.alertify.ajs-resizable:not(.ajs-maximized) .ajs-handle {\n  display: block;\n}\n.alertify.ajs-movable:not(.ajs-maximized) .ajs-header {\n  cursor: move;\n}\n.alertify.ajs-modeless .ajs-dimmer,\n.alertify.ajs-modeless .ajs-reset {\n  display: none;\n}\n.alertify.ajs-modeless .ajs-modal {\n  overflow: visible;\n  max-width: none;\n  max-height: 0;\n}\n.alertify.ajs-modeless.ajs-pinnable .ajs-commands button.ajs-pin {\n  display: inline-block;\n  background-image: url(" + ___CSS_LOADER_URL_REPLACEMENT_4___ + ");\n}\n.alertify.ajs-modeless.ajs-unpinned .ajs-modal {\n  position: absolute;\n}\n.alertify.ajs-modeless.ajs-unpinned .ajs-commands button.ajs-pin {\n  background-image: url(" + ___CSS_LOADER_URL_REPLACEMENT_5___ + ");\n}\n.alertify.ajs-modeless:not(.ajs-unpinned) .ajs-body {\n  max-height: 500px;\n  overflow: auto;\n}\n.alertify.ajs-basic .ajs-header {\n  opacity: 0;\n}\n.alertify.ajs-basic .ajs-footer {\n  visibility: hidden;\n}\n.alertify.ajs-frameless .ajs-header {\n  position: absolute;\n  top: 0;\n  left: 0;\n  right: 0;\n  min-height: 60px;\n  margin: 0;\n  padding: 0;\n  opacity: 0;\n  z-index: 1;\n}\n.alertify.ajs-frameless .ajs-footer {\n  display: none;\n}\n.alertify.ajs-frameless .ajs-body .ajs-content {\n  position: absolute;\n  top: 0;\n  right: 0;\n  bottom: 0;\n  left: 0;\n}\n.alertify.ajs-frameless:not(.ajs-resizable) .ajs-dialog {\n  padding-top: 0;\n}\n.alertify.ajs-frameless:not(.ajs-resizable) .ajs-dialog .ajs-commands {\n  margin-top: 0;\n}\n.ajs-no-overflow {\n  overflow: hidden !important;\n  outline: none;\n}\n.ajs-no-overflow.ajs-fixed {\n  position: fixed;\n  top: 0;\n  right: 0;\n  bottom: 0;\n  left: 0;\n  overflow-y: scroll!important;\n}\n.ajs-no-selection,\n.ajs-no-selection * {\n  -webkit-user-select: none;\n     -moz-user-select: none;\n      -ms-user-select: none;\n          user-select: none;\n}\n@media screen and (max-width: 568px) {\n  .alertify .ajs-dialog {\n    min-width: 150px;\n  }\n  .alertify:not(.ajs-maximized) .ajs-modal {\n    padding: 0 5%;\n  }\n  .alertify:not(.ajs-maximized).ajs-resizable .ajs-dialog {\n    min-width: initial;\n    min-width: auto /*IE fallback*/;\n  }\n}\n@-moz-document url-prefix() {\n  .alertify button:focus {\n    outline: 1px dotted #3593D2;\n  }\n}\n.alertify .ajs-dimmer,\n.alertify .ajs-modal {\n  -webkit-transform: translate3d(0, 0, 0);\n          transform: translate3d(0, 0, 0);\n  -webkit-transition-property: opacity, visibility;\n  transition-property: opacity, visibility;\n  -webkit-transition-timing-function: linear;\n          transition-timing-function: linear;\n  -webkit-transition-duration: 250ms;\n          transition-duration: 250ms;\n}\n.alertify.ajs-hidden .ajs-dimmer,\n.alertify.ajs-hidden .ajs-modal {\n  visibility: hidden;\n  opacity: 0;\n}\n.alertify.ajs-in:not(.ajs-hidden) .ajs-dialog {\n  -webkit-animation-duration: 500ms;\n          animation-duration: 500ms;\n}\n.alertify.ajs-out.ajs-hidden .ajs-dialog {\n  -webkit-animation-duration: 250ms;\n          animation-duration: 250ms;\n}\n.alertify .ajs-dialog.ajs-shake {\n  -webkit-animation-name: ajs-shake;\n          animation-name: ajs-shake;\n  -webkit-animation-duration: 0.1s;\n          animation-duration: 0.1s;\n  -webkit-animation-fill-mode: both;\n          animation-fill-mode: both;\n}\n@-webkit-keyframes ajs-shake {\n  0%,\n  100% {\n    -webkit-transform: translate3d(0, 0, 0);\n            transform: translate3d(0, 0, 0);\n  }\n  10%,\n  30%,\n  50%,\n  70%,\n  90% {\n    -webkit-transform: translate3d(-10px, 0, 0);\n            transform: translate3d(-10px, 0, 0);\n  }\n  20%,\n  40%,\n  60%,\n  80% {\n    -webkit-transform: translate3d(10px, 0, 0);\n            transform: translate3d(10px, 0, 0);\n  }\n}\n@keyframes ajs-shake {\n  0%,\n  100% {\n    -webkit-transform: translate3d(0, 0, 0);\n            transform: translate3d(0, 0, 0);\n  }\n  10%,\n  30%,\n  50%,\n  70%,\n  90% {\n    -webkit-transform: translate3d(-10px, 0, 0);\n            transform: translate3d(-10px, 0, 0);\n  }\n  20%,\n  40%,\n  60%,\n  80% {\n    -webkit-transform: translate3d(10px, 0, 0);\n            transform: translate3d(10px, 0, 0);\n  }\n}\n.alertify.ajs-slide.ajs-in:not(.ajs-hidden) .ajs-dialog {\n  -webkit-animation-name: ajs-slideIn;\n          animation-name: ajs-slideIn;\n  -webkit-animation-timing-function: cubic-bezier(0.175, 0.885, 0.32, 1.275);\n          animation-timing-function: cubic-bezier(0.175, 0.885, 0.32, 1.275);\n}\n.alertify.ajs-slide.ajs-out.ajs-hidden .ajs-dialog {\n  -webkit-animation-name: ajs-slideOut;\n          animation-name: ajs-slideOut;\n  -webkit-animation-timing-function: cubic-bezier(0.6, -0.28, 0.735, 0.045);\n          animation-timing-function: cubic-bezier(0.6, -0.28, 0.735, 0.045);\n}\n.alertify.ajs-zoom.ajs-in:not(.ajs-hidden) .ajs-dialog {\n  -webkit-animation-name: ajs-zoomIn;\n          animation-name: ajs-zoomIn;\n}\n.alertify.ajs-zoom.ajs-out.ajs-hidden .ajs-dialog {\n  -webkit-animation-name: ajs-zoomOut;\n          animation-name: ajs-zoomOut;\n}\n.alertify.ajs-fade.ajs-in:not(.ajs-hidden) .ajs-dialog {\n  -webkit-animation-name: ajs-fadeIn;\n          animation-name: ajs-fadeIn;\n}\n.alertify.ajs-fade.ajs-out.ajs-hidden .ajs-dialog {\n  -webkit-animation-name: ajs-fadeOut;\n          animation-name: ajs-fadeOut;\n}\n.alertify.ajs-pulse.ajs-in:not(.ajs-hidden) .ajs-dialog {\n  -webkit-animation-name: ajs-pulseIn;\n          animation-name: ajs-pulseIn;\n}\n.alertify.ajs-pulse.ajs-out.ajs-hidden .ajs-dialog {\n  -webkit-animation-name: ajs-pulseOut;\n          animation-name: ajs-pulseOut;\n}\n.alertify.ajs-flipx.ajs-in:not(.ajs-hidden) .ajs-dialog {\n  -webkit-animation-name: ajs-flipInX;\n          animation-name: ajs-flipInX;\n}\n.alertify.ajs-flipx.ajs-out.ajs-hidden .ajs-dialog {\n  -webkit-animation-name: ajs-flipOutX;\n          animation-name: ajs-flipOutX;\n}\n.alertify.ajs-flipy.ajs-in:not(.ajs-hidden) .ajs-dialog {\n  -webkit-animation-name: ajs-flipInY;\n          animation-name: ajs-flipInY;\n}\n.alertify.ajs-flipy.ajs-out.ajs-hidden .ajs-dialog {\n  -webkit-animation-name: ajs-flipOutY;\n          animation-name: ajs-flipOutY;\n}\n@-webkit-keyframes ajs-pulseIn {\n  0%,\n  20%,\n  40%,\n  60%,\n  80%,\n  100% {\n    -webkit-transition-timing-function: cubic-bezier(0.215, 0.61, 0.355, 1);\n            transition-timing-function: cubic-bezier(0.215, 0.61, 0.355, 1);\n  }\n  0% {\n    opacity: 0;\n    -webkit-transform: scale3d(0.3, 0.3, 0.3);\n            transform: scale3d(0.3, 0.3, 0.3);\n  }\n  20% {\n    -webkit-transform: scale3d(1.1, 1.1, 1.1);\n            transform: scale3d(1.1, 1.1, 1.1);\n  }\n  40% {\n    -webkit-transform: scale3d(0.9, 0.9, 0.9);\n            transform: scale3d(0.9, 0.9, 0.9);\n  }\n  60% {\n    opacity: 1;\n    -webkit-transform: scale3d(1.03, 1.03, 1.03);\n            transform: scale3d(1.03, 1.03, 1.03);\n  }\n  80% {\n    -webkit-transform: scale3d(0.97, 0.97, 0.97);\n            transform: scale3d(0.97, 0.97, 0.97);\n  }\n  100% {\n    opacity: 1;\n    -webkit-transform: scale3d(1, 1, 1);\n            transform: scale3d(1, 1, 1);\n  }\n}\n@keyframes ajs-pulseIn {\n  0%,\n  20%,\n  40%,\n  60%,\n  80%,\n  100% {\n    -webkit-transition-timing-function: cubic-bezier(0.215, 0.61, 0.355, 1);\n            transition-timing-function: cubic-bezier(0.215, 0.61, 0.355, 1);\n  }\n  0% {\n    opacity: 0;\n    -webkit-transform: scale3d(0.3, 0.3, 0.3);\n            transform: scale3d(0.3, 0.3, 0.3);\n  }\n  20% {\n    -webkit-transform: scale3d(1.1, 1.1, 1.1);\n            transform: scale3d(1.1, 1.1, 1.1);\n  }\n  40% {\n    -webkit-transform: scale3d(0.9, 0.9, 0.9);\n            transform: scale3d(0.9, 0.9, 0.9);\n  }\n  60% {\n    opacity: 1;\n    -webkit-transform: scale3d(1.03, 1.03, 1.03);\n            transform: scale3d(1.03, 1.03, 1.03);\n  }\n  80% {\n    -webkit-transform: scale3d(0.97, 0.97, 0.97);\n            transform: scale3d(0.97, 0.97, 0.97);\n  }\n  100% {\n    opacity: 1;\n    -webkit-transform: scale3d(1, 1, 1);\n            transform: scale3d(1, 1, 1);\n  }\n}\n@-webkit-keyframes ajs-pulseOut {\n  20% {\n    -webkit-transform: scale3d(0.9, 0.9, 0.9);\n            transform: scale3d(0.9, 0.9, 0.9);\n  }\n  50%,\n  55% {\n    opacity: 1;\n    -webkit-transform: scale3d(1.1, 1.1, 1.1);\n            transform: scale3d(1.1, 1.1, 1.1);\n  }\n  100% {\n    opacity: 0;\n    -webkit-transform: scale3d(0.3, 0.3, 0.3);\n            transform: scale3d(0.3, 0.3, 0.3);\n  }\n}\n@keyframes ajs-pulseOut {\n  20% {\n    -webkit-transform: scale3d(0.9, 0.9, 0.9);\n            transform: scale3d(0.9, 0.9, 0.9);\n  }\n  50%,\n  55% {\n    opacity: 1;\n    -webkit-transform: scale3d(1.1, 1.1, 1.1);\n            transform: scale3d(1.1, 1.1, 1.1);\n  }\n  100% {\n    opacity: 0;\n    -webkit-transform: scale3d(0.3, 0.3, 0.3);\n            transform: scale3d(0.3, 0.3, 0.3);\n  }\n}\n@-webkit-keyframes ajs-zoomIn {\n  0% {\n    opacity: 0;\n    -webkit-transform: scale3d(0.25, 0.25, 0.25);\n            transform: scale3d(0.25, 0.25, 0.25);\n  }\n  100% {\n    opacity: 1;\n    -webkit-transform: scale3d(1, 1, 1);\n            transform: scale3d(1, 1, 1);\n  }\n}\n@keyframes ajs-zoomIn {\n  0% {\n    opacity: 0;\n    -webkit-transform: scale3d(0.25, 0.25, 0.25);\n            transform: scale3d(0.25, 0.25, 0.25);\n  }\n  100% {\n    opacity: 1;\n    -webkit-transform: scale3d(1, 1, 1);\n            transform: scale3d(1, 1, 1);\n  }\n}\n@-webkit-keyframes ajs-zoomOut {\n  0% {\n    opacity: 1;\n    -webkit-transform: scale3d(1, 1, 1);\n            transform: scale3d(1, 1, 1);\n  }\n  100% {\n    opacity: 0;\n    -webkit-transform: scale3d(0.25, 0.25, 0.25);\n            transform: scale3d(0.25, 0.25, 0.25);\n  }\n}\n@keyframes ajs-zoomOut {\n  0% {\n    opacity: 1;\n    -webkit-transform: scale3d(1, 1, 1);\n            transform: scale3d(1, 1, 1);\n  }\n  100% {\n    opacity: 0;\n    -webkit-transform: scale3d(0.25, 0.25, 0.25);\n            transform: scale3d(0.25, 0.25, 0.25);\n  }\n}\n@-webkit-keyframes ajs-fadeIn {\n  0% {\n    opacity: 0;\n  }\n  100% {\n    opacity: 1;\n  }\n}\n@keyframes ajs-fadeIn {\n  0% {\n    opacity: 0;\n  }\n  100% {\n    opacity: 1;\n  }\n}\n@-webkit-keyframes ajs-fadeOut {\n  0% {\n    opacity: 1;\n  }\n  100% {\n    opacity: 0;\n  }\n}\n@keyframes ajs-fadeOut {\n  0% {\n    opacity: 1;\n  }\n  100% {\n    opacity: 0;\n  }\n}\n@-webkit-keyframes ajs-flipInX {\n  0% {\n    -webkit-transform: perspective(400px) rotate3d(1, 0, 0, 90deg);\n            transform: perspective(400px) rotate3d(1, 0, 0, 90deg);\n    -webkit-transition-timing-function: ease-in;\n            transition-timing-function: ease-in;\n    opacity: 0;\n  }\n  40% {\n    -webkit-transform: perspective(400px) rotate3d(1, 0, 0, -20deg);\n            transform: perspective(400px) rotate3d(1, 0, 0, -20deg);\n    -webkit-transition-timing-function: ease-in;\n            transition-timing-function: ease-in;\n  }\n  60% {\n    -webkit-transform: perspective(400px) rotate3d(1, 0, 0, 10deg);\n            transform: perspective(400px) rotate3d(1, 0, 0, 10deg);\n    opacity: 1;\n  }\n  80% {\n    -webkit-transform: perspective(400px) rotate3d(1, 0, 0, -5deg);\n            transform: perspective(400px) rotate3d(1, 0, 0, -5deg);\n  }\n  100% {\n    -webkit-transform: perspective(400px);\n            transform: perspective(400px);\n  }\n}\n@keyframes ajs-flipInX {\n  0% {\n    -webkit-transform: perspective(400px) rotate3d(1, 0, 0, 90deg);\n            transform: perspective(400px) rotate3d(1, 0, 0, 90deg);\n    -webkit-transition-timing-function: ease-in;\n            transition-timing-function: ease-in;\n    opacity: 0;\n  }\n  40% {\n    -webkit-transform: perspective(400px) rotate3d(1, 0, 0, -20deg);\n            transform: perspective(400px) rotate3d(1, 0, 0, -20deg);\n    -webkit-transition-timing-function: ease-in;\n            transition-timing-function: ease-in;\n  }\n  60% {\n    -webkit-transform: perspective(400px) rotate3d(1, 0, 0, 10deg);\n            transform: perspective(400px) rotate3d(1, 0, 0, 10deg);\n    opacity: 1;\n  }\n  80% {\n    -webkit-transform: perspective(400px) rotate3d(1, 0, 0, -5deg);\n            transform: perspective(400px) rotate3d(1, 0, 0, -5deg);\n  }\n  100% {\n    -webkit-transform: perspective(400px);\n            transform: perspective(400px);\n  }\n}\n@-webkit-keyframes ajs-flipOutX {\n  0% {\n    -webkit-transform: perspective(400px);\n            transform: perspective(400px);\n  }\n  30% {\n    -webkit-transform: perspective(400px) rotate3d(1, 0, 0, -20deg);\n            transform: perspective(400px) rotate3d(1, 0, 0, -20deg);\n    opacity: 1;\n  }\n  100% {\n    -webkit-transform: perspective(400px) rotate3d(1, 0, 0, 90deg);\n            transform: perspective(400px) rotate3d(1, 0, 0, 90deg);\n    opacity: 0;\n  }\n}\n@keyframes ajs-flipOutX {\n  0% {\n    -webkit-transform: perspective(400px);\n            transform: perspective(400px);\n  }\n  30% {\n    -webkit-transform: perspective(400px) rotate3d(1, 0, 0, -20deg);\n            transform: perspective(400px) rotate3d(1, 0, 0, -20deg);\n    opacity: 1;\n  }\n  100% {\n    -webkit-transform: perspective(400px) rotate3d(1, 0, 0, 90deg);\n            transform: perspective(400px) rotate3d(1, 0, 0, 90deg);\n    opacity: 0;\n  }\n}\n@-webkit-keyframes ajs-flipInY {\n  0% {\n    -webkit-transform: perspective(400px) rotate3d(0, 1, 0, 90deg);\n            transform: perspective(400px) rotate3d(0, 1, 0, 90deg);\n    -webkit-transition-timing-function: ease-in;\n            transition-timing-function: ease-in;\n    opacity: 0;\n  }\n  40% {\n    -webkit-transform: perspective(400px) rotate3d(0, 1, 0, -20deg);\n            transform: perspective(400px) rotate3d(0, 1, 0, -20deg);\n    -webkit-transition-timing-function: ease-in;\n            transition-timing-function: ease-in;\n  }\n  60% {\n    -webkit-transform: perspective(400px) rotate3d(0, 1, 0, 10deg);\n            transform: perspective(400px) rotate3d(0, 1, 0, 10deg);\n    opacity: 1;\n  }\n  80% {\n    -webkit-transform: perspective(400px) rotate3d(0, 1, 0, -5deg);\n            transform: perspective(400px) rotate3d(0, 1, 0, -5deg);\n  }\n  100% {\n    -webkit-transform: perspective(400px);\n            transform: perspective(400px);\n  }\n}\n@keyframes ajs-flipInY {\n  0% {\n    -webkit-transform: perspective(400px) rotate3d(0, 1, 0, 90deg);\n            transform: perspective(400px) rotate3d(0, 1, 0, 90deg);\n    -webkit-transition-timing-function: ease-in;\n            transition-timing-function: ease-in;\n    opacity: 0;\n  }\n  40% {\n    -webkit-transform: perspective(400px) rotate3d(0, 1, 0, -20deg);\n            transform: perspective(400px) rotate3d(0, 1, 0, -20deg);\n    -webkit-transition-timing-function: ease-in;\n            transition-timing-function: ease-in;\n  }\n  60% {\n    -webkit-transform: perspective(400px) rotate3d(0, 1, 0, 10deg);\n            transform: perspective(400px) rotate3d(0, 1, 0, 10deg);\n    opacity: 1;\n  }\n  80% {\n    -webkit-transform: perspective(400px) rotate3d(0, 1, 0, -5deg);\n            transform: perspective(400px) rotate3d(0, 1, 0, -5deg);\n  }\n  100% {\n    -webkit-transform: perspective(400px);\n            transform: perspective(400px);\n  }\n}\n@-webkit-keyframes ajs-flipOutY {\n  0% {\n    -webkit-transform: perspective(400px);\n            transform: perspective(400px);\n  }\n  30% {\n    -webkit-transform: perspective(400px) rotate3d(0, 1, 0, -15deg);\n            transform: perspective(400px) rotate3d(0, 1, 0, -15deg);\n    opacity: 1;\n  }\n  100% {\n    -webkit-transform: perspective(400px) rotate3d(0, 1, 0, 90deg);\n            transform: perspective(400px) rotate3d(0, 1, 0, 90deg);\n    opacity: 0;\n  }\n}\n@keyframes ajs-flipOutY {\n  0% {\n    -webkit-transform: perspective(400px);\n            transform: perspective(400px);\n  }\n  30% {\n    -webkit-transform: perspective(400px) rotate3d(0, 1, 0, -15deg);\n            transform: perspective(400px) rotate3d(0, 1, 0, -15deg);\n    opacity: 1;\n  }\n  100% {\n    -webkit-transform: perspective(400px) rotate3d(0, 1, 0, 90deg);\n            transform: perspective(400px) rotate3d(0, 1, 0, 90deg);\n    opacity: 0;\n  }\n}\n@-webkit-keyframes ajs-slideIn {\n  0% {\n    margin-top: -100%;\n  }\n  100% {\n    margin-top: 5%;\n  }\n}\n@keyframes ajs-slideIn {\n  0% {\n    margin-top: -100%;\n  }\n  100% {\n    margin-top: 5%;\n  }\n}\n@-webkit-keyframes ajs-slideOut {\n  0% {\n    margin-top: 5%;\n  }\n  100% {\n    margin-top: -100%;\n  }\n}\n@keyframes ajs-slideOut {\n  0% {\n    margin-top: 5%;\n  }\n  100% {\n    margin-top: -100%;\n  }\n}\n.alertify-notifier {\n  position: fixed;\n  width: 0;\n  overflow: visible;\n  z-index: 1982;\n  -webkit-transform: translate3d(0, 0, 0);\n          transform: translate3d(0, 0, 0);\n}\n.alertify-notifier .ajs-message {\n  position: relative;\n  width: 260px;\n  max-height: 0;\n  padding: 0;\n  opacity: 0;\n  margin: 0;\n  -webkit-transform: translate3d(0, 0, 0);\n          transform: translate3d(0, 0, 0);\n  -webkit-transition-duration: 250ms;\n          transition-duration: 250ms;\n  -webkit-transition-timing-function: linear;\n          transition-timing-function: linear;\n}\n.alertify-notifier .ajs-message.ajs-visible {\n  -webkit-transition-duration: 500ms;\n          transition-duration: 500ms;\n  -webkit-transition-timing-function: cubic-bezier(0.175, 0.885, 0.32, 1.275);\n          transition-timing-function: cubic-bezier(0.175, 0.885, 0.32, 1.275);\n  opacity: 1;\n  max-height: 100%;\n  padding: 15px;\n  margin-top: 10px;\n}\n.alertify-notifier .ajs-message.ajs-success {\n  background: rgba(91, 189, 114, 0.95);\n}\n.alertify-notifier .ajs-message.ajs-error {\n  background: rgba(217, 92, 92, 0.95);\n}\n.alertify-notifier .ajs-message.ajs-warning {\n  background: rgba(252, 248, 215, 0.95);\n}\n.alertify-notifier .ajs-message .ajs-close {\n  position: absolute;\n  top: 0;\n  right: 0;\n  width: 16px;\n  height: 16px;\n  cursor: pointer;\n  background-image: url(" + ___CSS_LOADER_URL_REPLACEMENT_6___ + ");\n  background-repeat: no-repeat;\n  background-position: center center;\n  background-color: rgba(0, 0, 0, 0.5);\n  border-top-right-radius: 2px;\n}\n.alertify-notifier.ajs-top {\n  top: 10px;\n}\n.alertify-notifier.ajs-bottom {\n  bottom: 10px;\n}\n.alertify-notifier.ajs-right {\n  right: 10px;\n}\n.alertify-notifier.ajs-right .ajs-message {\n  right: -320px;\n}\n.alertify-notifier.ajs-right .ajs-message.ajs-visible {\n  right: 290px;\n}\n.alertify-notifier.ajs-left {\n  left: 10px;\n}\n.alertify-notifier.ajs-left .ajs-message {\n  left: -300px;\n}\n.alertify-notifier.ajs-left .ajs-message.ajs-visible {\n  left: 0;\n}\n.alertify-notifier.ajs-center {\n  left: 50%;\n}\n.alertify-notifier.ajs-center .ajs-message {\n  -webkit-transform: translateX(-50%);\n          transform: translateX(-50%);\n}\n.alertify-notifier.ajs-center .ajs-message.ajs-visible {\n  left: 50%;\n  -webkit-transition-timing-function: cubic-bezier(0.57, 0.43, 0.1, 0.65);\n          transition-timing-function: cubic-bezier(0.57, 0.43, 0.1, 0.65);\n}\n.alertify-notifier.ajs-center.ajs-top .ajs-message {\n  top: -300px;\n}\n.alertify-notifier.ajs-center.ajs-top .ajs-message.ajs-visible {\n  top: 0;\n}\n.alertify-notifier.ajs-center.ajs-bottom .ajs-message {\n  bottom: -300px;\n}\n.alertify-notifier.ajs-center.ajs-bottom .ajs-message.ajs-visible {\n  bottom: 0;\n}\n.ajs-no-transition.alertify .ajs-dimmer,\n.ajs-no-transition.alertify .ajs-modal,\n.ajs-no-transition.alertify .ajs-dialog {\n  -webkit-transition: none!important;\n  transition: none!important;\n  -webkit-animation: none!important;\n          animation: none!important;\n}\n.ajs-no-transition.alertify-notifier .ajs-message {\n  -webkit-transition: none!important;\n  transition: none!important;\n  -webkit-animation: none!important;\n          animation: none!important;\n}\n@media (prefers-reduced-motion: reduce) {\n  .alertify .ajs-dimmer,\n  .alertify .ajs-modal,\n  .alertify .ajs-dialog {\n    -webkit-transition: none!important;\n    transition: none!important;\n    -webkit-animation: none!important;\n            animation: none!important;\n  }\n  .alertify-notifier .ajs-message {\n    -webkit-transition: none!important;\n    transition: none!important;\n    -webkit-animation: none!important;\n            animation: none!important;\n  }\n}\n", ""]);
// Exports
/* harmony default export */ __webpack_exports__["default"] = (___CSS_LOADER_EXPORT___);


/***/ }),
/* 10 */
/***/ (function(module) {

"use strict";


module.exports = function (i) {
  return i[1];
};

/***/ }),
/* 11 */
/***/ (function(module) {

"use strict";


/*
  MIT License http://www.opensource.org/licenses/mit-license.php
  Author Tobias Koppers @sokra
*/
module.exports = function (cssWithMappingToString) {
  var list = [];

  // return the list of modules as css string
  list.toString = function toString() {
    return this.map(function (item) {
      var content = "";
      var needLayer = typeof item[5] !== "undefined";
      if (item[4]) {
        content += "@supports (".concat(item[4], ") {");
      }
      if (item[2]) {
        content += "@media ".concat(item[2], " {");
      }
      if (needLayer) {
        content += "@layer".concat(item[5].length > 0 ? " ".concat(item[5]) : "", " {");
      }
      content += cssWithMappingToString(item);
      if (needLayer) {
        content += "}";
      }
      if (item[2]) {
        content += "}";
      }
      if (item[4]) {
        content += "}";
      }
      return content;
    }).join("");
  };

  // import a list of modules into the list
  list.i = function i(modules, media, dedupe, supports, layer) {
    if (typeof modules === "string") {
      modules = [[null, modules, undefined]];
    }
    var alreadyImportedModules = {};
    if (dedupe) {
      for (var k = 0; k < this.length; k++) {
        var id = this[k][0];
        if (id != null) {
          alreadyImportedModules[id] = true;
        }
      }
    }
    for (var _k = 0; _k < modules.length; _k++) {
      var item = [].concat(modules[_k]);
      if (dedupe && alreadyImportedModules[item[0]]) {
        continue;
      }
      if (typeof layer !== "undefined") {
        if (typeof item[5] === "undefined") {
          item[5] = layer;
        } else {
          item[1] = "@layer".concat(item[5].length > 0 ? " ".concat(item[5]) : "", " {").concat(item[1], "}");
          item[5] = layer;
        }
      }
      if (media) {
        if (!item[2]) {
          item[2] = media;
        } else {
          item[1] = "@media ".concat(item[2], " {").concat(item[1], "}");
          item[2] = media;
        }
      }
      if (supports) {
        if (!item[4]) {
          item[4] = "".concat(supports);
        } else {
          item[1] = "@supports (".concat(item[4], ") {").concat(item[1], "}");
          item[4] = supports;
        }
      }
      list.push(item);
    }
  };
  return list;
};

/***/ }),
/* 12 */
/***/ (function(module) {

"use strict";


module.exports = function (url, options) {
  if (!options) {
    options = {};
  }
  if (!url) {
    return url;
  }
  url = String(url.__esModule ? url.default : url);

  // If url is already wrapped in quotes, remove them
  if (/^['"].*['"]$/.test(url)) {
    url = url.slice(1, -1);
  }
  if (options.hash) {
    url += options.hash;
  }

  // Should url be wrapped?
  // See https://drafts.csswg.org/css-values-3/#urls
  if (/["'() \t\n]|(%20)/.test(url) || options.needQuotes) {
    return "\"".concat(url.replace(/"/g, '\\"').replace(/\n/g, "\\n"), "\"");
  }
  return url;
};

/***/ }),
/* 13 */
/***/ (function(module) {

"use strict";
module.exports = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAYAAACNMs+9AAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAALEgAACxIB0t1+/AAAABZ0RVh0Q3JlYXRpb24gVGltZQAwNy8xMy8xNOrZqugAAAAcdEVYdFNvZnR3YXJlAEFkb2JlIEZpcmV3b3JrcyBDUzbovLKMAAAAh0lEQVQYlY2QsQ0EIQwEB9cBAR1CJUaI/gigDnwR6NBL/7/xWLNrZ2b8EwGotVpr7eOitWa1VjugiNB7R1UPrKrWe0dEAHBbXUqxMQbeewDmnHjvyTm7C3zDwAUd9c63YQdUVdu6EAJzzquz7HXvTiklt+H9DQFYaxFjvDqllFyMkbXWvfpXHjJrWFgdBq/hAAAAAElFTkSuQmCC";

/***/ }),
/* 14 */
/***/ (function(module) {

"use strict";
module.exports = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAYAAACNMs+9AAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAALEgAACxIB0t1+/AAAABZ0RVh0Q3JlYXRpb24gVGltZQAwNy8xMy8xNOrZqugAAAAcdEVYdFNvZnR3YXJlAEFkb2JlIEZpcmV3b3JrcyBDUzbovLKMAAAAOUlEQVQYlWP8//8/AzGAhYGBgaG4uBiv6t7eXkYmooxjYGAgWiELsvHYFMCcRX2rSXcjoSBiJDbAAeD+EGu+8BZcAAAAAElFTkSuQmCC";

/***/ }),
/* 15 */
/***/ (function(module) {

"use strict";
module.exports = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAYAAACNMs+9AAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAALEgAACxIB0t1+/AAAABZ0RVh0Q3JlYXRpb24gVGltZQAwNy8xMS8xNEDQYmMAAAAcdEVYdFNvZnR3YXJlAEFkb2JlIEZpcmV3b3JrcyBDUzbovLKMAAAAQ0lEQVQYlaXNMQoAIAxD0dT7H657l0KX3iJuUlBUNOsPPCGJm7VDp6ryeMxMuDsAQH7owW3pyn3RS26iKxERMLN3ugOaAkaL3sWVigAAAABJRU5ErkJggg==";

/***/ }),
/* 16 */
/***/ (function(module) {

"use strict";
module.exports = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAYAAACNMs+9AAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAALEgAACxIB0t1+/AAAABZ0RVh0Q3JlYXRpb24gVGltZQAwNy8xMy8xNOrZqugAAAAcdEVYdFNvZnR3YXJlAEFkb2JlIEZpcmV3b3JrcyBDUzbovLKMAAAASklEQVQYlZWQ0QkAMQhDtXRincOZX78KVtrDCwgqJNEoIB3MPLj7lRUROlpyVXGzby6zWuY+kz6tj5sBMTMAyVV3/595RbOh3cAXsww1raeiOcoAAAAASUVORK5CYII=";

/***/ }),
/* 17 */
/***/ (function(module) {

"use strict";
module.exports = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAYAAACNMs+9AAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAALEgAACxIB0t1+/AAAABZ0RVh0Q3JlYXRpb24gVGltZQAwNy8xMy8xNOrZqugAAAAcdEVYdFNvZnR3YXJlAEFkb2JlIEZpcmV3b3JrcyBDUzbovLKMAAAAQklEQVQYlcWPMQ4AIAwCqU9u38GbcbHRWN1MvKQDhQFMEpKImGJA0gCgnYw0V0rwxseg5erT4oSkQVI5d9f+e9+xA0NbLpWfitPXAAAAAElFTkSuQmCC";

/***/ }),
/* 18 */
/***/ (function(module) {

"use strict";
module.exports = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAYAAACNMs+9AAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAALEgAACxIB0t1+/AAAABZ0RVh0Q3JlYXRpb24gVGltZQAwNy8xMy8xNOrZqugAAAAcdEVYdFNvZnR3YXJlAEFkb2JlIEZpcmV3b3JrcyBDUzbovLKMAAAAO0lEQVQYlWP8//8/AzGAiShV6AqLi4txGs+CLoBLMYbC3t5eRmyaWfBZhwwYkX2NTxPRvibKjRhW4wMAhxkYGbLu3pEAAAAASUVORK5CYII=";

/***/ }),
/* 19 */
/***/ (function(module) {

"use strict";
module.exports = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAgAAAAICAYAAADED76LAAAABGdBTUEAALGPC/xhBQAAAFBJREFUGBl1j0EKADEIA+ve/P9f9bh1hEihNBfjVCO1v7RKVqJK4h8gM5cAPR42AkQEpSXPwMTyoi13n5N9YqJehm3Fnr7nL1D0ZEbD5OubGyC7a9gx+9eNAAAAAElFTkSuQmCC";

/***/ }),
/* 20 */
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(3);
/* harmony import */ var _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_styleDomAPI_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(4);
/* harmony import */ var _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_styleDomAPI_js__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_styleDomAPI_js__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_insertBySelector_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(5);
/* harmony import */ var _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_insertBySelector_js__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_insertBySelector_js__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_setAttributesWithoutAttributes_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(6);
/* harmony import */ var _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_setAttributesWithoutAttributes_js__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_setAttributesWithoutAttributes_js__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_insertStyleElement_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(7);
/* harmony import */ var _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_insertStyleElement_js__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_insertStyleElement_js__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_styleTagTransform_js__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(8);
/* harmony import */ var _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_styleTagTransform_js__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(_Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_styleTagTransform_js__WEBPACK_IMPORTED_MODULE_5__);
/* harmony import */ var _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_css_loader_dist_cjs_js_default_css__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(21);

      
      
      
      
      
      
      
      
      

var options = {};

options.styleTagTransform = (_Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_styleTagTransform_js__WEBPACK_IMPORTED_MODULE_5___default());
options.setAttributes = (_Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_setAttributesWithoutAttributes_js__WEBPACK_IMPORTED_MODULE_3___default());

      options.insert = _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_insertBySelector_js__WEBPACK_IMPORTED_MODULE_2___default().bind(null, "head");
    
options.domAPI = (_Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_styleDomAPI_js__WEBPACK_IMPORTED_MODULE_1___default());
options.insertStyleElement = (_Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_insertStyleElement_js__WEBPACK_IMPORTED_MODULE_4___default());

var update = _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default()(_Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_css_loader_dist_cjs_js_default_css__WEBPACK_IMPORTED_MODULE_6__["default"], options);




       /* harmony default export */ __webpack_exports__["default"] = (_Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_css_loader_dist_cjs_js_default_css__WEBPACK_IMPORTED_MODULE_6__["default"] && _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_css_loader_dist_cjs_js_default_css__WEBPACK_IMPORTED_MODULE_6__["default"].locals ? _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_css_loader_dist_cjs_js_default_css__WEBPACK_IMPORTED_MODULE_6__["default"].locals : undefined);


/***/ }),
/* 21 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_css_loader_dist_runtime_noSourceMaps_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(10);
/* harmony import */ var _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_css_loader_dist_runtime_noSourceMaps_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_css_loader_dist_runtime_noSourceMaps_js__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(11);
/* harmony import */ var _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_1__);
// Imports


var ___CSS_LOADER_EXPORT___ = _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_1___default()((_Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_css_loader_dist_runtime_noSourceMaps_js__WEBPACK_IMPORTED_MODULE_0___default()));
// Module
___CSS_LOADER_EXPORT___.push([module.id, "/**\r\n * alertifyjs 1.14.0 http://alertifyjs.com\r\n * AlertifyJS is a javascript framework for developing pretty browser dialogs and notifications.\r\n * Copyright 2024 Mohammad Younes <Mohammad@alertifyjs.com> (http://alertifyjs.com) \r\n * Licensed under GPL 3 <https://opensource.org/licenses/gpl-3.0>*/\r\n.alertify .ajs-dialog {\n  background-color: white;\n  -webkit-box-shadow: 0px 15px 20px 0px rgba(0, 0, 0, 0.25);\n          box-shadow: 0px 15px 20px 0px rgba(0, 0, 0, 0.25);\n  border-radius: 2px;\n}\n.alertify .ajs-header {\n  color: black;\n  font-weight: bold;\n  background: #fafafa;\n  border-bottom: #eee 1px solid;\n  border-radius: 2px 2px 0 0;\n}\n.alertify .ajs-body {\n  color: black;\n}\n.alertify .ajs-body .ajs-content .ajs-input {\n  display: block;\n  width: 100%;\n  padding: 8px;\n  margin: 4px;\n  border-radius: 2px;\n  border: 1px solid #CCC;\n}\n.alertify .ajs-body .ajs-content p {\n  margin: 0;\n}\n.alertify .ajs-footer {\n  background: #fbfbfb;\n  border-top: #eee 1px solid;\n  border-radius: 0 0 2px 2px;\n}\n.alertify .ajs-footer .ajs-buttons .ajs-button {\n  background-color: transparent;\n  color: #000;\n  border: 0;\n  font-size: 14px;\n  font-weight: bold;\n  text-transform: uppercase;\n}\n.alertify .ajs-footer .ajs-buttons .ajs-button.ajs-ok {\n  color: #3593D2;\n}\n.alertify-notifier .ajs-message {\n  background: rgba(255, 255, 255, 0.95);\n  color: #000;\n  text-align: center;\n  border: solid 1px #ddd;\n  border-radius: 2px;\n}\n.alertify-notifier .ajs-message.ajs-success {\n  color: #fff;\n  background: rgba(91, 189, 114, 0.95);\n  text-shadow: -1px -1px 0 rgba(0, 0, 0, 0.5);\n}\n.alertify-notifier .ajs-message.ajs-error {\n  color: #fff;\n  background: rgba(217, 92, 92, 0.95);\n  text-shadow: -1px -1px 0 rgba(0, 0, 0, 0.5);\n}\n.alertify-notifier .ajs-message.ajs-warning {\n  background: rgba(252, 248, 215, 0.95);\n  border-color: #999;\n}\n", ""]);
// Exports
/* harmony default export */ __webpack_exports__["default"] = (___CSS_LOADER_EXPORT___);


/***/ }),
/* 22 */
/***/ (function(__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   driver: function() { return /* binding */ ke; }
/* harmony export */ });
let F = {};
function D(e = {}) {
  F = {
    animate: !0,
    allowClose: !0,
    overlayOpacity: 0.7,
    smoothScroll: !1,
    disableActiveInteraction: !1,
    showProgress: !1,
    stagePadding: 10,
    stageRadius: 5,
    popoverOffset: 10,
    showButtons: ["next", "previous", "close"],
    disableButtons: [],
    overlayColor: "#000",
    ...e
  };
}
function a(e) {
  return e ? F[e] : F;
}
function W(e, o, t, i) {
  return (e /= i / 2) < 1 ? t / 2 * e * e + o : -t / 2 * (--e * (e - 2) - 1) + o;
}
function Q(e) {
  const o = 'a[href]:not([disabled]), button:not([disabled]), textarea:not([disabled]), input[type="text"]:not([disabled]), input[type="radio"]:not([disabled]), input[type="checkbox"]:not([disabled]), select:not([disabled])';
  return e.flatMap((t) => {
    const i = t.matches(o), p = Array.from(t.querySelectorAll(o));
    return [...i ? [t] : [], ...p];
  }).filter((t) => getComputedStyle(t).pointerEvents !== "none" && ae(t));
}
function Z(e) {
  if (!e || se(e))
    return;
  const o = a("smoothScroll");
  e.scrollIntoView({
    // Removing the smooth scrolling for elements which exist inside the scrollable parent
    // This was causing the highlight to not properly render
    behavior: !o || re(e) ? "auto" : "smooth",
    inline: "center",
    block: "center"
  });
}
function re(e) {
  if (!e || !e.parentElement)
    return;
  const o = e.parentElement;
  return o.scrollHeight > o.clientHeight;
}
function se(e) {
  const o = e.getBoundingClientRect();
  return o.top >= 0 && o.left >= 0 && o.bottom <= (window.innerHeight || document.documentElement.clientHeight) && o.right <= (window.innerWidth || document.documentElement.clientWidth);
}
function ae(e) {
  return !!(e.offsetWidth || e.offsetHeight || e.getClientRects().length);
}
let O = {};
function b(e, o) {
  O[e] = o;
}
function l(e) {
  return e ? O[e] : O;
}
function V() {
  O = {};
}
let R = {};
function N(e, o) {
  R[e] = o;
}
function L(e) {
  var o;
  (o = R[e]) == null || o.call(R);
}
function ce() {
  R = {};
}
function le(e, o, t, i) {
  let p = l("__activeStagePosition");
  const n = p || t.getBoundingClientRect(), f = i.getBoundingClientRect(), w = W(e, n.x, f.x - n.x, o), r = W(e, n.y, f.y - n.y, o), v = W(e, n.width, f.width - n.width, o), s = W(e, n.height, f.height - n.height, o);
  p = {
    x: w,
    y: r,
    width: v,
    height: s
  }, J(p), b("__activeStagePosition", p);
}
function G(e) {
  if (!e)
    return;
  const o = e.getBoundingClientRect(), t = {
    x: o.x,
    y: o.y,
    width: o.width,
    height: o.height
  };
  b("__activeStagePosition", t), J(t);
}
function de() {
  const e = l("__activeStagePosition"), o = l("__overlaySvg");
  if (!e)
    return;
  if (!o) {
    console.warn("No stage svg found.");
    return;
  }
  const t = window.innerWidth, i = window.innerHeight;
  o.setAttribute("viewBox", `0 0 ${t} ${i}`);
}
function pe(e) {
  const o = ue(e);
  document.body.appendChild(o), te(o, (t) => {
    t.target.tagName === "path" && L("overlayClick");
  }), b("__overlaySvg", o);
}
function J(e) {
  const o = l("__overlaySvg");
  if (!o) {
    pe(e);
    return;
  }
  const t = o.firstElementChild;
  if ((t == null ? void 0 : t.tagName) !== "path")
    throw new Error("no path element found in stage svg");
  t.setAttribute("d", U(e));
}
function ue(e) {
  const o = window.innerWidth, t = window.innerHeight, i = document.createElementNS("http://www.w3.org/2000/svg", "svg");
  i.classList.add("driver-overlay", "driver-overlay-animated"), i.setAttribute("viewBox", `0 0 ${o} ${t}`), i.setAttribute("xmlSpace", "preserve"), i.setAttribute("xmlnsXlink", "http://www.w3.org/1999/xlink"), i.setAttribute("version", "1.1"), i.setAttribute("preserveAspectRatio", "xMinYMin slice"), i.style.fillRule = "evenodd", i.style.clipRule = "evenodd", i.style.strokeLinejoin = "round", i.style.strokeMiterlimit = "2", i.style.zIndex = "10000", i.style.position = "fixed", i.style.top = "0", i.style.left = "0", i.style.width = "100%", i.style.height = "100%";
  const p = document.createElementNS("http://www.w3.org/2000/svg", "path");
  return p.setAttribute("d", U(e)), p.style.fill = a("overlayColor") || "rgb(0,0,0)", p.style.opacity = `${a("overlayOpacity")}`, p.style.pointerEvents = "auto", p.style.cursor = "auto", i.appendChild(p), i;
}
function U(e) {
  const o = window.innerWidth, t = window.innerHeight, i = a("stagePadding") || 0, p = a("stageRadius") || 0, n = e.width + i * 2, f = e.height + i * 2, w = Math.min(p, n / 2, f / 2), r = Math.floor(Math.max(w, 0)), v = e.x - i + r, s = e.y - i, c = n - r * 2, d = f - r * 2;
  return `M${o},0L0,0L0,${t}L${o},${t}L${o},0Z
    M${v},${s} h${c} a${r},${r} 0 0 1 ${r},${r} v${d} a${r},${r} 0 0 1 -${r},${r} h-${c} a${r},${r} 0 0 1 -${r},-${r} v-${d} a${r},${r} 0 0 1 ${r},-${r} z`;
}
function ve() {
  const e = l("__overlaySvg");
  e && e.remove();
}
function fe() {
  const e = document.getElementById("driver-dummy-element");
  if (e)
    return e;
  let o = document.createElement("div");
  return o.id = "driver-dummy-element", o.style.width = "0", o.style.height = "0", o.style.pointerEvents = "none", o.style.opacity = "0", o.style.position = "fixed", o.style.top = "50%", o.style.left = "50%", document.body.appendChild(o), o;
}
function K(e) {
  const { element: o } = e;
  let t = typeof o == "string" ? document.querySelector(o) : o;
  t || (t = fe()), ge(t, e);
}
function he() {
  const e = l("__activeElement"), o = l("__activeStep");
  e && (G(e), de(), ie(e, o));
}
function ge(e, o) {
  const i = Date.now(), p = l("__activeStep"), n = l("__activeElement") || e, f = !n || n === e, w = e.id === "driver-dummy-element", r = n.id === "driver-dummy-element", v = a("animate"), s = o.onHighlightStarted || a("onHighlightStarted"), c = (o == null ? void 0 : o.onHighlighted) || a("onHighlighted"), d = (p == null ? void 0 : p.onDeselected) || a("onDeselected"), m = a(), g = l();
  !f && d && d(r ? void 0 : n, p, {
    config: m,
    state: g
  }), s && s(w ? void 0 : e, o, {
    config: m,
    state: g
  });
  const u = !f && v;
  let h = !1;
  xe(), b("previousStep", p), b("previousElement", n), b("activeStep", o), b("activeElement", e);
  const P = () => {
    if (l("__transitionCallback") !== P)
      return;
    const x = Date.now() - i, y = 400 - x <= 400 / 2;
    o.popover && y && !h && u && (X(e, o), h = !0), a("animate") && x < 400 ? le(x, 400, n, e) : (G(e), c && c(w ? void 0 : e, o, {
      config: a(),
      state: l()
    }), b("__transitionCallback", void 0), b("__previousStep", p), b("__previousElement", n), b("__activeStep", o), b("__activeElement", e)), window.requestAnimationFrame(P);
  };
  b("__transitionCallback", P), window.requestAnimationFrame(P), Z(e), !u && o.popover && X(e, o), n.classList.remove("driver-active-element", "driver-no-interaction"), n.removeAttribute("aria-haspopup"), n.removeAttribute("aria-expanded"), n.removeAttribute("aria-controls"), a("disableActiveInteraction") && e.classList.add("driver-no-interaction"), e.classList.add("driver-active-element"), e.setAttribute("aria-haspopup", "dialog"), e.setAttribute("aria-expanded", "true"), e.setAttribute("aria-controls", "driver-popover-content");
}
function we() {
  var e;
  (e = document.getElementById("driver-dummy-element")) == null || e.remove(), document.querySelectorAll(".driver-active-element").forEach((o) => {
    o.classList.remove("driver-active-element", "driver-no-interaction"), o.removeAttribute("aria-haspopup"), o.removeAttribute("aria-expanded"), o.removeAttribute("aria-controls");
  });
}
function I() {
  const e = l("__resizeTimeout");
  e && window.cancelAnimationFrame(e), b("__resizeTimeout", window.requestAnimationFrame(he));
}
function me(e) {
  var r;
  if (!l("isInitialized") || !(e.key === "Tab" || e.keyCode === 9))
    return;
  const i = l("__activeElement"), p = (r = l("popover")) == null ? void 0 : r.wrapper, n = Q([
    ...p ? [p] : [],
    ...i ? [i] : []
  ]), f = n[0], w = n[n.length - 1];
  if (e.preventDefault(), e.shiftKey) {
    const v = n[n.indexOf(document.activeElement) - 1] || w;
    v == null || v.focus();
  } else {
    const v = n[n.indexOf(document.activeElement) + 1] || f;
    v == null || v.focus();
  }
}
function ee(e) {
  var t;
  ((t = a("allowKeyboardControl")) == null || t) && (e.key === "Escape" ? L("escapePress") : e.key === "ArrowRight" ? L("arrowRightPress") : e.key === "ArrowLeft" && L("arrowLeftPress"));
}
function te(e, o, t) {
  const i = (n, f) => {
    const w = n.target;
    e.contains(w) && ((!t || t(w)) && (n.preventDefault(), n.stopPropagation(), n.stopImmediatePropagation()), f == null || f(n));
  };
  document.addEventListener("pointerdown", i, !0), document.addEventListener("mousedown", i, !0), document.addEventListener("pointerup", i, !0), document.addEventListener("mouseup", i, !0), document.addEventListener(
    "click",
    (n) => {
      i(n, o);
    },
    !0
  );
}
function ye() {
  window.addEventListener("keyup", ee, !1), window.addEventListener("keydown", me, !1), window.addEventListener("resize", I), window.addEventListener("scroll", I);
}
function be() {
  window.removeEventListener("keyup", ee), window.removeEventListener("resize", I), window.removeEventListener("scroll", I);
}
function xe() {
  const e = l("popover");
  e && (e.wrapper.style.display = "none");
}
function X(e, o) {
  var C, y;
  let t = l("popover");
  t && document.body.removeChild(t.wrapper), t = Pe(), document.body.appendChild(t.wrapper);
  const {
    title: i,
    description: p,
    showButtons: n,
    disableButtons: f,
    showProgress: w,
    nextBtnText: r = a("nextBtnText") || "Next &rarr;",
    prevBtnText: v = a("prevBtnText") || "&larr; Previous",
    progressText: s = a("progressText") || "{current} of {total}"
  } = o.popover || {};
  t.nextButton.innerHTML = r, t.previousButton.innerHTML = v, t.progress.innerHTML = s, i ? (t.title.innerHTML = i, t.title.style.display = "block") : t.title.style.display = "none", p ? (t.description.innerHTML = p, t.description.style.display = "block") : t.description.style.display = "none";
  const c = n || a("showButtons"), d = w || a("showProgress") || !1, m = (c == null ? void 0 : c.includes("next")) || (c == null ? void 0 : c.includes("previous")) || d;
  t.closeButton.style.display = c.includes("close") ? "block" : "none", m ? (t.footer.style.display = "flex", t.progress.style.display = d ? "block" : "none", t.nextButton.style.display = c.includes("next") ? "block" : "none", t.previousButton.style.display = c.includes("previous") ? "block" : "none") : t.footer.style.display = "none";
  const g = f || a("disableButtons") || [];
  g != null && g.includes("next") && (t.nextButton.disabled = !0, t.nextButton.classList.add("driver-popover-btn-disabled")), g != null && g.includes("previous") && (t.previousButton.disabled = !0, t.previousButton.classList.add("driver-popover-btn-disabled")), g != null && g.includes("close") && (t.closeButton.disabled = !0, t.closeButton.classList.add("driver-popover-btn-disabled"));
  const u = t.wrapper;
  u.style.display = "block", u.style.left = "", u.style.top = "", u.style.bottom = "", u.style.right = "", u.id = "driver-popover-content", u.setAttribute("role", "dialog"), u.setAttribute("aria-labelledby", "driver-popover-title"), u.setAttribute("aria-describedby", "driver-popover-description");
  const h = t.arrow;
  h.className = "driver-popover-arrow";
  const P = ((C = o.popover) == null ? void 0 : C.popoverClass) || a("popoverClass") || "";
  u.className = `driver-popover ${P}`.trim(), te(
    t.wrapper,
    (k) => {
      var $, B, M;
      const T = k.target, E = (($ = o.popover) == null ? void 0 : $.onNextClick) || a("onNextClick"), A = ((B = o.popover) == null ? void 0 : B.onPrevClick) || a("onPrevClick"), H = ((M = o.popover) == null ? void 0 : M.onCloseClick) || a("onCloseClick");
      if (T.classList.contains("driver-popover-next-btn"))
        return E ? E(e, o, {
          config: a(),
          state: l()
        }) : L("nextClick");
      if (T.classList.contains("driver-popover-prev-btn"))
        return A ? A(e, o, {
          config: a(),
          state: l()
        }) : L("prevClick");
      if (T.classList.contains("driver-popover-close-btn"))
        return H ? H(e, o, {
          config: a(),
          state: l()
        }) : L("closeClick");
    },
    (k) => !(t != null && t.description.contains(k)) && !(t != null && t.title.contains(k)) && typeof k.className == "string" && k.className.includes("driver-popover")
  ), b("popover", t);
  const S = ((y = o.popover) == null ? void 0 : y.onPopoverRender) || a("onPopoverRender");
  S && S(t, {
    config: a(),
    state: l()
  }), ie(e, o), Z(u);
  const _ = e.classList.contains("driver-dummy-element"), x = Q([u, ..._ ? [] : [e]]);
  x.length > 0 && x[0].focus();
}
function oe() {
  const e = l("popover");
  if (!(e != null && e.wrapper))
    return;
  const o = e.wrapper.getBoundingClientRect(), t = a("stagePadding") || 0, i = a("popoverOffset") || 0;
  return {
    width: o.width + t + i,
    height: o.height + t + i,
    realWidth: o.width,
    realHeight: o.height
  };
}
function Y(e, o) {
  const { elementDimensions: t, popoverDimensions: i, popoverPadding: p, popoverArrowDimensions: n } = o;
  return e === "start" ? Math.max(
    Math.min(
      t.top - p,
      window.innerHeight - i.realHeight - n.width
    ),
    n.width
  ) : e === "end" ? Math.max(
    Math.min(
      t.top - (i == null ? void 0 : i.realHeight) + t.height + p,
      window.innerHeight - (i == null ? void 0 : i.realHeight) - n.width
    ),
    n.width
  ) : e === "center" ? Math.max(
    Math.min(
      t.top + t.height / 2 - (i == null ? void 0 : i.realHeight) / 2,
      window.innerHeight - (i == null ? void 0 : i.realHeight) - n.width
    ),
    n.width
  ) : 0;
}
function j(e, o) {
  const { elementDimensions: t, popoverDimensions: i, popoverPadding: p, popoverArrowDimensions: n } = o;
  return e === "start" ? Math.max(
    Math.min(
      t.left - p,
      window.innerWidth - i.realWidth - n.width
    ),
    n.width
  ) : e === "end" ? Math.max(
    Math.min(
      t.left - (i == null ? void 0 : i.realWidth) + t.width + p,
      window.innerWidth - (i == null ? void 0 : i.realWidth) - n.width
    ),
    n.width
  ) : e === "center" ? Math.max(
    Math.min(
      t.left + t.width / 2 - (i == null ? void 0 : i.realWidth) / 2,
      window.innerWidth - (i == null ? void 0 : i.realWidth) - n.width
    ),
    n.width
  ) : 0;
}
function ie(e, o) {
  const t = l("popover");
  if (!t)
    return;
  const { align: i = "start", side: p = "left" } = (o == null ? void 0 : o.popover) || {}, n = i, f = e.id === "driver-dummy-element" ? "over" : p, w = a("stagePadding") || 0, r = oe(), v = t.arrow.getBoundingClientRect(), s = e.getBoundingClientRect(), c = s.top - r.height;
  let d = c >= 0;
  const m = window.innerHeight - (s.bottom + r.height);
  let g = m >= 0;
  const u = s.left - r.width;
  let h = u >= 0;
  const P = window.innerWidth - (s.right + r.width);
  let S = P >= 0;
  const _ = !d && !g && !h && !S;
  let x = f;
  if (f === "top" && d ? S = h = g = !1 : f === "bottom" && g ? S = h = d = !1 : f === "left" && h ? S = d = g = !1 : f === "right" && S && (h = d = g = !1), f === "over") {
    const C = window.innerWidth / 2 - r.realWidth / 2, y = window.innerHeight / 2 - r.realHeight / 2;
    t.wrapper.style.left = `${C}px`, t.wrapper.style.right = "auto", t.wrapper.style.top = `${y}px`, t.wrapper.style.bottom = "auto";
  } else if (_) {
    const C = window.innerWidth / 2 - (r == null ? void 0 : r.realWidth) / 2, y = 10;
    t.wrapper.style.left = `${C}px`, t.wrapper.style.right = "auto", t.wrapper.style.bottom = `${y}px`, t.wrapper.style.top = "auto";
  } else if (h) {
    const C = Math.min(
      u,
      window.innerWidth - (r == null ? void 0 : r.realWidth) - v.width
    ), y = Y(n, {
      elementDimensions: s,
      popoverDimensions: r,
      popoverPadding: w,
      popoverArrowDimensions: v
    });
    t.wrapper.style.left = `${C}px`, t.wrapper.style.top = `${y}px`, t.wrapper.style.bottom = "auto", t.wrapper.style.right = "auto", x = "left";
  } else if (S) {
    const C = Math.min(
      P,
      window.innerWidth - (r == null ? void 0 : r.realWidth) - v.width
    ), y = Y(n, {
      elementDimensions: s,
      popoverDimensions: r,
      popoverPadding: w,
      popoverArrowDimensions: v
    });
    t.wrapper.style.right = `${C}px`, t.wrapper.style.top = `${y}px`, t.wrapper.style.bottom = "auto", t.wrapper.style.left = "auto", x = "right";
  } else if (d) {
    const C = Math.min(
      c,
      window.innerHeight - r.realHeight - v.width
    );
    let y = j(n, {
      elementDimensions: s,
      popoverDimensions: r,
      popoverPadding: w,
      popoverArrowDimensions: v
    });
    t.wrapper.style.top = `${C}px`, t.wrapper.style.left = `${y}px`, t.wrapper.style.bottom = "auto", t.wrapper.style.right = "auto", x = "top";
  } else if (g) {
    const C = Math.min(
      m,
      window.innerHeight - (r == null ? void 0 : r.realHeight) - v.width
    );
    let y = j(n, {
      elementDimensions: s,
      popoverDimensions: r,
      popoverPadding: w,
      popoverArrowDimensions: v
    });
    t.wrapper.style.left = `${y}px`, t.wrapper.style.bottom = `${C}px`, t.wrapper.style.top = "auto", t.wrapper.style.right = "auto", x = "bottom";
  }
  _ ? t.arrow.classList.add("driver-popover-arrow-none") : Ce(n, x, e);
}
function Ce(e, o, t) {
  const i = l("popover");
  if (!i)
    return;
  const p = t.getBoundingClientRect(), n = oe(), f = i.arrow, w = n.width, r = window.innerWidth, v = p.width, s = p.left, c = n.height, d = window.innerHeight, m = p.top, g = p.height;
  f.className = "driver-popover-arrow";
  let u = o, h = e;
  o === "top" ? (s + v <= 0 ? (u = "right", h = "end") : s + v - w <= 0 && (u = "top", h = "start"), s >= r ? (u = "left", h = "end") : s + w >= r && (u = "top", h = "end")) : o === "bottom" ? (s + v <= 0 ? (u = "right", h = "start") : s + v - w <= 0 && (u = "bottom", h = "start"), s >= r ? (u = "left", h = "start") : s + w >= r && (u = "bottom", h = "end")) : o === "left" ? (m + g <= 0 ? (u = "bottom", h = "end") : m + g - c <= 0 && (u = "left", h = "start"), m >= d ? (u = "top", h = "end") : m + c >= d && (u = "left", h = "end")) : o === "right" && (m + g <= 0 ? (u = "bottom", h = "start") : m + g - c <= 0 && (u = "right", h = "start"), m >= d ? (u = "top", h = "start") : m + c >= d && (u = "right", h = "end")), u ? (f.classList.add(`driver-popover-arrow-side-${u}`), f.classList.add(`driver-popover-arrow-align-${h}`)) : f.classList.add("driver-popover-arrow-none");
}
function Pe() {
  const e = document.createElement("div");
  e.classList.add("driver-popover");
  const o = document.createElement("div");
  o.classList.add("driver-popover-arrow");
  const t = document.createElement("header");
  t.id = "driver-popover-title", t.classList.add("driver-popover-title"), t.style.display = "none", t.innerText = "Popover Title";
  const i = document.createElement("div");
  i.id = "driver-popover-description", i.classList.add("driver-popover-description"), i.style.display = "none", i.innerText = "Popover description is here";
  const p = document.createElement("button");
  p.type = "button", p.classList.add("driver-popover-close-btn"), p.setAttribute("aria-label", "Close"), p.innerHTML = "&times;";
  const n = document.createElement("footer");
  n.classList.add("driver-popover-footer");
  const f = document.createElement("span");
  f.classList.add("driver-popover-progress-text"), f.innerText = "";
  const w = document.createElement("span");
  w.classList.add("driver-popover-navigation-btns");
  const r = document.createElement("button");
  r.type = "button", r.classList.add("driver-popover-prev-btn"), r.innerHTML = "&larr; Previous";
  const v = document.createElement("button");
  return v.type = "button", v.classList.add("driver-popover-next-btn"), v.innerHTML = "Next &rarr;", w.appendChild(r), w.appendChild(v), n.appendChild(f), n.appendChild(w), e.appendChild(p), e.appendChild(o), e.appendChild(t), e.appendChild(i), e.appendChild(n), {
    wrapper: e,
    arrow: o,
    title: t,
    description: i,
    footer: n,
    previousButton: r,
    nextButton: v,
    closeButton: p,
    footerButtons: w,
    progress: f
  };
}
function Se() {
  var o;
  const e = l("popover");
  e && ((o = e.wrapper.parentElement) == null || o.removeChild(e.wrapper));
}
function ke(e = {}) {
  D(e);
  function o() {
    a("allowClose") && v();
  }
  function t() {
    const s = l("activeIndex"), c = a("steps") || [];
    if (typeof s == "undefined")
      return;
    const d = s + 1;
    c[d] ? r(d) : v();
  }
  function i() {
    const s = l("activeIndex"), c = a("steps") || [];
    if (typeof s == "undefined")
      return;
    const d = s - 1;
    c[d] ? r(d) : v();
  }
  function p(s) {
    (a("steps") || [])[s] ? r(s) : v();
  }
  function n() {
    var h;
    if (l("__transitionCallback"))
      return;
    const c = l("activeIndex"), d = l("__activeStep"), m = l("__activeElement");
    if (typeof c == "undefined" || typeof d == "undefined" || typeof l("activeIndex") == "undefined")
      return;
    const u = ((h = d.popover) == null ? void 0 : h.onPrevClick) || a("onPrevClick");
    if (u)
      return u(m, d, {
        config: a(),
        state: l()
      });
    i();
  }
  function f() {
    var u;
    if (l("__transitionCallback"))
      return;
    const c = l("activeIndex"), d = l("__activeStep"), m = l("__activeElement");
    if (typeof c == "undefined" || typeof d == "undefined")
      return;
    const g = ((u = d.popover) == null ? void 0 : u.onNextClick) || a("onNextClick");
    if (g)
      return g(m, d, {
        config: a(),
        state: l()
      });
    t();
  }
  function w() {
    l("isInitialized") || (b("isInitialized", !0), document.body.classList.add("driver-active", a("animate") ? "driver-fade" : "driver-simple"), ye(), N("overlayClick", o), N("escapePress", o), N("arrowLeftPress", n), N("arrowRightPress", f));
  }
  function r(s = 0) {
    var E, A, H, $, B, M, z, q;
    const c = a("steps");
    if (!c) {
      console.error("No steps to drive through"), v();
      return;
    }
    if (!c[s]) {
      v();
      return;
    }
    b("__activeOnDestroyed", document.activeElement), b("activeIndex", s);
    const d = c[s], m = c[s + 1], g = c[s - 1], u = ((E = d.popover) == null ? void 0 : E.doneBtnText) || a("doneBtnText") || "Done", h = a("allowClose"), P = typeof ((A = d.popover) == null ? void 0 : A.showProgress) != "undefined" ? (H = d.popover) == null ? void 0 : H.showProgress : a("showProgress"), _ = ((($ = d.popover) == null ? void 0 : $.progressText) || a("progressText") || "{{current}} of {{total}}").replace("{{current}}", `${s + 1}`).replace("{{total}}", `${c.length}`), x = ((B = d.popover) == null ? void 0 : B.showButtons) || a("showButtons"), C = [
      "next",
      "previous",
      ...h ? ["close"] : []
    ].filter((ne) => !(x != null && x.length) || x.includes(ne)), y = ((M = d.popover) == null ? void 0 : M.onNextClick) || a("onNextClick"), k = ((z = d.popover) == null ? void 0 : z.onPrevClick) || a("onPrevClick"), T = ((q = d.popover) == null ? void 0 : q.onCloseClick) || a("onCloseClick");
    K({
      ...d,
      popover: {
        showButtons: C,
        nextBtnText: m ? void 0 : u,
        disableButtons: [...g ? [] : ["previous"]],
        showProgress: P,
        progressText: _,
        onNextClick: y || (() => {
          m ? r(s + 1) : v();
        }),
        onPrevClick: k || (() => {
          r(s - 1);
        }),
        onCloseClick: T || (() => {
          v();
        }),
        ...(d == null ? void 0 : d.popover) || {}
      }
    });
  }
  function v(s = !0) {
    const c = l("__activeElement"), d = l("__activeStep"), m = l("__activeOnDestroyed"), g = a("onDestroyStarted");
    if (s && g) {
      const P = !c || (c == null ? void 0 : c.id) === "driver-dummy-element";
      g(P ? void 0 : c, d, {
        config: a(),
        state: l()
      });
      return;
    }
    const u = (d == null ? void 0 : d.onDeselected) || a("onDeselected"), h = a("onDestroyed");
    if (document.body.classList.remove("driver-active", "driver-fade", "driver-simple"), be(), Se(), we(), ve(), ce(), V(), c && d) {
      const P = c.id === "driver-dummy-element";
      u && u(P ? void 0 : c, d, {
        config: a(),
        state: l()
      }), h && h(P ? void 0 : c, d, {
        config: a(),
        state: l()
      });
    }
    m && m.focus();
  }
  return {
    isActive: () => l("isInitialized") || !1,
    refresh: I,
    drive: (s = 0) => {
      w(), r(s);
    },
    setConfig: D,
    setSteps: (s) => {
      V(), D({
        ...a(),
        steps: s
      });
    },
    getConfig: a,
    getState: l,
    getActiveIndex: () => l("activeIndex"),
    isFirstStep: () => l("activeIndex") === 0,
    isLastStep: () => {
      const s = a("steps") || [], c = l("activeIndex");
      return c !== void 0 && c === s.length - 1;
    },
    getActiveStep: () => l("activeStep"),
    getActiveElement: () => l("activeElement"),
    getPreviousElement: () => l("previousElement"),
    getPreviousStep: () => l("previousStep"),
    moveNext: t,
    movePrevious: i,
    moveTo: p,
    hasNextStep: () => {
      const s = a("steps") || [], c = l("activeIndex");
      return c !== void 0 && s[c + 1];
    },
    hasPreviousStep: () => {
      const s = a("steps") || [], c = l("activeIndex");
      return c !== void 0 && s[c - 1];
    },
    highlight: (s) => {
      w(), K({
        ...s,
        popover: s.popover ? {
          showButtons: [],
          showProgress: !1,
          progressText: "",
          ...s.popover
        } : void 0
      });
    },
    destroy: () => {
      v(!1);
    }
  };
}



/***/ }),
/* 23 */
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(3);
/* harmony import */ var _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_styleDomAPI_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(4);
/* harmony import */ var _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_styleDomAPI_js__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_styleDomAPI_js__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_insertBySelector_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(5);
/* harmony import */ var _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_insertBySelector_js__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_insertBySelector_js__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_setAttributesWithoutAttributes_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(6);
/* harmony import */ var _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_setAttributesWithoutAttributes_js__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_setAttributesWithoutAttributes_js__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_insertStyleElement_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(7);
/* harmony import */ var _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_insertStyleElement_js__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_insertStyleElement_js__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_styleTagTransform_js__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(8);
/* harmony import */ var _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_styleTagTransform_js__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(_Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_styleTagTransform_js__WEBPACK_IMPORTED_MODULE_5__);
/* harmony import */ var _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_css_loader_dist_cjs_js_driver_css__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(24);

      
      
      
      
      
      
      
      
      

var options = {};

options.styleTagTransform = (_Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_styleTagTransform_js__WEBPACK_IMPORTED_MODULE_5___default());
options.setAttributes = (_Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_setAttributesWithoutAttributes_js__WEBPACK_IMPORTED_MODULE_3___default());

      options.insert = _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_insertBySelector_js__WEBPACK_IMPORTED_MODULE_2___default().bind(null, "head");
    
options.domAPI = (_Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_styleDomAPI_js__WEBPACK_IMPORTED_MODULE_1___default());
options.insertStyleElement = (_Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_insertStyleElement_js__WEBPACK_IMPORTED_MODULE_4___default());

var update = _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default()(_Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_css_loader_dist_cjs_js_driver_css__WEBPACK_IMPORTED_MODULE_6__["default"], options);




       /* harmony default export */ __webpack_exports__["default"] = (_Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_css_loader_dist_cjs_js_driver_css__WEBPACK_IMPORTED_MODULE_6__["default"] && _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_css_loader_dist_cjs_js_driver_css__WEBPACK_IMPORTED_MODULE_6__["default"].locals ? _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_css_loader_dist_cjs_js_driver_css__WEBPACK_IMPORTED_MODULE_6__["default"].locals : undefined);


/***/ }),
/* 24 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_css_loader_dist_runtime_noSourceMaps_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(10);
/* harmony import */ var _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_css_loader_dist_runtime_noSourceMaps_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_css_loader_dist_runtime_noSourceMaps_js__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(11);
/* harmony import */ var _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_1__);
// Imports


var ___CSS_LOADER_EXPORT___ = _Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_1___default()((_Users_rcmanuel_AppData_Local_Prepros_app_7_26_0_resources_app_asar_node_modules_css_loader_dist_runtime_noSourceMaps_js__WEBPACK_IMPORTED_MODULE_0___default()));
// Module
___CSS_LOADER_EXPORT___.push([module.id, ".driver-active .driver-overlay,.driver-active *{pointer-events:none}.driver-active .driver-active-element,.driver-active .driver-active-element *,.driver-popover,.driver-popover *{pointer-events:auto}@keyframes animate-fade-in{0%{opacity:0}to{opacity:1}}.driver-fade .driver-overlay{animation:animate-fade-in .2s ease-in-out}.driver-fade .driver-popover{animation:animate-fade-in .2s}.driver-popover{all:unset;box-sizing:border-box;color:#2d2d2d;margin:0;padding:15px;border-radius:5px;min-width:250px;max-width:300px;box-shadow:0 1px 10px #0006;z-index:1000000000;position:fixed;top:0;right:0;background-color:#fff}.driver-popover *{font-family:Helvetica Neue,Inter,ui-sans-serif,\"Apple Color Emoji\",Helvetica,Arial,sans-serif}.driver-popover-title{font:19px/normal sans-serif;font-weight:700;display:block;position:relative;line-height:1.5;zoom:1;margin:0}.driver-popover-close-btn{all:unset;position:absolute;top:0;right:0;width:32px;height:28px;cursor:pointer;font-size:18px;font-weight:500;color:#d2d2d2;z-index:1;text-align:center;transition:color;transition-duration:.2s}.driver-popover-close-btn:hover,.driver-popover-close-btn:focus{color:#2d2d2d}.driver-popover-title[style*=block]+.driver-popover-description{margin-top:5px}.driver-popover-description{margin-bottom:0;font:14px/normal sans-serif;line-height:1.5;font-weight:400;zoom:1}.driver-popover-footer{margin-top:15px;text-align:right;zoom:1;display:flex;align-items:center;justify-content:space-between}.driver-popover-progress-text{font-size:13px;font-weight:400;color:#727272;zoom:1}.driver-popover-footer button{all:unset;display:inline-block;box-sizing:border-box;padding:3px 7px;text-decoration:none;text-shadow:1px 1px 0 #fff;background-color:#fff;color:#2d2d2d;font:12px/normal sans-serif;cursor:pointer;outline:0;zoom:1;line-height:1.3;border:1px solid #ccc;border-radius:3px}.driver-popover-footer .driver-popover-btn-disabled{opacity:.5;pointer-events:none}:not(body):has(>.driver-active-element){overflow:hidden!important}.driver-no-interaction,.driver-no-interaction *{pointer-events:none!important}.driver-popover-footer button:hover,.driver-popover-footer button:focus{background-color:#f7f7f7}.driver-popover-navigation-btns{display:flex;flex-grow:1;justify-content:flex-end}.driver-popover-navigation-btns button+button{margin-left:4px}.driver-popover-arrow{content:\"\";position:absolute;border:5px solid #fff}.driver-popover-arrow-side-over{display:none}.driver-popover-arrow-side-left{left:100%;border-right-color:transparent;border-bottom-color:transparent;border-top-color:transparent}.driver-popover-arrow-side-right{right:100%;border-left-color:transparent;border-bottom-color:transparent;border-top-color:transparent}.driver-popover-arrow-side-top{top:100%;border-right-color:transparent;border-bottom-color:transparent;border-left-color:transparent}.driver-popover-arrow-side-bottom{bottom:100%;border-left-color:transparent;border-top-color:transparent;border-right-color:transparent}.driver-popover-arrow-side-center{display:none}.driver-popover-arrow-side-left.driver-popover-arrow-align-start,.driver-popover-arrow-side-right.driver-popover-arrow-align-start{top:15px}.driver-popover-arrow-side-top.driver-popover-arrow-align-start,.driver-popover-arrow-side-bottom.driver-popover-arrow-align-start{left:15px}.driver-popover-arrow-align-end.driver-popover-arrow-side-left,.driver-popover-arrow-align-end.driver-popover-arrow-side-right{bottom:15px}.driver-popover-arrow-side-top.driver-popover-arrow-align-end,.driver-popover-arrow-side-bottom.driver-popover-arrow-align-end{right:15px}.driver-popover-arrow-side-left.driver-popover-arrow-align-center,.driver-popover-arrow-side-right.driver-popover-arrow-align-center{top:50%;margin-top:-5px}.driver-popover-arrow-side-top.driver-popover-arrow-align-center,.driver-popover-arrow-side-bottom.driver-popover-arrow-align-center{left:50%;margin-left:-5px}.driver-popover-arrow-none{display:none}\n", ""]);
// Exports
/* harmony default export */ __webpack_exports__["default"] = (___CSS_LOADER_EXPORT___);


/***/ })
/******/ 	]);
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			id: moduleId,
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = __webpack_modules__;
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	!function() {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = function(module) {
/******/ 			var getter = module && module.__esModule ?
/******/ 				function() { return module['default']; } :
/******/ 				function() { return module; };
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	!function() {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = function(exports, definition) {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	!function() {
/******/ 		__webpack_require__.o = function(obj, prop) { return Object.prototype.hasOwnProperty.call(obj, prop); }
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	!function() {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = function(exports) {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/jsonp chunk loading */
/******/ 	!function() {
/******/ 		__webpack_require__.b = document.baseURI || self.location.href;
/******/ 		
/******/ 		// object to store loaded and loading chunks
/******/ 		// undefined = chunk not loaded, null = chunk preloaded/prefetched
/******/ 		// [resolve, reject, Promise] = chunk loading, 0 = chunk loaded
/******/ 		var installedChunks = {
/******/ 			0: 0
/******/ 		};
/******/ 		
/******/ 		// no chunk on demand loading
/******/ 		
/******/ 		// no prefetching
/******/ 		
/******/ 		// no preloaded
/******/ 		
/******/ 		// no HMR
/******/ 		
/******/ 		// no HMR manifest
/******/ 		
/******/ 		// no on chunks loaded
/******/ 		
/******/ 		// no jsonp function
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/nonce */
/******/ 	!function() {
/******/ 		__webpack_require__.nc = undefined;
/******/ 	}();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be in strict mode.
!function() {
"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var alertifyjs__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(1);
/* harmony import */ var alertifyjs__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(alertifyjs__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var alertifyjs_build_css_alertify_css__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(2);
/* harmony import */ var alertifyjs_build_css_themes_default_css__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(20);
/* harmony import */ var driver_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(22);
/* harmony import */ var driver_js_dist_driver_css__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(23);
//import '@google/model-viewer';

 // Importa los estilos de Alertify
 // Opcional: Importa un tema, como el tema por defecto



(function ($) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).on('load', function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practice to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	$(function () {

		if ($('#ar_model_viewer_for_woocommerce_ar2').is(':checked')) {
			//alert("Radio Button Is checked!");
			$('.cmb2-id-ar-model-viewer-for-woocommerce-ar-modes').hide();
			$('.cmb2-id-ar-model-viewer-for-woocommerce-ar-scale').hide();
			$('.cmb2-id-ar-model-viewer-for-woocommerce-ar-placement').hide();
			$('.cmb2-id-ar-model-viewer-for-woocommerce-xr-environment').hide();
			$('#cmb2-id-ar-model-viewer-for-woocommerce-ar-settings').hide();
			$('.cmb2-id-ar-model-viewer-for-woocommerce-ar-button').hide();
		}
		if ($('#ar_model_viewer_for_woocommerce_ar_button2').is(':checked')) {
			//alert("Radio Button Is checked!");
			$('.cmb2-id-ar-model-viewer-for-woocommerce-ar-button-text').hide();
			$('.cmb2-id-ar-model-viewer-for-woocommerce-ar-button-background-color').hide();
			$('.cmb2-id-ar-model-viewer-for-woocommerce-ar-button-text-color').hide();
		}
	});

})(jQuery);
}();
/******/ })()
;