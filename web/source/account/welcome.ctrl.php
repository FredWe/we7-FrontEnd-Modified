<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn: pro/web/source/account/welcome.ctrl.php : v f2069dc830e0 : 2014/09/23 07:26:50 : yanghf $
 */
defined('IN_IA') or exit('Access Denied');
if (!empty($_W['uid'])) {
	header('Location: '.url('account/display'));
	exit;
}
header("Location: ".url('user/login'));
exit;
