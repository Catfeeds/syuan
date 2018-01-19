<?php
/**
 * 会员注册
 */
namespace User\Controller;

use Common\Controller\HomebaseController;

class RegisterController extends HomebaseController {
	
	function index() {
	    if(sp_is_user_login()) { //已经登录时直接跳到首页
	        redirect(__ROOT__."/");
	    } else {
	        $this->display(":register");
	    }
	}
	
	function doregister() {
	    if(IS_POST) {
			$username = I('post.username', '', 'trim');
			$email = I('post.email', '', 'trim');
			$password =I('post.password', '');
			$mobile = I('post.mobile', '', 'trim');
			$mobile_verify = I('post.mobile_verify', '', 'trim');
			
			if(empty($username) || !preg_match('/^[\x{4e00}-\x{9fa5}\-\w]{5,15}$/u', $username)) {
				$this->error('登录名格式错误');
			} else if(empty($email) || !preg_match('/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/', $email)) {
				$this->error('邮箱账号格式错误');
			} else if(empty($password) || !preg_match('/((?=.*[0-9])(?=.*[A-z]))|((?=.*[A-z])(?=.*[^A-z0-9]))|((?=.*[0-9])(?=.*[^A-z0-9]))^.{6,20}$/', $password)) {
				$this->error('密码格式错误');
			} else if(empty($mobile) || !preg_match('/^1[3|4|5|7|8]\d{9}$/', $mobile)) {
				$this->error('手机号格式错误');
			} else if(empty($mobile_verify)
						|| !preg_match('/^\d{4}$/', $mobile_verify)
							|| (!session('regok_'.$mobile.$mobile_verify) && !sp_check_mobile_verify_code(1, $mobile, $mobile_verify))) {
				$this->error('短信验证码错误');
			} else {
				session('regok_'.$mobile.$mobile_verify, true);
				$users_model = D("Common/Users");
				if($users_model->where(array('user_login' => $username))->count() > 0) {
					$this->error('登录名已存在');
				} else if($users_model->where(array('user_email' => $email))->count() > 0) {
					$this->error('邮箱账号已经存在');
				} else if($users_model->where(array('mobile' => $mobile))->count() > 0) {
					$this->error('手机号已存在');
				} else {
					$salt = sp_random_string(6);
					$data = array(
						'user_login' => $username,
						'user_email' => $email,
						'mobile' => $mobile,
						'user_nicename' => $username,
						'user_pass' => md5(md5($password).$salt),
						'pass_salt' => $salt,
						'last_login_ip' => get_client_ip(),
						'create_time' => date("Y-m-d H:i:s"),
						'last_login_time' => date("Y-m-d H:i:s"),
						'user_status' => 1,
						'mobile_status' => 1,
						'email_status' => 0,
						'user_type'=> 2,//会员
					);
					$uid = $users_model->add($data);
					if($uid) {
						$user = $users_model->where(array('id' => $uid))->find();
						sp_user_login($user);
						$this->success("注册成功！", __ROOT__."/");
					}else{
						$this->error("注册失败！", U("user/register/index"));
					}
				}
			}
		} else {
			$this->error('参数错误');
		}
	}
	
	public function checkusername() {
		$username = I('get.username', '', 'trim');
		if($username && D("Common/Users")->where(array('user_login' => $username))->count() > 0) {
			echo 'false';
			return false;
		}		
		echo 'true';
		return false;
	}
	
	public function checkemail() {
		$email = I('get.email', '', 'trim');
		if($email && D("Common/Users")->where(array('user_email' => $email))->count() > 0) {
			echo 'false';
			return false;
		}
		echo 'true';
		return false;
	}
	
	public function checkmobile() {
		$mobile = I('get.mobile', '', 'trim');
		if($mobile && D("Common/Users")->where(array('mobile' => $mobile))->count() > 0) {
			echo 'false';
			return false;
		}
		echo 'true';
		return false;
	}
}