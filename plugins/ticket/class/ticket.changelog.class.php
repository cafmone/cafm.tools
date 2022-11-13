<?php
/**
 * ticket_changelog
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2012, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class ticket_changelog
{

var $lang = array();

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param ticket_controller $controller
	 * @param integer $id ticket id
	 */
	//--------------------------------------------
	function __construct($controller, $id) {
		$this->db         = $controller->db;
		$this->file       = $controller->file;
		$this->response   = $controller->response;
		$this->controller = $controller;
		$this->settings   = $controller->settings;
		$this->user       = $controller->user;
		$this->id = $id;
	}

	//--------------------------------------------
	/**
	 * Set changelog
	 *
	 * returns true on update
	 *
	 * @access public
	 * @param array $f fields to update
	 * @return bool
	 */
	//--------------------------------------------
	function set($f) {
		$updated = false;
		$user = $this->user->get();
		$login = $user['login'];
		$result = $this->db->select('ticket_tickets', '*', array('id', $this->id));
		if(is_array($result)) {
			$result = array_shift($result);
			foreach($result as $k => $v) {
				if($k !== 'updated') {
					if(is_null($v)) {
						$v = '';
					}
					if(isset($f[$k]) && $f[$k] !== $v) {
						$c['ticket'] = $this->id;
						$c['option'] = $k;
						$c['from']   = $v;
						$c['to']     = $f[$k];
						$c['login']  = $login;
						$c['date']   = $f['updated'];
						$error = $this->db->insert('ticket_changelog', $c);
						$updated = true;
					}
				}
			}
		}
		return $updated;
	}

	//--------------------------------------------
	/**
	 * Get changelog
	 *
	 * @access public
	 * @return string
	 */
	//--------------------------------------------
	function get( $date = null ) {
		$return = '';
		if(!isset($date)) {
			$result = $this->db->select('ticket_changelog', '*', array('ticket', $this->id));
		} else {
			$result = $this->db->select('ticket_changelog', array('option','login','from','to'), '`ticket`="'.$this->id.'" AND `date`="'.$date.'"');
		}
		if(is_array($result)) {
			$table = $this->response->html->table();
			$table->css = 'changelogtable table table-bordered';

			krsort($result);
			foreach($result as $k => $v) {
				$tr = $this->response->html->tr();
				if(isset($v['date'])) {
					$d['date']   = date($this->controller->date_format,$v['date']);
				}
				$d['login']  = $v['login'];
				$d['option'] = '';
				if(
					$v['option'] === 'flag_01' ||
					$v['option'] === 'flag_02' ||
					$v['option'] === 'flag_03' ||
					$v['option'] === 'flag_04' ||
					$v['option'] === 'flag_05' ||
					$v['option'] === 'flag_06' ||
					$v['option'] === 'flag_07' ||
					$v['option'] === 'flag_08' ||
					$v['option'] === 'flag_09' ||
					$v['option'] === 'flag_10'
				) {
					$label = $v['option'];
					if(isset($this->settings['labels'][$v['option']])) {
						$label = $this->settings['labels'][$v['option']];
					}
					$d['option'] = $label;
					$res = $this->db->select('ticket_form', array('option'), array('id', $v['from']));
					if(is_array($res)) {
						$d['from'] = $res[0]['option'];
					} else {
						$d['from'] = '&#160;';
					}
					$res = $this->db->select('ticket_form', array('option'), array('id', $v['to']));
					if(is_array($res)) {
						$d['sign'] = '-&gt;';
						$d['to'] = $res[0]['option'];
					} else {
						$d['sign'] = '-&gt;';
						$d['to'] = '';
					}
				} else {
					if(isset($this->lang[$v['option']])) {
						$d['option'] = $this->lang[$v['option']];
					}
					$d['from'] = $v['from'];
					$d['sign'] = '-&gt;';
					$d['to']   = $v['to'];
				}
				$tr->add($d);
				$table->add($tr);
			}
			$return = $table->get_string();
		}
		return $return;
	}

}
?>
