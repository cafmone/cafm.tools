<?php
/**
 * HTTP reader based on curl
 *
 * http://de.php.net/manual/en/book.curl.php
 *
 * @package phppublisher
 * @author Alexander Kuballa <akuballa@users.sourceforge.net>
 * @copyright Copyright (c) 2011, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class curl
{
var $user;
var $pass;

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @return null
	 */
	//--------------------------------------------
	function __construct() {
		try {
			if(function_exists('curl_init') === false) {
				Throw new Exception(
					"FATAL ERROR: ".
					"Could not find function curl_init()! ".
					"Curl might not be installed properly. ".
					"See http://de.php.net/manual/en/book.curl.php. ".
					"For ubuntu try sudo apt-get install php5-curl."
				);
			}
		} catch(Exception $e) {
			echo $e->getMessage();
			exit;
		}
	}

	//--------------------------------------------
	/**
	 * Get infos from response
	 *
	 * $haystack supports
	 * [header|meta|body|charset|title|url|tag]
	 * as searchable values
	 *
	 * @access public
	 * @param enum $haystack
	 * @param string $needle
	 * @return mixed
	 */
	//--------------------------------------------
	function get($haystack, $needle = null) {
		switch($haystack) {
			case 'header':
				if(isset($needle)) {
					$l = count($this->response['header'])-1;
					if(isset($this->response['header'][$l][$needle])) {
						return $this->response['header'][$l][$needle];
					}
				} else {
					return $this->response['header'];
				}
			break;
			case 'meta':
				if(isset($needle)) {
					if(isset($this->response['meta'][$needle])) {
						return $this->response['meta'][$needle];
					}
				} else {
					return $this->response['meta'];
				}
			break;
			case 'body':
				return $this->response['body'];
			break;
			case 'charset':
				return $this->response['charset'];
			break;
			case 'title':
				return $this->response['title'];
			break;
			case 'url':
				return $this->response['url'];
			break;
			case 'tag':
				if(isset($needle)) {
					return $this->__tags($needle);
				}
			break;
		}
	}	

	//---------------------------------------
	/**
	 * Is Redirect
	 *
	 * If page is redirected,
	 * method returns trace as array
	 * e.g. array('302',301',200')
	 * 
	 *
	 * @access public
	 * @return null|array
	 */
	//---------------------------------------
	function isRedirect() {
		$return = null;
		if(count($this->response['header']) > 1) {
			foreach($this->response['header'] as $header) {
				$return[] = $header['status'];
			}
		}
		return $return;	
	}	

	//---------------------------------------
	/**
	 * Connect
	 *
	 * Connect to url and set $this->response
	 *
	 * @access public
	 * @param string $url
	 * @param string $params
	 * @return null
	 */
	//---------------------------------------
	function connect($url, $params = '')	{
		$c = curl_init();

		curl_setopt($c, CURLOPT_URL, $url);
		curl_setopt($c, CURLOPT_POST, 1);
		curl_setopt($c, CURLOPT_POSTFIELDS, $params);

		curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);

		curl_setopt($c, CURLOPT_FRESH_CONNECT, 1);
		curl_setopt($c, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($c, CURLOPT_POSTREDIR, 3);
		curl_setopt($c, CURLOPT_MAXREDIRS, 10);
		curl_setopt($c, CURLOPT_HEADER, 1);
		curl_setopt($c, CURLOPT_ENCODING, 'gzip,deflate');
		curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 90);
		curl_setopt($c, CURLOPT_TIMEOUT, 90);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($c, CURLOPT_FAILONERROR, true);
		curl_setopt($c, CURLOPT_USERAGENT, 'Mozilla/5.0(Windows; U; Windows NT 5.2; rv:1.9.2) Gecko/20100101 Firefox/3.6');
		if(isset($this->user) && isset($this->pass)) {
			curl_setopt($c, CURLOPT_USERPWD, $this->user . ":" . $this->pass);
		}
		// cookies
		$cookie = @tempnam('/dummydir', 'xx');;
		curl_setopt($c, CURLOPT_COOKIEJAR, $cookie);
		curl_setopt($c, CURLOPT_COOKIEFILE, $cookie);
		$response = curl_exec ($c);

		@unlink($cookie);

		if(curl_error($c))	{
			$this->error = curl_error($c);
		} else {
			$this->info = curl_getinfo($c);
			$this->response = $this->__split($response, $this->info['redirect_count'], $this->info['url']);
		}
		curl_close($c);
	}
	
	//---------------------------------------
	/**
	 * Split response
	 *
	 * @access private
	 * @param string $response
	 * @param integer $redirects
	 * @param string $url
	 * @return array
	 */
	//---------------------------------------	
	function __split( $response, $redirects, $url ) {
		if(!empty($response)) {
			$return = array();
			// handle headers
			for($i = 0; $i <= $redirects; $i++) {
				$offset   = strrpos($response, "\r\n\r\n");
				$header   = substr($response, 0, $offset);
				$return['header'][$i] = $this->__header($header);
				// cut off response
				$response = substr($response, $offset+4);
			}
			// handle meta
			$offset            = stripos($response, "<body");
			$head              = substr($response, 0, $offset);
			$response          = substr($response, $offset);
			$return['url']     = $url;
			$return['meta']    = $this->__meta($head);
			$return['charset'] = $this->__charset($return['header'][0]);
			$return['title']   = $this->__title($head);
			if(!isset($return['charset']) || $return['charset'] === '') {
				$return['charset'] = 'ISO-8859-1';
			}			
			$return['body'] = mb_convert_encoding($response, 'UTF-8', $return['charset']);
			return $return;
		} else {
			return 0;
		}
	}
	
	//---------------------------------------
	/**
	 * Header
	 *
	 * @access private
	 * @param string $header
	 * @return null|array
	 */
	//---------------------------------------
	function __header($header) {
		$return = array();
		$headers = explode("\r\n\r\n", $header);
		$j = 0;
		foreach($headers as $v) {
			if($j === 0) {
				$f = strpos($v, ' ')+1;
				$return['response'] = $v;
				$return['status']   = substr($v, $f, 3);
				$j++;
			} else {
				$v = explode(': ', $v);
				$return[strtolower($v[0])] = $v[1];
			}
		}

		if(count($return) > 0) {
			return $return;
		}
	}
	
	//---------------------------------------
	/**
	 * Title
	 *
	 * @access private
	 * @param string $header
	 * @return string
	 */
	//---------------------------------------
	function __title($header) {
		$title = preg_replace('~.*?<title[^>]*?>(.*?)</title>.*~is', '$1', $header, -1, $count);
		if($count > 0) {
			return $title;
		}
	}
	
	//---------------------------------------
	/**
	 * Meta
	 *
	 * @access private
	 * @param string $data
	 * @return array
	 */
	//---------------------------------------
	function __meta($data) {
		$return = null;
		// replace linefeeds
		$data = str_replace("\r\n", "\n", $data);
		$data = str_replace("\n", "", $data);
		// get meta
		preg_match_all('~<meta([^>].*?)>~i', $data, $matches );
		if(isset($matches[1][0])) {
			foreach($matches[1] as $match) {
				$key   = null;
				$value = null;
				$lang  = null;
				$metas = explode('" ', $match);
				foreach($metas as $meta) {
					$t = explode('=', $meta);
					$k = strtolower(trim($t[0])); unset($t[0]);
					$v = trim(str_replace('"', '', implode('=', $t)));
					switch($k) {
						case 'name':
						case 'http-equiv': $key = strtolower($v); break;
						case 'content':	$value = $v; break;
						case 'lang': $lang = strtolower($v); break;
					}
				}
				if(isset($key) && isset($value)) {
					if(!isset($lang) ) {
						$return[$key] = $value;
					}
					else if(isset($lang) ) {
						$return[$key][$lang] = $value;
					}
				}
			}
		}
		return $return;
	}
	
	//---------------------------------------
	/**
	 * Charset
	 *
	 * get charset as uppercase string
	 *
	 * @access private
	 * @param array $data
	 * @return null|string
	 */
	//---------------------------------------
	function __charset($data) {
		if(isset($data['content-type'])) {
			$set = $data['content-type'];
			$offset = strpos($set, 'charset=');
			if($offset !== false) {
				return strtoupper(substr($set, $offset+8));
			}
		}
	}
	
	//---------------------------------------
	/**
	 * Get a HTML Tag from body
	 *
	 * $name must be a valid html tag
	 * like a, span or h1. Methode returns
	 * null if nothing is found or an array
	 * of tag attributes
	 *
	 * @access private
	 * @param array $name
	 * @return null|array
	 */
	//---------------------------------------
	function __tags($name) {
		$return = null;
		preg_match_all('~<'.$name.'([^>].*?|)>(.*?)<\\\\?/'.$name.'>~is', $this->response['body'], $matches );
		if(isset($matches[2][0])) {
			$i = 0;
			foreach($matches[2] as $key => $match) {
				$return[$i]['label'] = $matches[2][$key];
				if(isset($matches[1][$key]) && $matches[1][$key] !== '') {
					$attribs = explode('" ', $matches[1][$key]);
					foreach($attribs as $attrib) {
						$t = explode('=', $attrib);
						$k = strtolower(trim($t[0])); unset($t[0]);
						$v = trim(str_replace('"', '', implode('=', $t)));
						$return[$i][$k] = $v;
					}
				}
				$i++;
			}
		}
		return $return;
	}
	
}
