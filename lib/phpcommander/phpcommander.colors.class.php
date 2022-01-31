<?php
/**
 * PHPCommander Colors
 *
 * @package phpcommander
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2008 - 2010, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class phpcommander_colors
{
	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param object $phpcommander
	 */
	//--------------------------------------------
	function __construct( $phpcommander ) {
		$this->commander = $phpcommander;
	}

	//--------------------------------------------
	/**
	 * function get_template
	 *
	 * @access public
	 * @return object
	 */
	//--------------------------------------------
	function get_template() {
		$max    = 15;
		$colors = '';
		$count  = count($this->commander->colors);
		if($count <= $max) {
			$max = $count-1;
		}

		for($i = 0; $i <= $max; $i++) {
			$colors .= "'".$this->commander->colors[$i]."'";
			if($i < $max) { $colors .= ','; }
		}
		$vars = array_merge(
		$this->commander->lang['colors'], 
		array(
			'colors'       => $colors,
			));

		$t = $this->commander->html->template( $this->commander->tpldir.'/phpcommander.colors.html');
		$t->add($vars);
		return $t;
	}
		
}
?>
