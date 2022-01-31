<?php
/**
 * Tablebuilder
 *
 * @package htmlobjects
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2012, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.1
 */
class htmlobject_tablebuilder extends htmlobject_table
{
/**
* Actions row of table
*
* @access public
* @var array
* <code>
* $actions = array();
* $actions[] = 'delete';
* $actions[] = 'sort';
* $actions[] = 'edit';
* OR
* $actions = array(
*   array('delete' => '[translation]', 'button' => true),
*   array('delete' => '[translation]'),'
*   array('edit' => '[translation]', 'title' => 'Title text'),
* );
* $table = new htmlobject_tablebuilder();
* $table->actions = $actions;
* </code>
*/
var $actions = array();
/**
* Name for submit actions
*
* @access public
* @var string
*/
var $actions_name = '';
/**
* Use array_sort
*
* if true, body will be
* sorted by tablebuilder
*
* @access public
* @var bol
*/
var $autosort = false;
/**
* Table body
*
* @access public
* @var array
* <code>
* $body = array();
* $body[] = array('id' => 'value1', 'date' => 'value2', ...)
* $body[] = array('id' => 'value1', 'date' => 'value2', ...)
*
* $table = new htmlobject_tablebuilder();
* $table->body = $body;
* </code>
*/
var $body = array();
/**
* Global prefix for css classes
*
* @access public
* @var string
*/
var $css_prefix = 'htmlobject_';
/**
* Url to process request
* no html form if empty
*
* @access public
* @var string
*/
var $form_action = '';
/**
* Form method
*
* @access public
* @var enum [GET|POST]
*/
var $form_method = 'POST';
/**
* Add handler and callback to trs
*
* Element and id of identifier will
* be added to event callback function.
* Use $table->handler_tr = null; or
* $table->handler_tr = array(); to disable.
*
* @access public
* @var array
* <code>
* $table->handler_tr = array('onclick' => 'tr_click');
* 
* <tr onclick="tr_click(this, 'id')">
* </code>
*/
var $handler_tr = array(
	'onclick'     => 'tr_click',
	'onmouseover' => 'tr_hover',
	'onmouseout'  => 'tr_hover'
);
	
/**
* Head row of table (th)
*
* @access public
* @var array
* <code>
* $head = array();
* $head['id']['title']      = 'Id';
* $head['date']['sortable'] = false;
* $head['date']['hidden']   = true;
* $head['date2']['title']   = 'Date';
* $head['date2']['map']     = date;
*
* $table = new htmlobject_tablebuilder();
* $table->head = $head;
* </code>
*/
var $head = array();
/**
* Hide table when body is empty
*
* @access public
* @var bool
*/
var $hide_empty = false;
/**
* Field to add value to checkbox
*
* @access public
* @var string
*/
var $identifier = '';
/**
* Array of identifiers to be checked
*
* @access public
* @var array
*/
var $identifier_checked = array();
/**
* Array of identifiers to be disabled
*
* @access public
* @var array
*/
var $identifier_disabled = array();
/**
* Name of identifier input
*
* @access public
* @var string
*/
var $identifier_name = '';
/**
* Type of identifier input
*
* @access public
* @var enum [checkbox|radio]
*/
var $identifier_type = 'checkbox';
/**
* Initial limit
*
* @access private
* @var int
*/
var $limit = 20;
/**
* Select with limit values
*  
* @access public
* @var array
* <code>
* $limit_select = array(
	array("value" => 0,  "text" => 'none'),
*	array("value" => 10, "text" => 10),
*	array("value" => 20, "text" => 20),
*	array("value" => 30, "text" => 30),
*	);
*
* $table = new htmlobject_tablebuilder();
* $table->limit_select = $limit_select;
* </code>
*/
var $limit_select = array();
/**
* Maximum
*
* @access public
* @var int
*/
var $max = 0;
/**
* Offset
*  
* @access public
* @var int
*/
var $offset = 0;
/**
* Sortorder
* 
* @access public
* @var enum [ASC|DESC]
*/
var $order = 'ASC';
/**
* Pageturn Bottom 
*
* @access public
* @var int
*/
var $pageturn_bottom = 9;
/**
* Field to be sorted
*
* @access public
* @var string
*/
var $sort = '';
/**
* css class for sort select
*
* @access public
* @var string
*/
var $sort_css = 'form-control form-control-sm input-sm';
/**
* show sort form buttons
*
* buttons not in array are disabled
*
* @access public
* @var bool
*/
var $sort_buttons = array('sort','order','limit','offset','refresh');
/**
* Enable sortform
*
* @access public
* @var bool
*/
var $sort_form = true;
/**
* Enable sortlink in headrow
*
* @access public
* @var bool
*/
var $sort_link = true;

	//----------------------------------------------------------------------------------------
	/**
	* Constructor
	*
	* @access public
	* @param htmlobject $htmlobject
	* @param string $id id for posted vars
	* @param array|htmlobject_response $params array(key => value, ...);
	*/
	//----------------------------------------------------------------------------------------
	function __construct($html, $id, $params = null) {
		$this->html     = $html;
		$this->request  = $this->html->request();
		if(isset($params)) {
			if(is_array($params)) {
				$this->response = $this->html->response();
				$this->response->params = $params;
			} elseif ($params instanceof htmlobject_response) {
				$this->response = $params;
			}
		} else {
			$this->response = $this->html->response();
		}
		$this->__id = $id;
	}

	//------------------------------------------------
	/**
	* Init basic values __body and __cols
	*
	* @access public
	*/
	//------------------------------------------------
	function init() {

		$minus = 0;
		// Execute head array special key values
		reset($this->head);
		foreach($this->head as $key => $value) {
			//  special key hidden
			if(@array_key_exists('hidden', $this->head[$key]) == true) {
				if($this->head[$key]['hidden'] === true) {
					$minus = $minus+1;
				}
			}
		}
		$this->__cols = count($this->head) - $minus;
		if($this->identifier !== '') { $this->__cols = $this->__cols +1; }
		// Sortfunction eabled and sort value valid?
		if($this->sort !== '' && isset($this->body[0]) && isset($this->body[0][$this->sort])) {
			// use autosort ?
			if($this->autosort === true) {
					// reinit tablebuilder to get sort and order from request
					$this->__request('', '', $this->limit, $this->offset); 
					$this->__sort();
			}
			// max still untouched?
			if($this->max === 0) { $this->max = count($this->body); }
			// Input bigger than Output?
			if(count($this->body) > $this->limit && $this->limit != 0) {
				// max smaller than  limit + offset?
				if(($this->offset + $this->limit) < $this->max ) {
					$max = $this->offset + $this->limit;
				} else { $max = $this->max;	}
				// Transfer Input to Output
				for($i = $this->offset; $i < $max; $i++) {
					$this->__body[$i] = $this->body[$i];
				}
			} else { $this->__body = $this->body; }
		} else {
			if(!isset($this->body) || is_string($this->body)) {
				$this->body = array();
			}
			$this->__body = $this->body;
		}
		// save memory
		$this->body = null;
		// reinit to avoid wrong offset
		unset($_REQUEST[$this->__id]['max']);
		$this->__request($this->sort, $this->order, $this->limit, $this->offset);

	}

	//------------------------------------------------
	/**
	* Get table as object
	*
	* @access public
	* @return object
	*/
	//------------------------------------------------
	function get_object() {

		$this->init();

		if(
			isset($this->__body) &&
			is_array($this->__body) &&
			( count($this->__body) > 0 || $this->hide_empty !== true )
		) {

			$table = $this->html->table();

			$table->js          = $this->get_js();
			$table->params      = $this->__get_params();

			$table->css         = $this->css;
			$table->id          = $this->id;
			$table->style       = $this->style;
			$table->title       = $this->title;
			$table->handler     = $this->handler;
			$table->align       = $this->align;
			$table->border      = $this->border;
			$table->bgcolor     = $this->bgcolor;
			$table->cellpadding = $this->cellpadding;
			$table->cellspacing = $this->cellspacing;
			$table->frame       = $this->frame;
			$table->rules       = $this->rules;
			$table->summary     = $this->summary;
			$table->width       = $this->width;
			if (isset($this->__customattribs)) {
				$table->__customattribs = $this->__customattribs;
			}

			// build table
			// build additional table head
			if(isset($this->__headrow)) {
				reset($this->__headrow);
				foreach ($this->__headrow as $row) {
					$row->__elements[0]->colspan = $this->__cols;
					$table->add($row);
				}
			}
			// build pageturn
			$div = $this->html->div();
			$div->css = 'pageturn_wrapper'; 
			// add sort form
			if($this->sort_form === true) {
				$div->add($this->__get_sort(), 'sortform');
			}
			$div->add($this->__get_pageturn(), 'pageturn');
			$div->add('<div style="line-height:0px;clear:both;" class="floatbreaker">&#160;</div>');
			// build pageturn td
			$td = $this->html->td();
			$td->colspan = $this->__cols;
			$td->type = 'td';
			$td->css = $this->css_prefix.'td pageturn_head';
			$td->add($div);
			$td->add('<div style="line-height:0px;clear:both;" class="floatbreaker">&#160;</div>');

			$tr = $this->html->tr();
			$tr->css = $this->css_prefix.'tr pageturn_head';
			$tr->add($td, 'pageturn_head');

			$table->add($tr, 'pageturn_head');
		
			// build table head		
			$table->add($this->__get_head());
	
			// build table body
			if(count($this->__body) > 0) {
				$table->add($this->__get_body());
			} else {
				$td = $this->html->td();
				$td->colspan = $this->__cols;
				$td->type = 'td';
				$td->css = $this->css_prefix.'td firstcol';
				$td->add($this->html->lang['table']['no_data']);
		
				$tr = $this->html->tr();
				$tr->css = $this->css_prefix.'tr';
				$tr->add($td);
				$table->add($tr);
			}

			// build table actions
			$actions = $this->__get_actions();
			if($actions !== '') {
				$table->add($actions, 'actions');
			}

			// insert bottom pageturn
			if(count($this->__body) > $this->pageturn_bottom && $this->limit < $this->max && $this->limit !== '0') {
				$td = $this->html->td();
				$td->colspan = $this->__cols;
				$td->type = 'td';
				$td->css = $this->css_prefix.'td pageturn_bottom';
				$td->add($this->__get_pageturn());
		
				$tr = $this->html->tr();
				$tr->css = $this->css_prefix.'tr pageturn_bottom';
				$tr->add($td);

				$table->add($tr, 'pageturn_bottom');
			}
			if(isset($this->__bottomrow)) {
				reset($this->__bottomrow);
				foreach ($this->__bottomrow as $row) {
					$row->__elements[0]->colspan = $this->__cols;
					$table->add($row);
				}
			}
			return $table;
		} else {
			$div = $this->html->div();
			$div->js = '';
			$div->add('&#160;');
			$div->style = 'display:none;';
			return $div;
		}

	}

	//------------------------------------------------
	/**
	* Get table as string
	*
	* @access public
	* @return string
	*/
	//------------------------------------------------
	function get_string() {
		$_str = '';
		$table = $this->get_object();
		if($table instanceof htmlobject_table) {
			$_str = $table->js;
			if($this->form_action !== '') {
				$_str .= '<form css="form-inline" action="'.$this->form_action.'" method="'.$this->form_method.'">';
				$_str .= $table->params->get_string();
			}
			$_str .= $table->get_string();
			($this->form_action !== '') ? $_str .= '</form>' : null;
			return $_str;
		} else {
			return $table->get_string();
		}
	}

	//------------------------------------------------
	/**
	* Adds a row to top of table
	*
	* @access public
	* @param string $str
	*/
	//------------------------------------------------
	function add_headrow($str = '') {
		$tr = $this->html->tr();
		$tr->css = $this->css_prefix.'tr';
		
		$td = $this->html->td();
		$td->type = 'td';
		$td->css = $this->css_prefix.'td head';
		$td->add($str);
		$tr->add($td);	

		$this->__headrow[] = $tr;	
	}

	//------------------------------------------------
	/**
	* Adds a row to bottom of table
	*
	* @access public
	* @param string $str
	*/
	//------------------------------------------------
	function add_bottomrow($str = '') {
		$tr = $this->html->tr();
		$tr->css = $this->css_prefix.'tr';
		
		$td = $this->html->td();
		$td->type = 'td';
		$td->css = $this->css_prefix.'td head';
		$td->add($str);
		$tr->add($td);	

		$this->__bottomrow[] = $tr;	
	}

	//------------------------------------------------
	/**
	* Get JS for tr hover and click function
	*
	* @access public
	* @return string
	*/
	//------------------------------------------------
	function  get_js() {
		$_str = '';
		$id_1 = '';
		$id_2 = '';
		if($this->identifier !== '') {
			$id_1 = 'try { document.getElementById(arg).checked = true; } catch(e) {}';
			$id_2 = 'try { document.getElementById(arg).checked = false; } catch(e) {}';
		}
		$_str .= "\n";
		$_str .= '<script type="text/javascript">'."\n";
		$_str .= 'function tr_hover(element) {'."\n";
		$_str .= '	x = element.className.match(/tr_hover/g);'."\n";
		$_str .= '	if(x == null) {	element.className = element.className + " tr_hover"; }'."\n";
		$_str .= '	else { element.className = element.className.replace(/ tr_hover/g, "");	}'."\n";
		$_str .= '}'."\n";
		$_str .= 'function tr_click(element, arg) {'."\n";
		$_str .= '	x = element.className.match(/tr_click/g);'."\n";
		$_str .= '	if(x == null) {	element.className = element.className + " tr_click";'; 
		$_str .= '	'.$id_1.' }'."\n";
		$_str .= '	else { element.className = element.className.replace(/ tr_click/g, "");';	
		$_str .= '	'.$id_2.' }'."\n";
		$_str .= '}'."\n";
		$_str .= '</script>'."\n";
		return $_str;
	}

	//------------------------------------------------
	/**
	* Get tablebuilder params
	*
	* @access public
	* @return array
	*/
	//------------------------------------------------
	function get_params() {
		$e = array(
			$this->__id => array(
				'sort'   => $this->sort,
				'order'  => $this->order,
				'limit'  => $this->limit,
				'offset' => $this->offset,
			)
		);
		return $e;
	}

	//------------------------------------------------
	/**
	* Get tablebuilder params string
	*
	* @access public
	* @return string
	*/
	//------------------------------------------------
	function get_params_string() {
		$e  = '';
		$e .= '&'.$this->__id.'[sort]='.$this->sort;
		$e .= '&'.$this->__id.'[order]='.$this->order;
		$e .= '&'.$this->__id.'[limit]='.$this->limit;
		$e .= '&'.$this->__id.'[offset]='.$this->offset;
		return $e;
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
		$tr = '';
		if(count($this->head) > 0) {
			$tr = $this->html->tr();
			$tr->css = $this->css_prefix.'tr headrow';
			reset($this->head);
			$i = 0;
			foreach($this->head as $key_2 => $value) {

				if(!isset($value['title'])) {
					$value['title'] = '';
				}

				$hidden = false;
				if(@array_key_exists('hidden', $this->head[$key_2]) == true) {
					if($this->head[$key_2]['hidden'] === true) {
						$hidden = true;
					}
				}
				
				$sortable = true;
				if(@array_key_exists('sortable', $this->head[$key_2]) == true) {
					if($this->head[$key_2]['sortable'] === false) {
						$sortable = false;
					}
				}

				if($hidden === false) {
					if($value['title'] == '') { 
						$str = '&#160;'; 
					} else {
						$marker = '';
						$linkclass = '';
						if($this->sort !== '' && $sortable ===  true && $this->sort_link === true) {
							if(isset($this->head[$key_2]['map'])) {
								$sort = $this->head[$key_2]['map'];
							} else {
								$sort = $key_2;
							}
							$order_param = '&'.$this->__id.'[order]=ASC';
							if($this->sort === $sort) {
								if($this->order === 'ASC') {
									$order_param = '&'.$this->__id.'[order]=DESC';
									$linkclass = 'asc';
									$marker = '<small>&#9662;</small>';
								} else {
									$linkclass = 'desc';
									$marker = '<small>&#9652;</small>';
								}
							}
							$params  = $this->html->thisfile;
							$params .= '?'.$this->__id.'[sort]='.$sort;
							$params .= '&'.$this->__id.'[limit]='.$this->limit;
							$params .= '&'.$this->__id.'[offset]=0';
							$params .= $order_param;
							$params .= $this->response->get_string(null, null, '&', true);

							$str        = $this->html->a();
							$str->href  = $params;
							$str->label = $value['title'].' '.$marker;
							$str->css   = $linkclass;
						} else {
							$str = $value['title'];
						}
					}
					$mark = '';
					if($i === 0) {
						$mark = ' firstcol';
						$i = 1;
					}
					$td = $this->html->td();
					$td->type = 'th';
					$td->css = $this->css_prefix.'th '.$key_2.' '.$linkclass.$mark;
					if(isset($this->head[$key_2]['style']) && $this->head[$key_2]['style'] !== '') {
						$td->style = $this->head[$key_2]['style'];
					}
					$td->add($str);
					$tr->add($td);
				}
			}
			if($this->identifier !== '') {
				$td = $this->html->td();
				$td->type = 'th';
				$td->css = $this->css_prefix.'th '.$this->identifier_name;
				$td->add('&#160;');
				$tr->add($td);
			}
		}
		return $tr;
	}

	//------------------------------------------------
	/**
	* Get a row of table body
	*
	* @access protected
	* @return object|array htmlobject_tr or empty array
	*/
	//------------------------------------------------
	function __get_body() {
		$ar = array();
		$rt = 'odd';
		$i  = 0;
		$c  = count($this->__body)-1;
		reset($this->__body);

		foreach ($this->__body as $line => $val) {
			$ident = substr(uniqid('p', true), 0, 14);
			$f     = '';
			if($i === 0)  { $f = ' first'; };
			if($i === $c) { $f = ' last'; };
		
			$tr = $this->html->tr();
			$tr->css = $this->css_prefix.'tr ' .$rt.$f;
			$tr->handler = $this->__get_js_tr($ident);

			$data = array();
			reset($this->head);
			foreach( $this->head as $key => $value ) {
				if(isset($this->__body[$line][$key])) {
					$data[$key] = $val[$key];
				} else {
					$data[$key] = '<i>NULL</i>';
				}
			}

			reset($data);
			$j = 0;
			foreach($data as $key_2 => $v) {
				if($v == '') { $v = '&#160;'; }
				$hidden = false;
				if(@array_key_exists('hidden', $this->head[$key_2]) == true) {
					if($this->head[$key_2]['hidden'] === true) {
						$hidden = true;
					}
				}
				if($hidden === false) {
					$mark = '';
					if($j === 0) {
						$mark = ' firstcol';
						$j = 1;
					}
					$td = $this->html->td();
					$td->type = 'td';
					$td->css = $this->css_prefix.'td '.$key_2.$mark;
					$td->add($v);
					$tr->add($td);
				}
			}
			// identifier
			if($this->identifier !== '') {
				$tr->add($this->__get_indentifier($line, $ident));
			}

			$ar[] = $tr;
			$rt === 'odd' ? $rt = 'even' : $rt = 'odd';
			$i++;
		}
		return $ar;
	}

	//------------------------------------------------
	/**
	* Get table actions row
	*
	* @access protected
	* @return object|string htmlobject_tr or empty string
	*/
	//------------------------------------------------
	function __get_actions () {
		$tr = '';
		if(isset($this->actions[0]) && isset($this->__body)) {
			$tr      = $this->html->tr();
			$tr->css = $this->css_prefix.'tr';
		
			$td          = $this->html->td();
			$td->colspan = $this->__cols;
			$td->type    = 'td';
			$td->css     = $this->css_prefix.'td actions';

			$div      = $this->html->div();
			$div->css = "actiontable";
			$div->add($this->__get_select());
			
			foreach($this->actions as $key_2 => $v) {
				if(!is_array($v)) {
					$html        = $this->html->input();
					$html->css   = 'btn btn-xs btn-default';
					$html->name  = $this->actions_name;
					$html->value = $v;
					$html->type  = 'submit';
					$div->add($html);
				}
				if(is_array($v)) {
					$k = key($v);
					if(!isset($v['button'])) {
						$html        = $this->html->input();
						$html->css   = 'btn btn-xs btn-default';
						$html->name  = $this->actions_name.'['.$k.']';
						$html->value = $v[$k];
						$html->type  = 'submit';
					} else {
						$html        = $this->html->button();
						$html->css   = 'btn btn-xs btn-default';
						$html->name  = $this->actions_name.'['.$k.']';
						$html->label = $v[$k];
						$html->type  = 'submit';
					}
					if(isset($v['title'])) {
						$html->title = $v['title'];
					}
					$div->add($html);
				}
			}
			$div->add('<div style="line-height:0px;clear:both;" class="floatbreaker">&#160;</div>');
			$td->add($div);
			$tr->add($td);
		}
		return $tr;	
	}

	//------------------------------------------------
	/**
	* Get sort functions form
	*
	* @access protected
	* @return object|string
	*/
	//------------------------------------------------
	function __get_sort() {
		$div = '';
		if($this->sort_form === true) {
			reset($this->head);
			foreach($this->head as $key_2 => $v) {
				if(!isset($v['title'])) {
					$v['title'] = '';
				}
				if(isset($v['sortable']) == false) {
					$v['sortable'] = true;
				} 
				if($v['sortable'] === true) {
					if(isset($v['map'])) {
						$value[] = array($v['map'], $v['title']);
					} else {
						$value[] = array($key_2, $v['title']);
					}
				}
			}
			$sort            = $this->html->select();
			$sort->name      = $this->__id.'[sort]';
			$sort->selected  = array($this->sort);
			$sort->css       = 'form-control form-control-sm input-sm sort';
			$sort->handler   = 'onchange="this.form.submit(); return false;"';
			$sort->title     = $this->html->lang['table']['label_sort'];
			if(!in_array('sort', $this->sort_buttons)) {
				$sort->style = 'display: none;';
			}
			$sort->add($value, array(0, 1));

			$order           = $this->html->select();
			$order->name     = $this->__id.'[order]';
			$order->css      = $this->sort_css.' order';
			$order->handler  = 'onchange="this.form.submit(); return false;"';
			$order->title    = $this->html->lang['table']['label_order'];
			if(!in_array('order', $this->sort_buttons)) {
				$order->style = 'display: none;';
			}
			$order->selected = array($this->order);
			$value = array(array("ASC", $this->html->lang['table']['option_asc']),array("DESC",$this->html->lang['table']['option_desc']));
			$order->add($value, array(0, 1));

			if (count($this->limit_select) <= 0) {
				$this->limit_select = array(
					array("value" => 0, "text" => $this->html->lang['table']['option_nolimit']),
					array("value" => 10, "text" => 10),
					array("value" => 20, "text" => 20),
					array("value" => 30, "text" => 30),
					array("value" => 40, "text" => 40),
					array("value" => 50, "text" => 50),
					);
			}
			$limit           = $this->html->select();
			$limit->name     = $this->__id.'[limit]';
			$limit->css      = $this->sort_css.' limit';
			$limit->title    = $this->html->lang['table']['label_limit'];
			if(!in_array('limit', $this->sort_buttons)) {
				$limit->style = 'display: none;';
			}
			$limit->handler  = 'onchange="this.form.submit(); return false;"';
			$limit->selected = array($this->limit);
			$limit->add($this->limit_select, array("value", "text"));

			$offset        = $this->html->input();
			$offset->name  = $this->__id.'[offset]';
			$offset->css   = $this->sort_css.' offset';
			$offset->value = "$this->offset";
			$offset->type  = 'text';
			$offset->size  = 3;
			$offset->title = $this->html->lang['table']['label_offset'];
			if(!in_array('offset', $this->sort_buttons)) {
				$offset->style = 'display: none;';
			}
			
			$action        = $this->html->input();
			$action->css   = 'btn btn-sm btn-default refresh';
			$action->name  =  $this->__id.'[action]';
			$action->value = '&#8635;';
			$action->title = $this->html->lang['table']['button_refresh'];
			if(!in_array('refresh', $this->sort_buttons)) {
				$action->style = 'display: none;';
			}
			$action->type  = 'submit';

			$div      = $this->html->div();
			$div->css = "sort_box";
			$div->add($sort->get_string());
			$div->add($order);
			$div->add($offset->get_string());
			$div->add($limit->get_string());
			$div->add($action);
			$div->add('<div style="line-height:0px;clear:both;" class="floatbreaker">&#160;</div>');
			
		}
		return $div;
	}

	//------------------------------------------------
	/**
	* Get params inputs
	*
	* @access protected
	* @return htmlobject_div
	*/
	//------------------------------------------------
	function __get_params() {
		$div = '';
		if(isset($this->response->params)) {
			$form = $this->response->get_form(null, null, false);
			$div = $this->html->div();
			$div->style = 'display:inline;padding:0;margin:0;';
			$div->add($form->get_elements());
		}
		return $div;
	}

	//------------------------------------------------
	/**
	* Get single value from $this->html->lang['table']
	*
	* @access protected
	* @param string $param
	* @return string
	*/
	//------------------------------------------------
	function __get_lang($param) {
		return $this->html->lang['table'][$param];
	}

	//------------------------------------------------
	/**
	* Get page turn functions
	*
	* @access protected
	* @return object
	*/
	//------------------------------------------------
	function __get_pageturn() {
		$div = '';
		if($this->sort !== '') {

			$params  = $this->html->thisfile;
			$params .= '?'.$this->__id.'%5Bsort%5D='.$this->sort;
			$params .= '&'.$this->__id.'%5Border%5D='.$this->order;
			$params .= '&'.$this->__id.'%5Blimit%5D='.$this->limit;
			$params .= '&'.$this->__id.'%5Boffset%5D='.$this->offset;
			$params .= $this->response->get_string(null, null, '&', true);
			
			$first = $this->html->a();
			$first->css = 'btn btn-sm btn-default';
			$first->href = $params.'&'.$this->__id.'%5Baction%5D=%3C%3C'; 
			$first->label = '&lt;&lt;';
			$first->title = $this->html->lang['table']['pageturn_first'];
			
			$prev = $this->html->a();
			$prev->css = 'btn btn-sm btn-default';
			$prev->href = $params.'&'.$this->__id.'%5Baction%5D=%3C';
			$prev->label = '&lt;';
			$prev->title = $this->html->lang['table']['pageturn_prev'];

			$next = $this->html->a();
			$next->css = 'btn btn-sm btn-default';
			$next->href = $params.'&'.$this->__id.'%5Baction%5D=%3E';
			$next->label = '&gt;';
			$next->title = $this->html->lang['table']['pageturn_next'];

			$last = $this->html->a();
			$last->css = 'btn btn-sm btn-default';
			$last->href = $params.'&'.$this->__id.'%5Baction%5D=%3E%3E';
			$last->label = '&gt;&gt;';
			$last->title = $this->html->lang['table']['pageturn_last'];

			if($this->limit === '0' || $this->limit === 0) { 
				$limit = $this->max;
			} else {
				$limit = $this->limit;
			}
			
			if(( $this->offset + $limit ) >= $this->max) {
				$next->style = 'visibility:hidden;';
				$last->style = 'visibility:hidden;';
			}
			if($this->offset <= 0) {
				$first->style = 'visibility:hidden;';
				$prev->style = 'visibility:hidden;';
			}
			
			if(($this->offset + $limit) < $this->max ) {
				$max = $this->offset + $limit;
			} else {
				$max = $this->max;
			}

			$td_l  = $this->html->td();
			$td_l->css = 'pageturn_left';
			$td_l->add($first);
			$td_l->add($prev);

			$td_m      = $this->html->td();
			$td_m->css = 'pageturn_middle';

			$offset = ( $this->offset + 1 );
			if(intval($max) === 0) {
				$offset = 0;
			}

			$str  = '<span class="first">'.$offset.'</span>';
			$str .= '<span class="delimiter"> - </span>';
			$str .= '<span class="last">'.$max.'</span>';
			$str .= '<span class="delimiter"> / </span>';
			$str .= '<span class="max">'.$this->max.'</span>';
			$str .= '<div style="line-height:0px;clear:both;" class="floatbreaker">&#160;</div>';
			$td_m->add($str);

			$td_r      = $this->html->td();
			$td_r->css = 'pageturn_right';
			$td_r->add($next);
			$td_r->add($last);

			$tr = $this->html->tr();
			$tr->add($td_l);
			$tr->add($td_m);
			$tr->add($td_r);

			$table      = $this->html->table();
			$table->css = "pageturn_table";
			$table->add($tr);

			$div      = $this->html->div();
			$div->css = "pageturn_box";
			$div->add($table);
		}
		return $div;
	}	

	//------------------------------------------------
	/**
	* Get identifier multi select function
	* 
	* @access protected
	* @return string
	*/
	//------------------------------------------------
	function __get_select() {
		$_str = '';
		if($this->identifier_type == 'checkbox' && $this->identifier !== '') {
			$_str .= '<div class="selecttable" id="'.$this->__id.'SelectTable" style="display:none;">';
			$_str .= '<span style="padding: 0 5px 0 0;">'.$this->html->lang['table']['select_label'].'</span>';
			$_str .= ' <a href="javascript:'.$this->__id.'select(\'all\');">'.$this->html->lang['table']['select_all'].'</a>'."\n";
			$_str .= ' <a href="javascript:'.$this->__id.'select(\'none\');">'.$this->html->lang['table']['select_none'].'</a>'."\n";
			$_str .= ' <a href="javascript:'.$this->__id.'select(\'invert\');">'.$this->html->lang['table']['select_invert'].'</a>'."\n";
			$_str .= '<script type="text/javascript">'."\n";
			$_str .= 'document.getElementById("'.$this->__id.'SelectTable").style.display = "inline"'."\n";
			$_str .= 'function '.$this->__id.'select(arg) {'."\n";
			$_str .= '  if(arg == "all") {'."\n";
			$_str .= '    for(i = 0; i < document.getElementsByName("'.$this->identifier_name.'[]").length; i++)  {'."\n";
			$_str .= '      document.getElementsByName("'.$this->identifier_name.'[]")[i].checked = true;'."\n";			
			$_str .= '    }'."\n";			
			$_str .= '  }'."\n";
			$_str .= '  if(arg == "none") {'."\n";
			$_str .= '    for(i = 0; i < document.getElementsByName("'.$this->identifier_name.'[]").length; i++)  {'."\n";
			$_str .= '      document.getElementsByName("'.$this->identifier_name.'[]")[i].checked = false;'."\n";			
			$_str .= '    }'."\n";			
			$_str .= '  }'."\n";
			$_str .= '  if(arg == "invert") {'."\n";
			$_str .= '    for(i = 0; i < document.getElementsByName("'.$this->identifier_name.'[]").length; i++)  {'."\n";
			$_str .= '      if(document.getElementsByName("'.$this->identifier_name.'[]")[i].checked == false) {'."\n";
			$_str .= '        document.getElementsByName("'.$this->identifier_name.'[]")[i].checked = true;'."\n";			
			$_str .= '      } else {'."\n";
			$_str .= '        document.getElementsByName("'.$this->identifier_name.'[]")[i].checked = false;'."\n";
			$_str .= '      }'."\n";
			$_str .= '    }'."\n";
			$_str .= '  }'."\n";
			$_str .= '}'."\n";
			$_str .= '</script>'."\n";
			$_str .= '</div>'."\n";
		}
		return $_str;
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
		$td = '&#160;';
		if($this->identifier !== '' && in_array($this->identifier, array_keys($this->__body[$key]))) {
			$html = $this->html->input();
			$html->id = $ident;
			$html->name = $this->identifier_name.'[]';
			$html->value = $this->__body[$key][$this->identifier];
			$html->type = $this->identifier_type;
			if(in_array($this->__body[$key][$this->identifier], $this->identifier_checked)) {
				$html->checked = true;
			}
			if(in_array( $this->__body[$key][$this->identifier], $this->identifier_disabled)) {
				$html = '&#160;';
			}
			$td = $this->html->td();
			$td->type = 'td';
			$td->css = $this->css_prefix.'td identifier '.$this->identifier_name;
			$td->add($html);
		}
		return $td;
	}

	//------------------------------------------------
	/**
	* Get JS for tr
	* 
	* @access public
	* @param string $ident
	* @return string
	*/
	//------------------------------------------------
	function  __get_js_tr($ident) {
		$script = array();
		if(isset($this->handler_tr) && is_array($this->handler_tr)) {
			foreach($this->handler_tr as $k => $v) {
				$script[]= ' '.$k.'="'.$v.'(this, \''.$ident.'\')"';
			}
		}
		return join(' ', $script);
	}

	//------------------------------------------------
	/**
	* Sort array [body] by key [sort]
	*
	* @access protected
	*/
	//------------------------------------------------
	function __sort() {
		if($this->order !== '') {
			if($this->order == 'ASC') $sort_order = SORT_ASC;
			if($this->order == 'DESC') $sort_order = SORT_DESC;
		} else {
			$sort_order = SORT_ASC;
		}
		$column = array();
		reset($this->body);
		foreach($this->body as $val) {
			if(isset($val[$this->sort])) {
				$column[] = $val[$this->sort];
			}
		}
		if(count($this->body) === count($column)) {
			array_multisort($column, $sort_order, $this->body);
		}
	}

	//------------------------------------------------
	/**
	* Build params from request
	*
	* @access protected
	* @param string $sort
	* @param enum $order [ASC|DESC]
	* @param string $limit
	* @param string $offset
	*/
	//------------------------------------------------
	function __request($sort = '', $order = '', $limit = '', $offset = '') {
		
		$r_limit = $this->request->get($this->__id.'[limit]');		
		if(is_numeric($r_limit)) {
			$this->limit = $r_limit;
		}
		else if($limit !== '') {
			$this->limit = $limit;
		}

		// handle offset
		$r_offset = $this->request->get($this->__id.'[offset]');
		if(is_numeric($r_offset)) {
 			if(strpos($r_offset, '0') === 0 && strlen($r_offset) > 1) {
				$this->offset = 0;
			} else {
				$this->offset = $r_offset;
			}
		}
		else if ($offset !== '') {
			$this->offset = $offset;
		}
		
		$r_order = $this->request->get($this->__id.'[order]');
		if($r_order === 'ASC' || $r_order === 'DESC') {
			$this->order = $r_order;
		}
		else if($order !== '' && $this->order === '') {
			$this->order = $order;
		}

		// handle sort XSS vulnerability
		$r_sort = $this->request->get($this->__id.'[sort]');
		if($r_sort !== '') {
			if(isset($this->head)) {
				if(array_key_exists($r_sort, $this->head)) {
					$this->sort = $r_sort;
				}
			}
		}
		else if($sort !== '') {
			$this->sort = $sort;
		}

		//------------------------------------------------------------------- set new offset
		$action = $this->request->get($this->__id.'[action]');
		if($action !== '') {		
			switch ($action) {
				case '<': $this->offset = $this->offset - $this->limit; break;
				case '<<': $this->offset = 0; break;
				case '>': $this->offset = $this->offset + $this->limit; break;
				case '>>': $this->offset = $this->max - $this->limit; break;
				case $this->html->lang['table']['button_refresh']: break;
			}
		}
		//------------------------------------------- check offset
		if($this->offset >= $this->max ) {
			$this->offset = $this->max - $this->limit;
		}
		if($this->offset < 0 ) {
			$this->offset = 0;
		}
		if($this->limit === '0' || $this->limit >= $this->max){
			$this->offset = 0;
		}

	}

}//-- end class
?>
