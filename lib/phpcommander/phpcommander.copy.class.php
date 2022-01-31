<?php
/**
 * PHPCommander Copy
 *
 * @package phpcommander
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2010, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class phpcommander_copy
{

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param string $path path to dir
	 * @param object $phpcommander
	 */
	//--------------------------------------------
	function __construct($path, $response, $file) {
		$this->__path = $path;
		$this->__pc   = $response;
		$this->__file = $file;
	}

	//--------------------------------------------
	/**
	 * Action copy
	 *
	 * @access public
	 * @param enum $type [copy|paste]
	 * @return htmlobject_formbuilder
	 */
	//--------------------------------------------
	function action($type) {	
		if($type === 'copy') {
			return $this->copy('copy');
		}
		else if($type === 'cut') {
			return $this->copy('cut');
		}
		else if($type === 'paste') {
			return $this->paste();
		}
		else if($type === 'clear') {
			return $this->clear();
		}
	}

	//--------------------------------------------
	/**
	 * Copy
	 *
	 * copy is only active when a session 
	 * is registered
	 *
	 * @access protected
	 * @return object
	 */
	//--------------------------------------------
	function copy( $action ) {
		$arCookie = array();
		$oldCookie = array();
		if (isset($_REQUEST[$this->__pc->identifier])) {
			$arCookie['class']  = get_class($this->__file);
			$arCookie['action'] = $action;
			foreach($_REQUEST[$this->__pc->identifier] as $key => $value) {
				$value      = stripslashes($value); //damned magic quotes
				$arCookie[uniqid('i')] = str_replace( '//', '/', $this->__path.'/'.$value );
			}
			$fullCookie       = array_unique($arCookie);
			$_SESSION['copy'] = $fullCookie;
		}
		$url = $this->__pc->get_url($this->__pc->actions_name, 'select');
		$this->__pc->redirect($url);
	}

	//--------------------------------------------
	/**
	 * Paste
	 *
	 * @access protected
	 * @return object
	 * @todo check pasted files are in root scope
	 */
	//--------------------------------------------
	function paste() {
		if(isset($_SESSION['copy']) && count($_SESSION['copy']) > 0) {
			$errors = array();
			$action = $_SESSION['copy']['action'];
			if($action === 'copy') {
				foreach( $_SESSION['copy'] as $key => $value ) {
					if($key !== 'action') {
						if($key !== 'class') {
							$name = basename( $value );
							$error = $this->__file->copy( $value, $this->__path.'/'.$name);
							if($error !== '') {
								$errors[] = $error;
							}
						}
					}
				}
				if(count($errors) < 1 ) {
					$url = $this->__pc->get_url($this->__pc->actions_name, 'select');
				} else {
					$url = $this->__pc->get_url($this->__pc->actions_name, 'select', $this->__pc->message, $errors);
				}
			}
			if($action === 'cut') {
				foreach( $_SESSION['copy'] as $key => $value ) {
					if($key !== 'action') {
						if($key !== 'class') {
							$name = basename( $value );
							$error = $this->__file->move( $value, $this->__path.'/'.$name);
							if($error !== '') {
								$errors[] = $error;
							}
						}
					}
				}
				unset($_SESSION['copy']);
				if(count($errors) < 1 ) {
					$url = $this->__pc->get_url($this->__pc->actions_name, 'select');
				} else {
					$url = $this->__pc->get_url($this->__pc->actions_name, 'select', $this->__pc->message, $errors);
				}
			}
		} else {
			$url = $this->__pc->get_url($this->__pc->actions_name, 'select');
		}
		$this->__pc->redirect($url);

	}

	//--------------------------------------------
	/**
	 * Clear Session
	 *
	 * @access protected
	 * @return null
	 */
	//--------------------------------------------
	function clear() {
		unset($_SESSION['copy']);
		$this->__pc->redirect($this->__pc->get_url($this->__pc->actions_name, 'select'));
	}

}
?>
