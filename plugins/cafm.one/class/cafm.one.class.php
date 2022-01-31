<?php
/**
 * cafm_one
 *
 * @package phppublisher
 * @author Alexander Kuballa [akuballa@users.sourceforge.net]
 * @copyright Copyright (c) 2020, Alexander Kuballa
 * @license BSD License (see LICENSE.TXT)
 * @version 1.0
 */

class cafm_one
{
	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 */
	//--------------------------------------------
	function __construct($file, $response, $db = null, $user = null) {
		$this->file     = $file;
		$this->response = $response;
		$this->db       = $db;
		$this->user     = $user;
		$this->ini      = $this->file->get_ini(PROFILESDIR.'cafm.one.ini');

		if(isset($this->ini['login']['url']) && $this->ini['login']['url'] === 'localhost') {
			$this->local = true;
		}
	}

	//--------------------------------------------
	/**
	 * form
	 *
	 * @access public
	 */
	//--------------------------------------------
	function form($bezeichner = '', $fields = array()) {
		if(
			isset($this->ini['login']) && 
			isset($this->ini['login']['user']) && 
			isset($this->ini['login']['pass'])
		) {
			### TODO - attribs is disabled
			if(isset($this->ini['login']['attribs'])) {

				// handle local
				if(!isset($this->local)) {
					$params = 'addon=todos&todos_action=form';
					if($bezeichner !== '') {
						$params .= '&bezeichner='.$bezeichner;
					}
					if(is_array($fields) && count($fields)>0) {
						foreach($fields as $k => $v) {
							$params .= '&fields['.$k.']='.$v['wert'];
						}
					}
				} else {
					$params = array();
					$params['todos_action'] = 'form';
					if($bezeichner !== '') {
						$params['bezeichner'] = $bezeichner;
					}
					if(is_array($fields) && count($fields)>0) {
						$params['fields'] = $fields;
					}
				}
				return $this->__connect($params);
			}
		} else {
			return 'ERROR: Check settings of plugin cafm.one';
		}
	}

	//--------------------------------------------
	/**
	 * Prefixes
	 *
	 * @param bool $filter
	 * @access public
	 */
	//--------------------------------------------
	function prefixes($filter=true) {
		if(
			isset($this->ini['login']) && 
			isset($this->ini['login']['user']) && 
			isset($this->ini['login']['pass'])
		) {
			// handle local
			if(!isset($this->local)) {
				$params = 'addon=todos&todos_action=prefixes';
				$tables = $this->__connect($params, true);
				if(is_array($tables) && $filter === true) {
					if(isset($this->ini['disabled'])) {
						foreach($this->ini['disabled'] as $k => $v) {
							unset($tables[$k]);
						}
					}
				}
			} else {
				$params = array();
				$params['todos_action'] = 'prefixes';
				$tables = $this->__connect($params, true);
				if(is_array($tables) && $filter === true) {
					if(isset($this->ini['disabled'])) {
						foreach($this->ini['disabled'] as $k => $v) {
							unset($tables[$k]);
						}
					}
				}
			}
			return $tables;
		} else {
			return 'ERROR: Check settings of plugin cafm.one (user, password)';
		}
	}

	//--------------------------------------------
	/**
	 * Interval
	 *
	 * @access public
	 */
	//--------------------------------------------
	function interval($prefix) {
		if(
			isset($this->ini['login']) && 
			isset($this->ini['login']['user']) && 
			isset($this->ini['login']['pass'])
		) {
			// handle local
			if(!isset($this->local)) {
				$params = 'addon=todos&todos_action=interval&prefix='.$prefix;
			} else {
				$params = array();
				$params['todos_action'] = 'interval';
				$params['prefix'] = $prefix;
			}
			return $this->__connect($params, true);
		} else {
			return 'ERROR: Check settings of plugin cafm.one';
		}
	}

	//--------------------------------------------
	/**
	 * Attribs
	 *
	 * @access public
	 */
	//--------------------------------------------
	function attribs($prefix) {
		if(
			isset($this->ini['login']) && 
			isset($this->ini['login']['user']) && 
			isset($this->ini['login']['pass'])
		) {
			// handle local
			if(!isset($this->local)) {
				$params = 'addon=todos&todos_action=attribs&prefix='.$prefix;
			} else {
				$params = array();
				$params['todos_action'] = 'attribs';
				$params['prefix'] = $prefix;
			}
			return $this->__connect($params, true);
		} else {
			return 'ERROR: Check settings of plugin cafm.one';
		}
	}

	//--------------------------------------------
	/**
	 * Bezeichner
	 *
	 * @access public
	 */
	//--------------------------------------------
	function identifiers( $help = true ) {
		if(
			isset($this->ini['login']) && 
			isset($this->ini['login']['user']) && 
			isset($this->ini['login']['pass'])
		) {

			// handle local
			if(!isset($this->local)) {
				$params = 'addon=todos&todos_action=bezeichner';
				(isset($help) && $help === true) ? $params.= '&help=true' : '';
			} else {
				$params = array();
				$params['addon'] = 'todos';
				$params['todos_action'] = 'bezeichner';
				$params['help'] = (isset($help) && $help === true) ? 'true' : '';
			}
			return $this->__connect($params, true);
		} else {
			return 'ERROR: Check settings of plugin cafm.one';
		}
	}

	//--------------------------------------------
	/**
	 * Gewerk
	 *
	 * @access public
	 */
	//--------------------------------------------
	function gewerk() {
		if(
			isset($this->ini['login']) && 
			isset($this->ini['login']['user']) && 
			isset($this->ini['login']['pass'])
		) {
			// handle local
			if(!isset($this->local)) {
				$params = 'addon=todos&todos_action=gewerk';
			} else {
				$params = array();
				$params['todos_action'] = 'gewerk';
			}
			return $this->__connect($params, true);
		} else {
			return 'ERROR: Check settings of plugin cafm.one';
		}
	}

	//--------------------------------------------
	/**
	 * Details
	 *
	 * @param string $bezeichner
	 * @param array $todofields
	 * @param string $prefix
	 * @param string $interval
	 * @param string $showlabel
	 * @param bool $raw if true return array
	 * @access public
	 */
	//--------------------------------------------
	function details(
			$bezeichner = '', 
			$todofields = array(), 
			$prefix = '', 
			$interval = '', 
			$showlabel = true,
			$raw = false,
			$filters = array(),
			$show_risks = true
		) {

		if(
			isset($this->ini['login']) && 
			isset($this->ini['login']['user']) && 
			isset($this->ini['login']['pass'])
		) {

### TODO disabled prefixes

			// handle local
			if(!isset($this->local)) {
				$params = 'addon=todos&todos_action=todos';
				if($bezeichner !== '') {
					$params .= '&bezeichner='.$bezeichner;
				}
				if(is_array($todofields) && count($todofields)>0) {
					foreach($todofields as $k => $v) {

						// handle delimiter [~] in $v
						if(is_string($v) && strpos($v, '[~]') !== false) {
							$v = explode('[~]', $v);
						}

						if(is_array($v)) {
							foreach($v as $l) {
								$params .= '&fields['.$k.'][]='.$l;
							}
						} else {
							$params .= '&fields['.$k.']='.$v;
						}
					}
				}
				if($prefix !== '') {
					$params .= '&prefix='.$prefix;
				}
				if($interval !== '') {
					$params .= '&interval='.$interval;
				}
			} else {
				$params = array();
				$params['todos_action'] = 'todos';
				if($bezeichner !== '') { $params['bezeichner'] = $bezeichner; }
				if($prefix !== '')     { $params['prefix'] = $prefix; }
				if($interval !== '')   { $params['interval'] = $interval; }
				if(is_array($todofields) && count($todofields) > 0) {
					$params['fields'] = $todofields;
				}
			}

			return $this->__connect($params, true);
		} else {
			return 'ERROR: Check settings of plugin cafm.one';
		}
	}

	//--------------------------------------------
	/**
	 * Details2HTML
	 *
	 * @param string $bezeichner
	 * @param array $todofields
	 * @param string $prefix
	 * @param string $interval
	 * @param string $deviceid
	 * @param array $disabled
	 * @param enum $disablemode [off|confirm|internal]
	 * @access public
	 */
	//--------------------------------------------
	function details2html (
			$bezeichner = '', 
			$todofields = array(), 
			$prefix = '', 
			$interval = '',
			$deviceid = '',
			$disabled = array(),
			$disablemode = 'off'
		) {

		$result = $this->details($bezeichner, $todofields, $prefix, $interval, true, true);
		// settings
		$showlabel = true;
		$show_risks = true;

		if(is_array($result)) {
			$str = '';
			$i = 0;
			foreach($result as $todos) {
				// handle prefix label
				if($i === 0) {
					$i = 1;
				} else {
					$str .= '<br>';
				}
				if(isset($todos['label']) && $showlabel === true){
					$str .= '<h3>'.$todos['label'].'</h3>'."\n";
				}
				if(isset($todos['copyright']) && $todos['copyright'] !== ''){
					$str .= '<div class="copyright">'.$todos['copyright'].'</div>'."\n";
					$str .= '<div class="floatbreaker"></div>'."\n";
				}
				// handle todo groups
				if(isset($todos['groups']) && is_array($todos['groups'])){
					foreach($todos['groups'] as $gewerk) {
						$gwlink = '';
						if(isset($gewerk['link']) && $gewerk['link'] !== '') {
							$gwlink = ' <a href="'.$gewerk['link'].'" target="_blank" class="icon icon-link"></a>';
						}
						$gw = '<div class="head" style="margin: 10px 0 10px 0;"><b>'.$gewerk['label'].'</b>'.$gwlink.'</div>'."\n";
						$gwtmp = '';
						if(isset($gewerk['groups']) && is_array($gewerk['groups'])){
							foreach($gewerk['groups'] as $group) {
								$oblink = '';
								if(isset($group['link']) && $group['link'] !== '') {
									$oblink = ' <a href="'.$group['link'].'" target="_blank" class="icon icon-link"></a>';
								}
								$gwtmp .= '<div class="box" style="margin: 0 0 0 15px;">'."\n";
								$obergruppe = '<div class="head" style="margin: 10px 0 10px 0;"><b>'.$group['label'].'</b>'.$oblink.'</div>'."\n";
								$tmp = '';
								if(isset($group['groups']) && is_array($group['groups'])){
									foreach($group['groups'] as $bk => $bgroup) {
										$bglink = '';
										if(isset($bgroup['link']) && $bgroup['link'] !== '') {
											$bglink = ' <a href="'.$bgroup['link'].'" target="_blank" class="icon icon-link"></a>';
										}

										$blockid = uniqid('p');

										$tmp .= '<div class="box" style="margin: 0 0 0 15px;" id="'.$blockid.'">'."\n";
										$tmp .= '<div class="head" style="margin: 0 0 10px 0;">';
										$tmp .= '<b>'.$bgroup['label'].'</b>'.$bglink;
										// only init js when device available
										if(isset($disablemode) && $disablemode !== '') {
											if($disablemode === 'internal') {
												$label = 'toggle';
												$tmp .= '<input type="button" value="'.$label.'" class="noprint btn btn-sm btn-default" style="margin:0 0 0 10px; padding: 4px; line-height:10px;" onclick="todospicker.modal.init(\''.$blockid.'\',\''.$todos['prefix'].'\',\'\');" value="'.$label.'">';
											}
										}
										$tmp .= '</div>'."\n";

										foreach($bgroup['todos'] as $key => $value) {
											$style = '';
											if(isset($filters['interval']) && $filters['interval'] !== $value['interval']) {
												#if(isset($filters['show_empty']) && $filters['show_empty'] === 'true') {
												#	$style = ' style="background:#ddd;"';
												#} else {
													continue;
												#}
											}
											if(isset($filters['period']) && $filters['period'] !== $value['period']) {
												#if(isset($filters['show_empty']) && $filters['show_empty'] === 'true') {
												#	$style = ' style="background:#ddd;"';
												#} else {
													continue;
												#}
											}
											if(isset($filters['person']) && $filters['person'] !== $value['person']) {
												#if(isset($filters['show_empty']) && $filters['show_empty'] === 'true') {
												#	$style = ' style="background:#ddd;"';
												#} else {
													continue;
												#}
											}
											$tlink = '';
											if(isset($value['link']) && $value['link'] !== '') {
												$tlink = ' <a href="'.$value['link'].'" target="_blank" class="icon icon-link"></a>';
											}
											// check disabled
											$label  = 'off';
											$marker = '';
											if(array_key_exists($key, $disabled)) {
												$label  = 'on';
												$marker = ' style="text-decoration:line-through;"';
											}
											// genrated elment id
											$element = $key;
											$tmp .= '<div class="box"'.$style.'>';
											$tmp .= '<div style="float:left;width:25px;text-align:right;">&bull;</div>'."\n";
											$tmp .= '<div style="margin: 0 0 0 35px;" id="'.$element.'">';
											$tmp .= '<span'.$marker.'>';
											// replace \n by <br>
											$tmp .= str_replace("\n", '<br>', $value['label']);
											$tmp .= ' ('.$value['interval'].' / '.$value['period'].' / '.$value['person'].')';
											$tmp .= '</span> ';
											$tmp .= $tlink;
											if(isset($value['risks'])) {
												$tmp .= ' <sup>'.$value['risks'].'</sup>'."\n";
											}
											// only init js when device available
											if(isset($disablemode) && $disablemode !== '') {
												if($disablemode === 'confirm' && isset($deviceid) && $deviceid !== '') {
													$tmp .= '<button type="button" value="'.$label.'" class="noprint btn btn-sm btn-default" style="margin:0 0 0 10px; padding: 3px; line-height:10px; width:30px;" onclick="todospicker.modal.init(\''.$element.'\',\''.$deviceid.'\',\''.$todos['prefix'].'\',\''.$key.'\');">'.$label.'</button>';
												}
												else if($disablemode === 'internal') {
													$tmp .= '<button type="button" value="'.$label.'" class="noprint btn btn-sm btn-default" style="margin:0 0 0 10px; padding: 3px; line-height:10px; width:30px;" onclick="todospicker.modal.init(\''.$element.'\',\''.$todos['prefix'].'\',\''.$key.'\');">'.$label.'</button>';
													if(array_key_exists($key, $disabled)) {
														$tmp .= '<input id="'.$todos['prefix'].'-'.$key.'" type="checkbox" name="disabled['.$key.']" value="on" checked="checked" style="position: absolute; left:-2000px; top:-2000px; visibility: hidden;">';
													}
												}
												else if($disablemode === 'off') {

												}
											}
											$tmp .= '<div style="clear:both;margin:0 0 3px 0;" class="floatbreaker">&#160;</div>'."\n";
											$tmp .= '</div>'."\n";
											$tmp .= '</div>'."\n";
										}
										$tmp .= '</div>'."\n";
									}
									if($tmp !== '') {
										$gwtmp .= $obergruppe.$tmp;
										$gwtmp .= '</div>'."\n";
									}
								}
							}
						}
						if($gwtmp !== '') {
							$str .= $gw.$gwtmp;
						}
					}
				}
				// handle risks
				if(
					isset($todos['risks']) && 
					is_array($todos['risks']) && 
					$show_risks === true
				){
					$str .= '<h4>Gef&auml;hrdungen</h4>';
					foreach($todos['risks'] as $risk) {
						$str .= '<div style="margin: 0 0 3px 15px;"><b>'.$risk['label'].'</b></div>';
						if(isset($risk['todos']) && is_array($risk['todos'])) {
							foreach($risk['todos'] as $t) {
								$str .= '<div style="margin: 0 0 3px 15px;">'.$t.'</div>';
							}
						}
					}
				}

			}
			return $str.'<br><br>';
		} else {
			return $result.'<br><br>';
		}

	}

	//--------------------------------------------
	/**
	 * Connect
	 *
	 * @access private
	 */
	//--------------------------------------------
	function __connect($params = '', $decode = true) {
		if(!isset($this->local)) {
			// remote host
			require_once(CLASSDIR.'lib/curl/curl.class.php');
			$http = new curl();
			$http->user = $this->ini['login']['user'];
			$http->pass = $this->ini['login']['pass'];
			$http->connect($this->ini['login']['url'], $params);
			if(isset($http->error)) {
				$data = $http->error.' - Please check plugin cafm.one.';
			}
			else if(isset($http->response) && $http->response !== 0) {
				// handle status
				if(isset($http->info['http_code'])) {
					if($http->info['http_code'] === 200) {
						$content = $http->get('body', null);
						if($decode === true) {
							$data = json_decode($content, true);
						} else {
							$data = $content;
						}
					}
					else {
						$data = 'ERROR: '.$http->info['http_code'];
					}
				}
			}
			else if($http->response === 0) {
				$data = 'ERROR: No data from '.$url;
			}
		} else {
			// localhost
			$tmp = $_REQUEST;
			$_REQUEST = $params;
			require_once(CLASSDIR.'addons/todos/class/todos.controller.class.php');
			$response = $this->response->response('todos');
			$controller = new todos_controller($this->file, $response, $this->db, $this->user);
			$data = $controller->json(true);
			if($decode === true) {
				$data = json_decode($data, true);
			}
			// restore
			$_REQUEST = $tmp;
		}

		return $data;
	}

}
?>
