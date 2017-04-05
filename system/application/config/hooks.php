<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
  | -------------------------------------------------------------------------
  | Hooks
  | -------------------------------------------------------------------------
  | This file lets you define "hooks" to extend CI without hacking the core
  | files.  Please see the user guide for info:
  |
  |	http://codeigniter.com/user_guide/general/hooks.html
  |
 */
// define a hook to set the language according to path-paramenet (/en/,/de/) and values stored in session
$hook['pre_controller'] = array(
    'class' => 'blacktri_filters',
    'function' => 'blacktri_filters',
    'filename' => 'blacktri_filters.php',
    'filepath' => 'hooks'
);
/* End of file hooks.php */
/* Location: ./system/application/config/hooks.php */