<?php

class testcontroller extends CI_Controller {

    function __construct() {
        parent::__construct();
    }

    function wstest1() {
        $apiuser = authenticate();
        $arg = $this->uri->segment(3);
        echo "<h1>Webservice parameter: $arg<h1>";
    }

    function wstest2() {
        $apiuser = authenticate();
        $body = str_replace('body=', '', urldecode(file_get_contents('php://input')));

        $doc = new DOMDocument();
        $doc->loadXML($body);

        $customerfirstname = $doc->getElementsByTagName('firstname')->item(0)->nodeValue;
        $customerlastname = $doc->getElementsByTagName('lastname')->item(0)->nodeValue;
        $customeremail = $doc->getElementsByTagName('email')->item(0)->nodeValue;
        $subid = $doc->getElementsByTagName('subid')->item(0)->nodeValue;

        echo "<h1>Create new account</h1>";
        echo "email: $customeremail<br>";
        echo "etracker account-id: $subid<br>";
        echo "firstname: $customerfirstname<br>";
        echo "lastname: $customerlastname<br>";
    }

    function test() {
        $this->load->model('optimisation');
        $this->load->library('email');
        echo "test";
    }

    /*
     * some unittests for function optimisation->containsPattern
     */

    function testPatternMatching() {
        $this->load->model('optimisation');

        $urls = array(
            'http://www.blacktri.com',
            'http://www.blacktri.com/',
            'https://www.blacktri.com',
            'http://www.blacktri.com/pfad/',
            'http://www.blacktri.com/pfad',
            'http://www.blacktri.com/pfad/seite.html',
            'http://www.blacktri.com/pfad/seite.html?foo=bar',
            'http://www.blacktri.com/pfad/?foo=bar',
            'http://www.blacktri.com/pfad?foo=test'
        );
        $pattern = array(
            'www.blacktri.com',
            'http://www.blacktri.com',
            'https://www.blacktri.com',
            'www.blacktri.com/pfad',
            'www.blacktri.com/pfad/',
            'www.blacktri.com/pfad/?foo=bar',
            '*blacktri*eite.html',
            'www.blacktri.com/pfad/?foo=*',
            '*?foo=test'
        );

        foreach ($pattern as $pt) {
            echo "<b>$pt</b> matches:";
            foreach ($urls as $u) {
                if ($this->optimisation->containsPattern($u, $pt)) {
                    echo"<br>..... $u";
                }
            }
            echo "<br><br>";
        }
    }

    /*
     * some unittests for function optimisation->containsPattern
      http://blacktri-dev.de/testcontroller/testPatternMatching/
     */

    function testPatternMatchingInternal() {

        if ($this->input->get('secToken') == '6e0f60f1d6701165edc2a5af3deb566a') {

            $this->load->model('optimisation');
            $givenUrl = urldecode($this->input->get('givenUrl'));
            $testPattern = urldecode($this->input->get('testPattern'));


            header('Content-Type: application/json');
            $response = $this->optimisation->containsPattern($givenUrl, $testPattern);
            echo json_encode(array('givenUrl' => $givenUrl, 'testPattern' => $testPattern, 'response' => $response));
        } else {
            header('HTTP/1.0 403 Forbidden');
            die();
        }
    }

}

?>