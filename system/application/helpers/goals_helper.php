<?php

/**
 * 
 * @param Int $lpcid 
 * @param Int $groupid
 * @param Int $groupindex
 */
function saveProjectGoals($sdk, $lpcid, $groupid = -1, $groupindex = FALSE, $customPost = FALSE) {
    $CI = & get_instance();
    $CI->lang->load('editor');

    if ($customPost) {
        $ids = $customPost['ids'];
        $types = $customPost['types'];
        $levels = $customPost['levels'];
        $names = $customPost['clickNames'];
        $params = $customPost['clickSelectors'];
        $pageids = $customPost['clickPages'];
    } else {
        $ids = $CI->input->post('conversion_goal_id');
        $types = $CI->input->post('conversion_goal_type');
        $levels = $CI->input->post('conversion_goal_level');
        $names = $CI->input->post('conversion_goal_name');
        $params = $CI->input->post('conversion_goal_param');
        $pageids = $CI->input->post('conversion_goal_pageid');
    }
    $clickPrefix = $CI->lang->line('Click goal prefix');

    $apigoals = $CI->config->item('api_goals');
    $paramGoals = array(
        $apigoals['3'],
        $apigoals['12'],
        $apigoals['13'],
        $apigoals['15'],
    );

    foreach ($types as $index => $type) {
        if ($type == 'CLICK' && $groupindex && $pageids[$index] != $groupindex) {
            continue;
        }

        if ($type != 'CLICK' && $CI->session->userdata('otherGoalsSaved')) {
            continue;
        }

        $id = $ids[$index] * 1 > 0 ? $ids[$index] : FALSE;
        $goal = array(
            'type' => $type,
            'level' => $levels[$index],
        );

        if (in_array($type, $paramGoals)) {
            $p = $params[$index];

            if ($type == 'CLICK') {
                $p = json_encode(array(
                    'selector' => $params[$index],
                ));
            }

            $goal += array(
                'name' => str_replace($clickPrefix, '', $names[$index]),
                'param' => $p,
            );
        }

        if ($id) {
            $sdk->updateGoal($lpcid, $id, $goal, $groupid);
        } else {
            $sdk->createGoal($lpcid, $goal, $groupid);
        }
    }

    $clicksDeleted = $customPost ? $customPost['clicksDeleted'] : $CI->input->post('archived_goal_id');
    archiveProjectGoals($sdk, $lpcid, $clicksDeleted);

    if ($groupid != -1) {
        $CI->session->set_userdata('otherGoalsSaved', TRUE);
    }

    dblog_debug('Goals saved for LPC = ' . $lpcid . ', Goals: ' . json_encode($types));
}

/**
 * After saving the goals, we verify if there are goals to be archived
 * @param Int $lpcid
 */
function archiveProjectGoals($sdk, $lpcid, $clicksDeleted) {
    foreach ($clicksDeleted as $index => $id) {
        if ($id * 1 > 0) {
            $sdk->deleteGoal($lpcid, $id);
        }
    }
}
