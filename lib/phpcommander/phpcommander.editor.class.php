<?php
/**
 * PHPCommander Editor
 *
 * @package phpcommander
 * @author Alexander Kuballa <akuballa@users.sourceforge.net>
 * @copyright Copyright (c) 2008 - 2016, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class phpcommander_editor
{

/**
* id for editor
* @access public
* @var string
*/
var $editorid = 'EditorContent';

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param string $path
	 * @param object $file
	 * @param object $phpcommander
	 */
	//--------------------------------------------
	function __construct( $phpcommander, $path) {
		$this->__pc   = $phpcommander;
		$this->__path = $path;

		$file = $this->__pc->response->html->request()->get($this->__pc->response->identifier);
		if($file !== '') {
			$this->__file = $file;
		}
	}

	//--------------------------------------------
	/**
	 * Edit action
	 *
	 * @access protected
	 * @return object
	 */
	//--------------------------------------------
	function action() {

		// check permissions
		if(isset($this->__pc->allow['download']) && $this->__pc->allow['download'] === true) {
			// check file param is not empty
			if(isset($this->__file) && $this->__file !== '') {
				require_once(CLASSDIR.'/lib/file/file.mime.class.php');
				$path = $this->__path.'/'.$this->__file;
				$mime = detect_mime($path);
				if(substr($mime, 0, 4) === 'text') {
					// check permissions
					if(isset($this->__pc->allow['edit']) && $this->__pc->allow['edit'] === true) {
						$response = $this->get_response();
						$form     = $response->form;
						if(!$form->get_errors() && $response->submit()) {
							$content = $form->get_request('content');
							$error   = $this->__pc->file->mkfile($this->__path.'/'.$this->__file, $content, 'w+', true);
							if($error === '') {
								$response->msg = sprintf($this->__pc->lang['file']['lang_saved'], $this->__file);
							} else {
								$response->error = $error;
							}
						}
						else if($response->cancel()) {
							echo 'nn';
							#$response->msg = '';
						}
						return $response;
					} else {
						$this->download($path, $mime);
					}
				} else {
					$this->download($path, $mime);
				}
			} else {
				$this->__pc->response->redirect(
					$this->__pc->response->get_url($this->__pc->actions_name, 'select')
				);
			}
		} else {
			$this->__pc->response->redirect(
				$this->__pc->response->get_url($this->__pc->actions_name, 'select')
			);
		}

	}

	//--------------------------------------------
	/**
	 * Get template
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function get_template( $response = null ) {
		if(!isset($response)) {
			$response = $this->action();
		}
		$form     = $response->form;
		$msg      = '';
		$headline = '';
		if(isset($response->msg))   { $msg = $response->msg; }
		if(isset($response->error)) { $msg = $response->error; }
		if(isset($this->headline))  { $headline .=  $this->headline; }
		$headline .= $response->html->request()->get($this->__pc->response->identifier);

		$close         = $this->__pc->html->input();
		$close->value  = '&times;';
		$close->name   = $this->__pc->response->id.'[cancel]';
		$close->type   = 'submit';
		$close->title  = $this->__pc->lang['editor']['lang_close'];
		$close->css    = 'btn btn-xs btn-default float-right close';

		if(isset($this->identifier_name)) {
			if(isset($this->__pc->allow['delete']) && $this->__pc->allow['delete'] === true) {
				$del         = $this->__pc->html->input();
				$del->value  = $this->__pc->lang['editor']['lang_delete'];
				$del->name   = $response->actions_name;
				$del->type   = 'submit';
				$del->css    = 'form-control btn btn-default btn-inline';
				$del->style  = 'margin: 3px 0 0 0;';
				$del->title  = $this->__pc->lang['editor']['lang_delete'];
			} else {
				$del = '';
			}
		} else {
			$del = '';
		}
		$submit = $form->get_elements('submit');
		$vars = array_merge(
			$this->__pc->lang['editor'],
				array(
					'thisfile'      => $this->__pc->html->thisfile,
					'msg'           => $msg,
					'lang_headline' => $headline,
					'ColorPicker'   => $this->__pc->colors()->get_template(),
					'submit_id'     => $submit->id,
					'submit_name'   => $submit->name,
					'content_id'    => $form->get_elements('content')->id
					));
		$t = $this->__pc->html->template($this->__pc->tpldir.'/phpcommander.edit.html');
		$t->add($vars);
		$t->add($form);
		$t->add($this->editorid, 'editorid');
		$t->add($close, 'close');
		$t->add($del, 'delete');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}

	//--------------------------------------------
	/**
	 * Get Response
	 *
	 * @access protected
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function get_response() {
		$d        = array();
		$response = $this->__pc->response;		
		$form     = $response->get_form($response->actions_name, 'edit');
		if( $this->__file !== '' ) {
			$content = $this->__pc->file->get_contents($this->__path.'/'.$this->__file);

			$d['content']['object']['type']            = 'htmlobject_textarea';
			$d['content']['object']['attrib']['id']    = 'editor_content';
			$d['content']['object']['attrib']['name']  = 'content';
			$d['content']['object']['attrib']['style'] = 'width:100%;height:100%;resize:none;';
			$d['content']['object']['attrib']['value'] = $content;
			$d['content']['object']['attrib']['wrap']  = 'off';

			$d['param_file']['label']                     = '';
			$d['param_file']['static']                    = true;
			$d['param_file']['object']['type']            = 'htmlobject_input';
			$d['param_file']['object']['attrib']['type']  = 'hidden';
			$d['param_file']['object']['attrib']['name']  = $response->identifier;
			$d['param_file']['object']['attrib']['value'] = $this->__file;
		}

		$fileinfo = $this->__pc->file->get_fileinfo($this->__path.'/'.$this->__file);

		$submit        = $form->get_elements('submit');
		$submit->id    = 'tttttt';
		$submit->value = $this->__pc->lang['editor']['lang_save'];
		if(isset($fileinfo['write']) && $fileinfo['write'] === false) {
			$submit->disabled = true;
		}
		$d['submit']['object'] = $submit;

		$cancel = $form->get_elements('cancel');
		$cancel->style = 'margin: 3px 0 0 0;';
		$d['cancel']['object'] = $cancel;

		$form->add($d);
		$response->form = $form;
		return $response;
	}
	//--------------------------------------------
	/**
	 * download
	 *
	 * @access public
	 * @return null
	 */
	//--------------------------------------------
	function download($path, $mime) {
		$file = $this->__pc->file->get_fileinfo($path);
		header("Pragma: public");
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: must-revalidate");
		header("Content-type: $mime");
		header("Content-Length: ".$file['filesize']);
		header("Content-disposition: inline; filename=".$file['name']);
		header("Accept-Ranges: ".$file['filesize']);
		#ob_end_flush();
		flush();
		readfile($path);
		exit(0);
	}


		
}
?>
