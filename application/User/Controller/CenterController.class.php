<?php

/**
 * 会员中心
 */
namespace User\Controller;
use Common\Controller\MemberbaseController;
class CenterController extends MemberbaseController {
	
	protected $users_model;
	function _initialize(){
		parent::_initialize();
	}
    //会员中心
	public function index() {
		
		$oauths = M("OauthUser")->where(array('uid' => $this->user['id'], 'status' => 1))->field('from,name')->select();
    	$new_oauths=array();
    	foreach ($oauths as $oa){
    		$new_oauths[strtolower($oa['from'])] = $oa;
    	}
    	$this->assign("oauths",$new_oauths);
    	$this->display(':center');
    }
}
