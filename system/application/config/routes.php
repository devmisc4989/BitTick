<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
  | -------------------------------------------------------------------------
  | URI ROUTING
  | -------------------------------------------------------------------------
  | This file lets you re-map URI requests to specific controller functions.
  |
  | Typically there is a one-to-one relationship between a URL string
  | and its corresponding controller class/method. The segments in a
  | URL normally follow this pattern:
  |
  | 	example.com/class/method/id/
  |
  | In some instances, however, you may want to remap this relationship
  | so that a different class/function is called than the one
  | corresponding to the URL.
  |
  | Please see the user guide for complete details:
  |
  |	http://codeigniter.com/user_guide/general/routing.html
  |
  | -------------------------------------------------------------------------
  | RESERVED ROUTES
  | -------------------------------------------------------------------------
  |
  | There are two reserved routes:
  |
  |	$route['default_controller'] = 'welcome';
  |
  | This route indicates which controller class should be loaded if the
  | URI contains no data. In the above example, the "welcome" class
  | would be loaded.
  |
  |	$route['scaffolding_trigger'] = 'scaffolding';
  |
  | This route lets you set a "secret" word that will trigger the
  | scaffolding feature for added security. Note: Scaffolding must be
  | enabled in the controller in which you intend to use it.   The reserved
  | routes must come before any wildcard or regular expression routes.
  |
 */

$route['404_override'] = "pages/notfound";

// dynamic google robots.txt
$route['robots.txt'] = "pages/robots";

// admin tracecode route
$route['support'] = "admin/tc";

// API routes
$route['api/v2/:any'] = "apiv2/dispatch";
$route['api/v1/:any'] = "apiv1/handleRequests";

// defnition of nice and seo-friendly URLs
$route['de'] = "";
$route['de/impressum'] = "pages/ip";
$route['de/agb'] = "pages/tc";
$route['de/features'] = "pages/tour";
$route['de/teasertool'] = "pages/teasertool";
$route['de/preise'] = "pages/pd";
$route['de/hilfe'] = "pages/hs";
$route['de/unternehmen'] = "pages/abt";
$route['de/erfolgreiche-ab-tests'] = "pages/cases";

$route['de/registrieren:any'] = "users/su";
$route['de/registrieren'] = "users/su";

$route['de/testregistrieren:any'] = "users/sutest";
$route['de/testregistrieren'] = "users/sutest";

$route['testen'] = "pages/pd";

$route['de/login'] = "users/si";
$route['de/logout'] = "users/logout";
$route['de/willkommen:any'] = "users/ecc";
$route['de/willkommen'] = "users/ecc";
$route['de/bestaetigen:any'] = "users/ec";
$route['de/bestaetigen'] = "users/ec";
$route['de/meinetests'] = "lpc/cs";


$route['en'] = "";
$route['en/imprint'] = "pages/ip";
$route['en/terms'] = "pages/tc";
$route['en/plans'] = "pages/pd";
$route['en/tour'] = "pages/tour";
$route['en/help'] = "pages/hs";
$route['en/about'] = "pages/abt";
$route['en/successful-ab-tests'] = "pages/cases";
$route['en/testregister:any'] = "users/sutest";
//$route['en/register'] = "users/su";
$route['en/login'] = "users/si";
$route['en/logout'] = "users/logout";
$route['en/welcome:any'] = "users/ecc";
$route['en/welcome'] = "users/ecc";
$route['en/confirm:any'] = "users/ec";
$route['en/confirm'] = "users/ec";
$route['en/mytests'] = "lpc/cs";

$route['default_controller'] = "pages";
$route['scaffolding_trigger'] = "";

$route['jsi18n/jqueryValidationEngine.js'] = "jsi18n/jqueryValidationEngine";

/* End of file routes.php */
/* Location: ./system/application/config/routes.php */