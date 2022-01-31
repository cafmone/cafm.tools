<?php
/**
 * query_export_backup
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2016, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class query_export_backup
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
	 * @param controller $phppublisher
	 */
	//--------------------------------------------
	function __construct( $controller ) {
		$this->db         = $controller->db;
		$this->file       = $controller->file;
		$this->response   = $controller->response;
		$this->controller = $controller;
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
		$mode = $this->response->html->request()->get('backup');
		if($mode !== '') {
			$this->download($mode);
		}

		$a        = $this->response->html->a();
		$a->href  = $this->response->get_url($this->actions_name, 'backup').'&backup=db';
		$a->label = 'Backup Database';
		$a->title = 'backup';

		$t = $this->response->html->template($this->tpldir.'query.export.backup.html');
		$t->add($a, 'backup_db');

		return $t;
	}

	//--------------------------------------------
	/**
	 * Download
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function download( $mode ) {

		switch( $mode ) {
			case 'db':
				if($this->db->type === 'mysql') {
					$date = date('Y-m-d-H-i-s',time());
					$path = PROFILESDIR.$this->db->db.'.backup.'.$date.'.sql';
					$str  = 'mysqldump';
					$str .= ' -h '.$this->db->host;
					$str .= ' -u '.$this->db->user;
					$str .= ' -p'.$this->db->pass;
					$str .= ' --allow-keywords ';
					$str .= ' --add-drop-table';
					$str .= ' --complete-insert';
					$str .= ' --quote-names';
					$str .= ' '.$this->db->db.' > '.$path;

					@exec($str);
					@exec("gzip $path");
					$file = $this->file->get_fileinfo($path.'.gz');
					$mime = 'application/gzip';
				}
			break;
		}

		if(isset($mime)) {
			header("Pragma: public");
			header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
			header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
			header("Cache-Control: must-revalidate");
			header("Content-type: $mime");
			header("Content-Length: ".$file['filesize']);
			header("Content-disposition: inline; filename=".$file['name']);
			header("Accept-Ranges: ".$file['filesize']);
			#ob_end_flush();
			flush();
			readfile($file['path']);

			$this->file->remove($file['path']);
			exit(0);
		}
	}

}
?>
