<?php
// +----------------------------------------------------------------------
// | BainiuCMS [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2014 http://www.bainiu.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: lezhizhe_net <lezhizhe_net@163.com>
// +----------------------------------------------------------------------
/**
 * 参    数：
 * 作    者：lht
 * 功    能：OAth2.0协议下第三方登录数据报表
 * 修改日期：2013-12-13
 */
namespace Api\Controller;
use Common\Controller\AdminbaseController;
class OauthadminController extends AdminbaseController {
	
	//设置
	function setting(){
		
		$qq_callback = UU('Api/Oauth/callback', array('type' => 'qq'), true, true);
		$sina_callback = UU('Api/Oauth/callback', array('type' => 'sina'), true, true);
		$sina_cancel_callback = UU('Api/Oauth/cancel', array('type' => 'sina'), true, true);

		$this->assign("qq_callback", $qq_callback);
		$this->assign("sina_callback", $sina_callback);
		$this->assign('sina_cancel_callback', $sina_cancel_callback);
		
		$config = array('THINK_SDK_QQ' => C('THINK_SDK_QQ'), 'THINK_SDK_SINA' => C('THINK_SDK_SINA'));
		if(!isset($config['THINK_SDK_QQ']['ENABLE'])) {
			$config['THINK_SDK_QQ']['ENABLE'] = 0;
		}
		if(!isset($config['THINK_SDK_SINA']['ENABLE'])) {
			$config['THINK_SDK_SINA']['ENABLE'] = 0;
		}

		$this->assign('config', $config);
		$this->display();
	}
	
	//设置
	function setting_post(){
		if($_POST){
			$qq_enable = I('qq_enable', 0, 'intval');
			$qq_key = I('qq_key', '', 'trim');
			$qq_sec = I('qq_sec', '', 'trim');
			if($qq_enable && (empty($qq_key) || empty($qq_sec))) {
				$this->error('请完整填写QQ互联的APPkey和APPsecret信息');
			}

			$sina_enable = I('sina_enable', 0, 'intval');
			$sina_key = I('sina_key', '', 'trim');
			$sina_sec = I('sina_sec', '', 'trim');
			if($sina_enable && (empty($sina_key) || empty($sina_sec))) {
				$this->error('请完整填写新浪微博的APPkey和APPsecret信息');
			}

			$data = array(
					'THINK_SDK_QQ' => array(
							'ENABLE' => $qq_enable ? true : false,
							'APP_KEY'    => $qq_key,
							'APP_SECRET' => $qq_sec,
							'CALLBACK'   => UU('Api/Oauth/callback', array('type' => 'qq'), true, true),
					),
					'THINK_SDK_SINA' => array(
							'ENABLE' => $sina_enable ? true : false,
							'APP_KEY'    => $sina_key,
							'APP_SECRET' => $sina_sec,
							'CALLBACK'   => UU('Api/Oauth/callback', array('type' => 'sina'), true, true),
					),
			);
			
			$result = sp_set_dynamic_config($data);
			
			if($result) {
				$this->success("更新成功！");
			} else {
				$this->error("更新失败！");
			}
		}
	}
}