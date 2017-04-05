<?php
/**
* Library to encapsulate device detection via the WURFL project
*/

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Wurfl {
    private $visitor; // holds the bto visitor array;
    private $deviceType; 
    private $deviceOs; 
    private $deviceBrowser; 
    private $wurflObj;

    // Constructor takes the optimisation webservice visitor array as an argument
    public function __construct($visitor) {
        $this->visitor = $visitor;
        $CI = & get_instance();
        define('WURFLDB', $CI->config->item('wurfl_db'));
        define('WURFLDBUSER', $CI->db->username);
        define('WURFLDBPW', $CI->db->password);
        require_once BASEPATH . 'application/libraries/wurfldb/TeraWurfl.php';
        $wurflObj = new TeraWurfl();
        $this->wurflObj = $wurflObj;
        $wurflObj->getDeviceCapabilitiesFromAgent();
        
        // derive type of device from wurfl capabilities
        if($wurflObj->getDeviceCapability('is_tablet')) {
            $this->deviceType = 'STR_CC_ATTR_VALUE_DEVICE_TYPE_TABLET';
        }

        elseif($wurflObj->getDeviceCapability('is_wireless_device')==false) {
            $this->deviceType = 'STR_CC_ATTR_VALUE_DEVICE_TYPE_DESKTOP';
        }
        elseif($wurflObj->getDeviceCapability('can_assign_phone_number')) {
            $this->deviceType = 'STR_CC_ATTR_VALUE_DEVICE_TYPE_MOBILE_PHONE';            
        }
        else {
            $this->deviceType = 'STR_CC_ATTR_VALUE_DEVICE_TYPE_OTHERS';                        
        } 

        $this->deviceOs = $wurflObj->getVirtualCapability('advertised_device_os');
        $this->deviceBrowser = $wurflObj->getVirtualCapability('advertised_browser');

    }

    public function getDeviceType() {
        return $this->deviceType;
    }

    public function getDeviceOs() {
        return $this->deviceOs;
    }

    public function getDeviceBrowser() {
        return $this->deviceBrowser;
    }

    public function showCapabilities() {
        echo 'Device OS: ' . $this->wurflObj->getVirtualCapability('advertised_device_os') . '<br />' .
        'OS version: ' . $this->wurflObj->getVirtualCapability('advertised_device_os_version') . '<br />' .
        'Browswer: ' . $this->wurflObj->getVirtualCapability('advertised_browser') . '<br />' .
        'Browser version: ' . $this->wurflObj->getVirtualCapability('advertised_browser_version') . '<br />' .
        'Width: ' . $this->wurflObj->getDeviceCapability('resolution_height') . '<br />' .
        'Height: ' . $this->wurflObj->getDeviceCapability('resolution_width') . '<br />' .
        'Is touch screen: ' . (int) $this->wurflObj->getVirtualCapability('is_touchscreen') . '<br />' .
        'Is wireless: ' . (int) $this->wurflObj->getDeviceCapability('is_wireless_device') . '<br />' .
        'Claims web support: ' . (int) $this->wurflObj->getDeviceCapability('device_claims_web_support') . '<br />' .
        'Supports flash: ' . (int) $this->wurflObj->getDeviceCapability('full_flash_support') . '<br />';
    }
}