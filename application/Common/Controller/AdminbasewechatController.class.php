<?php

/**
 * 后台公众号功能基础Controller
 */
namespace Common\Controller;
use Common\Controller\AdminbaseController;

class AdminbasewechatController extends AdminbaseController {
	
	protected $wxmp_model;
	protected $wechatmp = array();
	
	public function __construct() {
		parent::__construct();
	}

    function _initialize(){
		parent::_initialize();
		$this->wxmp_model = D("Wechat/WxMp");
		$this->mp_types = $this->wxmp_model->getTypes();
		$mpid = get_current_wechat_mpid();
		if($mpid > 0) {
			$this->wechatmp = $this->wxmp_model->where("mpid=$mpid")->find();
			if($this->wechatmp) {
				$this->assign("wechatmp", $this->wechatmp);
				return true;
			}
		}
		$this->error("请选择当前公众号!");
    }
}