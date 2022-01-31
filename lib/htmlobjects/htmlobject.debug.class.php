<?php
/**
 * Debug
 *
 * @package htmlobjects
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2010, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */
class htmlobject_debug
{
	//------------------------------------------------
	/**
	 * print debug string
	 *
	 * @access protected
	 */
	//------------------------------------------------
	static function _print( $level, $msg, $class = '') {
		if($level === '') {
			$msg = '<b>'.str_replace('_debug', '',  $class ).': '.$msg.'</b><br>';
			print $msg;
			print '<small>backtrace {<br>';
			foreach(debug_backtrace() as $key => $msg) {
				print '&#160;&#160;&#160;&#160;';
				print basename($msg['file']).' ';
				print 'line: '.$msg['line'].' ';
				print '['.$msg['class'].$msg['type'].$msg['function'].'()]';
				print '<br>';
			}
			print '}</small><br>';
		} else {
			print $level.': '.$msg.'<br>';
		}
	}

}
?>
