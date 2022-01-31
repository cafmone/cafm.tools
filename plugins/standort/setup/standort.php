<?php
/**
 * standort
 *
 * @package standort
 * @author Alexander Kuballa <akuballa@users.sourceforge.net>
 * @copyright Copyright (c) 2019, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

require_once('bootstrap.php');
$PROFILESDIR = realpath(PROFILESDIR).'/';
$CLASSDIR = realpath(CLASSDIR).'/';

require_once($CLASSDIR.'lib/htmlobjects/htmlobject.class.php');
require_once($CLASSDIR.'lib/db/query.class.php');
require_once($CLASSDIR.'lib/file/file.handler.class.php');
require_once($CLASSDIR.'plugins/standort/class/standort.user.class.php');

// init html object
$html = new htmlobject($CLASSDIR.'lib/htmlobjects/');

// init file object
$file = new file_handler();

// init db object
$query = new query($CLASSDIR.'lib/db');
$query->db = $PROFILESDIR;
$query->type = 'file';
$db = $query;

// init user object
$user = new standort_user($file);

require_once($CLASSDIR.'plugins/standort/class/standort.standalone.class.php');
$controller = new standort_standalone($file, $html->response(), $query, $user);
$controller->language = 'en';
$controller->treeurl = 'js/tree.js';
$controller->cssurl = 'css/';
$controller->jssurl = 'js/';
$controller->imgurl = 'img/';
$controller->imprinturl = '#';
$controller->privacynoticeurl = '#';
$controller->contacturl = '#';

echo $controller->action()->get_string();
?>
