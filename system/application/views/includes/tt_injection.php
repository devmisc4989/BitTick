<?php
echo '!function(t){var e="",n=!1,a="";elementHasText=function(t){var n=t.text().replace(/\s/g,"");return n.indexOf(e)>=0?n:!1},evaluateElementChildren=function(n){var a=elementHasText(n);if(!a)return!1;var i=n.children().length>0,r=a===e;if(i&&!r)n.children().each(function(){evaluateElementChildren(t(this))});else if(i&&r){var l=!1;n.children().each(function(){return elementHasText(t(this),e)?(l=!0,evaluateElementChildren(t(this))):void 0}),l||addCustomSpanTag(n,!0)}else r&&addCustomSpanTag(n,!1)},addCustomSpanTag=function(i,r){if(r){var l=!1,s=[],c="",h="",d=0;if(i.contents().each(function(){var n=this.nodeValue;if(elementHasText(t(this)))return t(this).replaceWith(\'<span class="_bt_tt_auxspan">\'+n+"</span>"),l=!1,!1;var a=t(this).text().replace(/\s/g,"");if(null!==a&&""!==a&&e.indexOf(a)>=0)if(s.push(d),3===this.nodeType)c+=n;else{var i=document.createElement("DIV"),r=document.createElement(this.nodeName);r.innerHTML=t(this).html(),i.appendChild(r),c+=i.innerHTML}return h=_bt.tt_getTextOnly(c),h.indexOf(e)>=0?(l=!0,!1):void d++}),l){var o;i.contents().each(function(e){o=t.inArray(e,s),0===o?t(this).replaceWith(\'<span class="_bt_tt_auxspan">\'+c+"</span>"):o>0&&t(this).remove()})}}else if(!i.hasClass("_bt_tt_auxspan")&&i.find(".bt_tt_auxspan").length<=0){var u=\'<span class="_bt_tt_auxspan">\'+i.text()+"</span>";i.html(u)}i=i.find("._bt_tt_auxspan");var f=_bt.tt_wrapElement(i,collectionid,n.id,a);f.fadeIn(0)},init=function(){try{_bt.tt_createStyle();var i=t.parseJSON(injectionJson);t.each(i,function(i,r){return t("body").children().length<=0?!1:(e=_bt.tt_getTextOnly(r.ctrl),n=r,a=r.vrnt?r.vrnt:r.ctrl,void t("body").children().each(function(){evaluateElementChildren(t(this))}))})}catch(r){throw new Error("Error deconding TT injection")}},init()}(window.BTJQuery||jQuery);';
return false;
?>

<script>
    (function ($) {
        var oText = "";
        var curVariant = false;
        var replaceText = "";
        // Verifies if the given element has the original text anywhere in its html content
        elementHasText = function ($e) {
            var eText = $e.text().replace(/\s/g, "");
            if (eText.indexOf(oText) >= 0) {
                return eText;
            }
            return false;
        };
        // This is a recursive method to evaluate the DOM tree looking for the given text
        evaluateElementChildren = function ($elem) {
            var elementText = elementHasText($elem);
            if (!elementText) {
                return false;
            }

            var hasChildren = $elem.children().length > 0;
            var sameText = elementText === oText;
            if (hasChildren && !sameText) {
                $elem.children().each(function () {
                    evaluateElementChildren($(this));
                });
            } else if (hasChildren && sameText) {
                var foundInChilds = false;
                $elem.children().each(function () {
                    if (elementHasText($(this), oText)) {
                        foundInChilds = true;
                        return evaluateElementChildren($(this));
                    }
                });
                if (!foundInChilds) {
                    addCustomSpanTag($elem, true);
                }
            } else if (sameText) {
                addCustomSpanTag($elem, false);
            }
        };
        // creates a custom span tag and wraps the given text in it
        addCustomSpanTag = function ($elem, siblings) {
            if (siblings) {
                var foundInNodes = false;
                var nodesFound = [];
                var builtText = "";
                var curText = "";
                var index = 0;
                $elem.contents().each(function () {
                    var orig = this.nodeValue;
                    if (elementHasText($(this))) {
                        $(this).replaceWith('<span class="_bt_tt_auxspan">' + orig + '</span>');
                        foundInNodes = false;
                        return false;
                    } else {
                        var sub = $(this).text().replace(/\s/g, "");
                        if (sub !== null && sub !== "" && oText.indexOf(sub) >= 0) {
                            nodesFound.push(index);
                            if (this.nodeType === 3) {
                                builtText += orig;
                            } else {
                                var tmp = document.createElement("DIV");
                                var node = document.createElement(this.nodeName);
                                node.innerHTML = $(this).html();
                                tmp.appendChild(node);
                                builtText += tmp.innerHTML;
                            }
                        }

                        curText = _bt.tt_getTextOnly(builtText);
                        if (curText.indexOf(oText) >= 0) {
                            foundInNodes = true;
                            return false;
                        }
                    }

                    index++;
                });
                if (foundInNodes) {
                    var pos;
                    $elem.contents().each(function (ind) {
                        pos = $.inArray(ind, nodesFound);
                        if (pos === 0) {
                            $(this).replaceWith('<span class="_bt_tt_auxspan">' + builtText + '</span>');
                        } else if (pos > 0) {
                            $(this).remove();
                        }
                    });
                }

            } else {
                if (!$elem.hasClass("_bt_tt_auxspan") && $elem.find(".bt_tt_auxspan").length <= 0) {
                    var htm = '<span class="_bt_tt_auxspan">' + $elem.text() + '</span>';
                    $elem.html(htm);
                }
            }

            $elem = $elem.find("._bt_tt_auxspan");
            var $ret = _bt.tt_wrapElement($elem, collectionid, curVariant.id, replaceText);
            $ret.fadeIn(0);
        };
        init = function () {
            try {
                _bt.tt_createStyle();
                var bt_tt_injection = $.parseJSON(injectionJson);
                $.each(bt_tt_injection, function (index, variants) {
                    if ($("body").children().length <= 0) {
                        return false;
                    }

                    oText = _bt.tt_getTextOnly(variants.ctrl);
                    curVariant = variants;
                    replaceText = variants.vrnt ? variants.vrnt : variants.ctrl;
                    $("body").children().each(function () {
                        evaluateElementChildren($(this));
                    });
                });
            } catch (e) {
                throw new Error("Error deconding TT injection");
            }
        };
        //init();
    })(window.BTJQuery || jQuery);
</script>
