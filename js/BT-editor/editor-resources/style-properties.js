/**
 * Used to populate select tags in style editor
 */

var BTStyleProps={
    "font-weight":{
        "type": "text",
        "name": "font-weight",
        "values": ["normal" , "bold" , "bolder" , "lighter" , "100" , "200" , "300" , "400" , "500" , "600" , "700" , "800" , "900" ,"inherit"],
        "help": "Specifies the weight of a font"
    } ,
    "font-size":{
        "type": "text",
        "name": "font-size",
        "values": ["9px" , "10px" , "11px" , "12px" , "14px" , "16px" , "18px" , "20px" , "22px" , "24px" , "26px" ,"28px" , "30px" , "36px" ,"inherit"],
        "help": "Specifies the size of a font"
    },
    "text-align":{
        "type": "text",
        "name": "text-align",
        "values": ["start","left","right","center","justify","inherit"],
        "help": "Specifies the horizontal alignment of text"
    },

    "font-style":{
        "type": "text",
        "name": "font-style",
        "values": ["normal","italic","oblique","inherit"],
        "help": "Specifies the font style for text"
    },
    "text-decoration":{
        "type": "text",
        "name": "text-decoration",
        "values": ["none","underline","overline","line-through","blink","inherit"],
        "help": "Specifies the decoration added to text"
    },
    "display":{
        "type": "layout",
        "name": "display",
        "values": ["inline","block","list-item","inline-block","table","inline-table","table-row-group","table-header-group","table-footer-group","table-row","table-column-group","table-column","table-cell","table-caption","none","inherit"],
        "help": "Specifies the type of box an element should generate"
    },
    
    "position":{
        "type": "layout",
        "name": "position",
        "values": ["static","relative","absolute","fixed","inherit"],
        "help": "Specifies the type of positioning method used for an element (static, relative, absolute or fixed)"
    },
    "visibility":{
        "type": "layout",
        "name": "visibility",
        "values": ["visible","hidden","collapse","inherit"],
        "help": "Specifies whether or not an element is visible"
    },
    "float":{
        "type": "layout",
        "name": "float",
        "values": ["left","right","none","inherit"],
        "help": "Specifies whether or not a box should float"
    },
    "clear":{
        "type": "layout",
        "name": "clear",
        "values": ["none","left","right","both","inherit"],
        "help": "Specifies which sides of an element where other floating elements are not allowed"
    },
    
    "background-repeat":{
        "type": "background",
        "name": "background-repeat",
        "values": ["repeat","repeat-x","repeat-y","no-repeat","inherit"],
        "help": "Sets how a background image will be repeated"
    },

    "border-style":{
        "type": "border",
        "name": "border-style",
        "values": ["none", "hidden", "dotted", "dashed", "solid", "double", "groove", "ridge", "inset", "outset"],
        "help" :"Sets style of border"
    }
    
    
}