<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
$dos = array('display', 'location_post', 'logo', 'location_list', 'location_del', 'whitelist');
$do = in_array($do, $dos) ? $do : 'logo';
$accounts = uni_accounts();
if(!empty($accounts)) {
	foreach($accounts as $key => $li) {
		if($li['level'] < 3) {
			unset($accounts[$key]);
		}
	}
}

$acid = intval($_GET['__acid']);
if(empty($acid)) {
	$acid = intval($_GPC['__acid']);
}
if(!$acid || empty($accounts[$acid])) {
	message('公众号不存在', url('wechat/account'), 'error');
} else {
	isetcookie('__acid', $acid, 86400 * 3);
}

if($do == 'logo') {
	load()->func('tpl');
	$coupon_setting = pdo_fetch('SELECT * FROM ' . tablename('coupon_setting') . ' WHERE uniacid = :aid AND acid = :acid', array(':aid' => $_W['uniacid'], ':acid' => $acid));
	if(checksubmit('submit')) {
		$_GPC['logo'] = trim($_GPC['logo']);
		empty($_GPC['logo']) && message('请上传商户logo', referer(), 'info');
		load()->classs('coupon');
		$acc = new coupon($acid);
		$status = $acc->LocationLogoupload($_GPC['logo']);

		if(is_error($status)) {
			message($status['message'], referer(), 'error');
		}
		$data = array(
			'uniacid' => $_W['uniacid'],
			'acid' => $acid,
			'logourl' => $status['url'],
		);
		if(empty($coupon_setting)) {
			pdo_insert('coupon_setting', $data);
		} else {
			pdo_update('coupon_setting', $data, array('uniacid' => $_W['uniacid']));
		}
		message('上传商户LOGO成功', referer(), 'success');
	}
}

if($do == 'location_post') {
	load()->func('tpl');
	if(checksubmit('submit')) {
		$data['business_name'] = trim($_GPC['business_name']) ? urlencode(trim($_GPC['business_name'])) : message('门店名称不能为空');
		$data['branch_name'] = urlencode(trim($_GPC['branch_name']));
		$data['category'] = (trim($_GPC['category']) && trim($_GPC['subclass'])) ? urlencode(trim($_GPC['category'])) . '-' .  urlencode(trim($_GPC['subclass'])) : message('请选择门店分类');
		$data['province'] = trim($_GPC['reside']['province']) ? urlencode(trim($_GPC['reside']['province'])) : message('请选择门店所在省');
		$data['city'] = trim($_GPC['reside']['city']) ? urlencode(trim($_GPC['reside']['city'])) : message('请选择门店所在市');
		$data['district'] = trim($_GPC['reside']['district']) ? urlencode(trim($_GPC['reside']['district'])) : message('请选择门店所在区');
		$data['address'] = trim($_GPC['address']) ? urlencode(trim($_GPC['address'])) : message('门店详细地址不能为空');
		$data['longitude'] = trim($_GPC['baidumap']['lng']) ? trim($_GPC['baidumap']['lng']) : message('请选择门店所在地理位置经度');
		$data['latitude'] = trim($_GPC['baidumap']['lat']) ? trim($_GPC['baidumap']['lat']) : message('请选择门店所在地理位置维度');
		$data['telephone'] = trim($_GPC['telephone']) ? trim($_GPC['telephone']) : message('门店电话不能为空');
		load()->classs('coupon');
		$post[] = $data;
		$acc = new coupon($acid);
		$status = $acc->LocationBatchAdd($post);

		if(is_error($status)) {
			message($status['message'], referer(), 'error');
		}
		$insert['uniacid'] = $_W['uniacid'];
		$insert['acid'] = $acid;
		$insert['business_name'] = trim($_GPC['business_name']);
		$insert['branch_name'] = trim($_GPC['branch_name']);
		$insert['category'] = trim($_GPC['category']). '-' . trim($_GPC['subclass']);
		$insert['province'] = trim($_GPC['reside']['province']);
		$insert['city'] = trim($_GPC['reside']['city']);
		$insert['district'] = trim($_GPC['reside']['district']);
		$insert['address'] = trim($_GPC['address']);
		$insert['longitude'] = trim($_GPC['baidumap']['lng']);
		$insert['latitude'] = trim($_GPC['baidumap']['lat']);
		$insert['telephone'] = trim($_GPC['telephone']);
		$insert['location_id'] = $status['location_id_list'][0];
				pdo_insert('coupon_location', $insert);
		$id = pdo_insertid();
		message('添加门店成功', url('wechat/manage/location_list'), 'success');
	}
}

if($do == 'location_list') {
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$condition = ' WHERE `uniacid`=:uniacid AND acid = :aid';
	$pars = array();
	$pars[':uniacid'] = $_W['uniacid'];
	$pars[':aid'] = $acid;
	$keyword = trim($_GPC['keyword']);
	if(!empty($keyword)) {
		$condition .= " AND (business_name LIKE '%{$keyword}%' OR address LIKE '%{$keyword}%')";
	}

	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('coupon_location').$condition, $pars);
	$list = pdo_fetchall("SELECT * FROM ".tablename('coupon_location') . $condition ." ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.','.$psize, $pars);
	$pager = pagination($total, $pindex, $psize);
}

if($do == 'location_del') {
	$id = intval($_GPC['id']);
	pdo_delete('coupon_location', array('uniacid' => $_W['uniacid'], 'acid' => $acid, 'id' => $id));
	message('删除门店成功', url('wechat/manage/location_list'), 'success');
}

if($do == 'whitelist') {
	$whitelist = pdo_fetchcolumn('SELECT whitelist FROM ' . tablename('coupon_setting') . ' WHERE uniacid = :aid AND acid = :acid', array(':aid' => $_W['uniacid'], ':acid' => $acid));
	if(!empty($whitelist)) {
		$whitelist = @iunserializer($whitelist);
	}
	if(checksubmit('submit')) {
		if(!empty($_GPC['username'])) {
			$data = array();
			foreach($_GPC['username'] as $da) {
				$da = trim($da);
				if(empty($da)) {
					continue;
				}
				$i++;
				$data[] = trim($da);
				if($i >= 10) {
					break;
				}
			}
		}

		load()->classs('coupon');
		$acc = new coupon($acid);
		$post['username'] = $data;
		$status = $acc->SetTestWhiteList($post);
		if(is_error($status)) {
			message($status['message'], '', 'error');
		} else {
			$data = iserializer($data);
			pdo_update('coupon_setting', array('whitelist' => $data), array('uniacid' => $_W['uniacid'], 'acid' => $acid));
		}

		message('设置测试黑名单成功', referer(), 'success');
	}
}
template('wechat/manage');