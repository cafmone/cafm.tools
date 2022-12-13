<?php
/**
 * tasks_config_form
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2022, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class tasks_config_form
{
/**
* name for selected values
* @access public
* @var string
*/
var $identifier_name = 'element_id';
/**
* name of message param
* @access public
* @var string
*/
var $message_param = 'Msg';
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'form_action';
/**
* translation
* @access public
* @var string
*/
var $lang = array(
	'label' => 'Options',
	'label_sort' => 'Sort Options',
	'label_delete' => 'Delete Option(s)',
	'label_insert' => 'New Option',
	'label_update' => 'Update Option',
	'label_subject' => 'Subject',
	'table_rank' => 'Rank',
	'table_id' => 'id',
	'table_name' => 'Option',
	'delete' => 'delete',
	'sort' => 'Sort options',
	'new' => 'New option',
	'update' => 'update',	
	'saved_option' => 'Option %s has been saved',
	'saved_group' => 'Groups have been saved',
	'deleted' => 'Option %s has been deleted',
	'sorted' => 'Option have been sorted',
	'noscript' => 'Error: JavaScript must be activated for this page',
	'groups' => 'Groups'
);
/**
* path to templates dir

* @access public
* @var string
*/
var $tpldir;

	
	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param array|string $root
	 * @param phpcommander $phpcommander
	 */
	//--------------------------------------------
	function __construct( $controller ) {
		$this->controller = $controller;
		$this->file       = $controller->file;
		$this->response   = $controller->response->response();
		$this->db         = $controller->db;
		$this->user       = $controller->user;
		$this->settings   = $controller->settings;
		$this->lang       = $this->user->translate($this->lang, CLASSDIR.'plugins/tasks/lang/', 'tasks.config.form.ini');
	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 * @param string $action
	 * @return htmlobject_tabmenu
	 */
	//--------------------------------------------
	function action($action = null) {
		$this->action = '';
		$ar = $this->response->html->request()->get($this->actions_name);
		if($ar !== '') {
			$this->action = $ar;
		} 
		else if(isset($action)) {
			$this->action = $action;
		}
		if($this->action === '') {
			$this->action = 'subject';
		}

		$this->response->add($this->actions_name, $this->action);
		$c = array();
		$s = $this->settings;
		if(isset($s['form'])) {
			$elements = $s['form'];
			unset($elements['supporter']);
			unset($elements['reporter']);
			if(is_array($elements)) {
				if($this->action === '') {
					$this->action = key($elements);
				}
				foreach($elements as $k => $v) {
					if($k !== 'group') {
						$c[] = ($this->action === $k) ? $this->element($k, false) : $this->element($k, true);
					}
					else if($k === 'group') {
						$c[] = ($this->action === $k) ? $this->group(false) : $this->group(true);
					}
				}
			}
		}

		if($this->action === 'subject') {
			$content = $this->element('subject', false, $this->lang['label_subject']);
			array_unshift($c, $content);
		} else {
			$content['label']   = $this->lang['label_subject'];
			$content['value']   = '';
			$content['target']  = $this->response->html->thisfile;
			$content['request'] = $this->response->get_array($this->actions_name, 'subject' );
			$content['onclick'] = false;
			array_unshift($c, $content);
		}

		$tab = $this->response->html->tabmenu('tasks_elements');
		$tab->message_param = $this->message_param;
		$tab->css = 'htmlobject_tabs';
		$tab->add($c);
		return $tab;
	}

	//--------------------------------------------
	/**
	 * Element
	 *
	 * @access public
	 * @return array htmlobject_tabs
	 */
	//--------------------------------------------
	function element( $element, $hidden = true, $label = null) {
		$data = '';
		if( $hidden === false ) {
			require_once(CLASSDIR.'plugins/tasks/class/tasks.config.element.class.php');
			$this->action = $element;
			$controller = new tasks_config_element( $this->response, $this->db, $element);
			$controller->actions_name = 'elements_action';
			$controller->message_param = $this->message_param;
			$controller->tpldir = $this->tpldir;
			$controller->lang = $this->lang;
			$data = $controller->action();
		}
		if(!isset($label)) {
			$label = $element;
		}
		if(isset($this->settings['labels'][$element])) {
			$label = $this->settings['labels'][$element];
		}
		$content['label']   = $label;
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, $element );
		$content['onclick'] = false;
		if($this->action === $element){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Group Element
	 *
	 * @access public
	 * @return array htmlobject_tabs
	 */
	//--------------------------------------------
	function group( $hidden = true ) {
		$t = '';
		if( $hidden === false ) {
			$response = $this->response;
			$form     = $response->get_form($this->actions_name, 'group');
			
			// EXISTING GROUPS
			$groups = array();
			$result = $this->db->select('tasks_form', array('option', 'id'), array('element', 'group'));
			if(is_array($result)) {
				foreach($result as $v) {
					$groups[$v['id']] = $v['option'];
				}
			}
			$d['element'] = '';
			if($this->user instanceof user) {
				$d['element'] = array();
				$result = $this->user->query->select('groups', array('group'), null, array('rank'));
				if(is_array($result)) {
					foreach($result as $v) {
						$group[] = array($v['group']);
					}
					$d['element']['label']                        = $this->lang['groups'];
					$d['element']['required']                     = true;
					$d['element']['object']['type']               = 'htmlobject_select';
					$d['element']['object']['attrib']['name']     = 'group[]';
					$d['element']['object']['attrib']['css']      = 'users2groups';
					$d['element']['object']['attrib']['index']    = array(0,0);
					$d['element']['object']['attrib']['multiple'] = true;
					$d['element']['object']['attrib']['options']  = $group;
					$d['element']['object']['attrib']['id']       = 'group_select';
					if(is_array($groups)) {
						$d['element']['object']['attrib']['selected'] = $groups;
					}
				}
			}
			$form->add($d);
			if(!$form->get_errors() && $response->submit()) {
				$ini    = $form->get_request();
				$ini    = $ini['group'];
				$errors = array();
				foreach($groups as $id => $option) {
					$error = '';
					if(in_array($option, $ini)) {
						$rank = array_keys($ini, $option);
						$rank = array_shift($rank);
						unset($ini[$rank]);
						$error = $this->db->update('tasks_form', array('rank' => $rank, ), array('id', $id));
					}
					else if(!in_array($option, $ini)) {
						$error = $this->db->delete('tasks_form', array('id', $id));
					}
					if($error !== '') {
						$errors[] = $error;
					}
				}
				foreach($ini as $k => $v) {
					$error = $this->db->insert('tasks_form', array('rank' => $k, 'option' => $v, 'element' => 'group' ));
					if($error !== '') {
						$errors[] = $error;
					}
				}
				if(count($errors) === 0) {
					$response->msg = $this->lang['saved_group'];
				} else {
					$response->error = implode('<br>', $errors);
				}
			}
			else if($form->get_errors()) {
				$response->error = implode('<br>', $form->get_errors());
			}
			$form->display_errors = false;
			$response->form = $form;

			if(isset($response->error)) {
				$_REQUEST[$this->message_param]['error'] = $response->error;
			}
			if(isset($response->msg)) {
				$this->response->redirect(
					$this->response->get_url(
						$this->actions_name, 'group', $this->message_param, $response->msg
					)
				);
			}
			$vars = array(
				'thisfile' => $response->html->thisfile,
			);
			$t = $response->html->template($this->tpldir.'tasks.config.form.html');
			$t->add($vars);
			$t->add($response->form);
			$t->group_elements(array('param_' => 'form'));
		}

		$content['label']   = $this->lang['groups'];
		$content['value']   = $t;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'group' );
		$content['onclick'] = false;
		if($this->action === 'group'){
			$content['active']  = true;
		}
		return $content;
	}

}
?>
