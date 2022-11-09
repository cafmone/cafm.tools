<?php
/**
 * bestandsverwaltung_import
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2015 - 2018, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class query_export_table
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = '';

/**
* demlimiter for csv fles
* @access public
* @var string
*/
var $delimiter;
/**
* enclosure for csv fles
* @access public
* @var string
*/
var $enclosure;

var $lang = array();

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param phppublisher $phppublisher
	 * @param htmlobject_response $response
	 */
	//--------------------------------------------
	function __construct($controller) {
		$this->db         = $controller->db;
		$this->file       = $controller->file;
		$this->response   = $controller->response;
		$this->controller = $controller;
		$this->dbtable    = $controller->dbtable;
		$this->columns    = $controller->controller->__get_columns($this->dbtable);
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
		if(is_array($this->columns)) {
			$response = $this->download();
			if(!isset($response->msg)) {
				if(isset($response->error)) {
					$_REQUEST[$this->message_param] = $response->error;
				}

				$form = $this->response->get_form($this->actions_name, 'table', false);
				$description = $this->controller->controller->__get_columns_info(
						$form,
						$this->dbtable,
						'form');

				$t = $this->response->html->template($this->tpldir.'/query.export.table.html');
				$t->add($this->response->html->thisfile, 'thisfile');
				$t->add($description->get_string(), 'description');
				$t->add($response->form);
				$t->group_elements(array('param_' => 'form'));
				$t->group_elements(array('column_' => 'columns'));
				return $t;
			} else {
				$this->response->redirect(
					$this->response->get_url(
						$this->actions_name, 'export', $this->message_param, $response->msg
					)
				);
			}
		}
		else if(is_string($this->columns)) {
			return $this->columns;
		}
	}

	//--------------------------------------------
	/**
	 * download
	 *
	 * @access public
	 * @return htmlobject_form
	 */
	//--------------------------------------------
	function download() {
		$response = $this->get_response();
		$form     = $response->form;
		if(!$form->get_errors() && $response->submit()) {

			$fields = $form->get_request('column');
			$export = $form->get_request('export');

			$error = $this->__export($fields, $export);
			if($error !== '') {
				$response->error = $error;
			}
		}
		else if($form->get_errors()) {
			$response->error = implode('<br>', $form->get_errors());
		}
		return $response;
	}

	//--------------------------------------------
	/**
	 * Get Response
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function get_response() {
		$response = $this->response;
		$form     = $response->get_form($this->actions_name, 'table');
		$d        = array();
		$columns   = $this->columns;
		$i        = 0;
		if(is_array($columns)) {
			foreach($columns as $column) {
				$d['column_'.$column['column']]['label']                       = $column['column'];
				$d['column_'.$column['column']]['object']['type']              = 'htmlobject_input';
				$d['column_'.$column['column']]['object']['attrib']['type']    = 'checkbox';
				$d['column_'.$column['column']]['object']['attrib']['name']    = 'column['.$i.']';
				$d['column_'.$column['column']]['object']['attrib']['value']   = $column['column'];
				if($column['extra'] !== 'auto_increment') {
					$d['column_'.$column['column']]['object']['attrib']['checked'] = true;
				}
				$i++;
			}
		}

		$submit = $form->get_elements('submit');
		$submit->value = "Export";

		$button = $this->response->html->button();
		$button->type = 'submit';
		$button->css = 'btn btn-default';
		$button->name = $submit->name;
		$button->label = 'Export';
		$button->value = $submit->value;

		$form->add($button, 'submit');

		// Export

		$d['bom']['label']                    = 'BOM';
		$d['bom']['css']                      = 'autosize';
		$d['bom']['style']                    = 'float:right;';
		$d['bom']['object']['type']           = 'htmlobject_input';
		$d['bom']['object']['attrib']['type'] = 'checkbox';
		$d['bom']['object']['attrib']['name'] = 'export[bom]';
		$d['bom']['object']['attrib']['checked'] = true;

		$o = array();
		$o[] = array("\n", '\n');
		$o[] = array("\r\n", '\r\n');

		$d['linefeed']['label']                       = 'Linefeed';
		$d['linefeed']['css']                         = 'autosize';
		$d['linefeed']['style']                       = 'float:right;';
		$d['linefeed']['object']['type']              = 'htmlobject_select';
		$d['linefeed']['object']['attrib']['index']   = array(0,1);
		$d['linefeed']['object']['attrib']['options'] = $o;
		$d['linefeed']['object']['attrib']['name']    = 'export[linefeed]';
		$d['linefeed']['object']['attrib']['style']   = 'width:80px;';
		$d['linefeed']['object']['attrib']['css']     = 'input-sm';
		$d['linefeed']['object']['attrib']['selected'] = array("\r\n");

		$o = array();
		$o[] = array(',');
		$o[] = array(';');
		$o[] = array('\t');

		$d['delimiter']['label']                       = 'Delimiter';
		$d['delimiter']['css']                         = 'autosize';
		$d['delimiter']['style']                       = 'float:right;';
		$d['delimiter']['object']['type']              = 'htmlobject_select';
		$d['delimiter']['object']['attrib']['index']   = array(0,0);
		$d['delimiter']['object']['attrib']['options'] = $o;
		$d['delimiter']['object']['attrib']['name']    = 'export[delimiter]';
		$d['delimiter']['object']['attrib']['id']      = 'delimiter';
		$d['delimiter']['object']['attrib']['style']   = 'width:80px;';
		$d['delimiter']['object']['attrib']['css']     = 'input-sm';
		$d['delimiter']['object']['attrib']['title']   = 'Column separator';
		$d['delimiter']['object']['attrib']['selected'] = array(';');

		$o = array();
		$o[] = array('','');
		$o[] = array("'","'");
		$o[] = array('quot','&#34;');

		$d['enclosure']['label']                       = 'Enclosure';
		$d['enclosure']['css']                         = 'autosize';
		$d['enclosure']['style']                         = 'float:right;';
		$d['enclosure']['object']['type']              = 'htmlobject_select';
		$d['enclosure']['object']['attrib']['index']   = array(0,1);
		$d['enclosure']['object']['attrib']['options'] = $o;
		$d['enclosure']['object']['attrib']['name']    = 'export[enclosure]';
		$d['enclosure']['object']['attrib']['id']      = 'enclosure';
		$d['enclosure']['object']['attrib']['style']   = 'width:80px;';
		$d['enclosure']['object']['attrib']['css']     = 'input-sm';
		$d['enclosure']['object']['attrib']['title']   = 'Field enclosure';
		$d['enclosure']['object']['attrib']['selected'] = array('quot');

		$form->add($d);
		$form->display_errors = false;
		$response->form = $form;

		return $response;
	}

	//--------------------------------------------
	/**
	 * Export
	 *
	 * @access public
	 * @param array $fields
	 * @param array $export
	 * @param string $enclosure
	 */
	//--------------------------------------------
	function __export($fields, $export) {

		if($export['delimiter'] === '\t' || $export['delimiter'] === 'tab') {
			$export['delimiter'] = chr(9);
		}
		if(!isset($export['enclosure'])) {
			$export['enclosure'] = '';
		}
		elseif($export['enclosure'] === 'quot') {
			$export['enclosure'] = '"';
		}

		$result = $this->db->select($this->dbtable, $fields);
		if(is_array($result)) {

			header("Pragma: public");
			header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
			header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
			header("Cache-Control: must-revalidate");
			header("Content-type: text/csv; charset=utf-8");
			header("Content-disposition: attachment; filename=".$this->dbtable.".csv");
			header('Content-Transfer-Encoding: binary');
			flush();

			if(isset($export['bom']) && $export['bom'] !== '') {
				echo pack('H*','EFBBBF');
			}

			foreach($result as $values) {
				$m = 0;
				foreach($values as $v) {
					if($m === 1) {
						echo $export['delimiter'];
					}
					echo $export['enclosure'];
					echo $v;
					echo $export['enclosure'];
					$m = 1;
				}
				echo $export['linefeed'];
				
			}
			exit(0);
		} else {
			return $result;
		}
	}

}
?>
