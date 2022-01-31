<?php
/**
 * bestandsverwaltung_config_backup
 *
 * This file is part of plugin bestandsverwaltung
 *
 *  This file is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU GENERAL PUBLIC LICENSE Version 2
 *  as published by the Free Software Foundation;
 *
 *  This file is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this file (see ../LICENSE.TXT) If not, see 
 *  <http://www.gnu.org/licenses/>.
 *
 *  Copyright (c) 2015-2016, Alexander Kuballa
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @author Uwe Pochadt
 * @copyright Copyright (c) 2008 - 2016, Alexander Kuballa
 * @license GNU GENERAL PUBLIC LICENSE Version 2 (see ../LICENSE.TXT)
 * @version 1.0
 */

class bestandsverwaltung_config_backup
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
var $lang = array(
		"lang_query" => "Database",
		"lang_export" => "Export",
		"lang_printout" => "Printout",
		"lang_filter" => "Custom Filters",
		"lang_qrcode" => "QRCode replacements",
		"lang_recording" => "Erfassung",
		"query" => array(
			"type" => "Type",
			"host" => "Host",
			"db" => "DB",
			"user" => "User",
			"pass" => "Pass"
		),
		"update_sucess" => "Settings updated successfully",
	);

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param file $file
	 * @param htmlobject_response $response
	 */
	//--------------------------------------------
	function __construct( $file, $response, $db, $user ) {
		$this->file     = $file;
		$this->response = $response;
		$this->db       = $db;
		$this->user     = $user;
		$this->settings = PROFILESDIR.'bestandsverwaltung.ini';
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

		$t = $this->response->html->template($this->tpldir.'bestandsverwaltung.config.backup.html');

		$a        = $this->response->html->a();
		$a->href  = $this->response->get_url($this->actions_name, 'backup').'&backup=db';
		$a->label = 'Backup Database';
		$a->title = 'backup';

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
					$path = PROFILESDIR.'bestand.backup.db.sql';

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
