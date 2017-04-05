var BT_SmartMessaging = (function () {
    var $ = window.BTJQuery;
    var messagePosition = $("#_bt_sms_js_arguments").data("sms_message_position");
    var messageType = $("#_bt_sms_js_arguments").data("sms_message_type");
    var variantId = $("#_bt_sms_js_arguments").data("sms_variantid");
    var timeOut = $("#_bt_sms_js_arguments").data("sms_timeout");
    var rule = $("#_bt_sms_js_arguments").data("sms_rule");
    var $etrackerBranding = $('#_bt_sms_branding_element');
    var $InnerContainer = $('#_bt_sms_inner_container');
    var $mainContainer = $('#_bt_sms_main_container');
    var $pagepeelId = $('#_bt_sms_peel_away');
    var $wrapperId = $('#_bt_sms_wrapper');
    var $overlayId = $('#_bt_sms_overlay');
    var $sliderTab = $('#_bt_sms_slider_tab');
    var $followLink = $('._bt_sms_follow_link');
    var $closeBtn = $('._bt_sms_close_button');
    var timeAnimation = 250;
    var cookieDays = 365;
    var redirectUrl = '';
    var timeClose = 0;
    var isVisible = false;
    /**
     * Appends the dialog div to the body according to the messageType
     */
    var appendDiv = {
        'popup': function () {
            $overlayId.css({
                display: 'none',
                position: 'fixed',
                opacity: '0.55',
                'z-index': '991110'
            });
            $wrapperId.css({
                display: 'none',
                'z-index': '991111'
            });
            $mainContainer.fadeIn(0);
            bindRule();
        },
        /**
         * depending on the position of the message, stablish the CSS rules
         */
        'message_bar': function () {
            if (typeof (messagePosition) !== 'undefined') {
                messagePosition = ($wrapperId.css('bottom') === '0px') ? 'bottom' : 'top';
            }

            $mainContainer.css({
                display: 'block',
                'margin-top': '-1000px',
                visibility: 'hidden'
            });

            switch (messagePosition) {
                case 'bottom':
                    $wrapperId.css({
                        display: 'none',
                        position: 'fixed',
                        bottom: '0'
                    });
                    break;
                default:
                    var h = $wrapperId.outerHeight();
                    $wrapperId.css({
                        display: 'none',
                        position: 'absolute',
                        top: '0'
                    });

                    var htm = '<div id="_bt_aux_container"></div>';
                    $('body').prepend(htm);
                    $('#_bt_aux_container').css({
                        display: 'none',
                        height: h + 'px',
                        margin: '0',
                        padding: '0'
                    });
                    break;
            }
            $mainContainer.css({
                visibility: 'visible'
            });
            bindRule();
        },
        /**
         * sliders are the messages that appears at the left or right of the page
         */
        'slider': function () {
            setTimeout(function () {
                if (typeof (messagePosition) !== 'undefined') {
                    messagePosition = ($wrapperId.hasClass('_right')) ? 'right' : 'left';
                }
                var w = $InnerContainer.outerWidth();
                var side = (messagePosition === 'right') ? 'right' : 'left';
                $wrapperId.css(side, '-' + w + 'px');

                $wrapperId.fadeOut(0, function () {
                    $mainContainer.fadeIn(0);
                    $sliderTab.on('click', function () {
                        showMessage(false, true);
                    });
                    $(this).fadeIn(0);
                });

                bindRule();
            }, 99);
        },
        /**
         * waits for the bg image to load before creating the page peel animation when the user hovers over the corner of the page
         */
        'peel_away': function () {
            var image_url = $('#_bt_sms_peel_away').css('background-image');
            var image;
            image_url = image_url.match(/^url\("?(.+?)"?\)$/);

            if (image_url[1]) {
                image_url = image_url[1];
                image = new Image();

                $('#_bt_sms_peel_away').css({
                    width: '0',
                    height: '0'
                });

                $(image).load(function () {
                    var w = image.width;
                    var h = image.height;

                    $pagepeelId.hover(function () {
                        $pagepeelId.stop().animate({
                            width: w + 'px',
                            height: h + 'px'
                        }, 750);
                    }, function () {
                        $pagepeelId.stop().animate({
                            width: '100px',
                            height: '100px'
                        }, 500);
                    });

                    $mainContainer.fadeIn(0, function () {
                        $pagepeelId.animate({
                            width: '100px',
                            height: '100px'
                        }, 750);
                    });

                    $pagepeelId.click(function () {
                        window.location.href = $(this).attr('title');
                    });

                }).error(function () {
                    console.log('Error loading the peel IMG bg');
                });
                image.src = image_url;
            }
        }
    };
    /**
     * returns the cookie content given a cookie name
     * @param {String} cname
     * @returns {String}
     */
    var getCookie = function (cname) {
        var name = cname + "=";
        var cookies = document.cookie.split(';');
        for (var i = 0; i < cookies.length; i++) {
            var c = cookies[i].trim();
            if (c.indexOf(name) === 0)
                return unescape(c.substring(name.length, c.length));
        }
        return "";
    };
    /**
     * sets the current session cookie to determine wether to show or not the message according with its type
     * @param {array} newsms
     * @returns {Boolean}
     */
    var setSessionCookie = function (newsms) {
        var ret = true;
        var sessionSms = $.parseJSON(getCookie("BT_session_sms"));

        if (sessionSms !== null && sessionSms.length > 0) {
            for (var index in sessionSms) {
                if (sessionSms[index]['sms']['rule'] === rule) {
                    ret = false;
                    break;
                } else if (messageType === 'popup' && sessionSms[index]['sms']['type'] === messageType) {
                    ret = false;
                    break
                }
            }
            newsms = (ret) ? sessionSms.concat(newsms) : sessionSms;
        }

        if (ret) {
            document.cookie = "BT_session_sms=" + escape(JSON.stringify(newsms)) + ";path=/";
        }
        return ret;
    };
    /**
     * depending on previous SMS types and/or rules, determines wether to show or not the SMS in this session
     * @param {object} log -- {rule, type, variant id}
     * @returns {Boolean} -- if true, the SMS will be shown in this session
     */
    var setGlobalCookie = function (log) {
        var d = new Date();
        var year = cookieDays * 24 * 60 * 60 * 1000;
        var currentsms = $.parseJSON(getCookie("BT_sms"));

        log.date = d.getTime();
        var btsms = new Array();
        var newsms = new Array();
        newsms.push({
            sms: log
        });

        var ret2 = true;
        var greeter = false;
        var nextyear = new Date(d.getTime() + (year));
        var expires = "expires=" + nextyear.toGMTString();

        if (currentsms !== null && currentsms.length > 0) {
            for (var index in currentsms) {
                greeter = ((currentsms[index]['sms']['rule'] === rule) && (rule === 'greeter'));
                if (currentsms[index]['sms']['variant'] === variantId || greeter) {
                    var date = currentsms[index]['sms']['date'];
                    ret2 = ((d.getTime() - date) < year) ? false : true;
                    currentsms[index]['sms']['date'] = (ret2) ? d.getTime() : date;
                    break;
                }
            }
            btsms = (ret2) ? currentsms.concat(newsms) : currentsms;
        } else {
            btsms = newsms;
        }

        var ret1 = (ret2) ? setSessionCookie(newsms) : false;

        if (ret1 && ret2) {
            document.cookie = "BT_sms=" + escape(JSON.stringify(btsms)) + "; path=/;" + expires;
        }
        return(ret1 && ret2);
    };
    /**
     * Logs the action (close, follow, open), the rule, SMS type and variant ID
     * @param {string} logtype -- "close", "open", "follow"
     * @returns {Boolean} -- if true, the SMS will be shown, depending on previous/current session data or if rule is "always_on
     */
    var setCookieObject = function (logtype) {
        var cookieO = {
            smslog: logtype,
            rule: rule,
            type: messageType,
            variant: variantId
        };
        return setGlobalCookie(cookieO);
    };
    /**
     * performs the tracking calls (TBD), by now, it logs the event type and variant ID
     * @param {string} eventType
     * @param {int} variantId
     */
    var trackSmsEvents = function (eventType, variantId) {
        if (eventType === "view") {
            console.log('View:  ' + variantId);
            _bt.deferredImpression();
            triggerEtracker("view");
        }
        if (eventType === "close") {
            console.log('Close:  ' + variantId);
        }
        if (eventType === "follow") {
            console.log('Follow:  ' + variantId);
            _bt.trackSmsFollow();
            triggerEtracker("click");
        }
    };
    // trigger etracker tracking event
    var triggerEtracker = function (event) {
        if (typeof (bto_attributes) !== 'undefined') {
            bto_attributes["etcc_int"] = [event, true];
            var ruleValue = 'NA';
            switch (rule) {
                case 'exit_intent':
                    ruleValue = 'STC_CC_ATTR_VALUE_TRIGGER_EXIT_INTENT';
                    break;
                case 'attn_grabber':
                    ruleValue = 'STC_CC_ATTR_VALUE_TRIGGER_ATTENTION_GRABBER';
                    break;
                case 'greeter':
                    ruleValue = 'STC_CC_ATTR_VALUE_TRIGGER_GREETER';
                    break;
                case 'always_on':
                    ruleValue = 'STC_CC_ATTR_VALUE_TRIGGER_ALWAYS_ON';
                    break;
            }

            bto_attributes["etcc_trg"] = ruleValue;
            cc_attributes = bto_attributes;
            if ($('#_bt_sms_js_arguments').length > 0) {
                var key2 = $('#_bt_sms_js_arguments').attr('data-sms_account_key2');
                if ((typeof (key2) !== 'undefined') && (key2 !== 'NA') && (typeof et_cc_wrapper !== 'undefined')) {
                    et_cc_wrapper(key2);
                }
            }
        }
    };
    /*
     * hides the SMS inmediately when the user clicks on a "follow" button 
     * If the message type is "slider" it only hides the contianer, not the trigger tab
     */
    var hideOnFollow = function () {

        if (messageType === 'slider') {
            showMessage(true, false);
            return;
        }

        $mainContainer.fadeOut(timeAnimation);
        $wrapperId.fadeOut(timeAnimation);
        if ($overlayId.length > 0)
            $overlayId.fadeOut(timeAnimation * 2);
        if ($('#_bt_aux_container').length > 0)
            $('#_bt_aux_container').fadeOut(timeAnimation * 2);
    };
    /**
     * Shows/closes the message with the appropriate animation according to the message type and position
     *      * If the SMS main container has the class "et_remote_sms", that means that the custom
     *      * etracker "survey" or "feedback" popups has to be open instead of the default behavior.
     *      * So, it checks for the language parameter (1=german, 2=english, 3=french, 4=spanish)
     *      * and calls the corresponding method with that parameter.
     * @param {boolean} close -- if true, the message is closed/hidden
     * @param {boolean} opentab -- if true, that means that the request was made by the slider "tab" and it should be open anyway
     * @returns void
     */
    var showMessage = function (close, opentab) {
        if ($mainContainer.hasClass('et_remote_sms')) {
            if(typeof(_etracker) === 'undefined'){
                return false;
            }
            
            var pType = $mainContainer.data("etr_popup_type");
            var $imgId = $('#_bt_et_' + pType + '_img');
            var lang = $imgId.length > 0 ? $imgId.data('etr_lang') : 1;
            if (pType === 'survey') {
                _etracker.openSurvey(lang);
            } else {
                _etracker.openFeedback(lang);
            }
            return false;
        }
        
        var show;
        var logtype = (close) ? 'close' : 'view';

        if (close || rule === 'always_on' || (opentab && messageType === 'slider')) {
            show = true;
        } else {
            show = setCookieObject(logtype);
        }

        if (!show && window.location.href.indexOf('_p=t') < 0) {
            console.log("do not show message due to cookie content!");
            return;
        }

        trackSmsEvents(logtype, variantId);

        switch (messageType) {
            case 'message_bar':
                var dir = (messagePosition === 'bottom') ? 'up' : 'down';
                $('#_bt_aux_container').slideToggle({direction: dir}, timeAnimation);
                $wrapperId.fadeToggle(timeAnimation, 'linear');
                if (close) {
                    $mainContainer.fadeOut(timeAnimation);
                }
                break;
            case 'slider':
                var w = $InnerContainer.outerWidth();
                var margin = (close) ? '-' + w + 'px' : 0;
                var param;
                if (messagePosition === 'right') {
                    param = {right: margin};
                } else {
                    param = {left: margin};
                }
                $wrapperId.animate(param, timeAnimation * 1.5);
                break;
            default:
                $overlayId.fadeToggle(timeAnimation * 2);
                $wrapperId.fadeToggle(timeAnimation);
                if (close) {
                    $mainContainer.fadeOut(timeAnimation);
                }
                break;
        }

        $closeBtn.off('click');
        $sliderTab.off('click');
        $etrackerBranding.find('span').off('click');
        $followLink.on('click', function (e) {
            hideOnFollow();
            trackSmsEvents('follow', variantId);
            return true;
        });
        $closeBtn.on('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            showMessage(true, false);
            return false;
        });
        $sliderTab.on('click', function () {
            if (close)
                showMessage(false, true);
            else
                showMessage(true, false);
        });
        $etrackerBranding.find('span, strong').attr('style', 'cursor:pointer !important;');
        $etrackerBranding.find('span').on('click', function () {
            var link = document.createElement('a');
            link.href = 'http://etracker.com/';
            link.target = '_blank';
            link.id = '_etracker_branding_link';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
    };
    /**
     * first waits for the cursor to be in the page (posY > 10 && time > 5 seconds).
     * Then checks wether the cursor leaves the page to call the function to show the message
     */
    var bindExitIntent = function () {
        var pos_y = -1;
        var maxTime = 1 * 1000;
        var curTime = 0;
        // when the cursor leaves the top of the page, the message is shown
        var leaveFromTop = function (e) {
            e.stopPropagation();
            if (e.clientY < 0 && !isVisible) {
                isVisible = true;
                console.log('showMessage, curTime=' + curTime);
                showMessage(false, false);
                $(document).off('mouseleave.btsms');
            }
        };
        // recursive function to update the time that the cursor has been in the page (every 250ms)
        var updateTimer = function () {
            curTime += (pos_y > 10 || pos_y < 0) ? 250 : 0;
            if (curTime >= maxTime && pos_y >= 0) {
                $(document).off('mousemove.btsms');
                $(document).on('mouseleave.btsms', leaveFromTop);
                console.log('exit intent now active, curTime=' + curTime);
            } else {
                setTimeout(updateTimer, 250);
            }
        };
        // resets the timer when the cursor has entered the document and the pos_y variable hasn't been set (-1)
        var resetTimer = function () {
            curTime *= (pos_y < 0) ? 0 : 1;
            $(document).off('mouseenter.btsms');
            console.log('resetTimer, curTime=' + curTime);
        };
        // updates the position of the cursor in the Y axis
        var setPosition = function (e) {
            if (pos_y < 0 && e.clientY < 10) {
                curTime = 0;
            }
            pos_y = e.clientY;
            console.log('setPosition, curTime=' + curTime);
        };
        console.log('bind exit intent');
        $(document).on('mouseenter.btsms', resetTimer);
        $(document).on('mousemove.btsms', setPosition);
        updateTimer();
    };
    /**
     * detects the idle time to show the "attention grabber" message
     */
    var bindIdleTime = function () {
        var curTime = 0;

        var resetTimer = function () {
            clearTimeout(curTime);
            curTime = setTimeout(function () {
                showMessage(false, false);
                $(document).off('mousemove.btsms click.btsms keypress.btsms');
            }, timeOut * 1000);
        };

        $(document).on('mousemove.btsms click.btsms keypress.btsms', resetTimer);
    };
    /**
     * Verifies the action required (Rule) to show the message -- Only if broser is not IE <= v7 ---
     */
    var bindRule = function () {
        if (detectIEVersion() >= 8) {
            switch (rule) {
                case 'exit_intent':
                    bindExitIntent();
                    break;
                case 'attn_grabber':
                    bindIdleTime();
                    break;
                case 'greeter':
                    setTimeout(function () {
                        showMessage(false, false);
                    }, timeOut * 1000);
                    break;
                case 'always_on':
                    showMessage(false, false);
                    break;
                default:
                    break;
            }
        }
    };
    /**
     * Check if the browser is IE and if so, returns its version or else "99"
     */
    var detectIEVersion = function () {
        var ver = 99;
        if (navigator.appName === 'Microsoft Internet Explorer') {
            var ua = navigator.userAgent;
            var re = new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");
            if (re.exec(ua) !== null)
                ver = parseFloat(RegExp.$1);
        }
        return ver;
    };

    appendDiv[messageType]();
    console.log("smartmessaging loaded");

    // public custom method to be called when getting post.messages data from an iframe page (for "follow" and "close" events)
    var _public = {
        custom_event: function (event) {
            if (event.data === 'follow') {
                trackSmsEvents('follow', variantId);
                setTimeout(function () {
                    hideOnFollow();
                    var frm = document.getElementById('_bt_sms_custom_iframe');
                    frm.contentWindow.postMessage('OK', '*');
                }, 99);
            } else if (event.data === 'close') {
                showMessage(true, false);
            }
        }
    };
    return _public;
})();

// We need to add an event listener to get the message from the iframe page
if (window.addEventListener) {
    addEventListener("message", BT_SmartMessaging.custom_event, false);
} else if (window.attachEvent) {
    window.attachEvent("message", BT_SmartMessaging.custom_event, false);
}