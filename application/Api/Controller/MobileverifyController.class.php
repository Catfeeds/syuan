<?php

/**
 * 验证码处理
 */
namespace Api\Controller;

use Common\Controller\HomebaseController;

class MobileverifyController extends HomebaseController {

    public function index() {
       echo hook_one("send_mobile_verify_code",array());
    }
	
	//发送验证码
    public function send() {
    	$verify = I('verify', '', 'trim');
		if((!$this->is_login() || $verify) && !sp_check_verify_code()) {
    		$this->error('图片验证码错误');
    	} else {
			$type = I('get.type', 0, 'intval');
			if($type > 0) {
				$mobile = I('get.mobile', '', 'trim');
				if(preg_match('/^1[3|4|5|7|8]\d{9}$/', $mobile)) {
					if($type == 1) {
						if(D("Common/Users")->where(array('mobile' => $mobile))->count() > 0) {
							$this->error('该手机号已注册, 请更换手机号');
						}
					} else if($type == 2) {
						if(D("Common/Users")->where(array('mobile' => $mobile))->count() < 1) {
							$this->error('账号不存在!');
						}
					} else if($type == 3) {
						if(sp_is_user_login()) {
							$userid = sp_get_current_userid();
							if(D("Common/Users")->where(array('mobile' => $mobile, 'id' => array('NEQ', $userid)))->count() > 0) {
								$this->error('该手机号已被其它账号使用, 请更换手机号');
							}
							$user = D("Common/Users")->find($userid);
							if($user['mobile_status'] == 1) {
								$this->error('您的手机号已经验证过了');
							}
						} else {
							$this->error('登录超时, 请重新登录!');
						}
					}
					$result = sp_send_mobile_verify_code($type, $mobile);
					if($result === true) {
						$this->success('ok');
					} else {
						$this->error($result);
					}
				} else {
					$this->error('手机号码错误');
				}
			} else {
				$this->error('参数错误');
			}
		}
	}

	public function send_recover(){
        $verify = I('verify', '', 'trim');
        if((!$this->is_login() || $verify) && !sp_check_verify_code()) {
            $this->error('图片验证码错误');
        } else {
            $type = I('get.type', 0, 'intval');
            if($type > 0) {
                $mobile = I('get.mobile', '', 'trim');
                if(preg_match('/^1[3|4|5|7|8]\d{9}$/', $mobile)) {
                    $result = sp_send_mobile_verify_code($type, $mobile);
                    if($result === true) {
                        $this->success('ok');
                    } else {
                        $this->error($result);
                    }
                } else {
                    $this->error('手机号码错误');
                }
            } else {
                $this->error('参数错误');
            }
        }
    }
}