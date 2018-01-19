<?php
namespace User\Controller;
use Common\Controller\MemberwechatbaseController;


class WechatController extends MemberwechatbaseController {

	function _initialize() {
		parent::_initialize();
		$this->check_mobile();
    }

    /*会员中心首页*/
    function index() {
    	$this->display();
    }

    /*推广*/
    function spread() {
    	$this->display();
    }
}