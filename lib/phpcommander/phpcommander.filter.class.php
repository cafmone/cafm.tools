<?php
/**
 * PHPCommander Filter
 *
 * @package phpcommander
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2010, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class phpcommander_filter
{

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param object $phpcommander
	 */
	//--------------------------------------------
	function __construct($response) {
		$this->__response = $response;
	}

	//--------------------------------------------
	/**
	 * Action delete
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function action() {
		$form = $this->get_form();
		$t = $this->__response->html->template($this->__response->tpldir.'/phpcommander.filter.html');
		$t->add($form);
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Get form
	 *
	 * @access protected
	 * @return htmlobject_formbuilder
	 */
	//--------------------------------------------
	function get_form() {

		$response = $this->__response;
		$form     = $response->get_form($response->actions_name, 'select');
		$filter   = $response->html->request()->get($response->prefix.'[filter]');

		$d['filter']['object']['type']            = 'htmlobject_input';
		$d['filter']['object']['attrib']['type']  = 'text';
		$d['filter']['object']['attrib']['name']  = $response->prefix.'[filter]';
		$d['filter']['object']['attrib']['value'] = $filter;
		$d['filter']['object']['attrib']['size']  = '10';

		$d['submit']['object']['type']            = 'htmlobject_input';
		$d['submit']['object']['attrib']['type']  = 'submit';
		$d['submit']['object']['attrib']['name']  = 'send';
		$d['submit']['object']['attrib']['css']   = 'btn btn-default';
		$d['submit']['object']['attrib']['value'] = $response->lang['folder']['lang_filter'];

		$form->add($d);
		return $form;

	}

}
?>
