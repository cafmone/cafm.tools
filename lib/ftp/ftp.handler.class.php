<?php
/**
 * PHP fsocket Ftp Client based on rfc959
 * For more information refer to http://www.w3.org/Protocols/rfc959/
 * http://www.raditha.com/php/ftp/
 *
 * @package ftp
 * @author Alexander Kuballa <akuballa@users.sourceforge.net>
 * @copyright Copyright (c) 2008, Alexander Kuballa
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.0
 */


class ftp_handler
{


//------------------------------------------ Private Section
/**
* searchstring
* @access private
* @var string
*/
var $searchstring = '';
/**
* fsocket Errornumber
* @access private
* @var string
*/
var $errno;
/**
* fsocket Errorstring
* @access private
* @var string
*/
var $errstr;
/**
* month to number
* @access private
* @var array
*/
var $ar_month = array(
		'jan' => '01',
		'feb' => '02',
		'mar' => '03',
		'apr' => '04',
		'may' => '05',
		'jun' => '06',
		'jul' => '07',
		'aug' => '08',
		'sep' => '09',
		'oct' => '10',
		'nov' => '11',
		'dec' => '12',
		);
	
/**
*  files not to be shown
*  @access private
*  @var array
*/
var $arExcludedFiles = array('.', '..');
/**
*  define allowed chars for filname
*  @access public
*  @var string
*/
var $regex_filename = '[a-zA-Z0-9~._-]';


	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 */
	//--------------------------------------------
	function ftp_handler( $ftp ) {
		$this->ftp = $ftp;
	}

	//-------------------------------------------------------
	/**
	 * connect to ftp server
	 *
	 * @access public
	 * @return socket
	 */
	//-------------------------------------------------------
	function connect() {
		if (!isset($this->sock)) {
			$this->sock = @fsockopen($this->ftp->server_url,$this->ftp->server_port,$this->errno,$this->errstr,30);
			if (!$this->sock) {
				echo "Error : Cannot connect to remote host \"".$this->ftp->server_url.":".$this->ftp->server_port."\"<br>";
				echo "Error : fsockopen() ".$this->errstr." (".$this->errno.")<br>";
				exit;
			} else {		
				$this->sock_read(); // allways read socket after write
				$this->sock_write("USER ".$this->ftp->server_user);
				$this->sock_write("PASS ".$this->ftp->server_pass);
				return $this->sock;
			}
		}
	}
	
	//-------------------------------------------------------
	/**
	 * send a command to the socket
	 *
	 * @param string $s ftp command
	 * @access public
	 */
	//-------------------------------------------------------
	function sock_write($s) {
		if($this->ftp->debug === true) {
			echo "> $s<br>";
			flush();
		}
		fputs($this->sock,$s."\r\n", 2048);
		$this->sock_read(); // allways read socket after write
	}
	
	//-------------------------------------------------------
	/**
	 * data connection to ftp server (passiv)
	 *
	 * @access public
	 * @return data-socket
	 */
	//-------------------------------------------------------
	function pasv()	{		
		$this->sock_write("PASV");
		$offset = strpos($this->message,"(");
		$s = substr($this->message,++$offset,strlen($this->message)-2);
		$parts = explode(',',$s);
		$data_host = "$parts[0].$parts[1].$parts[2].$parts[3]";
		$data_port = ((int)$parts[4] << 8) + (int) $parts[5];
		$this->data_sock = fsockopen($data_host,$data_port,$errno,$errstr,30);
		return $this->data_sock;
	}
	
	//-------------------------------------------------------
	/**
	 * read output from socket
	 *
	 * @access public
	 */
	//-------------------------------------------------------
	function sock_read() {
		do {
			$this->message = fread($this->sock, 9192);
			if($this->ftp->debug === true) {
				echo "> $this->message<br>";
				flush();
			} 
		} while (substr($this->message, 3, 1) != " ");
	}

	//-------------------------------------------------------
	/**
	 * get list of specified directory
	 *
	 * @param string $dir path to directory
	 * @access public
	 * @return array
	 */
	//-------------------------------------------------------
	function rawlist_dump( $dir = '/') {
		$rawlist = array();
		if(
			isset($this->__dir) &&
			$this->__dir === $dir &&
			isset($this->__rawlist)	
		) {
			return $this->__rawlist;
		} else {
			if($this->pasv()) {
				$this->sock_write('CWD '.$dir);
				$this->sock_write('LIST');
				$ftp_rawlist = array();
				while(true)	{
					$line = fgets($this->data_sock);
					$ftp_rawlist[] = $line;
					if($line == '') {
						fclose($this->data_sock);
						break;
					}
				}
				$this->sock_read(); // List sends second line
				$null = array_pop($ftp_rawlist); // remove last entry

				foreach ($ftp_rawlist as $v) {
					$v = str_replace("\r\n", "",  $v);
					$info = array();
					$vinfo = preg_split("/[\s]+/", $v, 9);
					if ($vinfo[0] !== "total") {
						if($vinfo[8] !== '..' && $vinfo[8] !== '.') {
							$info['chmod'] = $vinfo[0];
							$info['filesize'] = $vinfo[4];
							$info['name'] = $vinfo[8];
							//$info['num'] = $vinfo[1];
							//$info['owner'] = $vinfo[2];
							//$info['group'] = $vinfo[3];
							//$info['month'] = $vinfo[5];
							//$info['day'] = $vinfo[6];
							//$info['time'] = $vinfo[7];
							$month = $this->ar_month[strtolower($vinfo[5])];
							preg_match('~[:]~i', $vinfo[7], $matches);
							if($matches) {
								$today = getdate();
								$info['date'] = $today['year'].'/'.$month.'/'.$vinfo[6].' - '.$vinfo[7];					
							} else {
								$info['date'] = $vinfo[7].'/'.$month.'/'.$vinfo[6];
							}
							$rawlist[$info['name']] = $info;
						}
					}
				}
				$this->__dir = $dir;
				$this->__rawlist = $rawlist;
				return $rawlist;
			}
		}
	}

	//-------------------------------------------------------
	/**
	 * get namelist of specified directory
	 *
	 * @param string $dir path to directory
	 * @access public
	 * @return string (empty if file does not exist)
	 */
	//-------------------------------------------------------
	function file_exists( $path ) {
		$name  = basename($path);
		$cname = strtolower($name);
		$dir   = substr($path, 0, strrpos($path, '/'));
		$list  = array();
		if($this->pasv()) {
			$this->sock_write('CWD '.$dir);
			$this->sock_write('NLST');
			while(true)	{
				$line = fgets($this->data_sock);
				$line = str_replace("\r\n", "",  $line);
				if($line !== '..' && $line !== '.') {
					$list[] = strtolower($line);
					if($line === '') {
						fclose($this->data_sock);
						break;
					}
				}
			}
			$this->sock_read(); // NLST sends second line
		}
		if(in_array($cname, $list) === true) {
			return $name.' exists';
		} else {
			return '';
		}
	}

	//-------------------------------------------------------
	/**
	 * alias for rename
	 *
	 * @access public
	 * @param string $path path to ftp file
	 * @param string $target path to rename
	 */
	//-------------------------------------------------------
	function move($path, $target) {
		return $this->rename($path, $target);
	}

	//-------------------------------------------------------
	/**
	 * rename a file
	 *
	 * @access public
	 * @param string $path path to ftp file
	 * @param string $target path to rename
	 * @return string
	 */
	//-------------------------------------------------------
	function rename($path, $target) {
		$msg = '';
		if($path !== '' && $target !== '') {
			$this->connect();
			$this->sock_write('RNFR '.$path);
			$this->sock_write('RNTO '.$target);
			if(substr($this->message,0,3) !== '250') {
				$msg = $this->message;
			}
		}
		return $msg;
	}

	//-------------------------------------------------------
	/**
	 * make dir
	 *
	 * @access public
	 * @param string $path path to ftp file
	 * @return string
	 */
	//-------------------------------------------------------
	function mkdir($path) {
		$this->connect();
		$msg = $this->file_exists($path);
		if($msg === '') {
			$this->sock_write('MKD '.$path);
			if(substr($this->message,0,3) !== '257') {
				$msg = $this->message;
			}
		}
		return $msg;
	}

	//-------------------------------------------------------
	/**
	 * make file
	 *
	 * @access public
	 * @param string $path path to ftp file
	 * @return string
	 */
	//-------------------------------------------------------
	function mkfile($path) {
		$this->connect();
		$msg = $this->file_exists($path);
		if($msg === '') {
			if($this->pasv()) {
				$this->sock_write('STOR '.$path);				
				fclose($this->data_sock);
				$this->sock_read();
				if(substr($this->message,0,3) !== '226') {
					$msg = $this->message;
				}
			}
		}
		return $msg;
	}

	//-------------------------------------------------------
	/**
	 * copy a file ($path) to $target
	 *
	 * @access public
	 * @param $path string
	 * @param $target string
	 * @return string on error
	 */
	//-------------------------------------------------------
	function copy($path, $target) {
		$msg = '';
		if($path !== $target) {
			if(file_exists($path)) {
				$msg = $this->download($path, $target);
			}
			if(file_exists($target)) {
				$msg = $this->upload($path, $target);
			}
		}
		return $msg;
	}

	//-------------------------------------------------------
	/**
	 * download a file
	 *
	 * @access public
	 * @param $path path to ftp file
	 * @param $target path to local directory 
	 */
	//-------------------------------------------------------
	function download($path, $target) {
		$this->connect();
		$this->sock_write('TYPE I');
		$this->pasv();
		$this->sock_write('RETR '.$path);
		
			$out = '';
			while(true)	{
				$line = fgets($this->data_sock);
				if($line == '') {
					fclose($this->data_sock);
					break;
				} else {
					$out .= $line;
				}
			} 
			
		$fp = fopen($target.basename($path), 'w');
		fwrite($fp, $out);
		fclose($fp);
	}

	//-------------------------------------------------------
	/**
	 * upload file
	 *
	 * @access public
	 * @param string $path path to local file
	 * @param string $target path to ftp file
	 * @return string
	 */
	//-------------------------------------------------------
	function upload($path, $target, $replace = false) {
		$this->connect();
		$msg = '';
		if( $replace === false) {
			$msg = $this->file_exists($target);
		}
		if($msg === '') {
			$this->sock_write('TYPE I');
			if($this->pasv()) {
				$this->sock_write('STOR '.$target);				
				$fp = fopen($path,"rb");
				while(!feof($fp))
				{					
					$s = fread($fp, filesize($path)+1);
					fwrite($this->data_sock,$s);
				}
				fclose($this->data_sock);
				fclose($fp);
				$this->sock_read();
				if(substr($this->message,0,3) !== '226') {
					$msg = $this->message;
				}
			} else {
				$msg = 'Pasv error';
			}
		}
		return $msg;
	}

	//-------------------------------------------------------
	/**
	 * delete a file ($path)
	 *
	 * @access public
	 * @param string $path path to ftp file
	 * @param $recursive bool
	 * @return string
	 */
	//-------------------------------------------------------
	function remove($path, $recursive = false) {
		$ar = array();
		if($path !== '') {
			$this->connect();
			$bool = $this->is_dir($path);
			if($bool == true) {
				$list = $this->rawlist_dump($path);
				if(count($list) > 0) {
					if($recursive === true) {
						foreach($list as $key => $value) {
		            		$error = $this->remove($path.'/'.$value['name'], $recursive);
							if($error !== '') { $ar[] = $error; }
						}
						$this->sock_write('RMD '.$path);
						if(substr($this->message,0,3) !== '257') {
							$ar[] = $this->message;
						}
					}
					if($recursive === false) {
						$ar[] = 'dir '.$path.' is not empty';
					}
				} else {
					$this->sock_write('RMD '.$path);
					if(substr($this->message,0,3) !== '257') {
						$ar[] = $this->message;
					}
				}
			} 
			if($bool === false) {
				$this->sock_write('DELE '.$path);
				if(substr($this->message,0,3) !== '250') {
					$ar[] = $this->message;
				}
			}
		}
		$msg = join('<br>', $ar);
		return $msg;
	}
	
	//-------------------------------------------------------
	/**
	 * search for filename
	 *
	 * @access public
	 * @param string $searchstring
	 */
	//-------------------------------------------------------
	function search($searchstring = '', $recursive = true) {
		if($this->connect()) {
			if($searchstring != '') {
				$this->searchstring = $searchstring;
				$this->get_folder($this->server_dir, $recursive);
			} else {
				$this->get_folder($this->server_dir);
			}
		}
	}

	//-------------------------------------------------------
	/**
	 * read directory and return an array of fileinfos
	 *
	 * @param $path string
	 * @param $excludes array files not to be returned
	 * @return array
	 */
	//-------------------------------------------------------
	function get_files($path, $excludes='', $pattern='*', $subpattern = null) {
		$this->connect();
		$ar = array();
		if($excludes != '') {
			$this->arExcludedFiles = array_merge($this->arExcludedFiles, $excludes);
		}
		$rawlist = $this->rawlist_dump($path);
		if(is_array($rawlist)) {
			foreach ($rawlist as $k => $v) {
				$file = $path.'/'.$v['name'];
				if($v['chmod']{0} === '-') {
					if (in_array($file, $this->arExcludedFiles) === false){
						$tmp['path']      = $file;
						$tmp['name']      = $v['name'];
						$tmp['dir']       = basename($path);
						$tmp['filesize']  = $v['filesize'];
						$tmp['date']      = $v['date'];
						// fake
						$tmp['extension'] = '';
						$tmp['read']      = true;
						$tmp['write']     = true;
						$ar[] = $tmp;
					}
				}
			}
		}
		return $ar;
	}

	//-------------------------------------------------------	
	/**
	 * read directory and return an array of folderinfos
	 *
	 * @param string $path
	 * @param array $excludes files not to be returned
	 * @return array
	 */
	//-------------------------------------------------------
	function get_folders($path, $excludes = '', $pattern='*', $subpattern = null) {
		$this->connect();
		$ar = array();
		if($excludes != '') {
			$this->arExcludedFiles = array_merge($this->arExcludedFiles, $excludes);
		}
		$rawlist = $this->rawlist_dump($path);
		if(is_array($rawlist)) {
			foreach ($rawlist as $k => $v) {
				$file = $path.'/'.$v['name'];
				if($v['chmod']{0} === 'd') {
					if (in_array($file, $this->arExcludedFiles) === false){
						$tmp['path']        = $file;
						$tmp['name']        = $v['name'];
						$tmp['date']        = $v['date'];
						// fake
						$tmp['permissions'] = '';
						$tmp['read']        = true;
						$tmp['write']       = true;
						$ar[] = $tmp;
					}
				}
			}
		}
		return $ar;
	}

	//-------------------------------------------------------	
	/**
	 * Check file is writable
	 *
	 * @param array $path
	 * @return bool
	 * @todo find a way to find out is_writable for ftp
	 */
	//-------------------------------------------------------
	function is_writeable($path) {
		return true;
	}

	//-------------------------------------------------------	
	/**
	 * Check file is dir
	 *
	 * @param array $path
	 * @return bool
	 */
	//-------------------------------------------------------
	function is_dir($path) {
		$this->connect();
		$this->sock_write('CWD '.$path);
		if(substr($this->message, 0, 3) === '250') { return true; }
		if(substr($this->message, 0, 3) === '550') { return false; }
	}

	//-------------------------------------------------------	
	/**
	 * Quit connection
	 *
	 * @access public
	 */
	//-------------------------------------------------------
	function quit() {
		$this->connect();
		$this->sock_write('QUIT');
		fclose($this->sock);
	}


}

?>
