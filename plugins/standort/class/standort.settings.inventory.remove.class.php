<?php
/**
 * standort_settings_inventory_remove
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2020, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class standort_settings_inventory_remove
{
/**
* translation
* @access public
* @var string
*/
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
		$this->controller = $controller;
		$this->user       = $controller->user->get();
		$this->db         = $controller->db;
		$this->file       = $controller->file;
		$this->response   = $controller->response->response();
		$this->settings   = $controller->settings;
		$this->datadir    = $controller->datadir;

		$id = $this->response->html->request()->get('id');
		if($id !== '') {
			$this->id = $id;
			$this->response->add('id', $id);
		}
		$table = $this->response->html->request()->get('standort_select');
		if( $table !== '') {
			$this->response->add('standort_select', $table);
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
	function action() {
		$response = $this->remove();

		if(!isset($response->msg)) {
			if(isset($response->error)) {
				$_REQUEST[$this->message_param]['error'] = $response->error;
			}
			$t = $response->html->template($this->tpldir.'standort.settings.inventory.remove.html');
			$t->add($response->html->thisfile,'thisfile');
			$t->add($GLOBALS['settings']['config']['baseurl'].$GLOBALS['settings']['folders']['css'],'cssurl');
			$t->add($GLOBALS['settings']['config']['baseurl'].$GLOBALS['settings']['folders']['js'],'jsurl');
			$t->add($GLOBALS['settings']['config']['baseurl'],'baseurl');
			$t->add($response->form);
			$t->group_elements(array('param_' => 'form'));
			return $t;
		} else {
			$this->response->redirect(
					$this->response->get_url(
					$this->actions_name, 'select', $this->controller->message_param, $response->msg
				)
			);
		}
	}

	//--------------------------------------------
	/**
	 * Remove
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function remove() {

		$response = $this->get_response();
		$form     = $response->form;
		if(!$form->get_errors() && $response->submit()) {
			$user = $this->controller->user->get();
			if(isset($error) && $error !== '') {
				$response->error = $error;
			} else {
				$error = '';
				$id = $this->response->html->request()->get('id');
				$child = $this->db->select($this->settings['query']['content'], 'id', array('parent_id'=>$id), null, '1');
				if(!isset($child[0]['id'])) {
					if($this->file->exists($this->datadir.$id)) {
						$error = $this->file->remove($this->datadir.$id, true);
					}
					if($error === '') {
						$error = $this->db->delete($this->settings['query']['content'], array('id', $id));
					}
					if($error === '') {
						$response->msg = sprintf($this->lang['msg_remove_success'], $id);
					} else {
						$response->error = $error;
					}
				}
				elseif (isset($child[0]['id'])) {
					$response->error = sprintf($this->lang['error_has_children'], $id);
				}
			}
		}
		else if($form->get_errors()) {
			$response->error = implode('<br>', $form->get_errors());
		}
		return $response;
	}

	//--------------------------------------------
	/**
	 * Get Response
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function get_response() {
		$response = $this->response;
		$form = $response->get_form($this->actions_name, 'remove');
		if(isset($this->id) && $this->id !== '') {
			$div = $this->response->html->div();
			$result = $this->db->select('bestand','id',array('tabelle'=>'SYSTEM','merkmal_kurz'=>'RAUMBUCHID','wert'=>$this->id));
			if(is_array($result)) {
				$div->add(sprintf($this->lang['remove']['not_empty'], count($result)).'<br>');
				$div->add(sprintf($this->lang['confirm_remove'],$this->id));
			} else {
				$div->add(sprintf($this->lang['confirm_remove'],$this->id));
			}
			$d['question']['object'] = $div;
		} else {
			$d['question'] = '';
		}	
		$form->display_errors = false;
		$form->add($d);
		$response->form = $form;
		return $response;
	}

}
?>
