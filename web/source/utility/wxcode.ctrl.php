<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
$_W['uniacid'] = intval($_GPC['uniacid']);
load()->func('communication');
$username = trim($_GPC['username']);
$response = ihttp_get("https://mp.weixin.qq.com/cgi-bin/verifycode?username={$username}&r=" . TIMESTAMP);
if(!is_error($response)) {
	isetcookie('code_cookie', $response['headers']['Set-Cookie']);
	header('Content-type: image/jpg');
	echo $response['content'];
	exit();
}