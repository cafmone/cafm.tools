<?php
/**
 * user_users
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2012, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class user_users
{
/**
* name for selected values
* @access public
* @var string
*/
var $identifier_name = 'admin_id';
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'admin_action';
/**
* message param
* @access public
* @var string
*/
var $message_param;
/**
* settings
* @access public
* @var string
*/
var $settings;
/**
* name of action buttons
* @access public
* @var string
*/
var $lang = array(
	'label' => 'User',
	'login' => 'Login',
	'login_title' => 'Login Name',
	'group' => 'Groups',
	'group_title' => 'Select one or more groups',
	'lang' => 'Lang',
	'lang_title' => 'Select a language',
	'email' => 'EMail',
	'salutation' => 'Salutation',
	'salutation_mrs' => 'Mrs.',
	'salutation_mr' => 'Mr.',
	'forename' => 'Forename',
	'lastname' => 'Surname',
	'title' => 'Title',
	'date' => 'Date',
	'address' => 'Address',
	'city' => 'City',
	'zip' => 'Zip',
	'state' => 'State',
	'country' => 'Country',
	'office' => 'Office',
	'phone' => 'Phone',
	'cellphone' => 'Cell Phone',
	'firm' => 'Firm',
	'comment' => 'Comment',
	'readonly' => 'Read only',
	'readonly_title' => 'Allow read access only',
	'password' => 'Password',
	'password_title' => 'Password',
	'password_repeat' => 'Password (repeat)',
	'password_repeat_title' => 'Repeat Password',
	'action_delete' => 'delete',
	'action_export' => 'export',
	'action_update' => 'update',
	'action_new' => 'New User',
	'msg_saved' => 'User %s has been saved',
	'msg_deleted' => 'User %s has been deleted',
	'error_no_match' => '%s does not match %s',
	'error_user_exists' => 'User %s allready exists',
	'error_login' => 'string must be a-z0-9_@.-',
	'error_no_group' => 'No Group found. Please setup a group first.'
);
	
	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param object $user
	 * @param object $file
	 * @param object $response
	 */
	//--------------------------------------------
	function __construct( $controller ) {

		$this->controller = $controller;
		$this->user       = $controller->user;
		$this->response   = $controller->response;
		$this->query      = $controller->query;
		$this->file       = $controller->file;
		$this->elements   = array(
				'email',
				'forename',
				'lastname',
				'title',
				'date',
				'address',
				'city',
				'zip',
				'state',
				'country',
				'firm',
				'office',
				'phone',
				'cellphone'
			);
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
	function action( $action = null ) {

		$this->action = '';
		$ar = $this->response->html->request()->get($this->actions_name);
		if($ar !== '') {
			if(is_array($ar)) {
				$this->action = key($ar);
			} else {
				$this->action = $ar;
			}
		} 
		else if(isset($action)) {
			$this->action = $action;
		}
		if($this->response->cancel()) {
			$this->action = 'select';
		}

		switch( $this->action ) {
			// SELECT
			case '':
			case 'select':
			default:
				$response      = $this->select();
				$data['table'] = $response->table;
				$href          = $response->html->a();
				$href->css     = 'btn btn-sm btn-default btn-block';
				$href->href    = $response->html->thisfile.$response->get_string($this->actions_name, 'insert', '?', true );
				$href->label   = $this->lang['action_new'];
				$href->handler = 'onclick="phppublisher.wait();"';
				$data['new']   = $href;
				$vars = array_merge(
					$data, 
					array(
						'thisfile' => $response->html->thisfile,
				));
				$t = $response->html->template($this->tpldir.'/user.users.select.html');
				$t->add($vars);
				$t->add($response->form);
				$t->group_elements(array('param_' => 'form'));
				return $t;
			break;
			// INSERT
			case 'insert':
				$response = $this->insert();
				if(isset($response->error)) {
					if(!isset($_REQUEST['nomsg'])) {
						$_REQUEST[$this->message_param] = $response->error;
					}
				}
				if(isset($response->msg)) {
					$response->redirect(
							$response->get_url($this->actions_name, 'select', $this->message_param, $response->msg)
					);
				}
				$data['login'] = $this->lang['action_new'];
				$vars = array_merge(
					$data, 
					array(
						'thisfile' => $response->html->thisfile,
				));
				$t = $response->html->template($this->tpldir.'/user.users.insert.html');
				$t->add($vars);
				$t->add($response->form);
				$t->group_elements(array('param_' => 'form'));
				return $t;
			break;
			// UPDATE
			case 'update':
				$response = $this->update();
				if(isset($response->error)) {
					$_REQUEST[$this->message_param] = $response->error;
				}
				if(isset($response->msg)) {
					$response->redirect(
							$response->get_url($this->actions_name, 'select', $this->message_param, $response->msg)
					);
				}
				$data['login'] = $this->lang['label'].' '.$response->html->request()->get('user[login]');
				$vars = array_merge(
					$data, 
					array(
						'thisfile' => $response->html->thisfile,
				));
				$t = $response->html->template($this->tpldir.'/user.users.insert.html');
				$t->add($vars);
				$t->add($response->form);
				$t->group_elements(array('param_' => 'form'));
				return $t;
			break;
			// DELETE
			case 'delete':
				$response = $this->delete();
				if(isset($response->error)) {
					$_REQUEST[$this->message_param] = $response->error;
				}
				if(isset($response->msg)) {
					$response->redirect(
							$response->get_url($this->actions_name, 'select', $this->message_param, $response->msg)
					);
				}
				$vars = array('thisfile' => $response->html->thisfile);
				$t = $response->html->template($this->tpldir.'/user.users.delete.html');
				$t->add($vars);
				$t->add($this->lang['label'].' '.$this->lang['action_delete'], 'label');
				$t->add($response->form);
				$t->group_elements(array('param_' => 'form'));
				return $t;
			break;
			// EXPORT
			case 'export':
				$response = $this->export();
				if(isset($response->error)) {
					$_REQUEST[$this->message_param] = $response->error;
				}
				if(isset($response->msg)) {
					$response->redirect(
							$response->get_url($this->actions_name, 'select', $this->message_param, $response->msg)
					);
				}
				$vars = array('thisfile' => $response->html->thisfile);
				$t = $response->html->template($this->tpldir.'/user.users.export.html');
				$t->add($vars);
				$t->add($this->lang['label'].' '.$this->lang['action_export'], 'label');
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

		$head['login']['title']     = $this->lang['login'];
		$head['login']['style']     = 'width:120px;';
		$head['group']['title']     = $this->lang['group'];
		$head['lang']['title']      = $this->lang['lang'];
		$head['lang']['style']      = 'width:20px;';
		$head['read']['title']      = 'readonly';
		$head['read']['style']      = 'width:30px;';
		$head['action']['title']    = '&#160;';
		$head['action']['sortable'] = false;

		$result = $this->query->select('users', '*');
		$body   = array();
		if(is_array($result)) {

			foreach( $result as $f ) {
				$group  = array();
				$groups = $this->user->query->select('users2groups', array('group'), array('login', $f['login']), array('rank'));
				if(is_array($groups)) {
					foreach($groups as $v) {
						$group[] = $v['group'];
					}
					sort($group);
				}
				$func = '';
				if($f['login'] !== 'admin') {
					$params  = '?user[login]='.$f['login'];
					$params .= $response->get_string($this->actions_name, 'update', '&', true );
					$href = $response->html->a();
					$href->href = $response->html->thisfile.$params;
					$href->label = $this->lang['action_update'];
					$href->title = $this->lang['action_update'];
					$href->css   = 'update';
					$href->handler = 'onclick="phppublisher.wait();"';
					$func = $href->get_string();
				}
				$read = '&#160;';
				if(isset($f['readonly'])) {
					$read = 'x';
				}

				$body[] = array( 
					'login'  => $f['login'],
					'lang'   => $f['lang'],
					'group'  => implode(', ', $group),
					'read'   => $read,
					'action' => $func
					);
			}
		}
		$table                      = $response->html->tablebuilder( $this->controller->prefix_table, $response->params );
		$table->sort                = 'login';
		$table->css                 = 'htmlobject_table table table-bordered';
		$table->border              = 0;
		$table->id                  = 'Tabelle';
		$table->head                = $head;
		$table->body                = $body;
		$table->sort_params         = $response->get_string( $this->actions_name, 'select' );
		$table->sort_form           = true;
		$table->sort_link           = false;
		$table->autosort            = true;
		$table->identifier          = 'login';
		$table->identifier_name     = $this->identifier_name;
		$table->identifier_disabled = array( 'admin' );
		$table->actions_name        = $this->actions_name;
		$table->max                 = count( $result );
		$table->actions             = array(
											array('export' => $this->lang['action_export']),
											array('delete' => $this->lang['action_delete'])
										);

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
			$user = $form->get_request('user');
			$ini  = $user;
			unset($ini['group']);
			unset($ini['pass2']);	
			if(
				(isset($user['password']) && !isset($user['pass2'])) ||
				(isset($user['password']) && $user['password'] !== $user['pass2'])
			) {
				$form->set_error('user[pass2]', sprintf($this->lang['error_no_match'], $this->lang['password_repeat'], $this->lang['password']));
			}
			if(!$form->get_errors()) {
				$check = $this->query->select('users', '*', array('login', $user['login']));
				// Check user exists
				if(!is_array($check)) {
					if(
						isset($user['password']) &&
						isset($this->settings['user']['authenticate']) && 
						$this->settings['user']['authenticate'] === 'file'
					) {
						unset($ini['password']);
						$error = $this->change_htpasswd( $user['login'], $user['password'], 'insert' );
					}
					else if(
						isset($user['password']) &&
						isset($this->settings['user']['authenticate']) && 
						$this->settings['user']['authenticate'] === 'db'
					) {
						$ini['password'] = crypt($ini['password'], base64_encode($ini['password']));
					}
					if($error === '') {
						$error = $this->query->insert('users', $ini);
					}
					if($error === '') {
						foreach($user['group'] as $group) { 
							$error = $this->query->insert('users2groups', array('login' => $user['login'], 'group' => $group));
						}
					}
					// handle authorize httpd
					if($error === '') {
						if(
							isset($this->settings['user']['authorize']) && 
							$this->settings['user']['authorize'] === 'httpd'
						) {
							$f = $GLOBALS['settings']['config']['basedir'].$GLOBALS['settings']['folders']['login'].'.htaccess';
							if($this->file->exists($f.'-disabled')) {
								$error = $this->file->rename($f.'-disabled', $f);
							}
						}
					}
				} else {
					$form->set_error('user[login]', sprintf($this->lang['error_user_exists'], $user['login']));
					$error = sprintf($this->lang['error_user_exists'], $user['login']);
				}
				if($error === '') {
					$response->msg = sprintf($this->lang['msg_saved'], $user['login']);
				} else {
					$response->error = $error;
				}
 			} else {
				$response->error = join('<br>', $form->get_errors());
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
	 * @param enum $mode [update|account]
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function update( $mode = 'update' ) {

		$response = $this->get_response( $mode );
		$form     = $response->form;

		## TODO if authorize === ldap create user if not exists?
		if(!$form->get_errors() && $this->response->submit()) {
			$user     = $form->get_request('user',true);
			$ini      = $user;
			$username = $form->get_static('user[login]');

			## TODO if user is external -> do not save
			$check = $this->user->get($username);
			if(isset($check['external'])) {
				$id = uniqid('error');
				$d[$id]['object']['type'] = 'htmlobject_input';
				$d[$id]['object']['attrib']['type'] = 'hidden';
				$d[$id]['object']['attrib']['name'] = $id;
				$form->add($d);
				$form->set_error($id,'Error: User is external - not saving');
			}

			unset($ini['pass2']);
			unset($ini['group']);
			if(
				(isset($user['password']) && !isset($user['pass2'])) ||
				(isset($user['password']) && $user['password'] !== $user['pass2'])
			) {
				$form->set_error('user[pass2]', sprintf($this->lang['error_no_match'], $this->lang['password_repeat'], $this->lang['password']));
			}
			if(!$form->get_errors()) {
				$error = '';
				// protect admin
				if($username !== 'admin' || $mode === 'account') {
					if(
						isset($user['password']) &&
						$user['password'] !== '' &&
						isset($this->settings['user']['authenticate']) && 
						$this->settings['user']['authenticate'] === 'file'
					) {
						unset($ini['password']);
						$error = $this->change_htpasswd( $username, $user['password'], 'update' );
					} 
					else if(
						isset($user['password']) &&
						isset($this->settings['user']['authenticate']) && 
						$this->settings['user']['authenticate'] === 'db'
					) {
						$ini['password'] = crypt($ini['password'], base64_encode($ini['password']));
					} else {
						unset($ini['password']);
					}
					if($error === '') {
						$error = $this->query->update('users', $ini, array('login', $username));
					}
					if($error === '' && $mode === 'update') {
						$error = $this->query->delete('users2groups', array('login', $username));
						foreach($user['group'] as $group) { 
							$error = $this->query->insert('users2groups', array('login' => $username, 'group' => $group));
						}
					}
					if($error === '') {
						$response->msg = sprintf($this->lang['msg_saved'], $username);
					} else {
						$response->error = $error;
					}
				} else {
					$response->msg = '';
				}
			} else {
				$response->error = join('<br>', $form->get_errors());
			}
		}
		if($form->get_errors()) {
			$response->error = join('<br>', $form->get_errors());
		}
		return $response;
	}

	//--------------------------------------------
	/**
	 * Account
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function account() {
		return $this->update('account');
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
		$folders  = $response->html->request()->get($this->identifier_name);
		$form     = $response->form;
		if( $folders !== '' ) {
			$i = 0;
			foreach($folders as $folder) {
				$d['param_f'.$i]['label']                       = $folder;
				$d['param_f'.$i]['object']['type']              = 'htmlobject_input';
				$d['param_f'.$i]['object']['attrib']['type']    = 'checkbox';
				$d['param_f'.$i]['object']['attrib']['name']    = $this->identifier_name.'['.$i.']';
				$d['param_f'.$i]['object']['attrib']['value']   = $folder;
				$d['param_f'.$i]['object']['attrib']['checked'] = true;
				$i++;
			}
			$form->add($d);
			if(!$form->get_errors() && $response->submit()) {
				$errors  = array();
				$message = array();
				foreach($folders as $key => $user) {
					if($user !== 'admin') {
						$error = $this->query->delete( 'users', array('login', $user) );
						if($error === '' ) {
							$error = $this->change_htpasswd( $user, '', 'delete' );
						}
						if($error === '' ) {
							$error = $this->query->delete('users2groups', array('login', $user));
						}
						if($error !== '' ) {
							$errors[] = $error;
						} else {
							$form->remove($this->identifier_name.'['.$key.']');
							$message[] = sprintf($this->lang['msg_deleted'], $user);
						}
					}
				}
				if(count($errors) !== 0) {
					$response->error = join('<br>', $message);
				} else {
					$msg = array_merge($errors, $message);
					$response->msg = join('<br>', $msg);
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
	 * Export
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function export() {

		// table params
		if($this->response->html->request()->get($this->controller->prefix_table) !== '') {
			$this->response->add($this->controller->prefix_table, $this->response->html->request()->get($this->controller->prefix_table));
		}

		$response = $this->response;
		$folders  = $response->html->request()->get($this->identifier_name);
		$form     = $response->get_form($this->actions_name, 'export');
		if( $folders !== '' ) {
			$submit = $this->response->html->button();
			$submit->type  = 'submit';
			$submit->label = $this->response->html->lang['response']['submit'];
			$submit->value = $this->response->html->lang['response']['submit'];
			$submit->name  = $this->response->id.'[submit]';
			$submit->css   = 'form-control btn btn-default btn-inline submit';
			$form->add($submit, 'submit');

			// Export
			$ini = $this->file->get_ini($this->controller->profilesdir.'/users.export.ini');

			$d['bom']['label']                     = 'BOM';
			$d['bom']['css']                       = 'autosize';
			$d['bom']['object']['type']            = 'htmlobject_input';
			$d['bom']['object']['attrib']['type']  = 'checkbox';
			$d['bom']['object']['attrib']['name']  = 'export[bom]';
			$d['bom']['object']['attrib']['title'] = 'Add utf-8 Byte Order Mark';
			if(isset($ini['bom'])) {
				$_REQUEST['export']['bom'] = 'x';
			}

			$o = array();
			$o[] = array('\n');
			$o[] = array('\r\n');

			$d['linefeed']['label']                       = 'Linefeed';
			$d['linefeed']['css']                         = 'autosize';
			$d['linefeed']['object']['type']              = 'htmlobject_select';
			$d['linefeed']['object']['attrib']['index']   = array(0,0);
			$d['linefeed']['object']['attrib']['options'] = $o;
			$d['linefeed']['object']['attrib']['name']    = 'export[linefeed]';
			$d['linefeed']['object']['attrib']['id']      = 'delimiter';
			$d['linefeed']['object']['attrib']['style']   = 'width:60px;';
			$d['linefeed']['object']['attrib']['css']     = 'input-sm';
			$d['linefeed']['object']['attrib']['title']   = 'Linefeed';
			if(isset($ini['linefeed'])) {
				$d['linefeed']['object']['attrib']['selected'] = array($ini['linefeed']);
			} else {
				$d['linefeed']['object']['attrib']['selected'] = array('\\n');
			}

			$o = array();
			$o[] = array(',');
			$o[] = array(';');
			$o[] = array('\t');

			$d['delimiter']['label']                       = 'Delimiter';
			$d['delimiter']['css']                         = 'autosize';
			$d['delimiter']['object']['type']              = 'htmlobject_select';
			$d['delimiter']['object']['attrib']['index']   = array(0,0);
			$d['delimiter']['object']['attrib']['options'] = $o;
			$d['delimiter']['object']['attrib']['name']    = 'export[delimiter]';
			$d['delimiter']['object']['attrib']['id']      = 'delimiter';
			$d['delimiter']['object']['attrib']['style']   = 'width:60px;';
			$d['delimiter']['object']['attrib']['css']     = 'input-sm';
			$d['delimiter']['object']['attrib']['title']   = 'Column separator';
			if(isset($ini['delimiter'])) {
				$d['delimiter']['object']['attrib']['selected'] = array($ini['delimiter']);
			} else {
				$d['delimiter']['object']['attrib']['selected'] = array(';');
			}

			$o = array();
			$o[] = array('','');
			$o[] = array("'","'");
			$o[] = array('quot','&#34;');

			$d['enclosure']['label']                       = 'Enclosure';
			$d['enclosure']['css']                         = 'autosize';
			$d['enclosure']['object']['type']              = 'htmlobject_select';
			$d['enclosure']['object']['attrib']['index']   = array(0,1);
			$d['enclosure']['object']['attrib']['options'] = $o;
			$d['enclosure']['object']['attrib']['name']    = 'export[enclosure]';
			$d['enclosure']['object']['attrib']['id']      = 'enclosure';
			$d['enclosure']['object']['attrib']['style']   = 'width:60px;';
			$d['enclosure']['object']['attrib']['css']     = 'input-sm';
			$d['enclosure']['object']['attrib']['title']   = 'Field enclosure';
			if(isset($ini['enclosure'])) {
				$d['enclosure']['object']['attrib']['selected'] = array($ini['enclosure']);
			} else {
				$d['enclosure']['object']['attrib']['selected'] = array('');
			}

			$i = 0;
			foreach($folders as $folder) {
				$d['param_f'.$i]['label']                       = $folder;
				$d['param_f'.$i]['css']                         = 'autosize';
				$d['param_f'.$i]['style']                       = 'float:right;clear:both;';
				$d['param_f'.$i]['object']['type']              = 'htmlobject_input';
				$d['param_f'.$i]['object']['attrib']['type']    = 'checkbox';
				$d['param_f'.$i]['object']['attrib']['name']    = $this->identifier_name.'['.$i.']';
				$d['param_f'.$i]['object']['attrib']['value']   = $folder;
				$d['param_f'.$i]['object']['attrib']['checked'] = true;
				$i++;
			}
			$form->add($d);

			if(!$form->get_errors() && $response->submit()) {
				$errors  = array();
				$message = array();

				$delimiter = $this->response->html->request()->get('export[delimiter]');
				$enclosure = $this->response->html->request()->get('export[enclosure]');
				$linefeed  = $this->response->html->request()->get('export[linefeed]');
				$bom       = $this->response->html->request()->get('export[bom]');

				if($delimiter === '') {
					echo 'nothing to do';
					exit(0);
				}
				elseif($delimiter === '\t' || $delimiter === 'tab') {
					$delimiter = chr(9);
				}
				if($enclosure === 'quot') {
					$enclosure = chr(34);
				}
				if($linefeed === '') {
					$linefeed = chr(10);
				}
				else if($linefeed === '\n') {
					$linefeed = chr(10);
				}
				else if($linefeed === '\r\n') {
					$linefeed = "\r\n";
				}

				// save settings
				$error = $this->file->make_ini( $this->controller->profilesdir.'/users.export.ini', $this->response->html->request()->get('export') );
				if($error !== '') {
					$response->error = $error;
				} else {
					$data[0]['login'] = 'login';
					// csv header
					foreach($this->elements as $v) {
						if(isset($this->settings['user'][$v])) {
							$data[0][$v] = $v;
						}
					}
					$data[0]['groups'] = 'groups';
					$i = 1;
					foreach($folders as $key => $user) {
						$ini = $this->query->select( 'users', '*', array('login', $user) );
						$ini = array_shift($ini);
						$data[$i]['login'] = $user;
						foreach($data[0] as $k => $v) {
							if(isset($ini[$k]) && $ini[$k] !== '') {
								$data[$i][$k] = $ini[$k];
							} else {
								$data[$i][$k] = '';
							}
						}
						// groups
						$group  = array();
						$groups = $this->user->query->select('users2groups', array('group'), array('login', $user), array('rank'));
						if(is_array($groups)) {
							foreach($groups as $v) {
								$group[] = $v['group'];
							}
							sort($group);
							$data[$i]['groups'] = implode(',',$group);
						} else {
							$data[$i]['groups'] = '';
						}
						$i++;
					}
					$this->__export($data, $bom, $delimiter, $enclosure, $linefeed);
				}
			}
			if($form->get_errors()) {
				$response->error = join('<br>', $form->get_errors());
			}
		} else {
			$response->msg = '';
		}
		$response->form = $form;
		return $response;
	}

	//--------------------------------------------
	/**
	 * Get Response
	 *
	 * @access public
	 * @param enum $mode [select|insert|update|account|delete]
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function get_response($mode) {

		// table params
		if($this->response->html->request()->get($this->controller->prefix_table) !== '') {
			$this->response->add($this->controller->prefix_table, $this->response->html->request()->get($this->controller->prefix_table));
		}

		$response = $this->response;
		$form = $response->get_form($this->actions_name, $mode);
		if( $mode !== 'select' && $mode !== 'delete') {
			if( $mode === 'update' ) {
				$user = $this->response->html->request()->get( 'user[login]' );
				$ini  = $this->query->select( 'users', '*', array('login', $user) );
				$ini = array_shift($ini);

				$d['user']['static']                    = true;
				$d['user']['object']['type']            = 'htmlobject_input';
				$d['user']['object']['attrib']['name']  = 'user[login]';
				$d['user']['object']['attrib']['type']  = 'hidden';
				$d['user']['object']['attrib']['value'] = $user;
				$pass_required = false;
			}
			if( $mode === 'account' ) {
				// get logged in user
				$ini = $this->user->get();
				$div = $response->html->div();
				$div->name = 'user[login]';
				$div->value = $ini['login'];
				$div->css = 'form-control-static';
				$div->add($ini['login']);
				$d['user']['label']  = $this->lang['login'];
				$d['user']['static'] = true;
				$d['user']['object'] = $div;
				$pass_required = false;
			}
			if( $mode === 'insert' ) {
				$ini  = array();
				$ini['lang']  = 'en';
				$d['user']['label']                     = $this->lang['login'];
				$d['user']['required']                  = true;
				$d['user']['validate']['regex']         = '/^[a-z0-9_@.-]+$/i';
				$d['user']['validate']['errormsg']      = $this->lang['error_login'];
				$d['user']['object']['type']            = 'htmlobject_input';
				$d['user']['object']['attrib']['name']  = 'user[login]';
				$d['user']['object']['attrib']['type']  = 'text';
				$d['user']['object']['attrib']['value'] = '';
				$d['user']['object']['attrib']['title'] = $this->lang['login_title'];
				$pass_required = true;
			}

			if( $mode !== 'account' ) {
				$d['readonly']['label']                     = $this->lang['readonly'];
				$d['readonly']['object']['type']            = 'htmlobject_input';
				$d['readonly']['object']['attrib']['type']  = 'checkbox';
				$d['readonly']['object']['attrib']['name']  = 'user[readonly]';
				$d['readonly']['object']['attrib']['value'] = 'true';
				$d['readonly']['object']['attrib']['title'] = $this->lang['readonly_title'];
				if(isset($ini['readonly'])) {
					$d['readonly']['object']['attrib']['checked'] = true;
				}
			} else {
				$d['readonly'] = '';
			}

			$lang = array();
			$lang[] = array( 'en', 'en' );
			$lang[] = array( 'de', 'de' );
			$d['lang']['label']                        = $this->lang['lang'];
			$d['lang']['required']                     = true;
			$d['lang']['validate']['regex']            = '/^[a-z]+$/i';
			$d['lang']['validate']['errormsg']         = 'string must be a-z';
			$d['lang']['object']['type']               = 'htmlobject_select';
			$d['lang']['object']['attrib']['name']     = 'user[lang]';
			$d['lang']['object']['attrib']['index']    = array(0,1);
			$d['lang']['object']['attrib']['options']  = $lang;
			if(isset($ini['lang'])) {
				$d['lang']['object']['attrib']['selected'] = array( $ini['lang'] );
			}
			$d['lang']['object']['attrib']['title']    = $this->lang['lang_title'];
			
			$settings = $this->settings;
			foreach($this->elements as $v) {
				if(isset($settings['user'][$v])) {
					$d[$v]['label'] = $this->lang[$v];
					if(isset($settings['user'][$v.'_required'])) {
						$d[$v]['required'] = true;
					}
					$d[$v]['object']['type']            = 'htmlobject_input';
					$d[$v]['object']['attrib']['type']  = 'text';
					$d[$v]['object']['attrib']['name']  = 'user['.$v.']';
					if(isset($ini[$v])) {
						$d[$v]['object']['attrib']['value'] = $ini[$v];
					}
				} else {
					$d[$v] = '';
				}
			}

			$salutations = array();
			$salutations[] = array( '', '&#160;' );
			$salutations[] = array( 'mrs', $this->lang['salutation_mrs'] );
			$salutations[] = array( 'mr', $this->lang['salutation_mr'] );
			if(isset($settings['user']['salutation'])) {
				$d['salutation']['label'] = $this->lang['salutation'];
				if(isset($settings['user']['salutation_required'])) {
					$d['salutation']['required'] = true;
				}
				$d['salutation']['object']['type']               = 'htmlobject_select';
				$d['salutation']['object']['attrib']['name']     = 'user[salutation]';
				$d['salutation']['object']['attrib']['index']    = array(0,1);
				$d['salutation']['object']['attrib']['options']  = $salutations;
				if(isset( $ini['salutation'] )) {
					$d['salutation']['object']['attrib']['selected'] = array( $ini['salutation'] );
				}
			} else {
				$d['salutation'] = '';
			}

			if(isset($settings['user']['comment']) && $mode !== 'account') {
				$d['comment']['label']                     = $this->lang['comment'];
				$d['comment']['object']['type']            = 'htmlobject_textarea';
				$d['comment']['object']['attrib']['name']  = 'user[comment]';
				if(isset($ini['comment'])) {
					$d['comment']['object']['attrib']['value'] = $ini['comment'];
				}
			} else {
				$d['comment'] = '';
			}

			if($mode !== 'account') {
				$result = $this->query->select('groups', array('group'), null, array('rank'));
				if(is_array($result)) {
					foreach($result as $v) {
						$group[] = array($v['group']);
					}
					$d['group']['label']                        = $this->lang['group'];
					$d['group']['required']                     = true;
					$d['group']['object']['type']               = 'htmlobject_select';
					$d['group']['object']['attrib']['name']     = 'user[group][]';
					$d['group']['object']['attrib']['css']      = 'users2groups';
					$d['group']['object']['attrib']['index']    = array(0,0);
					$d['group']['object']['attrib']['multiple'] = true;
					$d['group']['object']['attrib']['options']  = $group;
					$d['group']['object']['attrib']['size']     = count($group);
					$d['group']['object']['attrib']['id']       = 'group_select';
					$d['group']['object']['attrib']['title']    = $this->lang['group_title'];

					if($mode === 'update') {
						$result = $this->query->select('users2groups', '*', array('login', $user), array('rank'));
						if(is_array($result)) {
							foreach($result as $v) {
								$groups[] = $v['group'];
							}	
							$d['group']['object']['attrib']['selected'] = $groups;
						}
					}
				}
			}
			else if($mode === 'account' && isset($ini['group'])) {
				$div = $response->html->div();
				$div->name = '';
				$div->id   = 'placebo';
				$div->css  = 'form-control-static';
				$div->add(implode(', ', $ini['group']));
				$d['group']['label']  = $this->lang['group'];
				$d['group']['object'] = $div;
			} else {
				$d['group'] = '';
			}

			## TODO if authorize === ldap allow admin to change password ?

			if(
				isset($settings['user']['authenticate']) && 
				isset($settings['user']['authorize']) &&
				$settings['user']['authorize'] !== 'ldap'
			) {
				$d['pass1']['label']                     = $this->lang['password'];
				$d['pass1']['required']                  = $pass_required;
				$d['pass1']['object']['type']            = 'htmlobject_input';
				$d['pass1']['object']['attrib']['name']  = 'user[password]';
				$d['pass1']['object']['attrib']['type']  = 'password';
				$d['pass1']['object']['attrib']['value'] = '';
				$d['pass1']['object']['attrib']['title'] = $this->lang['password_title'];

				$d['pass2']['label']                     = $this->lang['password_repeat'];
				$d['pass2']['required']                  = $pass_required;
				$d['pass2']['object']['type']            = 'htmlobject_input';
				$d['pass2']['object']['attrib']['name']  = 'user[pass2]';
				$d['pass2']['object']['attrib']['type']  = 'password';
				$d['pass2']['object']['attrib']['value'] = '';
				$d['pass2']['object']['attrib']['title'] = $this->lang['password_repeat_title'];
			} else {
				$d['pass1'] = '';
				$d['pass2'] = '';
			}

			$form->add($d);
		}
		$response->form = $form;
		$response->form->display_errors = false;
		return $response;
	}

	//--------------------------------------------
	/**
	 * Change httpd password
	 *
	 * @access protected
	 * @param string $user
	 * @param string $password
	 * @param enum $mode [insert|update|delete]
	 * @return string
	 */
	//--------------------------------------------
	function change_htpasswd( $user, $password, $mode = 'update' ) {
		$error = '';
		require_once(CLASSDIR.'lib/file/file.htpasswd.class.php');
		$passwd = new file_htpasswd(PROFILESDIR.'.htpasswd');
		if($mode === 'update') {
			$error = $passwd->update($user, $password);
		}
		if($mode === 'insert') {
			$error = $passwd->insert($user, $password);
		}
		if($mode === 'delete') {
			$error = $passwd->delete($user);
		}
		return $error;
	}

	//--------------------------------------------
	/**
	 * Export
	 *
	 * @access protected
	 * @param array $data
	 * @param string $bom
	 * @param string $delimiter
	 * @param string $enclosure
	 * @param string $linefeed
	 * @return string
	 */
	function __export($data, $bom, $delimiter, $enclosure, $linefeed) {
		$name = 'users.csv';
		header("Pragma: public");
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: must-revalidate");
		header("Content-type: text/csv; charset=utf-8");
		header("Content-disposition: attachment; filename=$name");
		header('Content-Transfer-Encoding: binary');
		flush();
		if($bom !== '') {
			echo pack('H*','EFBBBF');
		}
		foreach($data as $v) {
			echo $enclosure.implode($enclosure.$delimiter.$enclosure, $v).$enclosure;
			echo $linefeed;
		}
		exit(0);
	}

}
?>
