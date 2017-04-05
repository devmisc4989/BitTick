<?php
header('Content-type: application/x-javascript');
header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');

//dblog_debug("response: " . print_r($response,true));

if($response['type'] == 'REDIRECT') {
    echo (renderVisitorCookies($response));
    echo "_bt.setReady();\n";
    if ($response['splittestpagetype'] == OPT_PAGETYPE_VRNT) {
        echo (renderRedirectUrl($response));
        echo (renderExcludes($response));
    } else {
        echo (renderWebtrackingCalls($response));
        echo "_bt.removePreloader();\n";
        echo (renderExcludes($response));
    }
    echo "//bto split";
}
elseif($response['type'] == 'INJECTION') {
    echo (renderVisitorCookies($response));
    echo "_bt.setReady();\n";
    echo (renderWebtrackingCalls($response));
    echo (renderDomInjection($response));
    echo (renderExcludes($response));
    echo "//bto injection";
}
else { // only excludes due to non-allocation
    echo "_bt.setReady();\n";
    echo "_bt.removePreloader();\n";
    echo (renderExcludes($response));
    echo "//bto exclude";    
}

//============================================================

function renderVisitorCookies($response) {
    if(is_numeric($response['visitorid'])) {
        setcookie('GS3_v', $response['visitorid'], time() + 31536000);    
        return "_bt.setCookie('GS1_v','" . $response['visitorid'] . "',365);\n";
    }     
    else {
        return "";                
    }
}

function renderWebtrackingCalls($response) {
    // create all necessary building blocks for the rendering before deciding which ones to return
    $etrackerOutput = "if(typeof _etracker==='object'){\n";
    $dataLayerOutput = sprintf("_bt.data.projectCount = %d;\n",sizeof($response['webtracking']));
    $dataLayerOutput .= "_bt.data['projects'] = [];\n";
    foreach($response['webtracking'] as $track) {
        $dataLayerOutput .= sprintf("var p={projectname:'%s',projectid:%d,decisionname:'%s',decisionid:%d};\n",
            $track['collectionname'],$track['collectionid'],$track['landingpagename'],$track['landingpageid']);
        $dataLayerOutput .= "(_bt.data['projects']).push(p);\n";
        if(isset($track['smart_messages'])) {
            $smsoutput = "";
            if($track['smart_messages'] == 1) {
                // smart messaging needs the bto_attributes array, so render it if a smart message is contained
                $collectionname = str_replace('"','\"',$track['collectionname']) ;
                $pagename = str_replace('"','\"',$track['landingpagename']) ;
                $rulename = str_replace('"','\"',$track['rulename']) ;

                $smsoutput .= "var bto_attributes = {};\n";
                $smsoutput .= "bto_attributes['etcc_cmp'] = ['$collectionname', true];\n";
                $smsoutput .= "bto_attributes['etcc_var'] = ['$pagename', true];\n";
                $smsoutput .= "bto_attributes['etcc_seg'] = ['$rulename', true];\n";
            }
        }
        if(empty($smsoutput)) {
            $etrackerOutput = $etrackerOutput . "_etracker.sendEvent(new TestViewEvent('" .
            str_replace('"','\"',$track['collectionname']) . "','" . 
            $etcc_cty . "','" .
            str_replace('"','\"',$track['rulename']) . "','" . 
            str_replace('"','\"',$track['landingpagename']) . "'));";
            $etrackerOutput .= "\n";            
        }
        else {
            $etrackerOutput = $etrackerOutput . $smsoutput . "\n";            
            
        }
    }
    $etrackerOutput .= "}\n";

    // google tag manager
    $CI = & get_instance();
    if($CI->config->item('WA_GTM_DATALAYER_NAME') != 'NONE')
        $dlName = $CI->config->item('WA_GTM_DATALAYER_NAME');
    else
        $dlName = 'dataLayer';
    $gtmOutput = "if(typeof $dlName==='object'){\n";
    $gtmOutput = $gtmOutput . $dlName . ".push({'event': '_bto'});\n";
    $gtmOutput .= "}\n";
    
    // dispatch the type of web analytics integration
    $trackingOutput = $dataLayerOutput . "\n";
    if($response['new_variant']) {
        switch($response['wa_integration']) {
            case 'ETRACKER':
                $trackingOutput .= $etrackerOutput;
                break;
            case 'GTM':
                $trackingOutput .= $gtmOutput;
                break;
        }        
    }
    return $trackingOutput;
}

function renderDomInjection($response) {
    $myDomCode = $response['dom_code'];
    $CI = & get_instance();
    $CI->load->library('Jshandler');
    $jsHandlerScriptNeeded = false;
    $handlerCode = array();
    $handlerCode["[JS]"] = $CI->jshandler->getAllHandlerCodes($response['impressionEventJSEventHandlers']);
    if(strlen($handlerCode["[JS]"]) > 0)
        $jsHandlerScriptNeeded = true;
    $myDomCode = $CI->jshandler->combineDomCodes(json_encode($handlerCode),$myDomCode);

    $deferredCode = array();
    $deferredCode["[JS]"] = $CI->jshandler->getDeferredImpressionCodes($response['dom_triggered_activation']);
    if(strlen($deferredCode["[JS]"]) > 0)
        $jsHandlerScriptNeeded = true;
    $myDomCode = $CI->jshandler->combineDomCodes(json_encode($deferredCode),$myDomCode);

    if($jsHandlerScriptNeeded) {
        $handlerScriptCode = array();
        $handlerScriptCode["[JS]"] = $CI->jshandler->getJSHandlerScript();        
        $myDomCode = $CI->jshandler->combineDomCodes($myDomCode,json_encode($handlerScriptCode));
    }

    echo "_bt.applyCollectionChanges($myDomCode);\n";
}

function renderRedirectUrl($response) {
    // attach parameters for etracker-integration and referrer
    $redirecturl = $response['redirect']; 
    if($response['new_variant']) {
        $redirecturl = attachToQueryString($redirecturl,"_bt_projectid=" . $response['webtracking'][0]['collectionid']);
        $redirecturl = attachToQueryString($redirecturl,"_bt_projectname=" . urlencode($response['webtracking'][0]['collectionname']));
        $redirecturl = attachToQueryString($redirecturl,"_bt_decisionid=" . $response['webtracking'][0]['landingpageid']);
        $redirecturl = attachToQueryString($redirecturl,"_bt_decisionname=" . urlencode($response['webtracking'][0]['landingpagename']));
    }
    $output .= "_bt.redirect('" . $redirecturl . "');\n";
    return $output;
}

function renderExcludes($response) {
    foreach($response['exclude'] as $exclude) {
        $output .= "_bt.excludeFromTest('$exclude');\n";
    }
    return $output;
}

// helper function: attach a key=value substring to a URL/querystring taking into account wether
// a ? is already includedin the URL 
function attachToQueryString($querystring,$attachment) {
    if(strpos($querystring,'?') === false)
        return $querystring . "?" . $attachment;
    else 
        return $querystring . "&" . $attachment;
}