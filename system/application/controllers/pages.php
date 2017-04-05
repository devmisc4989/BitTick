<?php

/**
 * Controller for public static (or more or less static....) pages like home, about, terms etc.
 */
class pages extends CI_Controller {

    function __construct() {
        parent::__construct();
        //$this->load->model('user'); // needed for display of admin-menu on public pages in case user is logged in
        doAutoload(); // load resources from autoload_helper
    }

    /**
     * default function displaying the start page
     */
    function index($action = '') {
        if ($this->config->item('tenant') == 'dvlight')
            redirect("http://abtester.divolution.com/404"); // no access for divolution light
        if ($this->config->item('tenant') == 'etracker')
            redirect("/pages/notfound/"); // no access for etracker
            
//echo "baseurl: " . $this->config->item('base_url');
        //echo "trace: " . $this->config->item('TRACECODE');
        //echo "profile: " . configuration_profile;die();
        // js array
        $arrJavascript[] = base_url() . 'js/jquery.tools.min.js';
        $arrJavascript[] = base_url() . 'js/jquery.cycle.all.min.js';
        $arrJavascript[] = base_url() . 'js/scripts.js';
        $pageid['js'] = $arrJavascript;
        $pageid['title'] = $this->lang->line('title_homepage');
        $pageid['description'] = $this->lang->line('home_metadescription');
        $pageid['activehome'] = "class=\"active\"";
        $this->load->view('includes/public_header', $pageid);
        $this->load->view('public/home');
        $this->load->view('includes/public_footer');
    }

    /*
     *  controller for dynamic robots.txt
     */

    function robots() {
        $this->load->view('public/robots');
    }

    /*
     *  controller for imprint page
     */

    function ip() {
        if ($this->config->item('dvlight'))
            redirect("http://abtester.divolution.com/404"); // no access for divolution light
        if ($this->config->item('tenant') == 'etracker')
            redirect("/pages/notfound/"); // no access for etracker
            
// page title
        $pageid['title'] = $this->lang->line('title_imprint');
        $pageid['headimg'] = base_url() . "images/logo_sml.png";
        $this->load->view('includes/public_header', $pageid);
        $this->load->view('public/imprint');
        $this->load->view('includes/public_footer');
    }

    /*
     *  controller for terms and conditions page
     */

    function tc() {
        if ($this->config->item('tenant') == 'dvlight')
            redirect("http://abtester.divolution.com/404"); // no access for divolution light
        if ($this->config->item('tenant') == 'etracker')
            redirect("/pages/notfound/"); // no access for etracker
            
// page title
        $pageid['title'] = $this->lang->line('title_tc');
        $pageid['headimg'] = base_url() . "images/logo_sml.png";
        $this->load->view('includes/public_header', $pageid);
        $this->load->view('public/tc');
        $this->load->view('includes/public_footer');
    }

    /*
     *  controller for about page
     */

    function abt() {
        if ($this->config->item('tenant') == 'dvlight')
            redirect("http://abtester.divolution.com/404"); // no access for divolution light
        if ($this->config->item('tenant') == 'etracker')
            redirect("/pages/notfound/"); // no access for etracker
            
// page title
        $pageid['title'] = $this->lang->line('title_about');
        $pageid['description'] = $this->lang->line('about_metadescription');
        $pageid['headimg'] = base_url() . "images/logo_sml.png";
        $this->load->view('includes/public_header', $pageid);
        $this->load->view('public/about');
        $this->load->view('includes/public_footer');
    }

    /*
     *  controller for help&support
     */

    function hs() {
        if ($this->config->item('tenant') == 'dvlight')
            redirect("http://abtester.divolution.com/404"); // no access for divolution light
        if ($this->config->item('tenant') == 'etracker')
            redirect("/pages/notfound/"); // no access for etracker
            
        $pageid['title'] = $this->lang->line('title_helpsupport');
        $pageid['headimg'] = base_url() . "images/logo_sml.png";
        $pageid['activehs'] = "class=\"active\"";
        $this->load->view('includes/public_header', $pageid);
        $this->load->view('public/helpsupport');
    }

    /*
     *  controller for tour page
     */

    function tour() {
        if ($this->config->item('tenant') == 'dvlight')
            redirect("http://abtester.divolution.com/404"); // no access for divolution light
        if ($this->config->item('tenant') == 'etracker')
            redirect("/pages/notfound/"); // no access for etracker
            
// js array
        // page title
        $pageid['title'] = $this->lang->line('title_tour');
        $pageid['description'] = $this->lang->line('tour_metadescription');
        $pageid['headimg'] = base_url() . "images/logo_sml.png";
        $pageid['activetr'] = "class=\"active\"";
        $this->load->view('includes/public_header', $pageid);
        $this->load->view('public/tour');
        $this->load->view('includes/public_footer');
    }

    /*
     *  controller for teasertool landing page
     */

    function teasertool() {
        if ($this->config->item('tenant') == 'etracker')
            redirect("/pages/notfound/"); // no access for etracker
            
        // page title
        $pageid['title'] = $this->lang->line('title_teasertool');
        $pageid['description'] = $this->lang->line('teasertool_metadescription');
        $pageid['headimg'] = base_url() . "images/logo_sml.png";
        $pageid['activetr'] = "class=\"active\"";
        $this->load->view('includes/public_header', $pageid);
        $this->load->view('public/teasertool');
        $this->load->view('includes/public_footer');
    }

    /*
     *  controller for pricing details
     *  CURRENTLY NOT USED... wait for public launch
     */

    function pd() {
        if ($this->config->item('tenant') == 'dvlight')
            redirect("http://abtester.divolution.com/404"); // no access for divolution light
        if ($this->config->item('tenant') == 'etracker')
            redirect("/pages/notfound/"); // no access for etracker
            
// page title
        $pageid['title'] = $this->lang->line('title_pricing');
        $pageid['description'] = $this->lang->line('pricing_metadescription');
        $pageid['headimg'] = base_url() . "images/logo_sml.png";
        $pageid['activeplans'] = "class=\"active\"";
        $this->load->view('includes/public_header', $pageid);
        $this->load->view('public/pricing');
        $this->load->view('includes/public_footer');
    }

    /*
     *  controller for subscription confirmation page (called after user opted in in list and sent form)
     */

    function subc() {
        if ($this->config->item('tenant') == 'etracker')
            redirect("/pages/notfound/"); // no access for etracker
            
// page title
        $pageid['title'] = $this->lang->line('title_subscription');
        $pageid['description'] = "";
        $pageid['headimg'] = base_url() . "images/logo_sml.png";
        $this->load->view('includes/public_header', $pageid);
        $this->load->view('public/subc');
        $this->load->view('includes/public_footer');
    }

    /*
     *  controller for confirmation after click on verification link
     */

    function subth() {
        if ($this->config->item('tenant') == 'etracker')
            redirect("/pages/notfound/"); // no access for etracker
            
// page title
        $pageid['title'] = $this->lang->line('title_subscription');
        $pageid['description'] = "";
        $pageid['headimg'] = base_url() . "images/logo_sml.png";
        $this->load->view('includes/public_header', $pageid);
        $this->load->view('public/subth');
        $this->load->view('includes/public_footer');
    }

    /*
     *  controller for signup test user info
     */

    function stui() {
        if ($this->config->item('tenant') == 'etracker')
            redirect("/pages/notfound/"); // no access for etracker
            
// page title
        $pageid['title'] = $this->lang->line('title_subscription');
        $pageid['hidenavi'] = true;
        // additional css
        $pageid['others'] = "
			<style type='text/css'>
				html { background-color:white; }
				#inner_bg { background:none; background-color:#FFF; }
			</style>
		";
        $this->load->view('includes/public_header', $pageid);
        $this->load->view('public/test_user_info');
        $this->load->view('includes/public_footer');
    }

    /*
     *  controller for signup test user done
     */

    function stud() {
        if ($this->config->item('tenant') == 'etracker')
            redirect("/pages/notfound/"); // no access for etracker
            
// page title
        $pageid['title'] = $this->lang->line('title_subscription');
        $pageid['hidenavi'] = true;
        // additional css
        $pageid['others'] = "
			<style type='text/css'>
				html { background-color:white; }
				#inner_bg { background:none; background-color:#FFF; }
			</style>
		";

        $this->load->view('includes/public_header', $pageid);
        $this->load->view('public/test_user_done');
        $this->load->view('includes/public_footer');
    }

    /*
     *  controller for custom 404 errorpage
     */

    function notfound() {
        if ($this->config->item('tenant') == 'blacktri') {
            // page title
            $pageid['title'] = $this->lang->line('title_notfound');
            $pageid['headimg'] = base_url() . "images/logo_sml.png";
            $this->load->view('includes/public_header', $pageid);
            $this->load->view('public/notfound');
            $this->load->view('includes/public_footer');
        } else {
            header('HTTP/1.0 404 Not Found');
            echo "<h1>Object not found</h1><h2>Error 404</h2>";
        }
    }

    /*
     * controller for cases database
     */

    function cases() {
        if ($this->config->item('tenant') == 'etracker')
            redirect("/pages/notfound/"); // no access for etracker
        if ($this->config->item('tenant') == 'dvlight')
            redirect("http://abtester.divolution.com/404"); // no access for divolution light
            
// PRELIMINARY begin
        // construct an array of filter parameters
        $dofilter = ($this->input->get('action') == 'flt') ? true : false;
        $querykey = $this->input->get('filter');
        $querygroup = $this->input->get('grp');
        $filterkeys = array('pt_lp', 'pt_op', 'pt_ws', 'cg_o', 'cg_l', 'cg_e');

        // set filter values to construct a database query
        if ($dofilter) {
            foreach ($filterkeys as $key) {
                // initialize to a value taken from the session or "not filtered" else
                $sessionkey = $this->session->userdata($key);
                if ($sessionkey) {
                    $filter[$key]['value'] = ($sessionkey == "set") ? true : false;
                } else {
                    $filter[$key]['value'] = true;
                }
                // now check for filters in the querystring
                // only handle those filters from the same group as the one in the querystring
                if (substr($key, 0, 2) == $querygroup) {
                    if ($querykey == $key) {
                        // set the selected filter to "not filtered
                        $filter[$key]['value'] = true;
                        $this->session->set_userdata($key, "set");
                    } elseif ($querykey == ($querygroup . "_all")) {
                        // if the "More" link has been clicked, reset all filters to "not filtered"
                        $filter[$key]['value'] = true;
                        $this->session->unset_userdata($key);
                    } else {
                        // set all not selected filters to "filter this out!"
                        $filter[$key]['value'] = false;
                        $this->session->set_userdata($key, "unset");
                    }
                }
            }
        }

        $results = $this->cases->getFilteredCases($dofilter, $filter);
        //print_r($results);
        // add facet values to the $filter array
        $fc = $results['facets'];
        foreach ($filterkeys as $key) {
            $filter[$key]['facetcount'] = (isset($fc[$key])) ? $fc[$key] : 0;
            if ($filter[$key]['facetcount'] == 0) {
                $filter[$key]['visibility'] = 'hidden';
            } else {
                $filter[$key]['visibility'] = 'visible';
            }
        }

        // PRELIMINARY end
        // page title
        $pageid['title'] = $this->lang->line('title_cases');
        $pageid['headimg'] = base_url() . "images/logo_sml.png";
        $data['results'] = $results['entries'];
        $data['filter'] = $filter;
        $data['moreresults'] = $results['moreresults'];
        $this->load->view('includes/public_header', $pageid);
        $this->load->view('public/cases', $data);
        $this->load->view('includes/public_footer');
    }

    // demo generic email marketing
    function emm() {
        if ($this->config->item('tenant') == 'etracker')
            redirect("/pages/notfound/"); // no access for etracker
            
        $pageid['title'] = $this->lang->line('Demo BlackTri Optimizer Platform');
        $pageid['clientname'] = "Email-Marketing-Unternehmen";
        $pageid['productname'] = "Email-Marketing-Systemen";
        $this->load->view('includes/public_header', $pageid);
        $this->load->view('public/emailmarketingdemo',$pageid);
        $this->load->view('includes/public_footer');
    }

    // demo for optivo
    function opdm() {            
        $pageid['title'] = $this->lang->line('Demo BlackTri Optimizer Platform');
        $pageid['clientname'] = "optivo";
        $pageid['productname'] = "optivo broadmail";
        $this->config->set_item('tenant','etracker');
        $this->load->view('includes/protected_header', $pageid);
        $this->load->view('public/emailmarketingdemo_et',$pageid);
        $this->load->view('includes/public_footer');
    }

    // demo for ecircle
    function ecdm() {
        if ($this->config->item('tenant') == 'etracker')
            redirect("/pages/notfound/"); // no access for etracker
            
        $pageid['title'] = $this->lang->line('Demo BlackTri Optimizer Platform');
        $pageid['clientname'] = "eC-messenger";
        $pageid['productname'] = $pageid['clientname'];
        $this->load->view('includes/public_header', $pageid);
        $this->load->view('public/emailmarketingdemo',$pageid);
        $this->load->view('includes/public_footer');
    }

    // demo for inxmail
    function ixdm() {
        if ($this->config->item('tenant') == 'etracker')
            redirect("/pages/notfound/"); // no access for etracker
            
        $pageid['title'] = $this->lang->line('Demo BlackTri Optimizer Platform');
        $pageid['clientname'] = "Inxmail";
        $pageid['productname'] = "Inxmail Professional";
        $this->load->view('includes/public_header', $pageid);
        $this->load->view('public/emailmarketingdemo',$pageid);
        $this->load->view('includes/public_footer');
    }

    // demo for intelliad
    function iadm() {
        if ($this->config->item('tenant') == 'etracker')
            redirect("/pages/notfound/"); // no access for etracker
            
        $pageid['title'] = $this->lang->line('Demo BlackTri Optimizer Platform');
        $pageid['clientname'] = "intelliAd";
        $pageid['productname'] = "intelliAd";
        $this->load->view('includes/public_header', $pageid);
        $this->load->view('public/ppcdemo',$pageid);
        $this->load->view('includes/public_footer');
    }

    // demo for crealytics
    function cldm() {
        if ($this->config->item('tenant') == 'etracker')
            redirect("/pages/notfound/"); // no access for etracker
            
        $pageid['title'] = $this->lang->line('Demo BlackTri Optimizer Platform');
        $pageid['clientname'] = "crealytics";
        $pageid['productname'] = "camato";
        $this->load->view('includes/public_header', $pageid);
        $this->load->view('public/ppcdemo',$pageid);
        $this->load->view('includes/public_footer');
    }

    
}

// class ends here
?>