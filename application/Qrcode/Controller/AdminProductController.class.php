<?php

namespace Qrcode\Controller;
use Common\Controller\AdminbaseController;
class AdminProductController extends AdminbaseController {
	function _initialize() {
		parent::_initialize();
	}

	public function index() {
		$where  = array();
		$name   = I('name', '', 'trim');
		$status = I('status', 1, 'int');
		if($status) {
			$where['status'] = $status;
		}
		if($name) {
			$where['name'] = array('like', '%'.$name.'%');
		}
 		$count = M("Product")->where($where)->count();
    	$page  = $this->page($count, 20);
    	$lists = M("Product")->where($where)->order("pid DESC")->limit($page->firstRow . ',' . $page->listRows)->select();
		$uids = $users = array();
		foreach ($lists as $key => $value) {
			$uids[$value['uid']] = $value['uid'];
			$lists[$key]['createtime'] = date('Y-m-d H:i',$value['createtime']);
		}
		if($uids) {
			$users = M('Users')->where(array('id' => array('in', $uids)))->getField('id,user_nicename');
		}
		$this->assign('users', $users);
		$this->assign('list', $lists);
		$this->assign('status', $status);
		$this->assign('name', $name);
		$this->assign('page', $page->show('Admin'));
		$this->display();
	}

	public function delete() {
		$id = I('id', 0, 'int');
		if($id > 0) {
			if(M('Product')->where(array('pid' => $id))->save(array('status'=>2))!==false) {
				$this->success('删除成功！');
			}
		}
		$this->error('删除失败！');
	}

	public function recover() {
		$id = I('id', 0, 'int');
		if($id > 0) {
			if(M('Product')->where(array('pid' => $id))->save(array('status'=>1))!==false) {
				$this->success('还原成功！');
			}
		}
		$this->error('还原失败！');
	}
}