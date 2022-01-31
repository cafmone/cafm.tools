<?php
$thisfile = basename($_SERVER['PHP_SELF']);
require_once("../../inc/http.inc.php");
require_once("../../inc/htmlobject.inc.php");
require_once("../../local/file/file.class.php");
require_once("ftp.class.php");
require_once("ftp.htmlobject.class.php");

$ftp_config_list = new ftp_config_switcher('inner');
$ftp_config_list->datadir = 'data/';
$ftp_config_list->configfile = 'ftp.config.inc.php';
$ftp_config_list->thisfile = $thisfile;
$ftp_config_list->function_name = 'loadconfig';
$ftp_config_list->tab_request = array(
		'currenttab' => 'tab_2'
		);
		
if(http_request($ftp_config_list->function_name)) {
	$_REQUEST['strMsg'] = $ftp_config_list->action();
}

$ftp_config = new ftp_config('inner');
$ftp_config->css = 'htmlobject_tabs';
$ftp_config->prefix_tab = '';
$ftp_config->templatedir = 'html/';
$ftp_config->datadir = 'data/';
$ftp_config->thisfile = $thisfile;
$ftp_config->configfile = 'ftp.config.inc.php';
$ftp_config->tab_request = array(
		'searchstring' => $_REQUEST['searchstring'],
		'dir' => $_REQUEST['dir'],
		'currenttab' => 'tab_2'
		);

if(http_request($ftp_config->function_name)) {
	$strAction = $ftp_config->action();
}

$html = new htmlobject_head();
$html->add_meta('content-language','en');
$html->add_meta('content-type','text/html; charset=utf-8');
$html->add_meta('expires','Sat, 01 Dec 2001 00:00:00 GMT');
$html->add_meta('cache-control','no-cache');
$html->add_meta('pragma','no-cache');
$html->add_style('../../css/default.css');
$html->add_style('css/ftp.config.css');
$html->title = 'FTP';
echo $html->get_string();

if(isset($strAction)) {
	$inner_tabs = $strAction;
} else {
	$inner_tabs = $ftp_config->get_string();
}

	$outer_tabs = array();
	$outer_tabs[] = array(
		'target' => 'ftp.files.php',
		'label' => 'FTP',
		'request' => array('searchstring' => $_REQUEST['searchstring'],'dir' => $_REQUEST['dir']),
		);
	$outer_tabs[] = array(
		'target' => 'ftp.folders.php',
		'label' => 'Folders',
		'request' => array('searchstring' => $_REQUEST['searchstring'],'dir' => $_REQUEST['dir']),
		);
	$outer_tabs[] = array(
		'target' => $thisfile,
		'value' => $inner_tabs,
		'label' => 'Config',
		'request' => array('searchstring' => $_REQUEST['searchstring'],'dir' => $_REQUEST['dir']),
		);

$outer_tab = new htmlobject_tabmenu($outer_tabs);
$outer_tab->css = 'htmlobject_tabs';
$outer_tab->message_param = 'xx';
$outer_tab->custom_tab = $ftp_config_list->get_string();
echo $outer_tab->get_string(); 
?>
</body>
</html>
