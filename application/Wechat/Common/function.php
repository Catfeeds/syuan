<?php

/**
 * 获取微信回复ID
 * @param  integer $oid  数据ID
 * @param  string $from 数据ID来源
 * @return string       返回完整绝对路径
 */
function wechat_reply_url($oid, $from = 'portal') {
	switch ($from) {
		case 'value':
			# code...
			break;
		default:
			return UU('portal/article/index', array('id' => $oid), true, true);
			break;
	}
}

/**
 * 获取微信ID
 * @param  string $src     公众号图片地址
 * @param  array  $options 微信回复图片支持JPG、PNG格式，较好的效果为大图360*200小图200*200
 * @return string          返回图片绝对地址
 */
function wechat_get_img_url($src, $options = array()) {
	if(empty($src)) {//返回默认图片
		return '';
	}
	return sp_get_asset_upload_path($src, true);
}