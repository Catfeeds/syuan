<?php

namespace Wechat\Controller;

use Common\Controller\AdminbasewechatController;
use Wechat\Logic\WechatLogic;
use Wechat\Logic\QrcodeLogic;

class AdminQrcodeController extends AdminbasewechatController {

	/*二维码详情*/
	function index() {
		$keyword = I('get.keyword', '', 'trim');
		$category = I('get.category', '', 'trim');
		$where = array('original_id' => $this->wechatmp['original_id']);
		if($category) {
			$where['category'] = $category;
		}
		if($keyword) {
			$map = array();
			$map['nickname'] = array('like', '%'.$keyword.'%');
			$map['remark'] = array('like', '%'.$keyword.'%');
			$map['_logic'] = 'or';
			$openids = D("Wechat/WxFans")->where(array('_complex' => $map))->order("wechatid DESC")->limit(10)->getField('wechatid, openid', true);
			if($openids) {
				$where['openid'] = array('in', $openids);
			}
		}
		$count = D("Wechat/WxQrcode")->where($where)->count();
		$page = $this->page($count, 20);
		$list = D("Wechat/WxQrcode")->where($where)->order("qrid ASC")->limit($page->firstRow . ',' . $page->listRows)->select();
		$fans = array();
		if($list) {
			$openids = array();
			foreach($list as $key => $val) {
				if($val['openid']) {
					$openids[$val['openid']] = $val['openid'];
				}
				if($val['type'] == 1) {
					$val['expire'] = date('Y-m-d H:i:s', $val['expire']);
				}
				$list[$key] = $val;
			}
			if($openids) {
				$fans = D("Wechat/WxFans")->where(array('original_id' => $this->wechatmp['original_id'], 'openid' => array('in', $openids)))->getField('openid,nickname', true);
			}
		}

		$this->assign('fans', $fans);
		$this->assign('list', $list);
		$this->assign('keyword', $keyword);
		$this->assign('category', $category);
		$this->assign('categorys', D("Wechat/WxQrcode")->getCategorys());
		$this->assign("Page", $page->show('Admin'));		
		$this->display();
	}
}