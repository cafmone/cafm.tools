<?php
/**
 * filebrowser_search
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2020, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */


class filebrowser_search
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name;
/**
*  date as formated string
*  @access public
*  @var string
*/
var $date_format = "Y-m-d - H:i";
/**
* message param
* @access public
* @var string
*/
var $message_param;
/**
* prefix for tab menu
* @access public
* @var string
*/
var $prefix_tab;
/**
* path to templates
* @access public
* @var string
*/
var $tpldir;
/**
* path to translation
* @access public
* @var string
*/
var $langdir;
/**
* lang
* @access public
* @var array
*/
var $lang = array(
	'table_path' => 'Path',
	'table_name' => 'Name',
	'table_date' => 'Date',
	'table_size' => 'Size',
);


//var $apiparams = null;


	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param phppublisher $phppublisher
	 * @param htmlobject_response $response
	 */
	//--------------------------------------------
	function __construct( $controller ) {
		$this->response   = $controller->response;
		$this->file       = $controller->file;
		$this->query      = $controller->db;
		$this->user       = $controller->user;
		$this->controller = $controller;
		$this->commander  = $controller->commander->controller($controller->ini['folders']);
		$this->md5_params  = $controller->md5_params;
		$this->download_params = $controller->download_params;
	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 * @return htmlobject_tabmenu
	 */
	//--------------------------------------------
	function action() {

		// handle path
		$folders = $this->commander->get_root();
		$dir = $this->commander->dir;
		$root = $this->commander->root;
		
		$path = '';
		if(isset($folders) && is_array($folders)) {
			$path  = $folders[$root]['path'];
			$path .= ''.$dir.'/';
		}
		$this->basedir = $path;
		$this->md5_params .= '&'.$this->commander->prefix.'[dir]='.urlencode($dir).'&'.$this->commander->prefix.'[root]='.urlencode($root);
		$this->download_params .= '&'.$this->commander->prefix.'[dir]='.urlencode($dir).'&'.$this->commander->prefix.'[root]='.urlencode($root);

		### TODO handle root dir
		if($dir !== '..') {
			$list = $this->__get_list($this->basedir);
			if(isset($list['files'])) {
				$list['files'] = $this->__sort($list['files'], 'dir');
				//$this->response->html->help($list['files']);

				$this->files = $list['files'];
			}
		}

		// handle files
		if(isset($this->files)) {
			$script = 'files = '.json_encode($this->files).';';
		} else {
			$script = 'files = [];';
		}

		$t = $this->response->html->template($this->tpldir.'filebrowser.search.html');
		$t->add($this->commander->breadcrumps(), 'breadcrumps');
		$t->add($script, 'script');
		$t->add($this->md5_params, 'md5_params');
		$t->add($this->download_params, 'download_params');

		return $t;
	}

	//--------------------------------------------
	/**
	 * get list of files
	 *
	 * @access public
	 * @return htmlobject_tabmenu
	 */
	//--------------------------------------------
	function __get_list( $path ) {
		$list = array();
		$handle = opendir("$path/.");
		while (false !== ($file = @readdir($handle))) {
			if ($file !== '.'  && $file !== '..') {
				if(is_dir($path.'/'.$file)) {

					#$list['folders'][] = str_replace($this->basedir.'/', '',$path.'/'.$file);
					$tmp = $this->__get_list($path.'/'.$file);
					foreach($tmp as $k => $f) {
						if($k === 'folders') {
							#foreach($f as $f2) {
							#	$list['folders'][] = $f2;
							#}
						}
						else  {
							foreach($f as $f2) {
								$list['files'][] = $f2;
							}
						}
					}
				} else {
					$show = true;
					$pt = str_replace($this->basedir.'/', '', $path.'/');
					#if(isset($this->filter) && isset($this->filter['path'])) {
					#	preg_match('~^'.$this->filter['path'].'~u', $pt, $matches);
					#	if(!isset($matches[0])) {
					#		$show = false;
					#	}
					#}
					if($show === true) {
						$info = array(
							'dir'  => $pt,
							'file' => $file,
							'size' => @filesize($path.'/'.$file),
							'date' => date($this->date_format, filemtime($path.'/'.$file)),
						);
						$list['files'][] = $info;
					}
				}
			}
		}
		
		return $list;
	}
	
	
	//------------------------------------------------
	/**
	 * Sort array [ids] by key [sort]
	 *
	 * @access protected
	 * @param array $ids
	 * @param string $sort
	 * @param enum $order [ASC/DESC]
	 * @return array
	 */
	//------------------------------------------------
	function __sort($ids, $sort, $order = '') {
		if($order !== '') {
			if($order == 'ASC') $sort_order = SORT_ASC;
			if($order == 'DESC') $sort_order = SORT_DESC;
		} else {
			$sort_order = SORT_ASC;
		}
		$column = array();
		reset($ids);
		foreach($ids as $val) {
			if(isset($val[$sort])) {
				$column[] = $val[$sort];
			}
		}
		if(count($ids) === count($column)) {
			array_multisort($column, $sort_order, $ids);
		}
		return $ids;
	}

}
?>
