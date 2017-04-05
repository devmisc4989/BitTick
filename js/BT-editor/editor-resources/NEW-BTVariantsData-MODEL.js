var BTVariantsData = {
    "editor_version": 1,
    "activePage": 'page_1',
    "pageCount": 1, 
    "pages": {
        "page_1": {
            "id": 'idp',
            "name": 'name',
            "url": 'http://',
            "variants": {
                "variant_1": {
                    "editor_version": 1,
                    "original_version": 0.5, /* allows to check for compatibility issues when adding features */
                    "name": "My Cool Variant",
                    "id": 24643,
                    "selectors": {
                        "body >p:eq(1)": {
                            /* storage for html editing */
                            "html_edit": {"JS": "$('body' >p:eq(1).replaceWith(...)"},
                            /* storage for styles editor */
                            "styles_edit": {"JS": "$('body >p:eq(1)').css('color','red')"},
                            /* store menu actions */
                            "move": {"JS": "$('body >p:eq(1)').css('left','200px')"}
                        }
                    },
                    /* JS and CSS stored as strings for now. Can be split at "\n" check within editor to see where they come from*/
                    "dom_modification_code": {
                        "[JS]": "concatenation of all JS for this variant",
                        "[CSS]": "all CSS for this variant"
                    }
                }
            }
        }
    }
}
