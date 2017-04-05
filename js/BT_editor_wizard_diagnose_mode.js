// single global object to store variable (prevents collisions)
var diagnoseVar = {
    popunderWin: null, // contents the window (popup object) so it can be accesable from anywhere at anytime
    completeUrl: null, //  Will contain the entire URL (i.e: http://example.com?_trt=true&tracecode=xxxx
    originalUrl: null //  Will contain the original URL as entered by the user
};

// Opens the Diagnose mode popup in a new fancybox window
var OpenDiagnoseMode = function(back) {
    closePupunderWin();
    $('#step-enter-url .ctrl-buttons').css('visibility', 'visible');
    $('html').animate({
        scrollTop: 0
    }, 50, function() {
        OpenPopup('#step-enter-url', true, null, {
            onComplete: function() {
                backAndClose();

                $('#frm-test-diagnose').submit(function(e) {
                    return false;
                });

                if (!back)
                    validateUrl();
            }
        });
    });
};

// Before the form submit (test it now) checks if there is something in the URL (validationEngine [reuired])
var validateUrl = function() {
	var path= login_msg["base_ssl_url"];
    $("#frm-test-diagnose").validationEngine({
        onValidationComplete: function(form, status) {
            if (status) {
                $('#step-enter-url .ctrl-buttons').css('visibility', 'hidden');
                $.ajax({
                    type: "POST",
                    url: path+"lpc/createTracecode",
                    dataType: 'text'
                }).done(function(res) {
                    showNextStep(res);
                }).fail(function(jqXHR, textStatus) {
                    fancyboxClose();
                });
            }
        }
    });
};

// This function gets the tracecode from the controller, adds the href attribute to the link,
// open the "next step" fancybox with the message "your trace code has been created"
// and shows the button to open the popup that will load the url to test
var showNextStep = function(res) {
	var path= login_msg["base_ssl_url"];
    diagnoseVar.originalUrl = $('#diagnose-page-url').val();
    var urlVar = (diagnoseVar.originalUrl.indexOf('?') > 0) ? '&' : '?';
    diagnoseVar.completeUrl = diagnoseVar.originalUrl + urlVar + '_trt=true&tracecode=' + res;

    // unbinds click to prevent "multiple - same" event
    $('#popup-loader').unbind('click');

    // Opens the fancybox with the message "we have created your trace code, now you may proceed"
    OpenPopup('#step-tracecode-created', true, null, {
        onComplete: function() {
            backAndClose();

            // When the user clicks on the "Test it now" button it opens the url in a pop-under
            $('#popup-loader').click(function(e) {

                // Shows the loading pop-up
                showLoader();

                e.preventDefault();
                e.stopPropagation();
                makePopunder(diagnoseVar.completeUrl, false);

                // 1 second timeout to wait for the popup to load and call the bto controller.
                setTimeout(function() {
                    $('.issue-depend, .status-depend, .match-depend, .etpage-depend, .url-depend, .matching-depend, .delivery-depend').css('display', 'none');
                    $.ajax({
                        type: "POST",
                        url: login_msg["base_ssl_url"]+"lpc/trt",
                        dataType: 'json',
                        data: {
                            tracecode: res,
                            collectionid: collectionid
                        }
                    }).done(function(res) {
                        // closes the pop-under and calls the method to handle the response
                        closePupunderWin();
                        diagnoseModeResponse(res);
                    }).fail(function(jqXHR, textStatus) {
                        fancyboxClose();
                    });
                }, 1000);
            });
        }
    });
};

// Handles the response from the lpc/trt controller to call the apropriate method
var diagnoseModeResponse = function(res) {
    switch (res.issue) {
        case 0:
            // if issue = 0 shows the result match pop up
            showResultMatch(res);
            break;
        case 1:
            // if issue = 1 shows the monthly quota and alternatively the reset date.
            $('#issue-1').fadeIn(0);
            $('#status-' + res.client_status).fadeIn(0);
            $('#status-' + res.client_status + ' .quota')[0].innerHTML = res.quota;
            if (res.client_status === '6') {
                var resDate = getResetDate(res.quota_reset_date);
                $('.reset-date')[0].innerHTML = resDate;
            }
            showResultSimple();
            break;
        case 2: // no active tests
            $('#issue-2').fadeIn(0);
            showResultSimple();
            break;
        case 5: // no tracking code found
            $('#issue-5').fadeIn(0);
            showResultSimple();
            break;
        case 6:
            // if issue = 6 shows the tested client code and the current logged client code
            $('#client-code-tested')[0].innerHTML = res.clientcode_tested;
            $('#client-code-login')[0].innerHTML = res.clientcode_login;
            $('#issue-6').fadeIn(0);
            showResultSimple();
            break;
        default:
            fancyboxClose();
            break;
    }
};

// shows the apropriate div into the fancybox window (RESULT SIMPLE)
var showResultSimple = function(issue, status, quota, reset) {
    OpenPopup('#step-result-simple', true, null, {
        onComplete: function() {
            backAndClose();
        }
    });
};

// shows the apropriate div into the fancybox window (RESULT MATCH)
var showResultMatch = function(res) {
    var matchName = '';

    // shows the divs that depends on the et_pagename response value if this is != 'NA':
    if (res.et_pagename !== 'NA') {
        $('.etpage-depend').fadeIn(0);
        $('.et-pagename-content')[0].innerHTML = res.et_pagename;
    } else {
        $('.url-depend').fadeIn(0);
    }

    // if the response includes matching tests, this method shows a table with the name, the url and the match status (match, no match)
    if (res.matching_tests.length > 0) {
        $('.matching-depend').fadeIn(0);
        var htm = '<tr>';
        htm += '    <th>' + $('#table-testname').val() + '</th>';
        htm += '    <th>' + $('#table-testpage').val() + '</th>';
        htm += '    <th>' + $('#table-result').val() + '</th>';
        htm += '</tr>';
        for (var i = 0; i < res.matching_tests.length; i++) {
        	
            var result = $('#table-nomatch').val();
            if (res.matching_tests[i][2] !== 0) {
                if(res.matching_tests[i][4] !== 0) {
                    result = $('#table-match-conflict').val();
                    if(res.matching_tests[i][4] == 1) {
                        result = result + "<br>" + $('#table-conflict-sms').val();
                    }                 
                    if(res.matching_tests[i][4] == 2) {
                        result = result + "<br>" + $('#table-conflict-split').val();
                    }                 
                }
                else {
                    result = $('#table-match').val();                                        
                }
                matchName += (matchName.length === 0) ? res.matching_tests[i][0] : '';
            }
            

            var urls = '';
            try {
                var urlArray = res.matching_tests[i][1];
                $.each(urlArray, function (ind, value) {
                    if (value.mode === 'include') {
                        urls += urls !== '' ? '<br />' : '';
                        urls += ('<span style="color:green">' + value.url + '</span>');
                    }
                    if (value.mode === 'exclude') {
                        urls += urls !== '' ? '<br />' : '';
                        urls += ('<span style="color:red">' + value.url + '</span>');
                    }
                });
            } catch (e) {
                urls = res.matching_tests[i][1];
            }

            htm += '<tr>';
            htm += '    <td>' + res.matching_tests[i][0] + '</td>';
            htm += '    <td>' + urls + '</td>';
            htm += '    <td>' + result + '</td>';
            htm += '</tr>';
        }
        $('#checked-table').empty().append(htm);
    }

    // shows the tested URL under the title "page url for the match"
    $('.page-url-content')[0].innerHTML = diagnoseVar.originalUrl;
    $('#match-' + res.match).fadeIn(0);
    $('#delivery-' + res.delivery_status).fadeIn(0);

    // Adds the test name to the "strong" element when needed
    if ($('#match-' + res.match + ' .test-name').length > 0)
        $('#match-' + res.match + ' .test-name')[0].innerHTML = '&quot;' + matchName + '&quot;';

    if ((parseInt(res.match) === 1 || parseInt(res.match) === 2) && (parseInt(res.issue) === 3 || parseInt(res.issue) === 4)) {
        $('#issue-' + res.issue).fadeIn(0);
    }

    // shows the step-result-match pop up
    OpenPopup('#step-result-match', true, null, {
        onComplete: function() {
            backAndClose();
        }
    });
};

// Returns the quota reset date with format dd.mm.yyy
var getResetDate = function(day) {
    var curdate = new Date();
    var today = curdate.getDate();
    var month = curdate.getMonth() + 1;  // January would be 0 so... +1
    var year = curdate.getFullYear();

    // if today's date is greater than the quota_reset_day then it will be the next month
    if (today > day) {
        month = (month < 12) ? (month + 1) : 1;
        year = (month <= 1) ? (year + 1) : year;
    }
    // the 0 ... slice(-2) is to conver 1 to 01 and/or 17 remains the same.
    return ('0' + day).slice(-2) + '.' + ('0' + month).slice(-2) + '.' + year;
};

// Check for a click on back.btn (to go back) or close-btn (to close the fancybox window).
var backAndClose = function() {
    $('.back-btn').click(function() {
        OpenDiagnoseMode(true);
    });
    $('.close-btn').click(function() {
        fancyboxClose();
        return false;
    });
};

// Closes the Fancybox popup and reloads the entire page
var fancyboxClose = function() {
    $.fancybox.close();
    window.location.reload();
};

// If it is defined closes the popup window
var closePupunderWin = function() {
    if (diagnoseVar.popunderWin && diagnoseVar.popunderWin !== null) {
        diagnoseVar.popunderWin.close();
        diagnoseVar.popunderWin = null;
    }
};

// Shows the loading... popup while we wait for the ajax response
var showLoader = function() {
    OpenPopup('#step-diagnose-wait', true, null, null);
};

// Taken from http://www.sitepoint.com/forums/showthread.php?1040778-Popunder-that-works-well-for-all-browser and modified
// Add a "popunder" functionality that works in all browsers
var makePopunder = function(pUrl) {
    var self = this;
    var _parent = self;
    var mypopunder = null;
    var pName = (Math["floor"]((Math["random"]() * 1000) + 1));

    // Detecs the browser
    var browser = function() {
        var n = navigator["userAgent"]["toLowerCase"]();
        var b = {
            webkit: /webkit/ ["test"](n),
            mozilla: (/mozilla/ ["test"](n)) && (!/(compatible|webkit)/ ["test"](n)),
            chrome: /chrome/ ["test"](n),
            msie: (/msie/ ["test"](n)) && (!/opera/ ["test"](n)),
            firefox: /firefox/ ["test"](n),
            safari: (/safari/ ["test"](n) && !(/chrome/ ["test"](n))),
            opera: /opera/ ["test"](n)
        };
        b["version"] = (b["safari"]) ? (n["match"](/.+(?:ri)[\/: ]([\d.]+)/) || [])[1] : (n["match"](/.+(?:ox|me|ra|ie)[\/: ]([\d.]+)/) || [])[1];
        return b;
    }();

    // creates the popup and assign it to the variable globalvar.popunderWin so we can close it later
    self.doPopunder = function() {
        var sOptions = "toolbar=no,scrollbars=no,location=no,statusbar=no,menubar=no,resizable=0,width=160,height=90";
        mypopunder = _parent["window"]["open"](pUrl, pName, sOptions);
        if (mypopunder) {
            pop2under();
            diagnoseVar.popunderWin = mypopunder;
        }
    };

    // Depending on the browser tries to lose focus from the popup and focus the main window
    self.pop2under = function() {
        try {
            mypopunder["blur"]();
            mypopunder["opener"]["window"]["focus"]();
            window["self"]["window"]["blur"]();
            window["focus"]();
            if (browser["firefox"]) {
                openCloseWindow();
            }
            if (browser["webkit"]) {
                openCloseTab();
            }
        } catch (e) {
        }
    };

    // If the browser is firefox it opens and closes a new popup "about:blank" so the main window can get the focus
    self.openCloseWindow = function() {
        var ghost = window["open"]("about:blank");
        ghost["focus"]();
        ghost["close"]();
    };

    // I the user agent is webkit it opens a new window then it closes it and focus on the main window
    self.openCloseTab = function() {
        var ghost = document["createElement"]("a");
        ghost["href"] = "about:blank";
        ghost["target"] = "PopHelper";
        document["getElementsByTagName"]("body")[0]["appendChild"](ghost);
        ghost["parentNode"]["removeChild"](ghost);
        var clk = document["createEvent"]("MouseEvents");
        clk["initMouseEvent"]("click", true, true, window, 0, 0, 0, 0, 0, true, false, false, true, 0, null);
        ghost["dispatchEvent"](clk);
        window["open"]("about:blank", "PopHelper")["close"]();
    };

    // This line calls the main popup method to actually open the new window
    doPopunder(pUrl);
};