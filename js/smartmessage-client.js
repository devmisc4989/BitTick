var bt_sms_client = {
    element: null,
    // before submitting or redirecting, this method calls the "follow" tracking event and waits a response to continue (see followCallback).
    follow: function(e) {
        e.preventDefault();
        e.stopPropagation();
        this.element = (e.srcElement) ? e.srcElement : e.target;
        if (window.location !== window.parent.location) {
            parent.postMessage('follow', "*");
        }
        return true;
    },
    // Gets the JS library response and checks the element to redirect (if the element is a link) or submit the form (for inputs/submits).
    followCallback: function(event) {
        var elem = bt_sms_client.element;
        switch (elem.nodeName) {
            case 'A':
                bt_sms_client.redirectLink();
                break;
            case 'INPUT':
                elem.form.setAttribute('onSubmit', '');
                elem.form.submit();
                break;
            case 'FORM':
                elem.setAttribute('onSubmit', '');
                elem.submit();
                break;
        }
    },
    // If the clicked element was a anchor <a>, redirects the top page to its href property -- hacks if browser is IE < 9 --
    redirectLink: function() {
        var nav = navigator.userAgent.toLowerCase();
        var isIE = nav.indexOf('msie') !== -1;
        var version = parseInt(nav.substr(4, 2), 10);
        if (isIE && version < 9) {
            var link = document.createElement('a');
            link.href = this.element.toString();
            link.target = '_top';
            document.body.appendChild(link);
            link.click();
        } else {
            top.location = this.element.toString();
        }
    },
    // Calls the jslibrary method to track the "close" event and hide the SMS popup
    close: function(elem) {
        elem.preventDefault();
        elem.stopPropagation();
        parent.postMessage('close', "*");
    }
};

// We need to add an event listener to get the response from the JS library
if (window.addEventListener) {
    addEventListener("message", bt_sms_client.followCallback, false);
} else if (window.attachEvent) {
    window.attachEvent("message", bt_sms_client.followCallback, false);
}