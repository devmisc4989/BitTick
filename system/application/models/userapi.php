<?php

/*
 * This is a "helper" class with access to the DB, so that is why it is in the models folder
 * This class contains 2 common methods for adminmodel, apimodel and user.php
 */

class userapi extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * After inserting a new client row, we need to add the corresponding API_CLIENT entry
     * After that, it calls the verifyApikey() method to update the APIKEY of the inserted row
     * @param Int $clientid - the last inserted client id.
     */
    public function saveApiClient($clientid) {
        $tenant = $this->config->item('bt_apiclientid');

        $c = hash('crc32', $clientid);
        $m = $clientid . 'blacktri' . $c . '.com';
        $secret = hash('crc32', $m);

        $apic = array(
            'api_client_type' => 2,
            'api_tenant' => $tenant,
            'clientid' => $clientid,
            'apikey' => '',
            'apisecret' => $secret,
        );
        $this->db->insert('api_client', $apic);
        self::verifyApiKey($this->db->insert_id());
    }

    /**
     * Once the api_client has been registered, this method gets the inserted ID and tries to update 
     * the corresponding row with its CRC32 hash as its APIKEY, if the query fails, calls itself with a second
     * parameter called "recursive" so it can track a maximum number of calls.
     * If the parameter "recursive" is set, it adds the microtime and a random number to the api_clientid so it can
     * ensure uniqueness before trying to update the APIKEY again.
     * @param Int $api_clientid - the last inserted api clientid
     * @param bool/int $recursive - keeps track of a maximum number of recursive calls
     * @throws Exception - in case the recursion limit is reached and the row can't be updated
     */
    private function verifyApiKey($api_clientid, $recursive = FALSE) {
        $m = $api_clientid;

        if ($recursive && $recursive < 10) {
            $m .= '-' . microtime(TRUE) . '-' . rand(1, 9999);
        } else if ($recursive) {
            throw new Exception(' (account::verifyApiKey) recursion limit exceeded', 400106);
        }

        $hash = array('apikey' => hash('crc32', $m));
        $query = $this->db->where('api_clientid', $api_clientid)
                ->update('api_client', $hash);

        if (!$query) {
            dblog_debug('API ERROR (apikey): ' . $this->db->_error_number() . ' - ' . $this->db->_error_message());
            $rec = $recursive ? $recursive++ : 1;
            self::verifyApiKey($api_clientid, $rec);
        }
    }

}
