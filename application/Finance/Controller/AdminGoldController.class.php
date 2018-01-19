<?php

namespace Finance\Controller;
use Common\Controller\AdminbaseController;

class AdminGoldController extends AdminbaseController {

	function _initialize() {
		parent::_initialize();
	}

	function index() {
		$where = array();
		$uid = I('uid', 0, 'intval');
		$mobile = I('mobile', '', 'trim');
		$typevalue = I('typevalue', 0, 'intval');
		$starttime = I('starttime');
		$endtime = I('endtime');
		if($uid > 0) {
			$where['uid'] = $uid;
		} else {
			$uid = '';
		}
		if($mobile) {
			$where['mobile'] = $mobile;
		}
		if($typevalue > 0) {
			$where['type'] = $typevalue;
		}
		if($starttime && $endtime && ($endtime > $starttime)) {
			$where['create_at'] = array('BETWEEN', array($starttime, date('Y-m-d H:i:s', strtotime($endtime) + 86400)));
		}
		if($starttime && !$endtime) {
			$where['create_at'] = array('EGT', $starttime);
		}
		if(!$starttime && $endtime) {
			$where['create_at'] = array('ELT', $endtime);
		}

		$count = D('Finance/UsersGoldLog')->field('id')->where($where)->count();
		$page = $this->page($count, 20);
		$list = D('Finance/UsersGoldLog')->where($where)->order("id DESC")->limit($page->firstRow . ',' . $page->listRows)->select();
		if($list) {
			foreach($list as $key => $val) {
				$list[$key] = $val;
			}
		}

		$this->assign('uid', $uid);
		$this->assign('mobile', $mobile);
		$this->assign('starttime', $starttime);
		$this->assign('endtime', $endtime);
		$this->assign('typevalue', $typevalue);
		$this->assign('type', D('Finance/UsersGoldLog')->getTypes());
		$this->assign('category', D('Finance/UsersGoldLog')->getCategorys());
		$this->assign("page", $page->show('Admin'));
		$this->assign("list",$list);
		$this->display();
	}
}