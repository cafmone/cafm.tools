<?php
/*
http://tools.ietf.org/html/rfc821
**/


class smtp
{
/**
* smtp agent
* @access public
* @var enum [sock|mail]
*/
var $agent;
/**
* url to smtpserver
* @access public
* @var string
*/
var $url;
/**
* smtpserver port
* @access public
* @var int
*/
var $port = 25;
/**
* user to access smtpserver
* @access public
* @var string
*/
var $user;
/**
* password to access smtpserver
* @access public
* @var string
*/
var $pass;
/**
* reciver
* @access public
* @var string
*/
var $to;
/**
* Fsocket errornumber
*
* @access private
* @var string
*/
var $errno;
/**
* Fsocket errorstring
*
* @access private
* @var string
*/
var $errstr;
/**
* debugmode
* @access public
* @var bool
*/
var $debug = false;
/**
* subject
* @access public
* @var string
*/
var $subject;
/**
* mail body
* @access public
* @var string
*/
var $body;
/**
* mimetype of message
* @access public
* @var string
*/
var $mime = 'text/plain';
/**
* attachments
* @access public
* @var array
*/
var $attachments = array();

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 */
	//--------------------------------------------
	function __construct() {
		$this->error = null;

	}

	//-------------------------------------------------------
	/**
	 * Connect to smtp server
	 *
	 * @access public
	 * @return socket
	 */
	//-------------------------------------------------------
	function connect() {
		if($this->debug === true) {
			echo "> Agent: ".$this->agent."<br>";
			flush();
		}
		$this->error = null;
		switch ($this->agent) {
			case 'sock':
				return $this->sock();
			break;
			case 'mail':
				return $this->mail();
			break;
		}
	}

	//-------------------------------------------------------
	/**
	 * Send via php mail
	 *
	 * @access public
	 * @return null|string
	 */
	//-------------------------------------------------------
	function mail() {
		$header  = $this->header();
		$header .= 'Content-type: '.$this->mime.'; charset=UTF-8'."\r\n";
		if($this->debug === true) {
			echo "> ".$this->to."<br>";
			echo "> ".$this->subject."<br>";
			echo "> ".$this->body."<br>";
			echo "> $header<br>";
			flush();
		}
		if(!mail($this->to, $this->subject, $this->body, $header)) {
			$this->error = 'php mail() error';
		}
		return $this->error;
	}

	//-------------------------------------------------------
	/**
	 * Send via sock
	 *
	 * @access public
	 * @return null|string
	 */
	//-------------------------------------------------------
	function sock() {
		if (!isset($this->sock)) {
			$this->sock = @fsockopen($this->url,$this->port,$this->errno,$this->errstr,30);
			if (!$this->sock) {
				$this->error .= "Error : Cannot connect to ".$this->url.":".$this->port."<br>";
				$this->error .= "Error : fsockopen() ".$this->errstr." (".$this->errno.")<br>";
			} else {		
				$this->sock_read(); // allways read socket after write
				$this->sock_write("EHLO 127.0.0.1");
				// check smpt server reply
				if($this->message === '') {
					$this->error .= 'Error: '.$this->url.' did not reply on EHLO.';
					fclose($this->sock);
					return $this->error;
				} else {
					if(strpos($this->message, 'STARTTLS') !== false) {
						$this->sock_write("STARTTLS");
						if(stream_socket_enable_crypto($this->sock, true, STREAM_CRYPTO_METHOD_TLS_CLIENT) === false){
							$this->error .= 'Error: Unable to start tls encryption on '.$_SERVER['HTTP_HOST'];
							$this->sock_write("QUIT");
							fclose($this->sock);
							return $this->error;
						}
						$this->sock_write("EHLO 127.0.0.1");
					}
					$this->sock_write("AUTH LOGIN");
					$this->sock_write(base64_encode($this->user));
					$this->sock_write(base64_encode($this->pass));
					$this->sock_write("MAIL FROM: <".$this->from.">");
					$this->sock_write("RCPT TO: <".$this->to.">");
					$this->sock_write("DATA");

					$lf =  "\r\n";

					if(
						isset($this->attachments) && 
						is_array($this->attachments) && 
						count($this->attachments) > 0
					) {

						require_once(CLASSDIR.'/lib/file/file.mime.class.php');
						$uid = md5(uniqid(time()));

						$header  = $this->header();
						$header .= 'To: '.$this->to .$lf;
						$header .= 'Subject: '. $this->subject .$lf;
						$header .= 'Content-Type: multipart/mixed; boundary="'.$uid.'"'.$lf.$lf;
						$header .= '--'.$uid.$lf;
						$header .= 'Content-type: '.$this->mime.'; charset=UTF-8'.$lf;
						$header .= 'Content-Transfer-Encoding: 8bit' .$lf;
						$header .= $lf.$lf.$this->body .$lf;

						foreach($this->attachments as $file) {
							if(file_exists($file)) {
								$name = basename($file);
								$size = @filesize($file);
								$mime = detect_mime($file);

								$handle  = fopen($file, "r");
								$content = fread($handle, $size);
								fclose($handle);

								$header .= '--'.$uid.$lf;
								$header .= 'Content-Type: '.$mime.'; name="'.$name.'"'.$lf;
								$header .= 'Content-Transfer-Encoding: base64'.$lf;
								$header .= 'Content-Disposition: attachment; filename="'.$name.'"'.$lf.$lf;
								$header .= chunk_split(base64_encode($content));
								$header .= $lf;
							}
						}

					} else {
						$header  = $this->header();
						$header .= 'To: '.$this->to .$lf;
						$header .= 'Subject: '. $this->subject .$lf;
						$header .= 'Content-type: '.$this->mime.'; charset=UTF-8'.$lf;
						$header .= 'Content-Transfer-Encoding: 8bit' .$lf;
						$header .= $lf.$lf.$this->body;
					}

					$this->sock_write($header.$lf.".");
					$this->sock_write("QUIT");
					fclose($this->sock);
					$this->sock = null;
				}
			}
		}
		return $this->error;
	}

	//-------------------------------------------------------
	/**
	 * Send a command to the socket
	 *
	 * @param string $s smtp command
	 * @access public
	 */
	//-------------------------------------------------------
	function sock_write($s) {
		if($this->debug === true) {
			echo "> $s<br>";
			flush();
		}
		### TODO better error handling (e.g. SSL)
		fwrite($this->sock,$s."\r\n");
		$this->sock_read(); // allways read socket after write
	}

	//-------------------------------------------------------
	/**
	 * Read output from socket
	 *
	 * @access public
	 */
	//-------------------------------------------------------
	function sock_read() {
		$this->message = fread($this->sock, 9192);
		if($this->debug === true) {
			echo "> $this->message<br>";
			flush();
		} 
		if(substr($this->message, 0, 1) === "5") {
			$this->error .= $this->message.'<br>';
		}
	}

	//-------------------------------------------------------
	/**
	 * Standard header
	 *
	 * @access public
	 */
	//-------------------------------------------------------
	function header() {
		$lf =  "\r\n";
		$header  = 'From: '.$this->from .$lf;
		$header .= 'Date: '.date("D, d M Y H:i:s O" ,time()) .$lf;
		$header .= 'MIME-Version: 1.0'.$lf;
		$header .= 'Message-Id: <'.base64_encode(uniqid('m')).$this->from. ">".$lf;
		$header .= 'Reply-To: '.$this->from  .$lf;
		$header .= 'X-Mailer: PHP/' . phpversion() .$lf;
		return $header;
	}


	function test($path) {
		ini_set("SMTP","smtp.example.com" ); 
		return $this->connect();

	}

}
?>
