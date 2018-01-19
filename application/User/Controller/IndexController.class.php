<?php

/**
 * 会员注册登录
 */
namespace User\Controller;

use Common\Controller\HomebaseController;

class IndexController extends HomebaseController {
    
	//游客查看会员信息主页
	public function index() {
		
		$id = I("get.id", 0, 'intval');
		$user = M("Users")->where(array("id"=>$id))->find();
		
		if(empty($user)) {
			$this->error("查无此人！");
		}
		$this->assign($user);
		$this->display(":index");

    }
    
    function is_login(){
    	if(sp_is_user_login()){
    		$user = sp_get_current_user();
    		if($user['avatar']) {
    			$user['avatar'] = UU('User/Public/avatar', array('id' => $user['id']));
    		}
    		$this->ajaxReturn(array("status"=>1, "user" => array('avatar' => $user['avatar'], 'nicename' => $user['user_nicename'] === '' ? $user['user_login'] : $user['user_nicename'])));
    	}else{
    		$this->ajaxReturn(array("status"=>0, "info"=>"此用户未登录！"));
    	}
    }

    //退出
	public function logout(){
		session("user", null);
		$this->success("退出成功！", UU("User/Login/index"));
    }
}