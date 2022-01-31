<?php
/**
 * phppublisher_api
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2010, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class phppublisher_api
{

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param object $phppublisher
	 */
	//--------------------------------------------
	function __construct($phppublisher) {
		$this->pp = $phppublisher;
		$this->plugin = $this->pp->response->html->request()->get('plugin');
		$this->addon = $this->pp->response->html->request()->get('addon');
		if($this->plugin !== '') {
			$this->pp->response->add('plugin',$this->plugin);
		}
		else if($this->addon !== '') {
			$this->pp->response->add('addon',$this->addon);
		}
	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 */
	//--------------------------------------------
	function action() {
		$user = $this->pp->user->get();
		if(!isset($user['login'])) {
			echo 'not athorized';
			exit(0);
		}

		if($this->plugin !== '') {
			$ini      = $this->pp->file->get_ini( PROFILESDIR.'/plugins.ini' );
			$return   = array();
			if(in_array($this->plugin, $ini)) {
				if(file_exists(CLASSDIR.'/plugins/'.$this->plugin.'/class/'.$this->plugin.'.api.class.php')) {
					require_once(CLASSDIR.'/plugins/'.$this->plugin.'/class/'.$this->plugin.'.api.class.php');
					#$class = $this->plugin.'_api';

						// handle folder name - allow .
						$class = str_replace('.','_',$this->plugin).'_api';


					$controller = new $class($this->pp->file, $this->pp->response, $this->pp->db, $this->pp->user);
					if($this->pp->file->exists(PROFILESDIR.'/lang/')) {
						$controller->lang = $this->pp->user->translate($controller->lang, PROFILESDIR.'/lang/', $this->plugin.'.api.ini');
					} else {
						$controller->lang = $this->pp->user->translate($controller->lang, CLASSDIR.'/plugins/'.$this->plugin.'/lang', $this->plugin.'.api.ini');
					}
					$controller->action();
				}
			}
		}
		else if($this->addon !== '') {
			if(file_exists(CLASSDIR.'/addons/'.$this->addon.'/class/'.$this->addon.'.controller.class.php')) {
				require_once(CLASSDIR.'/addons/'.$this->addon.'/class/'.$this->addon.'.controller.class.php');
				$class = $this->addon.'_controller';
				$controller = new $class($this->pp->file, $this->pp->response, $this->pp->db, $this->pp->user);
				if(method_exists($controller, 'api')) {
					$controller->api();
				}
				#if($this->pp->file->exists(PROFILESDIR.'/lang/')) {
				#	$controller->lang = $this->pp->user->translate($controller->lang, PROFILESDIR.'/lang/', $this->addon.'.api.ini');
				#} else {
				#	$controller->lang = $this->pp->user->translate($controller->lang, CLASSDIR.'/plugins/'.$this->addon.'/lang', $this->addon.'.api.ini');
				#}
				
			}
		}
	}

}
?>
