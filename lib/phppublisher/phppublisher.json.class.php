<?php
/**
 * phppublisher_json
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2016, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class phppublisher_json
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
			if(file_exists(CLASSDIR.'/plugins/'.$this->plugin.'/class/'.$this->plugin.'.controller.class.php')) {
				require_once(CLASSDIR.'/plugins/'.$this->plugin.'/class/'.$this->plugin.'.controller.class.php');
				$class = $this->plugin.'_controller';
				$controller = new $class($this->pp->file, $this->pp->response, $this->pp->db, $this->pp->user);
				if(method_exists($controller, 'json')) {
					$controller->json();
				}
			}
		}
		else if($this->addon !== '') {
			if(file_exists(CLASSDIR.'/addons/'.$this->addon.'/class/'.$this->addon.'.controller.class.php')) {
				require_once(CLASSDIR.'/addons/'.$this->addon.'/class/'.$this->addon.'.controller.class.php');
				$class = $this->addon.'_controller';
				$controller = new $class($this->pp->file, $this->pp->response, $this->pp->db, $this->pp->user);
				if(method_exists($controller, 'json')) {
					$controller->json();
				}
			}
		}
	}

}
?>
