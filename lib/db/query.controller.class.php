<?php
/**
 * query_controller
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2015, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class query_controller
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'query_action';
/**
* name identifier
* @access public
* @var string
*/
var $identifier_name = 'query_ident';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'query_msg';

var $tpldir;

var $lang = array(
	'label_bestand' => 'bestand #%s',
);

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param file_handler $phppublisher
	 * @param htmlobject_response $response
	 * @param query $db
	 * @param user $user
	 */
	//--------------------------------------------
	function __construct($file, $response, $db, $user) {
		#$this->controller = $controller;
		$this->file = $file;
		$this->response = $response;
		$this->db = $db;
		$this->user = $user;
		#$this->lang = $this->user->translate($this->lang, CLASSDIR.'plugins/bestand/lang/', 'bestand.ini');
		$this->classdir = CLASSDIR.'lib/db/';

		$this->dbtables = array();
		$result = $this->db->handler()->query('SHOW TABLES');
		if(is_array($result)) {
			foreach($result as $table) {
				foreach($table as $v) {
					$this->dbtables[] = $v;
					break;
				}
			}
		}
		$this->dbtable = $this->db->handler->escape($this->response->html->request()->get('dbtable'));
		if($this->dbtable === '' && isset($this->dbtables[0])) {
			$this->dbtable = $this->dbtables[0];
		}
		$this->response->add('dbtable', $this->dbtable);
	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 * @param string $action
	 * @return htmlobject_tabmenu
	 */
	//--------------------------------------------
	function action($action = null) {
		$this->action = '';
		$ac = $this->response->html->request()->get($this->actions_name);
		if(is_array($ac)) {
			$this->action = key($ac);
		}
		else if($ac !== '') {
			$this->action = $ac;
		}
		else if($ac === '') {
			$this->action = 'select';
		}
		if($this->response->cancel()) {
			if(
				$this->action === 'addrow' || 
				$this->action === 'updaterow' || 
				$this->action === 'delrow'
			) {
				$this->action = 'select';
			}
		}
		if(isset($action)) {
			$this->action = $action;
		}
	
		$tabs = array();
		switch( $this->action ) {
			case '':
			default:
			case 'select':
				$content[] = $this->export();
				$content[] = $this->import();
				$content[] = $this->table();
				$content[] = $this->column();
				$content[] = $this->select(true);
			break;
			case 'addrow':
				$content[] = $this->export();
				$content[] = $this->import();
				$content[] = $this->table();
				$content[] = $this->column();
				$content[] = $this->addrow(true);
			break;
			case 'updaterow':
				$content[] = $this->export();
				$content[] = $this->import();
				$content[] = $this->table();
				$content[] = $this->column();
				$content[] = $this->updaterow(true);
			break;
			case 'delrow':
				$content[] = $this->export();
				$content[] = $this->import();
				$content[] = $this->table();
				$content[] = $this->column();
				$content[] = $this->delrow(true);
			break;
			case 'column':
				$content[] = $this->export();
				$content[] = $this->import();
				$content[] = $this->table();
				$content[] = $this->column(true);
				$content[] = $this->select();
			break;
			case 'table':
				$content[] = $this->export();
				$content[] = $this->import();
				$content[] = $this->table(true);
				$content[] = $this->column();
				$content[] = $this->select();
			break;
			case 'import':
				$content[] = $this->export();
				$content[] = $this->import(true);
				$content[] = $this->table();
				$content[] = $this->column();
				$content[] = $this->select();
			break;
			case 'export':
				$content[] = $this->export(true);
				$content[] = $this->import();
				$content[] = $this->table();
				$content[] = $this->column();
				$content[] = $this->select();
			break;
		}

		$tab = $this->response->html->tabmenu('query_tab');
		$tab->message_param = $this->message_param;
		$tab->css = 'htmlobject_tabs right';
		$tab->auto_tab = false;
		$tab->add($content);

		$t = $this->response->html->template($this->tpldir.'/query.controller.html');
		#$t->add($this->response->html->thisfile, 'thisfile');
		$t->add($tab->get_string(), 'content');
		$t->add('Datenbank <i>'.$this->db->db.'</i>', 'headline');
		return $t;
	}

	//--------------------------------------------
	/**
	 * Select
	 *
	 * @access public
	 * @return array
	 */
	//--------------------------------------------
	function select($visible = false) {
		$data = '';
		if($visible === true) {
			require_once($this->classdir.'query.select.class.php');
			$controller = new query_select($this);
			$controller->actions_name = $this->actions_name;
			$controller->message_param = $this->message_param;
			$controller->tpldir = $this->tpldir;
			$controller->lang  = $this->lang;
			$data = $controller->action();
		}
		$content['label']   = 'Select';
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'select' );
		$content['onclick'] = false;
		if($this->action === 'select'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Add column
	 *
	 * @access public
	 * @return array
	 */
	//--------------------------------------------
	function column($visible = false) {
		$data = '';
		if($visible === true) {
			require_once($this->classdir.'query.column.controller.class.php');
			$controller = new query_column_controller($this);
			$controller->tpldir = $this->tpldir;
			$controller->lang  = $this->lang;
			$data = $controller->action();
		}
		$content['label']   = 'Columns';
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'column' );
		$content['onclick'] = false;
		if($this->action === 'column'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Add table
	 *
	 * @access public
	 * @return array
	 */
	//--------------------------------------------
	function table($visible = false) {
		$data = '';
		if($visible === true) {
			require_once($this->classdir.'query.table.controller.class.php');
			$controller = new query_table_controller($this);
			$controller->tpldir = $this->tpldir;
			$controller->lang  = $this->lang;
			$data = $controller->action();
		}
		$content['label']   = 'Tables';
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'table' );
		$content['onclick'] = false;
		if($this->action === 'table'){
			$content['active']  = true;
		}
		return $content;
	}


	//--------------------------------------------
	/**
	 * Add Row
	 *
	 * @access public
	 * @return array
	 */
	//--------------------------------------------
	function addrow($visible = false) {
		$data = '';
		if($visible === true) {
			require_once($this->classdir.'query.addrow.class.php');
			$controller = new query_addrow($this);
			$controller->actions_name = $this->actions_name;
			$controller->message_param = $this->message_param;
			$controller->tpldir = $this->tpldir;
			$controller->lang  = $this->lang;
			$data = $controller->action();
		}
		$content['label']   = 'Select';
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'select' );
		$content['onclick'] = false;
		#$content['hidden']  = true;
		if($this->action === 'addrow'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Import
	 *
	 * @access public
	 * @return array
	 */
	//--------------------------------------------
	function import($visible = false) {
		$data = '';
		if($visible === true) {
			require_once($this->classdir.'query.import.controller.class.php');
			$controller = new query_import_controller($this);
			$controller->tpldir = $this->tpldir;
			$controller->lang  = $this->lang;
			$data = $controller->action();
		}
		$content['label']   = 'Import';
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'import' );
		$content['onclick'] = false;
		if($this->action === 'import'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Export
	 *
	 * @access public
	 * @return array
	 */
	//--------------------------------------------
	function export($visible = false) {
		$data = '';
		if($visible === true) {
			require_once($this->classdir.'query.export.controller.class.php');
			$controller = new query_export_controller($this);
			$controller->tpldir = $this->tpldir;
			$controller->lang  = $this->lang;
			$data = $controller->action();
		}
		$content['label']   = 'Export';
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'export' );
		$content['onclick'] = false;
		if($this->action === 'export'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Del row
	 *
	 * @access public
	 * @return array
	 */
	//--------------------------------------------
	function delrow($visible = false) {
		$data = '';
		if($visible === true) {
			require_once($this->classdir.'query.delrow.class.php');
			$controller = new query_delrow($this);
			$controller->actions_name = $this->actions_name;
			$controller->message_param = $this->message_param;
			$controller->tpldir = $this->tpldir;
			$controller->lang  = $this->lang;
			$data = $controller->action();
		}
		$content['label']   = 'Select';
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'select' );
		$content['onclick'] = false;
		#$content['hidden']  = true;
		if($this->action === 'delrow'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Update row
	 *
	 * @access public
	 * @return array
	 */
	//--------------------------------------------
	function updaterow($visible = false) {
		$data = '';
		if($visible === true) {
			require_once($this->classdir.'query.updaterow.class.php');
			$controller = new query_updaterow($this);
			$controller->actions_name = $this->actions_name;
			$controller->message_param = $this->message_param;
			$controller->tpldir = $this->tpldir;
			$controller->lang  = $this->lang;
			$data = $controller->action();
		}
		$content['label']   = 'Select';
		$content['value']   = $data;
		$content['target']  = $this->response->html->thisfile;
		$content['request'] = $this->response->get_array($this->actions_name, 'select' );
		$content['onclick'] = false;
		#$content['hidden']  = true;
		if($this->action === 'updaterow'){
			$content['active']  = true;
		}
		return $content;
	}

	//--------------------------------------------
	/**
	 * Get columns
	 *
	 * @access public
	 * @return array
	 */
	//--------------------------------------------
	function __get_columns($table) {
		$columns = $this->db->handler()->columns($this->db->db, $table);
		return $columns;
	}

	//--------------------------------------------
	/**
	 * Get columns info table
	 *
	 * @access protected
	 * @param object formbuilder $form
	 * @param string $table
	 * @param enum[edit|export|select|form] $mode
	 * @return string
	 */
	//--------------------------------------------
	function __get_columns_info($form, $database, $mode = '') {

		$form->css = 'form-horizontal';

		$tables = array();
		if(count($this->dbtables) > 0) {
			foreach($this->dbtables as $table) {
				$tables[] = array($table);
			}

			$d['tables']['label'] = '<a onclick="toggle_columns(\'columns_table\'); return false;" id="toggle_columns" href="#">Table</a>';
			$d['tables']['css']   = 'autosize';
			$d['tables']['style'] = 'margin: 0 0 15px 0;';
			if($mode === 'edit') {
				$d['tables']['label'] = 'Table';
			}
			else if($mode === 'select') {
				$d['tables']['label'] = 'Table';
				$d['tables']['css']   = null;
				$d['tables']['style'] = 'margin: 0 0 15px -10px;';
			}
			$d['tables']['object']['type']               = 'htmlobject_select';
			$d['tables']['object']['attrib']['index']    = array(0,0);
			$d['tables']['object']['attrib']['name']     = 'dbtable';
			$d['tables']['object']['attrib']['id']       = 'dbtable';
			$d['tables']['object']['attrib']['options']  = $tables;
			$d['tables']['object']['attrib']['css']      = 'input-sm';
			$d['tables']['object']['attrib']['selected'] = array($database);
			$d['tables']['object']['attrib']['handler']  = 'onmousedown="phppublisher.select.submit=true;phppublisher.select.init(this);return false;"';

			// form hack
			if($mode === 'form') {
				$d['tables']['label'] = 'Table';
				$d['tables']['css']   = null;
				$d['tables']['style'] = null;
				$form->add($d);
				return $form;
			}

			$form->add($d);

			$description  = '<script>';
			$description .= 'function toggle_columns(element) {';
			$description .= '	table = document.getElementById(element);';
			$description .= '	if(table.style.display === \'block\') {';
			$description .= '		table.style.display = "none"';
			$description .= '	} else {';
			$description .= '		table.style.display = "block";';
			$description .= '	}';
			$description .= '}';
			$description .= '</script>';
			if($mode === 'export') {
				$description .= $form->get_elements('tables')->get_string();
			} else {
				$description .= $form->get_string();
			}
			if($mode === 'edit') {
				$description .= '<div id="columns_table">';
			} else {
				$description .= '<div id="columns_table" style="display:none;">';
			}
			$description .= '<div style="float:left;">';
			$description .= '<table class="table table-bordered" style="float:left;">';
			$description .= '<tr>';
			$description .= '<th>column</td>';
			$description .= '<th class="type">type</th>';
			$description .= '<th class="length">length</th>';
			$description .= '<th class="null">null</th>';
			$description .= '<th class="default">default</th>';
			$description .= '<th class="extra">&#160;</th>';
			if($mode === 'export' || $mode === 'edit') {
				$description .= '<th class="ident">&#160;</th>';
			}
			$description .= '</tr>';

### TODO check
			$columns = $this->__get_columns($database);

			foreach($columns as $column) {
				$description .= '<tr>';
				$description .= '<td>'.$column['column'].'</td>';
				$description .= '<td class="type">'.$column['type'].'</td>';
				$description .= '<td class="length">'.$column['length'].'</td>';
				$description .= '<td class="null">'.$column['null'].'</td>';
				$description .= '<td class="default">'.$column['default'].'</td>';
				$description .= '<td class="extra">'.$column['extra'].'</td>';
				if($mode === 'export') {
					$checked = '';
					if($column['extra'] !== 'auto_increment') {
						$checked = ' checked="checked"';
					}
					$description .= '<td class="ident"><input type="checkbox"'.$checked.' name="field['.$column['column'].']"></td>';
				}
				if($mode === 'edit') {
					if($column['extra'] !== 'auto_increment') {
						$link = $this->response->html->a();
						$link->label = 'edit';
						$link->href = $this->response->get_url($this->actions_name,'column').'&query_column_action=edit&column='.$column['column'];
						$link->handler = 'onclick="phppublisher.wait();"';
						$description .= '<td class="action">'.$link->get_string().'</td>';
					} else {
						$description .= '<td>&#160;</td>';
					}
				}
				$description .= '</tr>';
			}
			$description .= '</table>';
			$description .= '</div>';
			if($mode === 'export') {

				$description .= '<div style="float:left;text-align: right;margin: 0 0 0 30px;">';

				$description .= '<div class="htmlobject_box autosize form-group" style="float:right;">';
				$description .= '<div class="left">';
				$description .= '<label class="control-label" for="delimiter">Delimiter</label>';
				$description .= '</div><div class="right">';
				$description .= '<select class="form-control" id="delimiter" name="export[delimiter]" style="width:60px;">';
				$description .= '<option value=",">,</option>';
				$description .= '<option value=";" selected="selected">;</option>';
				$description .= '<option value="\t">\t</option>';
				$description .= '</select>';
				$description .= '</div></div>';

				$description .= '<div class="htmlobject_box autosize form-group" style="clear:both; float:right;">';
				$description .= '<div class="left">';
				$description .= '<label class="control-label" for="enclosure">Enclosure</label>';
				$description .= '</div><div class="right">';
				$description .= '<select class="form-control" id="enclosure" name="export[enclosure]" style="width:60px;">';
				$description .= '<option value=""></option>';
				$description .= '<option value="\'">\'</option>';
				$description .= '<option value="quot" selected="selected">&#34;</option>';
				$description .= '</select>';
				$description .= '</div></div>';

				$description .= '<div class="htmlobject_box autosize form-group" style="clear:both; float:right;">';
				$description .= '<div class="left">';
				$description .= '<label class="control-label" for="linefeed">Linefeed</label>';
				$description .= '</div><div class="right">';
				$description .= '<select class="form-control" id="linefeed" name="export[linefeed]" style="width:60px;">';
				$description .= '<option value="'."\n".'">\n</option>';
				$description .= '<option value="'."\r\n".'" selected="selected">\r\n</option>';
				$description .= '</select>';
				$description .= '</div></div>';

				$description .= '<div class="htmlobject_box autosize" style="clear:both; float:right;">';
				$description .= '<div class="left">';
				$description .= '<label class="control-label" for="bom">BOM</label>';
				$description .= '</div><div class="right">';
				$description .= '<input type="checkbox" checked="checked" class="form-control checkbox" id="bom" name="export[bom]">';
				$description .= '</div></div>';

				$description .= '<div class="floatbreaker">&#160;</div>';

				$a = $this->response->html->button();
				$a->css = 'btn btn-default export float-right';
				$a->name = 'doexport';
				$a->value = 'export';
				$a->type = 'submit';
				$a->label = 'Export';

				$description .= $a->get_string();
				$description .= '</div>';
			}
			$description .= '<div class="floatbreaker">&#160;</div>';
			$description .= '</div>';
 		} else {
			$description = '';
		}
		return $description;
	}

}
?>
