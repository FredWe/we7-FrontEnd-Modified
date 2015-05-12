<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
$dos = array('module');
$do = in_array($do, $dos) ? $do : 'module';

if($do == 'module') {
	$module = uni_modules();
		if(!empty($module)) {
		$new = array();
		$filter = array('system', 'activity');
		foreach($module as $mou) {
			if(in_array($mou['type'], $filter)) continue;
			$new[] = $mou;
		}
	}
	unset($module);
	template('activity/module_model');
	die;
}