<?php
$ti = microtime(true);
require_once('curl.class.php');
#echo '<pre>';
#print_r(mb_list_encodings());
#echo '</pre>';
#$test = new curl('http://telekom.de');		
$test = new curl('localhost/curl/test.html');
#$test = new curl('kuballa.net');
#echo $test->get('header', 'last-modified');
#echo $test->get('meta', 'robots');
#echo $test->get('tag', 'a');
echo '<pre>';
print_r($test->get('meta', null));
echo '</pre>';
#echo htmlentities($test->get('body'), ENT_QUOTES, 'UTF-8');
echo memory_get_peak_usage(false).'<br>';
$ti = (microtime(true) - $ti);
echo $ti
?>
