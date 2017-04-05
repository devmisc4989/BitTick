<?php

class wurfl extends CI_Controller {

    function __construct() {
        parent::__construct();
        define('WURFLDB', $this->config->item('wurfl_db'));
    }

    /**
     * Detects the device properties and show them in the browser
     */
    public function index() {
        require_once BASEPATH . 'application/libraries/wurfldb/TeraWurfl.php';
        $wurflObj = new TeraWurfl();
        $wurflObj->getDeviceCapabilitiesFromAgent();
        echo 'Device: ' . self::getDevice($wurflObj) . '<br />' .
        'Device OS: ' . $wurflObj->getVirtualCapability('advertised_device_os') . '<br />' .
        'OS version: ' . $wurflObj->getVirtualCapability('advertised_device_os_version') . '<br />' .
        'Browswer: ' . $wurflObj->getVirtualCapability('advertised_browser') . '<br />' .
        'Browser version: ' . $wurflObj->getVirtualCapability('advertised_browser_version') . '<br />' .
        'Width: ' . $wurflObj->getDeviceCapability('resolution_height') . '<br />' .
        'Height: ' . $wurflObj->getDeviceCapability('resolution_width') . '<br />' .
        'Is touch screen: ' . (int) $wurflObj->getVirtualCapability('is_touchscreen') . '<br />' .
        'Supports flash: ' . (int) $wurflObj->getDeviceCapability('full_flash_support') . '<br />';
    }

    /**
     * returns the device type according to the corresponding evaluated capabilities
     * @param array $wurflObj
     * @return string
     */
    private function getDevice($wurflObj) {
        $devices = array(
            'phone' => $wurflObj->getDeviceCapability('can_assign_phone_number'),
            'tablet' => $wurflObj->getDeviceCapability('is_tablet'),
            'pda' => $wurflObj->getVirtualCapability('is_mobile'),
            'smartrv' => $wurflObj->getDeviceCapability('is_smarttv'),
            'xtop' => TRUE,
        );

        foreach ($devices as $key => $value) {
            if ((int) $value == 1) {
                return $key;
            }
        }
    }

    /**
     * Creates the wurfl DB
     */
    public function install() {
        if (!file_exists(BASEPATH . 'application/libraries/wurfldb/admin/updatedb.php')) {
            echo '<h1>Object not found</h1><h2>Error 404</h2>';
            return;
        }
        $this->load->database();
        $sql = "CREATE DATABASE IF NOT EXISTS " . WURFLDB;
        $this->db->query($sql);
        echo 'installed, now you can <a href="https://blacktri-dev.de/wurfl/updatedb">update the DB</a>';
    }

    /**
     * Updates the DB in a development environment
     */
    public function updatedb() {
        if (!file_exists(BASEPATH . 'application/libraries/wurfldb/admin/updatedb.php')) {
            echo '<h1>Object not found</h1><h2>Error 404</h2>';
            return;
        }
        require_once BASEPATH . 'application/libraries/wurfldb/admin/updatedb.php';
    }

}
