(function() {
    if (typeof (window["_bt"]) == "object")
    {
        _bt.IncrementInstanceNumber();
        return;
    }

    //initial loading
    var globaltime = new Date().getTime();

    // library version
    var version = 3421;

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
                trackingpath = '/index.php/track/me/',
                jquerypath = '/js/jquery-1.8.3.min.js',
				jQueryLoad = !_btNoJquery,
                includeCcList = "",
                maxCookieSize = 4000,
                gsPreloaderTimeout = 0.3, /* seconds */
                preloaderTimeoutTimer = 0,
                domChangeApplied = false,
                domCodeChangeArray = [],
                conditionalActivationData = 'activation',
                ajaxCallEventSet = false,
                qrs = '',
                ci = _btCi || '',
                cc = _btCc || '',
                pt = _btSuccess || false,
                btsync = _btSync || false,
                btTestType = _btTestType || 1,
                pg = document.location,
                rfr = document.referrer,
                etTrackerEvent = '',
                webServiceState = 'loading', // shows to etracker status of webservice call
                loglv = _etLoglv ? parseInt(getParameterByName('et_poploglv')) : (typeof(et_poploglv) !== 'undefined') ? et_poploglv : 0;

        // track state of webservice (loading / ready or done)
        this.state = function()
        {
            return webServiceState;
        }

        // create a data layer array
        this.data = {};
        this.data.webserviceResponded = 'initialized';
        this.data.projectCount = 0;

        Log("Check for Akamai CDN", 3);

        // if preloader timeout has been set externally, use it to overwrite
        if (!(typeof _btTo === 'undefined')) {
            et_popto = _btTo;
        }
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
        else {
            if(!(jquerypath.substring(0,2) == '//'))
                jquerypath = (("https:" == document.location.protocol) ? (sslhost + jquerypath) : (host + jquerypath));
        }

        // set tracking-URL
        var trackingUrl = (("https:" == document.location.protocol) ? (sslhost + trackingpath) : (host + trackingpath));

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

        // return a version of the library
        this.getVersion = function() {
            return version;
        }
        
        // deferred impression counting
        this.deferredImpression = function() {
        	instance.trackConversion(true, {ct: 7});
        }

        // smartmessaging follow conversion event
        this.trackSmsFollow = function() {
        	instance.trackConversion(true, {ct: 8});
        }

        // track a custom javascript goal
        this.trackCustomGoal = function (eventname) {
            instance.trackConversion(true, {ct: 9, cl: eventname});
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
            src.push('_rnd=' + Math.random());
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
            Log('sdc=' + SDC.toJSON(),3);
            src.push('sdc=' + escparam(SDC.toJSON()));
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
                if(!preview)
                    instance.initClickTracking();
            });
        };

        // This is called from the injected library (in the client's page) to track the corresponding event (time on page, teasertests...)
        this.trackEvents = function (events) {
            var params = {
                'cc' : cc,
                'v' : instance.readCookie('GS1_v'),
                'ev' : events
            }
            paramsJsonString = JSON.stringify(params);
            Log(paramsJsonString,3);
            (function(d, src) {
                var script = d.createElement('script');
                var s = d.getElementsByTagName('script')[0];
                script.src = src;
                s.parentNode.insertBefore(script, s);
            }(document, trackingUrl + escparam(paramsJsonString)));
            return true;
        };

        // for teasertests: when a headline is clicked, store information on the ID so it will be 
        // transferred with the next trackConversion request
        this.bufferTeasertestExclusionId = function (collectionid, pageId) {
            Log('set ttest_lpid=' + pageId, 3);
            SDC.setParam('ttest_lpid',pageId);
            // add the ID of the clicked page + the current PI value to SDC so the
            // page impression lift goal can be tracked
            ttest = SDC.getParam('ttest');
            if(typeof ttest.history[pageId] === "undefined") {
                pi = SDC.getParam('pi') + 1;
                historyElement = {'pi':pi};
                ttest.history[pageId] = historyElement;
                ttest.cid = collectionid;
                SDC.setParam('ttest',ttest);                
            }
        };
        this.discardTeasertestExclusionId = function () {
            Log('discard ttest_lpid', 3);
            SDC.setParam('ttest_lpid',0);
        };

        // helper function for injecting teaser test variants using text-base selectors
        
        /**
         * Creates a new style tag to append to the "head" of the document not to display the new elements (div / span) with class="_bt_tt"
         * @returns void
         */
        this.tt_createStyle = function () {
            var css = '._bt_tt { display:none; }';
            var head = document.head || document.getElementsByTagName('head')[0];
            var style = document.createElement('style');
            style.type = 'text/css';

            if (style.styleSheet) {
                style.styleSheet.cssText = css;
            } else {
                style.appendChild(document.createTextNode(css));
            }

            head.appendChild(style);
        };

        /**
         * @param {string} oText The original text (may include HTML tags)
         * @returns the text without HTML tags (TRIMMED -- without white spaces)
         */
        this.tt_getTextOnly = function (oText) {
            var tmp = document.createElement("DIV");
            tmp.innerHTML = oText;
            var ret = tmp.textContent || tmp.innerText || "";
            return ret.replace(/\s/g, '');
        };

        /**
         * After finding the element that contains the given text to be replaced, we need to find the closest
         * element which has an "href" attribute (the actual link) Could be the same element, a close parent
         * or a far ancestor, this is to track impression/conversions correctly.
         *  - The <span> tag is added as a child of the given element eitherway
         *  - If there is a link ('a'), we wrap it up in a <div> tag with the corresponding CLASS and ID
         *  - ELSE the ID and the CLASS are added as attributes of the <span> and NO wrapping <div> is created
         * @param {dom element} $elem The element that contains the "control" text
         * @param {int} lpcid The landingpage_collectionid
         * @param {int} lpd The landing_pageid
         * @param {string} text the text/html with the headline
         */
        this.tt_wrapElement = function ($elem, lpcid, lpd, text) {
            var $link = $elem.closest('a').length > 0 ? $elem.closest('a') : $elem;
            var uniqueAttr = '_bt_tt_' + Math.random().toString(36).substring(2);

            $link.addClass('_bt_tt  -bt-' + lpcid + '-' + lpd).attr('tt_unique_attr', uniqueAttr);
            if (text) {
                var $span = $link.hasClass('_bt_tt_auxspan') ? $link : $link.find('._bt_tt_auxspan');
                $span.text(text);
            }
            return $link;
        };
        
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
        };

        // Calls the "unbindEvent" method (on "_bt_tt" links when there is a TT_CLICK goal and when it is not part of the "excluded" ones)
        this.detachClickEvent = function (link) {
            if (link.getAttribute('btattached')) {
                unbindEvent(link, 'mousedown', function (evt) {
                    instance.trackConversion(true, {ct: 1, cl: link.href});
                });
                link.removeAttribute('btattached');
            }
        };
        
        // this is a public function that developers can use to refresh the injection in case
        // they use Ajax to refresh the page,which would otherwise be missed by the initial injection
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
            (window.jQuery || _bt.jQuery)(document).ajaxSuccess(function(evt, xhr, opt) {
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
        this.refreshOnTimer = function(retries){
            var retries = retries || 3;
            Log('Enabling refresh on timer call, retries ' + retries, 3);
            for(i=0; i<retries; i++){
                var localDomCodeChangeArray = instance.domCodeChangeArray;
                setTimeout(function(i) {
                    Log('Refresh on timer call executing step ' + i, 3);
                    for (domPath in localDomCodeChangeArray)
                    {
                        if(domPath == '[JS]'){
                            var code = localDomCodeChangeArray[domPath];
                            //replace function call with nop
                            code = code.replace('_bt.refreshOnTimer', '_bt.nop');
                            internalApplyDomChange(domPath, code);
                        }
                    }
                }, 1000 * (i+1), i + 1);
            }
        }
        this.nop = function(){
                    Log('Executing nop...', 3);
        }
        
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

            var hasOnlyJSCode = true;//this is used to check if the code only has javascript code and if it does we need to wait for domready to run the code
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
                            if(instance.jQuery(domPath).size() > 0)//skip
                                hasOnlyJSCode = false;
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
                        setState('done');

                        Log('Wait for domready event to close preloader: ' + hasOnlyJSCode, 3);
                        //close preloader
                        if (instance.CurrentGSIndex == -1)
                        {
                            if(hasOnlyJSCode)
                            {
                                instance.jQuery(document).ready(function() {
                                    setTimeout(function(){closePreloader(false)}, 10);
                                });
                            }
                            else
                            {
                            closePreloader(false);
                            }
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

		function evalScript(script){
			//old style
			//instance.jQuery("<script type=\"text/javascript\">"+script+"<\/script>").appendTo("head");
			
			//since we are using script only, we just eval the script in the global context.
			//to execute script in global context it needs a trick!
			( window.execScript || function( script ) {
				if(window.eval) window.eval.call( window, script );
			} )( script );
			
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
                    //evaluate script
                    Log('Executing web service javascript response now...', 3);
					evalScript("(function($){setTimeout(function(){\n" + changeValue + "\n}, 0);})(window.jQuery||_bt.jQuery);");					
                    setState('done');
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
            if (url.indexOf('?') >= 0)
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
            instance.setCookie('BT_ecl', excludeList, 0);
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
            instance.data.webserviceResponded = state;
        }

        this.dblog = function(message)
        {
            Log(message, 3);
        }
        this.Log = Log;
        
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
            if(gsPreloaderTimeout == 0)
                return;
            var defaultTimeout = Math.min(gsPreloaderTimeout, 3);
            Log("initPreloader - backup timeout " + defaultTimeout + "s",1);
			//set-up timer function
            preloaderTimeoutTimer = setTimeout( function(){closePreloader(true);}, defaultTimeout * 1000);
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

        this.getConditionalActivationData = function()
        {
            return conditionalActivationData;
        }

        this.setConditionalActivationData = function(data)
        {
            conditionalActivationData = data;
        }

        //====================================
        // End declaration of functions here
        //====================================

        // handle Opt-Out
        var url = window.location.href;
        if (url.indexOf("_bt_optout") != -1) {
            Log("opt-out argument found, exit", 1);
            alert("BlackTri wurde für diese Domain deaktiviert.");
            instance.setCookie('_bt_optout', 'true', 3650); // store in session cookie
            return;
        }
        if (instance.readCookie('_bt_optout') == 'true') {
            Log("opt-out cookie found, exit", 1);
            setState('done');
            return;
        }

        // internal JSON object folr old browsers (IE9)
        if(typeof JSON!=='object'){JSON={}}(function(){'use strict';var rx_one=/^[\],:{}\s]*$/,rx_two=/\\(?:["\\\/bfnrt]|u[0-9a-fA-F]{4})/g,rx_three=/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g,rx_four=/(?:^|:|,)(?:\s*\[)+/g,rx_escapable=/[\\\"\u0000-\u001f\u007f-\u009f\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g,rx_dangerous=/[\u0000\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g;function f(n){return n<10?'0'+n:n}function this_value(){return this.valueOf()}if(typeof Date.prototype.toJSON!=='function'){Date.prototype.toJSON=function(){return isFinite(this.valueOf())?this.getUTCFullYear()+'-'+f(this.getUTCMonth()+1)+'-'+f(this.getUTCDate())+'T'+f(this.getUTCHours())+':'+f(this.getUTCMinutes())+':'+f(this.getUTCSeconds())+'Z':null};Boolean.prototype.toJSON=this_value;Number.prototype.toJSON=this_value;String.prototype.toJSON=this_value}var gap,indent,meta,rep;function quote(string){rx_escapable.lastIndex=0;return rx_escapable.test(string)?'"'+string.replace(rx_escapable,function(a){var c=meta[a];return typeof c==='string'?c:'\\u'+('0000'+a.charCodeAt(0).toString(16)).slice(-4)})+'"':'"'+string+'"'}function str(key,holder){var i,k,v,length,mind=gap,partial,value=holder[key];if(value&&typeof value==='object'&&typeof value.toJSON==='function'){value=value.toJSON(key)}if(typeof rep==='function'){value=rep.call(holder,key,value)}switch(typeof value){case'string':return quote(value);case'number':return isFinite(value)?String(value):'null';case'boolean':case'null':return String(value);case'object':if(!value){return'null'}gap+=indent;partial=[];if(Object.prototype.toString.apply(value)==='[object Array]'){length=value.length;for(i=0;i<length;i+=1){partial[i]=str(i,value)||'null'}v=partial.length===0?'[]':gap?'[\n'+gap+partial.join(',\n'+gap)+'\n'+mind+']':'['+partial.join(',')+']';gap=mind;return v}if(rep&&typeof rep==='object'){length=rep.length;for(i=0;i<length;i+=1){if(typeof rep[i]==='string'){k=rep[i];v=str(k,value);if(v){partial.push(quote(k)+(gap?': ':':')+v)}}}}else{for(k in value){if(Object.prototype.hasOwnProperty.call(value,k)){v=str(k,value);if(v){partial.push(quote(k)+(gap?': ':':')+v)}}}}v=partial.length===0?'{}':gap?'{\n'+gap+partial.join(',\n'+gap)+'\n'+mind+'}':'{'+partial.join(',')+'}';gap=mind;return v}}if(typeof JSON.stringify!=='function'){meta={'\b':'\\b','\t':'\\t','\n':'\\n','\f':'\\f','\r':'\\r','"':'\\"','\\':'\\\\'};JSON.stringify=function(value,replacer,space){var i;gap='';indent='';if(typeof space==='number'){for(i=0;i<space;i+=1){indent+=' '}}else if(typeof space==='string'){indent=space}rep=replacer;if(replacer&&typeof replacer!=='function'&&(typeof replacer!=='object'||typeof replacer.length!=='number')){throw new Error('JSON.stringify');}return str('',{'':value})}}if(typeof JSON.parse!=='function'){JSON.parse=function(text,reviver){var j;function walk(holder,key){var k,v,value=holder[key];if(value&&typeof value==='object'){for(k in value){if(Object.prototype.hasOwnProperty.call(value,k)){v=walk(value,k);if(v!==undefined){value[k]=v}else{delete value[k]}}}}return reviver.call(holder,key,value)}text=String(text);rx_dangerous.lastIndex=0;if(rx_dangerous.test(text)){text=text.replace(rx_dangerous,function(a){return'\\u'+('0000'+a.charCodeAt(0).toString(16)).slice(-4)})}if(rx_one.test(text.replace(rx_two,'@').replace(rx_three,']').replace(rx_four,''))){j=eval('('+text+')');return typeof reviver==='function'?walk({'':j},''):j}throw new SyntaxError('JSON.parse');}}}());

        // function to encode and decode strings to base64
        var Base64={_keyStr:"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",encode:function(e){var t="";var n,r,i,s,o,u,a;var f=0;e=Base64._utf8_encode(e);while(f<e.length){n=e.charCodeAt(f++);r=e.charCodeAt(f++);i=e.charCodeAt(f++);s=n>>2;o=(n&3)<<4|r>>4;u=(r&15)<<2|i>>6;a=i&63;if(isNaN(r)){u=a=64}else if(isNaN(i)){a=64}t=t+this._keyStr.charAt(s)+this._keyStr.charAt(o)+this._keyStr.charAt(u)+this._keyStr.charAt(a)}return t},decode:function(e){var t="";var n,r,i;var s,o,u,a;var f=0;e=e.replace(/[^A-Za-z0-9\+\/\=]/g,"");while(f<e.length){s=this._keyStr.indexOf(e.charAt(f++));o=this._keyStr.indexOf(e.charAt(f++));u=this._keyStr.indexOf(e.charAt(f++));a=this._keyStr.indexOf(e.charAt(f++));n=s<<2|o>>4;r=(o&15)<<4|u>>2;i=(u&3)<<6|a;t=t+String.fromCharCode(n);if(u!=64){t=t+String.fromCharCode(r)}if(a!=64){t=t+String.fromCharCode(i)}}t=Base64._utf8_decode(t);return t},_utf8_encode:function(e){e=e.replace(/\r\n/g,"\n");var t="";for(var n=0;n<e.length;n++){var r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r)}else if(r>127&&r<2048){t+=String.fromCharCode(r>>6|192);t+=String.fromCharCode(r&63|128)}else{t+=String.fromCharCode(r>>12|224);t+=String.fromCharCode(r>>6&63|128);t+=String.fromCharCode(r&63|128)}}return t},_utf8_decode:function(e){var t="";var n=0;var r=c1=c2=0;while(n<e.length){r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r);n++}else if(r>191&&r<224){c2=e.charCodeAt(n+1);t+=String.fromCharCode((r&31)<<6|c2&63);n+=2}else{c2=e.charCodeAt(n+1);c3=e.charCodeAt(n+2);t+=String.fromCharCode((r&15)<<12|(c2&63)<<6|c3&63);n+=3}}return t}}

        // manage reading and writing to the session data collector cookie
        var SDC={
            sdc:'NA',
            sdcJsonString:'NA',
            // ensure the cookie exists and is filled with initialized values
            sync:function() {
                sdccookie = Base64.decode(instance.readCookie('BT_sdc'));
                //Log('SDC::decoded cookie:' + sdccookie,3);
                if(sdccookie != 'NA') {
                    try {
                        sdcJsonString = sdccookie.replace(/\+/g, ' ');
                        //Log('SDC::sdccookie:' + sdcJsonString,3);
                        sdc = JSON.parse(sdcJsonString);
                    }
                    catch (e) {
                        Log('SDC::sdc cookie not empty but invalid:' + sdccookie,3);
                        sdc = 'NA';
                    }
                }
                if((typeof sdc == 'undefined') || (sdc == 'NA')) { // set cookie again
                    Log('SDC::create new sdc object and store in cookie',3);
                    sdc = new Object();
                    sdc.et_coid = 'NA'; // etracker visitor-id
                    sdc.rfr = rfr; // referrer
                    sdc.ttest_lpid = 0; // exclude-page-id for teasertests
                    sdc.ttest = {
                        history : new Object()
                    };
                    sdc.time = new Date().getTime(); // session start time
                    sdc.pi = 0; // number of page impressions
                }
                // write sdc object back to cookie
                this.writeCookie();           
            },
            // save sdc object back to cookie
            writeCookie:function() {
                sdcJsonString = JSON.stringify(sdc); 
                //Log('SDC::cookie value:' + sdcJsonString,3);
                //Log('SDC::write cookie:' + Base64.encode(sdcJsonString),3);
                instance.setCookie('BT_sdc',Base64.encode(sdcJsonString), 0);                         
            },
            // get a parameter from the SDC
            getParam:function(key){
                this.sync();
                if(key=='time') {
                    result = new Date().getTime() - sdc.time;
                }
                else {
                    result = sdc[key];
                }
                Log('SDC::getParam ' + key + 'value: ' + result,3);
                return(result);
            },
            // set a parameter in the SDC
            setParam:function(key,value){
                this.sync();
                sdc[key] = value;
                Log('SDC::setParam ' + key + 'value: ' + sdc[key],3);
                this.writeCookie();
            },
            toJSON:function() {
                this.sync();
                mysdc = {
                    et_coid:sdc.et_coid,
                    rfr:sdc.rfr,
                    pi:sdc.pi,
                    ttest_lpid:sdc.ttest_lpid,
                    ttest:sdc.ttest,
                    time:(new Date().getTime() - sdc.time)
                };
                return(JSON.stringify(mysdc));
            }
        }

        // check if browser accepts cookies and break if not
        instance.setCookie('BT_ctst', '101', 0);
        if (instance.readCookie('BT_ctst') != '101') {
            // browser does not accept cookies, no further optimisation
            Log("no cookies, exit", 1);
            setState('done');
            return;
        }
        instance.setCookie('BT_ctst', '', -1); // delete the cookie to tidy up...
        
        // handle loglevel
        if (instance.readCookie("btLogLevel") != 'NA') {
            loglv = instance.readCookie("btLogLevel");
        }
        if(_etLoglv) {
            instance.setCookie("btLogLevel", loglv, 0); // store in session cookie
        }
        
        // increment page impression count and store in cookie. plus store the et_coid
        // value every time since it might not be present in the first request but the second.
        SDC.setParam('pi',SDC.getParam('pi') + 1);
        SDC.setParam('et_coid',instance.readCookie('_et_coid'));
        // use relative time (session time) to work with
        Log("time: " + SDC.getParam('time'),3);

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
                    setState('disabled');
                    return;
                }
            }

            //initialize
            Init();

            //attach onload events
            instance.initOnLoad();
        }        
    });
	
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
	//====================================
	// Begin declaration of custom editor functions
	//====================================		
	window._bt.moveElement = function(selector, object){
            window._bt.$(selector).css(object);
	};
	window._bt.setStyle = function(selector, object){
            window._bt.$(selector).css(object);
	}
	//====================================
	// End declaration of custom editor functions
	//====================================


	//====================================
	// Begin declaration of custom editor functions
	//====================================		
		function editorCheck(){
			var sendDomain = '*';			
			window.addEventListener('message', onReceiveMessage);
			window.top.postMessage(JSON.stringify({"method": 'bt_ping', params: [document.URL] }), sendDomain);
			window.BTSendDomain = sendDomain;
		}
		function onReceiveMessage(event){
			if (event == null || typeof event != "object") {
				window._bt.Log("[COM] Missing event");
				return;
			}
			var info = event.data;
			if (info == null) {
				window._bt.Log("--> Missing info");
				return;
			}
			try { info = JSON.parse(info); }
			catch (e) {
				window._bt.Log("--> Parse info failure: " + e.message);
				return;
			}
			if (typeof info != "object") {
				window._bt.Log("--> Invalid info received");
				return;
			}
			if(info.method && info.method == 'bt_pong'){
				//make clean-up
				window.removeEventListener('message', onReceiveMessage);
				var param = info.params[0];
				
				window.BTUrl = param.BTUrl;
				window.BTEditorUrl = param.BTEditorUrl;
				
				if(window&&window.get_bthost&&typeof(window.get_bthost)=='function')
					window.BTUrl = window.get_bthost();
					
				loadJS(window.BTUrl + '/js/opt-scripts.js?r=' + Math.random(), function(){
					if(typeof window.btInitCommunication == "function")
						window.btInitCommunication();
						
					if(typeof window.btTriggerTriggerDomReady == "function")
						window.btTriggerTriggerDomReady();
					if(typeof window.btLoaded == "function")
						window.btLoaded();
				});
			}
		}
		if(window.top && window.top.postMessage)
			editorCheck();
	//====================================
	// End declaration of custom editor functions
	//====================================
})();