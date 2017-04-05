/**
 * Created by charl_000 on 3/9/14.
 */

var BT_HtmlTags={
    "a": {
        "tag": "a",
        "type": "text",
        "description": "Link"
    },
    "abbr": {
        "tag": "abbr",
        "type": "text",
        "description": "Abbreviation"
    },
    "address": {
        "tag": "address",
        "type": "sections",
        "description": "Address"
    },
    "area": {
        "tag": "area",
        "type": "embedding",
        "description": "Image Map Area"
    },
    "article": {
        "tag": "article",
        "type": "sections",
        "description": "Article"
    },
    "aside": {
        "tag": "aside",
        "type": "sections",
        "description": "Aside"
    },
    "audio": {
        "tag": "audio",
        "type": "embedding"
    },
    "b": {
        "tag": "b",
        "type": "text",
        "description": "Bold Text"
    },
    "base": {
        "tag": "base",
        "type": "document"
    },
    "bdi": {
        "tag": "bdi",
        "type": "text"
    },
    "bdo": {
        "tag": "bdo",
        "type": "text"
    },
    "blockquote": {
        "tag": "blockquote",
        "type": "grouping",
        "description": "Block Quote"
    },
    "body": {
        "tag": "body",
        "type": "sections"
    },
    "br": {
        "tag": "br",
        "type": "grouping",
        "description": "Line Break"
    },
    "button": {
        "tag": "button",
        "type": "form",
        "description": "Button"
    },
    "canvas": {
        "tag": "canvas",
        "type": "embedding",
        "description": "Canvas"
    },
    "caption": {
        "tag": "caption",
        "type": "table"
    },
    "cite": {
        "tag": "cite",
        "type": "text",
        "description": "Citation"
    },
    "code": {
        "tag": "code",
        "type": "text",
        "description": "Code"
    },
    "col": {
        "tag": "col",
        "type": "table",
        "description": "Column"
    },
    "colgroup": {
        "tag": "colgroup",
        "type": "table",
        "description": "Column Group"
    },
    "command": {
        "tag": "command",
        "type": "interactive",
        "description": "Command"
    },
    "datalist": {
        "tag": "datalist",
        "type": "form"
    },
    "dd": {
        "tag": "dd",
        "type": "grouping",
        "description": "Definition List Description"
    },
    "del": {
        "tag": "del",
        "type": "text",
        "description": "Deleted Text"
    },
    "details": {
        "tag": "details",
        "type": "interactive",
        "description": "Details"
    },
    "dfn": {
        "tag": "dfn",
        "type": "text",
        "description": "Definition"
    },
    "div": {
        "tag": "div",
        "type": "grouping",
        "description": "Division"
    },
    "dl": {
        "tag": "dl",
        "type": "grouping",
        "description": "Definition List"
    },
    "dt": {
        "tag": "dt",
        "type": "grouping",
        "description": "Definition List Item"
    },
    "em": {
        "tag": "em",
        "type": "text",
        "description": "Emphasized Text"
    },
    "embed": {
        "tag": "embed",
        "type": "embedding",
        "description": "Embedded Object"
    },
    "fieldset": {
        "tag": "fieldset",
        "type": "form"
    },
    "figcaption": {
        "tag": "figcaption",
        "type": "grouping",
        "description": "Figure Caption"
    },
    "figure": {
        "tag": "figure",
        "type": "grouping",
        "description": "Figure"
    },
    "footer": {
        "tag": "footer",
        "type": "sections",
        "description": "Footer"
    },
    "form": {
        "tag": "form",
        "type": "form",
        "description": "Form"
    },
    "h1": {
        "tag": "h1",
        "type": "sections",
        "description": "Headline"
    },
    "h2": {
        "tag": "h2",
        "type": "sections",
        "description": "Headline"
    },
    "h3": {
        "tag": "h3",
        "type": "sections",
        "description": "Headline"
    },
    "h4": {
        "tag": "h4",
        "type": "sections",
        "description": "Headline"
    },
    "h5": {
        "tag": "h5",
        "type": "sections",
        "description": "Headline"
    },
    "h6": {
        "tag": "h6",
        "type": "sections",
        "description": "Headline"
    },
    "head": {
        "tag": "head",
        "type": "document",
        "description": "Head"
    },
    "header": {
        "tag": "header",
        "type": "sections",
        "description": "Header"
    },
    "hgroup": {
        "tag": "hgroup",
        "type": "sections",
        "description": "Hgroup"
    },
    "hr": {
        "tag": "hr",
        "type": "grouping",
        "description": "Horizonal Rule"
    },
    "html": {
        "tag": "html",
        "type": "root"
    },
    "i": {
        "tag": "i",
        "type": "text",
        "description": "Italic Text"
    },
    "iframe": {
        "tag": "iframe",
        "type": "embedding",
        "description": "iFrame"
    },
    "img": {
        "tag": "img",
        "type": "embedding",
        "description": "Image"
    },
    "input": {
        "tag": "input",
        "type": "form",
        "description": "Input Field"
    },
    "ins": {
        "tag": "ins",
        "type": "text",
        "description": "Inserted Text"
    },
    "kbd": {
        "tag": "kbd",
        "type": "text",
        "description": "Keyboard Text"
    },
    "keygen": {
        "tag": "keygen",
        "type": "form"
    },
    "label": {
        "tag": "label",
        "type": "form",
        "description": "Label"
    },
    "legend": {
        "tag": "legend",
        "type": "form",
        "description": "Legend"
    },
    "li": {
        "tag": "li",
        "type": "grouping",
        "description": "List Item"
    },
    "link": {
        "tag": "link",
        "type": "document"
    },
    "map": {
        "tag": "map",
        "type": "embedding",
        "description": "Image Map"
    },
    "mark": {
        "tag": "mark",
        "type": "text",
        "description": "Mark"
    },
    "menu": {
        "tag": "menu",
        "type": "interactive",
        "description": "Menu List"
    },
    "meta": {
        "tag": "meta",
        "type": "document"
    },
    "meter": {
        "tag": "meter",
        "type": "form",
        "description": "Meter"
    },
    "nav": {
        "tag": "nav",
        "type": "sections",
        "description": "Navigation"
    },
    "noscript": {
        "tag": "noscript",
        "type": "document",
        "description": "No Script"
    },
    "object": {
        "tag": "object",
        "type": "embedding",
        "description": "Embedded Object"
    },
    "ol": {
        "tag": "ol",
        "type": "grouping",
        "description": "Ordered List"
    },
    "optgroup": {
        "tag": "optgroup",
        "type": "form",
        "description": "Option Group"
    },
    "option": {
        "tag": "option",
        "type": "form",
        "description": "Selection Option"
    },
    "output": {
        "tag": "output",
        "type": "form"
    },
    "p": {
        "tag": "p",
        "type": "grouping",
        "description": "Paragraph"
    },
    "param": {
        "tag": "param",
        "type": "embedding",
        "description": "Object Parameter"
    },
    "pre": {
        "tag": "pre",
        "type": "grouping",
        "description": "Preformatted Text"
    },
    "progress": {
        "tag": "progress",
        "type": "form",
        "description": "Progress"
    },
    "q": {
        "tag": "q",
        "type": "text",
        "description": "Quotation"
    },
    "rp": {
        "tag": "rp",
        "type": "text"
    },
    "rt": {
        "tag": "rt",
        "type": "text"
    },
    "ruby": {
        "tag": "ruby",
        "type": "text"
    },
    "s": {
        "tag": "s",
        "type": "text"
    },
    "samp": {
        "tag": "samp",
        "type": "text",
        "description": "Sample Output"
    },
    "script": {
        "tag": "script",
        "type": "document"
    },
    "section": {
        "tag": "section",
        "type": "sections",
        "description": "Section"
    },
    "select": {
        "tag": "select",
        "type": "form",
        "description": "Selection List"
    },
    "small": {
        "tag": "small",
        "type": "text",
        "description": "Small Text"
    },
    "source": {
        "tag": "source",
        "type": "embedding"
    },
    "span": {
        "tag": "span",
        "type": "text",
        "description": "Span"
    },
    "strong": {
        "tag": "strong",
        "type": "text",
        "description": "Strong Text"
    },
    "style": {
        "tag": "style",
        "type": "document"
    },
    "sub": {
        "tag": "sub",
        "type": "text",
        "description": "Subscript Text"
    },
    "summary": {
        "tag": "summary",
        "type": "interactive",
        "description": "Summary"
    },
    "sup": {
        "tag": "sup",
        "type": "text",
        "description": "Superscript Text"
    },
    "table": {
        "tag": "table",
        "type": "table",
        "description": "Table"
    },
    "tbody": {
        "tag": "tbody",
        "type": "table",
        "description": "Table Body"
    },
    "td": {
        "tag": "td",
        "type": "table",
        "description": "Table Data"
    },
    "textarea": {
        "tag": "textarea",
        "type": "form",
        "description": "Text Field"
    },
    "tfoot": {
        "tag": "tfoot",
        "type": "table",
        "description": "Table Footer"
    },
    "th": {
        "tag": "th",
        "type": "table",
        "description": "Table Header"
    },
    "thead": {
        "tag": "thead",
        "type": "table",
        "description": "Table Header"
    },
    "time": {
        "tag": "time",
        "type": "text",
        "description": "Time"
    },
    "title": {
        "tag": "title",
        "type": "document"
    },
    "tr": {
        "tag": "tr",
        "type": "table",
        "description": "Table Row"
    },
    "track": {
        "tag": "track",
        "type": "embedding"
    },
    "ul": {
        "tag": "ul",
        "type": "grouping",
        "description": "Unordered List"
    },
    "var": {
        "tag": "var",
        "type": "text",
        "description": "Variable"
    },
    "video": {
        "tag": "video",
        "type": "embedding"
    },
    "wbr": {
        "tag": "wbr",
        "type": "text",
        "description": "Word Break"
    }
}
