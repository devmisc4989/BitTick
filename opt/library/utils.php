<?
$proxiedHeadersHtml = array(
	/* content related */
	"Content-Type",
	/*"Content-Length",*/
	/*"Content-Encoding",*/
	"Vary",
	/* expiring related */
	"Date",
	"Cache-Control",
	"Expires",
	"Last-Modified",
	"ETag",
	"Age"
);
$proxiedHeadersOtherResources = array(
	/* content related */
	"Content-Type",
	/*"Content-Length",*/
	"Content-Encoding",
	"Vary",
	/* expiring related */
	"Date",
	"Cache-Control",
	"Expires",
	"Last-Modified",
	"ETag",
	"Age"
);

if (!function_exists('getallheaders')) 
{ 
    function getallheaders() 
    { 
           $headers = ''; 
       foreach ($_SERVER as $name => $value) 
       { 
           if (substr($name, 0, 5) == 'HTTP_') 
           { 
               $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value; 
           } 
       } 
       return $headers; 
    } 
}
function rewriteUrl($scheme, $host, $actualurl) {
	// init
	global $config;
	$log = "Original: $actualurl";
	//skip if url is prefixed with ours just in case
	if(stripos($actualurl, $config['editor_url'])!==false)
		return $actualurl;

	$newurl = $actualurl;
	// check if the URL is protocol-relative (starts with //)
	//Fix1
	if(strpos($actualurl, '//') === 0) {
		if(!isset($_SESSION["TARGETURL"]))
			return;
		$TARGETURL = $_SESSION["TARGETURL"];
		$target_scheme = $TARGETURL["scheme"];
		$actualurl = str_replace('//', $target_scheme . '://', $actualurl);
		$log.=", Fix1: $actualurl";
	}
	
	// check if URL is an external URL by trying to extract the base URL of the loading page from it
	{
	//Fix2 - comented for now
	//$actualurl = str_replace('https://'.$host,'',$actualurl);
	//$actualurl = str_replace('http://'.$host,'',$actualurl);	
	//$log.=", Fix2: $actualurl";
	}
	
	// create the rewritten URL depending on wether it could have made relative or not
	if(strpos($actualurl, 'http') === 0) { // URL is absolute and thus an external URL
		$actualurl = str_replace('://','/',$actualurl);
		// prepend our own url
		global $config;
		$editor_url = $config['editor_url'];
		$newurl = "btproxy/" . $actualurl;	
		// remove duplicate slashes
		$newurl = str_replace('//','/',$newurl);
		$newurl = $editor_url . $newurl;
		$log.=", ABSOLUTE rewrite: $newurl";
		
	}
	else { // URL is relative. Make a clean relative URL from it.
		$base_url = rtrim($scheme . '://' . $host, '/') . "/"; // Ensure trailing slash
		$blacktriurl = (isset($_SESSION["BLACKTRIURL"])) ? $_SESSION["BLACKTRIURL"] : "";
		$abs = url_to_absolute( $blacktriurl, $actualurl);
		//echo "rewrite 1b base_url: $base_url absolute: $abs actual: $actualurl_store \n";
		
		if($abs !== false) {
			$actualurl = $abs;
		}
		// try to make the URL relative again
		$actualurl = str_replace($base_url,'',$actualurl);
		//$actualurl = str_replace('http://'.$host,'',$actualurl);
		
		$actualurl = ltrim($actualurl,'/'); // ensure URL to start without /
		//echo "rewrite 4 return: $actualurl \n";
		//$newurl = '/' . $actualurl;
		$editor_url = $config['editor_url'];
		$newurl = $editor_url . $actualurl;		
		$log.=", RELATIVE rewrite: $newurl";
	}
	
	/* Fix3 &curren fix */
	if(preg_match('/\&curren[^;]/', $bt) === 1)
		$newurl = str_replace('&curren', '&amp;curren', $newurl);
	
	OptLogRewrite($log, $host, $_SESSION["CLIENT"]*1);
	return $newurl;
}

function rewriteInlineDocumentUrl($url)
{
	global $config;	
	if(stripos($url, '/btproxy/')!==false) return $url;
	
	$url = str_replace('://','//', $url);
	$url = str_replace('//', '/', $url);
	if($url=="/") {
		if(isset($TARGETURL["scheme"])) {
			$myscheme = $TARGETURL["scheme"];
		}
		else {
			$myscheme = "";
		}
		$url=$myscheme."/";
	}		
	$newurl = $config['editor_url']."btproxy/" . $url;	
	return $newurl;
}

//rewrite absolute url for css requests and style tags
function rewriteScriptInternalUrls($matches)
{
	return rewriteInlineDocumentUrl($matches[0]);
}

function rewriteExternalScriptUrls($content,$scheme,$host)
{
	//json encoded url and normal url dettection! (http(s)?:\\?/\\?/[^"\n]+)
	$content = preg_replace_callback('~(http(s)?://[^"\n]+)~i',"rewriteScriptInternalUrls", $content);
	return $content;
}
function rewriteCssExternalLinks($matches)
{
	return rewriteInlineDocumentUrl($matches[2]);
}
function rewriteCssInternalLinks($matches)
{
	return rewriteInlineDocumentUrl($matches[0]);
}

function rewriteExternalCSSUrls($content,$scheme,$host)
{
	$content = rewriteCSSBackgroundUrls($content,$scheme,$host);
	$content = rewriteCSSImports($content,$scheme,$host);
	// url\([\'\"]?((?:(?:(?:http)s?)?:?//[^\s)\'\"]+))
	//$content = preg_replace_callback('~(?:(?:(?:http)s?)?:?//[^\.\s]+\.)~i',"internal_rewrite_css_url", $content);
	$content = preg_replace_callback('~(url\([\'\"]?)((?:(?:(?:http)s?)?:?//[^\b"\')]+))~i',"rewriteCssExternalLinks", $content);
	return $content;
}

function rewriteCSSBackgroundUrls($content,$scheme,$host) {
	function internal_rewrite_cssbackground($matches)
	{
		$href = $matches['image'];
		
		//check for inline encoded images
		if(stripos($href,'data:image/')==0)
			return $matches[0];
		
		$scheme = $_SESSION['css_scheme'];
		$host = $_SESSION['css_host'];
		$abs = rewriteUrl($scheme, $host, $href);
		$ret = str_replace($matches['image'], $abs, $matches[0]);
		//echo "href:$href ret:$ret<br>\n";
		return $ret;
	}
	$_SESSION['css_scheme'] = $scheme;
	$_SESSION['css_host'] = $host;
	$content = preg_replace_callback('~\bbackground(-image)?\s*:(.*?)\(\s*(\'|")?(?<image>.*?)\3?\s*\)~i','internal_rewrite_cssbackground',$content);
	return $content;
}

function rewriteStyleTagUrls($content){
	function internal_process_bt_style_tag($matches)
	{
		$data = $matches[0];
		$data = preg_replace_callback('~(?:(?:(?:http)s?)?:?//[^\b"\')]+)~i',"rewriteCssInternalLinks", $data);
		return $data;
	}	
	$content = preg_replace_callback('/<style\b[^<]*(?:(?!<\/style>)<[^<]*)*<\/style>/i', 'internal_process_bt_style_tag', $content);
	return $content;
}

function rewriteCSSImports($content,$scheme,$host) {
	function internal_rewrite_css_import($matches)
	{
		global $config;
		$pref = $matches[2];
		//check if the link does not have http or https or ://
		if( stripos($pref, '://')===false ) return "@import url('$pref')";
		//check if the link is proxied
		if(stripos($pref, '/btproxy/')!==false) return "@import url('$pref')";
		
		$pref = str_replace('://','//', $pref);
		$pref = str_replace('//', '/', $pref);
		if($pref=="/") {
			if(isset($TARGETURL["scheme"])) {
				$myscheme = $TARGETURL["scheme"];
			}
			else {
				$myscheme = "";
			}
			$pref=$myscheme."/";
		}
		$newurl = $config['editor_url']."btproxy/" . $pref;	
		return "@import url('$newurl')";
	}
	//$content = preg_replace_callback('/@import\s+url\(([\'"])(.+?)\\1\)/i','internal_rewrite_css_import', $content);
	$content = preg_replace_callback('/@import\s+url\(([\'"]?)([^\'")]+)[\'"]?\)?/i','internal_rewrite_css_import', $content);	
	return $content;
}

// use the DOMDocument HTML parser to rewrite URLs in the document
function rewriteDocumentUrls($content,$scheme,$host) {
	if ( is_null($content) ){
		return FALSE;
	} 
	else {
		//$result['content'] = $content; $result['basehref']
		$result = rewriteDocumentUrlsWithDom($content,$scheme,$host);
	}
	$result['content'] = rewriteCSSImports($result['content'],$scheme,$host);
	$result['content'] = rewriteCSSBackgroundUrls($result['content'],$scheme,$host);	
	$result['content'] = rewriteStyleTagUrls($result['content']);
	
	return $result;
}

// use the DOMDocument HTML parser to rewrite URLs in the document
// approach: we use the parser to find the urls, but perform serahc/replace
// in the original document, because exporting the document from the
// parser again leads to errors because the document exported
// is different from the original document (e.g. in some cases script
// tags get mangled etc...
function rewriteDocumentUrlsWithDom($content,$scheme,$host) {
	$basehref = "";
	$timetrack = true;
	if($timetrack) $basetime = microtime(true);
		
		$originalContent = $content;
		
	    // Instantiate the object
		$doc = new DOMDocument();
		// Build the DOM from the input (X)HTML snippet and suppress warnings
		$previous_value = libxml_use_internal_errors(TRUE);
		$doc->loadHTML($content);
		libxml_clear_errors();
		libxml_use_internal_errors($previous_value);

		// TODO change this to regex!!!!!!
		$elements = $doc->getElementsByTagName('body');
		foreach($elements as $element){
			$newdoc = new DOMDocument();
			$cloned = $element->cloneNode(TRUE);
			$newdoc->appendChild($newdoc->importNode($cloned,TRUE));
    		break;	
		}
		
		// find the <base href> tag
		$elements = $doc->getElementsByTagName('base');
		foreach($elements as $element){
			$href = $element->getAttribute('href');
			if(isset($href) && $href != "") {
				$basehref = $href;
			}
		}
		

		/*
		// Find the script tags
		$elements = $doc->getElementsByTagName('script');
		foreach($elements as $element){
			$href = $element->getAttribute('src');
			if(isset($href) && $href != "" && stripos($href,'//') !== false) {
				$hrefNew= rewriteUrl($scheme, $host, $href);
				$content = str_ireplace("\"$href\"", "\"$hrefNew\"", $content);
				$content = str_ireplace("'$href'", "'$hrefNew'", $content);
				//$element->setAttribute('src', $href);
			}
		}
	    */
        
		// Find the a tags
		$elements = $doc->getElementsByTagName('a');
		foreach($elements as $element){
			$href = $element->getAttribute('href');
			if(isset($href) && $href != "" && stripos($href,'//') !== false) {
				$hrefNew= rewriteUrl($scheme, $host, $href);
				$content = str_ireplace("\"$href\"", "\"$hrefNew\"", $content);
				$content = str_ireplace("'$href'", "'$hrefNew'", $content);
				//$element->setAttribute('href', $href);
			}
		}
	      
		// Find the link tags
		$elements = $doc->getElementsByTagName('link');
		foreach($elements as $element){
			$href = $element->getAttribute('href');
			if(isset($href) && $href != "" && stripos($href,'//') !== false) {
				$hrefNew= rewriteUrl($scheme, $host, $href);
				//original href not found, maybe it's unescaped from getAttribute.
				if(stripos($content, $href)===false)
				{	
					// if the extracted href can not be found in the original source,
					// the reason could be that DOM Document encodes all & to entities (&amp;)
					// in this case try to handle this by replacing only the substring until the first &
					$arr = explode("&",$href);
					$href = $arr[0];
					$arr = explode("&",$hrefNew);
					$hrefNew = $arr[0];
					$content = str_ireplace($href,$hrefNew, $content);
				}
				else {
					$content = str_ireplace("\"$href\"", "\"$hrefNew\"", $content);
					$content = str_ireplace("'$href'", "'$hrefNew'", $content);
				}
			}
		}
	      
		// Find the img tags
		$elements = $doc->getElementsByTagName('img');
		foreach($elements as $element){
			// Get the value of the href attribute
			$href = $element->getAttribute('src');
			if(isset($href) && $href != "" && stripos($href,'//') !== false) {
				// so not touch data-URLs
				if((strpos($href, 'data') !== 0) &&	((strpos($href, 'googleusercontent') === false))) {
					// rewrite the URL
					$hrefNew= rewriteUrl($scheme, $host, $href);
					$content = str_ireplace("\"$href\"", "\"$hrefNew\"", $content);
					$content = str_ireplace("'$href'", "'$hrefNew'", $content);
					//$element->setAttribute('src', $href);
				}
			}
		}            
	
		// Find the input tags
		$elements = $doc->getElementsByTagName('input');
		foreach($elements as $element){
			$href = $element->getAttribute('href');
			if(isset($href) && $href != "" && stripos($href,'//') !== false) {
				$hrefNew= rewriteUrl($scheme, $host, $href);
				$content = str_ireplace("\"$href\"", "\"$hrefNew\"", $content);
				$content = str_ireplace("'$href'", "'$hrefNew'", $content);
				//$element->setAttribute('href', $href);
			}
		}
	      
		// Find the iframe tags
		$elements = $doc->getElementsByTagName('iframe');
		foreach($elements as $element){
			$href = $element->getAttribute('src');
			if(isset($href) && $href != "" && stripos($href,'//') !== false) {
				$hrefNew= rewriteUrl($scheme, $host, $href);
				$content = str_ireplace("\"$href\"", "\"$hrefNew\"", $content);
				$content = str_ireplace("'$href'", "'$hrefNew'", $content);
				//$element->setAttribute('src', $href);
			}
		}

		// Find the meta tags
		$elements = $doc->getElementsByTagName('meta');
		$metatags = array();
		foreach($elements as $element){
			$meta_name = strtolower($element->getAttribute('name'));
			$meta_httpequiv = strtolower($element->getAttribute('http-equiv'));
			$meta_content = strtolower($element->getAttribute('content'));
			if(!empty($meta_httpequiv))
				$metatags[$meta_httpequiv] = $meta_content;
			else
				$metatags[$meta_name] = $meta_content;				
		}
	      
		//$content = $doc->saveHTML();
		
		$result = array();
		$result['content'] = $content;
		$result['meta'] = $metatags;
		$result['basehref'] = $basehref;
		return $result;
}

// load a list of site specifc search-replace strings from the DB and execute
function replaceSitespecificStrings($content) {
	$replaceStrings = $_SESSION["REPLACESTRINGS"];
	global $config;
	$editor_url = $config['editor_url'];
	
	foreach($replaceStrings as $rs) {
		$search = $rs['search'];
		$replace = $rs['replace'];
		$replace = str_replace('http://','http/',$replace);
		$replace = str_replace('https://','https/',$replace);
		$content = str_replace($search, $replace, $content);
		$content = str_replace("#EDURL#", $editor_url . "btproxy/", $content);
	}
	return $content;
}

function getUrlReplaceStrings($url) {
	global $db;
	$rules = array();
	//get database rules
	$mysql = mysql_connect($db['default']['hostname'], $db['default']['username'], $db['default']['password']);
	if(is_resource($mysql))
	{
		mysql_select_db($db['default']['database'], $mysql);
		$sql = "SELECT * FROM url_replace_strings WHERE url = '*' OR '".mysql_escape($url)."' LIKE CONCAT('%', url, '%')";
		$result = mysql_query($sql, $mysql);
		if($result)
		{
			while ($row = mysql_fetch_assoc($result))
			{
				$rules[] = $row;
			}
			mysql_free_result($result);
		}
		mysql_close($mysql);	
	}
	return $rules;	
}

function getUrlRules($url) {
	global $db;
	$rules = array();
	//get database rules
	$mysql = mysql_connect($db['default']['hostname'], $db['default']['username'], $db['default']['password']);
	if(is_resource($mysql))
	{
		mysql_select_db($db['default']['database'], $mysql);
		$sql = "SELECT * FROM url_filter WHERE url = '*' OR '".mysql_escape($url)."' LIKE CONCAT('%', url, '%')";
		$result = mysql_query($sql, $mysql);
		if($result)
		{
			while ($row = mysql_fetch_assoc($result))
			{
				$rules[] = $row["pattern"];
			}
			mysql_free_result($result);
		}
		mysql_close($mysql);	
	}
	return $rules;
}
function process_script_tag($matches)
{
	global $gaphmsg;
	$data = $matches[0];
	//check for google adsense
	if( stripos($data, 'google_ad_client') !== false )
	{
		$width = 468; $height = 60;//default values
		//get width
		preg_match("/google_ad_width\s*=\s*(\d+);/i", $data, $m);
		if($m[1]*1 > 0)
			$width = $m[1]*1;
		//get height
		preg_match("/google_ad_height\s*=\s*(\d+);/i", $data, $m);
		if($m[1]*1 > 0)
			$height = $m[1]*1;
		//get google ad slot
		preg_match("/(google_ad_slot\s*=\s*\"\d+\");/i", $data, $m);
		//return '<ins class="google_ad_ph" style="width:'.$width.'px; height:'.$height.'px;line-height:'.$height.'px;">'.$m[1].'</ins>';
		return '<ins class="google_ad_ph" style="width:'.$width.'px; height:'.$height.'px;">'.$gaphmsg.$matches[0].$m[1].'</ins>';
		//return $data;
	}
	//return empty (script tags gets replaced)
	return "";
}

function remove_scripts($data)
{
	//commented for now
	function array_stripos($haystack, $needle)
	{
		if (is_array($needle))
		{
			foreach ($needle as $need)
			{
				if (stripos($haystack, $need) !== false)
					return true;
			}
		}
		else
		{
			if (stripos($haystack, $needneedle) !== false)
				return true;
		}	
		return false;
	}
	function internal_process_bt_script_tag($matches)
	{
		global $config;
		$data = $matches[0];
		$rules = $_SESSION["RULES"];
		if( array_stripos($data, $rules ) )
		{
			return "";
		}
		
		//internal script full url rewrite
		if(stripos($data,"http://")!==false)
		{
			$data = str_replace("http://", $config["editor_url"]."btproxy/http/",$data);
		}
		else if (stripos($data,"https://")!==false)
		{
			$data = str_replace("https://", $config["editor_url"]."btproxy/https/",$data);
		}
		else if(stripos($data,"\"//")!==false)
		{
			$TARGETURL = $_SESSION["TARGETURL"];
			$target_scheme = $TARGETURL["scheme"];
			$data = str_replace("\"//", "\"".$config["editor_url"]."btproxy/".$target_scheme."/",$data);
		}
		else if(stripos($data,"'//")!==false)
		{
			$TARGETURL = $_SESSION["TARGETURL"];
			$target_scheme = $TARGETURL["scheme"];
			$data = str_replace("'//", "'".$config["editor_url"]."btproxy/".$target_scheme."/",$data);
		}
		return $data;
	}
    
	//$data = preg_replace_callback('/<script\b[^>]*>(.*?)<\/script>/i', 'internal_process_bt_script_tag', $data);
	//$data = preg_replace_callback('/<script\b[^<]*<\/script[^>]*>/i', 'internal_process_bt_script_tag', $data);
    
	$data = preg_replace_callback('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/i', 'internal_process_bt_script_tag', $data);
	return $data;
}

function OptLog($text, $domain='all', $client = 0)
{
	global $config;
	if(isset($config['opt_log_base_path'])) {
		$dir = $config['opt_log_base_path'] . '/data/'.$client.'/logs/';
	}
	else {
		$dir = dirname(__FILE__).'/../data/'.$client.'/logs/';
	}
	if(!file_exists($dir))
		mkdir($dir, 0777, true);
	//$dir = "/Users/eschneid/tmp/logs/";	
	$file = $dir.$domain.'.txt';
	$f = fopen($file, 'a');
	if($f)
	{
		fwrite($f, date("Y-m-d H:i:s")."\t".$text."\n\n");
		fclose($f);
	}
}
function OptLogRewrite($text, $domain='all', $client = 0)
{
	global $config;
	if(isset($config['opt_log_base_path'])) {
		$dir = $config['opt_log_base_path'] . '/data/'.$client.'/logs/';
	}
	else {
		$dir = dirname(__FILE__).'/../data/'.$client.'/logs/';
	}
	if(!file_exists($dir))
		mkdir($dir, 0777, true);
		
	$file = $dir.$domain.'.rewrite.txt';
	$f = fopen($file, 'a');
	if($f)
	{
		fwrite($f, date("Y-m-d H:i:s")."\t".$text."\n\n");
		fclose($f);
	}
}
function mysql_escape($str)
{
	 $search=array("\\","\0","\n","\r","\x1a","'",'"');
	 $replace=array("\\\\","\\0","\\n","\\r","\Z","\'",'\"');
	 return str_replace($search,$replace,$str);
}



/*
Copyright (c) 2008 Sebasti�n Grignoli
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions
are met:
1. Redistributions of source code must retain the above copyright
   notice, this list of conditions and the following disclaimer.
2. Redistributions in binary form must reproduce the above copyright
   notice, this list of conditions and the following disclaimer in the
   documentation and/or other materials provided with the distribution.
3. Neither the name of copyright holders nor the names of its
   contributors may be used to endorse or promote products derived
   from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED
TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
PURPOSE ARE DISCLAIMED.  IN NO EVENT SHALL COPYRIGHT HOLDERS OR CONTRIBUTORS
BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
POSSIBILITY OF SUCH DAMAGE.
*/

/**
 * @author   "Sebasti�n Grignoli" <grignoli@framework2.com.ar>
 * @package  Encoding
 * @version  1.2
 * @link     https://github.com/neitanod/forceutf8
 * @example  https://github.com/neitanod/forceutf8
 * @license  Revised BSD
  */

class Encoding {
    
  protected static $win1252ToUtf8 = array(
        128 => "\xe2\x82\xac",

        130 => "\xe2\x80\x9a",
        131 => "\xc6\x92",
        132 => "\xe2\x80\x9e",
        133 => "\xe2\x80\xa6",
        134 => "\xe2\x80\xa0",
        135 => "\xe2\x80\xa1",
        136 => "\xcb\x86",
        137 => "\xe2\x80\xb0",
        138 => "\xc5\xa0",
        139 => "\xe2\x80\xb9",
        140 => "\xc5\x92",

        142 => "\xc5\xbd",


        145 => "\xe2\x80\x98",
        146 => "\xe2\x80\x99",
        147 => "\xe2\x80\x9c",
        148 => "\xe2\x80\x9d",
        149 => "\xe2\x80\xa2",
        150 => "\xe2\x80\x93",
        151 => "\xe2\x80\x94",
        152 => "\xcb\x9c",
        153 => "\xe2\x84\xa2",
        154 => "\xc5\xa1",
        155 => "\xe2\x80\xba",
        156 => "\xc5\x93",

        158 => "\xc5\xbe",
        159 => "\xc5\xb8"
  );
  
    protected static $brokenUtf8ToUtf8 = array(
        "\xc2\x80" => "\xe2\x82\xac",
        
        "\xc2\x82" => "\xe2\x80\x9a",
        "\xc2\x83" => "\xc6\x92",
        "\xc2\x84" => "\xe2\x80\x9e",
        "\xc2\x85" => "\xe2\x80\xa6",
        "\xc2\x86" => "\xe2\x80\xa0",
        "\xc2\x87" => "\xe2\x80\xa1",
        "\xc2\x88" => "\xcb\x86",
        "\xc2\x89" => "\xe2\x80\xb0",
        "\xc2\x8a" => "\xc5\xa0",
        "\xc2\x8b" => "\xe2\x80\xb9",
        "\xc2\x8c" => "\xc5\x92",
        
        "\xc2\x8e" => "\xc5\xbd",
        
        
        "\xc2\x91" => "\xe2\x80\x98",
        "\xc2\x92" => "\xe2\x80\x99",
        "\xc2\x93" => "\xe2\x80\x9c",
        "\xc2\x94" => "\xe2\x80\x9d",
        "\xc2\x95" => "\xe2\x80\xa2",
        "\xc2\x96" => "\xe2\x80\x93",
        "\xc2\x97" => "\xe2\x80\x94",
        "\xc2\x98" => "\xcb\x9c",
        "\xc2\x99" => "\xe2\x84\xa2",
        "\xc2\x9a" => "\xc5\xa1",
        "\xc2\x9b" => "\xe2\x80\xba",
        "\xc2\x9c" => "\xc5\x93",
        
        "\xc2\x9e" => "\xc5\xbe",
        "\xc2\x9f" => "\xc5\xb8"
  );
    
  protected static $utf8ToWin1252 = array(
       "\xe2\x82\xac" => "\x80",
       
       "\xe2\x80\x9a" => "\x82",
       "\xc6\x92"     => "\x83",
       "\xe2\x80\x9e" => "\x84",
       "\xe2\x80\xa6" => "\x85",
       "\xe2\x80\xa0" => "\x86",
       "\xe2\x80\xa1" => "\x87",
       "\xcb\x86"     => "\x88",
       "\xe2\x80\xb0" => "\x89",
       "\xc5\xa0"     => "\x8a",
       "\xe2\x80\xb9" => "\x8b",
       "\xc5\x92"     => "\x8c",
       
       "\xc5\xbd"     => "\x8e",
       
       
       "\xe2\x80\x98" => "\x91",
       "\xe2\x80\x99" => "\x92",
       "\xe2\x80\x9c" => "\x93",
       "\xe2\x80\x9d" => "\x94",
       "\xe2\x80\xa2" => "\x95",
       "\xe2\x80\x93" => "\x96",
       "\xe2\x80\x94" => "\x97",
       "\xcb\x9c"     => "\x98",
       "\xe2\x84\xa2" => "\x99",
       "\xc5\xa1"     => "\x9a",
       "\xe2\x80\xba" => "\x9b",
       "\xc5\x93"     => "\x9c",
       
       "\xc5\xbe"     => "\x9e",
       "\xc5\xb8"     => "\x9f"
    );

  static function toUTF8($text){
  /**
   * Function Encoding::toUTF8
   *
   * This function leaves UTF8 characters alone, while converting almost all non-UTF8 to UTF8.
   * 
   * It assumes that the encoding of the original string is either Windows-1252 or ISO 8859-1.
   *
   * It may fail to convert characters to UTF-8 if they fall into one of these scenarios:
   *
   * 1) when any of these characters:   ��������������������������������
   *    are followed by any of these:  ("group B")
   *                                    �������������������������������
   * For example:   %ABREPRESENT%C9%BB. �REPRESENTɻ
   * The "�" (%AB) character will be converted, but the "�" followed by "�" (%C9%BB) 
   * is also a valid unicode character, and will be left unchanged.
   *
   * 2) when any of these: ����������������  are followed by TWO chars from group B,
   * 3) when any of these: ����  are followed by THREE chars from group B.
   *
   * @name toUTF8
   * @param string $text  Any string.
   * @return string  The same string, UTF8 encoded
   *
   */

    if(is_array($text))
    {
      foreach($text as $k => $v)
      {
        $text[$k] = self::toUTF8($v);
      }
      return $text;
    } elseif(is_string($text)) {
    
      $max = strlen($text);
      $buf = "";
      for($i = 0; $i < $max; $i++){
          $c1 = $text{$i};
          if($c1>="\xc0"){ //Should be converted to UTF8, if it's not UTF8 already
            $c2 = $i+1 >= $max? "\x00" : $text{$i+1};
            $c3 = $i+2 >= $max? "\x00" : $text{$i+2};
            $c4 = $i+3 >= $max? "\x00" : $text{$i+3};
              if($c1 >= "\xc0" & $c1 <= "\xdf"){ //looks like 2 bytes UTF8
                  if($c2 >= "\x80" && $c2 <= "\xbf"){ //yeah, almost sure it's UTF8 already
                      $buf .= $c1 . $c2;
                      $i++;
                  } else { //not valid UTF8.  Convert it.
                      $cc1 = (chr(ord($c1) / 64) | "\xc0");
                      $cc2 = ($c1 & "\x3f") | "\x80";
                      $buf .= $cc1 . $cc2;
                  }
              } elseif($c1 >= "\xe0" & $c1 <= "\xef"){ //looks like 3 bytes UTF8
                  if($c2 >= "\x80" && $c2 <= "\xbf" && $c3 >= "\x80" && $c3 <= "\xbf"){ //yeah, almost sure it's UTF8 already
                      $buf .= $c1 . $c2 . $c3;
                      $i = $i + 2;
                  } else { //not valid UTF8.  Convert it.
                      $cc1 = (chr(ord($c1) / 64) | "\xc0");
                      $cc2 = ($c1 & "\x3f") | "\x80";
                      $buf .= $cc1 . $cc2;
                  }
              } elseif($c1 >= "\xf0" & $c1 <= "\xf7"){ //looks like 4 bytes UTF8
                  if($c2 >= "\x80" && $c2 <= "\xbf" && $c3 >= "\x80" && $c3 <= "\xbf" && $c4 >= "\x80" && $c4 <= "\xbf"){ //yeah, almost sure it's UTF8 already
                      $buf .= $c1 . $c2 . $c3;
                      $i = $i + 2;
                  } else { //not valid UTF8.  Convert it.
                      $cc1 = (chr(ord($c1) / 64) | "\xc0");
                      $cc2 = ($c1 & "\x3f") | "\x80";
                      $buf .= $cc1 . $cc2;
                  }
              } else { //doesn't look like UTF8, but should be converted
                      $cc1 = (chr(ord($c1) / 64) | "\xc0");
                      $cc2 = (($c1 & "\x3f") | "\x80");
                      $buf .= $cc1 . $cc2;
              }
          } elseif(($c1 & "\xc0") == "\x80"){ // needs conversion
                if(isset(self::$win1252ToUtf8[ord($c1)])) { //found in Windows-1252 special cases
                    $buf .= self::$win1252ToUtf8[ord($c1)];
                } else {
                  $cc1 = (chr(ord($c1) / 64) | "\xc0");
                  $cc2 = (($c1 & "\x3f") | "\x80");
                  $buf .= $cc1 . $cc2;
                }
          } else { // it doesn't need convesion
              $buf .= $c1;
          }
      }
      return $buf;
    } else {
      return $text;
    }
  }

  static function toWin1252($text) {
    if(is_array($text)) {
      foreach($text as $k => $v) {
        $text[$k] = self::toWin1252($v);
      }
      return $text;
    } elseif(is_string($text)) {
      return utf8_decode(str_replace(array_keys(self::$utf8ToWin1252), array_values(self::$utf8ToWin1252), self::toUTF8($text)));
    } else {
      return $text;
    }
  }

  static function toISO8859($text) {
    return self::toWin1252($text);
  }

  static function toLatin1($text) {
    return self::toWin1252($text);
  }

  static function fixUTF8($text){
    if(is_array($text)) {
      foreach($text as $k => $v) {
        $text[$k] = self::fixUTF8($v);
      }
      return $text;
    }

    $last = "";
    while($last <> $text){
      $last = $text;
      $text = self::toUTF8(utf8_decode(str_replace(array_keys(self::$utf8ToWin1252), array_values(self::$utf8ToWin1252), $text)));
    }
    $text = self::toUTF8(utf8_decode(str_replace(array_keys(self::$utf8ToWin1252), array_values(self::$utf8ToWin1252), $text)));
    return $text;
  }
  
  static function UTF8FixWin1252Chars($text){
    // If you received an UTF-8 string that was converted from Windows-1252 as it was ISO8859-1 
    // (ignoring Windows-1252 chars from 80 to 9F) use this function to fix it.
    // See: http://en.wikipedia.org/wiki/Windows-1252
    
    return str_replace(array_keys(self::$brokenUtf8ToUtf8), array_values(self::$brokenUtf8ToUtf8), $text);
  }
  
  static function removeBOM($str=""){
    if(substr($str, 0,3) == pack("CCC",0xef,0xbb,0xbf)) {
      $str=substr($str, 3);
    }
    return $str;
  }
  
  public static function normalizeEncoding($encodingLabel)
  {
    $encoding = strtoupper($encodingLabel);
    $enc = preg_replace('/[^a-zA-Z0-9\s]/', '', $encoding);
    $equivalences = array(
        'ISO88591' => 'ISO-8859-1',
        'ISO8859'  => 'ISO-8859-1',
        'ISO'      => 'ISO-8859-1',
        'LATIN1'   => 'ISO-8859-1',
        'LATIN'    => 'ISO-8859-1',
        'UTF8'     => 'UTF-8',
        'UTF'      => 'UTF-8',
        'WIN1252'  => 'ISO-8859-1',
        'WINDOWS1252' => 'ISO-8859-1'
    );
    
    if(empty($equivalences[$encoding])){
      return 'UTF-8';
    }
   
    return $equivalences[$encoding];
  }

  public static function encode($encodingLabel, $text)
  {
    $encodingLabel = self::normalizeEncoding($encodingLabel);
    if($encodingLabel == 'UTF-8') return Encoding::toUTF8($text);
    if($encodingLabel == 'ISO-8859-1') return Encoding::toLatin1($text);
  }

}

/**
* This class overcomes a few common annoyances with the DOMDocument class,
* such as saving partial HTML without automatically adding extra tags
* and properly recognizing various encodings, specifically UTF-8.
*
* @author Artem Russakovskii
* @version 0.4
* @link http://beerpla.net
* @link http://www.php.net/manual/en/class.domdocument.php
*/
if(!class_exists("SmartDOMDocument")) {
  class SmartDOMDocument extends DOMDocument {

	  /**
	  * Adds an ability to use the SmartDOMDocument object as a string in a string context.
	  * For example, echo "Here is the HTML: $dom";
	  */
	  public function __toString() {
		  return $this->saveHTMLExact();
	  }

	  /**
	  * Load HTML with a proper encoding fix/hack.
	  * Borrowed from the link below.
	  *
	  * @link http://www.php.net/manual/en/domdocument.loadhtml.php
	  *
	  * @param string $html
	  * @param string $encoding
	  */
	  public function loadHTML($html, $encoding = "UTF-8") {
		  $html = mb_convert_encoding($html, 'HTML-ENTITIES', $encoding);
		  @parent::loadHTML($html); // suppress warnings
	  }

	  /**
	  * Return HTML while stripping the annoying auto-added <html>, <body>, and doctype.
	  *
	  * @link http://php.net/manual/en/migration52.methods.php
	  *
	  * @return string
	  */
	  public function saveHTMLExact() {
      $content = preg_replace(array("/^\<\!DOCTYPE.*?<html><body>/si",
                                    "!</body></html>$!si"),
                              "",
                              $this->saveHTML());

		  return $content;
	  }

    /**
    * This test functions shows an example of SmartDOMDocument in action.
    * A sample HTML fragment is loaded.
    * Then, the first image in the document is cut out and saved separately.
    * It also shows that Russian characters are parsed correctly.
    *
    */
    public static function testHTML() {
      $content = <<<CONTENT
<div class='class1'>
  <img src='http://www.google.com/favicon.ico' />
  Some Text
  <p>Ñ€ÑƒÑÑÐºÐ¸Ð¹</p>
</div>
CONTENT;

      print "Before removing the image, the content is: " . htmlspecialchars($content) . "<br>";

      $content_doc = new SmartDOMDocument();
      $content_doc->loadHTML($content);

      try {
        $first_image = $content_doc->getElementsByTagName("img")->item(0);

        if ($first_image) {
          $first_image->parentNode->removeChild($first_image);

          $content = $content_doc->saveHTMLExact();

          $image_doc = new SmartDOMDocument();
          $image_doc->appendChild($image_doc->importNode($first_image, true));
          $image = $image_doc->saveHTMLExact();
        }
      } catch(Exception $e) { }

      print "After removing the image, the content is: " . htmlspecialchars($content) . "<br>";
      print "The image is: " . htmlspecialchars($image);
    }

  }
}

/**
 * Edited by Nitin Kr. Gupta, publicmind.in
 */

/**
 * Copyright (c) 2008, David R. Nadeau, NadeauSoftware.com.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *	* Redistributions of source code must retain the above copyright
 *	  notice, this list of conditions and the following disclaimer.
 *
 *	* Redistributions in binary form must reproduce the above
 *	  copyright notice, this list of conditions and the following
 *	  disclaimer in the documentation and/or other materials provided
 *	  with the distribution.
 *
 *	* Neither the names of David R. Nadeau or NadeauSoftware.com, nor
 *	  the names of its contributors may be used to endorse or promote
 *	  products derived from this software without specific prior
 *	  written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY
 * WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY
 * OF SUCH DAMAGE.
 */

/*
 * This is a BSD License approved by the Open Source Initiative (OSI).
 * See:  http://www.opensource.org/licenses/bsd-license.php
 */

/**
 * Combine a base URL and a relative URL to produce a new
 * absolute URL.  The base URL is often the URL of a page,
 * and the relative URL is a URL embedded on that page.
 *
 * This function implements the "absolutize" algorithm from
 * the RFC3986 specification for URLs.
 *
 * This function supports multi-byte characters with the UTF-8 encoding,
 * per the URL specification.
 *
 * Parameters:
 * 	baseUrl		the absolute base URL.
 *
 * 	url		the relative URL to convert.
 *
 * Return values:
 * 	An absolute URL that combines parts of the base and relative
 * 	URLs, or FALSE if the base URL is not absolute or if either
 * 	URL cannot be parsed.
 */
function url_to_absolute( $baseUrl, $relativeUrl )
{
	// If relative URL has a scheme, clean path and return.
	$r = split_url( $relativeUrl );
	if ( $r === FALSE )
		return FALSE;
	if ( !empty( $r['scheme'] ) )
	{
		if ( !empty( $r['path'] ) && $r['path'][0] == '/' )
			$r['path'] = url_remove_dot_segments( $r['path'] );
		return join_url( $r );
	}

	// Make sure the base URL is absolute.
	$b = split_url( $baseUrl );
	if (empty( $b['path'] ) )
		$b['path'] = '';
	if ( $b === FALSE || empty( $b['scheme'] ) || empty( $b['host'] ) )
		return FALSE;
	$r['scheme'] = $b['scheme'];

	// If relative URL has an authority, clean path and return.
	if ( isset( $r['host'] ) )
	{
		if ( !empty( $r['path'] ) )
			$r['path'] = url_remove_dot_segments( $r['path'] );
		return join_url( $r );
	}
	unset( $r['port'] );
	unset( $r['user'] );
	unset( $r['pass'] );

	// Copy base authority.
	$r['host'] = $b['host'];
	if ( isset( $b['port'] ) ) $r['port'] = $b['port'];
	if ( isset( $b['user'] ) ) $r['user'] = $b['user'];
	if ( isset( $b['pass'] ) ) $r['pass'] = $b['pass'];

	// If relative URL has no path, use base path
	if ( empty( $r['path'] ) )
	{
		if ( !empty( $b['path'] ) )
			$r['path'] = $b['path'];
		if ( !isset( $r['query'] ) && isset( $b['query'] ) )
			$r['query'] = $b['query'];
		return join_url( $r );
	}

	// If relative URL path doesn't start with /, merge with base path
	if ( $r['path'][0] != '/' )
	{
		$base = mb_strrchr( $b['path'], '/', TRUE, 'UTF-8' );
		if ( $base === FALSE ) $base = '';
		$r['path'] = $base . '/' . $r['path'];
	}
	$r['path'] = url_remove_dot_segments( $r['path'] );
	return join_url( $r );
}

/**
 * Filter out "." and ".." segments from a URL's path and return
 * the result.
 *
 * This function implements the "remove_dot_segments" algorithm from
 * the RFC3986 specification for URLs.
 *
 * This function supports multi-byte characters with the UTF-8 encoding,
 * per the URL specification.
 *
 * Parameters:
 * 	path	the path to filter
 *
 * Return values:
 * 	The filtered path with "." and ".." removed.
 */
function url_remove_dot_segments( $path )
{
	// multi-byte character explode
	$inSegs  = preg_split( '!/!u', $path );
	$outSegs = array( );
	foreach ( $inSegs as $seg )
	{
		if ( $seg == '' || $seg == '.')
			continue;
		if ( $seg == '..' )
			array_pop( $outSegs );
		else
			array_push( $outSegs, $seg );
	}
	$outPath = implode( '/', $outSegs );
	if ( $path[0] == '/' )
		$outPath = '/' . $outPath;
	// compare last multi-byte character against '/'
	if ( $outPath != '/' &&
		(mb_strlen($path)-1) == mb_strrpos( $path, '/', 'UTF-8' ) )
		$outPath .= '/';
	return $outPath;
}


/**
 * This function parses an absolute or relative URL and splits it
 * into individual components.
 *
 * RFC3986 specifies the components of a Uniform Resource Identifier (URI).
 * A portion of the ABNFs are repeated here:
 *
 *	URI-reference	= URI
 *			/ relative-ref
 *
 *	URI		= scheme ":" hier-part [ "?" query ] [ "#" fragment ]
 *
 *	relative-ref	= relative-part [ "?" query ] [ "#" fragment ]
 *
 *	hier-part	= "//" authority path-abempty
 *			/ path-absolute
 *			/ path-rootless
 *			/ path-empty
 *
 *	relative-part	= "//" authority path-abempty
 *			/ path-absolute
 *			/ path-noscheme
 *			/ path-empty
 *
 *	authority	= [ userinfo "@" ] host [ ":" port ]
 *
 * So, a URL has the following major components:
 *
 *	scheme
 *		The name of a method used to interpret the rest of
 *		the URL.  Examples:  "http", "https", "mailto", "file'.
 *
 *	authority
 *		The name of the authority governing the URL's name
 *		space.  Examples:  "example.com", "user@example.com",
 *		"example.com:80", "user:password@example.com:80".
 *
 *		The authority may include a host name, port number,
 *		user name, and password.
 *
 *		The host may be a name, an IPv4 numeric address, or
 *		an IPv6 numeric address.
 *
 *	path
 *		The hierarchical path to the URL's resource.
 *		Examples:  "/index.htm", "/scripts/page.php".
 *
 *	query
 *		The data for a query.  Examples:  "?search=google.com".
 *
 *	fragment
 *		The name of a secondary resource relative to that named
 *		by the path.  Examples:  "#section1", "#header".
 *
 * An "absolute" URL must include a scheme and path.  The authority, query,
 * and fragment components are optional.
 *
 * A "relative" URL does not include a scheme and must include a path.  The
 * authority, query, and fragment components are optional.
 *
 * This function splits the $url argument into the following components
 * and returns them in an associative array.  Keys to that array include:
 *
 *	"scheme"	The scheme, such as "http".
 *	"host"		The host name, IPv4, or IPv6 address.
 *	"port"		The port number.
 *	"user"		The user name.
 *	"pass"		The user password.
 *	"path"		The path, such as a file path for "http".
 *	"query"		The query.
 *	"fragment"	The fragment.
 *
 * One or more of these may not be present, depending upon the URL.
 *
 * Optionally, the "user", "pass", "host" (if a name, not an IP address),
 * "path", "query", and "fragment" may have percent-encoded characters
 * decoded.  The "scheme" and "port" cannot include percent-encoded
 * characters and are never decoded.  Decoding occurs after the URL has
 * been parsed.
 *
 * Parameters:
 * 	url		the URL to parse.
 *
 * 	decode		an optional boolean flag selecting whether
 * 			to decode percent encoding or not.  Default = TRUE.
 *
 * Return values:
 * 	the associative array of URL parts, or FALSE if the URL is
 * 	too malformed to recognize any parts.
 */
function split_url( $url, $decode=FALSE)
{
	// Character sets from RFC3986.
	$xunressub     = 'a-zA-Z\d\-._~\!$&\'()*+,;=';
	$xpchar        = $xunressub . ':@% ';

	// Scheme from RFC3986.
	$xscheme        = '([a-zA-Z][a-zA-Z\d+-.]*)';

	// User info (user + password) from RFC3986.
	$xuserinfo     = '((['  . $xunressub . '%]*)' .
	                 '(:([' . $xunressub . ':%]*))?)';

	// IPv4 from RFC3986 (without digit constraints).
	$xipv4         = '(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})';

	// IPv6 from RFC2732 (without digit and grouping constraints).
	$xipv6         = '(\[([a-fA-F\d.:]+)\])';

	// Host name from RFC1035.  Technically, must start with a letter.
	// Relax that restriction to better parse URL structure, then
	// leave host name validation to application.
	$xhost_name    = '([a-zA-Z\d-.%]+)';

	// Authority from RFC3986.  Skip IP future.
	$xhost         = '(' . $xhost_name . '|' . $xipv4 . '|' . $xipv6 . ')';
	$xport         = '(\d*)';
	$xauthority    = '((' . $xuserinfo . '@)?' . $xhost .
		         '?(:' . $xport . ')?)';

	// Path from RFC3986.  Blend absolute & relative for efficiency.
	$xslash_seg    = '(/[' . $xpchar . ']*)';
	$xpath_authabs = '((//' . $xauthority . ')((/[' . $xpchar . ']*)*))';
	$xpath_rel     = '([' . $xpchar . ']+' . $xslash_seg . '*)';
	$xpath_abs     = '(/(' . $xpath_rel . ')?)';
	$xapath        = '(' . $xpath_authabs . '|' . $xpath_abs .
			 '|' . $xpath_rel . ')';

	// Query and fragment from RFC3986.
	$xqueryfrag    = '([' . $xpchar . '/?' . ']*)';

	// URL.
	$xurl          = '^(' . $xscheme . ':)?' .  $xapath . '?' .
	                 '(\?' . $xqueryfrag . ')?(#' . $xqueryfrag . ')?$';


	// Split the URL into components.
	$parts = array();
	if ( !preg_match( '!' . $xurl . '!', $url, $m ) )
		return FALSE;

	if ( !empty($m[2]) )		$parts['scheme']  = strtolower($m[2]);

	if ( !empty($m[7]) ) {
		if ( isset( $m[9] ) )	$parts['user']    = $m[9];
		else			$parts['user']    = '';
	}
	if ( !empty($m[10]) )		$parts['pass']    = $m[11];

	if ( !empty($m[13]) )		$h=$parts['host'] = $m[13];
	else if ( !empty($m[14]) )	$parts['host']    = $m[14];
	else if ( !empty($m[16]) )	$parts['host']    = $m[16];
	else if ( !empty( $m[5] ) )	$parts['host']    = '';
	if ( !empty($m[17]) )		$parts['port']    = $m[18];

	if ( !empty($m[19]) )		$parts['path']    = $m[19];
	else if ( !empty($m[21]) )	$parts['path']    = $m[21];
	else if ( !empty($m[25]) )	$parts['path']    = $m[25];

	if ( !empty($m[27]) )		$parts['query']   = $m[28];
	if ( !empty($m[29]) )		$parts['fragment']= $m[30];

	if ( !$decode )
		return $parts;
	if ( !empty($parts['user']) )
		$parts['user']     = rawurldecode( $parts['user'] );
	if ( !empty($parts['pass']) )
		$parts['pass']     = rawurldecode( $parts['pass'] );
	if ( !empty($parts['path']) )
		$parts['path']     = rawurldecode( $parts['path'] );
	if ( isset($h) )
		$parts['host']     = rawurldecode( $parts['host'] );
	if ( !empty($parts['query']) )
		$parts['query']    = rawurldecode( $parts['query'] );
	if ( !empty($parts['fragment']) )
		$parts['fragment'] = rawurldecode( $parts['fragment'] );
	
	return $parts;
}


/**
 * This function joins together URL components to form a complete URL.
 *
 * RFC3986 specifies the components of a Uniform Resource Identifier (URI).
 * This function implements the specification's "component recomposition"
 * algorithm for combining URI components into a full URI string.
 *
 * The $parts argument is an associative array containing zero or
 * more of the following:
 *
 *	"scheme"	The scheme, such as "http".
 *	"host"		The host name, IPv4, or IPv6 address.
 *	"port"		The port number.
 *	"user"		The user name.
 *	"pass"		The user password.
 *	"path"		The path, such as a file path for "http".
 *	"query"		The query.
 *	"fragment"	The fragment.
 *
 * The "port", "user", and "pass" values are only used when a "host"
 * is present.
 *
 * The optional $encode argument indicates if appropriate URL components
 * should be percent-encoded as they are assembled into the URL.  Encoding
 * is only applied to the "user", "pass", "host" (if a host name, not an
 * IP address), "path", "query", and "fragment" components.  The "scheme"
 * and "port" are never encoded.  When a "scheme" and "host" are both
 * present, the "path" is presumed to be hierarchical and encoding
 * processes each segment of the hierarchy separately (i.e., the slashes
 * are left alone).
 *
 * The assembled URL string is returned.
 *
 * Parameters:
 * 	parts		an associative array of strings containing the
 * 			individual parts of a URL.
 *
 * 	encode		an optional boolean flag selecting whether
 * 			to do percent encoding or not.  Default = true.
 *
 * Return values:
 * 	Returns the assembled URL string.  The string is an absolute
 * 	URL if a scheme is supplied, and a relative URL if not.  An
 * 	empty string is returned if the $parts array does not contain
 * 	any of the needed values.
 */
function join_url( $parts, $encode=FALSE)
{
	if ( $encode )
	{
		if ( isset( $parts['user'] ) )
			$parts['user']     = rawurlencode( $parts['user'] );
		if ( isset( $parts['pass'] ) )
			$parts['pass']     = rawurlencode( $parts['pass'] );
		if ( isset( $parts['host'] ) &&
			!preg_match( '!^(\[[\da-f.:]+\]])|([\da-f.:]+)$!ui', $parts['host'] ) )
			$parts['host']     = rawurlencode( $parts['host'] );
		if ( !empty( $parts['path'] ) )
			$parts['path']     = preg_replace( '!%2F!ui', '/',
				rawurlencode( $parts['path'] ) );
		if ( isset( $parts['query'] ) )
			$parts['query']    = rawurlencode( $parts['query'] );
		if ( isset( $parts['fragment'] ) )
			$parts['fragment'] = rawurlencode( $parts['fragment'] );
	}

	$url = '';
	if ( !empty( $parts['scheme'] ) )
		$url .= $parts['scheme'] . ':';
	if ( isset( $parts['host'] ) )
	{
		$url .= '//';
		if ( isset( $parts['user'] ) )
		{
			$url .= $parts['user'];
			if ( isset( $parts['pass'] ) )
				$url .= ':' . $parts['pass'];
			$url .= '@';
		}
		if ( preg_match( '!^[\da-f]*:[\da-f.:]+$!ui', $parts['host'] ) )
			$url .= '[' . $parts['host'] . ']';	// IPv6
		else
			$url .= $parts['host'];			// IPv4 or name
		if ( isset( $parts['port'] ) )
			$url .= ':' . $parts['port'];
		if ( !empty( $parts['path'] ) && $parts['path'][0] != '/' )
			$url .= '/';
	}
	if ( !empty( $parts['path'] ) )
		$url .= $parts['path'];
	if ( isset( $parts['query'] ) )
		$url .= '?' . $parts['query'];
	if ( isset( $parts['fragment'] ) )
		$url .= '#' . $parts['fragment'];
	return $url;
}


/**
 * This function encodes URL to form a URL which is properly 
 * percent encoded to replace disallowed characters.
 *
 * RFC3986 specifies the allowed characters in the URL as well as
 * reserved characters in the URL. This function replaces all the 
 * disallowed characters in the URL with their repective percent 
 * encodings. Already encoded characters are not encoded again,
 * such as '%20' is not encoded to '%2520'.
 *
 * Parameters:
 * 	url		the url to encode.
 *
 * Return values:
 * 	Returns the encoded URL string. 
 */
function encode_url($url) {
  $reserved = array(
    ":" => '!%3A!ui',
    "/" => '!%2F!ui',
    "?" => '!%3F!ui',
    "#" => '!%23!ui',
    "[" => '!%5B!ui',
    "]" => '!%5D!ui',
    "@" => '!%40!ui',
    "!" => '!%21!ui',
    "$" => '!%24!ui',
    "&" => '!%26!ui',
    "'" => '!%27!ui',
    "(" => '!%28!ui',
    ")" => '!%29!ui',
    "*" => '!%2A!ui',
    "+" => '!%2B!ui',
    "," => '!%2C!ui',
    ";" => '!%3B!ui',
    "=" => '!%3D!ui',
    "%" => '!%25!ui',
  );

  $url = rawurlencode($url);
  $url = preg_replace(array_values($reserved), array_keys($reserved), $url);
  return $url;
}

?>