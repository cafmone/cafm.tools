<?php
/**
 * Phppublisher HTMLobjects
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2016, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */ 
class phppublisher_htmlobject extends htmlobject
{

	function __construct($user) {
		$this->user = $user;
		parent::__construct(CLASSDIR.'lib/htmlobjects');
	}

	//------------------------------------------------
	/**
	 * Formbuilder Object
	 *
	 * deny read only users form submit
	 *
	 * @access public
	 * @param string $response_id
	 * @param enum $nocheck [null|true]
	 * @return htmlobject_formbuilder
	 */
	//------------------------------------------------
	function formbuilder($response_id = null, $nocheck = null) {
		$this->__require( 'base' );
		$this->__require( 'form' );
		$form = $this->__factory( 'formbuilder', $this );
		$form->action = $this->thisfile;
		if(isset($response_id) && !isset($nocheck)) {
			$submit = $this->request()->get($response_id.'[submit]');
			if($submit !== '') {
				if($this->user->is_readonly()) {
					$id = uniqid('error');
					$d[$id]['object']['type'] = 'htmlobject_input';
					$d[$id]['object']['attrib']['type'] = 'hidden';
					$d[$id]['object']['attrib']['name'] = $id;
					$form->add($d);
					$form->set_error($id,'Permission denied');
				}
			}
		}
		return $form;
	}

}
