<?php
/**
 * tasks_helper
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2022, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class tasks_helper
{

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param object $controller
	 */
	//--------------------------------------------
	function __construct( $controller ) {
		$this->controller = $controller;
		$this->file = $controller->file;
		$this->response = $controller->response;
		$this->db = $controller->db;
		
		#require_once(CLASSDIR.'lib/db/query.class.php');
		#$this->db = new query(CLASSDIR.'lib/db');
		#$this->db->host = $controller->db->host;
		#$this->db->type = $controller->db->type;
		#$this->db->user = $controller->db->user;
		#$this->db->pass = $controller->db->pass;
		#$this->db->db   = $controller->db->db;
	}

	//--------------------------------------------
	/**
	 * Count tasks
	 *
	 * @access public
	 * @return array
	 */
	//--------------------------------------------
	function count($where) {

		$result = $this->db->select('tasks_tasks','id', $where.' AND (updater IS NULL OR NOT updater LIKE \'closed\')');
		if(is_array($result)) {
			return count($result);
		} else {
			if($result !== '') {
				$this->response->html->help($result);
			}
		}

	}

}
?>
