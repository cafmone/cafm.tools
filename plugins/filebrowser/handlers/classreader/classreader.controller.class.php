<?php

class classreader_controller
{

var $lang = array(
	'docblock' => 'Docu',
	'source' => 'Source',
);

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param phppublisher $phppublisher
	 * @param htmlobject_response $response
	 */
	//--------------------------------------------
	#function __construct($file, $response, $db, $user) {
	#	$this->user             = $user;
	#	$this->file             = $file;
	#	$this->response         = $response->html->response();
	#	$this->response->params = $response->params;
	#	$this->tpldir           = CLASSDIR.'/plugins/classreader/templates';
	#	$this->settings         = PROFILESDIR.'classreader.ini';
	#}


//-------------------------------------------------------------------

	function get_template($template, $path) {

		require_once(CLASSDIR.'/plugins/classreader/classreader.class.php');
		$reader = new classreader($path);

		$ar = $reader->get();
		$out = array();
		$out['class'] = $ar['classname'];
		foreach($ar as $key => $value) {
		
			if($value !== '') {
				$out[$key] = '<b>'.$key.':</b> '.$value;
			} else {
				$out[$key] = '';
			}
			if($key === 'docblock') {
				$out['docblock'] = implode('<br>', $ar['docblock']);
			}
			if($key === 'attribs') {
				if(is_array($ar['attribs'])) {
					$out['attribs']    = '';
					$out['attribs-ul'] = '<ul>';
					foreach($ar['attribs'] as $k => $attrib) {
						$out['attribs'] .= '<a name="attrib-'.$k.'" href="#'.$ar['classname'].'">top</a>';
						$out['attribs'] .= '<div><b>attribute:</b> '. $k;
						$out['attribs'] .= '<br>';
						$out['attribs'] .= '<b>access:</b> '. $attrib['access'].'<br>';
						$out['attribs'] .= '<b>default:</b><div class="indent"><pre>'. $attrib['default'].'</pre></div><br>';
						$out['attribs'] .= '<pre>'.implode("\n", $attrib['docblock']).'</pre></div>';

						$out['attribs-ul'] .= '<li><a href="#attrib-'.trim($k).'">'.$k.'</a></li>';
					}
					$out['attribs-ul'] .= '</ul>';
				} else {
					$out['attribs']    = '';
					$out['attribs-ul'] = '';
				}
			}
			if($key === 'methods') {
				if(is_array($ar['methods'])) {
					$out['methods'] = '';
					$out['methods-ul'] = '<ul>';
					foreach($ar['methods'] as $k => $attrib) {
						$out['methods'] .= '<a name="method-'.$k.'" href="#'.$ar['classname'].'">top</a>';
						$out['methods'] .= '<div><b>function:</b> '. $k;
						$out['methods'] .= '<br>';
						if($attrib['params'] !== '') {
							$out['methods'] .= '<b>params:</b><div class="indent"><pre>';
							$out['methods'] .= str_replace(array(', ',','), "\n", $attrib['params']);
							$out['methods'] .= '</pre></div><br>';
						} else {
							$out['methods'] .= '<br>';
						}
						$out['methods'] .= '<pre>'.implode("\n", $attrib['docblock']).'</pre></div>';

						$out['methods-ul'] .= '<li><a href="#method-'.trim($k).'">'.$k.'</a></li>';
					}
					$out['methods-ul'] .= '</ul>';
				} else {
					$out['methods']    = '';
					$out['methods-ul'] = '';
				}
			}	

		}
		$template->add($out);
		return $template;
	}
}
?>
