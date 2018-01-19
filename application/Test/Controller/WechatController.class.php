<?php
namespace Test\Controller;

use Common\Controller\AppframeController;

/**
 * 微信公众号测试接口
 */
class WechatController extends AppframeController {
	

	function index() {
		echo 'index';
	}

	function autoReply() {
		$original_id = 'gh_209f767b8b85';
		$keyword = '师傅';
		$response = D('Wechat/WxAutoReply')->getReplyByKeyword($original_id, $keyword);
		print_r($response);
		$response = D('Portal/Posts')->getWechatMsgByKeyword($keyword);
		print_r($response);
		$wechat_account = 'ceshi999';
		$wechatmp = D('Wechat/Wechat', 'Logic')->getMpByAccount($wechat_account);
		if($wechatmp) {
			$this->wechat = D('Wechat/Wechat', 'Logic')->init($wechatmp);
		}
		$keyword = '苹果';
		$this->fansLogic = D('Wechat/Fans', 'Logic')->init($this->wechat);
		$response = D('Wechat/Message', 'Logic')->init($this->wechat, $this->fansLogic)->reply($keyword);
		print_r($response);
	}
}
