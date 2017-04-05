<?php

/*
 * Helper for DB based cache
 */

/**
 * Loads the DB driver when needed
 */
function lazyDbLoad() {
    $CI = & get_instance();
    if (!isset($CI->db)) {
        $CI->load->database();
    }
}

/**
 * @param String $key - VARCHAR(32)
 * @param String $value -  (TEXT) JSON ENCODED
 * @param Int $cachetime - TTL in secods
 * @param String $lang - "de" or "en"; default is 'en'
 */
function dbcache_store($key, $value, $cachetime, $lang = 'en') {
    lazyDbLoad();
    $CI = & get_instance();
    $data = array(
        'keyword' => $key,
        'value' => $value,
        'lang' => $lang,
        'ttl' => $cachetime,
    );
    if(dbcache_fetch($key,$lang)) {
        unset($data['keyword']);
        $CI->db->where('keyword', $key);
        $CI->db->update('cache', $data);        
    }
    else 
        $CI->db->insert('cache', $data);
}

/**
 * Gets a key as parameter and returns the value if it is found into the cache table
 * or else, return false
 * If the TTL for the found record has been reached, deletes the record from the DB and returns FALSE as well
 * 
 * @param String $key - VARCHAR(32)
 * @param String $lang - "de" or "en"; default is 'en'
 */
function dbcache_fetch($key, $lang = 'en') {
    lazyDbLoad();
    $CI = & get_instance();

    $query = $CI->db->select('value, timestamp, ttl')
            ->from('cache')
            ->where('keyword', $key)
            ->where('lang', $lang)
            ->get();

    if ($query->num_rows() > 0) {
        $ts = strtotime($query->row()->timestamp);
        if ((time() - $ts) > $query->row()->ttl) {
            dbcache_delete($key, $lang);
            return FALSE;
        }
        return $query->row()->value;
    } else {
        return FALSE;
    }
}

/**
 * Given a cache key, deletes the corresponding row from the cache table
 * @param String $key - VARCHAR(32)
 * @param String $lang - "de" or "en"; default is 'en'
 */
function dbcache_delete($key, $lang = 'en') {
    lazyDbLoad();
    $CI = & get_instance();

    $CI->db->where('keyword', $key)
            ->where('lang', $lang)
            ->delete('cache');
}

?>