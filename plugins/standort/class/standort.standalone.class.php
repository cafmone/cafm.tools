<?php
/**
 * standort_standalone
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2020, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class standort_standalone
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'standort_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'standort_msg';
/**
* identifier
* @access public
* @var string
*/
var $identifier_name = 'standort_ident';

/**
* treeurl
* path too tree.js
* @access public
* @var string
*/
var $treeurl = '';
/**
* cssurl
* path too css directory
* @access public
* @var string
*/
var $cssurl = 'css/';
/**
* imgurl
* path too image directory
* @access public
* @var string
*/
var $imgurl = 'img/';
/**
* jsurl
* path to js files
* @access public
* @var string
*/
var $jsurl = 'js/';
/**
* link to contact
* @access public
* @var array
*/
var $contacturl = null;
/**
* link to imprint
* @access public
* @var array
*/
var $imprinturl = null;
/**
* link to privacy notice
* @access public
* @var array
*/
var $privacynoticeurl = null;
/**
* language
* default language
* @access public
* @var string
*/
var $language = 'en';
/**
* translation
* @access public
* @var array
*/

var $lang = array(
	'label' => 'Label',
	'title' => 'Title',
	'imprint' => 'Imprint',
	'search' => 'Search ...',
	'contact' => 'Contact',
	'loading' => 'Loading ...',
	'privacynotice' => 'Privacy',
	'lang' => array(
		'en' => 'English',
	), 

);

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param file $file
	 * @param htmlobject_response $response
	 * @param query $db
	 * @param user $user
	 */
	//--------------------------------------------
	function __construct($file, $response, $db, $user) {
		$this->response    = $response;
		$this->user        = $user;
		$this->db          = $db;
		$this->file        = $file;
		$this->profilesdir = PROFILESDIR;

		// handle derived language
		$this->langdir = CLASSDIR.'plugins/standort/lang/';
		if($this->file->exists(PROFILESDIR.'standort/lang/en.standort.standalone.ini')) {
			$this->langdir = PROFILESDIR.'standort/lang/';
		}

		// handle derived templates
		$this->tpldir = CLASSDIR.'plugins/standort/templates/';
		if($this->file->exists(PROFILESDIR.'standort/templates/standort.standalone.html')) {
			$this->tpldir = PROFILESDIR.'standort/templates/';
		}

	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 */
	//--------------------------------------------
	function action() {

		// get languages (xss)
		$languages = array();
		$files = glob($this->langdir.'*.standort.standalone.ini');
		if(is_array($files)) {
			foreach($files as $f) {
				$tmp = explode('.', basename($f));
				$languages[$tmp[0]] = $tmp[0];
			}
		}

		// filter Gui lang by languages (xss)
		$lang = $this->response->html->request()->get('lang', true);
		if(!isset($lang)) {
			$lang = $this->language;
		} else {
			if(!array_key_exists($lang, $languages)) {
				$lang = $this->language;
			}
		}
		$this->user->lang = $lang;
		$this->translation = $this->user->translate($this->lang, $this->langdir, 'standort.standalone.ini');

		// escape id (xss)
		$id = $this->response->html->request()->get('id');
		if($id !== '') {
			$id = substr(htmlspecialchars($id), 0, 30);
		}

		$timestamp = 0;

		$script  = '<script src="'.$this->treeurl.'?__='.$timestamp.'"></script>'."\n";
		$script .= '<script language="JavaScript" type="text/javascript">'."\n";
		$script .= 'var timestamp = '.$timestamp.';'."\n";
		$script .= 'var identifiers = '.json_encode($this->translation['identifiers']).';'."\n";
		$script .= 'var lang = "'.$this->user->lang.'";'."\n";
		$script .= 'var languages = '.json_encode($this->translation['lang']).';'."\n";
		$script .= 'var id = "'.$id.'";'."\n";
		$script .= '</script>';

		$contact = '';
		if(isset($this->contacturl)) {
			$contact = '<a href="'.$this->contacturl.'">'.$this->translation['contact'].'</a>';
		}
		$privacynotice = '';
		if(isset($this->privacynoticeurl)) {
			$privacynotice = '<a href="'.$this->privacynoticeurl.'">'.$this->translation['privacynotice'].'</a>';
		}
		$imprint = '';
		if(isset($this->imprinturl)) {
			$imprint = '<a href="'.$this->imprinturl.'">'.$this->translation['imprint'].'</a>';
		}

		$t = $this->response->html->template($this->tpldir.'standort.standalone.html');
		$vars = array(
			'script' => $script,
			'thisfile' => $this->response->html->thisfile,
			'title' => $this->translation['title'],
			'cssurl' => $this->cssurl,
			'jsurl' => $this->jsurl,
			'imgurl' => $this->imgurl,
			'label' => $this->translation['label'],
			'search' => $this->translation['search'],
			'loading' => $this->translation['loading'],
			'contact' => $contact,
			'imprint' => $imprint,
			'privacynotice' => $privacynotice,
			'lang' => $this->user->lang,
		);
		$t->add($vars);
		return $t;
	}

	//--------------------------------------------
	/**
	 * Form
	 *
	 * @access public
	 * @param string $id
	 * @return htmlobject_form
	 */
	//--------------------------------------------
	function get_form($id = '') {

		$form = $this->response->get_form($this->actions_name, 'dummy', false);
		$form->id = 'languageform';
		$form->method = 'GET';
		$form->enctype = '';

		$d['id']['object']['type']            = 'htmlobject_input';
		$d['id']['object']['attrib']['type']  = 'hidden';
		$d['id']['object']['attrib']['name']  = 'id';
		$d['id']['object']['attrib']['value'] = $id;

		$lang = $this->translation['lang'];
		$d['lang']['object']['type']              = 'htmlobject_select';
		$d['lang']['object']['attrib']['name']    = 'lang';
		$d['lang']['object']['attrib']['index']   = array(0,0);
		$d['lang']['object']['attrib']['options'] = $lang;
		$d['lang']['object']['attrib']['handler'] = 'onchange="this.form.submit();"';
		if(isset($this->user->lang)) {
			$d['lang']['object']['attrib']['selected'] = array( $this->user->lang );
		}

		$form->display_errors = false;
		$form->add($d);
		return $form;
	}

}
?>
