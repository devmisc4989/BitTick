<?php

/**
 * @param Int $date - The creation date in seconds (strtotime)
 * @return String - ime since creation of decision group with the following format
 * If time < 1hour: <xx>min
 * If time < 1 day: <yy>h <xx>min
 * If time < 2 days: <zz>d <yy>h
 * Else: <zz> days
 */
function getGroupAge($date) {
    $CI = & get_instance();

    $df = 60 * 60 * 24;
    $hf = 60 * 60;
    $mf = 60;
    $restartDate = strtotime($date);
    $time = time() - $restartDate;
    if ($time > (2 * $df)) {
        $rep = intval($time / $df);
        $age = sprintf($CI->lang->line('table_tt_time_days'), $rep);
    } else if ($time >= $df) {
        $rep = intval(($time - $df) / $hf);
        $age = sprintf($CI->lang->line('table_tt_time_d_h'), '1', $rep);
    } else if ($time >= $hf) {
        $rep1 = intval($time / $hf);
        $rep2 = intval(($time % $hf) / $mf);
        $age = sprintf($CI->lang->line('table_tt_time_h_min'), $rep1, $rep2);
    } else {
        $rep = intval($time / $mf);
        $age = sprintf($CI->lang->line('table_tt_time_min'), $rep);
    }

    return $age;
}

/**
 * @param String $interface - either UI or API
 * @param Int $action - either 1 or 0 (to start or stop the project accordingly
 * @param String $label - either "pause" or "start"
 * @return string
 */
function getGroupMenuOptions($interface, $action, $label, $href) {
    $CI = & get_instance();

    $options = $interface == 'UI' ? '<a href="javascript:void(0);"  id="tto_original_variants">' . $CI->lang->line('Original und Varianten') . '</a>' : '';
    $options .= '<a href="javascript:void(0);"  class="tt_start_stop_story" id="start_stop_' . $action . '">' . $label . '</a>'
            . '<a href="' . $href . '" id="tt_display_story">' . $CI->lang->line('table_tt_action_display') . '</a>';

    if ($interface == 'UI') {
        $options .= '<a href="javascript:void(0);" class="tt_delete_story">' . $CI->lang->line('table_tt_action_delete') . '</a>';
    }

    return $options;
}

/**
 * @param Int $lpcid
 * @param Array $project
 * @param Array $group
 * @param Class $sdk
 * @return string - with the corresponding text/icon to display as the result field in the groups table
 */
function getGroupResult($group) {
    $CI = & get_instance();

    $thumbs = array(
        'LOST' => 'down',
        'WON' => 'up',
    );

    $res = isset($thumbs[$group->result]) ? $thumbs[$group->result] : 'none';
    $result = '<a class="thumb_' . $res . '"></a>';

    if ($res == 'none') {
        $result = $CI->lang->line('table_tt_result_collecting');
    } else if ($res == "up") {
        $uplift = round($group->uplift * 100, 2);
        $result .= "<b>" . round(100 * $uplift / 100) . "%</b>";
    }

    return $result;
}
