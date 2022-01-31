<?php
$thisfile = basename($_SERVER['PHP_SELF']);
if(file_exists("ftp.config.inc.php")) {
	require_once("ftp.config.inc.php");
} else {
	header("location: ftp.config.php?currenttab=tab_2");
	exit;
}
require_once("ftp.class.php");
require_once("../../inc/http.inc.php");
require_once("../../inc/htmlobject.inc.php");


	$html = new htmlobject_head();
	$html->add_meta('content-language','en');
	$html->add_meta('content-type','text/html; charset=utf-8');
	$html->add_meta('expires','Sat, 01 Dec 2001 00:00:00 GMT');
	$html->add_meta('cache-control','no-cache');
	$html->add_meta('pragma','no-cache');
	$html->add_style('../../css/default.css');
	$html->title = 'FTP';
	
	echo $html->get_string();

?>
<style>
.htmlobject_td.size {
text-align:right;
}
.dirbox {
height:400px;
width:600px;
margin:20px;

}
</style>
<?php

$preloader = new htmlobject_preloader();
$preloader->start();

$ftp = new ftp();
$ftp->server_url = $server_url;
$ftp->server_user = $server_user;
$ftp->server_pass = $server_pass;
$ftp->server_dir = $_REQUEST['dir'];
$ftp->connect();
$ftp->get_folder('', true, true);

$str = '<div class="dirbox">';
foreach($ftp->directories as $value) {
	$str .= '<a href="ftp.files.php?dir='.$value['path'].'" class="dirlinks">'.$value['path'].'</a>';
}
$str .= '</div>';

$tabs = array();
$tabs[] = array(
	'target' => 'ftp.files.php',
	'label' => 'FTP',
	);
$tabs[] = array(
	'target' => $thisfile,
	'value' => $str,
	'label' => 'Folders',
	'request' => array('searchstring' => $_REQUEST['searchstring'],'dir' => $_REQUEST['dir']),
	);
$tabs[] = array(
	'target' => 'ftp.config.php',
	'label' => 'Config',
	);


$tab = new htmlobject_tabmenu($tabs);
$tab->css = 'htmlobject_tabs';
echo $tab->get_string(); 

$preloader->stop();
?>
