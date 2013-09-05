<?php
include_once("../../class/dbconnect.php"); //Database handler functions and connection


function http_digest_parse($txt)
	{
    	// protect against missing data
    	$needed_parts = array('nonce'=>1, 'nc'=>1, 'cnonce'=>1, 'qop'=>1, 'username'=>1, 'uri'=>1, 'response'=>1);
    	$data = array();
    	$keys = implode('|', array_keys($needed_parts));

    	preg_match_all('@(' . $keys . ')=(?:([\'"])([^\2]+?)\2|([^\s,]+))@', $txt, $matches, PREG_SET_ORDER);

    	foreach ($matches as $m) {
        	$data[$m[1]] = $m[3] ? $m[3] : $m[4];
        	unset($needed_parts[$m[1]]);
    	}

    	return $needed_parts ? false : $data;
 }
 
 function validate($key, $secret){
	 $dbHandler = new databaseHandler();
	 $key = mysql_real_escape_string ($key);
	 $secret = mysql_real_escape_string ($secret);
	 $eds = $dbHandler->queryQ('apiuser','*',"AID = 3 AND key = " + $key." AND ".md5($secret));
	 if(count($eds) > 0){
		 return true;
	 }
	 return false;
 }
 function smiley($text, $size = "", $color = "") {
		$color = strtolower($color);
		$size = intval($size);
		if($size == 0){
			$size = 18;
		}
		$text = urldecode($text);
        $s2 = "src='http://cluealley.com/smile/";
		if(in_array($size, array("black", "white", "gray", "blue"))){
			$s2 = $s2.$color."/";
		} else {
			$s2 = $s2."yellow/";
		}
		
		$xs = array(12, 18, 16, 24, 32, 64, 128);
		if(in_array($size, $xs)){
			$s2 = "<img class='clue_smiley' ".$s2."/".$size."/";
		} else {
			foreach($xs as $x){
				if($size <  $x){
					$s2 = "<img class='clue_smiley' width='".$size."' height='".$size."' ".$s2."/".$x."/";
				}
				break;	
			}
		} 
        $sm = ".gif' />"; // Extension of the images (All images must be the same extension)
        $array = array(
            ':)' =>  $s2.'happy'.$sm,
            ':(' => $s2.'sad'.$sm,
            ';)' =>	$s2.'winking'.$sm,
            ':D' => 	$s2.'biggrin'.$sm,
				';;)' =>	$s2.'battingeyelashes'.$sm,
				'>:D<' => $s2.'bighug'.$sm,
				':-/' => $s2.'confused'.$sm,
				':x' => 	$s2.'lovestruck'.$sm,
				':">' =>	$s2.'blushing'.$sm,
				':P' => $s2.'tongue'.$sm,
				':-*' => $s2.'kiss'.$sm,
				'=((' => $s2.'brokenheart'.$sm,
				':-O' => $s2.'surprise'.$sm,
				'X(' => 	$s2.'angry'.$sm,
				':>' =>	$s2.'smug'.$sm,
				'B-)' => $s2.'cool'.$sm,
				':-S' => $s2.'worried'.$sm,
				'#:-S' => $s2.'whew'.$sm,
				'>:)' =>	$s2.'devil'.$sm,
				':((' =>	$s2.'crying'.$sm,
				':))' => $s2.'laughing'.$sm,
				':|' => $s2.'straightface'.$sm,
				'/:)' => $s2.'raisedeyebrows'.$sm,
				'=))' => $s2.'rollingonthefloor'.$sm,
				'O:-)' => $s2.'angel'.$sm,
				':-B' => $s2.'nerd'.$sm,
				'=;' => $s2.'talktothehand'.$sm,
				':-c' => $s2.'callme'.$sm,
				':)]' => $s2.'onthephone',
				'~X(' => $s2.'atwitsend',
				':-h' => $s2.'wave'.$sm,
				':-t' =>	$s2.'timeout'.$sm,
				'8->' => $s2.'daydreaming'.$sm,
				'I-)' => $s2.'sleepy'.$sm,
				'8-|' =>	$s2.'rollingeyes'.$sm,
				'L-)' =>	$s2.'loser'.$sm,
				':-&' =>	$s2.'sick'.$sm,
				':-$' =>	$s2.'donttellanyone'.$sm,
				'[-(' => $s2.'notalking'.$sm,
				':O)' => $s2.'clown'.$sm,
				'8-}' =>	$s2.'silly'.$sm,
				'<:-P' => $s2.'party'.$sm,
				'(:|' => $s2.'yawn'.$sm,
				'=P~' => $s2.'drooling'.$sm,
				':-?' => $s2.'thinking'.$sm,
				'#-o' => $s2.'doh'.$sm,
				'=D>' =>	$s2.'applause'.$sm,
				':-SS' => $s2.'nailbiting'.$sm,
				'@-)' => $s2.'hypnotized'.$sm,
				':^o' => $s2.'liar'.$sm,
				':-w' => $s2.'waiting'.$sm,
				':-<' => $s2.'sigh'.$sm,
				'>:P' =>	$s2.'phbbbbt'.$sm,
				'<):)' => $s2.'cowboy'.$sm,
				'X_X' => $s2.'Idontwanttosee'.$sm,
				':!!' => $s2.'hurryup'.$sm,
				'\m/' => $s2.'rockon'.$sm,
				':-q' =>	$s2.'thumbsdown'.$sm,
				':-bd' => $s2.'thumbsup'.$sm,
				'^#(^' => $s2.'itwasntme'.$sm,
				':ar!' => $s2.'pirate'.$sm
        );
        return str_replace(array_keys($array), array_values($array), stripslashes($text));
    } 


class RestUtils
{
	// function to parse the http auth header
	
	
	public static function processRequest(){
		if(!isset($_SERVER['PHP_AUTH_USER'])  || !isset($_SERVER['PHP_AUTH_PW']) ){
			die(RestUtils::sendResponse(401));					
		}			
				
		$dbUser = $_SERVER['PHP_AUTH_USER'];
		$dbPass = md5( $_SERVER['PHP_AUTH_PW'] ) ;
		
		if(!validate($dbUser, $dbPass)){
			die(RestUtils::sendResponse(401));	
		}
						
		// get our verb
		$request_method = strtolower($_SERVER['REQUEST_METHOD']);
		$return_obj		= new RestRequest();
		
		// we'll store our data here
		$data	= "";

		switch (strtoupper($request_method))
		{
			// gets are easy...
			case 'GET':
				$data = smiley($_GET['content']);
				break;
			case 'POST':
				$data = smiley($_POST['content']);
				break;
		}
		
		return $data;
	}

	public static function sendResponse($status = 200, $body = '', $content_type = 'text/html')
	{
		$status_header = 'HTTP/1.1 ' . $status . ' ' . RestUtils::getStatusCodeMessage($status);
		// set the status
		header($status_header);
		// set the content type
		header('Content-type: ' . $content_type);

		// pages with body are easy
		if($body != '')
		{
			
			echo($body);
		}
		// we need to create the body if none is passed
		else
		{
			// create some body messages
			$message = '';

			// this is purely optional, but makes the pages a little nicer to read
			// for your users.  Since you won't likely send a lot of different status codes,
			// this also shouldn't be too ponderous to maintain
			switch($status)
			{
				case 401:
					$message = 'You must be authorized to view this page.';
					break;
				case 404:
					$message = 'The requested URL ' . $_SERVER['REQUEST_URI'] . ' was not found.';
					break;
				case 500:
					$message = 'The server encountered an error processing your request.';
					break;
				case 501:
					$message = 'The requested method is not implemented.';
					break;
			}

			// servers don't always have a signature turned on (this is an apache directive "ServerSignature On")
 			//$signature = ($_SERVER['SERVER_SIGNATURE'] == '') ? $_SERVER['SERVER_SOFTWARE'] . ' Server at ' . $_SERVER['SERVER_NAME'] . ' Port ' . $_SERVER['SERVER_PORT'] : $_SERVER['SERVER_SIGNATURE'];

			// this should be templatized in a real-world solution
			$body = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
						<html>
							<head>
								<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
								<title>' . $status . ' ' . RestUtils::getStatusCodeMessage($status) . '</title>
							</head>
							<body>
								<h1>' . RestUtils::getStatusCodeMessage($status) . '</h1>
								<p>' . $message . '</p>
								<hr />
							</body>
						</html>';
			echo $body;
			exit;
		}
	}
    
	public static function getStatusCodeMessage($status)
	{
		// these could be stored in a .ini file and loaded
		// via parse_ini_file()... however, this will suffice
		// for an example
		$codes = Array(
		    100 => 'Continue',
		    101 => 'Switching Protocols',
		    200 => 'OK',
		    201 => 'Created',
		    202 => 'Accepted',
		    203 => 'Non-Authoritative Information',
		    204 => 'No Content',
		    205 => 'Reset Content',
		    206 => 'Partial Content',
		    300 => 'Multiple Choices',
		    301 => 'Moved Permanently',
		    302 => 'Found',
		    303 => 'See Other',
		    304 => 'Not Modified',
		    305 => 'Use Proxy',
		    306 => '(Unused)',
		    307 => 'Temporary Redirect',
		    400 => 'Bad Request',
		    401 => 'Unauthorized',
		    402 => 'Payment Required',
		    403 => 'Forbidden',
		    404 => 'Not Found',
		    405 => 'Method Not Allowed',
		    406 => 'Not Acceptable',
		    407 => 'Proxy Authentication Required',
		    408 => 'Request Timeout',
		    409 => 'Conflict',
		    410 => 'Gone',
		    411 => 'Length Required',
		    412 => 'Precondition Failed',
		    413 => 'Request Entity Too Large',
		    414 => 'Request-URI Too Long',
		    415 => 'Unsupported Media Type',
		    416 => 'Requested Range Not Satisfiable',
		    417 => 'Expectation Failed',
		    500 => 'Internal Server Error',
		    501 => 'Not Implemented',
		    502 => 'Bad Gateway',
		    503 => 'Service Unavailable',
		    504 => 'Gateway Timeout',
		    505 => 'HTTP Version Not Supported'
		);

		return (isset($codes[$status])) ? $codes[$status] : '';
	}
}

class RestRequest
{
	private $request_vars;
	private $data;
	private $http_accept;
	private $method;

	public function __construct()
	{
		$this->request_vars		= array();
		$this->data				= '';
		$this->http_accept		= (strpos($_SERVER['HTTP_ACCEPT'], 'json')) ? 'json' : 'xml';
		$this->method			= 'get';
	}

	public function setData($data)
	{
		$this->data = $data;
	}

	public function setMethod($method)
	{
		$this->method = $method;
	}

	public function setRequestVars($request_vars)
	{
		$this->request_vars = $request_vars;
	}

	public function getData()
	{
		return $this->data;
	}

	public function getMethod()
	{
		return $this->method;
	}

	public function getHttpAccept()
	{
		return $this->http_accept;
	}

	public function getRequestVars()
	{
		return $this->request_vars;
	}
}
?>
