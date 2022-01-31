<?php
interface phpcommander
{
var $regex_filename;
var $lang = array('permission_denied' => '');

	function get_folders() {}
	function get_files() {}
	function mkdir() {}
	function mkfile() {}
	function remove() {}
	function copy() {}
	function move() {}
	function get_contents() {}
	function is_dir() {}
	function is_writeable() {}
}
?>
