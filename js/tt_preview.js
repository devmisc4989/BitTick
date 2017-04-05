// helper functions to show preview of variants in a teaser test
;
(function ($) {
    var oText = '';
    var curVariant = false;

    // Creates the corresponding <div>s to hold the variants text
    addVariantDivs = function ($original) {
        $.each(curVariant.vrnts, function (ind, variant) {
            var $clone = $original.clone().insertAfter($original);
            var idc = '-bt-' + collectionid + '-' + variant.id;
            $clone.removeAttr('id');
            $clone.removeAttr('class');
            $clone.addClass('_bt_tt  ' + idc + '  _bt_tt_changed');

            if ($original.hasClass('_bt_tt_auxspan')) {
                $clone.text(variant.text);
            } else {
                $clone.find('._bt_tt_auxspan').text(variant.text);
            }
        });
        $original.fadeIn(0);
    };

    // Verifies if the given element has the original text anywhere in its html content
    elementHasText = function ($e) {
        var eText = $e.text().replace(/\s/g, '');
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
            var builtText = '';
            var curText = '';
            var index = 0;

            $elem.contents().each(function () {
                var orig = this.nodeValue;

                if (elementHasText($(this))) {
                    $(this).replaceWith('<span class="_bt_tt_auxspan">' + orig + '</span>');
                    foundInNodes = false;
                    return false;
                } else {
                    var sub = $(this).text().replace(/\s/g, '');
                    if (sub !== null && sub !== '' && oText.indexOf(sub) >= 0) {
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
            var htm = '<span class="_bt_tt_auxspan">' + $elem.text() + '</span>';
            $elem.html(htm);
        }

        $elem = $elem.find('._bt_tt_auxspan');
        var $original = _bt.tt_wrapElement($elem, collectionid, curVariant.ctrl.id, false);
        addVariantDivs($original);
    };

    // When the interfacetype is = 'API' we just add the custom attr and classes to the corresponding elements
    customTagForApiInterface = function (bt_tt_injection) {

        var addTags = function (idv, original) {
            $('.-bt-' + collectionid + '-' + idv).each(function () {
                $(this).addClass('_bt_tt_auxspan');
                var $link = $(this).closest('a').length > 0 ? $(this).closest('a') : $(this);

                if (typeof ($link.attr('tt_unique_attr')) === 'undefined') {
                    var uniqueAttr = '_bt_tt_' + Math.random().toString(36).substring(2);
                    $link.attr('tt_unique_attr', uniqueAttr);
                }

                if ($link !== $(this)) {
                    $(this).attr('tt_unique_attr', $link.attr('tt_unique_attr'));
                }

                if (original) {
                    $(this).fadeIn(0);
                }
            });
        };

        $.each(bt_tt_injection, function () {
            addTags(this.ctrl.id, true);
            $.each(this.vrnts, function () {
                addTags(this.id, false);
            });
        });
    };

    // injectionJson in set in view: TT preview
    init = function () {
        try {
            _bt.tt_createStyle();
            var bt_tt_injection = $.parseJSON(injectionJson);

            if (ttInterfaceType === 'API') {
                return customTagForApiInterface(bt_tt_injection);
            }

            $.each(bt_tt_injection, function (index, variants) {
                if ($('body').children().length <= 0) {
                    return false;
                }

                oText = _bt.tt_getTextOnly(variants.ctrl.text);
                curVariant = variants;

                $('body').children().each(function () {
                    evaluateElementChildren($(this));
                });
            });
        } catch (e) {
            throw new Error("Error deconding TT injection");
        }
    };

    init();
})(BTJQuery);

/* code */
(function ($) {
    if (!injectionJson) {
        console && console.log('No injectionJson found!');
        return;
    }

    var injection = $.parseJSON(window.injectionJson || injectionJson);
    var collectionid = window.collectionid || collectionid;
    var variantProcessed = false;

    //bind on domready just in case
    $(function () {
        processVariants(injection);
    });

    //use timeout as back-up
    setTimeout(function () {
        processVariants(injection);
    }, 100);

    function processVariants(lpc) {
        if (variantProcessed || !lpc) {
            return;
        }
        variantProcessed = true;

        for (var i = 0; i < lpc.length; i++) {
            var l = lpc[i];
            var $elC = $('.' + getElementClass(collectionid, l.ctrl.id));
            var hoverMenuId = getElementClass(collectionid, l.ctrl.id) + '_hover';

            //mark item
            $elC.addClass('_bt_tt_changed');
            $elC.hover(showLocalMenu, hideLocalMenu);

            $elC.data('vinfo', l.ctrl);
            $elC.attr('hover_menu', hoverMenuId);

            for (var j = 0; j < l.vrnts.length; j++) {
                var $elV = $('.' + getElementClass(collectionid, l.vrnts[j].id));

                $elV.addClass('_bt_tt_changed');
                $elV.hover(showLocalMenu, hideLocalMenu);
                $elV.data('vinfo', l.vrnts[j]);
                $elV.attr('hover_menu', hoverMenuId);
            }

            //build menu
            var $ul = $('<ul/>').attr('id', hoverMenuId).addClass('bt_variants_menu').css({'position': 'absolute', 'z-index': 999999});
            var $li = $('<li/>');
            $li.html(l.ctrl.lbl);
            $li.data('vid', getElementClass(collectionid, l.ctrl.id));
            $ul.append($li);

            for (var k = 0; k < l.vrnts.length; k++) {
                $li = $('<li/>');
                $li.html('Variant:' + l.vrnts[k].lbl);
                $li.data('vid', getElementClass(collectionid, l.vrnts[k].id));
                $ul.append($li);
            }

            $ul.find('li').click(showVariant);
            $ul.data('vis_vid', getElementClass(collectionid, l.ctrl.id));
            $('body').append($ul);
        }
    }

    function getElementClass(collectionid, id) {
        return '-bt-' + collectionid + '-' + id;
    }

    function showVariant(evt) {
        evt.stopPropagation();
        evt.preventDefault();
        var $elm = $(this);
        var $headline = $elm.closest('._bt_tt');
        var uniqueAttr = $headline.attr('tt_unique_attr');
        var vid = $elm.data('vid');

        $headline.fadeOut(0);
        $('.' + vid + '[tt_unique_attr=' + uniqueAttr + ']').fadeIn(0);
        $elm.parent().data('vis_vid', vid);
    }

    function showLocalMenu(evt) {
        var hoverMenuId = $(this).attr('hover_menu');
        var $menu = $('#' + hoverMenuId);

        $(this).append($menu);
        var pos = $(this).position();
        var mx = evt.pageX, my = evt.pageY;
        $menu.css('top', 0).css('left', mx - pos.left - 8);
        $menu.show();
    }

    function hideLocalMenu() {
        var hoverMenuId = $(this).attr('hover_menu');
        var $menu = $('#' + hoverMenuId);
        $menu.hide();
    }
})(window.BTJQuery || jQuery);