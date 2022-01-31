<?php
$thisfile = basename($_SERVER['PHP_SELF']);
require_once("config.inc.php");
require_once("../../class/file.class.php");
require_once("../../class/htmlobject/htmlobject.class.php");
require_once("../../class/htmlobject/htmlobject.head.class.php");
require_once("../../class/htmlobject/htmlobject.preloader.php");
require_once("../../class/htmlobject/htmlobject.box.class.php");
require_once("../../class/htmlobject/htmlobject.table.class.php");
require_once("../../class/htmlobject/htmlobject.tabs.class.php");
require_once("../../class/htmlobject/PHPLIB.php");

	$html = new htmlobject_head();
	$html->add_meta('content-language','en');
	$html->add_meta('content-type','text/html; charset=utf-8');
	$html->add_meta('expires','Sat, 01 Dec 2001 00:00:00 GMT');
	$html->add_meta('cache-control','no-cache');
	$html->add_meta('pragma','no-cache');
	$html->add_style('../../css/default.css');
	$html->title = 'FTP';
	echo $html->get_string();


echo '
<iframe src="ftp://'.$server_user.':'.$server_pass.'@'.$server_url.'" width="90%" height="400" name="SELFHTML_in_a_box">
  <p>Ihr Browser kann leider keine eingebetteten Frames anzeigen:
  Sie k&ouml;nnen die eingebettete Seite &uuml;ber den folgenden Verweis
  aufrufen: <a href="../../../index.htm">SELFHTML</a></p>
</iframe>';






?>