<?php

/**
 * NOTE ****** ONLY WHEN SAVESTEP = "allocation" *****************
 * For each variant in the projects saves the allocation set by the user in 
 * the LPC details page
 * This works for VAB as well as for SPLIT tests (see ue/ls and lpc/save)
 * @param int $lpcid
 * @param object $sdk
 * @param array/bool $cVariants  (When called from lpc (Update of split variants)
 * @param array $vAllocations  (When called from lpc (Update of split variants)
 */
function saveProjectAllocation($lpcid, $sdk, $pAllocation = FALSE, $cVariants = FALSE, $vAllocations = FALSE) {
    $CI = & get_instance();
    $variants = $cVariants ? $cVariants : $CI->input->post('variant_id');
    $allocations = $vAllocations ? $vAllocations : $CI->input->post('variant_allocation');
    $project_allocation = $pAllocation ? FALSE : $CI->input->post('project_allocation');
    $totalVariants = 0;
    $totalAllocation = 0;
    $firstAllocation = -1;
    $equalAllocation = TRUE;

    foreach ($variants as $ind => $lpd) {
        $alloc = $allocations[$ind];

        if ($firstAllocation == -1) {
            $firstAllocation = $alloc;
        } else {
            $equalAllocation &= $alloc == $firstAllocation;
        }

        $totalVariants ++;
        $totalAllocation += $alloc;
    }

    if ($equalAllocation) {
        foreach ($allocations as $ind => $alloc) {
            $allocations[$ind] = 100;
        }
    } else if ($totalAllocation != 100) {
        $remainder = (100 - $totalAllocation) / $totalVariants;

        foreach ($allocations as $ind => $alloc) {
            $allocations[$ind] += $remainder;
        }
    }

    $curProject = $sdk->getProject($lpcid);
    foreach ($variants as $ind => $lpd) {
        $curVariant = $sdk->getDecision($lpcid, $lpd);

        $decision = array(
            'allocation' => 1 / (100 / $allocations[$ind]),
        );

        if ($curProject->type != 'MULTIPAGE') {
            $decision['url'] = $curVariant->url;
        }
        $sdk->updateDecision($lpcid, $lpd, $decision);

        if ($curVariant->type == 'CONTROL') {
            $project = array(
                'mainurl' => $curProject->mainurl,
                'runpattern' => $curProject->runpattern,
                'allocation' => $project_allocation ? ($project_allocation * 100) : $curProject->allocation,
            );
            $sdk->updateProject($lpcid, $project);
        }

        echo 'OK';
    }
}

// helper function - normalize allocations so they have values between 0..1 and the sum is 1
// $decisions is an array of decisions, with the attribute 'allocation'
// the function returns the same array with changed allocations
// if testtype = OPT_TESTTYPE_TEASER, the we distribute the allocation evenly, because we do not
// support custom allocations for teasertests
function normalizeDecisionAllocation($decisions, $testtype) {
    if (sizeof($decisions) == 0) {
        return false;
    }

    $sumAllocations = 0;
    $invalidDecisions = false;
    
    foreach ($decisions as $decision) {
        if ($decision['allocation'] < 0) {
            $invalidDecisions = true;
        }
        $sumAllocations += $decision['allocation'];
    }
    
    // edge cases: negtive allocations or all allocations = 0. Distribute evenly then.
    for ($i = 0; $i < sizeof($decisions); $i++) {
        if (($sumAllocations == 0) || $invalidDecisions || ($testtype == OPT_TESTTYPE_TEASER)) {
            $decisions[$i]['allocation'] = 1 / sizeof($decisions);
        } else {
            $decisions[$i]['allocation'] /= $sumAllocations;
        }
    }
    
    return $decisions;
}
