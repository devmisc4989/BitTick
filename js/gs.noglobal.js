(function() {
    if (typeof (window["_bt"]) == "object")
    {
        _bt.IncrementInstanceNumber();
        return;
    }

    //initial loading
    var globaltime = new Date().getTime();

    _btCi = typeof (_btCi) != 'undefined' ? _btCi : '';
    _btCc = typeof (_btCc) != 'undefined' ? _btCc : '';
    // _btSuccess is deprectaed, we should use _btPage in the future instead
    // but if _btSuccess is set, use it to map it to _btPage values
    _btPage = typeof (_btPage) != 'undefined' ? _btPage : false;
    _btSuccess = typeof (_btSuccess) != 'undefined' ? _btSuccess : false; //_btSuccess = _btSuccess || false; sometimes this doesnt work (fix: using typeof)
    if (_btSuccess) {
        if (_btSuccess == true)
            _btPage = "success";
        if (_btSuccess == false)
            _btPage = "control";
    }

    //removed, because of global ads tracking

    _btSync = typeof (_btSync) != 'undefined' ? _btSync : false; // true == force synchronous call of webservice
    _btTestType = typeof (_btTestType) != 'undefined' ? _btTestType : 1;
    _etLoglv = window.location.href.indexOf('et_poploglv') >= 0;  // true if the querystring et_poploglv is set
	_btNoJquery = typeof(_btNoJquery) != 'undefined' ? _btNoJquery: false;
    _bt = window["_bt"] || new (function() {
        var instance = this,
                $, jQuery,
                host = 'http://blacktri-dev.de',
                sslhost = 'https://blacktri-dev.de',
                //host = 'http://dynamic-content.hhoffice.de.etracker.com',
                //sslhost = 'https://dynamic-content.hhoffice.de.etracker.com',
                //host = 'http://www.blacktri.com',
                //sslhost = 'https://www.blacktri.com',
                //host = 'http://blacktri-qa.bm.mcon.net',
                //sslhost = 'https://blacktri-qa.bm.mcon.net',
                /* @AT1 */ /* @AT2 */
                path = '/index.php/bto/d/?',
                diagnosepath = '/index.php/bto/diagnose/?',
                jquerypath = '/js/jquery-1.8.3.min.js',
				jQueryLoad = !_btNoJquery,
                includeCcList = "",
                maxCookieSize = 4000,
                gsPreloaderTimeout = 0.3, /* seconds */
                preloaderTimeoutTimer = 0,
                domChangeApplied = false,
                domCodeChangeArray = [],
                ajaxCallEventSet = false,
                qrs = '',
                ci = _btCi || '',
                cc = _btCc || '',
                pt = _btSuccess || false,
                btsync = _btSync || false,
                btTestType = _btTestType || 1,
                pg = document.location,
                rfr = document.referrer,
                sdcJsonString, // session data collector for personalization
                etTrackerEvent = '',
                webServiceState = 'loading', // shows to etracker status of webservice call
                loglv = _etLoglv ? parseInt(getParameterByName('et_poploglv')) : (typeof(et_poploglv) !== 'undefined') ? et_poploglv : 0; 

        // track state of webservice (loading / ready or done)
        this.state = function()
        {
            return webServiceState;
        }
        
        // if preloader timeout has been set externally, use it to overwrite
        if (!(typeof et_popto === 'undefined')) {
        	gsPreloaderTimeout = et_popto / 1000;
        	if(gsPreloaderTimeout > 3)
        		gsPreloaderTimeout = 3;
        	if(gsPreloaderTimeout < 0)
        		gsPreloaderTimeout = 0;
        }
        

        // if host and sslhost have been set externally, use them to overwrite
        if (!(typeof _btHost === 'undefined'))
            host = _btHost;
        if (!(typeof _btSslHost === 'undefined'))
            sslhost = _btSslHost;
        var gsJsHost = (("https:" == document.location.protocol) ? (sslhost + path) : (host + path));

        // if jquerypath has been set externally, use it to overwrite
        if (!(typeof _btJquerypath === 'undefined'))
        	jquerypath = _btJquerypath;
        else
        	jquerypath = (("https:" == document.location.protocol) ? (sslhost + jquerypath) : (host + jquerypath));

        // if variable et_referrer is set in querystring, this is a split-test where the referrer hgs been attached
        // so etracker can handle it. Set a JS variable with the value!
        et_rfr = getParameterByName('et_referrer');
        if (et_rfr != '') {
            et_referrer = et_rfr;
        }

        // check for parameters indicating the trace- or diagnose-mode
        trt = getParameterByName('_trt');
        if (trt == '')
            trt = false;
        tracecode = getParameterByName('tracecode');
        if (tracecode == '')
            tracecode = 'NA';

        // check for parameter indicating the no-redirect mode (when in a split test a rediretc has been executed,
        // the called page shall not trigger a redirect again
        noredirect = getParameterByName('_nrd');
        if (noredirect == '')
            noredirect = false;

        // derive OCPT or OCPC from the given code
        if (ci != "")
            var OCPT = true;
        if (cc != "") {
            var OCPC = true;
            ci = "NA";
        }

        // check for exlude-flag in querystring (for debugging)
        var url = window.location.href;
        if (url.indexOf("__exclude__") != -1) {
            Log("exclude argument found, exit", 3);
            setState('done');
            return;
        }

        // if only dedicated clients shall be served, their cc codes can be added to includeCcList 
        // which will be evaluated here
        if (includeCcList != "") {
            if (includeCcList.indexOf(cc) == -1) {
                Log("client code not found in include list, exit", 1);
                setState('done');
                return; // includeList not empty but does not contain the current client code
            }
        }

        // check for variables indicating the preview
        BT_lpid = getParameterByName('BT_lpid');
        if (BT_lpid == '')
            BT_lpid = 'NA';
        if (getParameterByName('_p') == 't') {
            preview = true;
            Log("Preview found, landingpage=" + BT_lpid, 3);
        }
        else
            preview = false;

        // exclude specific clients from optimization
        if (OCPC && (cc == "xxx")) {
            Log("exclude code found, exit", 3);
            setState('done');
            return;
        }

        //dettect if is is (no version, just ie, assumming that users uses at least ie8)
        //var isIE = navigator.appName.indexOf("Microsoft") != -1;
        var isIE = window.navigator.userAgent.match(/(?:(MSIE) |(Trident)\/.+rv:)([\w.]+)/i) || false;
        var isWebKit = new RegExp(/webkit/i).test(navigator.userAgent);
        var isMobile = ((navigator.platform.indexOf("iPhone") != -1) || (navigator.platform.indexOf("iPod") != -1) || (navigator.platform.indexOf("iPad") != -1) || (navigator.userAgent.indexOf("Android") != -1));

        Log('init anonymous, isMobile=' + isMobile + ', isWebKit=' + isWebKit + ', isIE=' + isIE, 3);
		
        this.BTObject = {};
        this.CurrentGSIndex = -1;
        this.$ = this.jQuery = jQuery || window.jQuery;

        var escparam = function(param) {
            return encodeURIComponent(param);
        };

        //====================================
        // Start declaration of functions here
        //====================================

        //entry point of the class, that will call associated methods based on cookie value
        function Init() {

            setState('loading');

            //init etracker
            instance.initEtrackerCall();
			
            //check if ci and cc not defined (emtpy string) and skip web service call
            if (ci == "" && cc == "")
                return;

            //check for jquery
            loadjQuery();

            // init preloader - make page invisibel to avoid flickering before webservice has loaded
            //if((instance.CurrentGSIndex==-1) && (_btPage == "control"))
            if ((instance.CurrentGSIndex == -1))
                initPreloader();

            //set up trim functionality
            if (typeof (''.trim) != 'function')
                String.prototype.trim = function() {
                    return this.replace(/^\s+|\s+$/, '');
                };

            //query string update
            qrs = document.location.search.replace('?', '');

            //for OCPC			
            if (OCPC)
            {
                Log("track OCPC impression", 3);
                instance.trackConversion(false);
                return;
            }
        }

		function loadJS(url, callback){
		
			var script = document.createElement("script")
			
			script.type = "text/javascript";		
			if (script.readyState){  //IE
				script.onreadystatechange = function(){
					if (script.readyState == "loaded" ||
							script.readyState == "complete"){
						script.onreadystatechange = null;
						callback();
					}
				};
			} else {  //Others
				script.onload = function(){
					callback();
				};
			}
		
			script.src = url;
			document.getElementsByTagName('head')[0].appendChild(script);
		}

        // call track conversion but set some variabes before to force the diagnose service to be called
        function diagnose() {
            instance.trackConversion(false);
        }
        
        // deferred impression counting
        this.deferredImpression = function() {
        	instance.trackConversion(true, {ct: 7});
        }

        // smartmessaging follow conversion event
        this.trackSmsFollow = function() {
        	instance.trackConversion(true, {ct: 8});
        }

        /* default to undefined or false, if we are using with user action should be called _bt.trackConversion(true); */
        this.trackConversion = function(conversion, trackOptions/* ct, cl*/)
        {
            var to = trackOptions || {};
            //check extra parameters and set to defaults
            if (typeof (to.ct) == 'undefined')
                to.ct = 4;
            if (typeof (to.cl) == 'undefined')
                to.cl = '';
            if (typeof (conversion) == 'undefined')
                conversion = true;

            Log('trackConversion, sdcJsonString:' + sdcJsonString,3);
            
            Log('trackConversion ', 'cv: ' + conversion, to, 'ct: ' + to.ct, ', cl: ' + to.cl, 3);

            if (trt) { // if we are in doagnose mode, set variables to force load of the diagnose webservice
                gsJsHost = gsJsHost.replace(path, diagnosepath);
                var src = [];
                src.push(gsJsHost);
                Log('gsjshost:' + gsJsHost, 3)
                src.push('tracecode=' + tracecode);
                cookiename = "noWS_" + cc;
                if (instance.readCookie(cookiename) == 'true') {
                    src.push('noWS=true');
                }

            }
            else {
                var src = [];
                src.push(gsJsHost);
            }
            src.push('v=' + instance.readCookie('GS1_v'));
            src.push('ecl=' + instance.readCookie('BT_ecl'));
            if (OCPT)
                src.push('ci=' + ci);
            if (OCPC)
                src.push('cc=' + cc);
            src.push('qrs=' + escparam(qrs));
            if (preview) {
                src.push('_p=t');
                src.push('BT_lpid=' + BT_lpid);
            }
            if (noredirect) {
                src.push('_nrd=true');
            }

            //check for params {ct:6, cl:'et_eC_Wrapper', et_pagename: et_pn, et_target:et_ta, et_tval:et_tv, et_tonr: et_to, et_tsale: et_ts }

            //check for etracker params et_pagename and et_target if ct not 6
            if (to.ct == 6)
            {
                if (typeof (to.et_pagename) != 'undefined' && to.et_pagename != '')
                    src.push('et_pagename=' + escparam(to.et_pagename));
                if (typeof (to.et_target) != 'undefined' && to.et_target != '')
                    src.push('et_target=' + escparam(to.et_target));
                if (typeof (to.et_tval) != 'undefined' && to.et_tval != '')
                    src.push('et_tval=' + escparam(to.et_tval));
                if (typeof (to.et_tonr) != 'undefined' && to.et_tonr != '')
                    src.push('et_tonr=' + escparam(to.et_tonr));
                if (typeof (to.et_tsale) != 'undefined' && to.et_tsale != '')
                    src.push('et_tsale=' + escparam(to.et_tsale));
            }
            else
            {
                if (typeof (et_pagename) != 'undefined' && et_pagename != '')
                    src.push('et_pagename=' + escparam(et_pagename));
                if (typeof (et_target) != 'undefined' && et_target != '')
                    src.push('et_target=' + escparam(et_target));
                if (typeof (et_tval) != 'undefined' && et_tval != '')
                    src.push('et_tval=' + escparam(et_tval));
                if (typeof (et_tonr) != 'undefined' && et_tonr != '')
                    src.push('et_tonr=' + escparam(et_tonr));
                if (typeof (et_tsale) != 'undefined' && et_tsale != '')
                    src.push('et_tsale=' + escparam(et_tsale));
            }

            //if conversion is not set send only rfr, else send cv=1
            if (conversion) {
                src.push('cv=1');
                //conversion type
                if (to.ct != '')
                    src.push('ct=' + escparam(to.ct));
                //if ct = 1 then add conversion link
                if (to.cl != '')
                    src.push('cl=' + escparam(to.cl));
            }
            else {
                src.push('cv=0');
            }

            src.push('sdc=' + escparam(sdcJsonString));
            src.push('pg=' + escparam(pg));
            src.push('pt=' + ((_btPage == 'success') ? 3 : 1));

            Log('Ws call (btsync=' + btsync + '): ' + src.join('&'), 3);
            // use crossdomain call to send data if ie, should work for ff by default (xmlhttp object)
            // ES 05.02.2013: force not to use Ajax since it produces errors
            if (false)
            {
                sendCrossDomainData(src.join('&'));
            }
            else//normal jsonp call for all the others
            {
                if (btsync) {
                    Log("execute sync ws call", 3);
                    document.write('<scr' + 'ipt type="text/javascript" src="' + src.join('&') + '"></scr' + 'ipt>');
                }
                else {
                    Log("execute async ws call", 3);
                    (function(d, src) {
                        var script = d.createElement('script');
                        var s = d.getElementsByTagName('script')[0];
                        script.src = src;
                        s.parentNode.insertBefore(script, s);
                    }(document, src.join('&')));
                    /*
                     var script = document.createElement('script');script.type = 'text/javascript';script.async = 'true';
                     script.src = src.join('&');
                     var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(script, s);
                     */
                }
            }
        }

        this.initOnLoad = function() {
            Log('Init onload events', 2);
            bindOnloadEvent(function() {
                //instance.initEtrackerCall();
                instance.initClickTracking();
            });
        }
        //page click tracking
        this.initClickTracking = function() {
            Log('initClickTracking', 2);
            //mousedown event for links
            var links = document.getElementsByTagName('a');
            for (var i = 0; i < links.length; i++)
            {
                (function(link) {
                    //check for attached event
                    if (link.getAttribute('btattached') == 'true')
                    {
                        Log('found link, btattached found, skipping', 3);
                        return;
                    }

                    //check for onclick onmousedown, onmouseup
                    var eventList = ['onclick', 'onmousedown', 'onmouseup'];
                    var skipEvent = false;
                    for (var i = 0; i < eventList.length; i++)
                    {
                        var attrValue = link.getAttribute(eventList[i]);
                        if (attrValue !== null && typeof (attrValue) == "string")
                        {
                            attrValue = attrValue.toLowerCase();
                            if (attrValue.indexOf('_bt.trackconversion()') != -1)
                            {
                                skipEvent = true;
                                break;
                            }
                        }
                    }
                    if (skipEvent)
                    {
                        Log('found link, manual tracking code found, skipping', 3);
                        return;
                    }

                    //Log('found link, attaching event and setting btattached attribute');
                    //attach attribute, might not work on IE lower then 8
                    link.setAttribute('btattached', 'true');

                    bindEvent(link, 'mousedown', function(evt) {
                        instance.trackConversion(true, {ct: 1, cl: link.href});
                    });

                })(links[i]);
            }
            ;
        }



        //custom etracker call init
        //eventStart
        var originalEtrackerEventFunction = null;
        function overrideETEventStart()
        {
            Log('[ET] Override ET_Event.eventStart', 3);
            originalEtrackerEventFunction = ET_Event.eventStart;
            ET_Event.eventStart = function(category, item, action, tags, value) {

                Log('[ET] bt tracking ET_Event.eventStart: ', category, item, action, tags, value, 3);

                instance.trackConversion(true, {ct: 5, cl: category + ':eventStart'});

                if (typeof (originalEtrackerEventFunction) == 'function')
                    originalEtrackerEventFunction(category, item, action, tags, value);
            }
        }

        //sendEvent
        var originalEtrackerSendEventFunction = null;
        function overrideETSendEvent()
        {
            Log('[ET] Override etCommerce.sendEvent', 3);
            var originalEtrackerSendEventFunction = function() {
            };
            if (typeof (etCommerce.sendEvent) == 'function')
            {
                originalEtrackerSendEventFunction = etCommerce.sendEvent;
                etCommerce.sendEvent = function(eventName) {
                    Log('[ET] bt tracking etCommerce.sendEvent: ', eventName, arguments, 3);
                    instance.trackConversion(true, {ct: 5, cl: eventName + ':sendEvent'});
                    if (typeof (originalEtrackerSendEventFunction) == 'function')
                        originalEtrackerSendEventFunction.apply(this, arguments);
                }
            }

        }

        //attachEvent
        var originalEtrackerAttachEventFunction = null;
        function overrideETAttachEvent()
        {
            Log('[ET] Override etCommerce.attachEvent', 3);
            var originalEtrackerAttachEventFunction = function() {
            };
            if (typeof (etCommerce.attachEvent) == 'function')
            {
                originalEtrackerAttachEventFunction = etCommerce.attachEvent;
                etCommerce.attachEvent = function(eventHandlerDefinitions) {
                    Log('[ET] etCommerce.attachEvent: ', arguments, 3);

                    var passedArguments = arguments;
                    //internal tracking function
                    var localTrackingFunction = function(evt) {
                        Log('[ET] bt tracking function arguments: ', passedArguments, 3);
                        Log('[ET] bt tracking function event argument: ', evt.type, 3);
                        instance.trackConversion(true, {ct: 5, cl: passedArguments[1] + ':' + evt.type + ':attachEvent'});
                    }

                    for (var eventType in eventHandlerDefinitions) {
                        if (eventHandlerDefinitions.hasOwnProperty(eventType)) {
                            var objectIdArray = eventHandlerDefinitions[eventType];
                            for (var objectId in objectIdArray) {
                                if (objectIdArray.hasOwnProperty(objectId)) {
                                    var domElement = document.getElementById(objectIdArray[objectId]);
                                    if (domElement)
                                        bindEvent(domElement, eventType, localTrackingFunction);
                                }
                            }
                        }
                    }

                    if (typeof (originalEtrackerAttachEventFunction) == 'function')
                        originalEtrackerAttachEventFunction.apply(this, arguments);
                }
            }

        }

        //doPreparedEvents
        function overrideETDoPreparedEvents()
        {
            //return;
            Log('[ET] Override etCommerce.doPreparedEvents', 3);

            //clone array because we dont need to change it, else would be chyanged by our code
            function deepCopy(obj) {
                if (Object.prototype.toString.call(obj) === '[object Array]') {
                    var out = [], i = 0, len = obj.length;
                    for (; i < len; i++) {
                        out[i] = arguments.callee(obj[i]);
                    }
                    return out;
                }
                if (typeof obj === 'object') {
                    var out = {}, i;
                    for (i in obj) {
                        out[i] = arguments.callee(obj[i]);
                    }
                    return out;
                }
                return obj;
            }

            if (typeof etCommercePrepareEvents === "undefined") {
                return;
            }

            var localCommercePrepareEvents = deepCopy(etCommercePrepareEvents || []);

            Log('[ET] etCommerce.doPreparedEvents: ', localCommercePrepareEvents, 3);
            for (var key in (localCommercePrepareEvents || [])) {
                if ((localCommercePrepareEvents || []).hasOwnProperty(key)) {
                    if (typeof (localCommercePrepareEvents[key]) == 'object') {
                        var arrayValues = localCommercePrepareEvents[key],
                                functionName = arrayValues.shift();

                        if (functionName == 'sendEvent') {
                            Log('[ET] bt tracking sending async sendEvent: ', arrayValues, 3);
                            instance.trackConversion(true, {ct: 5, cl: arrayValues[0] + ':sendEvent:prepareEvents'});
                        }
                        else if (functionName == 'attachEvent') {
                            Log('[ET] bt tracking async attachEvent: ', arrayValues, 3);
                            var attachedDefinitions = arrayValues[0];


                            var eventHandlerDefinitions = attachedDefinitions;
                            var passedArguments = arrayValues;

                            //internal tracking function
                            var localTrackingFunction = function(evt) {
                                Log('[ET] bt tracking sending async arguments: ', passedArguments, 3);
                                instance.trackConversion(true, {ct: 5, cl: evt.type + ':' + passedArguments[1] + ':attachEvent:prepareEvents'});
                            }

                            for (var eventType in eventHandlerDefinitions) {
                                if (eventHandlerDefinitions.hasOwnProperty(eventType)) {
                                    var objectIdArray = eventHandlerDefinitions[eventType];
                                    for (var objectId in objectIdArray) {
                                        if (objectIdArray.hasOwnProperty(objectId)) {
                                            var domElement = document.getElementById(objectIdArray[objectId]);
                                            if (domElement)
                                                bindEvent(domElement, eventType, localTrackingFunction);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        function overrideETWrappers()
        {
            Log('[ET] Override et_eC_Wrapper', 3);
            var originalEtrackerEt_eC_WrapperFunction = null;
            if (typeof (et_eC_Wrapper) == 'function')
            {
                originalEtrackerEt_eC_WrapperFunction = et_eC_Wrapper;
                window.et_eC_Wrapper = function(param, et_pn, et_ar, et_il, et_ur, et_ta, et_tv, et_to, et_ts, et_cu, et_ba, et_lp, et_tr, et_tg, et_sb, et_ref)
                {
                    originalEtrackerEt_eC_WrapperFunction(param, et_pn, et_ar, et_il, et_ur, et_ta, et_tv, et_to, et_ts, et_cu, et_ba, et_lp, et_tr, et_tg, et_sb, et_ref);
                    Log('[ET] bt tracking sending async arguments for et_eC_Wrapper call: ', et_pn, et_ta, et_tv, et_to, et_ts, 3);
                    instance.trackConversion(true, {ct: 6, cl: 'et_eC_Wrapper', et_pagename: et_pn, et_target: et_ta, et_tval: et_tv, et_tonr: et_to, et_tsale: et_ts});
                }
            }

            Log('[ET] Override et_cc_wrapper', 3);
            var originalEtrackerEt_cc_wrapperFunction = null;
            if (typeof (et_cc_wrapper) == 'function')
            {
                originalEtrackerEt_cc_wrapperFunction = et_cc_wrapper;
                window.et_cc_wrapper = function(cc_secureKey, param)
                {
                    originalEtrackerEt_cc_wrapperFunction(cc_secureKey, param);
                    Log('[ET] bt tracking sending async arguments for et_cc_wrapper call: ', cc_secureKey, param, 3);
                    if(typeof param == 'undefined')
                    	pgname = "";
                    else 
                    	pgname = param.cc_pagename;
                    instance.trackConversion(true, {ct: 6, cl: 'et_cc_wrapper', et_pagename: pgname});
                }
            }

        }

        var etrackerLoading = false;
        this.initEtrackerCall = function() {
            if (etrackerLoading)
            {
                Log('initEtrackerCall is loading, skipping', 3);
                return;
            }

            Log('initEtrackerCall', 3);
            etrackerLoading = true;

            //check to see if et_params exists and call it
            if (typeof (et_params) == 'function')
            {
                Log('et_params function found, calling!', 3)
                et_params.call();
            }

            //add onload event to async call
            bindOnloadEvent(overrideETDoPreparedEvents);

            var etrackerFunctionsLoaded = {}
            var etrackerInitCountdown = 4;
            var waitTimeout = 4000;
            var startTime = new Date();
            function checkEtrackerLoaded() {

                var currentTime = new Date();
                //check if timeout reached and canceling
                if (currentTime.getTime() - startTime.getTime() > waitTimeout || etrackerInitCountdown == 0)
                    return;

                if (typeof (ET_Event) != 'undefined' && typeof (ET_Event.eventStart) == 'function' && !(etrackerFunctionsLoaded['ET_Event.eventStart'] || false))
                {
                    etrackerInitCountdown--;
                    etrackerFunctionsLoaded['ET_Event.eventStart'] = true;
                    overrideETEventStart();
                }

                if (typeof (etCommerce) != 'undefined' && typeof (etCommerce.sendEvent) == 'function' && !(etrackerFunctionsLoaded['etCommerce.sendEvent'] || false))
                {
                    etrackerInitCountdown--;
                    etrackerFunctionsLoaded['etCommerce.sendEvent'] = true;
                    overrideETSendEvent();
                }

                if (typeof (etCommerce) != 'undefined' && typeof (etCommerce.attachEvent) == 'function' && !(etrackerFunctionsLoaded['etCommerce.attachEvent'] || false))
                {
                    etrackerInitCountdown--;
                    etrackerFunctionsLoaded['etCommerce.attachEvent'] = true;
                    overrideETAttachEvent();
                }

                if (typeof (et_eC_Wrapper) == 'function' && typeof (et_cc_wrapper) == 'function' && !(etrackerFunctionsLoaded['et_eC_Wrapper.et_cc_wrapper'] || false))
                {
                    etrackerInitCountdown--;
                    etrackerFunctionsLoaded['et_eC_Wrapper.et_cc_wrapper'] = true;
                    //for async call
                    overrideETWrappers();
                }
                else
                    setTimeout(checkEtrackerLoaded, 0);
            }
            ;
            checkEtrackerLoaded();
        }
        
        //this function triggers etracker logging
        this.refreshOnAjaxCall = function() {
            if (instance.ajaxCallEventSet)
            {
                Log('Ajax refresh call enabled, skipping', 3);
                return;
            }
            instance.ajaxCallEventSet = true;

            Log('Enabling ajax refresh call', 3);
            //copy code to local variable
            var localDomCodeChangeArray = instance.domCodeChangeArray;
            (window.$ || window.jQuery)(document).ajaxSuccess(function(evt, xhr, opt) {
                Log('Ajax call intercepted, applying changes', 3)
                setTimeout(function() {
                    for (domPath in localDomCodeChangeArray)
                    {
                        //call change
                        internalApplyDomChange(domPath, localDomCodeChangeArray[domPath]);
                    }
                }, 50);
            });
        }
        //this function enables ajax dom change apply

        //this function applies changes stored in the object to current page
        this.applyCollectionChanges = function(DomCodeChangeArray)
        {
			//skip if no jquery is loaded and does not exists on page
			if(!jQueryLoad && !isjQueryLoaded())
			{
	            Log("applyCollectionChanges -  jquery load is disabled and no jquery found on page, skipping", 3);
				return;
			}
			if( !isjQueryLoaded() )
			{
				setTimeout( function(){
					instance.applyCollectionChanges(DomCodeChangeArray);
				},0);
				return;
			}

            Log("applyCollectionChanges delayed call - DomCodeChangeArray: ", DomCodeChangeArray, 3);
            if (typeof (DomCodeChangeArray) == "undefined")
                return;

            //store dom code change array
            instance.domCodeChangeArray = DomCodeChangeArray;

            var localCollectionChangeApplied = {};
            var elementLoaded = false;
            var allElementsLoaded = true;
            //apply dom code changes only if it's initialized
            if (typeof (DomCodeChangeArray) == "undefined")
                return;
            var waitTimeout = 2000;
            var startTime = new Date();
            function checkDomLoaded() {

                var currentTime = new Date();
                var isTimeout = (currentTime.getTime() - startTime.getTime() > waitTimeout);
                if (!isTimeout)
                {
                    allElementsLoaded = true;
                    var jsNotEmpty = true;
                    for (domPath in DomCodeChangeArray)
                    {
                        if (typeof (localCollectionChangeApplied[domPath]) == 'undefined')
                        {
                            allElementsLoaded = false;
                            elementLoaded = instance.jQuery(domPath).size() > 0 || domPath == '[CSS]' || domPath == '[JS]' || domPath == '[SMS]';
                            if (elementLoaded)
                            {
                                //call change
                                internalApplyDomChange(domPath, DomCodeChangeArray[domPath]);
                                //set local flag
                                localCollectionChangeApplied[domPath] = true;
                            }
                        }
                    }

                    //if not all loaded call again
                    if (!allElementsLoaded)
                    {
                        //call next check
                        setTimeout(checkDomLoaded, 10);
                    }
                    else
                    {
                        Log('All dom changes applied! But JS might not be ready yet!', 1);
                        var jsEnabled = (typeof (DomCodeChangeArray['[JS]']) == 'string' && DomCodeChangeArray['[JS]'].length > 0 ) || 
                        	(typeof (DomCodeChangeArray['[SMS]']) == 'string' && DomCodeChangeArray['[SMS]'].length > 0);
                        Log('jsEnabled: ', jsEnabled, 2);
                        if (!jsEnabled)
                        {
                            Log('JS is empty, removing preloader!', 1);
                            setState('done');

                            //close preloader
                            if (instance.CurrentGSIndex == -1)
                                closePreloader(false);
                        }
                    }
                }
                else
                {
                    Log('Timeout reached!', 1);
                    //cleanup
                    localCollectionChangeApplied = null;

                    setState('done');

                    //close preloader
                    if (instance.CurrentGSIndex == -1)
                        closePreloader(false);
                }
            }
            ;
            checkDomLoaded();
        }
        //this function applies changes stored in the object to current page
        this.applyDelayedCollectionChanges = function(DomCodeChangeArray)
        {
            Log("applyDelayedCollectionChanges - DomCodeChangeArray: ", DomCodeChangeArray, 3);

            //apply dom code changes only if it's initialized
            if (typeof (DomCodeChangeArray) != "undefined")
            {
                for (domPath in DomCodeChangeArray)
                {
                    internalApplyDomChange(domPath, DomCodeChangeArray[domPath])
                }
            }
            //close preloader
            if (instance.CurrentGSIndex == -1)
                closePreloader(false);
        }
        function internalApplyDomChange(domPath, changeValue)
        {
            Log("internalApplyDomChange: ", domPath, 3);
            if(typeof changeValue != 'undefined')
            	if(changeValue != null)
            		changeValue = changeValue.trim();
            if (domPath == '[CSS]')
            {
                //create new
                instance.jQuery('<style type="text/css">' + changeValue + '</style>').appendTo('head');
            }
            else if (domPath == '[JS]' || domPath == '[SMS]')
            {
                instance.jQuery(document).ready(function() {
                    //create new
                    instance.jQuery("<script type=\"text/javascript\">(function($){\n" + changeValue + "\n})(_bt.jQuery)<\/script>").appendTo("head");

                    setState('done');
                    //close preloader
                    if (instance.CurrentGSIndex == -1)
                        closePreloader(false);
                });
            }
            else //in case we dont have inline css or js
            {
                //google detection
                if (changeValue.indexOf('<script type="text/javascript">') == 0 && changeValue.indexOf('google_ad_slot') > 0 && changeValue.indexOf('google_ad_client') > 0)
                {
                    //skip this for now
                    Log("DomPath: ", domPath, "google ad code, skipping", 3);
                }
                else
                {
                    Log("DomPath: ", domPath, "\nValue: ", changeValue, 3);
                    var domElement = instance.jQuery(domPath);
                    var changedElements = instance.jQuery(changeValue);
                    if (domElement.size() > 0)
                    {
                        Log('now applying changes in HTML', 3);
                        if (changeValue == "")
                        {
                            Log('hiding element ', domElement, 3);
                            domElement.hide();
                        }
                        else
                        {
                            Log('apply change', 3);
                            if (changedElements.size() == 1)
                                domElement.replaceWith(changeValue);
                            else if (changedElements.size() > 1)
                                domElement.html(changeValue);
                        }
                    }//end if
                }//end if changeValue.indexOf
            }//end if dompath		
        }

        //function to calculate how many gs.js file includes
        this.IncrementInstanceNumber = function() {
            instance.CurrentGSIndex++;
        }
        //this function redirects user
        this.redirect = function(url)
        {
            // attach a parameter to the URL in order to prevent endless redirect loops
            if (url.indexOf('&') >= 0)
                url += '&_nrd=true';
            else
                url += '?_nrd=true';
            window.location = url;
        }
        this.replaceHtml = function(html)
        {
            function insertAfter(newElement, targetElement) {
                var parent = targetElement.parentNode;
                if (parent.lastChild == targetElement) {
                    parent.appendChild(newElement);
                } else {
                    parent.insertBefore(newElement, targetElement.nextSibling);
                }
            }
        }
		
        // add a collection code to a cookie which excludes the user for the test. This is used to target only a limited percentage
        // of users to a test
        this.excludeFromTest = function(collectionCode) {
            var excludeList = decodeURIComponent(instance.readCookie('BT_ecl'));
            if (excludeList == 'NA') {
                excludeList = collectionCode + ':';
            }
            else {
                excludeList = excludeList.replace(collectionCode + ':', '');
                excludeList += collectionCode + ':';
            }
            instance.setCookie('BT_ecl', excludeList, 30);
        }

        // helper function to set a cookie
        // days: number of days for cookie to be valid
        // days = 0 or empty --> a session cookie will be set
        // days = -1 --> cookie will be deleted
        this.setCookie = function(cookieName, cookieValue, days)
        {
            var today = new Date();
            var expire = new Date();
            if (days == null)
                days = 0;
            expire.setTime(today.getTime() + 3600000 * 24 * days);
            if (days > 0)
                var cookieString = cookieName + "=" + escparam(cookieValue) + "; path=/; expires=" + expire.toUTCString();
            else
                var cookieString = cookieName + "=" + escparam(cookieValue) + "; path=/";
            document.cookie = cookieString;
        }
        this.readCookie = function(cookieName)
        {
            var nameEQ = cookieName + "=";
            var ca = document.cookie.split(';');
            for (var i = 0; i < ca.length; i++)
            {
                var c = ca[i];
                while (c.charAt(0) == ' ')
                    c = c.substring(1, c.length);
                if (c.indexOf(nameEQ) == 0) {
                    cv = c.substring(nameEQ.length, c.length);
                    return (decodeURIComponent(cv));              
                }
            }
            return 'NA';
        }

        // close white layer on content (used to reduce flicker)
        this.removePreloader = function()
        {
        	closePreloader(false);
        }

        this.setReady = function(state)
        {
            var s = state || 'ready';
            setState(s);
        }

        // to be called from webservice to indicate when no active client is present

        this.setNoWS = function()
        {
            cookiename = "noWS_" + cc; // deactivate for this client code
            instance.setCookie(cookiename, 'true', 0); // store in session cookie
            Log('setNoWs: Disable optimisation for this session', 1);
        }

        //internal functions (private functions)
        function setState(state)
        {
            Log('Webservice state: ', state, 2);
            webServiceState = state;
        }

        this.mylog = function(message)
        {
            Log(message, 2);
        }
        
        //log to console, first determines the log level stablished in the url query string, if 0 then no message is logged
        function Log() {
            if (window.console) {
                var args = [].slice.call(arguments);
                var lv = args[args.length - 1];         // The log level must be always the last argument
                args.splice(-1, 1);                           // The message itself is the concat of all the arguments except the lastone
                
                // only continues if the stablished log level is greater that 0 or if the level passed is <= to the loglv
                if (loglv === 0 || lv > loglv)
                    return;

                var logt;
                switch (lv) {
                    case 1:
                        logt = ' (Warning!) ';
                        break;
                    case 2:
                        logt = ' (Info) ';
                        break;
                    case 3:
                        logt = ' (Debug) ';
                        break;
                    default:
                        return;
                        break;
                }

                window.console.log(new Date().getTime() - globaltime + 'ms - ' + logt + args.join(' '));
            }
        }

        // Print revision number
        this.showRevision = function () {
            var revision = "$Rev: 2165 $";
            alert(revision);
        }

        // read a parameter from querystring
        function getParameterByName(name) {
            name = name.replace(/[\[]/, "\\\[").replace(/[\]]/, "\\\]");
            var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
                    results = regex.exec(location.search);
            return results == null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
        }

		//load sync jquery
		function loadjQuery()
		{
			if(_btPage == "success")
				return;
			Log('jQuery page initial version: ' + ( typeof(window.jQuery) != 'undefined' && window.jQuery && window.jQuery.fn?window.jQuery.fn.jquery:'no jquery loaded'),1 );
			
			if(jQueryLoad)
			{
				Log('Loading jquery async...',3);
				loadJS(jquerypath, function(){
					window.BTJQuery = window.jQuery.noConflict(true);
					Log('jQuery load complete, version: ' + window.BTJQuery.fn.jquery,3);
					instance.$ = instance.jQuery = window.BTJQuery;
					Log('jQuery page restored version: ' + ( window.jQuery && window.jQuery.fn?window.jQuery.fn.jquery:'no jquery loaded'),3 );
				});
			}
			else
				instance.$ = instance.jQuery = window.jQuery;
		}
		function isjQueryLoaded(){
			if(typeof (window.jQuery) != 'undefined' && !jQueryLoad)
				instance.$ = instance.jQuery = window.jQuery;
			return typeof(instance.$) != 'undefined';
		}
        
        //preloader function
		function initPreloader()
		{
			Log("initPreloader",1);
			//set-up timer function
			preloaderTimeoutTimer = setTimeout( function(){closePreloader(true);}, gsPreloaderTimeout * 1000);
			//create new
			createInlineStyle('body, html { visibility:hidden !important;background-image: none !important; background-color:#FFF !important;}','BTPreloaderCSS');
		}
		function closePreloader(timeout)
		{
			var tmo = timeout || false;
			var domsize = document.getElementsByTagName('body').length;
			if(!tmo && domsize == 0) {
				return;
			}
			Log("closePreloader" + (tmo?" on timeout":" after gs applied changes"),1);
			if(preloaderTimeoutTimer)
				clearTimeout(preloaderTimeoutTimer);
			
			//if(tmo)
			//setState('done');
			var o = document.getElementById('BTPreloaderCSS');
			if(o && typeof(o.parentElement) !== 'undefined') o.parentElement.removeChild(o);
			else if(o && typeof(o.parentNode) !== 'undefined') o.parentNode.removeChild(o);
		}
		function createInlineStyle(css, id){
			var head = document.head || document.getElementsByTagName('head')[0];
			var style = document.createElement('style');
			
			style.id = id;			
			style.type = 'text/css';
			if (style.styleSheet){
			  style.styleSheet.cssText = css;
			} else {
			  style.appendChild(document.createTextNode(css));
			}			
			head.appendChild(style);
		}		
        //ajax call helpers
        //send data to server avoiding crossdomain restrictions
        function sendCrossDomainData(url)
        {
            var ajax = createCrossDomainAjaxObject();
            if (ajax)
            {
                if (isIE)
                {
                    ajax.open("GET", url, false);
                    ajax.onload = function() {
                        alert(ajax.responseText)
                    };
                    ajax.send(null);
                }
                else
                {
                    ajax.open("GET", url, false);
                    ajax.onreadystatechange = function() {
                    };
                    ajax.send(null);
                }
            }
        }
        //this function checks to see if we have XDomainRequest object valid for IE crossdomain call, FF and others should implement in the XMLHttp object.
        function createCrossDomainAjaxObject()
        {
            var ajax = false;
            try {
                if (isIE)
                    ajax = new XDomainRequest();
                else
                    ajax = new XMLHttpRequest();
            }
            catch (trymicrosoft) {
                try {
                    ajax = new XDomainRequest();
                }
                catch (othermicrosoft)
                {
                    try {
                        ajax = new ActiveXObject("Msxml2.XMLHTTP");
                    }
                    catch (othermicrosoft1) {
                        try {
                            ajax = new ActiveXObject("Microsoft.XMLHTTP");
                        }
                        catch (failed) {
                            ajax = false;
                        }
                    }
                }
            }
            if (!ajax)
                alert("Error initializing XMLHttpRequest!");
            return ajax;
        }
        //event helpers
        function bindEvent(elm, evt, fn)
        {
            if (typeof (window.attachEvent) != 'undefined') {
                // this works for IE
                elm.attachEvent('on' + evt, fn);
            } else if (typeof (window.addEventListener) != 'undefined') {
                // this works for firefox
                elm.addEventListener(evt, fn, false);
            }
        }
        //event helpers
        function unbindEvent(elm, evt, fn)
        {
            if (typeof (window.detachEvent) != 'undefined') {
                // this works for IE
                elm.detachEvent('on' + evt, fn);
            } else if (typeof (window.removeEventListener) != 'undefined') {
                // this works for firefox
                elm.removeEventListener(evt, fn, false);
            }
        }
        function bindOnloadEvent(func) {
            if (typeof (window.addEventListener) != 'undefined') {
                window.addEventListener('load', func, false);
            } else if (typeof (document.addEventListener) != 'undefined') {
                document.addEventListener('load', func, false);
            } else if (typeof (window.attachEvent) != 'undefined') {
                window.attachEvent('onload', func);
            } else {
                if (typeof (window.onload) == 'function') {
                    var oldonload = onload;
                    window.onload = function() {
                        oldonload();
                        func();
                    };
                } else {
                    window.onload = func;
                }
            }
        }
        //onload function
        function ApplyChangesAfterOnLoad()
        {
            instance.applyCollectionChanges(instance.BTObject.BTDomCodeChangeArray);
        }

        //====================================
        // End declaration of functions here
        //====================================

        // function to encode and decode strings to base64
        var Base64={_keyStr:"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",encode:function(e){var t="";var n,r,i,s,o,u,a;var f=0;e=Base64._utf8_encode(e);while(f<e.length){n=e.charCodeAt(f++);r=e.charCodeAt(f++);i=e.charCodeAt(f++);s=n>>2;o=(n&3)<<4|r>>4;u=(r&15)<<2|i>>6;a=i&63;if(isNaN(r)){u=a=64}else if(isNaN(i)){a=64}t=t+this._keyStr.charAt(s)+this._keyStr.charAt(o)+this._keyStr.charAt(u)+this._keyStr.charAt(a)}return t},decode:function(e){var t="";var n,r,i;var s,o,u,a;var f=0;e=e.replace(/[^A-Za-z0-9\+\/\=]/g,"");while(f<e.length){s=this._keyStr.indexOf(e.charAt(f++));o=this._keyStr.indexOf(e.charAt(f++));u=this._keyStr.indexOf(e.charAt(f++));a=this._keyStr.indexOf(e.charAt(f++));n=s<<2|o>>4;r=(o&15)<<4|u>>2;i=(u&3)<<6|a;t=t+String.fromCharCode(n);if(u!=64){t=t+String.fromCharCode(r)}if(a!=64){t=t+String.fromCharCode(i)}}t=Base64._utf8_decode(t);return t},_utf8_encode:function(e){e=e.replace(/\r\n/g,"\n");var t="";for(var n=0;n<e.length;n++){var r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r)}else if(r>127&&r<2048){t+=String.fromCharCode(r>>6|192);t+=String.fromCharCode(r&63|128)}else{t+=String.fromCharCode(r>>12|224);t+=String.fromCharCode(r>>6&63|128);t+=String.fromCharCode(r&63|128)}}return t},_utf8_decode:function(e){var t="";var n=0;var r=c1=c2=0;while(n<e.length){r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r);n++}else if(r>191&&r<224){c2=e.charCodeAt(n+1);t+=String.fromCharCode((r&31)<<6|c2&63);n+=2}else{c2=e.charCodeAt(n+1);c3=e.charCodeAt(n+2);t+=String.fromCharCode((r&15)<<12|(c2&63)<<6|c3&63);n+=3}}return t}}

        // check if browser accepts cookies and break if not
        instance.setCookie('BT_ctst', '101', 0);
        if (instance.readCookie('BT_ctst') != '101') {
            // browser does not accept cookies, no further optimisation
            Log("no cookies, exit", 1);
            setState('done');
            return;
        }
        instance.setCookie('BT_ctst', '', -1); // delete the cookie to tidy up...
        
        // read the session data collector cookie and set one if not available
        Log('read cookie:' + instance.readCookie('BT_sdc'),3);
        sdccookie = Base64.decode(instance.readCookie('BT_sdc'));
        Log('decoded cookie:' + sdccookie,3);
        if(sdccookie != 'NA') {
        	try {
        		sdcJsonString = sdccookie.replace(/\+/g, ' ');
        		Log('sdccookie:' + sdcJsonString,3);
        		sdc = JSON.parse(sdcJsonString);
        	}
        	catch (e) {
        		Log('sdc cookie not empty but invalid:' + sdccookie,3);
                sdc = 'NA';
        	}
        }
        if((typeof sdc == 'undefined') || (sdc == 'NA')) { // set cookie again
            Log('create new sdc object and store in cookie',3);
            var sdc = new Object();
            sdc.et_coid = instance.readCookie('_et_coid'); // etracker visitor-id
            sdc.rfr = rfr; // referrer
            sdc.time = new Date().getTime(); // session start time
            sdc.pi = 0; // number of page impressions
            sdcJsonString = JSON.stringify(sdc);  // save absolute time in cookie
            Log('cookie value:' + sdcJsonString,3);
            Log('write cookie:' + Base64.encode(sdcJsonString),3);
            instance.setCookie('BT_sdc',Base64.encode(sdcJsonString), 0);          
        }
        // increment page impression count and store in cookie. plus store the et_coid
        // value every time since it might not be present in the first request but the second.
        if(typeof sdc.pi == 'undefined')
            sdc.pi = 0;
        sdc.pi++;
        sdc.et_coid = instance.readCookie('_et_coid');
        sdcJsonString = JSON.stringify(sdc);
        instance.setCookie('BT_sdc',Base64.encode(sdcJsonString), 0);
        // use relative time (session time) to work with
        sdc.time = (new Date().getTime()) - sdc.time;
        sdcJsonString = JSON.stringify(sdc);  
        Log('sdcJsonString:' + sdcJsonString,3);

        // check if diagnosemode is active. If so, call a special webservice and exit
        if (trt) {
            diagnose();
        }
        else {
            // normal handling (non-diagnose-mode)
            // check for deactivation cookie (set from webservice in case of inactive client)
            cookiename = "noWS_" + cc;
            if (instance.readCookie(cookiename) == 'true') {
                // browser does not accept cookies, no further optimisation
                if (!preview) {
                    Log("no active client and no preview, exit", 1);
                    setState('done');
                    return;
                }
            }

            //initialize
            Init();

            //attach onload events
            instance.initOnLoad();
        }
    });
})();