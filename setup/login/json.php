<?php
require_once('../bootstrap.php');
require_once(CLASSDIR.'cafm.tools.class.php');
$controller = new cafm_tools();
$output = $controller->json();
$output->action();
?>
