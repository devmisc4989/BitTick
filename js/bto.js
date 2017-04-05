var clientDomain = 'domain_a.com';

(function() {

    //initial loading
    var globaltime = new Date().getTime();

    _btTestType = typeof (_btTestType) != 'undefined' ? _btTestType : 1;
    _bt_loglv = window.location.href.indexOf('_bt_loglv') >= 0;  // true if the querystring et_poploglv is set
    _bt = window["_bt"] || new (function() {
        var instance = this,
                $, jQuery,
                gsPreloaderTimeout = 0.3, /* seconds */
                preloaderTimeoutTimer = 0,
                domChangeApplied = false,
                domCodeChangeArray = [],
                conditionalActivationData = 'activation',
                ajaxCallEventSet = false,
                qrs = '',
                btTestType = _btTestType || 1,
                pg = document.location,
                rfr = document.referrer,
                loglv = _bt_loglv ? parseInt(getParameterByName('_bt_loglv')) : (typeof(_bt_loglv) !== 'undefined') ? _bt_loglv : 0;

        // track state of webservice (loading / ready or done)
        this.state = function()
        {
            return webServiceState;
        }

        // create a data layer array
        this.data = {};
        this.data.webserviceResponded = 'initialized';
        this.data.projectCount = 0;        

        var isIE = window.navigator.userAgent.match(/(?:(MSIE) |(Trident)\/.+rv:)([\w.]+)/i) || false;
        var isWebKit = new RegExp(/webkit/i).test(navigator.userAgent);
        var isMobile = ((navigator.platform.indexOf("iPhone") != -1) || (navigator.platform.indexOf("iPod") != -1) || (navigator.platform.indexOf("iPad") != -1) || (navigator.userAgent.indexOf("Android") != -1));

        Log('init anonymous, isMobile=' + isMobile + ', isWebKit=' + isWebKit + ', isIE=' + isIE, 3);
		
        this.BTObject = {};

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

            // init preloader - make page invisibel to avoid flickering before webservice has loaded
            initPreloader();

            //set up trim functionality
            if (typeof (''.trim) != 'function')
                String.prototype.trim = function() {
                    return this.replace(/^\s+|\s+$/, '');
                };

            //query string update
            qrs = document.location.search.replace('?', '');

            if(window&&window.name=='frame_editor') {
                return;
            }

            deliverTests();
        }
		
        // main function to deliver tests as defined in the test definition file/s
        function deliverTests()
        {
            Log('deliverTests ', 3);

            var pageUrl = window.location.href;
			
            // find matching tests
            matchingTests = getMatchingTests(_bt_testdef.tests, pageUrl);
			
            if(!matchingTests) { // no result
                Log('No matching test, exit', 3);
                return false;
            }
            // for this rev, only use one test result
            matchingTest = matchingTests[0];

            // check for returning visitor and history
            buckets = SDC.getParam('buckets');
            index = matchingTest.id;
            variantid = -1; // -1 is undefined, 0 would be original
            if(buckets[index] != undefined) {
                variantid = buckets[index];
                // is this variant still available?
                if(variantid != 0) { // original is always available...
                    variant = getVariant(matchingTest,variantid);
                    if(!variant) {
                        variantid = -1;
                    }
                    else {
                        Log('Returning visitor, deliver variant ' + variantid, 3);
                    }
                }
                else {
                    Log('Returning visitor, deliver original', 3);
                }
            }

            // if new visitor, get random variant
            if(variantid == -1) {
                Log('New visitor in this test, get random variant', 3);
                variant = getRandomVariant(matchingTest);
                if(variant === 0) { // if we deliver the original
                    variantid = 0;
                }
                else {
                    variantid = variant.id;
                }
                // save in cookie
                buckets[matchingTest.id] = variantid;
                SDC.setParam('buckets',buckets);
                
                // track the delivery
                trackDelivery(_bt_testdef.tracking, matchingTest, variant);
            }

            // deliver the variant or original
            if(variantid == 0) {
                Log('Deliver original, Noop', 3);
            }
            else {
                testType = matchingTest.type;
                if(testType.toLowerCase() == 'split') {
                    // don't execute the split redirect if there is a _nrd=true in the page URL (to avoid infinite loops)
                    redirectUrl = variant.redirect_url;
                    if(pageUrl.indexOf("_nrd=true") > -1)
                        return;
                    // attach the querystring to the redirect url
                    // TBD
                    instance.redirect(redirectUrl);
                }
                else {  // do the dom injection
                    //create internal domchange array to use default applyCollectionChanges function
                    var domChangeArray = {"[JS]": variant.jsinjection,"[CSS]": variant.cssinjection};
                    instance.applyCollectionChanges(domChangeArray);
                }
            }
        }
		
        //this function applies changes stored in the object to current page
        this.applyCollectionChanges = function(DomCodeChangeArray)
        {
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
                else
                {
                    Log('Timeout reached!', 1);
                    //cleanup
                    localCollectionChangeApplied = null;

                    setState('done');

                    //close preloader
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
            closePreloader(false);
        }
		function evalScript(script){
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

        // read a parameter from querystring
        function getParameterByName(name) {
            name = name.replace(/[\[]/, "\\\[").replace(/[\]]/, "\\\]");
            var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
                    results = regex.exec(location.search);
            return results == null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
        }
        
        //preloader function
		function initPreloader()
		{
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

        //===============================================================
        // functions replacing bto optimisation  webservice functionality 
        //===============================================================

        // return all tests matching the current pageUrl
        function getMatchingTests(tests, pageUrl) {
            var index, indexDelivery;
            for(index = 0; index < tests.length; ++index) {  // all tests
                includeUrls = tests[index].delivery.include_urls;
                excludeUrls = tests[index].delivery.exclude_urls;
                numIncludedUrls = 0;
                numExcludedUrls = 0;
                for(indexDelivery = 0; indexDelivery < includeUrls.length; ++indexDelivery) {  // all included Urls
                    if(instance.isMatchingUrl(includeUrls[indexDelivery], pageUrl)) {
                        numIncludedUrls++;
                    }
                }
                for(indexDelivery = 0; indexDelivery < excludeUrls.length; ++indexDelivery) {  // all excluded Urls
                    if(instance.isMatchingUrl(excludeUrls[indexDelivery], pageUrl)) {
                        numExcludedUrls++;
                    }
                }
                if((numIncludedUrls > 0) && (numExcludedUrls == 0))
                    return [tests[index]];
            }
            return false;
        }

        // substitue for PHP fnmatch function
        // see http://stackoverflow.com/questions/26246601/wildcard-string-comparison-in-javascript
        function matchRuleShort(str, rule) {
            return new RegExp("^" + rule.split("*").join(".*") + "$").test(str);
        }		

        // check wether the pageUrl matches the pattern
        this.isMatchingUrl = function(pattern,url) {            
            pattern = pattern.trim();
            if(pattern.charAt(0) == '*')
                startsWithWildcard = true; 
            else
                startsWithWildcard = false;
            if(pattern.slice(-1) == '*')
                endsWithWildcard = true; 
            else
                endsWithWildcard = false;

            url = url.trim();
            // normalize pattern and url with respect to trailing /
            target = "/*";
            patternEndsWithSlashAsterisk = (pattern.slice(target.length * -1) === target);
            // when the pattern ends with "/*" we should not remove the trailing slash from the URL
            if(!patternEndsWithSlashAsterisk) {
                url = url.replace(/\/+$/,''); // remove trailing /
                url = url.replace("/?", "?");
            }
            pattern = pattern.replace(/\/+$/,''); // remove trailing /
            pattern = pattern.replace("/?", "?");

            // if pattern contains no ? then match against the URL without the querystring
            if((pattern.indexOf("?") == -1) && !endsWithWildcard) {
                url = url.split("?")[0];
            }
            // if pattern contains no protocol, then match against the URL without protocol
            if (pattern.indexOf("http") == -1) {
                // remove protocol if present
                url = url.replace("http://", "");
                url = url.replace("https://", "");
            }

            // escape meta characters of shell wildcard syntax
            pattern = pattern.replace("[", "\\[");
            pattern = pattern.replace("]", "\\]");
            pattern = pattern.replace("{", "\\{");
            pattern = pattern.replace("}", "\\}");
            pattern = pattern.replace("?", "\\?");

            return(matchRuleShort(url,pattern));
        }

        // get a variant with a give ID from a test
        function getVariant(test, id) {
            var index;
            var variants = test.variants;
            for(index = 0; index < variants.length; ++index) {
                if(test.variants[index].id == id) {
                    return test.variants[index];
                }
            }
            return false;
        }

        // get a random variant from a test
        function getRandomVariant(test) {            
            controlVariant = {
                id : 0
            };
            test.variants.push(controlVariant);
            numVariants = test.variants.length;
            index = Math.floor(Math.random() * 1000) % numVariants;
            if(test.variants[index].id == 0)
                return 0;
            else
                return test.variants[index];
        }

        // track with an additional event according to configuration
        function trackDelivery(trackingConfig, test, variant) {

            // google analytics
            if(trackingConfig.provider == 'GA') {
                trackingMessage = test.name + " - " + ((variant.id == 0) ? 'Control' : variant.name);
                dimension = 'dimension' + trackingConfig.GA_dimension_id;
                ddata = {};
                ddata[dimension] = trackingMessage;
                ga('send', 'pageview', ddata);                    
            }

            // google tag manager
            if(trackingConfig.provider == 'GTM') {
                instance.data['projects'] = [];
                variantname = (variant.id == 0) ? 'Control' : variant.name;
                var p = {
                    projectname : test.name,
                    projectid : test.id,
                    decisionname : variantname,
                    decisionid: variant.id
                };
                (instance.data['projects']).push(p);
                console.log(instance.data['projects']);
                dataLayer.push({'event': '_bto'});
            }

        }

        //===============================================================

        // internal JSON object for old browsers (IE9)
        if(typeof JSON!=='object'){JSON={}}(function(){'use strict';var rx_one=/^[\],:{}\s]*$/,rx_two=/\\(?:["\\\/bfnrt]|u[0-9a-fA-F]{4})/g,rx_three=/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g,rx_four=/(?:^|:|,)(?:\s*\[)+/g,rx_escapable=/[\\\"\u0000-\u001f\u007f-\u009f\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g,rx_dangerous=/[\u0000\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g;function f(n){return n<10?'0'+n:n}function this_value(){return this.valueOf()}if(typeof Date.prototype.toJSON!=='function'){Date.prototype.toJSON=function(){return isFinite(this.valueOf())?this.getUTCFullYear()+'-'+f(this.getUTCMonth()+1)+'-'+f(this.getUTCDate())+'T'+f(this.getUTCHours())+':'+f(this.getUTCMinutes())+':'+f(this.getUTCSeconds())+'Z':null};Boolean.prototype.toJSON=this_value;Number.prototype.toJSON=this_value;String.prototype.toJSON=this_value}var gap,indent,meta,rep;function quote(string){rx_escapable.lastIndex=0;return rx_escapable.test(string)?'"'+string.replace(rx_escapable,function(a){var c=meta[a];return typeof c==='string'?c:'\\u'+('0000'+a.charCodeAt(0).toString(16)).slice(-4)})+'"':'"'+string+'"'}function str(key,holder){var i,k,v,length,mind=gap,partial,value=holder[key];if(value&&typeof value==='object'&&typeof value.toJSON==='function'){value=value.toJSON(key)}if(typeof rep==='function'){value=rep.call(holder,key,value)}switch(typeof value){case'string':return quote(value);case'number':return isFinite(value)?String(value):'null';case'boolean':case'null':return String(value);case'object':if(!value){return'null'}gap+=indent;partial=[];if(Object.prototype.toString.apply(value)==='[object Array]'){length=value.length;for(i=0;i<length;i+=1){partial[i]=str(i,value)||'null'}v=partial.length===0?'[]':gap?'[\n'+gap+partial.join(',\n'+gap)+'\n'+mind+']':'['+partial.join(',')+']';gap=mind;return v}if(rep&&typeof rep==='object'){length=rep.length;for(i=0;i<length;i+=1){if(typeof rep[i]==='string'){k=rep[i];v=str(k,value);if(v){partial.push(quote(k)+(gap?': ':':')+v)}}}}else{for(k in value){if(Object.prototype.hasOwnProperty.call(value,k)){v=str(k,value);if(v){partial.push(quote(k)+(gap?': ':':')+v)}}}}v=partial.length===0?'{}':gap?'{\n'+gap+partial.join(',\n'+gap)+'\n'+mind+'}':'{'+partial.join(',')+'}';gap=mind;return v}}if(typeof JSON.stringify!=='function'){meta={'\b':'\\b','\t':'\\t','\n':'\\n','\f':'\\f','\r':'\\r','"':'\\"','\\':'\\\\'};JSON.stringify=function(value,replacer,space){var i;gap='';indent='';if(typeof space==='number'){for(i=0;i<space;i+=1){indent+=' '}}else if(typeof space==='string'){indent=space}rep=replacer;if(replacer&&typeof replacer!=='function'&&(typeof replacer!=='object'||typeof replacer.length!=='number')){throw new Error('JSON.stringify');}return str('',{'':value})}}if(typeof JSON.parse!=='function'){JSON.parse=function(text,reviver){var j;function walk(holder,key){var k,v,value=holder[key];if(value&&typeof value==='object'){for(k in value){if(Object.prototype.hasOwnProperty.call(value,k)){v=walk(value,k);if(v!==undefined){value[k]=v}else{delete value[k]}}}}return reviver.call(holder,key,value)}text=String(text);rx_dangerous.lastIndex=0;if(rx_dangerous.test(text)){text=text.replace(rx_dangerous,function(a){return'\\u'+('0000'+a.charCodeAt(0).toString(16)).slice(-4)})}if(rx_one.test(text.replace(rx_two,'@').replace(rx_three,']').replace(rx_four,''))){j=eval('('+text+')');return typeof reviver==='function'?walk({'':j},''):j}throw new SyntaxError('JSON.parse');}}}());

        // function to encode and decode strings to base64
        var Base64={_keyStr:"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",encode:function(e){var t="";var n,r,i,s,o,u,a;var f=0;e=Base64._utf8_encode(e);while(f<e.length){n=e.charCodeAt(f++);r=e.charCodeAt(f++);i=e.charCodeAt(f++);s=n>>2;o=(n&3)<<4|r>>4;u=(r&15)<<2|i>>6;a=i&63;if(isNaN(r)){u=a=64}else if(isNaN(i)){a=64}t=t+this._keyStr.charAt(s)+this._keyStr.charAt(o)+this._keyStr.charAt(u)+this._keyStr.charAt(a)}return t},decode:function(e){var t="";var n,r,i;var s,o,u,a;var f=0;e=e.replace(/[^A-Za-z0-9\+\/\=]/g,"");while(f<e.length){s=this._keyStr.indexOf(e.charAt(f++));o=this._keyStr.indexOf(e.charAt(f++));u=this._keyStr.indexOf(e.charAt(f++));a=this._keyStr.indexOf(e.charAt(f++));n=s<<2|o>>4;r=(o&15)<<4|u>>2;i=(u&3)<<6|a;t=t+String.fromCharCode(n);if(u!=64){t=t+String.fromCharCode(r)}if(a!=64){t=t+String.fromCharCode(i)}}t=Base64._utf8_decode(t);return t},_utf8_encode:function(e){e=e.replace(/\r\n/g,"\n");var t="";for(var n=0;n<e.length;n++){var r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r)}else if(r>127&&r<2048){t+=String.fromCharCode(r>>6|192);t+=String.fromCharCode(r&63|128)}else{t+=String.fromCharCode(r>>12|224);t+=String.fromCharCode(r>>6&63|128);t+=String.fromCharCode(r&63|128)}}return t},_utf8_decode:function(e){var t="";var n=0;var r=c1=c2=0;while(n<e.length){r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r);n++}else if(r>191&&r<224){c2=e.charCodeAt(n+1);t+=String.fromCharCode((r&31)<<6|c2&63);n+=2}else{c2=e.charCodeAt(n+1);c3=e.charCodeAt(n+2);t+=String.fromCharCode((r&15)<<12|(c2&63)<<6|c3&63);n+=3}}return t}}

        // manage reading and writing to the data collector cookie
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
                    sdc.status = 'new';
                    sdc.buckets = {};
                }
                // write sdc object back to cookie
                this.writeCookie();           
            },
            // save sdc object back to cookie
            writeCookie:function() {
                sdcJsonString = JSON.stringify(sdc); 
                //Log('SDC::cookie value:' + sdcJsonString,3);
                //Log('SDC::write cookie:' + Base64.encode(sdcJsonString),3);
                instance.setCookie('BT_sdc',Base64.encode(sdcJsonString), 365);                         
            },
            // get a parameter from the SDC
            getParam:function(key){
                this.sync();
                result = sdc[key];
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
                    test:sdc.test
                };
                return(JSON.stringify(mysdc));
            }
        }

        //====================================
        // End declaration of functions here
        //====================================

        // handle loglevel
        if (instance.readCookie("btLogLevel") != 'NA') {
            loglv = instance.readCookie("btLogLevel");
        }
        if(_bt_loglv) {
            instance.setCookie("btLogLevel", loglv, 0); // store in session cookie
        }

        // check licensed client domain
        if (window.location.href.indexOf(clientDomain) == -1) {
            Log("can not run on this domain, exit", 1);
            return;
        }


        //initialize
        Init();

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