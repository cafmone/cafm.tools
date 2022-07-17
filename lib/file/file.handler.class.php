<?php
/**
 * Filehandler
 *
 * @package file
 * @author Alexander Kuballa <akuballa@users.sourceforge.net>
 * @copyright Copyright (c) 2008 - 2017, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class file_handler
{
/**
*  date as formated string
*  use "U" for unix timestamp
*  @access public
*  @var string
*/
var $date_format = "Y-m-d - H:i";
/**
*  file permissions
*  @access public
*  @var string
*/
var $permissions_file = 0666;
/**
*  dir permissions
*  @access public
*  @var string
*/
var $permissions_dir = 0777;
/**
*  define allowed chars for filname
*  @access public
*  @var null|string
*/
var $regex_filename = '[a-zA-Z0-9 ~._()-]';
/**
*  translation for message strings
*  @access public
*  @var array
*/
var $lang = array(
	'remove_error'      => 'failed to delete %s',
	'copy_error'        => 'failed to copy %s',
	'filename_error'    => 'filename must be %s',
	'saved_error'       => 'failed to save %s',
	'create_error'      => 'failed to create %s',
	'exists_error'      => '%s already exists',
	'file'              => 'File',
	'folder'            => 'Folder',
	'permission_denied' => 'Permission denied',
	'not_found'         => 'File %s not found',
	'not_writeable'     => 'Folder %s is not writable',
);

/**
*  files not to be shown
*  @access private
*  @var array
*/
var $arExcludedFiles = array('.', '..');

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 */
	//--------------------------------------------
	function __construct() {
		// solve basename problem
		setlocale(LC_ALL, 'en_US.UTF8');
		// grrr, find Windos 
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			$this->WIN = true;
		}
	}

	//-------------------------------------------------------
	/**
	 * Check file exists
	 *
	 * @param array $path
	 * @return bool
	 */
	//-------------------------------------------------------
	function exists($path) {
		return file_exists($path);
	}

	//-------------------------------------------------------
	/**
	 * Check filename is valid
	 *
	 * @param $path string
	 * @param $replace bool
	 * @return string on error
	 */
	//-------------------------------------------------------
	function check_filename( $path, $replace = false ) {
		$str = '';
		$name = basename($path);
		if(isset($this->regex_filename)) {
			preg_match('/^'.$this->regex_filename.'{'.strlen($name).'}$/u', $name, $matches);
			if(!isset($matches[0])) {
				$str = sprintf($this->lang['filename_error'], $this->regex_filename);
			}
		}
		if($replace === false) {
			if( file_exists($path) && is_file($path) ) {
				$str = sprintf($this->lang['exists_error'], $this->lang['file'].' '.$name);
			}
			if( file_exists($path) && is_dir($path) ) {
				$str = sprintf($this->lang['exists_error'], $this->lang['folder'].' '.$name);
			}
		}
		return $str;
	}

	//-------------------------------------------------------
	/**
	 * Check permissions
	 *
	 * @param $path string
	 * @return string on error
	 */
	//-------------------------------------------------------	 
	function check_permissions( $path ) {
		$str = '';
		$name = basename($path);
		if(!is_readable($path)) {
			$str = $this->lang['permission_denied'];
		}
		return $str;
	}

	//-------------------------------------------------------
	/**
	 * Set file permissions
	 *
	 * @param $path string
	 */
	//-------------------------------------------------------
	function chmod($path) {
		if(is_file($path)) @chmod($path, $this->permissions_file);
		if(is_dir($path))  @chmod($path, $this->permissions_dir);
	}
	
	//-------------------------------------------------------
	/**
	 * Copy a file ($path) to $target
	 *
	 * @param $path string
	 * @param $target string
	 * @param $replace bool
	 * @return string on error
	 */
	//-------------------------------------------------------
	function copy($path, $target, $replace = false) {
		$str = '';
		if($path !== $target) {
			$str = $this->check_filename($target, $replace);
			if($str === '') {
				if(is_dir($path)) {
					$str = $this->mkdir($target);
					if($str === '')  {
						$handle = opendir("$path/.");
						while (false !== ($file = readdir($handle))) {
							if ($file !== '.'  && $file !== '..' ) {
								if(is_dir($path.'/'.$file)) {
									$this->copy($path.'/'.$file, $target.'/'.$file);
								} else {
									if(!@copy($path.'/'.$file, $target.'/'.$file)){
										$str .= sprintf($this->lang['copy_error'], $this->lang['file'].' '.basename($path));
									} else { 
										$this->chmod($target.'/'.$file);
									}
								}
							}
						}
					}
				}
				else {
					if(!@copy($path, $target)) {
						$str .= sprintf($this->lang['copy_error'], $this->lang['file'].' '.$path, $target);
					} else { 
						$this->chmod($target);
					}
				}
			}
		}
		return $str;
	}

	//-------------------------------------------------------
	/**
	 * Get file content as string
	 *
	 * @param $path string
	 * @return string
	 */
	//-------------------------------------------------------
	function get_contents($path) {
		$str = '';
		$error = $this->check_permissions($path);
		if($error === '') {
			$str = file_get_contents($path);
		} else {
			$str = $error;
		}
		return $str;
	}

	//-------------------------------------------------------	
	/**
	 * Return filinfos as array
	 *
	 * @param $path string 
	 * @return array|null
	 */
	//-------------------------------------------------------
	function get_fileinfo($path) {
		if(file_exists($path)) {
			$ar              = array();
			$pi              = pathinfo($path);
			$ar['path']      = $path;
			$ar['name']      = $pi["basename"];
			$ar['dir']       = $pi["dirname"];
			$ar['filesize']  = filesize($path);
			if(isset($this->date_format)) {
				$ar['date'] = date($this->date_format, filemtime ($path));
			} else {
				$ar['date'] = filemtime ($path);
			}
			if(isset($pi["extension"])) {
				$ar['extension'] = strtolower($pi["extension"]);
			} else {
				$ar['extension'] = '';
			}
			$ar['read']  = is_readable($path);
			$ar['write'] = is_writable($path);
			return $ar;
		}
	}
	
	//-------------------------------------------------------
	/**
	 * Read directory and return an array of fileinfos
	 *
	 * @param $path string
	 * @param $excludes array files not to be returned
	 * @return array
	 */
	//-------------------------------------------------------
	function get_files($path, $excludes='', $pattern='*', $subpattern = null) {
		$ar = array();
		if(file_exists($path)) {
			if($excludes != '') {
				$this->arExcludedFiles = array_merge($this->arExcludedFiles, $excludes);
			}
			$glob = glob("$path/$pattern");
			if(isset($subpattern) && $subpattern !== '') {
				$glob = $this->__subpattern($glob, glob("$path/$subpattern", GLOB_BRACE));
			}
			foreach ($glob as $file) {
				if (in_array($file, $this->arExcludedFiles) === false){
					if (is_file("$file") === true) {
						$ar[] = $this->get_fileinfo("$file");
					}
				}
			}
		}
		return $ar;
	}

	//-------------------------------------------------------	
	/**
	 * Return folderinfo as array
	 *
	 * @param $path string 
	 * @return array|null
	 */
	//-------------------------------------------------------
	function get_folderinfo($path) {
		if(file_exists($path)) {
			$ar['path'] = $path;
			$ar['name'] = basename($path);
			if(isset($this->date_format)) {
				$ar['date'] = date($this->date_format, filemtime ($path));
			} else {
				$ar['date'] = filemtime ($path);
			}
			$ar["permissions"] = $this->get_permissions_octal($path);
			if(!isset($this->WIN)) {
				$ar['read']  = is_executable($path);
				$ar['write'] = is_writable($path);
			} else {
				$ar['read']  = true;
				$ar['write'] = true;
			}
			return $ar;
		}
	}

	//-------------------------------------------------------
	/**
	 * Read directory and return an array of folderinfos
	 *
	 * @param string $path
	 * @param array $excludes files not to be returned
	 * @return array
	 */
	//-------------------------------------------------------
	function get_folders($path, $excludes = '', $pattern='*', $subpattern = null) {
		$ar = array();
		if(file_exists($path)) {
			if($excludes != '') {
				$this->arExcludedFiles = array_merge($this->arExcludedFiles, $excludes);
			}
			$glob = glob("$path/$pattern", GLOB_ONLYDIR);
			if(isset($subpattern) && $subpattern !== '') {
				$glob = $this->__subpattern($glob, glob("$path/$subpattern", GLOB_ONLYDIR|GLOB_BRACE));
			}
			foreach ($glob as $file) {
				if (in_array($file, $this->arExcludedFiles) === false){
					$ar[] = $this->get_folderinfo("$file");
				}
			}
			/*
			$handle = opendir ("$path/.");
			while (false !== ($file = readdir ($handle))) {
				if (in_array($file, $this->arExcludedFiles) === false){
					if (is_dir("$path/$file") === true) {
						$ar[] = $this->get_folderinfo("$path/$file");
					}		
				}	
			}
			*/
		}
		return $ar;
	}

	//-------------------------------------------------------
	/**
	 * Read an ini file ($path) and return an array
	 *
	 * @param $path string
	 * @param $multidimensional bool
	 * @param $unescape bool
	 * @return array | null
	 */
	//-------------------------------------------------------
	function get_ini($path, $multidimensional = true, $unescape = true) {
		if(file_exists($path)) {
			$ar = parse_ini_file($path, $multidimensional);
			if(is_array($ar) && $unescape === true) {
				foreach($ar as $key => $value) {
					if(is_array($value)) {
						foreach($value as $k => $v) {
							$ar[$key][$k] = str_replace('&#34;', '"', $v);
						}
					} else {
						$ar[$key] = str_replace('&#34;', '"', $value);
					}
				}
			}
			return $ar;
		} 
	}

	//-------------------------------------------------------
	/**
	 * Octal filepermissions
	 *
	 * @param $path string
	 * @return string
	 */
	//-------------------------------------------------------
	function get_permissions_octal($path) {
		$info = substr(sprintf('%o', fileperms($path)), -4);
		return $info;
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
		return is_dir($path);
	}

	//-------------------------------------------------------
	/**
	 * Check file is writable
	 *
	 * @param array $path
	 * @return bool
	 */
	//-------------------------------------------------------
	function is_writeable($path) {
		return is_writable($path);
	}

	//-------------------------------------------------------
	/**
	 * Create an ini file ($path) from an array
	 *
	 * " will be saved as &#34;
	 *
	 * @param $path string
	 * @param $data array
	 * @param $extension string fileextension
	 * @return string
	 * @todo ereg
	 */
	//-------------------------------------------------------
	function make_ini($path, $data, $extension = '.ini') {
		$str = '';
		if($extension) {
			preg_match('/'.$extension.'$/i', $path, $matches);
			if(count($matches) === 0) {
				$path = $path.$extension;
			}
		}
		if(is_array($data)) {
			$fp = @fopen($path, 'w+');
			if($fp) {
				foreach($data as $key => $value) {
					if(!is_array($value)) {
						fwrite($fp, trim($key).' = "'.str_replace('"', '&#34;', $value)."\"\n");
					} else {
						fwrite($fp, '['.trim($key).']'."\n");
						foreach($value as $subkey => $subvalue) {
							fwrite($fp, trim($subkey).' = "'.str_replace('"', '&#34;', $subvalue)."\"\n");
						}
					}
				}
				fclose($fp);
				$this->chmod($path);
	 		} else {
				$str = sprintf($this->lang['saved_error'], $this->lang['file'].' '.basename($path));
			}
		} else {
			$str = 'Error: make_ini data must be of type array!';
		}
		return $str;
	}
	
	//-------------------------------------------------------
	/**
	 * Make a directory ($path) 
	 *
	 * @param $path string
	 * @return string on error
	 */
	//-------------------------------------------------------
	function mkdir($path) {
		$str = '';
		$str = $this->check_filename($path);
		if($str === '')  {
			if(@mkdir($path) === false) {
				$str = sprintf($this->lang['create_error'], $this->lang['folder'].' '.basename($path));
			} else {
				$this->chmod($path);
			}
		}
		return $str;
	}

	//-------------------------------------------------------
	/**
	 * Make a file
	 *
	 * @param $path string
	 * @param $data string
	 * @param $mode for more details refere to php fopen
	 * @param $replace bool
	 * @return string on error
	 */
	//-------------------------------------------------------
	function mkfile($path, $data, $mode = 'w+', $replace = false) {
		$str = '';
		$str = $this->check_filename($path, $replace);
		if($str === '') {
			$fp = @fopen($path, $mode);
			if($fp) {
				if(fwrite($fp, $data) === false) {
					$str = sprintf($this->lang['saved_error'], $this->lang['file'].' '.basename($path));
				} else {
					$this->chmod($path);
				}
				fclose($fp);
			} else {
				$str = sprintf($this->lang['saved_error'], $this->lang['file'].' '.basename($path)).': '.$this->lang['permission_denied'];
			}		
		}
		return $str;
	}

	//-------------------------------------------------------
	/**
	 * Move a file ($path) to $target
	 *
	 * alias for rename
	 *
	 * @param $path string
	 * @param $target string
	 * @return string
	 */
	//-------------------------------------------------------
	function move($path, $target) {
		$str = $this->rename($path, $target);
		return $str;
	}

	//-------------------------------------------------------
	/**
	 * Rename a file or folder ($path) to $target
	 *
	 * @param $path string
	 * @param $target string
	 * @return string on error
	 * @todo find out why second realpath returns empty
	 */
	//-------------------------------------------------------
	function rename($path, $target) {
		$str = '';
		$rpath = realpath($path);
		if(isset($rpath) && $rpath !== '' && file_exists($rpath)) {
			if($rpath !== $target) {
				$str = $this->check_filename($target);
				if($str === '')  {
					if(@rename($rpath, $target) === false){
						$str .= sprintf($this->lang['copy_error'], $this->lang['file'].' '.basename($rpath));
					}
					else { 
						$this->chmod($target);
					}
				}
			}
		} else {
			$str .= sprintf($this->lang['not_found'], $path);
		}
		return $str;
	}

	//-------------------------------------------------------
	/**
	 * Delete a file ($path) 
	 *
	 * @param $path string
	 * @param $recursive bool
	 * @return string on error
	 */
	//-------------------------------------------------------
	function remove($path, $recursive = false) {
		$ar = array();
		if(is_file($path)) {
			if(@unlink($path) === false) $ar[] = sprintf($this->lang['remove_error'], $this->lang['file'].' '.basename($path));
		}
		if(is_dir($path)) {
			if($recursive === false) {
				if(@rmdir($path) === false) $ar[] = sprintf($this->lang['remove_error'], $this->lang['folder'].' '.basename($path));
			}
			if($recursive === true) {
				$scan = glob(rtrim($path,'/').'/*');
		 		foreach($scan as $file){
				$error = $this->remove($file, $recursive);
					if($error !== '') { $ar[] = $error; }
		 		}
				if(@rmdir($path) === false) $ar[] = sprintf($this->lang['remove_error'], $this->lang['folder'].' '.basename($path));
			}
		}
		$str = join('<br>', $ar);
		return $str;
	}

	//-------------------------------------------------------
	/**
	 * Remove Byte Object Mark
	 *
	 * @param $text string
	 * @return strings
	 */
	//-------------------------------------------------------
	function remove_utf8_bom($text)
	{
		$bom = pack('H*','EFBBBF');
		$text = preg_replace("/^$bom/", '', $text);
		return $text;
	}

	//-------------------------------------------------------
	/**
	 * symlink a file ($path) to $target
	 *
	 * @param $path string
	 * @param $target string
	 * @return string on error
	 */
	//-------------------------------------------------------
	function symlink($path, $target) {
		$str = '';
		if($path !== $target) {
			$name = basename($target);
			if( file_exists($target) && is_file($target) ) {
				$str = sprintf($this->lang['exists_error'], $this->lang['file'].' '.$name);
			}
			else if( file_exists($target) && is_dir($target) ) {
				$str = sprintf($this->lang['exists_error'], $this->lang['folder'].' '.$name);
			}
			else if( !file_exists($path) ) {
				$str = sprintf($this->lang['not_found'], $this->lang['file'].' '.basename($path));
			} else {
				@symlink( $path, $target);
			}
		}
		return $str;
	}

	//-------------------------------------------------------	
	/**
	 * Synchronize pattern and subpattern
	 *
	 * @param array $orig original glob
	 * @param array $sub sub glob
	 * @return array
	 */
	//-------------------------------------------------------
	function __subpattern($orig, $sub) {
		$return = array();
		foreach($sub as $value) {
			if(in_array($value, $orig)) {
				$return[] = $value;
			}
		}
		return $return;
	}
	
}
?>
