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

class ticket_select
{
/**
* translation
* @access public
* @var string
*/
var $lang = array();
/**
* trim notice
* @access public
* @var string
*/
var $substr = 200;

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
		$this->user       = $controller->user->get();
		$this->db         = $controller->db;
		$this->file       = $controller->file;
		$this->response   = $controller->response;
		$this->controller = $controller;
		$this->settings   = $controller->settings;
		## TODO handle missing settings[labels]

		$this->filter = $this->response->html->request()->get('filter');
		if($this->filter !== '') {
			$this->response->add('filter', $this->filter);
		}

		$this->elements = array(
			'flag_01',
			'flag_02',
			'flag_03',
			'flag_04',
			'flag_05',
			'flag_06',
			'flag_07',
			'flag_08',
			'flag_09',
			'flag_10'
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
	function action() {
		$response = $this->select();
		$data['table'] = $response->table;
		$vars = array_merge(
			$data, 
			array(
				'thisfile' => $response->html->thisfile,
		));
		$t = $response->html->template($this->tpldir.'ticket.select.html');
		$t->add($vars);
		$t->add($response->form);
		$t->group_elements(array('param_' => 'form'));
		return $t;
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
		$response = $this->get_response();
		$settings = $this->settings;

		$params = $response->get_array();
		$params['filter[login]'] = $response->html->request()->get('filter[login]');
		$params['filter[state]']  = $response->html->request()->get('filter[state]');

		$table                   = $response->html->tablebuilder( 'tt', $params );
		$table->sort             = 'updated';
		$table->order            = 'DESC';
		$table->limit            = 20;
		$table->css              = 'htmlobject_table table table-bordered';
		$table->border           = 0;
		$table->id               = 'Categorie_table';
		$table->handler_tr       = '';
		$table->sort_form        = true;
		$table->autosort         = true;
		#$table->identifier       = 'id';
		#$table->identifier_name  = $this->identifier_name;
		#$table->actions          = array($this->lang['delete']);
		#$table->actions_name     = $this->actions_name;

		$head['ticket']['title'] = $this->lang['select_ticket'];
		$head['ticket']['sortable'] = false;
		$head['details']['title'] = $this->lang['select_details'];
		$head['details']['sortable'] = false;
		$head['action']['title'] = '';
		$head['action']['sortable'] = false;

		$head['id']['title'] = $this->lang['select_id'];
		$head['id']['hidden'] = true;
		$head['created']['title'] = $this->lang['select_created'];
		$head['created']['hidden'] = true;
		if(isset($settings['form']['flag_01'])) {
			$head['flag_01']['title'] = $this->settings['labels']['flag_01'];
			$head['flag_01']['hidden'] = true;
		}
		if(isset($settings['form']['flag_02'])) {
			$head['flag_02']['title'] = $this->settings['labels']['flag_02'];
			$head['flag_02']['hidden'] = true;
		}
		if(isset($settings['form']['flag_03'])) {
			$head['flag_03']['title'] = $this->settings['labels']['flag_03'];
			$head['flag_03']['hidden'] = true;
		}
		if(isset($settings['form']['flag_04'])) {
			$head['flag_04']['title'] = $this->settings['labels']['flag_04'];
			$head['flag_04']['hidden'] = true;
		}
		if(isset($settings['form']['flag_05'])) {
			$head['flag_05']['title'] = $this->settings['labels']['flag_05'];
			$head['flag_05']['hidden'] = true;
		}
		#if(isset($settings['form']['category'])) {
		#	$head['category']['title'] = $this->lang['category'];
		#	$head['category']['hidden'] = true;
		#}
		$head['subject']['title'] = $this->lang['subject'];
		$head['subject']['hidden'] = true;
		$head['updated']['title'] = $this->lang['select_updated'];
		$head['updated']['hidden'] = true;

		$body = array();
		$result = $this->__select();
		if(is_array($result)) {
			$count = count( $result );
			foreach( $result as $f ) {

				$t = array();
				$d['id'] = $f['id'];
				$t['id'] = $this->lang['select_id'].': '.$f['id'];
				$d['created'] = $f['created'];
				$d['updated'] = $f['updated'];

				$t['created'] = $this->lang['select_created'].': '.date($this->controller->date_format, $f['created']);
				if(isset($settings['form']['reporter'])) {
					$d['reporter'] = '';
					if(isset($f['reporter']) && $f['reporter'] !== '') {
						$d['reporter'] = $f['reporter'];
					}
					else if($f['reporter'] === '') {
						$reporter = array();
						#if($f['reporter_salutation'] !== '') {
						#	$reporter[] = $this->lang['reporter_salutation_'.$f['reporter_salutation']];
						#}
						if($f['reporter_forename'] !== '') {
							$reporter[] = $f['reporter_forename'];
						}
						if($f['reporter_lastname'] !== '') {
							$reporter[] = $f['reporter_lastname'];
						}
						if(count($reporter) >= 1) {
							$d['reporter'] = implode(' ', $reporter);
						}
					}
					$t['reporter'] = $this->lang['reporter'].': '.$d['reporter'];
				}
				if(isset($settings['form']['supporter'])) {
					$d['supporter'] = '';
					if(isset($f['supporter'])) {
						$d['supporter'] = $f['supporter'];
					}
					$t['supporter'] = $this->lang['supporter'].': '.$d['supporter'];
				}
				if(isset($settings['form']['group'])) {
					$d['group'] = '';
					if(isset($f['group'])) {
						$d['group'] = $f['group'];
					}
					$t['group'] = $this->lang['group'].': '.$f['group'];
				}
				

				if(isset($settings['form']['flag_01'])) {
					$d['flag_01'] = '';
					if(isset($f['flag_01'])) {
						$res = $this->db->select('ticket_form', array('option'), array('id',$f['flag_01'] ));
						if(is_array($res)) {
							$status = $res[0]['option'];
						} else {
							$status = $f['flag_01'];
						}
						$d['flag_01'] = $status;
					}
					$t['flag_01'] = $this->settings['labels']['flag_01'].': '.$d['flag_01'];
				}

				if(isset($settings['form']['flag_02'])) {
					$d['flag_02'] = '';
					if(isset($f['flag_02'])) {
						$res = $this->db->select('ticket_form', array('option'), array('id',$f['flag_02'] ));
						if(is_array($res)) {
							$status = $res[0]['option'];
						} else {
							$status = $f['flag_02'];
						}
						$d['flag_02'] = $status;
					}
					$t['flag_02'] = $this->settings['labels']['flag_02'].': '.$d['flag_02'];
				}

				if($table->sort !== '' && isset($t[$table->sort])) {
					$t[$table->sort] = '<span>'.$t[$table->sort].'</span>';
				}
				$filter = $response->html->request()->get('filter[login]');
				if($filter !== '') {
					$t[$filter] = '<i>'.$t[$filter].'</i>';
				}
				$d['ticket'] = implode('<br>', $t);
				// Details
				$t = array();
				if(isset($f['updated']) && $f['updated'] != 0) {
					$t['updated'] = $this->lang['select_updated'].': '.date($this->controller->date_format, $f['updated']);
				}
				$d['subject'] = $f['subject'];
				$t['subject'] = $this->lang['subject'].': '.htmlentities($f['subject'], ENT_COMPAT, 'UTF-8');
				if(isset($f['updated']) && $f['updated'] !== '') {
					// Notice
					if($f['updater'] === 'notice') {
						$result = $this->db->select(
							'ticket_notices',
							array('notice','login'),	
							'`ticket`="'.$f['id'].'" AND `date`="'.$f['updated'].'" AND `private`=0'
						);
						if(is_array($result)) {
							$res = $result[0];
							$res = htmlentities($result[0]['notice'], ENT_COMPAT, 'UTF-8');
							$res = str_replace("\n", '<br>', $res);
							if(isset($result[0]['login']) && $result[0]['login'] !== '') {
								$res = $result[0]['login'].': '.$res;
							}
							// trim notice
							if(strlen($res) > $this->substr) { 
								if(isset($this->substr)) {
									$res = substr($res, 0, $this->substr).'...';
								}
							}
							$t['notice'] = '<hr><div class="notice_wrapper">'.$res.'</div>';
						}
					}
					// Changelog
					else if($f['updater'] === 'changelog') {
						$t['notice'] = '<hr><div class="notice_wrapper">'.$this->controller->changelog($f['id'])->get($f['updated']).'</div>';
					}
					// Closed
					else if($f['updater'] === 'closed') {
						$t['notice'] = '<hr><div class="notice_wrapper">'.$this->lang['label_closed'].'</div>';
					}
				}
				if($table->sort !== '' && isset($t[$table->sort])) {
					$t[$table->sort] = '<span>'.$t[$table->sort].'</span>';
				}

				$a          = $response->html->a();
				$a->href    = $response->html->thisfile.$response->get_string($this->actions_name, 'update', '?', true ).'&id='.$f['id'];
				$a->title   = $this->lang['action_edit'];
				$a->handler = 'onclick="phppublisher.wait();"';
				$a->css     = 'btn btn-default icon icon-edit edit';
				$d['details'] = '<div class="details_wrapper">'.implode('<br>', $t).'</div>';
				$d['action'] = $a;

				$body[] = $d;
			}
		}

		$table->head             = $head;
		$table->body             = $body;
		$table->max              = count($body);
		$response->table = $table;
		return $response;
	}

	//--------------------------------------------
	/**
	 * Build query
	 *
	 * @access protected
	 * @return array
	 */
	//--------------------------------------------
	function __select() {

		$filter = $this->filter;
		$where  = '';

		if(isset($filter['login']) && $filter['login'] !== '') {
			switch ($filter['login']) {
				case 'supporter':
					$where = "`supporter`='".$this->user['login']."'";
				break;
				case 'reporter':
					$where = "`reporter`='".$this->user['login']."'";
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
					if($where !== '') {
						$where = '('.$where.') ';
					}
				break;
			}
		}

		if(isset($filter['state']) && $filter['state'] !== '') {
			switch ($filter['state']) {
				case 'new':
					if($where !== '') { $where .= " AND "; }
					$where .= "`updated`=''";
				break;
				case 'open':
					if($where !== '') { $where .= " AND "; }
					$where .= "`updated`<>'' AND `updater`<>'closed'";
				break;
				case 'closed':
					if($where !== '') { $where .= " AND "; }
					$where .= "`updater`='closed'";
				break;
			}
		}

		foreach($this->elements as $e) {
			if(isset($filter[$e]) && $filter[$e] !== '') {
				if($where !== '') { $where .= " AND "; }
				$where .= "`".$e."`='".$this->db->handler()->escape($filter[$e])."'";
			}
		}

		$elements = array(
			'reporter',
			'supporter'
		);
		foreach($elements as $e) {
			if(isset($filter[$e]) && $filter[$e] !== '') {
				if($where !== '') { $where .= " AND "; }
				$where .= "`".$e."`='".$this->db->handler()->escape($filter[$e])."'";
			}
		}

		// subject
		if(isset($filter['subject']) && $filter['subject'] !== '') {
			if($where !== '') { $where .= " AND "; }
			$where .= "`subject` LIKE '".$this->db->handler()->escape($filter['subject'])."'";
		}

		// closed
		if(!isset($filter['closed']) && (!isset($filter['state']) || $filter['state'] === '')) {
			if($where !== '') { $where .= " AND "; }
			$where .= " (`updater` NOT LIKE 'closed' OR `updater` IS NULL) ";
		}

		$result = $this->db->select( 'ticket_tickets', '*', $where );
		if(is_array($result)) {
			return $result;
		}
		else if($result !== '') {
			$_REQUEST[$this->message_param] = $result;
			return array();
		} else {
			return array();
		}

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

		$settings = $this->settings;
		$response = $this->response;
		$form     = $response->get_form($this->actions_name, 'select', false);
		$form->remove('filter[');
		
		$d['filter_login']['label'] = $this->lang['filter_my'];
		$d['filter_login']['css']   = 'autosize';
		$d['filter_login']['style'] = 'float:right;clear:both;';
		$s = $this->response->html->select();
		$s->add(array('', '&#160;'), array(0,1));
		if(isset($this->settings['form']['reporter'])) {
			$s->add(array('reporter', $this->lang['filter_my_ticket']), array(0,1));
		}
		if(isset($this->settings['form']['supporter'])) {
			$s->add(array('supporter', $this->lang['filter_my_todo']), array(0,1));
		}
		if(isset($this->settings['form']['group'])) {
			$s->add(array('group', $this->lang['filter_my_group']), array(0,1));
		}
		$s->name    = 'filter[login]';
		$s->id      = 'filter_login';
		$s->css     = 'form-control';
		$s->handler = 'onchange="this.form.submit();phppublisher.wait();"';
		$s->style   = 'width: 110px;';
		$d['filter_login']['object'] = $s;

		$s = array();
		$s[] = array('', '&#160;');
		$s[] = array('new',$this->lang['filter_state_new']);
		$s[] = array('open',$this->lang['filter_state_open']);
		$s[] = array('closed',$this->lang['filter_state_closed']);

		$d['filter_state']['label']                       = $this->lang['filter_state'];
		$d['filter_state']['css']                         = 'autosize';
		$d['filter_state']['style']                       = 'float:right;clear:both;';
		$d['filter_state']['object']['type']              = 'htmlobject_select';
		$d['filter_state']['object']['attrib']['name']    = 'filter[state]';
		$d['filter_state']['object']['attrib']['index']   = array(0,1);
		$d['filter_state']['object']['attrib']['options'] = $s;
		$d['filter_state']['object']['attrib']['style']   = 'width: 110px;';
		$d['filter_state']['object']['attrib']['handler'] = 'onchange="this.form.submit();phppublisher.wait();return false;"';

		// TICKET INFOS
		foreach($this->elements as $v) {
			if(isset($settings['form'][$v])) {
				$result = $this->db->select('ticket_form', array('id', 'option'), array('element', $v), array('`rank`'));
				if(is_array($result)) {
					array_unshift($result, array('id' => '', 'option' => '&#160;'));
				} else {
					$result = array();
					$result[] = array('id' => '', 'option' => '&#160;');
				}
				// handle label
				$label = $v;
				if(isset($settings['labels'][$v])) {
					$label = $settings['labels'][$v];
				}
				$d[$v]['label']                       = $label;
				$d[$v]['css']                         = 'autosize';
				$d[$v]['style']                       = 'float:right;clear:both;';
				$d[$v]['object']['type']              = 'htmlobject_select';
				$d[$v]['object']['attrib']['name']    = 'filter['.$v.']';
				$d[$v]['object']['attrib']['index']   = array('id','option');
				$d[$v]['object']['attrib']['options'] = $result;
				$d[$v]['object']['attrib']['style']   = 'width: 110px;';
				$d[$v]['object']['attrib']['handler'] = 'onchange="this.form.submit();phppublisher.wait();return false;"';
			} else {
				$d[$v] = '';
			}
		}

		$tusers = $this->controller->user->query()->select('users', '*', null, array('login'));
		$users[0]['login'] = '';
		$users[0]['name']  = '&#160;';
		$i = 1;
		if(is_array($tusers)) {
			foreach($tusers as $v) {
			$name = $v['login'];
				if(isset($v['forename']) && isset($v['lastname'])) {
					$name = $v['forename'].' '.$v['lastname'];
				}
				$users[$i]['login'] = $v['login'];
				$users[$i]['name'] = $name;
				$i++;
			}
		}

		$elements = array(
			'reporter',
			'supporter'
		);
		foreach($elements as $v) {
			if(isset($settings['form'][$v])) {
				$d['filter_'.$v]['css']                         = 'autosize';
				$d['filter_'.$v]['style']                       = 'float:right;clear:both;';
				$d['filter_'.$v]['label']                       = $this->lang[$v];
				$d['filter_'.$v]['object']['type']              = 'htmlobject_select';
				$d['filter_'.$v]['object']['attrib']['name']    = 'filter['.$v.']';
				$d['filter_'.$v]['object']['attrib']['index']   = array('login','name');
				$d['filter_'.$v]['object']['attrib']['options'] = $users;
				$d['filter_'.$v]['object']['attrib']['style']   = 'width: 110px;';
				$d['filter_'.$v]['object']['attrib']['handler'] = 'onchange="this.form.submit();phppublisher.wait();return false;"';
			} else {
				$d['filter_'.$v] = '';
			}
		}

		$d['subject']['label']                     = $this->lang['subject'];
		$d['subject']['css']                       = 'autosize';
		$d['subject']['style']                     = 'float:right;clear:both;';
		$d['subject']['object']['type']            = 'htmlobject_input';
		$d['subject']['object']['attrib']['type']  = 'text';
		$d['subject']['object']['attrib']['name']  = 'filter[subject]';
		$d['subject']['object']['attrib']['id']    = 'subject';
		$d['subject']['object']['attrib']['value'] = '';
		$d['subject']['object']['attrib']['style'] = 'width: 110px;';

		$d['show_closed']['label']                     = $this->lang['label_show_closed'];
		$d['show_closed']['css']                       = 'autosize';
		$d['show_closed']['style']                     = 'float:right;clear:both;';
		$d['show_closed']['object']['type']            = 'htmlobject_input';
		$d['show_closed']['object']['attrib']['type']  = 'checkbox';
		$d['show_closed']['object']['attrib']['name']  = 'filter[closed]';
		$d['show_closed']['object']['attrib']['value'] = 'show closed';
		$d['show_closed']['object']['attrib']['handler'] = 'onchange="this.form.submit();phppublisher.wait();return false;"';


		$form->add($d);
		$form->display_errors = false;

		$response->form = $form;
		return $response;
	}

}
?>
