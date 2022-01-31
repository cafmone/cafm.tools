<?php

	//-------------------------------------------------------
	/**
	 * creates a php config file ($path) from an array
	 * @param $path string
	 * @param $data array
	 * @param $extension string fileextension
	 * @return string
	 */
	//-------------------------------------------------------
	function make_configfile($path, $data, $extension = '.php') {
	$strMsg = '';
		#ereg('['.$extension.']$', $path) ? 'true' : $path = $path.$extension;
		$fp = fopen($path, 'w+');
		if($fp) {
			fputs($fp, '<?php'."\n");
			foreach($data as $key => $value) {
				if(!is_array($value)) {
					$value = str_replace('"','\"', $value);
					$match = preg_match('~^(true|false|[0-9])$~i', $value);
					if(!$match) {
						fputs($fp, '$'.$key.' = "'.$value.'";'."\n");
					} else {
						fputs($fp, '$'.$key.' = '.$value.';'."\n");
					}
				} else {
					fputs($fp, '$'.$key.' = array();'."\n");
					foreach($value as $subkey => $subvalue) {
						if(!is_array($subvalue)) {
							$subvalue = str_replace('"','\"', $subvalue);
							$match = preg_match('~^(true|false|[0-9])$~i', $subvalue);
							if(!$match) {
								fputs($fp, '$'.$key.'[\''.$subkey.'\'] = "'.$subvalue.'";'."\n");
							} else {
								fputs($fp, '$'.$key.'[\''.$subkey.'\'] = '.$subvalue.';'."\n");
							}
						} else {
							fputs($fp, '$'.$key.'[\''.$subkey.'\'] = array();'."\n");
							foreach($subvalue as $subsubkey => $subsubvalue) {
								$subsubvalue = str_replace('"','\"', $subsubvalue);
								$match = preg_match('~^(true|false|[0-9])$~i', $subsubvalue);
								if(!$match) {
									fputs($fp, '$'.$key.'[\''.$subkey.'\'][\''.$subsubkey.'\'] = "'.$subsubvalue.'";'."\n");
								} else {
									fputs($fp, '$'.$key.'[\''.$subkey.'\'][\''.$subsubkey.'\'] = '.$subsubvalue.';'."\n");
								}
							}
						}
					}
				}
			}
			fputs($fp, '?>');
			fclose($fp);
			#$this->chmod($path);
			#$strMsg = basename($path) .$this->lang_saved;
		} else {
			$strMsg = $this->lang_saved_error.basename($path);
		}
	return $strMsg;
	}

?>
