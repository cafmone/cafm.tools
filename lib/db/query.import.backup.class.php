<?php
/**
 * query_import_backup
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2015 - 2018, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class query_import_backup
{
/**
* name of action buttons
* @access public
* @var string
*/
#var $actions_name = 'bestand_import_action';

var $lang = array();

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param phppublisher $phppublisher
	 * @param htmlobject_response $response
	 */
	//--------------------------------------------
	function __construct($controller) {
		$this->db         = $controller->db;
		$this->file       = $controller->file;
		$this->response   = $controller->response;
		$this->controller = $controller;
		$this->dbtable    = $controller->dbtable;
		$this->columns     = $this->controller->controller->__get_columns($this->dbtable);
		$this->import_file = PROFILESDIR.'/import/backup.gz';

		$this->delimiter = $this->response->html->request()->get('delimiter');
		if($this->delimiter !== '') {
			$this->response->add('delimiter',$this->delimiter);
		} else {
			$this->delimiter = ';';
		}

		$this->enclosure = $this->response->html->request()->get('enclosure');
		if($this->enclosure !== '') {
			$this->response->add('enclosure',$this->enclosure);
		} else {
			$this->enclosure = "'";
		}
	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 * @param string $action
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function action($action = null) {

		#if(is_array($this->columns)) {
			require_once(CLASSDIR.'/lib/phpcommander/phpcommander.upload.class.php');
			$xresponse = $this->response->response();
			$xresponse->id = 'xresponse';
			$xresponse->add($this->actions_name,'backup');
			$commander = new phpcommander_upload(PROFILESDIR.'/import', $xresponse, $this->file);
			$commander->actions_name = $this->dbtable.'_upload';
			$commander->message_param = 'upload_message';
			$commander->tpldir = CLASSDIR.'/lib/phpcommander/templates';
			$commander->allow_replace = true;
			$commander->allow_create = true;
			$commander->accept = '.gz';
			$commander->filename = basename($this->import_file);
			$upload = $commander->get_template();

			// redirect when upload message is set
			$xmsg = $this->response->html->request()->get('upload_message', true);
			if(isset($xmsg)) {
				$this->response->redirect(
					$this->response->get_url(
						$this->actions_name, 'backup', $this->message_param, $xmsg
					)
				);
			}

			// handle import
			if($this->response->html->request()->get('import') === 'true') {

### TODO check content size -> compare to max_allowed_packet

				$response = $this->get_response();
				$content  = $this->check_import(false);
				$error    = $this->db->handler()->query($content, true);
				if(isset($error)) {
					if(strpos($error, "\n") !== false) {
						$error = substr( $error, 0 , strpos($error, "\n") );
					}
					$response->error = $error;
				} else {
					$this->file->remove($this->import_file);
					$response->msg = 'Backup successfully imported';
				}
			} else {
				$content = $this->check_import(true);
				$response = $this->get_response();
			}
	
			if(!isset($response->msg)) {
				if(isset($response->error)) {
					$_REQUEST[$this->message_param]['error'] = $response->error;
				}
				$t = $this->response->html->template($this->tpldir.'/query.import.backup.html');
				$t->add($this->response->html->thisfile, 'thisfile');
				$t->add($response->form);
				if(is_object($upload)) {
					$t->add($upload, 'upload');
				}
				else if (is_array($upload)) {
					print_r($upload);
				}

				if(is_string($content) && $content !== '') {
					$t->add('<pre style="height:400px;white-space: pre-wrap;">'.$content.'</pre>', 'content');
					$a = $response->html->a();
					$a->css = 'btn btn-default';
					$a->label = 'Import';
					$a->href  = $this->response->get_url($this->actions_name, 'backup').'&import=true';
					$a->handler = 'onclick="phppublisher.wait();"';
					$t->add($a, 'import');
				} else {
					$t->add('', 'content');
					$t->add('', 'import');
				} 
				$t->group_elements(array('param_' => 'form'));
				return $t;
			} else {
				$this->response->redirect(
					$this->response->get_url(
						$this->actions_name, 'backup', $this->message_param, $response->msg
					)
				);
			}
		#}
		#else if(is_string($this->columns)) {
		#	return $this->columns;
		#}
	}

	//--------------------------------------------
	/**
	 * get response
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function get_response() {
		$response = $this->response;
		$form     = $response->get_form($this->actions_name, 'backup', false);
		$d        = array();

		/*
		$d['enclosure']['label']                         = 'Enclosure';
		$d['enclosure']['css']                           = 'autosize';
		$d['enclosure']['style']                         = 'float:right;';
		$d['enclosure']['object']['type']                = 'htmlobject_input';
		$d['enclosure']['object']['attrib']['style']     = 'width: 50px;';
		$d['enclosure']['object']['attrib']['name']      = 'enclosure';
		$d['enclosure']['object']['attrib']['id']        = 'enclosure';
		$d['enclosure']['object']['attrib']['value']     = htmlentities($this->enclosure);
		$d['enclosure']['object']['attrib']['maxlength'] = 5;
		*/

		$form->add($d);
		$form->display_errors = false;
		$response->form = $form;

		return $response;
	}

	//--------------------------------------------
	/**
	 * check import
	 *
	 * @access public
	 * @return string
	 */
	//--------------------------------------------
	function check_import($trim = true) {
		$output = '';
		$error  = '';
		if($this->file->exists($this->import_file)) {
			if(($fp = fopen($this->import_file, 'r')) !== FALSE) {
				if(@fread($fp, 2) == "\x1F\x8B") {
					fclose($fp);
					if(($zp = gzopen($this->import_file, 'r')) !== FALSE) {
						 while (!feof($zp)) {
							$output .= gzread($zp, 100000);
							if($trim === true && !feof($zp)) {
								$output .= "\n...";
								break;
							}
						}
						fclose($zp);
					}
				} else {
					fclose($fp);
					$error = 'ERROR: Not a gzip file';
				}
			} else {
				$error = 'ERROR: Could not open file for reading';
			}
		} else {
			#$error = 'No file uploaded yet';
		}
		if($error !== '') {
			$_REQUEST[$this->message_param] = $error;
		}
		return $output;
	}

}
?>
