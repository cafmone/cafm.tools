<?php
/**
 * Preloader
 *
 * @package htmlobjects
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2010, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */
class htmlobject_preloader
{
	//-------------------------------------------------
	/**
	* print preloader message
	*
	* @access public
	* @param string $string
	*/
	//-------------------------------------------------
	function start($string = 'Loading ...') {
		echo '
		<div id="Loadbar" style="margin:40px 0 0 40px;display:none">
		<strong>'.$string.'</strong>
		</div>
		<script type="text/javascript">
		document.getElementById("Loadbar").style.display = "block";
		</script>
		';
		flush();
	}

	//-------------------------------------------------
	/**
	* hide preloader message
	*
	* @access public
	*/
	//-------------------------------------------------
	function stop() {
		echo '
		<script type="text/javascript">
			document.getElementById("Loadbar").style.display = "none";
		</script>
		';
	}
}
