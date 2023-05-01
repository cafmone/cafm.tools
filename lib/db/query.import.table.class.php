<?php
/**
 * query_import_table
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2015, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class query_import_table
{
/**
* name of action buttons
* @access public
* @var string
*/
#var $actions_name = 'bestand_import_action';

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
		$this->columns    = $this->controller->controller->__get_columns($this->dbtable);

		$this->delimiter = $this->response->html->request()->get('delimiter');
		if($this->delimiter !== '') {
			$this->response->add('delimiter',$this->delimiter);
		} else {
			$this->delimiter = ';';
		}

		$this->enclosure = $this->response->html->request()->get('enclosure', true);
		if(isset($this->enclosure)) {
			$this->response->add('enclosure',$this->enclosure);
		} else {
			$this->enclosure = 'quot';
		}

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
			require_once(CLASSDIR.'/lib/phpcommander/phpcommander.upload.class.php');
			$xresponse = $this->response->response();
			$xresponse->id = 'xresponse';
			$xresponse->add($this->actions_name,'table');
			$commander = new phpcommander_upload(PROFILESDIR.'/import', $xresponse, $this->file);
			$commander->actions_name = $this->dbtable.'_upload';
			$commander->message_param = $this->message_param;
			$commander->tpldir = CLASSDIR.'/lib/phpcommander/templates';
			$commander->allow_replace = true;
			$commander->allow_create = true;
			$commander->accept = '.csv';
			$commander->filename = $this->dbtable.'.csv';
			$upload = $commander->get_template();

			$response = $this->get_response();
			$content  = $this->check_import();

			if($this->response->html->request()->get('import') === 'true') {
				if(is_array($content)) {
					$errors = array();
					foreach($content as $k => $v) {
						$result = $this->db->insert($this->dbtable, $v);
						if($result !== '') {
							$errors[] = $result;
						} else {
							unset($content[$k]);
						}
					}
					if(is_array($errors) && count($errors) > 0) {
						$response->error = implode('<br>', $errors);

### TODO better Error handling
/*
						if(is_array($content) && count($content) > 0) {
							$delimiter = $this->delimiter;
							if($delimiter === '\t' || $delimiter === 'tab') {
								$delimiter = chr(9);
							}
							var_dump($delimiter);
							exit();
							$handle = fopen(PROFILESDIR.'/import/'.$this->dbtable.'.csv', 'w');
							foreach($content as $v) {
								if($this->enclosure !== '') {
									fputcsv ($handle, $v, $delimiter, $this->enclosure);
								} else {
									fputcsv ($handle, $v, $delimiter);
								}
							}
							fclose($handle);
						} else {
							$this->file->remove(PROFILESDIR.'/import/'.$this->dbtable.'.csv');
						}
*/
					} else {
						$this->file->remove(PROFILESDIR.'/import/'.$this->dbtable.'.csv');
						$response->msg = 'file imported';
					}
				}
			} else {
				$response = $this->get_response();
			}
	
			if(!isset($response->msg)) {
				// redirect on error
				if(isset($response->error)) {
					$this->response->redirect(
						$this->response->get_url(
							$this->actions_name, 'table', $this->message_param.'[error]', $response->error
						)
					);
				}

				$form = $this->response->get_form($this->actions_name, 'table', false);
				$description = $this->controller->controller->__get_columns_info(
						$form,
						$this->dbtable,
						'');

				$t = $this->response->html->template($this->tpldir.'/query.import.table.html');
				$t->add($description, 'description');
				$t->add($this->response->html->thisfile, 'thisfile');
				$t->add($response->form);
				if(is_object($upload)) {
					$t->add($upload, 'upload');
				}
				else if (is_array($upload)) {
					print_r($upload);
				}

				if(is_string($content)) {
					$t->add($content, 'content');
					$t->add('', 'import');
				} 
				else if(is_array($content)) {
					$table = $this->get_table($content, $response);
					$t->add($table, 'content');
					$a = $response->html->a();
					$a->css = 'btn btn-default';
					$a->label = 'import';
					$a->href  = $this->response->get_url($this->actions_name, 'table').'&import=true';
					$t->add($a, 'import');
				}
				$t->group_elements(array('param_' => 'form'));
				return $t;
			} else {
				$this->response->redirect(
					$this->response->get_url(
						$this->actions_name, 'table', $this->message_param, $response->msg
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
	 * Response
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function get_response() {
		$response = $this->response;
		$form     = $response->get_form($this->actions_name, 'table');

		$submit = $form->get_elements('submit');
		$submit->value = "refresh";
		$form->add($submit, 'submit');

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
		$d['delimiter']['object']['attrib']['name']    = 'delimiter';
		$d['delimiter']['object']['attrib']['id']      = 'delimiter';
		$d['delimiter']['object']['attrib']['style']   = 'width:60px;';
		$d['delimiter']['object']['attrib']['css']     = 'input-sm';
		$d['delimiter']['object']['attrib']['title']   = 'Column separator';
		$d['delimiter']['object']['attrib']['handler'] = 'onchange="phppublisher.wait();this.form.submit();"';
		if(isset($this->delimiter)) {
			$d['delimiter']['object']['attrib']['selected'] = array($this->delimiter);
		} else {
			$d['delimiter']['object']['attrib']['selected'] = array(';');
		}

		$o = array();
		$o[] = array('empty','');
		$o[] = array("'","'");
		$o[] = array('quot','&#34;');

		$d['enclosure']['label']                       = 'Enclosure';
		$d['enclosure']['css']                         = 'autosize';
		$d['enclosure']['style']                         = 'float:right;';
		$d['enclosure']['object']['type']              = 'htmlobject_select';
		$d['enclosure']['object']['attrib']['index']   = array(0,1);
		$d['enclosure']['object']['attrib']['options'] = $o;
		$d['enclosure']['object']['attrib']['name']    = 'enclosure';
		$d['enclosure']['object']['attrib']['id']      = 'enclosure';
		$d['enclosure']['object']['attrib']['style']   = 'width:60px;';
		$d['enclosure']['object']['attrib']['css']     = 'input-sm';
		$d['enclosure']['object']['attrib']['title']   = 'Field enclosure';
		$d['enclosure']['object']['attrib']['handler'] = 'onchange="phppublisher.wait();this.form.submit();"';
		if(isset($this->enclosure)) {
			$d['enclosure']['object']['attrib']['selected'] = array($this->enclosure);
		} 

		$form->add($d);
		$form->display_errors = false;
		$response->form = $form;

		return $response;
	}

	//--------------------------------------------
	/**
	 * Table
	 *
	 * @access public
	 * @return htmlobject_tablebuilder
	 */
	//--------------------------------------------
	function get_table($content,$response) {
		$h = array();
		$sort = '';
		foreach($this->columns as $k => $column) {
			if(isset($column['extra']) && $column['extra'] === 'auto_increment' ) {
				// do nothing
			} else {
				$h[$column['column']]['title'] = $column['column'];
				if($sort === '') {
					$sort = $column['column'];
				}
			}
		}
		reset($this->columns);

		$resar = $response->get_array();
		$resar[$this->actions_name] = 'table';

		$table              = $this->response->html->tablebuilder( 'import_table', $resar );
		$table->sort        = $sort;
		$table->order       = 'ASC';
		$table->limit       = 100;
		$table->offset      = 0;
		$table->max         = count($content);
		$table->css         = 'htmlobject_table table table-bordered';
		$table->border      = 0;
		$table->id          = 'import_table';
		$table->handler_tr  = '';
		$table->form_action = $this->response->html->thisfile;
		$table->sort_form   = true;
		$table->sort_link   = false;
		$table->autosort    = true;
		$table->head        = $h;
		$table->body        = $content;
		$table->limit_select = array(
				array("value" => 100, "text" => 100),
				array("value" => 200, "text" => 200),
				array("value" => 500, "text" => 500)
			);

		return $table;
	}

	//--------------------------------------------
	/**
	 * Check Import
	 *
	 * @access public
	 * @return string|empty
	 */
	//--------------------------------------------
	function check_import() {
		$output = array();
		$error  = "";
		if($this->file->exists(PROFILESDIR.'/import/'.$this->dbtable.'.csv')) {
			$data  = str_replace("\r\n","\n",$this->file->get_contents(PROFILESDIR.'/import/'.$this->dbtable.'.csv'));
			$data  = $this->file->remove_utf8_bom($data);
			$lines = explode("\n", $data);
			if(is_array($lines)) {
				foreach($lines as $i => $line) {
					if($line !== '') {
						$tmp = array();
						$delim = $this->delimiter;
						if($delim === 'tab' || $delim === '\t') {
							$delim = "\t";
						}
						$fields = explode($delim,$line);
						$k = 0;
						foreach($this->columns as $column) {
							if(isset($column['extra']) && $column['extra'] === 'auto_increment' ) {
								#$tmp[$column['column']] = '&#160;';
							} else {
								if(isset($fields[$k])) {
									// remove enclosure
									$value = $fields[$k];
									if(isset($this->enclosure) && $this->enclosure !== 'empty') {
										$enclosure = $this->enclosure;
										if( $enclosure === 'quot') {
											$enclosure = '"';
										}
										$value = preg_replace('~^['.$enclosure.'](.*)['.$enclosure.']$~i', '$1', $value);
									}
									$length = $column['length'];
									if (preg_match('!\S!u', $value)) {
										$value = utf8_decode($value);
									}
									if(strlen($value) > $length) {
										$error .= 'Error: Wrong length ('.strlen($value).' > '.$length.') in column '.($i+1).' for '.$column['column'].' on line '.($i+1).'<br>';
									} else {
										$tmp[$column['column']] = $value;
									}
								} else {
									if($column['null'] === 'no') {
										$error .= 'Error: Missing value for '.$column['column'].' expected in column '.($k+1).' on line '.($i+1).'<br>';
									} else {
										$tmp[$column['column']] = '';
									}
								}
								$k++;
							}
						}
						$output[] = $tmp;
					}
				}
			} else {
				$error .= 'Data is not an array';
			}
		} else {
			$error .= 'No file uploaded yet';
		}

		if($error === '') {
			return $output;
		} else {
			return $error;
		}
	}

}
?>
