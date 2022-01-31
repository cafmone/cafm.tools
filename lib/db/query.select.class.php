<?php
/**
 * bestandsverwaltung_select
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2015, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class query_select
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'query_action';

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
		$this->dbtable    = $controller->dbtable;
		$this->file       = $controller->file;
		$this->response   = $controller->response;
		$this->controller = $controller;
		$this->columns     = $this->controller->__get_columns($this->dbtable);

		$this->filter = $this->response->html->request()->get('filter');
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
		$count = $this->db->handler->query('SELECT COUNT(*) as num FROM `'.$this->dbtable.'`');
		if(is_array($count)) {
			$count = $count[0]['num'];

			$form = $this->response->get_form($this->actions_name, 'select', false);
			$description = $this->controller->__get_columns_info(
					$form,
					$this->dbtable,
					'export');

			$t = $this->response->html->template($this->tpldir.'/query.select.html');
			$t->add($this->response->html->thisfile, 'thisfile');
			$t->add($description, 'description');
			$t->add($this->__get_response($this->columns));

			$a = $this->response->html->a();
			$a->css = 'btn btn-sm btn-default flush';
			$a->label = 'Flush';
			$a->title = 'delete all rows';
			$a->handler = 'onclick="phppublisher.wait();"';
			$a->href  = $this->response->get_url($this->actions_name,'delrow');
			$a->href .= '&'.$this->controller->identifier_name.'[AllRows]=all';

			$i = $this->response->html->a();
			$i->css = 'btn btn-sm btn-default insert';
			$i->label = 'Insert';
			$i->title = 'add a new row';
			$i->handler = 'onclick="phppublisher.wait();"';
			$i->href  = $this->response->get_url($this->actions_name,'addrow');

			$t->add($a,'flush');
			$t->add($i,'insert');
			$t->add($this->__get_table($this->columns, $count), 'table');
			$t->group_elements(array('param_' => 'form'));
			$t->group_elements(array('filter_' => 'filter'));
			return $t;
		}
		else if(is_string($count)) {
			return $count;
		}
	}

	//--------------------------------------------
	/**
	 * Build Table
	 *
	 * @access protected
	 * @param array $column
	 * @param integer $count
	 * @return htmlobject_tablebuilder
	 */
	//--------------------------------------------
	function __get_table($columns, $count = '') {

		// export mode?
		if($this->response->html->request()->get('doexport') !== '') {
			$f = $this->response->html->request()->get('field');
			if(is_array($f)) {
				$exports = $f;
			}
		}

		$h = array();
		foreach($columns as $k => $column) {
			if(isset($column['extra']) && $column['extra'] === 'auto_increment') {
				$identifier = $column['column'];
			}
			$h[$column['column']]['title'] = $column['column'];
		}
		if(isset($identifier)) {
			$h['action']['title'] = '&#160;';
			$h['action']['sortable'] = false;
		}

		$table           = $this->response->html->tablebuilder( 'qt', $this->response->get_array() );
		$table->sort     = key($columns);
		$table->order    = 'ASC';
		$table->limit    = 100;
		$table->offset   = 0;
		$table->max      = $count;
		$table->head     = $h;
		$table->autosort = false;
		$table->init();

		$href_update = $this->response->get_url($this->actions_name,'updaterow');
		$tparams = $table->get_params_string();

		if(is_array($this->filter) && count($this->filter) > 0) {
			$i = 0;
			$sql = 'SELECT * FROM `'.$this->dbtable.'` WHERE ';
			foreach($this->filter as $k => $v) {
				if(
					(isset($v['value']) && $v['value'] !== '') || 
					(isset($v['operator']) && $v['operator'] === 'ISNULL'))
				{
					$f = $this->db->handler()->escape($v['field']);
					$n = '';
					if(isset($v['not'])) {
						$n = $this->db->handler()->escape($v['not']);
					}
					$o = $this->db->handler()->escape($v['operator']);
					if($o === 'equal') {
						$o = '=';
					}
					$x = '';
					if(isset($v['value'])) {
						$x = $this->db->handler()->escape($v['value']);
					}
					if($i > 0) {
						if($o === 'ISNULL') {
							$sql .= ' AND '.$n.' `'.$f.'` IS NULL';
						} else {
							$sql .= ' AND '.$n.' `'.$f.'` '.$o.' \''.$x.'\'';
						}
					} else {
						if($o === 'ISNULL') {
							$sql .= ' '.$n.' `'.$f.'` IS NULL';
						} else {
							$sql .= ' '.$n.' `'.$f.'` '.$o.' \''.$x.'\'';
						}
					}
					$i++;
				}
			}
			$sql .= ' ORDER BY `'.$table->sort.'` '.$table->order;
			$content = $this->db->handler()->query($sql);

			// TODO Error string
			if(is_array($content)) {
				$table->max = count($content);
				$table->autosort = true;
			} else {
				$table->max = 0;
			}
		} else {
			if(isset($exports)) {
				$content = $this->db->select($this->dbtable, '*', null, '`'.$table->sort.'` '.$table->order);
			} else {
				$content = $this->db->select($this->dbtable, '*', null, '`'.$table->sort.'` '.$table->order, $table->offset.','.$table->limit);
			}
		}

		if(!is_array($content)) {
			// handle db error
			if(is_string($content) && $content !== '') {
				$_REQUEST[$this->message_param] = $content;
			}
			$content = array();
		}

		if(isset($exports)) {
			// this might exit the script
			$error = $this->__export($content);
			if($error !== '') {
				$_REQUEST[$this->message_param]['error'] = $error;
			}
		}

		// add update button to content
		if(isset($identifier)) {
			foreach($content as $k => $v) {
				$a = $this->response->html->a();
				$a->css = 'update';
				$a->label = 'update';
				$a->handler = 'onclick="phppublisher.wait();"';
				$a->href  = $href_update;
				$a->href .= '&'.$this->controller->identifier_name.'['.$identifier.'][]='.$v[$identifier];
				$a->href .= $tparams;
				$content[$k]['action'] = $a;
			}
		}

		$table->css       = 'htmlobject_table table table-bordered';
		$table->border    = 0;
		$table->id        = 'db_table';
		$table->sort_form = true;
		$table->sort_link = false;
		$table->body      = $content;
		$table->limit_select = array(
				array("value" => 100, "text" => 100),
				array("value" => 200, "text" => 200),
				array("value" => 500, "text" => 500)
			);
		if(isset($identifier)) {
			$table->identifier      = $identifier;
			$table->identifier_name = $this->controller->identifier_name.'['.$identifier.']';
			$table->actions         = array(array('delrow' => 'delete'),array('updaterow' => 'update'));
			$table->actions_name    = $this->actions_name;
		}
		return $table->get_string();
	}

	//--------------------------------------------
	/**
	 * Get Response
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function __get_response($columns) {
		$response = $this->response;
		$form = $response->get_form($this->actions_name, 'select', false);

		$d['tpl_field']['object']['type']              = 'htmlobject_select';
		$d['tpl_field']['object']['attrib']['index']   = array('column','column');
		$d['tpl_field']['object']['attrib']['options'] = $columns;
		$d['tpl_field']['object']['attrib']['id']      = 'tpl_field';
		$d['tpl_field']['object']['attrib']['style']   = 'width:auto;display:inline;';
		$d['tpl_field']['object']['attrib']['name']    = 'tpl[0][field]';
		$d['tpl_field']['object']['attrib']['css']     = 'input-sm';

		$not[] = array('');
		$not[] = array('NOT');

		$d['tpl_not']['object']['type']              = 'htmlobject_select';
		$d['tpl_not']['object']['attrib']['index']   = array(0,0);
		$d['tpl_not']['object']['attrib']['options'] = $not;
		$d['tpl_not']['object']['attrib']['id']      = 'tpl_not';
		$d['tpl_not']['object']['attrib']['style']   = 'width:auto;display:inline;';
		$d['tpl_not']['object']['attrib']['css']     = 'input-sm';
		$d['tpl_not']['object']['attrib']['name']    = 'tpl[0][notd]';

		$operators[] = array('equal','=');
		$operators[] = array('LIKE','LIKE');
		$operators[] = array('ISNULL','NULL');

		$d['tpl_operator']['object']['type']              = 'htmlobject_select';
		$d['tpl_operator']['object']['attrib']['index']   = array(0,1);
		$d['tpl_operator']['object']['attrib']['options'] = $operators;
		$d['tpl_operator']['object']['attrib']['id']      = 'tpl_operator';
		$d['tpl_operator']['object']['attrib']['style']   = 'width:auto;display:inline;';
		$d['tpl_operator']['object']['attrib']['name']    = 'tpl[0][operator]';
		$d['tpl_operator']['object']['attrib']['css']     = 'input-sm';

		$d['tpl_value']['object']['type']              = 'htmlobject_input';
		$d['tpl_value']['object']['attrib']['id']      = 'tpl_value';
		$d['tpl_value']['object']['attrib']['style']   = 'width:auto;display:inline;';
		$d['tpl_value']['object']['attrib']['name']    = 'tpl[0][value]';
		$d['tpl_value']['object']['attrib']['css']     = 'input-sm';

		// handle filter
		if(is_array($this->filter) && count($this->filter) > 0) {
			$i = 0;
			foreach($this->filter as $k => $v) {

				// unset double entry from response
				$form->remove('filter['.$k.'][div]');
				$form->remove('filter['.$k.'][field]');
				$form->remove('filter['.$k.'][not]');
				$form->remove('filter['.$k.'][operator]');
				$form->remove('filter['.$k.'][value]');

				if(
					(isset($v['value']) && $v['value'] !== '') || 
					(isset($v['operator']) && $v['operator'] === 'ISNULL'))
				{

					$div = $this->response->html->div();
					$div->name = 'filter['.$k.'][div]';
					$div->id = $k;

					$f = $this->response->html->select();
					$f->name    = 'filter['.$k.'][field]';
					$f->style = 'width:auto;display:inline;';
					$f->css = 'form-control input-sm';
					$f->selected = array($v['field']);
					$f->add($columns, array('column','column'));

					$n = $this->response->html->select();
					$n->name     = 'filter['.$k.'][not]';
					$n->css = 'form-control input-sm';
					$n->style = 'width:auto;display:inline;';
					if(isset($v['not'])) {
						$n->selected = array($v['not']);
					}
					$n->add($not, array(0,0));

					$o = $this->response->html->select();
					$o->name     = 'filter['.$k.'][operator]';
					$o->selected = array($v['operator']);
					$o->css = 'form-control input-sm';
					$o->style = 'width:auto;display:inline;';
					$o->add($operators, array(0,1));

					$x = $this->response->html->input();
					$x->name    = 'filter['.$k.'][value]';
					$x->css = 'form-control input-sm';
					$x->style = 'width:auto;display:inline;';
					if(isset($v['value'])) {
						$x->value   = $v['value'];
					}

					$a = $this->response->html->a();
					$a->label = '-';
					$a->style = 'margin: 0 0 0 5px;';
					$a->css = 'btn btn-sm btn-default';
					$a->handler = 'onclick="filter.remove(\''.$k.'\');"';
					$a->href = '#';

					$div->add($f);
					$div->add($n);
					$div->add($o);
					$div->add($x);
					$div->add($a);

					$d['filter_'.$i]['object'] = $div;

					$i++;
				} else {
					// unset filter
					unset($this->filter[$k]);
				}
			}
		}

		// add filter to response
		if(is_array($this->filter) && count($this->filter) > 0) {
			$this->response->add('filter',$this->response->html->request()->get('filter'));
		} else {
			$d['filter_x0'] = '';
			$this->filter = '';
		}

		$form->display_errors = false;
		$form->add($d);
		return $form;
	}

	//--------------------------------------------
	/**
	 * Export
	 *
	 * @access public
	 * @param array $data
	 */
	//--------------------------------------------
	function __export($data) {

		$export = $this->response->html->request()->get('export');
		$fields = $this->response->html->request()->get('field');

		if(is_array($export) && $export !== '' && is_array($fields) && $fields !== '') {

			if(is_array($data)) {

				if(!isset($export['delimiter']) || $export['delimiter'] === '') {
					return 'Error: No delimiter valid delimiter';
				}
				elseif($export['delimiter'] === '\t' || $export['delimiter'] === 'tab') {
					$export['delimiter'] = chr(9);
				}
				if(!isset($export['enclosure'])) {
					$export['enclosure'] = '';
				}
				elseif($export['enclosure'] === 'quot') {
					$export['enclosure'] = '"';
				}

				header("Pragma: public");
				header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
				header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
				header("Cache-Control: must-revalidate");
				header("Content-type: text/csv");
				header("Content-disposition: attachment; filename=".$this->dbtable.".csv");
				header('Content-Transfer-Encoding: binary');
				flush();

				if(isset($export['bom']) && $export['bom'] !== '') {
					echo pack('H*','EFBBBF');
				}

				foreach($data as $values) {
					$m = 0;
					foreach($values as $k => $v) {
						if(array_key_exists($k, $fields)) {
							if($m === 1) {
								echo $export['delimiter'];
							}
							echo $export['enclosure'];
							echo $v;
							echo $export['enclosure'];
							$m = 1;
						}
					}
					echo $export['linefeed'];
				}
				exit(0);

			} else {
				return 'Error: No data to export';
			}

		} else {
			return 'Error: Nothing to do';
		}
	}

}
?>
