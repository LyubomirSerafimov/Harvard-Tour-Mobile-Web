function scrollToTop() {
	scrollTo(0,0); 
}

(function (window) {
    function kgoBridgeHandler(config) {
        if (typeof config == 'object') {
            for (var i in config) {
                this.config[i] = config[i];
            }
        }
    }
    
    kgoBridgeHandler.prototype = {
        config: {
            events: false,  // desktop browser simulation mode
            base: "",
            url: "",
            ajaxArgs: "",
            pagePage: "",
            pageArgs: "",
            serverURL: "",
            timeout: 60,
            localizedStrings: {}
        },
        callbacks : {},
        callbackIdCounter : 0,
        
        // This code list is duplicated in iOS and Android code.  
        // Do not change existing codes!
        errorCodes : {
            KGOBridgeErrorAPINotSupported : 1,
            KGOBridgeErrorJSONConvertFailed : 2
        },
        
        // ====================================================================
        // Bridge API
        // ====================================================================
        
        //
        // Page load
        //
        
        initPage: function (params, statusCallback) {
            if (typeof statusCallback == "undefined") { statusCallback = null; }
            
            this.nativeAPI("page", "init", params, statusCallback);
        },
        
        //
        // Errors
        //
        
        initPageError: function (httpStatus, title, message) {
            switch (httpStatus) {
                case 401:
                case 407:
                    title = this.localizedString("ERROR_HTTP_UNAUTHORIZED_REQUEST_TITLE");
                    message = this.localizedString("ERROR_HTTP_UNAUTHORIZED_REQUEST_MESSAGE");
                    break;
                case 408:
                    title = this.localizedString("ERROR_HTTP_CONNECTION_TIMEOUT_TITLE");
                    message = this.localizedString("ERROR_HTTP_CONNECTION_TIMEOUT_MESSAGE");
                    break;
                case 404:
                case 503:
                default:
                    title = this.localizedString("ERROR_HTTP_CONNECTION_FAILED_TITLE");
                    message = this.localizedString("ERROR_HTTP_CONNECTION_FAILED_MESSAGE");
                    break;
            }
            
            this.handleError("pageinit", httpStatus, title, message);
        },

        handleError: function (errorType, code, title, message) {
            if (typeof title   != "string") { title   = ""; }
            if (typeof message != "string") { message = ""; }
            
            this.nativeAPI("error", errorType, {
                "code"    : code, 
                "title"   : title, 
                "message" : message
            });
        },
        
        //
        // Dialogs
        //
        
        alert: function (message, responseCallback /* optional */) {
            var ok = this.localizedString("BUTTON_OK");

            this.alertDialog(message, null, ok, null, null, function (error, params) {
                if (typeof responseCallback != "undefined" && responseCallback && error !== null) {
                    responseCallback();
                }
            }, function (error, params) {
                if (typeof responseCallback != "undefined" && responseCallback) {
                    responseCallback();
                }
            });
        },
        
        confirm: function (question, responseCallback) {
            var ok = this.localizedString("BUTTON_OK");
            var cancel = this.localizedString("BUTTON_CANCEL");
            
            this.alertDialog(message, null, ok, cancel, null, function (error, params) {
                if (error !== null) {
                    responseCallback(false);
                }
            }, function (error, params) {
                // Return true when main button is pressed
                responseCallback(error === null && params["button"] == "main");
            });
        },
        
        shareDialog: function (buttonConfig) {
            var buttonTitles = [];
            var actionURLs = [];
            if ("mail" in buttonConfig) {
                buttonTitles.push(this.localizedString("SHARE_OPTION_EMAIL"));
                actionURLs.push(buttonConfig["mail"]);
            }
            if ("facebook" in buttonConfig) {
                buttonTitles.push(this.localizedString("SHARE_OPTION_FACEBOOK"));
                actionURLs.push(buttonConfig["facebook"]);
            }
            if ("twitter" in buttonConfig) {
                buttonTitles.push(this.localizedString("SHARE_OPTION_TWITTER"));
                actionURLs.push(buttonConfig["twitter"]);
            }
            
            var title = this.localizedString("SHARE_THIS_ITEM");
            var cancel = this.localizedString("BUTTON_CANCEL");
            
            var that = this;
            this.actionDialog(title, cancel, null, buttonTitles, null, function(error, params) {
                if ("button" in params && params["button"].indexOf('alternate') === 0) {
                    var index = +params["button"].substr(9);
                    if (index >= 0 && index < actionURLs.length) {
                        that.loadURL(actionURLs[index]);
                    }
                }
            });
        },
        
        alertDialog: function (title, message, 
                               cancelButtonTitle, mainButtonTitle, alternateButtonTitle, 
                               statusCallback, buttonCallback) {
            // required params
            var params = {
                "title" : title,
                "cancelButtonTitle" : cancelButtonTitle
            };
            
            // optional params
            if (typeof message == "string") {
                params["message"] = message;
            }
            if (typeof mainButtonTitle == "string") {
                params["mainButtonTitle"] = mainButtonTitle;
            }
            if (typeof alternateButtonTitle == "string") {
                params["alternateButtonTitle"] = alternateButtonTitle;
            }
            
            // optional callbacks
            if (typeof statusCallback == "undefined") { statusCallback = null; }
            
            var additionalCallbacks = [];
            if (typeof buttonCallback != "undefined") {
                additionalCallbacks.push({
                    "param"     : "buttonClickedCallback",
                    "callback"  : buttonCallback,
                    "repeating" : false
                });
            }
            
            this.nativeAPI("dialog", "alert", params, statusCallback, additionalCallbacks);
        },
        
        actionDialog: function (title, 
                                cancelButtonTitle, destructiveButtonTitle, alternateButtonTitles, 
                                statusCallback, buttonCallback) {
            // required params
            var params = {
                "title" : title,
                "cancelButtonTitle" : cancelButtonTitle
            };
            
            // optional params
            if (typeof destructiveButtonTitle == "string") {
                params["destructiveButtonTitle"] = destructiveButtonTitle;
            }
            if (typeof alternateButtonTitles != "undefined") {
                for (var i = 0; i < alternateButtonTitles.length; i++) {
                    params["alternateButtonTitle"+i] = alternateButtonTitles[i];
                }
            }
            
            // optional callbacks
            if (typeof statusCallback == "undefined") { statusCallback = null; }
            
            var additionalCallbacks = [];
            if (typeof buttonCallback != "undefined") {
                additionalCallbacks.push({
                    "param"     : "buttonClickedCallback",
                    "callback"  : buttonCallback,
                    "repeating" : false
                });
            }
            
            this.nativeAPI("dialog", "action", params, statusCallback, additionalCallbacks);
        },

        //
        // Events
        //
        
        addEventListener: function (eventType, eventHandlerCallback, statusCallback) {
            var params = {
                "event" : eventType
            };
            
            this.nativeAPI("listener", "add", params, statusCallback, [{
                "param"     : "eventHandlerCallback",
                "callback"  : eventHandlerCallback,
                "repeating" : true
            }]);
        },
        
        removeEventListener: function (eventType, eventHandlerCallback, statusCallback) {
            var params = {
                "event" : eventType
            };
            
            this.nativeAPI("listener", "remove", params, statusCallback, [{
                "param"     : "eventHandlerCallback",
                "callback"  : eventHandlerCallback,
                "repeating" : true,
                "remove"    : true
            }]);
        },
        
        // ====================================================================
        // Low level implementation
        // ====================================================================
        
        nativeAPI: function (category, type, params, statusCallback, additionalCallbacks) {
            var url = "kgobridge://"+escape(category)+"/"+escape(type);
            var paramStrings = [];
            if (typeof params == "object") {
                for (var key in params) {
                    paramStrings.push(escape(key)+"="+escape(params[key]));
                }
            }
            
            // status callback
            var that = this;
            var callbackId = this.callbackIdCounter++;
            this.callbacks[callbackId] = {
                "callback" : function (error, params) {
                    if (error !== null && "code" in error) {
                        var code = error["code"];
                        var title = "title" in error ? error["title"] : "Unknown Title";
                        var message = "message" in error ? error["message"] : "Unknown message";
                        
                        for (codeKey in that.errorCodes) {
                            if (that.errorCodes[codeKey] == code) {
                                code = codeKey;
                                break;
                            }
                        }
                        that.log("kgoBridge api returned error "+code+" ("+title+" : "+message+")");
                    }
                    if (typeof statusCallback != "undefined" && statusCallback) {
                        statusCallback(error, params);
                    }
                    if (error !== null && typeof additionalCallbacks != "undefined") {
                        // Remove other callbacks on error
                        for (var i = 0; i < additionalCallbacks.length; i++) {
                            if (typeof additionalCallbacks[i]["remove"] == "undefined" || !additionalCallbacks[i]["remove"]) {
                                var callbackId = that.callbackIdForCallback(additionalCallbacks[i]["callback"]);
                                if (callbackId) {
                                    delete that.callbacks[callbackId];
                                }
                            }
                        }
                    }
                },
                "repeating" : false
            };
            paramStrings.push("statusCallback="+callbackId);
            
            // additional callbacks
            if (typeof additionalCallbacks != "undefined") {
                for (var i = 0; i < additionalCallbacks.length; i++) {
                    if (typeof additionalCallbacks[i]["remove"] == "undefined" || !additionalCallbacks[i]["remove"]) {
                        // Adding a callback
                        var callbackId = this.callbackIdCounter++;
                        this.callbacks[callbackId] = {
                            "callback"  : additionalCallbacks[i]["callback"],
                            "repeating" : additionalCallbacks[i]["repeating"]
                        };
                        paramStrings.push(additionalCallbacks[i]["param"]+"="+callbackId);
                        
                    } else {
                        // Removing a callback
                        var callbackId = this.callbackIdForCallback(additionalCallbacks[i]["callback"]);
                        if (callbackId) {
                            paramStrings.push(additionalCallbacks[i]["param"]+"="+callbackId);
                            delete this.callbacks[callbackId];
                        }
                    }
                }
            }
            
            if (paramStrings.length) {
                url += "?"+paramStrings.join("&");
            }
            
            this.loadURL(url);
        },
        
        nativeAPICallback: function (callbackId, error, params) {
            if (callbackId in this.callbacks && this.callbacks[callbackId]) {
                if (typeof params !== "object") {
                    params = {};
                }
                
                // Callbacks frequently perform operations which will not work
                // at the time the native app sends the callback (alert, log, etc)
                // So delay the callback by 100ms to avoid these problems.
                var that = this;
                setTimeout(function () {
                    that.callbacks[callbackId]["callback"].call(that, error, params);
                    
                    if (!that.callbacks[callbackId]["repeating"]) {
                        delete that.callbacks[callbackId];
                    }
                }, 100);
            }
        },
        
        callbackIdForCallback: function (callback) {
            for (var callbackId in this.callbacks) {
                if (this.callbacks[callbackId]["callback"] === callback) {
                    return callbackId;
                }
            }
            return null;
        },
        
        loadURL: function (url) {
            var lcURL = url.toLowerCase();
            if (lcURL.indexOf("http://") == 0 || lcURL.indexOf("https://") == 0) {
                // wrap external URLs so that we don't get confused by other iframes
                url = "kgobridge://external/link?url="+encodeURIComponent(url);
            }
            
            if (this.config.events) {
                var iframe = document.createElement("IFRAME");
                iframe.setAttribute("src", url);
                document.documentElement.appendChild(iframe);
                iframe.parentNode.removeChild(iframe);
                iframe = null;
            } else {
                this.log("kgoBridge would have called "+url);
            }
        },
        
        localizedString: function (key) {
            if (key in this.config.localizedStrings) {
                return this.config.localizedStrings[key];
            } else {
                return key;
            }
        },
        
        ajaxLoad: function () {
            var pageURL = this.config.url+this.config.pagePath+"?"+this.config.ajaxArgs;
            if (this.config.pageArgs.length) {
                pageURL += "&"+this.config.pageArgs;
            }
            
            ajaxContentIntoContainer({
                url: pageURL, 
                container: document.getElementById("container"), 
                timeout: this.config.timeout, 
                error: this.initPageError
            });
        },
        
        bridgeToAjaxLink: function (href) {
            // must be able to pass through non-kgobridge links
            var bridgePrefix = "kgobridge://link/";
            var oldhref= href;
            if (href.indexOf(bridgePrefix) == 0) {
                href = this.config.url+"/"+href.substr(bridgePrefix.length);
                
                var anchor = '';
                var anchorPos = href.indexOf("#");
                if (anchorPos > 0) {
                    anchor = href.substr(anchorPos);
                    href = href.substr(0, anchorPos);
                }
                href = href+(href.indexOf("?") > 0 ? "&" : "?")+this.config.ajaxArgs+anchor;
            }
            return href;
        },
        
        log: function (message) {
            if (this.config.events) {
                this.loadURL("kgobridge://console/log?message="+encodeURIComponent(message));
                
            } else if (typeof console != "undefined" && typeof console.log != "undefined") {
                console.log("KGO_LOG: "+message);
            }
        }
    };
    
    window.kgoBridgeHandler = kgoBridgeHandler;
})(window);
