<?php
/**
 * user_groups
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2012, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class user_groups
{
/**
* name for selected values
* @access public
* @var string
*/
var $identifier_name = 'admin_id';
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
var $actions_name = 'admin_action';
/**
* translation
* @access public
* @var string
*/
var $lang = array(
	"label" => "Groups",
	"new" => "New Group",
	"sort" => "Sort Groups",
	"rank" => "Rank",
	"group" => "Group",
	"users" => "Users",
	"action_update" => "update",
	"label_update" => "Update group %s",
	"label_delete" => "Delete group(s)",
	"users" => "Users",
	"users_title" => "Select on or more users",
	"delete" => "delete",	
	"saved" => "Group %s has been saved",
	"deleted" => "Group %s has been deleted",
	"sorted" => "Groups have been sorted",
	"noscript" => "Error: JavaScript must be activated for this page",
	"error_exists" => "Group %s already exists",
	"error_group" => "Name is not valid. &amp; &lt; &quot are not allowed!"
);
/**
* path to templates dir

* @access public
* @var string
*/
var $tpldir = '';

	
	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param object $response
	 * @param object $query
	 */
	//--------------------------------------------
	function __construct( $response, $query ) {
		$this->settings = PROFILESDIR.'/groups';
		$this->response = $response;
		$this->query    = $query;
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

		$content   = array();
		switch( $this->action ) {
			case '':
			case 'select':
				$response = $this->select();

				$data['table'] = $response->table;

				$href          = $response->html->a();
				$href->css     = 'btn btn-sm btn-default';
				$href->href    = $response->html->thisfile.$response->get_string($this->actions_name, 'insert', '?', true );
				$href->label   = $this->lang['new'];
				$href->handler = 'onclick="phppublisher.wait();"';
				$href->style   = 'margin: 0 3px 0 0;';
				$data['new'] = $href;

				$data['sort'] = '';
				if($response->table->max > 1) {
					$href          = $response->html->a();
					$href->css     = 'btn btn-sm btn-default';
					$href->href    = $response->html->thisfile.$response->get_string($this->actions_name, 'sort', '?', true );
					$href->label   = $this->lang['sort'];
					$href->handler = 'onclick="phppublisher.wait();"';
					$data['sort'] = $href;
				}

				$vars = array_merge(
					$data, 
					array(
						'thisfile' => $response->html->thisfile,
				));
				$t = $response->html->template($this->tpldir.'/user.groups.select.html');
				$t->add($vars);
				$t->add($response->form);
				$t->group_elements(array('param_' => 'form'));
				return $t;
			break;

			case 'insert':
				$response = $this->insert();
				if(isset($response->error)) {
					$_REQUEST[$this->message_param] = $response->error;
				}
				if(isset($response->msg)) {
					$this->__redirect($response->msg);
				}
				$vars = array(
					'label' => $this->lang['new'],
					'thisfile' => $response->html->thisfile,
				);
				$t = $response->html->template($this->tpldir.'/user.groups.insert.html');
				$t->add($vars);
				$t->add($response->form);
				$t->group_elements(array('param_' => 'form'));
				return $t;
			break;

			case 'update':
				$group  = $this->response->html->request()->get('group');
				if($group !== '') {
					$this->response->add('group', $group);
				}
				$response = $this->update();
				if(isset($response->error)) {
					$_REQUEST[$this->message_param] = $response->error;
				}
				if(isset($response->msg)) {
					$this->__redirect($response->msg);
				}
				$vars = array(
					'label' => sprintf($this->lang['label_update'], $group),
					'thisfile' => $response->html->thisfile,
				);
				$t = $response->html->template($this->tpldir.'/user.groups.update.html');
				$t->add($vars);
				$t->add($response->form);
				$t->group_elements(array('param_' => 'form'));
				return $t;
			break;

			case 'sort':
				$response = $this->sort();
				if(isset($response->error)) {
					$_REQUEST[$this->message_param] = $response->error;
				}
				if(isset($response->msg)) {
					$this->__redirect($response->msg);
				}
				$data['headline'] = $this->lang['sort'];
				$data['noscript'] = $this->lang['noscript'];
				$vars = array_merge(
					$data, 
					array(
						'thisfile' => $response->html->thisfile,
				));
				$t = $response->html->template($this->tpldir.'/user.groups.sort.html');
				$t->add($vars);
				$t->add($response->form);
				$t->group_elements(array('param_' => 'form'));
				return $t;
			break;

			case $this->lang['delete']:
			case 'delete':
				$response = $this->delete();
				if(isset($response->error)) {
					$_REQUEST[$this->message_param] = $response->error;
				}
				if(isset($response->msg)) {
					$this->__redirect($response->msg);
				}
				$vars = array('thisfile' => $response->html->thisfile);
				$t = $response->html->template($this->tpldir.'/user.groups.delete.html');
				$t->add($vars);
				$t->add($response->form);
				$t->add($this->lang['label_delete'], 'label');
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
		$head['rank']['title']     = $this->lang['rank'];
		$head['rank']['style']     = 'width:30px;';
		$head['rank']['sortable']  = true;
		$head['group']['title']    = $this->lang['group'];
		$head['group']['style']    = 'width:120px;';
		$head['group']['sortable'] = true;
		$head['users']['title']    = $this->lang['users'];
		$head['users']['sortable'] = true;
		$head['action']['title']    = '&#160;';
		$head['action']['sortable'] = false;
		$result = $this->query->select( 'groups', '*' );
		$body = array();
		if(is_array($result)) {
			foreach( $result as $k => $f ) {
				$user = array();
				$users = $this->query->select( 'users2groups', array('login'), array('group', $f['group']));
				if(is_array($users)) {
					foreach($users as $v) {
						$user[] = $v['login'];
					}
					sort($user);
				}
				$params  = '?group='.$f['group'];
				$params .= $response->get_string($this->actions_name, 'update', '&', true );
				$a = $response->html->a();
				$a->href  = $response->html->thisfile.$params;
				$a->css   = 'update';
				$a->label = $this->lang['action_update'];
				$a->title = $this->lang['action_update'];
				$a->handler = 'onclick="phppublisher.wait();"';
				$body[] = array(
					'rank' 	=> $f['rank'], 
					'group' => $f['group'],
					'users' => implode(', ', $user),
					'action' => $a->get_string()
					);
			}
		}
		$table                      = $response->html->tablebuilder( 'ut', $response->get_array($this->actions_name, 'select') );
		$table->sort                = 'rank';
		$table->css                 = 'htmlobject_table table table-bordered';
		$table->border              = 0;
		$table->id                  = 'Groups_table';
		$table->head                = $head;
		$table->body                = $body;
		$table->sort_params         = $response->get_string( $this->actions_name, 'select' );
		$table->sort_form           = true;
		$table->sort_link           = false;
		$table->autosort            = true;
		$table->identifier          = 'group';
		$table->identifier_name     = $this->identifier_name;
		$table->identifier_disabled = array( 'Admin' );
		$table->actions             = array(& $this->lang['delete']);
		$table->actions_name        = $this->actions_name;
		$table->max                 = count($body);

		$response->table = $table;
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
			$group = $form->get_request('group');
			$check = $this->query->select('groups', '*', array('group', $group['group']));
			// Check group exists
			if(!is_array($check)) {
				$result = $this->query->select('groups', array('group'));
				if(is_array($result)) {
					$group['rank'] = count($result);
				}
				else {
					$group['rank'] = 0;
				}
				$error = $this->query->insert('groups', $group );
				if($error === '') {
					$users = $form->get_request('users');
					if(is_array($users)) {
						foreach($users as $user) { 
							$error = $this->query->insert('users2groups', array('login' => $user, 'group' => $group['group']));
						}
					}
				}
				if($error === '') {
					$response->msg = sprintf($this->lang['saved'], $group['group']);
				} else {
					$response->error = $error;
				}
			} else {
				$form->set_error('group[group]', sprintf($this->lang['error_exists'], $group['group']));
			}
		}
		if($form->get_errors()) {
			$response->error = join('<br>', $form->get_errors());
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
		$response = $this->get_response('update');
		$form     = $response->form;
		$group    = $this->response->html->request()->get('group');
		$error    = '';
		if(!$form->get_errors() && $response->submit()) {
			$users = $form->get_request('users');
			$error = $this->query->delete('users2groups', array('group', $group));
			if(is_array($users)) {
				foreach($users as $user) { 
					$error = $this->query->insert('users2groups', array('login' => $user, 'group' => $group));
				}
			}
			if($error === '') {
				$response->msg = sprintf($this->lang['saved'], $group);
			} else {
				$response->error = $error;
			}
		}		
		if($form->get_errors()) {
			$response->error = join('<br>', $form->get_errors());
		}
		$response->group = $group;
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
		$response = $this->get_response('delete');
		$groups   = $response->html->request()->get($this->identifier_name);
		$form     = $response->form;
		if( $groups !== '' ) {
			$i = 0;
			foreach($groups as $group) {
				$d['param_f'.$i]['label']                       = $group;
				$d['param_f'.$i]['object']['type']              = 'htmlobject_input';
				$d['param_f'.$i]['object']['attrib']['type']    = 'checkbox';
				$d['param_f'.$i]['object']['attrib']['name']    = $this->identifier_name.'[]';

				$d['param_f'.$i]['object']['attrib']['value']   = $group;
				$d['param_f'.$i]['object']['attrib']['checked'] = true;		
				$i++;
			}
			$form->add($d);
			if(!$form->get_errors() && $response->submit()) {
				$errors  = array();
				$message = array();
				foreach($groups as $key => $group) {
					if($group !== 'Admin') {
						$error = $this->query->delete( 'groups', array('group', $group) );
						if($error === '' ) {
							$error = $this->query->delete('users2groups', array('group', $group));
						}
						if($error !== '' ) {
							$errors[] = $error;
						} else {
							$form->remove($this->identifier_name.'['.$key.']');
							$message[] = sprintf($this->lang['deleted'], $group);
						}
					}
				}
				$i = 0;
				$result = $this->query->select('groups', array('group'), null, array('rank'));
				if(is_array($result)) {
					foreach($result as $k => $v) {
						$error = $this->query->update('groups', array('rank' => $i), array('group', $v['group']));
						$i++;
					}
				}
				if(count($errors) === 0) {
					$response->msg = join('<br>', $message);
				} else {
					$msg = array_merge($errors, $message);
					$response->error = join('<br>', $msg);
				}
			}
			if($form->get_errors()) {
				$response->error = join('<br>', $form->get_errors());
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
		$result   = $this->query->select('groups', '*', null, array('rank'));

		if(is_array($result) && count($result) > 1) {
			$names = array();
			foreach($result as $k => $v){
				$names[] = array($v['group']);
			}
			$d['select']['label']                        = '';
			$d['select']['object']['type']               = 'htmlobject_select';
			$d['select']['object']['attrib']['index']    = array(0,0);
			$d['select']['object']['attrib']['id']       = 'languages_select';
			$d['select']['object']['attrib']['css']      = 'picklist';
			$d['select']['object']['attrib']['name']     = 'groups[]';
			$d['select']['object']['attrib']['options']  = $names;
			#$d['select']['object']['attrib']['size']     = count($names);
			$d['select']['object']['attrib']['multiple'] = true;
	
			$form->add($d);
			if(!$form->get_errors() && $response->submit()) {
				$groups = $form->get_request('groups');
				foreach($groups as $k => $v) {
					$error = $this->query->update('groups', array('rank' => $k), array('group', $v));
				}
				if($error === '') {
					$response->msg = $this->lang['sorted'];
				} else {
					$response->error = $error;
				}
			}
			if($form->get_errors()) {
				$response->error = join('<br>', $form->get_errors());
			}
		} else {
			$response->msg = '';
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
	function get_response( $mode ) {
		$response = $this->response;
		$form     = $response->get_form($this->actions_name, $mode);
		if( $mode === 'insert' ) {
			$d['name']['label']                         = $this->lang['group'];
			$d['name']['required']                      = true;
			$d['name']['validate']['regex']             = '~^[^"<&]+$~i';
			$d['name']['validate']['errormsg']          = $this->lang['error_group'];
			$d['name']['object']['type']                = 'htmlobject_input';
			$d['name']['object']['attrib']['name']      = 'group[group]';
			$d['name']['object']['attrib']['type']      = 'text';
			$d['name']['object']['attrib']['value']     = '';
			$d['name']['object']['attrib']['maxlength'] = 60;
			$form->add($d);
		}
		if($mode === 'update') {
			$group  = $this->response->html->request()->get('group');
			if($group !== '') {
				$this->response->add('groupbbbb', $group);
			}
			$div = $response->html->div();
			$div->name = 'dummy';
			$div->value = $group;
			$div->css = 'form-control-static';
			$div->add($group);
			$d['name']['label']  = $this->lang['group'];
			$d['name']['static'] = true;
			$d['name']['object'] = $div;
		} else {
			$group = array();
		}

		if($mode !== 'delete') {
			$result = $this->query->select('users', array('login'), null, array('login'));
			if(is_array($result)) {
				foreach($result as $v) {
					$groups[] = array($v['login']);
				}
				$d['group']['label']                        = $this->lang['users'];
				$d['group']['object']['type']               = 'htmlobject_select';
				$d['group']['object']['attrib']['name']     = 'users[]';
				$d['group']['object']['attrib']['css']      = 'users2groups';
				$d['group']['object']['attrib']['index']    = array(0,0);
				$d['group']['object']['attrib']['multiple'] = true;
				$d['group']['object']['attrib']['options']  = $groups;
				$d['group']['object']['attrib']['size']     = count($groups);
				$d['group']['object']['attrib']['id']       = 'group_select';
				$d['group']['object']['attrib']['title']    = $this->lang['users_title'];

				$result = $this->query->select('users2groups', '*', array('group', $group));
				if(is_array($result)) {
					$tmp = array();
					foreach($result as $v) {
						if(isset($v['login'])) {
							$tmp[] = $v['login'];
						}
					}
					$d['group']['object']['attrib']['selected'] = $tmp;
				}
				$form->add($d);
			}
		}
		$response->form = $form;
		$response->form->display_errors = false;
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
