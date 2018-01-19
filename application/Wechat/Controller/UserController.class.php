<?php
namespace Wechat\Controller;
use Common\Controller\MemberwechatbaseController;

class UserController extends MemberwechatbaseController {

	function regmobile() {
		$referer = I('referer', '', 'trim');
		if($referer) {
			$referer = urldecode($referer);
		}
		if($this->user['mobile_status'] == 1) {
			$this->error('您已绑定过手机号!', $referer);
		}
		if(IS_POST) {
            $mobile = I('post.mobile', '', 'trim');
            $mobile_verify = I('post.code', '', 'trim');
            if(empty($mobile) || !preg_match('/^1[3|4|5|7|8]\d{9}$/', $mobile)) {
                $this->error('手机号格式错误');
            } else if(empty($mobile_verify)
                        || !preg_match('/^\d{4}$/', $mobile_verify)
                            || !sp_check_mobile_verify_code(3, $mobile, $mobile_verify)) {
                $this->error('短信验证码错误');
            } else if(D("Common/Users")->where(array('mobile' => $mobile, 'id' => array('NEQ', $this->user['id'])))->count() > 0) {
                    $this->error('该手机号已存在');
            } else {
                if(D("Common/Users")->save(array('id' => $this->user['id'], 'mobile' => $mobile, 'mobile_status' => 1))) {
                    $this->success('验证成功');
                } else {
                    $this->error('验证失败');
                }
            }
        }
        if(!$referer) {
        	$this->error('参数错误!');
        }	
		$backable = false;
		$backable = I('backable', 0, 'intval');

		$options = sp_get_site_options();
		$site_host = parse_url($options['site_host']);
		$options['site_host'] = $site_host['host'];

		$this->assign('site_options', $options);
		$this->assign('referer', $referer);
		$this->assign('backable', $backable);
		$this->display();
	}
}