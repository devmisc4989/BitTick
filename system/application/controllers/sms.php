<?php

/**
 * Smart Messaging Controller.
 */
class sms extends CI_Controller {

    public $smsid;

    /**
     * first it checks if the user is logged in or else he is redirected to the login page
     * @return type
     */
    function __construct() {
        parent::__construct();
        doAutoload(); // load resources from autoload_helper

        $clientid = $this->session->userdata('sessionUserId');
        if (!$clientid) {
            lang_redirect('login');
            return;
        }

        $this->load->model('smsmodel');
        $this->load->model('optimisation');
        $this->load->model('user');
        $this->load->library('log');
    }

    /**
     * Redirects to the default page (lpc collection list), this controller is to be called via ajax, not directly
     */
    public function index() {
        $login_targetpage = $this->session->userdata('login_targetpage');
        if (!$login_targetpage) {
            redirect($this->config->item('base_ssl_url') . "lpc/cs/");
        } else {
            $this->session->unset_userdata('login_targetpage');
            redirect($login_targetpage);
        }
    }

    /**
     * return a json with the array of available sms rules, message types and content types located in the sms config file
     * and the available templates located in the DB.
     */
    public function getTemplateList() {
        $this->config->load('sms');
        $this->lang->load('sms-ui');
        $template_groups = array();
        if($this->config->item('tenant') != 'etracker')
            $groups = array();
        else
            $groups = $this->smsmodel->sms_getTemplateGroups();
        foreach ($groups as $group) {
            $template_groups[] = array(
                'sms_template_group_id' => $group['sms_template_group_id'],
                'thumbnail_url' => $group['thumbnail_url'],
                'templates' => $this->smsmodel->sms_getTemplates($group['sms_template_group_id']),
            );
        }
        $rules = $this->config->item('sms_rules');
        $ruleDescriptions = $this->lang->line('sms_ui_rules');
        foreach ($rules as $key => $rule) {
            $rules[$key]['description'] = $ruleDescriptions[$rule['value']];
        }

        $attrib = array(
            'rules' => $rules,
            'message_types' => $this->config->item('message_types'),
            'content_types' => $this->config->item('content_types'),
            'template_groups' => $template_groups,
        );
        echo json_encode($attrib);
    }

    /**
     * returns the XML encoded in JSON given a template ID
     */
    public function getTemplateAttributes() {
        $tempid = $this->input->get('tempid') ? $this->input->get('tempid') : 3;
        $res = $this->smsmodel->sms_getXmlByTemplate($tempid);
        $xml_content = str_replace('#template_path#', $this->config->item('sms_template_path'), $res[0]['xml_content']);
        
        foreach ($this->lang->line('sms template labels') as $key => $value) {
            if (strpos($xml_content, $key) >= 0) {
                $xml_content = str_replace($key, $value, $xml_content);
            }
        }

        $xml = simplexml_load_string($xml_content);

        $a = array();
        foreach ($xml->area as $area) {
            if ($area->selector->attributes()->type != 'active' || $area->block->attribute) {
                $sel = self::getAreaSelector($area);
                $a[] = ($area->block->attribute) ? $sel + self::getAreaBlocks($area) : $sel;
            }
        }

        echo json_encode(array(
            'id' => $res[0]['sms_template_id'],
            'label' => $res[0]['name'],
            'areas' => $a
        ));
    }

    /**
     * Return the array of selectors with the corresponding options given an xml->area
     * @param array $area
     * @return array
     */
    private function getAreaSelector($area) {
        $opt = array();
        foreach ($area->selector->option as $option) {
            $opt[] = array(value => (string) $option->attributes()->value, label => (string) $option);
        }

        $type = (string) $area->selector->attributes()->type;
        $value = (string) $area->selector->option->attributes()->value;

        $a1 = array(
            'selector' => array(
                'type' => $type,
                'name' => (string) $area->selector->attributes()->name,
                'label' => (string) $area->selector->label,
            )
        );

        if ($type != 'active') {
            $a1['selector']['default'] = $a1['selector']['value'] = ($type == 'pulldown') ? $value : false;
        }
        if ($area->selector->option) {
            $a1['selector']['options'] = $opt;
        }

        return $a1;
    }

    /**
     * return the array of blocks including the attributes for each of them
     * @param array $area
     * @return array
     */
    private function getAreaBlocks($area) {
        $attrib = array();
        $seltype = $area->selector->attributes()->type;
        foreach ($area->block as $block) {
            foreach ($block->attribute as $att) {
                $attrib[] = self::getBlockAttributes($area, $block, $att, $seltype);
            }
        }
        return array('blocks' => array(array('attr' => $attrib)));
    }

    /**
     * Given a $block array, returns the attributes in it
     * @param array $block
     * @return array
     */
    private function getBlockAttributes($area, $block, $att, $seltype) {
        $arr = explode(';', $att->default);
        $ops = array();
        foreach ($arr as $key) {
            $a = explode('|', $key);
            $ops[] = array('label' => $a[0], 'value' => $a[1]);
        }

        $a1 = array(
            'name' => (string) str_replace('$', '', $att->name),
            'type' => (string) $att->type,
            'db_type' => ($att->type == 'enum') ? 'string' : (string) $att->type,
            'label' => (string) $att->label,
        );

        if ((string) $att->type == 'enum') {
            $a1['default'] = $ops[0]['value'];
            $a1['value'] = $ops[0]['value'];
            $a1['options'] = $ops;
        } else {
            $a1['default'] = (string) $att->default;
            $a1['value'] = (string) $att->default;
        }

        if ($seltype == 'checkbox') {
            $a1['depends_on'] = (string) $area->selector->attributes()->name;
            $a1['display_val'] = TRUE;
        } else if ($seltype == 'pulldown') {
            $a1['display_val'] = (string) $block->attributes()->selector;
        }

        return $a1;
    }

    /**
     * returns the HTML Preview according the the user preferences for the current template
     */
    public function getPreview($t = false) {
        $template = (!$t) ? file_get_contents("php://input") : $t;
        $input = json_decode($template);

        $res = $this->smsmodel->sms_getXmlByTemplate($input->id);
        $xml = simplexml_load_string(str_replace('#template_path#', $this->config->item('sms_template_path'), $res[0]['xml_content']));

        $htm = '';
        foreach ($xml->area as $area) {
            $htm.= self::getAreaSource($area, $input);
        }

        foreach ($input->attributes as $k => $v) {
            $val =  $v->value;
            $htm = str_replace('$' . $k, $val, $htm);
        }

        /************ TESTING DIFFERENT CSS RESET without "!important" */
        $htm= str_replace('cleanslate.css', 'sms-reset-class.css', $htm);
        $htm = str_replace('!important', '', $htm);

        /******************************************/

        if ($t) {
            return $htm;
        } else {
            echo $htm;
        }
    }

    /**
     * return the html for every block evaluated depending on the selector type and its value
     * @param array $area
     * @param array $input
     * @return string
     */
    private function getAreaSource($area, $input) {
        $nm = $area->selector->attributes()->name;
        $tp = $area->selector->attributes()->type;
        $val = $input->selectors->$nm->value;
        $h = '';

        foreach ($area->block as $block) {
            if ($tp == 'active' || ($tp == 'checkbox' && (int) $val == 1)) {
                $h.= $block->source;
            } else if ($tp == 'pulldown' && $block->attributes()->selector == $val) {
                return $block->source;
            }
        }
        return $h;
    }

    /**
     * validates the values suplied by the client to create/edit the smart message.
     * @param int $tempid - the template ID or null
     * @param int $lpd - the landing page ID or null
     * @param string $rtype - the rule type
     * @param string $mtype - the message type
     * @return boolean
     */
    private function validateSms($tempid, $rtype, $mtype) {
        $this->config->load('sms');
        $rules = array();
        $types = array();
        $error = "";

        $validate = $this->smsmodel->sms_validateSmsId($tempid);

        foreach ($this->config->item('sms_rules') as $r) {
            $rules[] = $r['value'];
        }
        foreach ($this->config->item('message_types') as $m) {
            $types[] = $m['value'];
        }

        $error.= ((int) $this->smsid > 0 && $validate['cs'] < 1) ? "The smart message id is not valid ($this->smsid). \n" : "";
        $error.= ($validate['ct'] < 1) ? "The template id is not valid ($tempid). \n" : "";
        $error.= (!in_array($rtype, $rules)) ? "The Rule Type is not valid ($rtype). \n" : "";
        $error.= (!in_array($mtype, $types)) ? "The Message type is not valid ($mtype). \n" : "";

        if (strlen(trim($error)) > 0) {
            dblog_debug($error);
            return $error;
        }
        return '';
    }

    /**
     * validates the template id, the landing page id (if any), the rule type, the message type
     * calls the method to save the smart_message,
     * deletes the current attributes and generates the valid array to save the new corresponding attributes.
     * @return boolean
     */
    public function saveSms() {
        // retrieve account_key2
        $clientid = $this->session->userdata('sessionUserId');
        $userdata = $this->user->clientdatabyid($clientid);
        $accountkey2 = $userdata['account_key2'];

        $input = json_decode(file_get_contents("php://input"));
        $this->smsid = $input->sms->smsid ? $input->sms->smsid : null;

        $valid = self::validateSms($input->template->id, $input->sms->rule, $input->sms->message_type);
        if ($valid != '') {
            echo json_encode(array(status => FALSE, error => $valid));
            return;
        }

        $sms = self::saveSmartMessage($input);

        $x = $this->smsmodel->sms_getXmlByTemplate($input->template->id);
        $xml = simplexml_load_string(str_replace('#template_path#', $this->config->item('sms_template_path'), $x[0]['xml_content']));

        $att = array();
        foreach ($xml->area as $key) {
            $att = array_merge($att, array_merge(self::getValidSelector($key), self::getValidAttributes($key)));
        }

        $selectors = self::saveSmsAttributes($input->template->selectors, $att);
        $sms["$this->smsid"]['selectors'] = $selectors['A'];

        $attributes = self::saveSmsAttributes($input->template->attributes, $att);
        $sms["$this->smsid"]['attributes'] = $attributes['A'];

        $jsargs = array(
            'data-sms_message_type' => $input->sms->message_type,
            'data-sms_rule' => $input->sms->rule,
            'data-sms_timeout' => $input->sms->duration ? $input->sms->duration : 0,
        );
        $jsargs['data-sms_message_position'] = $attributes['P'];
        $jsargs['data-sms_account_key2'] = $accountkey2;

        $dom_code = self::saveHtmlParameters($jsargs, $input->template);
        $sms["$this->smsid"]['sms_domcode'] = $dom_code;

        $curSms = $this->session->userdata('sms_configuration');
        $finalSms = ($curSms) ? $curSms + $sms : $sms;
        $this->session->set_userdata('sms_configuration', $finalSms);

        echo json_encode(array(status => TRUE, sms_id => $this->smsid, sms_html => $dom_code['[SMS_HTML]']));
    }

    /**
     * Deletes/Undelets the sms from the session variable if it hasn't been saved in the DB or keeps its ID
     * in a session variable to be deleted when the LPC is saved
     * @param boolean $del -- If TRUE, the SMS is marked to be deleted, if FALSE it is marked not to be deleted (Undelete)
     */
    public function deleteSms($del = TRUE) {
        $this->smsid = $this->input->post('smsid');
        if ((int) $this->smsid < 1) {
            $smsconf = (array) $this->session->userdata('sms_configuration');
            $smsconf["$this->smsid"]["delete"] = $del;
            $this->session->set_userdata('sms_configuration', $smsconf);
        } else {
            $curdel = $this->session->userdata('sms_delete');
            if (!$del && ($key = array_search($del, $curdel)) !== false) {
                unset($curdel[$key]);
                $finaldel = $curdel;
            } else if ($del) {
                $newdel = array($this->smsid);
                $finaldel = $curdel ? array_merge($curdel, $newdel) : $newdel;
            }
            $this->session->set_userdata('sms_delete', $finaldel);
        }
        echo json_encode(array(status => TRUE, sms_id => $this->smsid));
    }

    /**
     * Calls the deleteSms method with FALSE as parameter to "undelete" the SMS
     */
    public function undeleteSms() {
        echo self::deleteSms(FALSE);
    }

    /**
     * saves the smart message itself
     * @param array $input
     * @return array
     */
    private function saveSmartMessage($input) {
        $args = array();

        if ($input->template->attributes->bt_sms_position->value) {
            $args['position'] = $input->template->attributes->bt_sms_position->value;
        }if ($input->template->duration) {
            $args['duration'] = $input->template->duration;
        }

        if (!(int) $this->smsid > 0) {
            $this->smsid = md5(time()) . '-' . $this->session->userdata('sessionUserId');
        }

        $sms = array(
            "$this->smsid" => array(
                'smart_message' => array(
                    'SMS_' . $this->smsid,
                    $input->template->id,
                    $input->sms->rule,
                    json_encode($args),
                    json_encode($input->ui),
                )
            )
        );

        return $sms;
    }

    /**
     * First, deletes the previous attributes associated to the smart message id
     * then it checks if the attribute name pased in the array is in the list of valid arguments located in the XML file
     * if so, the attributes are saved in the DB
     * @param array $attr - array of attributes passed from the saveSMS method
     */
    private function saveSmsAttributes($element, $att) {
        $mp = '';
        $newatt = array();

        foreach ($element as $key => $value) {
            $t = $value->type;
            $v = $value->value;

            if ($t == 'checkbox' || $t == 'pulldown') {
                $t = 'int';
                $v = (int) $value->value;
            }

            if (in_array($key, $att)) {
                $newatt[] = array(
                    'name' => $key,
                    'type' => $t,
                    'value' => $v,
                    'field' => $t . '_value',
                );
                $mp .= $key == 'bt_sms_position' ? $v : '';
            }
        }
        return array('A' => $newatt, 'P' => $mp);
    }

    /**
     * returns the array of valid selector names to save or ignore the values that the user sent via ajax
     * @param array $area
     * @return array $s
     */
    private function getValidSelector($area) {
        $s = array();
        foreach ($area->selector as $key) {
            if ($key->attributes()->type != 'active') {
                $s[] = $key->attributes()->name;
            }
        }
        return $s;
    }

    /**
     * returns the array with the valid  attribute names
     * @param array $area
     * @return array $a
     */
    private function getValidAttributes($area) {
        $a = array();

        foreach ($area->block as $key) {
            foreach ($key->attribute as $value) {
                $a[] = str_replace('$', '', $value->name);
            }
        }
        return $a;
    }

    /**
     * adds the div with the sms parameters to the HTML to be saved as the dom_modification_code
     */
    private function saveHtmlParameters($jsargs, $template) {
        $htm = preg_replace('/(\n|\t|\s+)/', ' ', self::getPreview(json_encode($template)));

        $div = '<div id="_bt_sms_js_arguments" ';
        foreach ($jsargs as $key => $value) {
            $div .= $key . '="' . $value . '" ';
        }
        $div.= '></div>';

        if (substr_count($htm, '</body>') > 0) {
            $hcode = str_replace('</body>', $div . '</body>', $htm);
        } else {
            $hcode = $htm . $div;
        }

        $this->load->helper('url');
        $url = base_ssl_url() . 'js/smartmessaging.js';
        // transform to protocol-relative
        $url = str_replace('http://', '//',$url);
        $url = str_replace('https://', '//',$url);
        return array(
            '[SMS]' => "$('<style>#_bt_sms_main_container\{display:none;\}</style>$hcode').appendTo('body'); $.getScript('$url');",
            '[SMS_HTML]' => $hcode,
        );
    }

    /**
     * generates the HTML from the XML file located in the Database given a smart_message_id sent via AJAX
     */
    public function getHtml($tempid) {
        $x = $this->smsmodel->sms_getXmlByTemplate($tempid);
        $xml = simplexml_load_string($x[0]['xml_content']);
        $attr = $this->smsmodel->sms_getAttributes();
        $htm = '';

        foreach ($xml->area as $key) {
            $a1 = $key->selector->attributes();
            $htm.= ($a1->type == 'active') ? $key->block->source : self::getSourceBySelector($attr, $key);
        }

        $ret = self::replaceAttributeValues($attr, $htm, $xml);

        return $ret;
    }

    /**
     * receives the array of attributes and the area and returns the source for the matched element in the XML array
     * @param array $attr - array of sms_attributes
     * @param array $area - current area to be evaluated
     * @return string - the source contained in the given block
     */
    private function getSourceBySelector($attr, $area) {
        $ret = '';

        // this function returns the source located in the current area that matches the current Attribute value
        $returnSource = function($val) use ($area) {
            if ($area->selector->attributes()->type == 'checkbox') {
                return ($val == 1) ? $area->block->source : '';
            }

            $r = '';
            foreach ($area->block as $k) {
                $r.= ($k->attributes()->selector == $val) ? $k->source : '';
            }
            return $r;
        };

        foreach ($attr as $key) {
            if (!is_null($key['int_value']) && $area->selector->attributes()->name == $key['name']) {
                $ret .= $returnSource($key['int_value']);
            }
        }

        return $ret;
    }

    /**
     * Receives the array of attributes to replace the corresponding values in the piece of html corresonding to
     * the current block being evaluated.
     * if there are one or more values that hasn't any custom text, the default values are set.
     * @param array $attr - the array of attributes from the database
     * @param string $htm - the current html  block being evaluated
     * @return string - the html with the modified values
     */
    private function replaceAttributeValues($attr, $htm, $xml) {

        // If the attribute type is enum, it is replaced either by the db attribute value or the first element in the string
        $replaceEnum = function($var, $val, $htm) {
            $sArr = explode(';', $val);
            $oArr = explode('|', $sArr[0]);
            return str_replace($var, $oArr[1], $htm);
        };

        foreach ($attr as $key) {
            $htm = str_replace('$' . $key['name'], $key[$key['type'] . '_value'], $htm);
        }

        foreach ($xml->area as $k) {
            foreach ($k->block->attribute as $v) {
                $htm = ($v->type == 'enum') ? $replaceEnum($v->name, $v->default, $htm) : str_replace($v->name, $v->default, $htm);
            }
        }

        return $htm;
    }

}
