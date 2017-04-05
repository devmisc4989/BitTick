var BT_eventTracker = (function () {
    var $ = window.BTJQuery; // jQuery reference
    var self = this; // BT_eventTracker object (this object)
    self.bt = (typeof (_bt) === 'object') ? _bt : null; // the GS _bt object
    self.batchTimeout = false; // reference to the timeout function to send the batch to gs.js
    self.lastRequestTime = 0; // the timestamp of the latest request to gs.js
    self.timeFrame = 15; // the time block expressed in seconds
    self.totalTIme = null; // The time that the user has been active on the page.
    self.events = []; // Batch of events to be sent to gs.js every <timeFrame> seconfs
    self.deferredJson = false; // array to store data for dom triggered activarions (deferred impressions and click goals)

    // Handles the batch of events and when to make a request to gs.js
    self.eventBatch = {
        // Bind a "leave page" event to call the "sendBatch" function inmediately
        init: function () {
            var firstRequest = 2; // Time in seconds of the first request
            $(window).on('beforeunload', function () {
                clearTimeout(self.batchTimeout);
                return self.eventBatch.sendBatch(0, false, true);
            });
            self.eventBatch.sendBatch(firstRequest, true, false);
            self.deferredJson = self.bt.getConditionalActivationData();
        },
        // verifies if there are at least one element in the events batch, if so, call the corresponding gs.js function to track events
        sendBatch: function (tout, recursive, unload) {
            self.batchTimeout = setTimeout(function () {
                if (self.events.length > 0) {
                    var evt = self.events;
                    self.events = [];
                    self.lastRequestTime = Math.round((new Date).getTime() / 1000);

                    // The flag "unload" is to wait for a response from the gs.js function before leaving the page
                    if (unload) {
                        return self.bt.trackEvents(evt);
                    } else {
                        self.bt.trackEvents(evt);
                    }
                }

                if (recursive) {
                    var timeout = self.totalTIme === null || self.totalTIme >= 15 ? self.timeFrame : 5;
                    self.eventBatch.sendBatch(timeout, true, false);
                }
            }, tout * 1000);
        }
    };

    // This sub-object tracks only the TIMEONPAGE goal
    self.TIMEONPAGE = {
        startTime: Math.round((new Date).getTime() / 1000), // Unix epoch elapsed time in seconds (similar to php microtime)

        // Initializes the args array with CV = 1 (1 second minimum time on page).
        init: function ($parameters) {
            self.totalTIme = 1;
            self.lastRequestTime = self.TIMEONPAGE.startTime;
            var cid = $parameters.data("eventtracker_cid");
            var cgid = $parameters.data("eventtracker_cgid");
            var lpid = $parameters.data("eventtracker_lpid");
            self.TIMEONPAGE.addArgumentsToBatch(cid, cgid, lpid);

            // after one second, starts capturing any user activity
            setTimeout(function () {
                self.TIMEONPAGE.bindUserActivity(cid, cgid, lpid);
            }, 1000);
        },
        // Adds a new entry in the events batch when it is not set yet (for TIMEONPAGE tracking
        addArgumentsToBatch: function (cid, cgid, lpid) {
            var args = {
                'cid': cid,
                'cgid': cgid,
                'lpid': lpid,
                'cv': self.totalTIme,
                't': 'c'
            };
            self.events.push(args);
            self.bt.dblog("time: " + self.totalTIme + " (lpid)");
        },
        // Detects any user activity on the page to update the events arguments
        bindUserActivity: function (cid, cgid, lpid) {
            $(document).on('scroll.bttime resize.bttime mousemove.bttime mousedown.bttime keypress.bttime tap.bttime taphold.bttime swipe.bttime', function (e) {
                e.stopPropagation();
                $(document).off('scroll.bttime resize.bttime mousemove.bttime mousedown.bttime keypress.bttime tap.bttime taphold.bttime swipe.bttime');

                var inEvents = false;
                var curTime = Math.round((new Date).getTime() / 1000);
                self.totalTIme = curTime - self.TIMEONPAGE.startTime;

                // Update the corresponding entry in the batch of events with the exact elapsed time in seconds
                $.each(self.events, function (ind, event) {
                    if (event.cid === cid && event.cgid === cgid && event.lpid === lpid) {
                        inEvents = true;
                        self.events[ind].cv = self.totalTIme;
                    }
                });

                // if the events batch is empty, add a new entry with the corresponding data
                if (!inEvents) {
                    self.TIMEONPAGE.addArgumentsToBatch(cid, cgid, lpid);
                }

                // if the last request time was made more that <timeframe> seconds (15) ago, makes a request inmediately
                var tf = self.totalTIme >= 15 ? parseInt(self.timeFrame) : 5;
                if (parseInt(curTime - self.lastRequestTime) > tf) {
                    clearTimeout(self.batchTimeout);
                    self.eventBatch.sendBatch(0, true, false);
                }

                // Calls itself recursively (after 1 second to avoid overload (tracking everytime the user interacts with the page)
                setTimeout(function () {
                    self.TIMEONPAGE.bindUserActivity(cid, cgid, lpid);
                }, 1000);
            });
        }
    };

    // This sub-object handles the TEASERTEST VIEWS goals
    self.TT_VIEWS = {
        visibleElements: [], // Array with the ID of the visible elements that has been tracked already
        totalElements: $('._bt_tt').length, // The toal number of element to check for (to clear the interval function when all elements has been displayed.

        // calls the element visibility check function at page load, whenever the visitor scrolls/resizes the window or when new content is loaded
        init: function ($parameters) {
            self.TT_VIEWS.checkElementVisibility($parameters);
            $(window).on('DOMContentLoaded.btview load.btview resize.btview scroll.btview', function () {
                self.TT_VIEWS.checkElementVisibility($parameters);
            });
        },
        // For every element with class "_bt_tt", checks if it is visible on page load or if it gets visible on run time to add a new entry to the batch
        checkElementVisibility: function ($parameters) {
            $('._bt_tt').each(function () {
                var ids = $(this).attr('class').split('-');
                var lpid = parseInt(ids[3]);
                var disp = $(this).css('display').toUpperCase();

                if (disp !== 'NONE' && $.inArray(lpid, self.TT_VIEWS.visibleElements) < 0 && self.TT_VIEWS.inViewPort($(this))) {
                    self.TT_VIEWS.visibleElements.push(lpid);
                    self.TT_VIEWS.addArgumentsToBatch($parameters, lpid);
                }
            });
        },
        // Verfies if the given element is in the visible area of the page.
        inViewPort: function ($elem) {
            var wH, wW;
            var pos = $elem.offset();
            var wX = $(window).scrollLeft(), wY = $(window).scrollTop();
            var oH = $elem.outerHeight(), oW = $elem.outerWidth();

            if (document.compatMode === 'BackCompat') {
                wH = document.body.clientHeight;
                wW = document.body.clientWidth;
            } else {
                wH = document.documentElement.clientHeight;
                wW = document.documentElement.clientWidth;
            }

            if (pos.left >= wX && pos.top >= wY && oW + pos.left <= wX + wW && oH + pos.top <= wY + wH) {
                return true;
            }
            return false;
        },
        // Adds a new entry in the events batch when an element is visible on page load or gets visible on run time.
        addArgumentsToBatch: function ($parameters, lpid) {
            var excluded = parseInt($parameters.data("eventtracker_lpid_exclude"));
            if (lpid !== excluded) {
                var args = {
                    'cid': $parameters.data("eventtracker_cid"),
                    'lpid': lpid,
                    't': 'i'
                };
                self.events.push(args);
                self.bt.dblog("viewable: " + lpid);
            }
        }
    };

    // handles the TEASERTEST CLICKS goal - verifies each of the "teaser tests" elements to track "click" events on them when applicable
    self.TT_CLICKS = {
        init: function ($parameters) {
            var excluded = parseInt($parameters.data("eventtracker_lpid_exclude"));

            $('._bt_tt').each(function () {
                var ids = $(this).attr('class').split('-');
                var lpid = parseInt(ids[3]);

                if (lpid !== excluded) {
                    var etype = $(this)[0].tagName.toUpperCase();
                    var $elem = (etype !== 'A' && $(this).find('a').length > 0) ? $(this).find('a').first() : $(this);

                    $elem.on('click.btclick', function (e) {
                        e.stopPropagation();
                        $(this).off('click.btclick');

                        self.bt.bufferTeasertestExclusionId($parameters.data("eventtracker_cid"), lpid); // buffer the id so it will be passed to the bto/d/ webservice

                        var args = {
                            'cid': $parameters.data("eventtracker_cid"),
                            'cgid': $parameters.data("eventtracker_cgid"),
                            'lpid': lpid,
                            'cv': 1,
                            't': 'c'
                        };
                        self.events.push(args);
                        self.bt.dblog("my click: " + lpid);
                    });
                }
            });
        }
    };

    // For VISUAL tests, this tracks the CLICK event on a selected element based on the user configuration
    self.CLICKS = {
        init: function (obj) {
            var $element = $(obj.selector);
            var etype = $element[0].tagName.toUpperCase();
            var isLinkElement = false;

            // If the element is a link <a> we detach the "normal" click event and call the "trackConversion" function manually.
            if (etype === 'A' && $element.attr('btattached')) {
                self.bt.dblog('Attach GOAL Click event and detach "normal" click event for link: ' + $element.attr('href'));
                self.bt.detachClickEvent($element[0]);
                isLinkElement = true;
            }

            $element.on('click.btclick', function (e) {
                e.stopPropagation();
				
                $(this).off('click.btclick');
				var This = this;
				
                var args = {
                    'cid': obj.collectionid,
                    'cgid': obj.goalid,
                    'lpid': obj.landingpageid,
                    'cv': 1,
                    't': 'c'
                };
                self.events.push(args);

                self.bt.dblog("CLICK: {" + obj.selector + '},  LPID: ' + obj.landingpageid);
				
                if (isLinkElement) {
					simulateClick(This, 100, obj);
                    return (self.bt.trackEvents(self.events));
                } else {
					simulateClick(This, 200, obj);					
                    return self.eventBatch.sendBatch(0, true, true);
                }
            });
        }
    };

    self.eventBatch.init();

    // If the DIV with the event tracker arguments has been set correctly gets the handler and calls the corresponding method
    if ($('._bt_eventtracker_arg').length > 0 && self.bt !== null && typeof (self.bt.trackEvents) !== 'undefined') {
        $('._bt_eventtracker_arg').each(function () {
            var handler = $(this).data("eventtracker_handler");

            if (typeof (self[handler]) !== 'undefined') {

                // This is not to track conversions twice on TT elements (All of them, TT_VIEWS, TT_CLICKS, even "excluded" elements)
                $('._bt_tt').each(function () {
                    var etype = $(this)[0].tagName.toUpperCase();
                    var $elem = $(this);

                    if (etype !== 'A') {
                        $elem = $(this).find('a').length > 0 ? $(this).find('a').first() : $(this).closest('a');
                    }

                    if (typeof ($elem[0]) !== 'undefined') {
                        self.bt.detachClickEvent($elem[0]);
                    }
                });
                self[handler].init($(this));
            }
        });
    }

	//simulate clicks
	var simulateClick = function(elm, delay, obj){
		
		var delay = delay || 100;
		var obj = obj || {selector: '', landingpageid: ''};
		
		setTimeout(function(){
			//continue event bubbling					
			center = findCenter(elm),
			x = Math.floor( center.x ),
			y = Math.floor( center.y ),
			coord = { clientX: x, clientY: y },
			self.bt.dblog("SIMULATE CLICK: {" + obj.selector + '},  LPID: ' + obj.landingpageid + ', COORDS: ' + coord.clientX + 'x' + coord.clientY);					
			$(elm).simulate("click", coord );
		},delay);
	}
    // handle dom triggered activation
    var applyDomChanges = function (index, obj) {
        if (typeof (window["_bt"]) === 'object') {
            if(obj.type == 'project_activation') {
                window["_bt"].applyCollectionChanges($.parseJSON(obj.dom_code));
                var args = {
                    'cid': obj.collectionid,
                    'lpid': obj.landingpageid,
                    't': 'i'
                };
                var events = [];
                events.push(args);
                window["_bt"].trackEvents(events);                
            }
            if(obj.type == 'CLICKS') {
                self.CLICKS.init(obj);
            }
        }
    };

    var bindSelectorAction = function (index, obj) {
        var interval = setInterval(function () {
            var match = false;
            if(obj.action === 'is_visible') {
                if($(obj.selector).is(':visible'))
                    match = true;
            }
            if(obj.action === 'exists') {
                if($(obj.selector).length > 0)
                    match = true;
            }
            if(obj.action === 'expression_true') {
                myExpression = obj.selector;
                try {
                    result = eval(myExpression);
                    if(result) {
                        match = true;
                    }
                }
                catch (e) {
                    if (e instanceof SyntaxError) {
                        console.log("Syntax Error: " + myExpression);
                        clearInterval(interval);
                    }
                }
            }
            if (match) {
                clearInterval(interval);
                applyDomChanges(index, obj);
            }
        }, 50);
    };
    if (typeof (self.deferredJson) === 'object') {
        $.each(self.deferredJson, function (index, obj) {
            if (obj.action !== 'not_used') {
                bindSelectorAction(index, obj);
            } else {
                applyDomChanges(index, obj);
            }
        });
    }
	
	/** helper functions for simulate**/	
	function findCenter( elem ) {
		var offset,
			document = $( elem.ownerDocument );
		elem = $( elem );
		offset = elem.offset();
	
		return {
			x: offset.left + elem.outerWidth() / 2 - document.scrollLeft(),
			y: offset.top + elem.outerHeight() / 2 - document.scrollTop()
		};
	}	
	 /*!
	 * jQuery Simulate v0.0.1 - simulate browser mouse and keyboard events
	 * https://github.com/jquery/jquery-simulate
	 *
	 * Copyright 2012 jQuery Foundation and other contributors
	 * Released under the MIT license.
	 * http://jquery.org/license
	 *
	 * Date: Sun Dec 9 12:15:33 2012 -0500
	 */
	
	;(function( $, undefined ) {
		var rkeyEvent = /^key/,
			rmouseEvent = /^(?:mouse|contextmenu)|click/;
		
		$.fn.simulate = function( type, options ) {
			return this.each(function() {
				new $.simulate( this, type, options );
			});
		};
		
		$.simulate = function( elem, type, options ) {
			var method = $.camelCase( "simulate-" + type );
		
			this.target = elem;
			this.options = options;
		
			if ( this[ method ] ) {
				this[ method ]();
			} else {
				this.simulateEvent( elem, type, options );
			}
		};
		
		$.extend( $.simulate, {
		
			keyCode: {
				BACKSPACE: 8,
				COMMA: 188,
				DELETE: 46,
				DOWN: 40,
				END: 35,
				ENTER: 13,
				ESCAPE: 27,
				HOME: 36,
				LEFT: 37,
				NUMPAD_ADD: 107,
				NUMPAD_DECIMAL: 110,
				NUMPAD_DIVIDE: 111,
				NUMPAD_ENTER: 108,
				NUMPAD_MULTIPLY: 106,
				NUMPAD_SUBTRACT: 109,
				PAGE_DOWN: 34,
				PAGE_UP: 33,
				PERIOD: 190,
				RIGHT: 39,
				SPACE: 32,
				TAB: 9,
				UP: 38
			},
		
			buttonCode: {
				LEFT: 0,
				MIDDLE: 1,
				RIGHT: 2
			}
		});
		
		$.extend( $.simulate.prototype, {
		
			simulateEvent: function( elem, type, options ) {
				var event = this.createEvent( type, options );
				this.dispatchEvent( elem, type, event, options );
			},
		
			createEvent: function( type, options ) {
				if ( rkeyEvent.test( type ) ) {
					return this.keyEvent( type, options );
				}
		
				if ( rmouseEvent.test( type ) ) {
					return this.mouseEvent( type, options );
				}
			},
		
			mouseEvent: function( type, options ) {
				var event, eventDoc, doc, body;
				options = $.extend({
					bubbles: true,
					cancelable: (type !== "mousemove"),
					view: window,
					detail: 0,
					screenX: 0,
					screenY: 0,
					clientX: 1,
					clientY: 1,
					ctrlKey: false,
					altKey: false,
					shiftKey: false,
					metaKey: false,
					button: 0,
					relatedTarget: undefined
				}, options );
		
				if ( document.createEvent ) {
					event = document.createEvent( "MouseEvents" );
					event.initMouseEvent( type, options.bubbles, options.cancelable,
						options.view, options.detail,
						options.screenX, options.screenY, options.clientX, options.clientY,
						options.ctrlKey, options.altKey, options.shiftKey, options.metaKey,
						options.button, options.relatedTarget || document.body.parentNode );
		
					// IE 9+ creates events with pageX and pageY set to 0.
					// Trying to modify the properties throws an error,
					// so we define getters to return the correct values.
					if ( event.pageX === 0 && event.pageY === 0 && Object.defineProperty ) {
						eventDoc = event.relatedTarget.ownerDocument || document;
						doc = eventDoc.documentElement;
						body = eventDoc.body;
		
						Object.defineProperty( event, "pageX", {
							get: function() {
								return options.clientX +
									( doc && doc.scrollLeft || body && body.scrollLeft || 0 ) -
									( doc && doc.clientLeft || body && body.clientLeft || 0 );
							}
						});
						Object.defineProperty( event, "pageY", {
							get: function() {
								return options.clientY +
									( doc && doc.scrollTop || body && body.scrollTop || 0 ) -
									( doc && doc.clientTop || body && body.clientTop || 0 );
							}
						});
					}
				} else if ( document.createEventObject ) {
					event = document.createEventObject();
					$.extend( event, options );
					// standards event.button uses constants defined here: http://msdn.microsoft.com/en-us/library/ie/ff974877(v=vs.85).aspx
					// old IE event.button uses constants defined here: http://msdn.microsoft.com/en-us/library/ie/ms533544(v=vs.85).aspx
					// so we actually need to map the standard back to oldIE
					event.button = {
						0: 1,
						1: 4,
						2: 2
					}[ event.button ] || event.button;
				}
		
				return event;
			},
		
			keyEvent: function( type, options ) {
				var event;
				options = $.extend({
					bubbles: true,
					cancelable: true,
					view: window,
					ctrlKey: false,
					altKey: false,
					shiftKey: false,
					metaKey: false,
					keyCode: 0,
					charCode: undefined
				}, options );
		
				if ( document.createEvent ) {
					try {
						event = document.createEvent( "KeyEvents" );
						event.initKeyEvent( type, options.bubbles, options.cancelable, options.view,
							options.ctrlKey, options.altKey, options.shiftKey, options.metaKey,
							options.keyCode, options.charCode );
					// initKeyEvent throws an exception in WebKit
					// see: http://stackoverflow.com/questions/6406784/initkeyevent-keypress-only-works-in-firefox-need-a-cross-browser-solution
					// and also https://bugs.webkit.org/show_bug.cgi?id=13368
					// fall back to a generic event until we decide to implement initKeyboardEvent
					} catch( err ) {
						event = document.createEvent( "Events" );
						event.initEvent( type, options.bubbles, options.cancelable );
						$.extend( event, {
							view: options.view,
							ctrlKey: options.ctrlKey,
							altKey: options.altKey,
							shiftKey: options.shiftKey,
							metaKey: options.metaKey,
							keyCode: options.keyCode,
							charCode: options.charCode
						});
					}
				} else if ( document.createEventObject ) {
					event = document.createEventObject();
					$.extend( event, options );
				}
		
				if ( !!/msie [\w.]+/.exec( navigator.userAgent.toLowerCase() ) || (({}).toString.call( window.opera ) === "[object Opera]") ) {
					event.keyCode = (options.charCode > 0) ? options.charCode : options.keyCode;
					event.charCode = undefined;
				}
		
				return event;
			},
		
			dispatchEvent: function( elem, type, event ) {
				if ( elem.dispatchEvent ) {
					elem.dispatchEvent( event );
				} else if ( elem.fireEvent ) {
					elem.fireEvent( "on" + type, event );
				}
			},
		
			simulateFocus: function() {
				var focusinEvent,
					triggered = false,
					element = $( this.target );
		
				function trigger() {
					triggered = true;
				}
		
				element.bind( "focus", trigger );
				element[ 0 ].focus();
		
				if ( !triggered ) {
					focusinEvent = $.Event( "focusin" );
					focusinEvent.preventDefault();
					element.trigger( focusinEvent );
					element.triggerHandler( "focus" );
				}
				element.unbind( "focus", trigger );
			},
		
			simulateBlur: function() {
				var focusoutEvent,
					triggered = false,
					element = $( this.target );
		
				function trigger() {
					triggered = true;
				}
		
				element.bind( "blur", trigger );
				element[ 0 ].blur();
		
				// blur events are async in IE
				setTimeout(function() {
					// IE won't let the blur occur if the window is inactive
					if ( element[ 0 ].ownerDocument.activeElement === element[ 0 ] ) {
						element[ 0 ].ownerDocument.body.focus();
					}
		
					// Firefox won't trigger events if the window is inactive
					// IE doesn't trigger events if we had to manually focus the body
					if ( !triggered ) {
						focusoutEvent = $.Event( "focusout" );
						focusoutEvent.preventDefault();
						element.trigger( focusoutEvent );
						element.triggerHandler( "blur" );
					}
					element.unbind( "blur", trigger );
				}, 1 );
			}
		});
	})( window.BTJQuery );
})();