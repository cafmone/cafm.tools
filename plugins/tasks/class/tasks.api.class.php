<?php
/**
 * tasks_api
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2022, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class tasks_api
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'tasks_action';

var $lang = array(
	'supporter' => 'Supporter',
	'new_task' => 'New Task',
);

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param phppublisher $phppublisher
	 * @param htmlobject_response $response
	 */
	//--------------------------------------------
	function __construct($file, $response, $db, $user) {
		$this->file     = $file;
		$this->response = $response;
		$this->user     = $user;
		$this->db       = $db;
		$this->settings = $this->file->get_ini(PROFILESDIR.'/tasks.ini');
		$this->lang = $this->user->translate($this->lang, CLASSDIR.'plugins/ticket/lang/', 'tasks.api.ini');
		if(isset($this->settings['settings']['db'])) {
			$this->db->db = $this->settings['settings']['db'];
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
		$command = $this->response->html->request()->get($this->actions_name);
		switch($command) {
			case 'get_supporters':
				$this->get_supporters(true);
			break;
			case 'get_changelog':
				$this->get_changelog();
			break;
			case 'tasks':
				$this->tasks(true);
			break;
		}
	}

	//--------------------------------------------
	/**
	 * Get Supporters
	 *
	 * @access public
	 * @return htmlobject_form
	 */
	//--------------------------------------------
	function get_supporters($visible = false) {
		if($visible === true) {
			$id = $this->response->html->request()->get('id');
			if($id !== '') {
				$result = $this->user->query->select('users2groups', array('login'), array('group', $id));
				if(is_array($result)) {
					$select        = $this->response->html->select();
					$select->css   = 'htmlobject_select form-control';
					$select->id    = 'supporter';
					$select->name  = 'supporter';
					$select->add(array('','&#160;'), array(0,1));
					foreach($result as $v) {
						$select->add(array($v['login']), array(0,0));
					}
					$select->handler = 'onmousedown="phppublisher.select.init(this, \''.$this->lang['supporter'].'\'); return false;"';
					echo $select->get_string();
				}
			}
		}
	}

	//--------------------------------------------
	/**
	 * Get Changelog
	 *
	 * @access public
	 */
	//--------------------------------------------
	function get_changelog() {
		$id = $this->response->html->request()->get('id');
		if($id !== '') {
			require_once(CLASSDIR.'plugins/ticket/ticket.controller.class.php');
			$controller = new ticket_controller($this->file, $this->response, $this->db, $this->user);
			echo $controller->changelog($id)->get();
		}	
	}

	//--------------------------------------------
	/**
	 * Select
	 *
	 * @access public
	 */
	//--------------------------------------------
	function tasks($visible = false) {
		if($visible === true) {

			// plugin INFOS
			$params = '';
			$where = array();
			$elements = array(
				'callback',
				'referer',
				'tag',
				'value'
			);
			foreach($elements as $v) {
				$tmp = $this->response->html->request()->get($v);
				if($tmp !== '') {
					$params .= '&'.$v.'='.$tmp;
					$where[$v] = htmlentities($tmp);
				}
			}

			if($params !== '') {

				if(isset($this->settings['form'])) {
					$values = array_keys($this->settings['form']);
				} else {
					$values = array();
				}
				$values[] = 'id';
				$values[] = 'subject';

				$result = $this->db->select(
					'tasks_tasks',
					$values,
					$where,
					array('`updated` DESC')
				);
				if(is_array($result)) {

					$options = array();
					$opts = $this->db->select('tasks_form',array('id','option'));
					if(is_array($opts)) {
						foreach($opts as $o) {
							$options[$o['id']] = $o['option'];
						}
					}

					$head = array();
					$head['id']['title'] = 'ID';
					$head['id']['sortable'] = false;
					$head['id']['style'] = 'width: 50px;';
					$head['subject']['title'] = 'Subject';
					$head['subject']['sortable'] = false;
					$head['button']['title'] = '&#160;';
					$head['button']['style'] = 'width: 40px;';
					$head['button']['sortable'] = false;

					$body = array();
					foreach($result as $r) {
						$str = $r['subject'].'<br>';
						foreach($r as $k => $v) {
							if(isset($v) && $k !== 'id' && $k !== 'subject') {
								$tmp = (isset($options[$v])) ? $options[$v] : $v;
								$label = (isset($this->settings['labels'][$k])) ? $this->settings['labels'][$k] : $k;
								$str .= $label.': '.$tmp.'<br>';
							}
						}
						$a = $this->response->html->a();
						$a->href    = '?index_action=tasks&index_action_plugin=tasks&tasks_action=update&id='.$r['id'];
						$a->css     = 'btn btn-default btn-sm';
						$a->label   = '<span class="icon icon-edit"></span>';
						//$a->handler = 'onclick="phppublisher.wait();"';
						$a->target  = '_blank"';
						$body[] = array(
							'id' => $r['id'], 
							'subject' => $str,
							'button' => $a->get_string()
							);
					}

					$table = $this->response->html->tablebuilder( 'tasks_select', $this->response->get_array() );
					$table->sort  = 'updatet';
					$table->order = 'ASC';
					$table->limit           = 50;
					$table->offset          = 0;
					$table->max             = count($result);
					$table->css             = 'htmlobject_table table table-bordered';
					$table->id              = 'ticket_select';
					$table->sort_form       = false;
					$table->sort_link       = true;
					$table->autosort        = true;
					$table->head            = $head;
					$table->body            = $body;
					echo $table->get_string();

					$a = $this->response->html->a();
					$a->css = 'btn btn-default';
					$a->label = $this->lang['new_task'];
					$a->target = '_blank';
					$a->href = '?index_action=plugin&index_action_plugin=tasks&tasks_action=insert'.$params;

					echo '<center>';
					echo $a->get_string();
					echo '</center>';
					
				}
				elseif($result !== '') {
					echo '<div class="alert alert-danger">'.$result.'</div>';
				} else {
					$a = $this->response->html->a();
					$a->css = 'btn btn-default';
					$a->label = $this->lang['new_task'];
					$a->target = '_blank';
					$a->href = '?index_action=plugin&index_action_plugin=tasks&tasks_action=insert'.$params;

					echo '<center>';
					echo $a->get_string();
					echo '</center>';
				}
			}
		}
	}

}
?>
