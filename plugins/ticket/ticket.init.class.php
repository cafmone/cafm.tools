<?php
/**
 * ticket_init
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2012, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class ticket_init
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'ticket_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'ticket_msg';

var $tpldir;

var $lang = array(
	'label' => 'Tickets',
	'new_ticket' => 'New Ticket',
	'my_ticket' => 'My Ticket',
	'my_todo' => 'My Todo',
	'my_group' => 'My Group',
	'all_tickets' => 'All Tickets'
);

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param htmlobject_response $response
	 * @param file_handler $file
	 * @param user $user
	 */
	//--------------------------------------------
	function __construct($response, $file, $user, $db) {
		$this->file = $file;
		$this->response = $response;
		$this->user = $user->get();
		$this->db = $db;

		$this->profilesdir = PROFILESDIR;

		$this->settings = $this->profilesdir.'/ticket.ini';
		$this->file = $file;
		$this->response = $response;
	}

	//--------------------------------------------
	/**
	 * Start
	 *
	 * @access public
	 * @return string
	 */
	//--------------------------------------------
	function start() {
		$errors = '';
	
		// handle templates
		if($this->file->exists($this->profilesdir.'/templates/')) {
			$files = $this->file->get_files(CLASSDIR.'plugins/ticket/templates/', '', '*.html');
			foreach($files as $file) {
				if(strpos($file['name'], '.config.') === false) {
					if(!$this->file->exists($this->profilesdir.'templates/'.$file['name'])) {
						$error = $this->file->copy($file['path'], $this->profilesdir.'templates/'.$file['name']);
						if($error !== '') {
							$errors[] = $error;
						}
					}
				}
			}
		}

		// handle lang
		if($this->file->exists($this->profilesdir.'/lang/')) {
			$files = $this->file->get_files(CLASSDIR.'plugins/ticket/lang/');
			foreach($files as $file) {
				if(!$this->file->exists($this->profilesdir.'lang/'.$file['name'])) {
					$error = $this->file->copy($file['path'], $this->profilesdir.'lang/'.$file['name']);
					if($error !== '') {
						$errors[] = $error;
					}
				}
			}
		}

		// handle folders
		$folders = array('ticket','ticket/templates','ticket/attachments' );
		foreach($folders as $v) {
			$target = $this->profilesdir.'/'.$v;
			if(!$this->file->exists($target)) {
				$error = $this->file->mkdir($target);
				if($error !== '') {
					$errors[] = $error;
				}
			}
		}

		// handle initial ini
		if(!$this->file->exists($this->profilesdir.'/ticket.ini')) {
			$error = $this->file->copy(CLASSDIR.'/plugins/ticket/setup/ticket.ini', $this->profilesdir.'/ticket.ini');
			if($error !== '') {
				$errors[] = $error;
			}
		}

		
		if(is_array($errors)) {
			$errors = implode('<br>', $errors);
		}

		return $errors;
	}

	//--------------------------------------------
	/**
	 * Menu
	 *
	 * @access public
	 * @return string
	 */
	//--------------------------------------------
	function menu() {

		$response = $this->response;
		$form = $response->get_form($this->actions_name, 'update');
		$ini = $this->file->get_ini($this->settings);

		$input = $response->html->input();
		$input->type = 'text';
		$input->name = 'id';
		$input->id = 'ticketshortcut';
		$input->style = 'margin-left:3px;width: 100px;display:inline-block;';
		$input->css = 'form-control input-sm';

		$box = $response->html->div();
		$box->add('<span class="icon icon-edit" style="margin: 0 10px 0 0;"></span>');
		$box->css = 'htmlobject_box autosize';
		$box->style = 'margin: 5px 0 5px 20px';
		$box->add($input);

		$new        = $response->html->a();
		$new->href  = $response->html->thisfile.$response->get_string($this->actions_name, 'insert', '?', true );
		$new->label = '<span class="icon icon-plus" style="margin: 0 10px 0 0;"></span> '.$this->lang['new_ticket'];
		$new->css   = 'list-group-item list-group-item-action';

		$my = '';
		$supp = '';
		$group = '';
		$all = '';

		#if(!isset($this->db->type)) {
		#	$new = '<div style="padding:10px;"><b>Error:</b> Check your db settings</div>';
		#} else {
		/*
			if(isset($ini['form']['reporter'])) {
				$my        = $response->html->a();
				$my->href  = $response->html->thisfile.$response->get_string($this->actions_name, 'select', '?', true ).'&filter[login]=reporter&filter[state]=open';
				$my->label = $this->lang['my_ticket'];

				$my_open        = $response->html->a();
				$my_open->href  = $response->html->thisfile.$response->get_string($this->actions_name, 'select', '?', true ).'&filter[login]=reporter&filter[state]=new';
				$my_open->label = $this->__select('reporter');
				$my = '<div class="list-group-item list-group-item-action">'.$my->get_string().' ('.$my_open->get_string().')'.'</div>';
			}
			if(isset($ini['form']['supporter'])) {
				$my_sup        = $response->html->a();
				$my_sup->href  = $response->html->thisfile.$response->get_string($this->actions_name, 'select', '?', true ).'&filter[login]=supporter&filter[state]=open';
				$my_sup->label = $this->lang['my_todo'];

				$s_open        = $response->html->a();
				$s_open->href  = $response->html->thisfile.$response->get_string($this->actions_name, 'select', '?', true ).'&filter[login]=supporter&filter[state]=new';
				$s_open->label = $this->__select('supporter');

				$supp = '<div class="list-group-item list-group-item-action">'.$my_sup->get_string().' ('.$s_open->get_string().')'.'</div>';
			}
			if(isset($ini['form']['group'])) {
				$group        = $response->html->a();
				$group->href  = $response->html->thisfile.$response->get_string($this->actions_name, 'select', '?', true ).'&filter[login]=group&filter[state]=open';
				$group->label = $this->lang['my_group'];

				$g_open        = $response->html->a();
				$g_open->href  = $response->html->thisfile.$response->get_string($this->actions_name, 'select', '?', true ).'&filter[login]=group&filter[state]=new';
				$g_open->label = $this->__select('group');

				$group = '<div class="list-group-item list-group-item-action">'.$group->get_string().' ('.$g_open->get_string().')'.'</div>';
			}
		*/
			$all        = $response->html->a();
			$all->href  = $response->html->thisfile.$response->get_string($this->actions_name, 'select', '?', true );
			$all->label = '<span class="icon icon-home" style="margin: 0 10px 0 0;"></span> '.$this->lang['all_tickets'];
			$all->css   = 'list-group-item list-group-item-action';
		#}

		$t = $response->html->template($this->tpldir.'ticket.menu.html');
		$t->add($this->response->html->thisfile, 'thisfile');
		$t->add($new, 'new_ticket');
		$t->add($my, 'my_ticket');
		$t->add($supp, 'my_todo');
		$t->add($group, 'my_group');
		$t->add($all, 'all_tickets');
		$t->add($box, 'input');
		$t->add($this->lang['label'], 'label');
		$t->add($form);
		$t->group_elements(array('param_' => 'form'));

		return $t->get_string();
	}

	//--------------------------------------------
	/**
	 * select
	 *
	 * @access protected
	 * @return integer
	 */
	//--------------------------------------------
	function __select($filter) {

		if(isset($this->db) && isset($this->db->type)) {

			$count = 0;
			switch ($filter) {
				case 'reporter':
					$where = "`reporter`='".$this->user['login']."'";
					$where .= " AND `updated` IS NULL";
					$result = $this->db->select( 'ticket_tickets', '*', $where);
				break;
				case 'supporter':
					$where = "`supporter`='".$this->user['login']."'";
					$where .= " AND `updated`=''";
					$result = $this->db->select( 'ticket_tickets', '*', $where);
				break;
				case 'group':
					$where = '';
					$i = 0;
					foreach($this->user['group'] as $group) {
						if($i === 0) {
							$where .= '`group`=\''.$group.'\'';
							$i = 1;
						}
						if($i > 0) {
							$where .= ' OR `group`=\''.$group.'\'';
						}
					}
					$where  = '('.$where.')';
					$where .= " AND `updated`=''";
					$result = $this->db->select( 'ticket_tickets', '*', $where);
				break;
			}
			if(isset($result) && is_array($result)) {
				$count = count($result);
			}
			return $count;
		}
	}
}
?>
