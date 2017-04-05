<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class developer_ajax extends CI_Controller {

    public function index() {

        //$this->load->library('sesssion');

        $this->load->helper('cookie');
        $lang_abbr = get_cookie('BT_lg');
        $tenant = get_cookie('BT_tenant') /* || 'blacktri' */;
        if ($lang_abbr === FALSE) {
            $lang_abbr = $this->config->item('language') == 'english' ? 'en' : 'de';
        }
        $output = array(
            'dev_data' => array(
                //'lang_abbr'=>$this->config->item('language'),
                'lang_abbr' => $lang_abbr,
                // 'tenant'=>$this->config->item('tenant')
                'tenant' => $tenant,
                'branch_path'=>BASEPATH
            ),
            'toolbar' => $this->load->view('developer_toolbar/dev_toolbar', '', true)
        );
        echo json_encode($output);
    }

}

?>