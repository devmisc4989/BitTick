<?
/**
* OO cURL Class
* Object oriented wrapper for the cURL library.
* @author David Hopkins (semlabs.co.uk)
* @version 0.3
*/
class CURL
{
	
	public $sessions 				=	array();
	public $retry					=	2;
	public $header 					=	array();
	private $opts = array(
		CURLOPT_USERAGENT	=> "Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/30.0.1599.17 Safari/537.36",
		CURLOPT_SSL_VERIFYHOST	=> false,
		CURLOPT_SSL_VERIFYPEER	=> false,
		CURLOPT_FOLLOWLOCATION	=> true,/* to activate follow location redirect */
		CURLOPT_RETURNTRANSFER	=> true,
		CURLOPT_AUTOREFERER => true,
		CURLOPT_TIMEOUT		=> 10,
		CURLOPT_HTTPHEADER => array("Accept-Language:de-DE,de;q=0.8,en-US;q=0.6,en;q=0.4", "Expect:")
	);

	/**
	* Adds a cURL session to stack
	* @param $url string, session's URL
	* @param $opts array, optional array of cURL options and values
	*/
	public function addSession( $url)
	{
		$this->sessions[] = curl_init( $url );
		$key = count( $this->sessions ) - 1;
		$this->setOpts( $this->opts, $key );
	}
	
	/**
	* Sets an option to a cURL session
	* @param $option constant, cURL option
	* @param $value mixed, value of option
	* @param $key int, session key to set option for
	*/
	public function setOpt( $option, $value, $key = 0 )
	{
		curl_setopt( $this->sessions[$key], $option, $value );
	}
	
	/**
	* Sets an array of options to a cURL session
	* @param $options array, array of cURL options and values
	* @param $key int, session key to set option for
	*/
	public function setOpts( $options, $key = 0 )
	{
		curl_setopt_array( $this->sessions[$key], $options );
	}
	
	/**
	* Executes as cURL session
	* @param $key int, optional argument if you only want to execute one session
	*/
	public function exec( $key = false )
	{
		$no = count( $this->sessions );
		
		if( $no == 1 )
			$res = $this->execSingle();
		elseif( $no > 1 ) {
			if( $key === false )
				$res = $this->execMulti();	
			else
				$res = $this->execSingle( $key );
		}
		
		if( $res )
			return $res;
		else 
			return "an error has occurred";
	}
	
	/**
	* Executes a single cURL session
	* @param $key int, id of session to execute
	* @return array of content if CURLOPT_RETURNTRANSFER is set
	*/
	public function execSingle( $key = 0 )
	{
		if( $this->retry > 0 )
		{
			$retry = $this->retry;
			$code = 0;
			while( $retry >= 0 && ( $code[0] == 0 || $code[0] >= 400 ) )
			{
				$res = $this->curl_redir_exec( $this->sessions[$key] );
				$code = $this->info( $key, CURLINFO_HTTP_CODE );
				
				$retry--;
			}
		}
		else
			$res = curl_redir_exec( $this->sessions[$key] );
		
		return $res;
	}
	
	/**
	* Executes a stack of sessions
	* @return array of content if CURLOPT_RETURNTRANSFER is set
	*/
	public function execMulti()
	{
		$mh = curl_multi_init();
		
		#Add all sessions to multi handle
		foreach ( $this->sessions as $i => $url )
			curl_multi_add_handle( $mh, $this->sessions[$i] );
		
		do
			$mrc = curl_multi_exec( $mh, $active );
		while ( $mrc == CURLM_CALL_MULTI_PERFORM );
		
		while ( $active && $mrc == CURLM_OK )
		{
			if ( curl_multi_select( $mh ) != -1 )
			{
				do
					$mrc = curl_multi_exec( $mh, $active );
				while ( $mrc == CURLM_CALL_MULTI_PERFORM );
			}
		}

		if ( $mrc != CURLM_OK )
			echo "Curl multi read error $mrc\n";
		
		#Get content foreach session, retry if applied
		foreach ( $this->sessions as $i => $url )
		{
			$code = $this->info( $i, CURLINFO_HTTP_CODE );
			if( $code[0] > 0 && $code[0] < 400 )
				$res[] = curl_multi_getcontent( $this->sessions[$i] );
			else
			{
				if( $this->retry > 0 )
				{
					$retry = $this->retry;
					$this->retry -= 1;
					$eRes = $this->execSingle( $i );
					
					if( $eRes )
						$res[] = $eRes;
					else
						$res[] = false;
						
					$this->retry = $retry;
					echo '1';
				}
				else
					$res[] = false;
			}

			curl_multi_remove_handle( $mh, $this->sessions[$i] );
		}

		curl_multi_close( $mh );
		
		return $res;
	}
	
	/**
	* Closes cURL sessions
	* @param $key int, optional session to close
	*/
	public function close( $key = false )
	{
		if( $key === false )
		{
			foreach( $this->sessions as $session )
				curl_close( $session );
		}
		else
			curl_close( $this->sessions[$key] );
	}
	
	/**
	* Remove all cURL sessions
	*/
	public function clear()
	{
		foreach( $this->sessions as $session )
			curl_close( $session );
		unset( $this->sessions );
	}
	
	/**
	* Returns an array of session information
	* @param $key int, optional session key to return info on
	* @param $opt constant, optional option to return
	*/
	public function info( $key = false, $opt = false )
	{
		if( $key === false )
		{
			foreach( $this->sessions as $key => $session )
			{
				if( $opt )
					$info[] = curl_getinfo( $this->sessions[$key], $opt );
				else
					$info[] = curl_getinfo( $this->sessions[$key] );
			}
		}
		else
		{
			if( $opt )
				$info[] = curl_getinfo( $this->sessions[$key], $opt );
			else
				$info[] = curl_getinfo( $this->sessions[$key] );
		}
		
		return $info;
	}
	
	/**
	* Returns an array of errors
	* @param $key int, optional session key to retun error on
	* @return array of error messages
	*/
	public function error( $key = false )
	{
		if( $key === false )
		{
			foreach( $this->sessions as $session )
				$errors[] = curl_error( $session );
		}
		else
			$errors[] = curl_error( $this->sessions[$key] );
			
		return $errors;
	}
	
	/**
	* Returns an array of session error numbers
	* @param $key int, optional session key to retun error on
	* @return array of error codes
	*/
	public function errorNo( $key = false )
	{
		if( $key === false )
		{
			foreach( $this->sessions as $session )
				$errors[] = curl_errno( $session );
		}
		else
			$errors[] = curl_errno( $this->sessions[$key] );
			
		return $errors;
	}
	
	/*
	 * Helper function: process redirects without using curl's built in "followlocation" option
	 * This is necessary for security reasons (see http://php.net/manual/de/function.curl-setopt.php#71313)
	 */
	public function curl_redir_exec($ch) {
		//return curl_exec($ch);
		
		//print_r($ch);
		//clean header
		$this->header = array();
		
        static $curl_loops = 0;
        static $curl_max_loops = 20;
        if ($curl_loops++ >= $curl_max_loops)
        {
            $curl_loops = 0;
            return FALSE;
        }
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        $data = curl_exec($ch);
        //list($header, $data) = explode("\n\n", $data, 2);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$header = substr($data, 0, $header_size);
		
		//store header
		$this->header = http_parse_headers($header);
		
		$data = substr($data, $header_size);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($http_code == 301 || $http_code == 302)
        {
            $matches = array();
            preg_match('/Location:(.*?)\n/', $header, $matches);
            $url = @parse_url(trim(array_pop($matches)));
            //print_r($url);
            if (!$url)
            {
                //couldn't process the url to redirect to
                $curl_loops = 0;
                return $data;
            }
            $last_url = parse_url(curl_getinfo($ch, CURLINFO_EFFECTIVE_URL));
            if (!$url['scheme'])
                $url['scheme'] = $last_url['scheme'];
            if (!$url['host'])
                $url['host'] = $last_url['host'];
            if (!$url['path'])
                $url['path'] = $last_url['path'];
            $new_url = $url['scheme'] . '://' . $url['host'] . $url['path'] . ($url['query']?'?'.$url['query']:'');
            curl_setopt($ch, CURLOPT_URL, $new_url);
            return $this->curl_redir_exec($ch);
        } else {
            $curl_loops=0;
            return $data;
        }
    }
	
    public function setUserAgent($ua){
        if($ua!='')
            $this->opts[CURLOPT_USERAGENT] = $ua;
    }	
}

/* parse headers */
if (!function_exists('http_parse_headers'))
{
    function http_parse_headers($raw_headers)
    {
        $headers = array();
        $key = ''; // [+]

        foreach(explode("\n", $raw_headers) as $i => $h)
        {
            $h = explode(':', $h, 2);

            if (isset($h[1]))
            {
                if (!isset($headers[$h[0]]))
                    $headers[$h[0]] = trim($h[1]);
                elseif (is_array($headers[$h[0]]))
                {
                    // $tmp = array_merge($headers[$h[0]], array(trim($h[1]))); // [-]
                    // $headers[$h[0]] = $tmp; // [-]
                    $headers[$h[0]] = array_merge($headers[$h[0]], array(trim($h[1]))); // [+]
                }
                else
                {
                    // $tmp = array_merge(array($headers[$h[0]]), array(trim($h[1]))); // [-]
                    // $headers[$h[0]] = $tmp; // [-]
                    $headers[$h[0]] = array_merge(array($headers[$h[0]]), array(trim($h[1]))); // [+]
                }

                $key = $h[0]; // [+]
            }
            else // [+]
            { // [+]
                if (substr($h[0], 0, 1) == "\t") // [+]
                    $headers[$key] .= "\r\n\t".trim($h[0]); // [+]
                elseif (!$key) // [+]
                    $headers[0] = trim($h[0]);trim($h[0]); // [+]
            } // [+]
        }

        return $headers;
    }
}
?>