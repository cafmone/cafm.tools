<?php
$thisfile = basename($_SERVER['PHP_SELF']);
require_once("../../inc/http.inc.php");
require_once("../../inc/htmlobject.inc.php");
require_once("../../local/file/file.class.php");
require_once("ftp.class.php");
require_once("ftp.htmlobject.class.php");

if(!file_exists("ftp.config.inc.php")) {
	header("location: ftp.config.php?currenttab=tab_2");
	exit;
}

$ftp_config_list = new ftp_config_switcher('inner');
$ftp_config_list->datadir = 'data/';
$ftp_config_list->configfile = 'ftp.config.inc.php';
$ftp_config_list->thisfile = $thisfile;
$ftp_config_list->function_name = 'loadconfig';
$ftp_config_list->tab_request = array(
		'currenttab' => 'tab_0'
		);
		
if(http_request($ftp_config_list->function_name)) {
	$_REQUEST['strMsg'] = $ftp_config_list->action();
}

//-------------------------------------------- Output
$html = new htmlobject_head();
$html->add_meta('content-language','en');
$html->add_meta('content-type','text/html; charset=utf-8');
$html->add_meta('expires','Sat, 01 Dec 2001 00:00:00 GMT');
$html->add_meta('cache-control','no-cache');
$html->add_meta('pragma','no-cache');
$html->add_style('../../css/default.css');
$html->add_style('css/ftp.files.css');
$html->title = 'FTP';
echo $html->get_string();
echo '<body>';

require("ftp.config.inc.php");

$ftp = new ftp_files();
$ftp->server_url = $server_url;
$ftp->server_user = $server_user;
$ftp->server_pass = $server_pass;
$ftp->server_dir = $server_dir;
$ftp->identifier_name = 'identifier';
$ftp->functions = array('rename','download');
$ftp->functions_name = 'action';
$ftp->show_dirlist = true;
$ftp->show_search = true;

if(http_request($ftp->functions_name)) {
	$arAction = $ftp->action();
}

$preloader = new htmlobject_preloader();
$preloader->start();
//--------------------------------------------------- Tabs
$tabs = array();
if(isset($arAction)) {
	$tabs[] = $arAction;
} else {
	$tabs[] = array(
		'target' => $thisfile,
		'value' => $ftp->get_string(),
		'label' => 'FTP',
		'request' => array('search' => $ftp->vars['search'],'dir' => $ftp->server_dir),
		);
	$tabs[] = array(
		'target' => 'ftp.folders.php',
		'label' => 'Folders',
		'request' => array('searchstring' => $_REQUEST['searchstring'],'dir' => $_REQUEST['dir']),
		);
	$tabs[] = array(
		'target' => 'ftp.config.php',
		'label' => 'Config',
		'request' => array('searchstring' => $_REQUEST['searchstring'],'dir' => $_REQUEST['dir']),
		);
}





$tab = new htmlobject_tabmenu($tabs);
$tab->css = 'htmlobject_tabs';
$tab->custom_tab = $ftp_config_list->get_string();
echo $tab->get_string(); 

$preloader->stop();
?>
</body>
</html>
