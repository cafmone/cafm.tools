<?php
/**
 * cafm_one_config_groups
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2020, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class cafm_one_config_groups
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name;
/**
* path to templates
* @access public
* @var string
*/
var $tpldir;
/**
* message param
* @access public
* @var string
*/
var $message_param;
/**
* path to ini file
* @access public
* @var string
*/
var $settings;
/**
* translation
* @access public
* @var array
*/
var $lang = array();

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param file $file
	 * @param htmlobject_response $response
	 */
	//--------------------------------------------
	function __construct( $controller ) {
		$this->file     = $controller->file;
		$this->response = $controller->response;
		$this->db       = $controller->db;
		$this->user     = $controller->user;
		$this->settings = $controller->settings;
		$this->ini      = $controller->ini;

		### TODOS
		require_once(CLASSDIR.'plugins/cafm.one/class/cafm.one.class.php');
		$this->taetigkeiten = new cafm_one($this->file, $this->response, $this->db, $this->user);
		$this->tables = $this->taetigkeiten->prefixes(false);
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
		$form = $this->update();

		$vars = array('thisfile' => $this->response->html->thisfile);
		$t = $this->response->html->template($this->tpldir.'cafm.one.config.groups.html');
		$t->add($vars);
		$t->add($form);
		$t->group_elements(array('param_' => 'form'));

		// todos fieldset
		$t->group_elements(array('table_' => 'tables'));
		$elements = $t->get_elements('tables');
		if(isset($elements)) {
			$t->remove('tables');

			$tmp = array();
			foreach($elements as $k => $e) {
				$prefix = str_replace('table_', '', $k);
				if(isset($this->tables[$prefix]['tag']) && $this->tables[$prefix]['tag'] !== '') {
					$tmp[$this->tables[$prefix]['tag']][] = $e;
					$tmp[$this->tables[$prefix]['tag']]['label'] = $this->tables[$prefix]['tag'];
				} else {
					$tmp['zzz_empty_tag'][] = $e;
					$tmp['zzz_empty_tag']['label'] = 'Various';
				}
			}
			sort($tmp);

			$output = $this->response->html->div();
			foreach($tmp as $tag) {
				$label = $tag['label'];
				unset($tag['label']);
				$div = $this->response->html->customtag('div');
				$div->style = 'margin: 0 0 10px 20px;';
				$div->add($tag);
				$output->add('<h4>'.$label.'</h4>');
				$output->add($div);
			}
			$t->add($output, 'tables');
		} else {
			$t->add('', 'tables');
		}

		// handle error
		if(isset($form->error)) {
			if(!isset($elements)) {
				$t->add('<div style="padding:50px;text-align:center;">'.$form->error.'</div>', 'tables');
			} else {
				$_REQUEST[$this->message_param]['error'] = $form->error;
			}
		}

		return $t;
	}

	//--------------------------------------------
	/**
	 * Update
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function update() {
		$form = $this->get_form();
		if(!$form->get_errors() && $this->response->submit()) {
			$error = '';
			$request = $form->get_request();
			if($request === '') {
				$request = array();
			}
			$old = $this->file->get_ini( $this->settings );
			if(is_array($old)) {
				unset($old['disabled']);
				$request = array_merge($old, $request);
			}

			if( $error === '' ) {
				$error = $this->file->make_ini( $this->settings, $request );
				if( $error === '' ) {
					$msg = $this->lang['msg_sucess'];
					$this->response->redirect($this->response->get_url($this->actions_name, 'groups', $this->message_param, $msg));
				} else {
					$form->error = $error;
				}
			} else {
				$form->error = $error;
			}
		} 
		else if($form->get_errors()) {
			$form->error = implode('<br>', $form->get_errors());
		}
		return $form;
	}

	//--------------------------------------------
	/**
	 * Get Form
	 *
	 * @access public
	 * @return htmlobject_form
	 */
	//--------------------------------------------
	function get_form() {
		$ini  = $this->ini;
		$form = $this->response->get_form($this->actions_name, 'groups');
		$d    = array();

		$submit = $form->get_elements('submit');
		$submit->value = $this->lang['button_hide'];
		$form->add($submit, 'submit');

		if(
			isset($ini['login']['url']) && 
			isset($ini['login']['user']) && 
			isset($ini['login']['pass'])
		) {
			if(is_array($this->tables)) {
				foreach($this->tables as $t) {
					$label = $t['lang'];
					#$label = substr($t['lang'], 0, 120);
					#strlen($label) < strlen($t['lang']) ? $label = $label.'...' : null;

					$d['table_'.$t['prefix']]['label']                     = $label;
					$d['table_'.$t['prefix']]['css']                       = 'autosize inverted checkbox';
					$d['table_'.$t['prefix']]['object']['type']            = 'htmlobject_input';
					$d['table_'.$t['prefix']]['object']['attrib']['type']  = 'checkbox';
					$d['table_'.$t['prefix']]['object']['attrib']['name']  = 'disabled['.$t['prefix'].']';
					if(isset($ini['disabled'][$t['prefix']])) {
						$d['table_'.$t['prefix']]['object']['attrib']['checked'] = true;
					}
				}
			} else {
				$form->error = $this->tables;
				$form->add('','submit');
			}
		} else {
			$form->error = $this->lang['error_login_data'];
			$form->add('','submit');
		}

		$form->display_errors = false;
		$form->add($d);
		return $form;
	}

}
?>
