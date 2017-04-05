<?php

/*
 * Wrapper functions for APC (memory cache)
 * For system without APC (e.g. Eckhard's local development machine), the session is uses instead of APC
 */

function apch_store($key, $value, $cachetime) {
    if (function_exists("apc_store")) {
        apc_store($key, $value, $cachetime);
    } else {
        $CI = & get_instance();
        $CI->load->helper('dbcache');
        dbcache_store($key, $value, $cachetime);
    }
}

function apch_fetch($key) {
    $CI = & get_instance();
    if (function_exists("apc_fetch")) {
        $value = apc_fetch($key);
    } 
    else {
        $CI = & get_instance();
        $CI->load->helper('dbcache');
        $value = dbcache_fetch($key);
    }
    return $value;
}

function apch_delete($key) {
    if (function_exists("apc_delete")) {
        apc_delete($key);
    } else {
        $CI = & get_instance();
        $CI->load->helper('dbcache');
        $value = dbcache_delete($key);
    }
}

function getValueFromCache($key) {
    $CI = & get_instance();
    $serialized_entries = apch_fetch($key);
    if (!$serialized_entries) {
        //dblog_debug("cache miss:$key");
        return false;        
    }
    else {
        //dblog_debug("cache hit:$key");
        return(unserialize($serialized_entries));            
    }
}

function storeValueInCache($key,$entries) {
    $CI = & get_instance();
    //dblog_debug("cache save:$key");
    $serialized_entries = serialize($entries);
    $cachetime = $CI->config->item('SESSIONCACHETIME');
    apch_store($key, $serialized_entries, $cachetime);    
}

?>