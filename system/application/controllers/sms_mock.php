<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class sms_mock extends CI_Controller {
    public function __construct()
    {
        parent::__construct();
    }
    
    public function index(){

    }

    function getPreview(){
        $data = json_decode(file_get_contents("php://input"));
     $html="<div id='messagepop' style='display:none; position:absolute; width:400px; height: auto; top:0; background-color: #EE0000; border:0;padding: 10px 20px; z-index:991109; font: bold 0.9em sans-serif; color:#FFFFFF; text-align: center; line-height: 20px;'>Error: the email address is in use, please select another and try again!</div>";
        echo $html;

    }

    function saveSms(){
        $data = json_decode(file_get_contents("php://input"));
        echo json_encode($data);
    }

    
}