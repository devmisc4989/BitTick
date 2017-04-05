<?php

/*
 *  render an <a href> tag which opens a window with the support help text for a given top
 *  $id: string specifying the topic. This id refers to a link text and a link target, both of
 *      which are define in support_lang.php
 */

function splink($id, $target = "") {
    $CI = & get_instance();
    $tenant = $CI->config->item('tenant');

    $text = $CI->lang->line($id . '_text');
    if ($target == "") {
        $myurl = $CI->lang->line($id . '_target');
        $parts = explode('#', $myurl); // separate URL from anchor, if needed
        $url = $parts[0];
        $anchor = isset($parts[1]) ? '#' . $parts[1] : '';
        if ($tenant == 'etracker') {
            if(strpos($url,'http') === false)
                $target = $CI->config->item('base_ssl_url') . "blog/" . $url . "/?style=plain" . $anchor;
            else 
                $target = $url . $anchor;
        }
        else {
            $target = $myurl;
        }
        $tag = "<a href=\"$target\" class=\"help_link\" target=\"_blank\">$text</a>";
    }
    else {
        $tag = "<a href=\"$target\" class=\"help_link\" target=\"_blank\">$text</a>";
    }
    return $tag;
}

?>