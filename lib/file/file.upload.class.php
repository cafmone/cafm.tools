<?php
/**
 * Fileupload
 *
 * @package file
 * @author Alexander Kuballa <akuballa@users.sourceforge.net>
 * @copyright Copyright (c) 2008 - 2010, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class file_upload
{
/**
*  allow to replace file
*  @access public
*  @var bool
*/
var $allow_replace = false;
/**
*  translation for message strings
*  @access public
*  @var array
*/
var $lang = array(
	'400' => 'Bad Request: Uploadfile not set',
	'401' => 'Unauthorized: Directory is not writeable',
	'403' => 'Forbidden: ',
	'404' => 'Not Found: Directory not exists',
	'406' => 'Not Acceptable: File is not uploded',
	'500' => 'Server Error: ',
	'Server' => array(
		'1' => 'File %s exceeds php.ini upload_max_filesize (%s)',
		'2' => 'File exceeds MAX_FILE_SIZE',
		'3' => 'File was only partially uploaded',
		'4' => 'No file was uploaded',
		'6' => 'Missing a temporary folder',
		'7' => 'Failed to write file to disk',
		'8' => 'File upload stopped by extension',
	),
);

	//---------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param $object file
	 * @access public
	 */
	//---------------------------------------
	function __construct( $obj ) {
		$this->file = $obj;
	}

	//---------------------------------------
	/**
	 * Upload
	 *
	 * @access public
	 * @param $key string
	 * @param $dir string
	 * @param $name string
	 * @return [string|array] empty string on success
	 */
	//---------------------------------------
	function upload( $key, $dir, $name = '' ) {
		$errors = array();
		if(isset( $_FILES[$key]) ) {
			// make everything an array
			$userfiles = array();
			if(isset($_FILES[$key]['name']) && is_array($_FILES[$key]['name'])) {
				foreach($_FILES[$key]['name'] as $i => $f) {
					$userfiles[] = array(
						'name'     => $_FILES[$key]['name'][$i],
						'type'     => $_FILES[$key]['type'][$i],
						'tmp_name' => $_FILES[$key]['tmp_name'][$i],
						'error'    => $_FILES[$key]['error'][$i],
						'size'     => $_FILES[$key]['size'][$i],
					);
				}
			} else {
				$userfiles[] = $_FILES[$key];
			}

			if(is_array($userfiles) && count($userfiles) > 0) {

				foreach($userfiles as $userfile) {
					if( $userfile['error'] === 0 ) {
						if(is_uploaded_file($userfile["tmp_name"])) {
							($name == '' || count($userfiles) > 1) ? $name = $userfile['name'] : null;
							if(is_dir($dir)) {
								$file = $dir."/".$name;
								$error = $this->file->check_filename( $file, $this->allow_replace );
								if( $error === '' ) {
									if(@move_uploaded_file($userfile['tmp_name'],  $file) !== false) {
										$this->file->chmod($file);
										### TODO chmod error

									} else $errors[] = $this->print_error('500', $this->lang['Server'][7]);
								} else $errors[] = $this->print_error('403', $error);
							} else $errors[] = $this->print_error('404');
						} else $errors[] = $this->print_error('406');
					} else {
						switch ($userfile['error']) {
							case 1 : 
 								 $errors[] = $this->print_error('500', sprintf($this->lang['Server'][$userfile['error']], $userfile['name'],ini_get('upload_max_filesize')));
							break;
							default :
								 $errors[] = $this->print_error('500', $this->lang['Server'][$userfile['error']]);
							break;
						}
					}
				}
			}

		} else $errors[] = $this->print_error('400');

		// handle errors
		if(count($errors) > 0) {
			return $errors;
		} else {
			return '';
		}
	}

	//---------------------------------------
	/**
	 * @access protected
	 * @param $key int
	 * @param $error string
	 * @return array
	 */
	//---------------------------------------
	function print_error( $key, $error = '' ) {
		$arr = array(
			'status' => $key,
			'msg'    => $this->lang[$key].$error,
		);
		return $arr;
	}

}
?>
