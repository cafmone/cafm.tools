<?php
/**
 * PHPCommander Delete
 *
 * @package phpcommander
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2010, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class phpcommander_delete
{

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param string $path path to file
	 * @param object $response
	 * @param object $file
	 */
	//--------------------------------------------
	function __construct($path, $response, $file) {
		$this->__path = $path;
		$this->__pc   = $response;
		$this->__file = $file;
	}

	//--------------------------------------------
	/**
	 * Delete
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function action() {
		$msg = array();
		$response = $this->get_response();
		$form     = $response->form;
		$files    = $response->html->request()->get($response->identifier);
		if($files !== '') {
			if(!$form->get_errors() && $response->submit()) {
				foreach($files as $key => $file) {
					if($file !== '') {
						$path  = $this->__path.'/'.$file;
						$error = $this->__file->remove($path, true);
						if($error !== '') {
							$form->set_error($response->identifier."[$key]", $error);
						} else {
							$form->remove($response->identifier.'['.$key.']');
							$msg[] = sprintf($response->lang['file']['lang_deleted'], $file);
						}
					}
					if(!$form->get_errors()) {
						$response->msg = $msg;
					} else {
						$response->error = $form->get_errors() + $msg;
					}
				}
			}
			else if($form->get_errors()) {
				$response->error = $form->get_errors();
			}
		}
		else if($files === '' && !$form->get_errors()) {
			$response->msg = '';
		}
		return $response;
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

		$response = $this->__pc;
		$form     = $response->get_form($response->actions_name, 'delete');
		$files    = $response->html->request()->get($response->identifier);
		if( $files !== '' ) {
			$i = 0;
			if(!is_array($files)){ $files = array($files); }	
			foreach($files as $file) {
				$d['param_f'.$i]['label']                       = $file;
				$d['param_f'.$i]['object']['type']              = 'htmlobject_input';
				$d['param_f'.$i]['object']['attrib']['type']    = 'checkbox';
				$d['param_f'.$i]['object']['attrib']['name']    = $response->identifier.'['.$i.']';
				$d['param_f'.$i]['object']['attrib']['id']      = $response->identifier.'_'.$i;
				$d['param_f'.$i]['object']['attrib']['value']   = $file;
				$d['param_f'.$i]['object']['attrib']['checked'] = true;
				++$i;
			}
			$form->add($d);
		}
		$response->form = $form;
		return $response;
	}

}
?>
