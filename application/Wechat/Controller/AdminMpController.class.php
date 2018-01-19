<?php

namespace Wechat\Controller;

use Common\Controller\AdminbaseController;

class AdminMpController extends AdminbaseController {
	
	protected $wxmp_model;
	protected $mp_types = array();
	
	function _initialize() {
		parent::_initialize();
		$this->wxmp_model = D("Wechat/WxMp");
		$this->mp_types = $this->wxmp_model->getTypes();
	}
	
	/**
	 * 公众号列表
	 */
	function index(){
		
		$mplist = $this->wxmp_model->select();
		$this->assign('mp_types', $this->mp_types);
		$this->assign("mplist", $mplist);
		$this->display();
	}
	
	/**
	 * 添加公众号信息
	 */
	function add() {
		$mp_encrypts = $this->wxmp_model->getEncrypts();
		$this->assign('mp_types', $this->mp_types);
		$this->assign('mp_encrypts', $mp_encrypts);
		$this->assign('token', sp_random_string(24));
		$this->assign('aeskey', sp_random_string(43));
		$this->display();
	}
	
	/**
	 * 提交添加公众号信息
	 */
	function add_post() {
		if (IS_POST) {
			if(isset($_POST['avatar']) && $_POST['avatar']) {
				$_POST['avatar']=sp_asset_relative_url($_POST['avatar']);
			}
			if(isset($_POST['qrcode']) && $_POST['qrcode']) {
				$_POST['qrcode']=sp_asset_relative_url($_POST['qrcode']);
			}
			if ($this->wxmp_model->create()) {
				$mpid = $this->wxmp_model->add();
				if ($mpid !== false) {
					$this->success("添加成功！");
				} else {
    				$this->error("添加失败！");
    			}
			} else {
    			$this->error($this->wxmp_model->getError());
    		}
		}
	}
	
	/**
	 * 修改公众号信息
	 */
	function edit() {
		$mpid = intval(I("get.id"));
		$mp = $this->wxmp_model->where("mpid=$mpid")->find();
		if (!$mp) {
			$this->error("公众号不存在！");
		}
		$mp_encrypts = $this->wxmp_model->getEncrypts();
		$this->assign('mp_types', $this->mp_types);
		$this->assign('mp_encrypts', $mp_encrypts);
		$this->assign('mp', $mp);
		$this->display();
	}
	
	/**
	 * 提交修改公众号信息
	 */
	function edit_post() {
		if (IS_POST) {
			if(isset($_POST['avatar']) && $_POST['avatar']) {
				$_POST['avatar']=sp_asset_relative_url($_POST['avatar']);
			}
			if(isset($_POST['qrcode']) && $_POST['qrcode']) {
				$_POST['qrcode']=sp_asset_relative_url($_POST['qrcode']);
			}
			if ($this->wxmp_model->create()) {
				if ($this->wxmp_model->save()!==false) {
					$this->success("保存成功！");
				} else {
					$this->error("保存失败！");
				}
			} else {
				$this->error($this->wxmp_model->getError());
			}
		}
	}
	
	/**
	 * 删除公账号信息
	 */
	function delete() {
		$mpid = intval(I("get.id"));
		if ($this->wxmp_model->delete($mpid)!==false) {
			$this->success("删除成功！");
		} else {
			$this->error("删除失败！");
		}
	}
}