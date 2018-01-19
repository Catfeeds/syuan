<?php
namespace Wechat\Controller;
use Common\Controller\HomebaseController;

class IndexController extends HomebaseController{
	
	function index() {
		$wechatmp = D("Wechat/WxMp")->where(array('type' => 3))->order('mpid desc')->find();
		if($wechatmp) {
			$wechatmp['qrcode'] = sp_get_asset_upload_path($wechatmp['qrcode']);
		}
		$this->assign('wechatmp', $wechatmp);
		$this->display(':index');
	}
}