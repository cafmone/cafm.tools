<?
# todo
# function htmlobject_input = class? < done
# ftp_files extends htmlobject_table?
# ftp_config put request to form < done
# do not redirect on error - set Request instead?






//-------------------------------------------------------------------------------------------------------------------------------
/**
 * Ftp Browser
 *
 * @package ftp
 * @author Alexander Kuballa <akuballa@users.sourceforge.net>
 * @copyright Copyright (c) 2008, Alexander Kuballa
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.0
  */  

class ftp_files extends ftp
{
/**
* path to ftp template dir
* @access public
* @var string
*/
var $templatedir = 'html/';
/**
* path to download dir
* @access public
* @var string
*/
var $downloaddir = '../../tmp/';
/**
* name for selected values
* @access public
* @var string
*/
var $identifier_name = 'identifier';
/**
* show or hide dirlist
* @access public
* @var array [ download / rename ]
*/
var $functions = array('rename','download');
/**
* name of action buttons
* @access public
* @var string
*/
var $functions_name = 'action';
/**
* show or hide dirlist
* @access public
* @var bool
*/
var $show_dirlist = true;
/**
* show or hide search
* @access public
* @var bool
*/
var $show_search = true;
/**
* target file for href
* @access public
* @var string
*/
var $thisfile = '';


	//----------------------------------------------------------------------------------------
	/**
	 * init vars
	 * @acess public
	 * @param string $dir initial directory
	 */
	//----------------------------------------------------------------------------------------
	function init() {
		#ini_set('memory_limit', '128M');
		if($this->show_dirlist === true) {
			if(isset($_REQUEST['dir'])) {
				$this->server_dir = http_request('dir');
			}
		}
		if($this->show_search === true) {
			$this->searchstring = http_request('search');
		}
	}
	
	//----------------------------------------------------------------------------------------
	/**
	 * get current action
	 * @acess public
	 * @return string
	 */
	//----------------------------------------------------------------------------------------
	function get_action() {	
		return http_request($this->functions_name);	
	}	

	//----------------------------------------------------------------------------------------
	/**
	 * perform action
	 * @acess public
	 * @return multiple
	 */
	//----------------------------------------------------------------------------------------
	function action() {
		$this->init();
		if(http_request($this->functions_name) != '') {
			switch (http_request($this->functions_name)) {
				case 'download':
					if(isset($_REQUEST[$this->identifier_name]) == true) {
						(http_request('num') == '') ? $num = 0 : $num = http_request('num');

						$max = count($_REQUEST[$this->identifier_name]);
						
						$args = '&'.$this->functions_name.'=download';
						foreach ($_REQUEST[$this->identifier_name] as $key => $id) {
							$args .= '&'.$this->identifier_name.'[]='.$id; 
						}
						
						if($num < $max) {
							$width = round(($num+1) / $max * 100);
							echo '<div style="float:left;">'.($num +1).'&#160;</div>';
							echo '<div style="background:blue;width:200px;float:left;">';
							echo '<div style="background:red;width:'.$width.'%;">&#160;</div>';
							echo '</div>';
							echo '<div style="float:left;">&#160;'.$max.'</div>';
							flush();
							$this->download($_REQUEST[$this->identifier_name][$num], $this->downloaddir);
							$num++;
							echo '
								<script>
								num = '.$num.';
								location.href = "'.$this->thisfile.'?num='.$num.''.$args.'";
								</script>
							';
						} else {
							$str = '<a href="../../local/file/file.files.php?dir='.$this->downloaddir.'">Browse</a>';
							return $arAction = array("label" => 'Download', "value" => $str);
						}
						exit;
					}
				break;
				case 'rename':
					switch (http_request('subaction')) {
						//-------------------------------------------------  
						case '' :
							$str = '';
							if(isset($_REQUEST[$this->identifier_name]) == true) {
								$str .= '<form action="'.$this->thisfile.'">';
								foreach($_REQUEST[$this->identifier_name] as $value) {
									$str .= htmlobject_input($this->identifier_name.'[]', $value, '', 'hidden');
									$str .= htmlobject_input('new_identifier[]', basename($value), '', 'text');
								}
								$t = new Template_PHPLIB();
								$t->debug = false;
								$t->setFile('tplfile', $this->templatedir . 'ftp.files.rename.html');
								$t->setVar(array(
									'dir'				=> htmlobject_input('dir', $vars['dir'], '', 'hidden').''.htmlobject_input($this->functions_name, 'rename', '', 'hidden'),
									'thisfile'			=> $this->thisfile,
									'list'				=> $str,
									'submit_cancel'		=> htmlobject_input('subaction', 'cancel', '', 'submit'),
									'submit_delete'		=> htmlobject_input('subaction', 'rename', '', 'submit'),
								));
								return $arAction = array("label" => 'Rename', "value" => $t->parse('out', 'tplfile'));
								$redirect = false;
							}
						break;
						//------------------------------------------------- 				
						case 'rename' :
							if (isset($_REQUEST[$this->identifier_name]) && isset($_REQUEST['new_identifier'])) {
							echo 'x';
							flush();
								$ftp = new ftp();
								$ftp->debug = true;
								$ftp->server_url = $server_url;
								$ftp->server_user = $server_user;
								$ftp->server_pass = $server_pass;
							
								foreach ($_REQUEST[$this->identifier_name] as $key => $old) {
									$ftp->rename($old, $vars['dir'].'/_admin/'.$_REQUEST['new_identifier'][$key]);
									$strMsg .= 'renamed '.basename($old).' to '.$_REQUEST['new_identifier'][$key].'<br>';
								}
							$redirect = true;
							}
						break;
					}
			break;			
			}
		}
	}

	//----------------------------------------------------------------------------------------
	/**
	 * get output as string
	 * @acess public
	 * @return string
	 */
	//----------------------------------------------------------------------------------------
	function get_string() {
	
		$this->init();
		$this->search($this->searchstring, $this->show_dirlist);


		$strDirecotries = '';
		$langDirecotries = '';
		$dir = '';
		if($this->show_dirlist === true) {
			$down = split('/', $this->server_dir);
			$tmp = array_pop($down);
			$down_path = array(array('path' => join('/', $down), 'name' => '..'));
			$ar_directories = array_merge($down_path, $this->directories);
			foreach($ar_directories as $value) {
				$strDirecotries .= '<a href="'.$this->thisfile.'?dir='.urlencode($value['path']).'" class="dirlistlinks">'.$value['name'].'</a>';
			}
			$strDirecotries = '<div id="dirlist" class="dirlist">'.$strDirecotries.'</div>'; 
			$langDirecotries = 'Subdirectories ['. count($this->directories).']';
			$dir = htmlobject_input('dir', $this->server_dir, 'Directory', 'text');
		}
		
		$strSearch = '';
		if($this->show_search === true) {
			$strSearch = htmlobject_input('search', $this->searchstring, 'Search', 'text');
		}

		//-------------------------------------------------------------------------------------------- Table		
		$td_list_top = array();
		$td_list_top['path'] = array();
		$td_list_top['path']['title'] = 'Path';
		$td_list_top['path']['sortable'] = false;
		$td_list_top['path']['hidden'] = true;
		if($this->searchstring != '' && $this->show_dirlist === true) {
			$td_list_top['dir'] = array();
			$td_list_top['dir']['title'] = 'Directory';
		}
		$td_list_top['name'] = array();
		$td_list_top['name']['title'] = 'Name';
		$td_list_top['date'] = array();
		$td_list_top['date']['title'] = 'Date';
		$td_list_top['size'] = array();
		$td_list_top['size']['title'] = 'Size';
		$td_list_top['mail'] = array();
		$td_list_top['mail']['title'] = 'Mail';
		$td_list_top['mail']['sortable'] = false;

		$td_list_body = array();
		if (is_array($this->files)) {
			foreach($this->files as $key => $value) {
				$mail = '&#160;';
				$bool = ereg('Imported', $value['path']);
				if($bool !== false) {
					$string = str_replace('/Imported', '', $value['path']);
					$string = str_replace('.imported', '', $string);
					$string = str_replace('.failed', '', $string);
					$string = str_replace('.blocked', '', $string);
						
					$mail = '<a href="../imap/imap.php?searchstring=TEXT %22'.$string.'%22&dir=importmeldung" target="_blank">mail</a>';
				}
				if($this->searchstring != '' && $this->show_dirlist === true) {
					$td_list_body[] = array(
							'path' => $value['path'],
							'dir' => '<a href="'.$this->thisfile.'?dir='.urlencode($value['dir']).'">'.$value['dir'].'</a>&#160;',
							'name' => '<a href="ftp://'.$this->server_user.':'.$this->server_pass.'@'.$this->server_url.$value['path'].'" target="_blank">'.$value['name'].'</a>',
							'date' => $value['date'],
							'size' => $value['size'],
							'mail' => $mail,
							);
				} else {
					$td_list_body[] = array(
							'path' => $value['path'],
							'name' => '<a href="ftp://'.$this->server_user.':'.$this->server_pass.'@'.$this->server_url.$value['path'].'" target="_blank">'.$value['name'].'</a>',
							'date' => $value['date'],
							'size' => $value['size'],
							'mail' => $mail,
							);
				}
			}
		}

		$table = new htmlobject_table_builder('name', null, null, null, 'ftp_');
		$table->css = 'htmlobject_table';
		$table->border = 1;
		$table->cellspacing = 0;
		$table->cellpadding = 0;
		$table->autosort = true;
		$table->sort_params = '&dir='.urlencode($this->server_dir).'&search='.urlencode($this->searchstring);
		$table->id = 'ftp_files_table';
		$table->head = $td_list_top;
		$table->body = $td_list_body;
		$table->bottom = $this->functions;
		$table->identifier = 'path';
		$table->identifier_name = $this->identifier_name;
		$table->max = count($td_list_body);
		$table->bottom_buttons_name = $this->functions_name;

		//-------------------------------------------------------- File Box
		$t = new Template_PHPLIB();
		$t->debug = false;
		$t->setFile('tplfile', $this->templatedir . 'ftp.files.html');
		$t->setVar(array(
			'thisfile'			=> $this->thisfile,
			'dir'				=> $dir,
			'search'			=> $strSearch,
			'table'				=> $table->get_string(),
			'dirlist' 			=> $strDirecotries,
			'lang_dirlist' 		=> $langDirecotries,
			));

		return $t->parse('out', 'tplfile');
	}

}


//-------------------------------------------------------------------------------------------------------------------------------
/**
 * Ftp Config
 * uses classes htmlobject_input, htmlobject_button, file and phplib
 *
 * @package ftp
 * @author Alexander Kuballa <akuballa@users.sourceforge.net>
 * @copyright Copyright (c) 2008, Alexander Kuballa
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.0
  */

class ftp_config extends htmlobject_tabmenu
{
/**
* path to ftp html templates dir
* @access public
* @var string
*/
var $templatedir = 'html/';
/**
* templatefile
* @access public
* @var string
*/
var $template = 'ftp.config.html';	
/**
* name of action buttons (input name)
* @access public
* @var string
*/
var $function_name = 'action';
/**
* path to data dir (configfiles)
* @access public
* @var string
*/
var $datadir = 'data/';
/**
* target file for tabs href and forms
* @access public
* @var string
*/
var $thisfile = '';
/**
* main (active) configfile
* @access public
* @var string
*/
var $configfile = '';
/**
* add request to tab
* @access public
* @var array
*/
var $tab_request = array();

//------------------------------------------------ Lang Section
//------------------------------- buttons
/**
*  label for delete button
*  @access public
*  @var string
*/
var $lang_function_delete = 'delete';
/**
*  label for save button
*  @access public
*  @var string
*/
var $lang_function_save = 'save';
/**
*  label for update button
*  @access public
*  @var string
*/
var $lang_function_update = 'update';
/**
*  label for activate button
*  @access public
*  @var string
*/
var $lang_function_activate = 'activate';
/**
*  label for cancel button
*  @access public
*  @var string
*/
var $lang_function_cancel = 'cancel';

//-------------------------------  tabs
/**
*  Label for delete tab
*  @access public
*  @var string
*/
var $lang_tab_delete = 'Delete';
/**
*  Label for edit tab
*  @access public
*  @var string
*/
var $lang_tab_edit = 'Edit';
/**
*  Label for new tab
*  @access public
*  @var string
*/
var $lang_tab_new = 'New';

//------------------------------- messages
/**
*  message on file remove error
*  @access public
*  @var string
*/
var $lang_remove_error = 'failed to delete ';
/**
*  message on file remove
*  @access public
*  @var string
*/
var $lang_remove = 'deleted ';
/**
*  message on file copy error
*  @access public
*  @var string
*/
var $lang_copy_error = 'failed to activate ';
/**
*  message on file copy
*  @access public
*  @var string
*/
var $lang_copy = 'activated ';
/**
*  message on file move
*  @access public
*  @var string
*/
var $lang_move = 'moved ';
/**
*  message on filename error
*  @access public
*  @var string
*/
var $lang_filename_error = 'filename must be';
/**
*  message on file save
*  @access public
*  @var string
*/
var $lang_saved = ' has been saved';
/**
*  message on file save error
*  @access public
*  @var string
*/
var $lang_saved_error = 'failed to save ';





var $data;

	//-------------------------------------------------
	/**
	 * init class
	 * @acess public
	 * @param string $prefix
	 */
	//-------------------------------------------------
	function ftp_config($prefix) {
		$this->prefix = $prefix;
		
		$this->data  = array();
		$this->data[] = array (
				'label' 	=> 'Name',
				'regex' 	=> array (
							'regex' 	=> '[a-zA-Z0-9~._-]',
							'errormsg' 	=> 'must be',
							),
				'object'	=> array ( 	
									'type' => 'htmlobject_input',
									'attrib' => array( 
										'type' 		=> 'text',
										'name'		=> 'name',
										'title' 	=> 'Name [a-zA-Z0-9~._-]',
										),
							),
			);
		$this->data[] = array (
				'label' 	=> 'Url',
				'regex' 	=> array (
							'regex' 	=> '[a-zA-Z0-9./-]',
							'errormsg' 	=> 'must be',
							),
				'object'	=> array ( 	
									'type' => 'htmlobject_input',
									'attrib' => array( 
										'type' 		=> 'text',
										'name'		=> 'server_url',
										),
							),
			);
		$this->data[] = array (
				'label' 	=> 'Port',
				'regex' 	=> array (
							'regex' 	=> '[0-9]',
							'errormsg' 	=> 'must be',
							),
				'object'	=> array ( 	
									'type' => 'htmlobject_input',
									'attrib' => array( 
										'type' 		=> 'text',
										'name'		=> 'server_port',
										),
							),
			);
		$this->data[] = array (
				'label' 	=> 'User',
				'regex' 	=> array (
							'regex' 	=> '[a-zA-Z0-9]',
							'errormsg' 	=> 'must be',
							),
				'object'	=> array ( 	
									'type' => 'htmlobject_input',
									'attrib' => array( 
										'type' 		=> 'text',
										'name'		=> 'server_user',
										),
							),
			);
		$this->data[] = array (
				'label' 	=> 'Pass',
				'regex' 	=> array (
							'regex' 	=> '',
							'errormsg' 	=> '',
							),
				'object'	=> array ( 	
									'type' => 'htmlobject_input',
									'attrib' => array( 
										'type' 		=> 'text',
										'name'		=> 'server_pass',
										),
							),
			);
		$this->data[] = array (
				'label' 	=> 'Dir',
				'regex' 	=> array (
							'regex' 	=> '',
							'errormsg' 	=> '',
							),
				'object'	=> array ( 	
									'type' => 'htmlobject_input',
									'attrib' => array( 
										'type' 		=> 'text',
										'name'		=> 'server_dir',
										),
							),
			);			
	}
	
	//-------------------------------------------------
	/**
	 * perform action
	 * @acess public
	 * @return multiple
	 */
	//-------------------------------------------------	
	function action() {
	
		$strReturn = '';

		if($this->get_request($this->function_name) != '') {
		
			$strMsg = '';
			$file = new file();
			$file->lang_remove_error = $this->lang_remove_error;
			$file->lang_remove = $this->lang_remove;
			$file->lang_move = $this->lang_move;
			$file->lang_filename_error = $this->lang_filename_error;
			$file->lang_saved = $this->lang_saved;
			$file->lang_saved_error = $this->lang_saved_error;

			$arr = $this->get_request_as_array();

			switch ($this->get_request($this->function_name)) {
				case 'update':
				case 'save':
				
					$strMsg = '';
					$arMsg = $this->check_request();

					if($arMsg == '') {
						$arr['name'] = preg_replace('~.php$~i', '', $arr['name']);
						$strMsg = $file->make_configfile($this->datadir.$arr['name'], $arr);
						$strMsg .= '&'.$this->prefix.'=0';
						$redirect = true;						
					} else {
						$i = 0;
						$_REQUEST['errorfile'] = $arr['name'];
						foreach ($arMsg as $msg) {
							$strMsg .= $msg['msg'];
							$_REQUEST['error'][] = $msg['name'];
							$i++;
						}					
						if($this->get_request($this->function_name) == 'save') {
							$_REQUEST[$this->prefix] = '1';
							$_REQUEST[$this->message_param] = $strMsg;
							foreach($this->get_request_as_array() as $key=>$value) {
								$_REQUEST[$key] = $value;
							}
						} else {
							// unset 
							foreach($this->get_request_as_array() as $key=>$value) {
								$_REQUEST[$key] = '';
							}
							$_REQUEST[$this->message_param] = $strMsg;
						}
					}

				break;
				//-------------------------------- delete
				case 'delete':
					switch ($this->get_request('sub'.$this->function_name)) {
						case '':
							if($arr['name'] != '') {

								$arval = array(
									'thisfile'			=> $this->thisfile,
									'name'				=> preg_replace('~.php$~i', '', $arr['name']),
									'input_hidden'		=> $this->get_tab_request_as_input(array('name' => $arr['name'], 'sub'.$this->function_name => 'delete')),
									'submit_delete'		=> $this->get_button($this->function_name, 'delete', $this->lang_function_delete),
									'submit_save'		=> $this->get_button($this->function_name, 'keep', $this->lang_function_cancel),
									);

								$t = new Template_PHPLIB();
								$t->debug = false;
								$t->setFile('tplfile', $this->templatedir.$this->template);
								$t->setVar(array_merge($arval, $this->get_template_array()));
								$strBoxes .=  $t->parse('out', 'tplfile');
								foreach ($this->tab_request as $key => $value) {
									$_REQUEST[$key] = $value;
								}
								$_REQUEST[$this->prefix] = '0';
								$inner_tabs = array();
								$inner_tabs[] = array(
									'target' => $this->thisfile,
									'value' => $strBoxes,
									'label' => $this->lang_tab_delete,
									'request' => $this->tab_request,
									);
									
								parent::_set($inner_tabs);
								$strReturn = parent::get_string();
							}
						break;
						//-------------------------------- remove
						case 'delete':
							if($arr['name'] != '') {
								$strMsg = $file->remove($this->datadir.$arr['name']);
							}
							$strMsg .= '&'.$this->prefix.'=0';
							$redirect = true;
						break;
					}
				break;
				//-------------------------------- keep				
				case 'keep':
					$strMsg = '&'.$this->prefix.'=0';
					$redirect = true;
				break;
			}

			if($redirect === true) {
				$args = '';
				foreach ($this->tab_request as $key => $value) {
					$args .= '&'.$key.'='.$value;
				}
			    $url = $this->thisfile .'?'.$this->message_param.'='.$strMsg.$args;
				$this->redirect($url);
				exit;
			} else {
				if($strReturn != '') { return $strReturn; }
			}

		}
	}

	//-------------------------------------------------
	/**
	 * check request
	 * @acess public
	 * @return array
	 */
	//-------------------------------------------------
	function check_request() {
		$arReturn = array();
		$i = 0;
		foreach ($this->data as $data) {
			if(isset($data['regex']['regex']) && $data['regex']['regex'] != '') {
				if (ereg($data['regex']['regex'], $this->get_request($data['object']['attrib']['name'])) === false ) {
					$arReturn[$i] = array();
					$arReturn[$i]['msg'] = $data['label'].' '.$data['regex']['errormsg'].' '.$data['regex']['regex'].'<br>';
					$arReturn[$i]['name'] = $data['object']['attrib']['name'];
				}
			}
			$i++;
		}
		if(count($arReturn) > 0) {
			return $arReturn;
		}
		else {
			return '';
		}
	}

	//-------------------------------------------------
	/**
	 * get values of $this->data from http request
	 * @acess public
	 * @return array 
	 */
	//-------------------------------------------------
	function get_request_as_array() {
		$arReturn = array();
		foreach ($this->data as $data) {
			if(isset($data['object']) && $data['object'] != '') {
				$arReturn[$data['object']['attrib']['name']] = $this->get_request($data['object']['attrib']['name']);
			}					
		}
		return $arReturn;
	}

	//-------------------------------------------------
	/**
	 * get values of $this->data from http request
	 * @acess public
	 * @return string 
	 */
	//-------------------------------------------------
	function get_request_as_string() {
		$strReturn = '';
		foreach ($this->data as $data) {
			if(isset($data['object']) && $data['object'] != '') {
				$strReturn .= '&'.$data['object']['attrib']['name'].'='. $this->get_request($data['object']['attrib']['name']);
			}					
		}
		return $strReturn;
	}
	
	//-------------------------------------------------
	/**
	 * get tab_request as html inputs
	 * @acess public
	 * @param array $arValues additional values
	 * @return string
	 */
	//-------------------------------------------------		
	function get_tab_request_as_input($arValues = array()) {

		$strReturn = '';
		$arValues = array_merge($this->tab_request, $arValues);				
		foreach ($arValues as $key => $value) {
			$strReturn .= $this->get_input($key, $value, 'hidden');
		}
		return $strReturn;
	}

	//-------------------------------------------------
	/**
	 * get array for html template
	 * @acess public
	 * @param string $path
	 * @param bool $mark_error
	 * @return array
	 */
	//-------------------------------------------------		
	function get_template_array($path = '', $mark_error = false) {
	
		if($path != '') { require_once($path); }
		
		$artpl = array();
		foreach ($this->data as $data) {
		
			// --------------------- build htmlobject	
		
			$html = new $data['object']['type']();
			foreach ($data['object']['attrib'] as $key => $param) {
				$html->$key = $param;
			}
			if($html->id == '') { $html->id = uniqid('p'); }
			if($path != '') { $html->value = $$data['object']['attrib']['name']; }
			else { $html->value = $this->get_request($data['object']['attrib']['name']); }

			// --------------------- mark as error
			
			if($mark_error === true) {
				if(isset($_REQUEST['error'])) {
					if(in_array($data['object']['attrib']['name'], $_REQUEST['error'])) {
						$data['label'] = '<span class="error">'.$data['label'].'</span>';
					}
				}
			}			
			
			// --------------------- mark as required if ['regex']['errormsg'] is set
			
			if(isset($data['regex']['errormsg']) && $data['regex']['errormsg'] != '') {
				$data['label'] = $data['label'].' *';
			}
			
			// --------------------- build box
			
			$box = new htmlobject_box();
			$box->label = $data['label'];
			$box->content = $html;
			$box->css = 'htmlobject_box';
			
			// --------------------- add to array
			
			$artpl = array_merge($artpl, array($data['object']['attrib']['name'] => $box->get_string()));
		}
		return $artpl;		
	}	
	
	//-------------------------------------------------
	/**
	 * get htmlobject_input as string
	 * @acess public
	 * @param string $name
	 * @param string $value
	 * @param enum $type
	 * @return string
	 */
	//-------------------------------------------------		
	function get_input($name, $value, $type = 'hidden') {

		$value = str_replace('"', '&quot;', $value);
		$value = str_replace('<', '&lt;', $value);
		
		$html = new htmlobject_input();
		$html->name = $name;
		$html->value = $value;
		$html->type = $type;
		
		return $html->get_string();
	}	
	
	//-------------------------------------------------
	/**
	 * get htmlobject_button as string
	 * @acess public
	 * @param string $name
	 * @param string $value
	 * @param string $label
	 * @return string
	 */
	//-------------------------------------------------		
	function get_button($name, $value, $label) {

		$html = new htmlobject_button();
		$html->name = $name;
		$html->value = $value;
		$html->type = 'submit';
		$html->label = $label;
		
		return $html->get_string();
	}

	//-------------------------------------------------
	/**
	 * get content for edit tab
	 * @acess public
	 * @return string
	 */
	//-------------------------------------------------		
	function get_edit_tab_data() {

		$file = new file();
		$strBoxes = '';
			
		//------------------------------------ load config files
			
		foreach ($file->get_files($this->datadir) as $f) {
			
			$mark_error = false;	
			if($this->get_request($this->function_name) == 'update' && $this->get_request('errorfile') == $f['name']) {
				$mark_error = true;			
			}

			$arval = array(
				'thisfile'			=> $this->thisfile,
				'input_hidden'		=> $this->get_tab_request_as_input(array('name' => $f['name'], $this->prefix => '0')),
				'submit_delete'		=> $this->get_button($this->function_name, 'delete', $this->lang_function_delete),
				'submit_save'		=> $this->get_button($this->function_name, 'update', $this->lang_function_update),
			);				
			
			$t = new Template_PHPLIB();
			$t->debug = false;
			$t->setFile('tplfile', $this->templatedir.$this->template);
			$t->setVar(array_merge($arval, $this->get_template_array($f['path'], $mark_error)));
			$strBoxes .=  $t->parse('out', 'tplfile');

		}
		return $strBoxes;
	}
	
	//-------------------------------------------------
	/**
	 * get content for new tab
	 * @acess public
	 * @return string
	 */
	//-------------------------------------------------		
	function get_new_tab_data() {
	
		$mark_error = false;	
		if($this->get_request($this->function_name) == 'save') {
			$mark_error = true;			
		}
		
		$arval = array(
			'thisfile'			=> $this->thisfile,
			'input_hidden'		=> $this->get_tab_request_as_input(array($this->prefix => '1')),
			'submit_delete'		=> '',
			'submit_save'		=> $this->get_button($this->function_name, 'save', $this->lang_function_save),
			);
		
		$t_new = new Template_PHPLIB();
		$t_new->debug = false;
		$t_new->setFile('tplfile', $this->templatedir.$this->template);
		$t_new->setVar(array_merge($arval,  $this->get_template_array('', $mark_error)));
	
		return $t_new->parse('out', 'tplfile');
	}

	//-------------------------------------------------
	/**
	 * get content
	 * @acess public
	 * @return string
	 */
	//-------------------------------------------------	
	function get_string() {
	
		$strBoxes = $this->get_edit_tab_data();

		//------------------------------------ set new tab active when no config files found
		
		if($strBoxes == '') { $_REQUEST[$this->prefix] = '1';}
	
		//------------------------------------ make tabs
			
		$inner_tabs = array();
		$inner_tabs[0] = array(
			'target' 	=> $this->thisfile,
			'value' 	=> $strBoxes,
			'label' 	=> $this->lang_tab_edit,
			'request' 	=> $this->tab_request,
			);
		$inner_tabs[1] = array(
			'target' 	=> $this->thisfile,
			'value' 	=> $this->get_new_tab_data(),
			'label' 	=> $this->lang_tab_new,
			'request' 	=> $this->tab_request,
			);

		parent::_set($inner_tabs);
		return parent::get_string();
	}

} // end class


//-------------------------------------------------------------------------------------------------------------------------------
/**
 * Ftp Config Switcher
 *
 * @package ftp
 * @author Alexander Kuballa <akuballa@users.sourceforge.net>
 * @copyright Copyright (c) 2008, Alexander Kuballa
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.0
  */

class ftp_config_switcher extends ftp_config
{
var $param = 'config';

	//-------------------------------------------------
	/**
	 * perform action
	 * @acess public
	 * @return multiple
	 */
	//-------------------------------------------------	
	function action() {
	$strMsg = '';
	
		if($this->get_request($this->function_name) != '') {
			switch ($this->get_request($this->function_name)) {
				//-------------------------------- activate				
				case 'activate':
					if($this->get_request($this->param) != '') {
						$strMsg = $this->set_active($this->datadir.$this->get_request($this->param));
					}
				break;
			}
		}
	return $strMsg;
	}

	//-------------------------------------------------
	/**
	 * set active config
	 * @acess public
	 * @param string $path
	 * @return string
	 */
	//-------------------------------------------------	
	function set_active($path) {
	
		$file = new file();
		$file->lang_copy_error = $this->lang_copy_error;
		$file->lang_copy = $this->lang_copy;
		return $file->copy($path, $this->configfile);
	
	}

	//-------------------------------------------------
	/**
	 * get config switcher form
	 * @acess public
	 * @return string
	 */
	//-------------------------------------------------	
	function get_string() {

		$ar = array();
		$file = new file();
		//------------------------------------ config files
		foreach ($file->get_files($this->datadir) as $f) {
			$ar[] = array('id' => $f['name'], 'name' => preg_replace('~.php$~i', '', $f['name']));
		}

		// load config file to get current name 
		require($this->configfile);
				
		$html = new htmlobject_select();
		$html->name = $this->param;
		$html->text = $ar;
		$html->selected = array($name);
		$html->selected_by_text = true;
		
		$form = new htmlobject_form();
		$form->action = $this->thisfile;
		$form->method = 'GET';
		$form->fields = $html->get_string().$this->get_button($this->function_name, 'activate', $this->lang_function_activate).$this->get_tab_request_as_input();
		
		return $form->get_string();
	}

}
?>
