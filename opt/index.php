<?
session_start();

$timetrack = false;
if($timetrack) $basetime = microtime(true);
//error_reporting(E_ALL);

if(isset($_REQUEST['blacktriurl'])) {
	$url = trim($_REQUEST['blacktriurl']);
}
else {
	$url = "";
}
/* includes */
define('BASEPATH', '/');
include "../system/application/config/config.php";
include "../system/application/config/database.php";
if(!isset($config)) {
	// on eckhards machine, this include does not work. Thus we use this dirty hack: if the 
	// include fails, use a hardwired path
	$path = $_SERVER['DOCUMENT_ROOT'] . '/system/application';
	set_include_path(get_include_path() . PATH_SEPARATOR . $path);
	include "config/config.php";
	include "config/database.php";
}

if(isset($_REQUEST['protocol']))
	$nossl = false;
else 
	$nossl = true;


if($nossl){
	$config['base_url'] = str_replace('https://', 'http://', $config['base_url']);
	$config['base_ssl_url'] = str_replace('https://', 'http://', $config['base_ssl_url']);
	$config['editor_url'] = str_replace('https://', 'http://', $config['editor_url']);
}

include "library/curl.php";
include "library/utils.php";

if( $url!= "")
{
	//echo "editor-url" . $config['editor_url'];
	//check first request and get final url after redirection, for example www.hess-natur.de does redirect	
    $deviceId = 'desktop';
    if(isset($_REQUEST['device'])) {
        $deviceId = trim($_REQUEST['device']);
    }
    $userAgent = getUserAgentByDevice($deviceId);
    $_SESSION["DEVICEID"] = $deviceId;
    
	$curl = new CURL();
		
    $curl->setUserAgent($userAgent);
	
	$curl->addSession($url);
	$result = $curl->exec();
	$info = $curl->info(); $info = $info[0];
	$curl->close();
	if($url != $info["url"] && $info["url"]!="")
		$url = $info["url"];
	$url = str_replace('HTTP','http', str_replace('HTTPS', 'http', $url));
	//get url rules based on url's from database
	$rules = getUrlRules($url);
	
	$_SESSION["RULES"] = $rules;
	$replaceStrings = getUrlReplaceStrings($url);
	$_SESSION["REPLACESTRINGS"] = $replaceStrings;

    // approach to extract a valid path:
    // - parse the url and extract the path
    // - take the path. if it ends with a scriptname, remove the scriptname
    // - if the path ends with a controller-like name, remove the controller name
    // - if the path ends with something like a directory, keep this
    // - a scriptname is a string that contains an extenstion or does not end
    // with a slash.
    // an extension is identified from an array
    // after removing the script the file should end with a /
    $myurl = $url;
    $t = parse_url($myurl);
    if(!isset($t['path']))
        $t['path'] = "";
    $path = $t['path'];
    // 1. try to extract scriptname with extension
    preg_match("/[^\/]*[\/]{0,1}$/i",$path,$matches); // matches the last part of the path, including a trailing /
    $lastsegment = $matches[0];
    // 2. If the last segment looks like a controller, remove it
    // controller == string which does not include a dot and does not end with a /
    $removeLastSegment = false;
    if((strpos($lastsegment,".") === false) && (substr($lastsegment, -1) !== "/")) {
        $removeLastSegment = true;
    }
    else {
        // 3. if the lastsegment is a script (contains a known extension), then remove it from the path
        $extstring = "asp,aspx,axd,asx,asmx,ashx,css,cfm,yaws,swf,html,htm,xhtml,jhtml,jsp,jspx,wss,do,action,js,pl,php,php4,php3,phtml,py,rb,rhtml,xml,rss,svg,cgi,dll";
        $extensions = explode(',',$extstring);
        foreach($extensions as $extension) {
            if(stripos($lastsegment,"." . $extension) !== false) {
                $removeLastSegment = true;
                break;
            }
        }
    }
    if($removeLastSegment) { // remove the last segment from the path
        $path = str_replace($lastsegment, "", $path);
    }
    // 4. avoid a complete empty path
    if($path == "")
        $path = "/";
    $t['path'] = $path;
	$_SESSION["TARGETURL"] = $t;
	
	if(isset($_REQUEST['client']))
		$_SESSION["CLIENT"] = $_REQUEST['client'];
	else 
		$_SESSION["CLIENT"] = 1;
	if(isset($_REQUEST['noscripts']))
		$_SESSION["NOSCRIPTS"] = $_REQUEST["noscripts"] == "yes";	
	else 
		$_SESSION["NOSCRIPTS"] = false;
    	
	$redir = getRedirectUrl($url);
	if(!$nossl) {
		if(strpos($redir,'?') === false)
			$redir .= "?protocol=ssl";
		else
			$redir .= "&protocol=ssl";
	}
		
	//keep original url
	$_SESSION['REDIRURL'] = str_replace($config['editor_url'],'/', $redir);
	
	header("Location: $redir");
	exit(0);
}
//session_write_close();

$document_domain = $config['document_domain'];
$base_url = $config['base_url'];
$base_ssl_url = $config['base_ssl_url'];
$forcedInject = false;

$TARGETURL = $_SESSION["TARGETURL"];
$CURRENTURI = Encoding::toUTF8($_SERVER["REQUEST_URI"]);
$URI = $CURRENTURI;

if(stripos($URI, 'btinject/') !== false)
{
	$forcedInject = true;
	$URI = str_replace('btinject/', '', $URI);
}

$CLIENT = $_SESSION["CLIENT"] * 1;

$DEVICEID = $_SESSION["DEVICEID"];
$userAgent = getUserAgentByDevice($DEVICEID);

//check cookie folder
if(isset($config['opt_cookie_base_path'])) {
	$cookie_folder = $config['opt_cookie_base_path'] . '/data/'.$CLIENT.'/logs/cookies/';
}
else {
	$cookie_folder = dirname(__FILE__).'/data/'.$CLIENT.'/logs/cookies/';
}
if(!file_exists($cookie_folder))
	mkdir($cookie_folder, 0777, true);

//get main url content
if($URI!="")
{
	//logging
	$domain = $TARGETURL["host"];
	$isRelative = false;
	$isProxy = false;
	$isRoot = false;
	$referer = $TARGETURL["scheme"]."://".$TARGETURL["host"];
	$scheme = $TARGETURL["scheme"];
	$cachePos = strpos($URI, "btcache-");
    $proxyPos = strpos($URI, "btproxy/");
    $relPos = strpos($URI, "/btrel/");    
	$burl = "";
	
	if($proxyPos!==false)
		$isProxy = true;
	if($cachePos!==false)
		$isRelative = true;
	if($relPos!==false)
		$isRelative = true;
	if($isProxy)
	{
		//remove first
		$pos = stripos($URI,"/", $proxyPos + 1);
		if($pos!==false)
		{
			$burl = substr($URI,$pos + 1);

			// handle protocol-relative URLs
			if(!(substr( $burl, 0, 4 ) === "http"))	    		    
			    $burl = preg_replace('/\//', $scheme . '/', $burl, 1);
			
			$burl = str_replace("https/", "https://", str_replace("http/", "http://", $burl) );
		}
	}
	else if($isRelative)
	{
		if($relPos!==false)
		{
			//remove /btrel/
			$burl = str_replace('/btrel/','/',$URI);
			//replace http/ with http://
			$burl = str_replace("/https/", "https://", str_replace("/http/", "http://", $burl) );
		}
		else if($cachePos!==false)
		{
			//remove first
			$pos = stripos($URI,"/",$cachePos + 1);
			if($pos!==false)
			{
				$URI = substr($URI,$pos + 1);
				$burl = str_replace("https/", "https://", str_replace("http/", "http://", $URI) );
				//echo "burl : $burl";
			}
		}
				
		if(strpos($URI,'http') === false) {
			$path = $TARGETURL["path"];
			$burl = $TARGETURL["scheme"]."://".$TARGETURL["host"].$path.$burl;
		}
	//OptLog("###burl: " . $burl, $domain, $CLIENT);
	}
	else
    {
		$isRoot = true;
		if(strpos($URI,'http') !== false) {
			// in case this is not really a false URL, but it starts with http, then 
			// treat it as an absolute URL and do not attach it to the
			// original domain
			$burl = str_ireplace('https/', 'https://', $URI);
			$burl = str_ireplace('http/', 'http://', $burl);
		}
		else {
			$burl = $TARGETURL["scheme"]."://".$TARGETURL["host"].(substr($URI,0,1)=='/'?"":"/").$URI;
		}
    }
	//url space fix for CURL;
	$burl = str_replace(" ", "+", $burl);
	
	if($timetrack) echo "before curl " . ($basetime - microtime(true)) . "<br>";
	
	//initialize global CURL info
	$curl = new CURL();
    $curl->setUserAgent($userAgent);
	$curl->addSession($burl);
	//get request method
	$requestMethod = strtolower($_SERVER['REQUEST_METHOD']);

	OptLog("0 -\nProxy link: ".($isProxy?"yes":"no")."\nLink type: ".($isRelative?"relative":($isProxy?"proxy":"root"))."\nURI:".$URI. "\nRequest URL:".$burl."\n Method: ".$requestMethod, $domain, $CLIENT);
    OptLog("0.1 - Device: ".$DEVICEID."\nUserAgent: ".$userAgent, $domain, $CLIENT);

	if($requestMethod=="post" || $requestMethod=="put")
	{			
		$curl->setOpt(CURLOPT_POST, true);
		//http 1.0 -> 1.1 fix
		$curl->setOpt(CURLOPT_HTTPHEADER, array('Expect:'));
		//read request body
		$requestBody = @file_get_contents('php://input');
		$curl->setOpt(CURLOPT_POSTFIELDS, $requestBody);
		
		OptLog("9 - POST: Proxy link: ".($isProxy?"yes":"no")."\nLink type: ".($isRelative?"relative":($isProxy?"proxy":"root"))."\nURI:".$URI. "\nRequest URL:".$burl. " Post data:".print_r($_POST, true)." \nBody: $requestBody", $domain, $CLIENT);
	}
	
	$urldata = parse_url($burl);
	$cookiedomain = $urldata["host"];
	$requestHeaders = getallheaders();
	//set cookie store
	$curl->setOpt(CURLOPT_COOKIEJAR, $cookie_folder.$cookiedomain.".txt");
	$curl->setOpt(CURLOPT_COOKIEFILE, $cookie_folder.$cookiedomain.".txt");
	
	//set required request headers
	if(isset($requestHeaders['X-Requested-With']))
		$curl->setOpt(CURLOPT_HTTPHEADER, array('X-Requested-With: '.$requestHeaders['X-Requested-With']));
	
	if($isProxy)
	{
		$proxydomain = $cookiedomain;
		$refererUrl = $urldata["scheme"] ."://".$urldata["host"];
		//set cookie store
		$curl->setOpt(CURLOPT_COOKIEJAR, $cookie_folder.$proxydomain.".txt");
		$curl->setOpt(CURLOPT_COOKIEFILE, $cookie_folder.$proxydomain.".txt");
		//set referer
		$curl->setOpt(CURLOPT_REFERER, $refererUrl);
		//exec
		$result = $curl->exec();
		$info = $curl->info(); $info = $info[0];
		$curl->close();
		
		//OptLog("9 - POST: proxydomain: $proxydomain, RETURN: !< $result >!\n".print_r($info,true), $domain, $CLIENT);
	}
	else
	{
		//set referer
		$curl->setOpt(CURLOPT_REFERER, $referer);
		//exec request
		$result = $curl->exec();
		$info = $curl->info(); $info = $info[0];
		$curl->close();
	}
	
	//check for gzip compression
	if(!array_key_exists('Content-Encoding', $curl->header)) {
		$curl->header['Content-Encoding'] = 'NA';
	}
	if( strtolower($curl->header['Content-Encoding']) == 'gzip')
	{
		$checkGzip = gzinflate(substr($result, 10));
		if($checkGzip!==FALSE)
		{
			$result = $checkGzip;
			unset($curl->header['Content-Encoding']);
		}
	}		

	//echo $result;die();
	if($timetrack) echo "after curl " . ($basetime - microtime(true)) . "<br>";
	
	OptLog("1 - ".$info['http_code'].", ".$info['content_type']."\nURI:".$URI. "\nURL:".$info['url'], $domain, $CLIENT);
	
	//if we have content type text/html and it's not error 404 page then add our scripts again
	$pos = stripos(trim($info['content_type']),'text/html');
	
	//do inject if current uri matches rediruri.
	$injectScript = $CURRENTURI == $_SESSION['REDIRURL'];
	if($injectScript || $forcedInject/*$pos!==false && $pos == 0*/)
	{
		if(stripos($result, '<head')===false || 
			stripos($result, '</head>')===false || 
			(stripos($result, '</body>')===false)&&(stripos($result, '</html>')!==false)) {
			showInvalidHtml();
			exit(0);
		}
		if( stripos($result, '</body>')===false && stripos($result, '</html>')===false) {
			$result .= "</body></html>";
		}
		if(stripos($result, '</html>')===false) {
			$result .= "</html>";
		}
		OptLog("2 - ".$info['http_code'].", found text/html page, injecting bt scripts - ".$burl, $domain, $CLIENT);
				
		//check to see if we have gs.js script in page and remove it when editting 
		if( $_SESSION["NOSCRIPTS"] ) {
			$result = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $result);
		}
		else {
			$result = remove_scripts($result);
		}
				
		// exchange the UTF-8 encoding of & to the original, because it makes problems in URLs 
		$result = str_ireplace("&#038;", "&", $result);
		
		// rewrite all URLs and get usefull info
		if($timetrack) echo "before rewrite " . ($basetime - microtime(true)) . "<br>";
		$parsed_result = rewriteDocumentUrls($result,$TARGETURL["scheme"],$TARGETURL["host"]);
				
		OptLog("4 - BASE HREF: ".$parsed_result['basehref'], $domain, $CLIENT);
		
		//get original body content to use for editor
        //$originalBodyContent = $parsed_result['bodycontent'];
		$result = $parsed_result['content'];
		$metatags = $parsed_result['meta'];
		if($timetrack) echo "after rewrite " . ($basetime - microtime(true)) . "<br>";
		
        //$result = replaceSitespecificStrings($result);	
        $result = preg_replace('/<meta.+(?=x-ua-compatible)([^>]+)?\/?>/i', '', $result);
		//add our head stuff
		$head = getHeadScripts($domain, $info['content_type'],$parsed_result['basehref']);
        $head.=getEditorStyles();
		$result = preg_replace('@(<head[^>]*>)@six',"$1$head", $result, 1);		

		/* to be removed */
		//add our scripts, moved to header scripts
		//$code = getWorkingScripts($domain, $info['content_type'], $originalBodyContent);
		//$code = getWorkingScripts($domain, $info['content_type'], '');
		//$result = preg_replace('@(</html>)@six',"$code$1", $result, 1);
		
		//set ie headers just in case
		header('X-UA-Compatible: IE=9');

		//set headers from the proxied headers list
		foreach($proxiedHeadersHtml as $headerName) {
			if(array_key_exists($headerName, $curl->header))
			{
				$headers = $curl->header[$headerName];
				if(is_array($headers))
				{
					//get last header
					$headerValue = $headers[count($headers)-1];
				}
				else {
					header("$headerName: ".$headers);						
					$headerValue = $headers;
				}
				header("$headerName: ".$headerValue);
				if(strtolower($headerName) == 'content-type') {
					$contentHeader = $headerValue;
				}
			}
		}
		// problem with charset: if not set in the header, then PHP automatically adds utf-8 as default
		// which would override a charset defined in a meta tag. Handle this by:
		// - if charset is not set AND charset is set in meta tag
		// then set it in the HTTP header
		//print_r($metatags);
		if((array_key_exists('content-type', $metatags)) && (empty($contentHeader))) {
			header("Content-Type: ". $metatags['content-type']);
		}
	}
	else
	{
		//set headers from the proxied headers list
		foreach($proxiedHeadersOtherResources as $headerName)
			if(array_key_exists($headerName, $curl->header))
			{
				$headers = $curl->header[$headerName];
				if(is_array($headers))
				{
					//get last header
					header("$headerName: ".$headers[count($headers)-1]);
				}
				else
					header("$headerName: ".$headers);	
			}
	}
	
	// if we have a CSS file, check for background images and absolute links to be replaced
	$pos = stripos(trim($info['content_type']),'text/css');
	if( $pos!==false && $pos == 0 )
	{
		//rewrite background images
		$result = rewriteExternalCSSUrls($result,$TARGETURL["scheme"],$TARGETURL["host"]);
	}
	
	$pos = stripos(trim($info['content_type']),'text/javascript');
	if( $pos!==false && $pos == 0 )
	{
		//rewrite inline script url's
		$result = rewriteExternalScriptUrls($result,$TARGETURL["scheme"],$TARGETURL["host"]);
	}
	//header('Content-type: '.$info['content_type']);
	//header('Content-Encoding: '.$curl->header['Content-Encoding']);
	
	
	//get end scripts
	$bodyEndScripts = getEndScripts();
	$result = preg_replace('@(<\/body>)@six',"$bodyEndScripts$1", $result, 1);		
	
	
	echo $result;
}


/*
function getWorkingScripts($domain, $content_type, $originalHtml)
{
	global $url, $result, $document_domain, $base_url, $base_ssl_url;
	$code = '
		<script type="text/javascript">
			document.domain = "'.$document_domain.'";
            window.btOriginalHtml = '.json_encode($originalHtml).';
		</script>
		<script type="text/javascript" src="'. $base_ssl_url .'js/opt-scripts.js"></script>
	';
	return $code;
}
*/
function getHeadScripts($domain, $content_type, $basehref)
{
	global $url, $result, $document_domain, $config, $base_url, $base_ssl_url;

	$head = "";
	//remove base href
	$result = preg_replace("/<base [^>]+>/i","", $result);

	//add X-UA meta tag in case header doesnt work	
	if(!empty($basehref))
	{
		$basehref = rtrim(str_replace("://","/", $basehref),'/');
		$basehref.="/";	
	}
	$head .= '
		<base href="'.$config['editor_url'].''.$basehref.'btrel/">
		<meta http-equiv="X-UA-Compatible" content="IE=IE9"/>
		<script type="text/javascript">
			//document.domain = "'.$document_domain.'";
			//window.BTEditorUrl = "'.$config['editor_url'].'";
			//window.BTSkipUrl = "'.$document_domain.'";
		</script>
	';
	
	/*
	$head .= '
	<base href="https://opt.blacktri-dev.de/"/>';
	*/
	return $head;
}
function getEndScripts()
{
	global $base_ssl_url;
	$scripts = '
		<script type="text/javascript" src="'. $base_ssl_url .'js/gs.js"></script>
	';	
	return $scripts;
}
function getRedirectUrl($url){
	global $config;
	//die($url);
	$rnd = mt_rand(1,100000000000000);
	$url = str_replace('://','/',$url);
	//if we have query strings or pure html without extension skip "/" adding
	$last = strrpos($url, "/");
	
	$addSlash = true;
	if(stripos($url,"?")!==false)
		$addSlash = false;
	//check for top level domains
	else if(stripos($url,".", $last)!=false)
		$addSlash = false;
		
	// ES 21.10.2014 - adding slashes might generally be a bad idea because
	// some sites have a problem with additional URL. unclear what the reason is.
	$addSlash = false;
	if($addSlash)
		$url = rtrim($url,'/')."/";
		
	$newurl = $config['editor_url'] . "btcache-".$rnd."/".$url;
	
	return $newurl;
}

function getEditorStyles(){
    return '
        <style>
            .bt_rearrange:after {
              content: "";
              display: table;
              clear: both;
            }
            .client_allow_rearrange, .client_allow_rearrange a{
                cursor: move!important;
            }
            .client_allow_rearrange{
            	z-index: 10000!important;
            }
			.client_outlined_element_etracker {
				outline: 3px groove #fb8900 !important;
			}
			.client_outlined_element_blacktri {
				outline: 3px groove #9ccd2b !important;
			}			
			.client_outlined_element_ {
				outline: 3px groove #9ccd2b !important;
			}			
        </style>
    ';
}
function showInvalidHtml(){
	global $document_domain;
	echo '<html><body><h3>Invalid HTML</h3>
	<script type="text/javascript">
		document.domain = "'.$document_domain.'";
		if(typeof top.EditorInvalidHtml == "function")
			top.EditorInvalidHtml();
	</script>
	</body></html>';
}
function getUserAgentByDevice($deviceid){
    global $config;
    foreach($config['device_types'] as $type => $devices)
    {
        foreach($devices as $id => $device)
            if($id == $deviceid)
                return $device['ua'];
    }
    return '';
}
?>