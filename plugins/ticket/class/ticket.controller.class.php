<?php
/**
 * ticket_controller
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2012, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class ticket_controller
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
	'label_ticket' => 'Ticket #%s',
	'label_notice' => 'Notices',
	'label_supporter' => 'Supporter',
	'label_supporter_info' => 'Supporter Infos',
	'label_reporter' => 'Reporter',
	'label_message' => 'Message',
	'label_changelog' => 'Changelog',
	'label_close' => 'Close Ticket(s)',
	'label_mail' => 'Send Email ?',
	'label_closed' => 'Closed',
	'label_show_closed' => 'Show Closed',
	'label_reply' => 'Reply',
	'group' => 'Group',
	'supporter' => 'Supporter',
	'reporter' => 'Reporter',
	/*
	'reporter_email' => 'Email',
	'reporter_salutation' => 'Salutation',
	'reporter_salutation_mrs' => 'Mrs.',
	'reporter_salutation_mr' => 'Mr.',
	'reporter_forename' => 'Forename',
	'reporter_lastname' => 'Lastname',
	'reporter_office' => 'Office',
	'reporter_phone' => 'Phone',
	'reporter_firm' => 'Firm',
	*/
	'subject' => 'Subject',
	'description' => 'Description',
	'attachment' => 'Attachment',
	'created' => 'Created',
	'updated' => 'Updated',
	'select_value' => 'Please select a %s',
	'select_id' => 'ID',
	'select_created' => 'Created',
	'select_updated' => 'Updated',
	'select_ticket' => 'Ticket',
	'select_details' => 'Details',
	'filter_my' => 'My',
	'filter_my_ticket' => 'Ticket',
	'filter_my_todo' => 'Todo',
	'filter_my_group' => 'Group',
	'filter_state' => 'Status',
	'filter_state_new' => 'new',
	'filter_state_open' => 'open',
	'filter_state_closed' => 'closed',
	'filter_no_filter' => 'No filter',
	'notice_new' => 'Notice',
	'notice_private' => 'private',
	'notice_make_private' => 'click to make private',
	'notice_make_public' => 'click to make public',
	'notice_delete' => 'click to delete notice',
	'notice_save' => 'Save Notice',
	'notice_saved' => 'saved notice',
	'notice_deleted' => 'deleted notice',
	'mail_to' => 'To',
	'mail_from' => 'From',
	'mail_body' => 'Body',
	'mail_subject' => 'Subject',
	'mail_send' => 'Sended email',
	'mail_send_to' => 'Email send to: %s',
	'mail_not_send_to' => 'Email not send to: %s',
	'msg_saved' => 'Saved Ticket %s',
	'msg_updated' => 'Updated Ticket %s',
	'msg_closed' => 'Closed Ticket %s',
	'no_result' => 'No Result',
	'error_not_found' => 'Ticket %s not found',
	'error_no_mailadress' => 'Error: Mail not sent. No user found to send mail to.',
	'action_reply' => 'Save Ticket',
	'action_close' => 'Close Ticket',
	'back_to_overview' => 'Back to overview'
);
var $date_format = "Y/m/d H:i";

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param file_handler $phppublisher
	 * @param htmlobject_response $response
	 * @param query $db
	 * @param user $user
	 */
	//--------------------------------------------
	function __construct($file, $response, $db, $user) {
		$this->file = $file;
		$this->response = $response;
		$this->user = $user;
		$this->profilesdir = PROFILESDIR;
		$this->settings = $this->file->get_ini($this->profilesdir.'/ticket.ini');
		$this->lang = $this->user->translate($this->lang, CLASSDIR.'plugins/ticket/lang/', 'ticket.ini');
		$this->classdir = CLASSDIR.'plugins/ticket/class/';

		$this->db = $db;
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

		if(!isset($this->db->type)) {
			$content  = '<div style="margin: 80px auto 50px auto;width:200px;"><b>Error:</b> Check your db settings</div>';
		} else {
			$this->response->add($this->actions_name, $this->action);
			$content = array();
			switch( $this->action ) {
				case '':
				default:
				case 'select':
					$content = $this->select();
				break;
				case 'insert':
					$content = $this->insert();
				break;
				case 'update':
					$content = $this->update();
				break;
				case 'notice':
					$content = $this->notice();
				break;
				case 'mail':
					$content = $this->mail();
				break;
				case $this->lang['action_close']:
				case 'close':
					$content = $this->close();
				break;
				case 'download':
					$this->download();
				break;
			}
		}

		$c['label']   = '&#160;';
		$c['value']   = $content;
		$c['hidden']  = true;
		$c['target']  = $this->response->html->thisfile;
		$c['request'] = $this->response->get_array($this->actions_name, 'select' );
		$c['onclick'] = false;
		$c['active']  = true;

		$tab = $this->response->html->tabmenu('ticket_tab');
		$tab->message_param = $this->message_param;
		$tab->css = 'ticket_tab';
		$tab->boxcss = 'tabs-content noborder';
		$tab->add(array($c));

		return $tab;

	}

	//--------------------------------------------
	/**
	 * Insert
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function insert() {
		require_once($this->classdir.'ticket.insert.class.php');
		$controller = new ticket_insert($this);
		$controller->actions_name = $this->actions_name;
		$controller->message_param = $this->message_param;
		$controller->tpldir = $this->tpldir;
		$controller->lang  = $this->lang;
		$data = $controller->action();
		return $data;
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
		require_once($this->classdir.'ticket.update.class.php');
		$controller = new ticket_update($this);
		$controller->actions_name = $this->actions_name;
		$controller->message_param = $this->message_param;
		$controller->tpldir = $this->tpldir;
		$controller->lang  = $this->lang;
		$data = $controller->action();
		return $data;
	}

	//--------------------------------------------
	/**
	 * Select
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function select() {
		require_once($this->classdir.'ticket.select.class.php');
		$controller = new ticket_select($this);
		$controller->actions_name = $this->actions_name;
		$controller->tpldir = $this->tpldir;
		$controller->lang  = $this->lang;
		$controller->message_param = $this->message_param;
		$controller->identifier_name  = 'ticket_ident';
		$data = $controller->action();
		return $data;
	}

	//--------------------------------------------
	/**
	 * Notice
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function notice( $action = null ) {
		require_once($this->classdir.'ticket.notice.class.php');
		$response = $this->response->response();
		$response->id = 'note';
		$controller = new ticket_notice($response, $this);
		$controller->actions_name = 'notice_action';
		$controller->message_param = $this->message_param;
		$controller->tpldir = $this->tpldir;
		$controller->lang  = $this->lang;
		$data = $controller->action( $action );
		return $data;
	}

	//--------------------------------------------
	/**
	 * Changelog
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function changelog( $id ) {
		require_once($this->classdir.'ticket.changelog.class.php');
		$controller = new ticket_changelog($this, $id);
		$controller->actions_name = 'changelog_action';
		$controller->message_param = $this->message_param;
		$controller->tpldir = $this->tpldir;
		$controller->lang  = $this->lang;
		return $controller;
	}

	//--------------------------------------------
	/**
	 * Close
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function close() {
		require_once($this->classdir.'ticket.close.class.php');
		$controller = new ticket_close($this);
		$controller->actions_name = $this->actions_name;
		$controller->message_param = $this->message_param;
		$controller->tpldir = $this->tpldir;
		$controller->lang  = $this->lang;
		$controller->identifier_name  = 'ticket_ident';
		return $controller->action();
	}

	//--------------------------------------------
	/**
	 * Mail
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function mail() {
		require_once($this->classdir.'ticket.mail.class.php');
		$controller = new ticket_mail($this);
		$controller->actions_name = 'mail_action';
		$controller->message_param = $this->message_param;
		$controller->tpldir = $this->tpldir;
		$controller->lang  = $this->lang;
		return $controller->action();
	}

	//--------------------------------------------
	/**
	 * Download
	 *
	 * @access public
	 * @return null
	 */
	//--------------------------------------------
	function download() {
		if(isset($this->settings['settings']['attachment'])) {
			$id = $this->response->html->request()->get('id');
			$file = $this->response->html->request()->get('file');
			if($file !== '') {
				$result = $this->db->select('ticket_attachments', '*', array('file', $file));
				if(is_array($result)) {
					$file = $this->profilesdir.'/ticket/attachments/'.$id.'/'.$file;
					if(file_exists($file)) {
						header("Pragma: public");
						header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
						header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
						header("Cache-Control: must-revalidate");
						header("Content-type: ".$result[0]['type']."");
						header("Content-Length: ".$result[0]['size']);
						header("Content-disposition: attachment; filename=".$result[0]['name']);
						header("Accept-Ranges: ".$result[0]['size']); 
						readfile($file);
					}
				}
			}
		}
		exit(0);
	}

	//--------------------------------------------
	/**
	 * Get Response
	 *
	 * @access public
	 * @param enum $mode [insert|update]
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function get_response( $mode ) {

		$settings = $this->settings;
		$response = $this->response;
		$form     = $response->get_form($this->actions_name, $mode);
		$form->css = 'form-horizontal';

		// handle db field length
		$columns = $this->db->handler()->columns($this->db->db, 'ticket_tickets');

		if($mode === 'update') {
			$id = $this->response->html->request()->get('id');
			$ini = $this->db->select('ticket_tickets', '*', array('id', $id));
			if(is_array($ini)) {
				$ini = $ini[0];
				$submit = $form->get_elements('submit');
				$submit->value = $this->lang['action_reply'];
				$form->add($submit, 'submit');
			} else {
				return sprintf($this->lang['error_not_found'], $id);
			}
		}

		$d['supporter'] = '';
		if(isset($settings['form']['supporter'])) {
			$d['supporter'] = array();
			$rg = $response->html->request()->get('group');
			if($mode === 'update' && $rg === '' && isset($ini['group'])) {
				$rg = $ini['group'];
			}
			if(isset($settings['form']['group'])) {
				// Supporter placeholder
				$d['supporter']['label'] = $this->lang['supporter'];
				if(isset($settings['required']['supporter'])) {
					$d['supporter']['required'] = true;
				}
				$d['supporter']['style']                    = 'visibility:hidden;';
				$d['supporter']['object']['type']           = 'htmlobject_input';
				$d['supporter']['object']['attrib']['type'] = 'hidden';
				$d['supporter']['object']['attrib']['name'] = 'supporter';
				$d['supporter']['object']['attrib']['id']   = 'supporter';
				$d['supporter']['object']['attrib']['handler'] = 'onmousedown="phppublisher.select.init(this, \''.$this->lang['supporter'].'\'); return false;"';

				$result = $this->db->select('ticket_form', array('option'), array('id', $rg));
				if(is_array($result)) {
					$rg = $result[0]['option'];
				}
				$result = $this->user->query->select('users2groups', array('login'), array('group', $rg));
				if(is_array($result)) {
					$select       = $this->response->html->select();
					$select->css  = 'htmlobject_select form-control';
					$select->id   = 'supporter';
					$select->name = 'supporter';
					$tmp = $response->html->request()->get('supporter');
					if(isset($ini['supporter']) && $tmp === '') {
						$select->selected = array($ini['supporter']);
					} else {
						$select->selected = array($tmp);
					}
					$select->add(array('','&#160;'), array(0,1));
					foreach($result as $v) {
						$select->add(array($v['login']), array(0,0));
					}
					$d['supporter']['label'] = $this->lang['supporter'];
					if(isset($settings['required']['supporter'])) {
						$d['supporter']['required'] = true;
					}
					$d['supporter']['object'] = $select;
				}
			}
			else if(!isset($settings['form']['group'])) {
				$select       = $this->response->html->select();
				$select->css  = 'htmlobject_select form-control';
				$select->id   = 'supporter';
				$select->name = 'supporter';
				$select->handler = 'onmousedown="phppublisher.select.init(this, \''.$this->lang['supporter'].'\'); return false;"';
				$select->add(array('','&#160;'), array(0,1));
				if($this->user instanceof user) {
					$result = $this->user->query->select('users', array('login'), null, array('login'));
					if(is_array($result)) {
						foreach($result as $v) {
							$select->add(array($v['login']), array(0,0));
						}
					}
				}
				if(isset($ini['supporter'])) {
					$select->selected = array($ini['supporter']);
				}

				$d['supporter']['label'] = $this->lang['supporter'];
				if(isset($settings['required']['supporter'])) {
					$d['supporter']['required'] = true;
				}
				$d['supporter']['object'] = $select;
			}
		}
		
		// TICKET INFOS
		$elements = array(
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
		foreach($elements as $v) {
			if(isset($settings['form'][$v])) {
				$result = $this->db->select('ticket_form', array('id', 'option'), array('element', $v), array('`rank`'));
				if($mode === 'insert') {
					$label = $v;
					if(isset($settings['labels'][$v])) {
						$label = $settings['labels'][$v];
					}
					$option = '&#160;';
				}
				else {
					$option = '&#160;';
				}
				if(is_array($result)) {
					array_unshift($result, array('id' => '', 'option' => $option));
				} else {
					$result = array();
					$result[] = array('id' => '', 'option' => $option);
				}
				// handle label
				$label = $v;
				if(isset($settings['labels'][$v])) {
					$label = $settings['labels'][$v];
				}
				$d[$v]['label'] = $label;
				if($mode !== 'select' && isset($settings['required']) && isset($settings['required'][$v])) {
					$d[$v]['required'] = true;
				}
				$d[$v]['object']['type']              = 'htmlobject_select';
				$d[$v]['object']['attrib']['name']    = $v;
				$d[$v]['object']['attrib']['index']   = array('id','option');
				$d[$v]['object']['attrib']['options'] = $result;
				if(isset($ini[$v])) {
					$d[$v]['object']['attrib']['selected'] = array($ini[$v]);
				}
				$d[$v]['object']['attrib']['handler'] ='onmousedown="phppublisher.select.init(this, \''.$label.'\'); return false;"';
			} else {
				$d[$v] = '';
			}
		}

		// GROUP
		$d['group'] = '';
		if(isset($settings['form']['group'])) {
			$d['group'] = array();
			$result = $this->db->select('ticket_form', array('option'), array('element', 'group'), array('`rank`'));
			if(is_array($result)) {
				$d['group']['label'] = $this->lang['group'];
				if(isset($settings['required']) && isset($settings['required']['group'])) {
					$d['group']['required'] = true;
				}
				$option = array('', '&#160;');
				$select = $response->html->select();
				$select->name = 'group';
				$select->css = 'form-control';
				$select->add($option, array(0,1));
				$select->add($result, array('option','option'));
				$select->handler = 'onmousedown="phppublisher.select.init(this, \''.$this->lang['group'].'\'); return false;"';
				if(isset($ini['group'])) {
					$select->selected = array($ini['group']);
				}
				if(isset($settings['form']['supporter'])) {
					$select->handler = $select->handler.' onchange="get_users(this); return false;"'; 
				}
				$d['group']['object'] = $select;
			} else {
				$d['group'] = '';
			}
		}

		// REPORTER INFOS
		if(isset($settings['form']['reporter'])) {
			$d['reporter'] = array();
			$tusers = $this->user->list_users();
			$users[0]['login'] = '--empty--';
			$users[0]['name']  = '&#160;';
			$i = 1;
			if(is_array($tusers)) {
				foreach($tusers as $v) {
				$name = $v['login'];
					if(isset($v['forename']) && isset($v['lastname'])) {
						$name = $v['forename'].' '.$v['lastname'];
					}
					$users[$v['login']]['login'] = $v['login'];
					$users[$v['login']]['name'] = $name;
					$i++;
				}
			}
			$d['reporter']['label']                       = $this->lang['reporter'];
			$d['reporter']['object']['type']              = 'htmlobject_select';
			$d['reporter']['object']['attrib']['name']    = 'reporter';
			$d['reporter']['object']['attrib']['index']   = array('login','name');
			$d['reporter']['object']['attrib']['options'] = $users;
			$d['reporter']['object']['attrib']['handler'] = 'onmousedown="phppublisher.select.init(this, \''.$this->lang['reporter'].'\'); return false;"';
			if($mode !== 'select') {
				if(isset($ini['reporter'])) {
					$d['reporter']['object']['attrib']['selected'] = array($ini['reporter']);
				} else {
					if($mode === 'insert') {
						$user = $this->user->get();
						$d['reporter']['object']['attrib']['selected'] = array($user['login']);
					}
				}
			}
		} else {
			$d['reporter'] = '';
		}

		$d['subject']['label'] = $this->lang['subject'];
		if($mode === 'insert') {
			if($mode === 'insert') {
				$d['subject']['required'] = true;
			}
			$d['subject']['object']['type']            = 'htmlobject_input';
			$d['subject']['object']['attrib']['type']  = 'text';
			$d['subject']['object']['attrib']['name']  = 'subject';
			$d['subject']['object']['attrib']['id']    = 'subject';
			$d['subject']['object']['attrib']['value'] = '';
			if(isset($columns['subject']['length'])) {
				$d['subject']['object']['attrib']['maxlength'] = $columns['subject']['length'];
			}
			if(isset($ini['subject'])) {
				$d['subject']['object']['attrib']['value'] = $ini['subject'];
			}
		}
		if($mode === 'update') {
			$div = $this->response->html->div();
			$div->name = 'subject';
			$div->id   = 'subject';
			if(isset($ini['subject'])) {
				$div->add(htmlentities($ini['subject'], ENT_COMPAT, 'UTF-8'));
			}
			$d['subject']['object'] = $div;
		}

		$d['description']['label'] = $this->lang['description'];
		if($mode === 'insert') {
			$d['description']['required'] = true;
			$d['description']['object']['type']            = 'htmlobject_textarea';
			$d['description']['object']['attrib']['name']  = 'description';
			$d['description']['object']['attrib']['id']    = 'description';
			$d['description']['object']['attrib']['value'] = '';
			$d['description']['object']['attrib']['style'] = 'width:100%;';
			if(isset($columns['description']['length'])) {
				$d['description']['object']['attrib']['maxlength'] = $columns['description']['length'];
			}
		}	
		else if($mode === 'update') {
			$div = $this->response->html->div();
			$div->name  = 'description';
			$div->id    = 'description';
			if(isset($ini['description'])) {
				$ini['description'] = htmlentities($ini['description'], ENT_COMPAT, 'UTF-8');
				$ini['description'] = str_replace("\n", '<br>', $ini['description']);
				$ini['description'] = str_replace("[[b]]", '<b>', $ini['description']);
				$ini['description'] = str_replace("[[/b]]", '</b>', $ini['description']);
				$ini['description'] = str_replace("[[i]]", '<i>', $ini['description']);
				$ini['description'] = str_replace("[[/i]]", '</i>', $ini['description']);
				$ini['description'] = str_replace("[[u]]", '<u>', $ini['description']);
				$ini['description'] = str_replace("[[/u]]", '</u>', $ini['description']);
				$ini['description'] = preg_replace('~\[\[a (.*)\]\](.*)\[\[/a\]\]~', '<a href="$1" target="_blank">$2</a>', $ini['description']);
				/*
				$regexUrl = '!(((f|ht)tp(s)?://)[-a-zA-Zа-яА-Я()0-9@:%_+.,:~#?&;//=\]\[]+)!i';
				if(preg_match_all($regexUrl, $ini['description'], $urls)) {
					if(isset($urls[0])) {
						$urls[0] = array_unique($urls[0]);
						foreach($urls[0] as $url) {
							if(isset($url) && $url !== '') {
								$ini['description'] = str_replace($url, '<a href="'.$url.'" target="_blank" title="link: '.$url.'">'.$url.'</a>', $ini['description']);
							}
						}
					}
				}
				*/
				$div->add($ini['description']);
			}
			$d['description']['object'] = $div;
		}

### TODO attachment upload from class?

		$d['attachment'] = '';
		if(isset($settings['settings']['attachment']) && $mode === 'insert') {
			$d['attachment'] = array();
			$d['attachment']['object']['type']            = 'htmlobject_input';
			$d['attachment']['object']['attrib']['style'] = 'margin-top: 15px;';
			$d['attachment']['object']['attrib']['type']  = 'file';
			$d['attachment']['object']['attrib']['id']    = 'attachment';
			$d['attachment']['object']['attrib']['name']  = 'attachment';
			$d['attachment']['object']['attrib']['size']  = '40';
		}

		$d['attachments'] = '';
		if($mode === 'update') {
			$sql = "SELECT `file`,`name` FROM `ticket_attachments` WHERE `ticket`='".$ini['id']."' AND `notice` IS NULL";
			$result = $this->db->handler->query($sql);
			if(is_array($result)) {
				$a        = $response->html->a();
				$a->css   = 'attachment';
				$a->href  = $response->get_url($this->actions_name, 'download').'&file='.$result[0]['file'];
				$a->label = $result[0]['name'];

				$div = $response->html->div();
				$div->name = 'attachment';
				$div->add($a);

				$d['attachments'] = array();
				$d['attachments']['label']  = $this->lang['attachment'];
				$d['attachments']['object'] = $div;
			}
		}

		// notice (message)
		$d['message'] = '';
		if($mode === 'update') {
			$d['message'] = array();
			$d['message']['label'] = $this->lang['notice_new'];
			$d['message']['object']['type']            = 'htmlobject_textarea';
			$d['message']['object']['attrib']['name']  = 'message';
			$d['message']['object']['attrib']['id']    = 'message';
			$d['message']['object']['attrib']['value'] = '';
			$d['message']['object']['attrib']['cols']  = '';
			$d['message']['object']['attrib']['rows']  = '';
			$n_columns = $this->db->handler()->columns($this->db->db, 'ticket_notices');
			if(isset($n_columns['notice']['length'])) {
				$d['message']['object']['attrib']['maxlength'] = $n_columns['notice']['length'];
			}
		}

		$d['close'] = '';
		if($mode === 'update' && $ini['updater'] !== 'closed') {
			$d['close'] = array();
			$d['close']['static']                    = true;
			$d['close']['object']['type']            = 'htmlobject_input';
			$d['close']['object']['attrib']['name']  = $this->actions_name;
			$d['close']['object']['attrib']['value'] = $this->lang['action_close'];
			$d['close']['object']['attrib']['css']   = 'btn btn-default btn-inline';
			$d['close']['object']['attrib']['type']  = 'submit';
		} else {
			$response->closed = true;
			$d['close'] = $this->lang['label_closed'];
		}
		if($mode === 'update') {
			$response->created = date($this->date_format, $ini['created']);
		}

		$d['plugin'] = '';
		if($mode === 'insert') {
			$plugin = $this->response->html->request()->get('plugin');
			if($plugin !== '') {
				$d['plugin'] = array();
				$d['plugin']['label']                     = 'Plugin';
				$d['plugin']['static']                    = true;
				$d['plugin']['object']['type']            = 'htmlobject_input';
				$d['plugin']['object']['attrib']['name']  = 'plugin';
				$d['plugin']['object']['attrib']['value'] = htmlentities($plugin);
				$d['plugin']['object']['attrib']['readonly'] = true;
				$d['plugin']['object']['attrib']['type']  = 'text';
				if(isset($columns['plugin']['length'])) {
					$d['plugin']['object']['attrib']['maxlength'] = $columns['plugin']['length'];
				}
			}
		}
		else if($mode === 'update') {
			if(isset($ini['plugin'])) {
				$div = $this->response->html->div();
				$div->name = 'plugin';
				$div->add(htmlentities($ini['plugin'], ENT_COMPAT, 'UTF-8'));
				$d['plugin'] = array();
				$d['plugin']['label'] = 'Plugin';
				$d['plugin']['object'] = $div;
			}
		}

		$d['referer'] = '';
		if($mode === 'insert') {
			$referer = $this->response->html->request()->get('referer');
			if($referer !== '') {
				$d['referer'] = array();
				$d['referer']['label']                     = 'Referer';
				$d['referer']['static']                    = true;
				$d['referer']['object']['type']            = 'htmlobject_input';
				$d['referer']['object']['attrib']['name']  = 'referer';
				$d['referer']['object']['attrib']['value'] = htmlentities($referer);
				$d['referer']['object']['attrib']['readonly'] = true;
				$d['referer']['object']['attrib']['type']  = 'text';
				if(isset($columns['referer']['length'])) {
					$d['referer']['object']['attrib']['maxlength'] = $columns['referer']['length'];
				}
			}
		}
		else if($mode === 'update') {
			if(isset($ini['referer'])) {
				$div = $this->response->html->div();
				$div->name = 'referer';
				$div->add(htmlentities($ini['referer'], ENT_COMPAT, 'UTF-8'));
				$d['referer'] = array();
				$d['referer']['label'] = 'Referer';
				$d['referer']['object'] = $div;
			}
		}

		$form->add($d);
		$form->display_errors = false;

		$response->form = $form;
		return $response;
	}

}
?>
