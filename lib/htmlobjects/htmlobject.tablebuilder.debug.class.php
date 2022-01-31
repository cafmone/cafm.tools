<?php
/**
 * Tablebuilder Debug
 *
 * @package htmlobjects
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2012, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */
class htmlobject_tablebuilder_debug extends htmlobject_tablebuilder
{
/**
* Body errors
*
* @access private
* @var array
*/
var $__error_body;
/**
* Head errors
*
* @access private
* @var array
*/
var $__error_head;
/**
* Init errors
*
* @access private
* @var array
*/
var $__error_init;
/**
* Index errors
*
* @access private
* @var array
*/
var $__error_index = array();

	//------------------------------------------------
	/**
	* Init basic values __body and __cols
	*
	* @access public
	*/
	//------------------------------------------------
	function init() {
		if(!is_array($this->body)) {
			$this->__error_init[] = array('ERROR', 'attribute body must be of type array');
		}
		parent::init();
		if( isset($this->identifier) && $this->identifier !== '' ) {
			if($this->identifier_name === '') {
				$this->__error_init[] = array('ERROR', 'found identifier but no identifier_name');
			}
			if( !isset($this->actions) || count($this->actions) < 1) {
				$this->__error_init[] = array('NOTICE', 'found identifier ['.$this->identifier.'] but no actions');
			} elseif (!isset($this->actions_name) || $this->actions_name === '') {
				$this->__error_init['aname'] = array('ERROR', 'found actions but no actions_name');
			}
		}
		if(	isset($this->actions) && count($this->actions) > 0) {
			if(!isset($this->identifier) || $this->identifier === '') {
				$this->__error_init[] = array('ERROR', 'found actions but no identifier');
			}
			if (!isset($this->actions_name) || $this->actions_name === '') {
				$this->__error_init['aname'] = array('ERROR', 'found actions but no actions_name');
			}
		}
		if( $this->__error_init ) {
			htmlobject_debug::_print( '', 'init', __CLASS__ );
			foreach( $this->__error_init as $error ) {
				htmlobject_debug::_print( $error[0], $error[1] );
			}
		}
	}

	//------------------------------------------------
	/**
	* Get table head
	*
	* @access protected
	* @return object|string htmlobject_tr or empty string
	*/
	//------------------------------------------------	
	function __get_head() {
		$error = null;
		if(count($this->head) < 1) {
			$this->__error_head[] = array('ERROR', 'No Table head set');
		} else {
			foreach( $this->head as $key => $value ) {
				foreach($this->__body as $body) {
					if(!in_array($key, array_keys($body))) {
						$this->__error_head[] = array('ERROR', 'found index ['.$key.'] in head but not in body');
						$this->__error_index[] = $key;
					}
					break;
				}
				if(!isset($this->head[$key]['title'])) {
					if(!isset($this->head[$key]['hidden']) || $this->head[$key]['hidden'] !== true) {
						$this->__error_head[] = array('NOTICE', 'could not find index [title] for head index ['.$key.']');
					}
				}

			}
			if( $this->__error_head ) {
				htmlobject_debug::_print( '', 'Building head', __CLASS__ );
				foreach( $this->__error_head as $error ) {
					htmlobject_debug::_print( $error[0], $error[1] );
				}
			}
		}
		return parent::__get_head();
	}

	//------------------------------------------------
	/**
	* Add identifier td to body row
	*
	* @access public
	* @param int $key
	* @param string $ident
	* @return object|string
	*/
	//------------------------------------------------
	function __get_indentifier($key, $ident) {
		if(!in_array($this->identifier, array_keys($this->__body[$key]))) {
			$this->__error_body[] = array('ERROR', 'identifier ['.$this->identifier.'] not found in body['.$key.']');
		}
		return parent::__get_indentifier($key, $ident);
	}

	//------------------------------------------------
	/**
	* Get rows of table body
	*
	* @access protected
	* @return array
	*/
	//------------------------------------------------		
	function __get_body() {
		$body = parent::__get_body();
		foreach ($this->__body as $line => $val) {
			foreach( $this->head as $key => $value ) {
				if(!in_array( $key, $this->__error_index)) {
					if(!isset($this->__body[$line][$key])) {
						$this->__error_body[] = array('ERROR', 'could not find index ['.$key.'] in body['.$line.']');
					}
				}
			}
			foreach($val as $key2 => $value) {
				if(!isset($this->head[$key2])) {
					$this->__error_body[] = array('NOTICE', 'found index ['.$key2.'] in body['.$line.'] but not in head array');
				}
			}
			if( $this->__error_body ) {
				htmlobject_debug::_print( '', 'Building body', __CLASS__ );
				foreach( $this->__error_body as $error ) {
					htmlobject_debug::_print( $error[0], $error[1] );
				}
				$this->__error_body = null;
			}
		}
		return $body;
	}

}
?>
