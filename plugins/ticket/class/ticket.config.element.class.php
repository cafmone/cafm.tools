<?php
/**
 * ticket_config_element
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2012, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class ticket_config_element
{
/**
* name for selected values
* @access public
* @var string
*/
var $identifier_name = 'platform_id';
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
var $actions_name = 'elements_action';
/**
* name of action buttons
* @access public
* @var string
*/
var $lang = array();
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
	function __construct( $response, $db, $element ) {
		$this->response = $response;
		$this->element  = $element;
		$this->db       = $db;
		$this->db_table = 'ticket_form';
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
		$this->action = '';
		$ar = $this->response->html->request()->get($this->actions_name);
		if($ar !== '') {
			$this->action = $ar;
		} 
		else if(isset($action)) {
			$this->action = $action;
		}
		if($this->response->cancel()) {
			$this->action = 'select';
		}
		if(!isset($this->action)) {
			$this->action = 'select';
		}

		$content   = array();
		switch( $this->action ) {
			case '':
			case 'select':
				$response = $this->select();
				if(!$response->table instanceof htmlobject_div) {
					$data['table'] = $response->table;

					$href        = $response->html->a();
					$href->href  = $response->html->thisfile.$response->get_string($this->actions_name, 'insert', '?', true );
					$href->label = $this->lang['new'];
					$data['new'] = $href;

					$href         = $response->html->a();
					$href->href   = $response->html->thisfile.$response->get_string($this->actions_name, 'sort', '?', true );
					$href->label  = $this->lang['sort'];
					$data['sort'] = $href;
				} else {
					$data['table'] = '';
					$data['new']   = $response->table;
					$data['sort']  = '';
				}
				$vars = array_merge(
					$data, 
					array(
						'thisfile' => $response->html->thisfile,
				));
				$t = $response->html->template($this->tpldir.'ticket.config.element.select.html');
				$t->add($vars);
				$t->add($response->form);
				$t->group_elements(array('param_' => 'form'));
				return $t;
			break;

			case 'insert':
				$response = $this->insert();
				if(isset($response->error)) {
					$_REQUEST[$this->message_param]['error'] = $response->error;
				}
				if(isset($response->msg)) {
					$this->__redirect($response->msg);
				}
				$vars = array(
					'label' => $this->lang['label_insert'],
					'thisfile' => $response->html->thisfile,
				);
				$t = $response->html->template($this->tpldir.'ticket.config.element.insert.html');
				$t->add($vars);
				$t->add($response->form);
				$t->group_elements(array('param_' => 'form'));
				return $t;
			break;

			case 'update':
				$response = $this->update();
				if(isset($response->error)) {
					$_REQUEST[$this->message_param]['error'] = $response->error;
				}
				if(isset($response->msg)) {
					$this->__redirect($response->msg);
				}
				$vars = array(
					'label' => $this->lang['label_update'],
					'thisfile' => $response->html->thisfile,
				);
				$t = $response->html->template($this->tpldir.'ticket.config.element.update.html');
				$t->add($vars);
				$t->add($response->form);
				$t->group_elements(array('param_' => 'form'));
				return $t;
			break;

			case 'sort':
				$response = $this->sort();
				if(isset($response->error)) {
					$_REQUEST[$this->message_param]['error'] = $response->error;
				}
				if(isset($response->msg)) {
					$this->__redirect($response->msg);
				}
				$data['label']    = $this->lang['label_sort'];
				$data['noscript'] = $this->lang['noscript'];
				$vars = array_merge(
					$data, 
					array(
						'thisfile' => $response->html->thisfile,
				));
				$t = $response->html->template($this->tpldir.'ticket.config.element.sort.html');
				$t->add($vars);
				$t->add($response->form);
				$t->group_elements(array('param_' => 'form'));
				return $t;
			break;

			case $this->lang['delete']:
			case 'delete':
				$response = $this->delete();
				if(isset($response->error)) {
					$_REQUEST[$this->message_param]['error'] = $response->error;
				}
				if(isset($response->msg)) {
					$this->__redirect($response->msg);
				}
				$data['label']    = $this->lang['label_delete'];
				$vars = array_merge(
					$data, 
					array(
						'thisfile' => $response->html->thisfile,
				));
				$t = $response->html->template($this->tpldir.'ticket.config.element.delete.html');
				$t->add($vars);
				$t->add($response->form);
				$t->group_elements(array('param_' => 'form'));
				return $t;
			break;
		}
	}

	//--------------------------------------------
	/**
	 * Select
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function select() {
		$response = $this->get_response('select');
		$head['id']['title']      = $this->lang['table_id'];
		$head['id']['sortable']   = false;
		$head['id']['hidden']     = true;
		$head['rank']['title']    = $this->lang['table_rank'];
		$head['rank']['sortable'] = true;
		$head['rank']['style']    = 'width: 90px;';
		$head['name']['title']    = $this->lang['table_name'];
		$head['name']['sortable'] = true;
		$head['action']['title']    = '&#160;';
		$head['action']['sortable'] = false;
		$result = $this->db->select( $this->db_table, '*', array('element', $this->element) );
		if(is_array($result) || $result === '') {
			$count = (is_array($result)) ? count( $result ): 0;
			$body = array();
			if($result !== '') {
				foreach( $result as $f ) {
					$a        = $response->html->a();
					$a->href  = $response->html->thisfile.$response->get_string($this->actions_name, 'update', '?', true ).'&id='.$f['id'];
					$a->label = $this->lang['update'];
					$body[] = array(
						'id' 	=> $f['id'], 
						'rank' 	=> $f['rank'], 
						'name' 	=> $f['option'],
						'action' 	=> $a->get_string()
						);
				}
			}
			$table                      = $response->html->tablebuilder( 'tcc', $response->get_array($this->actions_name, 'select') );
			$table->sort                = 'rank';
			$table->css                 = 'htmlobject_table table table-bordered';
			$table->border              = 0;
			$table->id                  = 'Categorie_table';
			$table->head                = $head;
			$table->body                = $body;
			$table->sort_params         = $response->get_string( $this->actions_name, 'select' );
			$table->sort_form           = false;
			$table->autosort            = true;
			$table->identifier          = 'id';
			$table->identifier_name     = $this->identifier_name;
			$table->actions             = array($this->lang['delete']);
			$table->actions_name        = $this->actions_name;
			$table->max                 = $count;

			$response->table = $table;
		}
		else if(is_string($result)) {
			$div = $this->response->html->div();
			$div->css = 'errormsg';
			$div->add($result);
			$response->table = $div;
		}
		return $response;
	}

	//--------------------------------------------
	/**
	 * Insert
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function insert() {
		$response = $this->get_response('insert');
		$form     = $response->form;
		if(!$form->get_errors() && $response->submit()) {
			$result = $this->db->select( $this->db_table, array('id'), array('element', $this->element) );
			if(isset($result) && is_array($result)) {
				$rank  = count($result);
			} else {
				$rank = 0;
			}
			$name  = $form->get_request('name');
			$error = $this->db->insert($this->db_table, array('element' => $this->element, 'option' => $name, 'rank' => $rank));
			if(!isset($error) || $error === '') {
				$response->msg = sprintf($this->lang['saved_option'], $name);
			} else {
				$response->error = $error;
			}
		} 
		else if($form->get_errors()) {
			$response->error = implode('<br>', $form->get_errors());
		}
		return $response;
	}

	//--------------------------------------------
	/**
	 * Update
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function update() {
		$id = $this->response->html->request()->get('id');
		$this->response->params['id'] = $id;
		if($id !== '') {
			$response = $this->get_response('update', $id);
			$form     = $response->form;
			if(!$form->get_errors() && $response->submit()) {
				$name  = $form->get_request('name');
				$error = $this->db->update($this->db_table, array('option' => $name), array('id', $id));
				if(!isset($error) || $error === '') {
					$response->msg = sprintf($this->lang['saved_option'], $name);
				} else {
					$response->error = $error;
				}
			}
			else if($form->get_errors()) {
				$response->error = implode('<br>', $form->get_errors());
			}
		} else {
			$response->msg = '';
		}
		return $response;
	}

	//--------------------------------------------
	/**
	 * Delete
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function delete() {
		$response   = $this->get_response('delete');
		$elements = $response->html->request()->get($this->identifier_name);
		$form       = $response->form;
		if( $elements !== '' ) {
			$i = 0;
			foreach($elements as $id) {
				$result = $this->db->select( $this->db_table, array('option'), array('id', $id) );
				$name = $result[0]['option'];
				$d['param_f'.$i]['label']                       = $name;
				$d['param_f'.$i]['object']['type']              = 'htmlobject_input';
				$d['param_f'.$i]['object']['attrib']['type']    = 'checkbox';
				$d['param_f'.$i]['object']['attrib']['name']    = $this->identifier_name.'['.$i.']';
				$d['param_f'.$i]['object']['attrib']['value']   = $id;
				$d['param_f'.$i]['object']['attrib']['checked'] = true;
				$i++;
			}
			$form->add($d);
			if(!$form->get_errors() && $response->submit()) {
				$errors  = array();
				$message = array();
				foreach($elements as $key => $id) {
					$error = $this->db->delete( $this->db_table, array('id' ,$id) );
					if($error === '') {
						$form->remove($this->identifier_name.'['.$key.']');
						$message[] = sprintf($this->lang['deleted'], $id);
					} else {
						$errors[] = $error;
					}
				}
				$i = 0;
				$result = $this->db->select( $this->db_table, array('id'), null, array('rank'));
				if(is_array($result)) {
					foreach($result as $v) {
						$error = $this->db->update($this->db_table, array('rank' => $i), array('id', $v['id']) );
						if($error !== '') {
							$errors[] = $error;
						}
						$i++;
					}
				}
				if(count($errors) === 0) {
					$response->msg = join('<br>', $message);
				} else {
					$response->error =  join('<br>', array_merge($errors, $message));
				}
			}
		} else {
			$response->msg = '';
		}
		return $response;
	}

	//--------------------------------------------
	/**
	 * Sort
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function sort( $hidden = false ) {
		$response = $this->get_response('sort');
		$form     = $response->form;
		$result   = $this->db->select($this->db_table, array('id', 'option'), array('element', $this->element), array('`rank`'));
		if(is_array($result)) {
			$d['select']['label']                        = '';
			$d['select']['object']['type']               = 'htmlobject_select';
			$d['select']['object']['attrib']['index']    = array('id', 'option');
			$d['select']['object']['attrib']['id']       = 'picklist';
			$d['select']['object']['attrib']['name']     = 'options[]';
			$d['select']['object']['attrib']['css']      = 'picklist';
			$d['select']['object']['attrib']['options']  = $result;
			$d['select']['object']['attrib']['multiple'] = true;
			$form->add($d);
			if(!$form->get_errors() && $response->submit()) {
				$i = 0;
				foreach($form->get_request('options') as $id) {
					$error = $this->db->update($this->db_table, array('rank' => $i), 'id='.$id);
					$i++;
				}
				if(!isset($error) || $error === '') {
					$response->msg = $this->lang['sorted'];
				} else {
					$response->error = $error;
				}
			}
		} else {
			if($result !==  '') {
				$response->error = $result;
			} else {
				$response->msg = '';
			}
		}
		return $response;
	}

	//--------------------------------------------
	/**
	 * Get Response
	 *
	 * @access public
	 * @param string $mode
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function get_response( $mode, $id = null ) {
		$response = $this->response;
		$form     = $response->get_form($this->actions_name, $mode);
		if( $mode === 'insert' || $mode === 'update') {
			// handle maxlength 
			$columns = $this->db->handler()->columns($this->db->db, $this->db_table);

			$d['name']['label']                         = $this->lang['table_name'];
			$d['name']['required']                      = true;
			$d['name']['object']['type']                = 'htmlobject_input';
			$d['name']['object']['attrib']['name']      = 'name';
			$d['name']['object']['attrib']['type']      = 'text';
			$d['name']['object']['attrib']['value']     = '';
			if(isset($columns['option']['length'])) {
				$d['name']['object']['attrib']['maxlength'] = $columns['option']['length'];
			} else {
				$d['name']['object']['attrib']['maxlength'] = 60;
			}
			if($mode === 'update') {
				$result = $this->db->select($this->db_table, '*', array('id',$id));
				$d['name']['object']['attrib']['value'] = $result[0]['option'];
			}
			$form->add($d);
		}
		$form->display_errors = false;
		$response->form = $form;
		return $response;
	}

	//--------------------------------------------
	/**
	 * Redirect
	 *
	 * @access public
	 * @param string $msg
	 * @param string $mode
	 */
	//--------------------------------------------
	function __redirect( $msg, $mode = 'select' ) {
		$this->response->redirect($this->response->get_url($this->actions_name, $mode, $this->message_param, $msg));
	}

}
?>
