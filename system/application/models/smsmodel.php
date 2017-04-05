<?php

/**
 * Smart Messaging Model
 */
class smsmodel extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->load->database();
        $this->lang->load('sms-ui');
        $this->load->library('session');
    }
    
    /**
     * @return array -- sms template groups
     */
    public function sms_getTemplateGroups(){
        $sql = " SELECT sms_template_group_id, thumbnail_url FROM sms_template_group ORDER BY sort_order ";
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /**
     * return the array of available templates located in sms_template
     * @return array
     */
    public function sms_getTemplates($group) {
        $plan = $this->session->userdata('userplan'); // some plans may not see all templates....

        $sql = "SELECT sms_template_id, name, message_type, content_type, thumbnail_url, previewimage_url, description 
            FROM sms_template WHERE sms_template_group_id = ? 
            AND blocked_userplans not like '%$plan%' 
            ORDER BY sort_order, name ";
        $query = $this->db->query($sql, array($group));

        $res = array();
        foreach ($query->result() as $q1) {
            $r = array();
            $r['sms_template_id'] = $q1->sms_template_id;
            $r['name'] = $q1->name;
            $r['message_type'] = $q1->message_type;
            $r['thumbnail_url'] = "images/sms/thumbnail/" . $q1->thumbnail_url;
            $r['previewimage_url'] = "images/sms/preview/" . $q1->previewimage_url;
            $r['description'] = self::sms_replaceDescriptionLang($q1->description);
            $r['content_type'] = explode(';', $q1->content_type);
            // eventually add an intro description (these language elements are defined only for specific templates)
            $r['intro'] = $this->lang->line("sms_intro_" . $q1->name);
            $res[] = $r;
        }
        return $res;
    }
    
    /**
     * Given a template description (Headline;Subline) replaces each of them with the corresponding
     * value located in the lang files
     * @param string $desc
     * @return string
     */
    private function sms_replaceDescriptionLang($desc){
        $description = explode(";",$desc);
        $htmldescription = "<ul>";
        $smslang = $this->lang->line('sms template descriptions');
        foreach($description as $line) {
            $htmldescription .= "<li>" . $smslang[$line] . "</li>";
        }
        $htmldescription .= "</ul>";
        return $htmldescription;
    }

    /**
     * Returns the xml_content given a sms_template id
     * @param int $tempid
     * @return string
     */
    public function sms_getXmlByTemplate($tempid) {
        $sql = " SELECT sms_template_id, name, xml_content FROM sms_template WHERE sms_template_id = ? ";
        $query = $this->db->query($sql, array($tempid));
        return $query->result_array();
    }

    /**
     * Given a smart_message_id, returns the set of attributes from the corresponding table
     * @param integer $smsid
     */
    public function sms_getAttributes() {
        $sql = "SELECT a.name, a.type, a.int_value, a.string_value, a.text_value, a.enum_value "
                . " FROM smart_message s INNER JOIN sms_attribute a ON a.smart_message_id = s.smart_message_id "
                . " WHERE s.smart_message_id = ? ";
        $query = $this->db->query($sql, array($this->smsid));
        return $query->result_array();
    }

    /**
     * verifies that the smart message id, the template id and the landing page id are valid values
     * @param int $tempid
     * @param int $lpd
     * @return array
     */
    public function sms_validateSmsId($tempid) {
        $sql1 = " SELECT COUNT(*) AS cnt FROM smart_message WHERE smart_message_id = ? ";
        $query1 = $this->db->query($sql1, array($this->smsid));
        $csms = $query1->row()->cnt;

        $sql2 = " SELECT COUNT(*) AS cnt FROM sms_template WHERE sms_template_id = ? ";
        $query2 = $this->db->query($sql2, array($tempid));
        $csmt = $query2->row()->cnt;

        return array('cs' => $csms, 'ct' => $csmt);
    }

    /*     * *************************** SMS METHODS MOVED FROM MVT **************************** */

    /**
     * In the UE controller, if the "isNewSMSTest" parameter was sent as TRUE, this method is called to update the
     * field "smartmessage" in the corresponding LPC row
     * @param Int $lpcid - 
     * @param Int $isSms - 1 if it is an SMS or else, 0.
     */
    public function updateSmsInProject($lpcid, $isSms) {
        self::deletePendingSms();
        $this->db->where('landingpage_collectionid', $lpcid)
                ->update('landingpage_collection', array('smartmessage' => $isSms));
    }

    /**
     * If there are SMS ID's in the session variable pending to be deleted, well... delete them.
     */
    private function deletePendingSms() {
        $sms2del = $this->session->userdata('sms_delete');
        if (!$sms2del) {
            return;
        }

        foreach ($sms2del as $smsid) {
            self::deleteSmsAttributes($smsid);
            $sql = "DELETE FROM smart_message WHERE smart_message_id = ? ";
            $this->db->query($sql, array($smsid));
        }
        $this->session->unset_userdata('sms_delete');
    }


    /**
     * If the test is an SMS, this methods performs manual calls to the MVT model to delete SMS that are marked, update the
     * domcode of the variants that has an SMS and 
     * @param type $lpcid
     * @param type $variant
     * @return type
     */
    public function updateVariantWithSms($lpcid, $variant, $smsdata) {
        $smsid = ($variant->sms->id) ? $variant->sms->id : FALSE;
        $variant_id = (int) $variant->id;

        if ($smsid && !is_null($smsid)) {
            $domcode = self::addSmsToDomModificationCode($variant->dom_modification_code, $smsid, $variant_id);
            self::updateDomCodeWithSms($variant_id, $domcode);

            $newsmsid = self::createSmartMessage($smsid, $variant_id, $lpcid);
            $smsdata[] = array(
                oldsms => $smsid,
                newsms => $newsmsid
            );
        }
        return $smsdata;
    }

    /**
     * if the evaluated variant has an SMS id, this method is called to update the
     * "dom_modification_code" with the [SMS] part
     * @param Int $lpd - the variant id
     * @param array $domcode - the update domcode with the [SMS] in it.
     */
    private function updateDomCodeWithSms($lpd, $domcode) {
        $data = array(
            "dom_modification_code" => json_encode($domcode),
        );
        $this->db->where('landing_pageid', $lpd)
                ->update('landing_page', $data);
    }

    /**
     * Given an SMS ID, adds the SMS dom_code to the rest of the dom_modification_code for every variant
     * @param array $domcode
     * @param int $smsid
     * @return array
     */
    private function addSmsToDomModificationCode($domcode, $smsid, $lpid) {
        $this->load->helper('featurematrix_helper');
        $smscode = $this->session->userdata('sms_configuration');
        $branding = hasSmSBranding();
        $smshtml = '[SMS_HTML]';
        $smsms = '[SMS]';
        /* use the html returned from client instead of what is stored in session. The style editor will be changing it */
        $smsHtml = $domcode->$smshtml;

        if ($smscode && $smsid) {
            foreach ($smscode as $ids => $content) {
                if ($ids == $smsid && !$content['delete']) {
                    /* use client side html instead of session version */
                    $currentS = $currentH = $smsHtml;
                    $variantId = 'data-sms_variantid="' . $lpid . '" data-sms_message_type';
                    $addCode = !$branding ? "" : " $('#_bt_sms_branding_element').attr('style', 'display: block !important'); ";
                    $content['sms_domcode'][$smshtml] = str_replace('data-sms_message_type', $variantId, $currentH);
                    $content['sms_domcode'][$smsms] = str_replace('data-sms_message_type', $variantId, self::createSmsDomCode($currentS)) . $addCode;
                    return $content['sms_domcode'] + (array) $domcode;
                }
            }
        }

        $previousdom = array();
        $smsdom = array();

        if (isset($domcode->{'[JS]'})) {
            $previousdom['[JS]'] = $domcode->{'[JS]'};
        }
        if (isset($domcode->{'[CSS]'})) {
            $previousdom['[CSS]'] = $domcode->{'[CSS]'};
        }

        if ($domcode->$smshtml) {
            $this->load->helper('url');
            $hcode = $domcode->$smshtml;
            $smsdom[$smsms] = self::createSmsDomCode($hcode);
            $smsdom[$smsms] .=!$branding ? "" : " $('#_bt_sms_branding_element').attr('style', 'display: block !important'); ";
            $smsdom[$smshtml] = $domcode->$smshtml;
        }

        return $smsdom + $previousdom;
    }

    private function createSmsDomCode($html) {
        $html = str_replace('<script', '\x3Cscript', $html);
        $html = str_replace('</script', '\x3C/script', $html);
        $url = base_url() . 'js/smartmessaging.js';
        // transform to protocol-relative
        $url = str_replace('http://', '//', $url);
        $url = str_replace('https://', '//', $url);
        return "$('<style>#_bt_sms_main_container\{display:none;\}</style>$html').appendTo('body'); $.getScript('$url');";
    }

    /**
     * after creating the smart message, calls the createSmsAttributes function to create the attributes for it
     * @param array $args
     * @return int
     */
    private function createSmartMessage($smsid, $lpid, $lpcid) {
        $params = array();
        $attributes = array();
        $smsconf = $this->session->userdata('sms_configuration');

        if (!$smsconf) {
            return $smsid;
        }

        foreach ($smsconf as $ids => $content) {
            if ($ids == $smsid && !$content['delete']) {
                $params = $content['smart_message'];
                $attributes = array_merge($content['selectors'], $content['attributes']);
                break;
            }
        }

        if (count($params) > 0) {
            array_push($params, $lpid);
            array_push($params, $lpcid);
            $smsid = self::insertSms($params, $lpid);
            self::createSmsAttributes($smsid, $attributes);
        }

        return $smsid;
    }

    /**
     * @param array $params
     * @param int $lpid
     * @return int
     */
    private function insertSms($params, $lpid) {
        self::deleteSmsByLpid($lpid);
        $sql = " INSERT INTO smart_message(name, sms_template_id, rule_type, rule_args, sms_structure, landing_pageid, landingpage_collectionid) " .
                " VALUES(?, ?, ?, ?, ?, ?, ?) ";
        $this->db->query($sql, $params);
        return $this->db->insert_id();
    }

    /**
     * first, it deletes all the previous attributes by calling deleteSmsAttributes
     * @param int $smsid - smart message ID to create its attributes
     * @param array $attributes
     */
    private function createSmsAttributes($smsid, $attributes) {
        self::deleteSmsAttributes($smsid);
        foreach ($attributes as $attr) {
            $field = mysql_real_escape_string($attr['field']);
            $sql = " INSERT INTO sms_attribute (smart_message_id, name, type, $field) VALUES (?, ?, ?, ?) ";
            $this->db->query($sql, array($smsid, $attr['name'], $attr['type'], $attr['value']));
        }
    }

    /**
     * @param int $smsid -- smart message ID to delete all its attributes
     */
    private function deleteSmsAttributes($smsid) {
        $sql = " DELETE FROM sms_attribute WHERE smart_message_id = ? ";
        $this->db->query($sql, array($smsid));
    }

    /**
     * @param string $not_used_lps
     */
    private function deleteSmsByLpid($not_used_lps) {
        $lps = explode(',', $not_used_lps);
        foreach ($lps as $lp) {
            $sql = "DELETE a.* FROM sms_attribute a INNER JOIN smart_message s ON s.smart_message_id = a.smart_message_id "
                    . " WHERE s.landing_pageid = ?";
            $this->db->query($sql, array($lp));
            $sql = "DELETE FROM smart_message WHERE landing_pageid = ?";
            $this->db->query($sql, array($lp));
        }
    }
    /*     * *********************END SMS METHODS************************** */

}
