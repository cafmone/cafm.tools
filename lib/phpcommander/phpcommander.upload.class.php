<?php
/**
 * PHPCommander Upload
 *
 * @package phpcommander
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2015, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class phpcommander_upload
{
/**
* filetype to accept
* @access public
* @var string
*/
var $accept = '';
/**
*  allow to create folder if missing
*  @access public
*  @var bool
*/
var $allow_create = false;
/**
*  allow to replace file
*  @access public
*  @var bool
*/
var $allow_replace = false;
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'commander_upload_action';
/**
*  name for uploaded file
*  @access public
*  @var string
*/
var $filename;
/**
* translation
* @access public
* @var array
*/
var $lang = array(
	'upload' => 'Upload',
	'uploaded_file' => 'File %s successfully uploaded',
	'uploaded_files' => 'File(s) successfully uploaded',
	'error_max_files' => 'An error has occurred. The server can upload %s files at the same time only. Proceed anyway?',
	'error_max_filesize' => 'File exceeds maximum filesize %s ',
	'title_upload' => 'Maximum filesize %s',
	'title_upload_multiple' => 'Maximum %s files, filesize %s',
);
/**
* message param
* @access public
* @var string
*/
var $message_param = 'commander_upload_msg';
/**
* allow multiple fileupload
* @access public
* @var string
*/
var $multiple = false;
/**
* template dir
* @access public
* @var array
*/
var $tpldir;
	
	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param string $path (to upload to)
	 * @param object $response
	 * @param string $file
	 */
	//--------------------------------------------
	function __construct($path, $response, $file) {
		$this->__pc   = $response;
		$this->__file = $file;
		$this->path   = $path;
		$this->ident  = $this->__pc->html->request()->get($this->__pc->id.'[ident]');
	}

	//--------------------------------------------
	/**
	 * Get template
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function get_template() {

		$ident = $this->ident;
		$response = $this->get_response();
		$form = $response->form;
		
		// handle file exceeds MAX_FILE_SIZE
		if($response->html->request()->get('ident') !== '' && $ident === '') {
			$url = $response->get_url(
				'',
				'',
				$this->message_param.'[error]',
				sprintf($this->lang['error_max_filesize'], ini_get('upload_max_filesize'))
			).'&'.$this->__pc->id.'['.$this->__pc->actions_name.']'.'=select';
			$response->redirect($url);
		}

		// remove ident from params else endless loop
		unset($this->__pc->params[$this->__pc->id]['ident']);
		if(!$form->get_errors() && $response->submit()) {
			if($ident !== '') {
				$this->upload($ident);
			}
		}
		else if($form->get_errors()) {
			$_REQUEST[$this->message_param] = join('<br>', $form->get_errors());
		}

		$elementid = uniqid('up');

		$nu        = $this->__pc->html->input();
		$nu->type  = 'file';
		$nu->id    = $elementid;
		$nu->name  = $elementid;
		$nu->css   = 'form-control Filedata';
		$nu->style = 'height:100%;cursor:pointer;';
		$nu->size  = '1';

		// handle filetype accept
		if(isset($this->accept) && $this->accept !== '') {
			$nu->customattribs = 'accept="'.$this->accept.'"';
		}
		// handle multiple fileupload
		if(isset($this->multiple) && $this->multiple === true) {
			$nu->name .= '[]';
			$nu->customattribs .= ' multiple';
			$nu->title = sprintf($this->lang['title_upload_multiple'],ini_get('max_file_uploads'), ini_get('upload_max_filesize'));
		} else {
			$nu->title = sprintf($this->lang['title_upload'], ini_get('upload_max_filesize'));
		}

		$id        = $this->__pc->html->input();
		$id->type  = 'hidden';
		$id->id    = uniqid('up');
		$id->name  = $this->__pc->id.'[ident]';
		$id->value = $elementid;

		$ffid      = uniqid('ff');

		$ff        = $this->__pc->html->input();
		$ff->type  = 'submit';
		$ff->css   = 'form-control fakefile';
		$ff->name  = $ffid;
		$ff->id    = $ffid;
		$ff->value = $this->lang['upload'];
		$ff->title = 'max. '.ini_get('upload_max_filesize');

		if($this->__file->exists($this->path)) {
			if(!$this->__file->is_writeable($this->path)) {
				$nu->disabled = true;
				$nu->style = 'height:100%;cursor:not-allowed;';
				$ff->disabled = true;
				$nu->title = sprintf($this->__file->lang['not_writeable'],basename($this->path));
			}
		} else {
			if($this->__file->exists(dirname($this->path)) && $this->allow_create === true) {
				// do nothing
			} else {
				$nu->disabled = true;
				$nu->style = 'height:100%;cursor:not-allowed;';
				$ff->disabled = true;
				$nu->title = sprintf($this->__file->lang['not_found'],basename($this->path));
			}
		}

		$uploadform = $form->get_elements();
		$url        = $this->__pc->get_url($this->actions_name, 'upload').'&ident='.$elementid;
		$formid     = uniqid('form');
		$boxid      = uniqid('box');

		$script  = 'document.getElementById("'.$elementid.'").onchange = function(){'."\n";
		// handle multiple fileupload
		if(isset($this->multiple) && $this->multiple === true) {
			$max = (int) ini_get('max_file_uploads');
			$script .= '    var max='.$max.';'."\n";
			$script .= '    var $fileUpload = $("#'.$elementid.'");'."\n";
			$script .= '    if (parseInt($fileUpload.get(0).files.length)>max) {'."\n";
			$script .= '        x = confirm("'.sprintf($this->lang['error_max_files'], $max).'");'."\n";
			$script .= '        if(x != true) {'."\n";
			$script .= '            return;'."\n";
			$script .= '       }'."\n";
			$script .= '    }'."\n";
		}
		$script .= '    document.getElementById("'.$formid.'").submit();'."\n";
		$script .= '};'."\n";
		$script .= 'document.getElementById("'.$boxid.'").style.position = "relative";'."\n";
		$script .= 'document.getElementById("'.$elementid.'").style.position = "absolute";'."\n";
		$script .= 'document.getElementById("'.$elementid.'").style.top = 0;'."\n";
		$script .= 'document.getElementById("'.$elementid.'").style.left = 0;'."\n";
		$script .= 'document.getElementById("'.$elementid.'").style.margin = 0;'."\n";
		$script .= 'document.getElementById("'.$elementid.'").style.opacity = 0;'."\n";
		$script .= 'document.getElementById("'.$elementid.'").style.filter = "alpha(opacity=0)";'."\n";
		$script .= 'document.getElementById("'.$elementid.'").style.width = "100%";'."\n";
		$script .= 'document.getElementById("'.$ffid.'").style.width = "100%";'."\n";

		$vars = array(
			'boxid'       => $boxid,
			'ident'       => $id,
			'formid'      => $formid,
			'thisfile'    => htmlentities($url),
			'uploadinput' => $nu,
			'fakeinput'   => $ff,
			'script'      => $script,
		);
		$t = $this->__pc->html->template($this->tpldir.'/phpcommander.upload.html');
		$t->add($vars);
		$t->add($form);
		$t->group_elements(array('param_' => 'uploadform'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * upload
	 *
	 * @access public
	 * @return array
	 */
	//--------------------------------------------
	function upload() {
		require_once(CLASSDIR.'lib/file/file.upload.class.php');
		$upload = new file_upload($this->__file);
		$upload->allow_replace = $this->allow_replace;
		$return = array();
		if($this->ident !== '') {
			$filename = '';
			if(isset($this->filename)) {
				$filename = $this->filename;
			}

			// check folder exists
			if(!$this->__file->exists($this->path)) {
				if($this->__file->exists(dirname($this->path)) && $this->allow_create === true) {
					$error = $this->__file->mkdir($this->path);
					if($error !== '') {
						$_REQUEST[$this->message_param] = $error;
						$doupload = false;
					} else {
						$doupload = true;
					}
				} else {
					$doupload = false;
				}
			} else {
				$doupload = true;
			}

			if($doupload === true) {
				$errors = $upload->upload( $this->ident, $this->path, $filename );
				if($errors === '') {
					if(isset($this->multiple) && $this->multiple === true) {
						$return['status'] = '200';
						$return['msg'] = $this->lang['uploaded_files'];
						$_REQUEST[$this->message_param] = $this->lang['uploaded_files'];
					} else {
						$return['status'] = '200';
						$return['msg'] = sprintf($this->lang['uploaded_file'], $_FILES[$this->ident]['name']);
						$_REQUEST[$this->message_param] = sprintf($this->lang['uploaded_file'], $_FILES[$this->ident]['name']);
					}
				} else {
					// handle errors
					$msg = '';
					if(is_array($errors)) {
						foreach($errors as $error) {
							$msg .= $error['msg'].'<br>';
						}
					}
					$return['status'] = '200';
					$return['msg'] = $msg; 
					$_REQUEST[$this->message_param]['error'] = $msg;
				}
			}

		}
		return $return;
	}

	//--------------------------------------------
	/**
	 * Get response
	 *
	 * @access protected
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function get_response() {
		$response       = $this->__pc;
		$form           = $response->get_form($this->actions_name, 'upload');

		$submit = $form->get_elements('submit');
		$submit->type = 'hidden';
		$form->add($submit,'submit');

		$response->form = $form;
		return $response;
	}
		
}
?>
