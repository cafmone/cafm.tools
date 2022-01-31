<?php
require_once('../../../../bootstrap.php');
require_once(CLASSDIR.'lib/htmlobjects/htmlobject.class.php');
require_once(CLASSDIR.'lib/file/file.handler.class.php');
$html  = new htmlobject(CLASSDIR.'lib/htmlobjects/');
$file  = new file_handler();
$ini   = $file->get_ini(PROFILESDIR.'settings.ini');
$id    = $html->request()->get('id', true);
$error = false;

if(isset($id)) {
	$url  = $ini['config']['baseurl'].$ini['folders']['login'];
	$url .= '?index_action=plugin';
	$url .= '&index_action_plugin=bestandsverwaltung';
	$url .= '&bestandsverwaltung_action=inventory';
	$url .= '&inventory_action=select';
	$url .= '&filter%5Bid%5D='.urlencode($id);
	$html->response()->redirect($url);
} else {
	$error = true;
}

// handle errors
if($error === true) {
	header("HTTP/1.0 404 Not Found", true, 404);
	echo 'ERROR: missing ID';
	die();
}
?>

