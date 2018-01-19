<?php
/**
 * 会员登录
 */
namespace User\Controller;

use Common\Controller\HomebaseController;

class LoginController extends HomebaseController {
	
	function index() {
	    if(sp_is_user_login()){ //已经登录时直接跳到首页
	        redirect(__ROOT__."/");
	    } else {
	        $this->display(":login");
	    }
	}
	
	function active(){
		$this->check_login();
		$this->display(":active");
	}
	
	function doactive(){
		$this->check_login();
		$current_user = session('user');
		if($current_user['user_status']==2){
		    $this->_send_to_active();
		    $this->success('激活邮件发送成功，激活请重新登录！',U("user/index/logout"));
		}else if($current_user['user_status']==1){
		    $this->error('您的账号已经激活，无需再次激活！');
		}else{
		    $this->error('您的账号无法发送激活邮件！');
		}
	}
	
	function forgot_password() {
		if(IS_POST) {
			if(!sp_check_verify_code()){
				$this->error("验证码错误");
			} else {
				$email=I("post.email", '', 'trim');
				if(empty($email)) {
					$this->error("邮箱不能为空");
				} else if(!preg_match('/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/', $email)) {
					$this->error("邮箱格式不正确");
				} else {
					$users_model = D("Common/Users");
					$find_user = $users_model->where(array('user_email' => $email, 'user_type' => 2))->field('id,user_login,user_nicename,user_email,user_status,user_type,email_status')->find();
					if($find_user) {
						if($find_user['user_status'] == 0) {
							$this->error("账号被禁用");
						} else {
							$timestamp = time();
							$options = get_site_options();
							//邮件标题
							$title = $options['site_name']."密码重置";
							$activekey = md5($find_user['id'].$timestamp.uniqid());
							$active_key_expire = $timestamp + 86400;
							if(!$users_model->where(array('id' => $find_user['id']))->save(array('user_activation_key' => $activekey, 'active_key_expire' => $active_key_expire))) {
								$this->error('密码重置激活码生成失败！');
							}
							//生成激活链接
							$url = U('user/login/password_reset', array('hash' => $activekey), "", true);
							//邮件内容
							$template =<<<EOF
							#username#，你好！<br>
							请点击或复制下面链接进行密码重置, 有效期24小时：<br>
							<a href="http://#link#">http://#link#</a>
EOF;
							$content = str_replace(array('http://#link#', '#username#'), array($url, $find_user['user_login']), $template);
						
							$send_result = sp_send_email($user['user_email'], $title, $content);
							if($send_result['error']){
								$this->error('密码重置邮件发送失败！');
							}
							$this->success("密码重置邮件发送成功, 请登录邮箱查收重置邮件", U('user/login/index'));
						}
					} else {
						$this->error("账号不存在！");
					}
				}
			}
			
		} else {
			$this->display(":forgot_password");
		}
	}
	
	function password_reset() {
		if(IS_POST){
			if(!sp_check_verify_code()){
				$this->error("验证码错误");
			} else {
				$password = I("post.password", '', 'trim');
				$repassword = I("post.repassword", '', 'trim');
				if(empty($password)) {
					$this->error("密码不能为空！");
				} else if(!preg_match('/((?=.*[0-9])(?=.*[A-z]))|((?=.*[A-z])(?=.*[^A-z0-9]))|((?=.*[0-9])(?=.*[^A-z0-9]))^.{6,20}$/', $password)) {
					$this->error("密码格式错误");
				} else if($password != $repassword) {
					$this->error("确认密码不正确");
				} else {
					$users_model = D("Common/Users");
					$user_activation_key = I("post.code", '', 'trim');
					$id = I("post.id", 0, 'intval');
					$find_user = $users_model->where(array('id' => $id, 'user_activation_key' => $user_activation_key))->field('id,user_status,pass_salt')->find();
					if($find_user && $find_user['user_status'] == 1) {
						$password = md5(md5($password).$find_user['pass_salt']);
						$result = $users_model->where(array('id' => $id))->save(array('user_pass' => $password, 'user_activation_key' => ''));
						if($result) {
							$this->success("密码重置成功，请登录！", U("user/login/index"));
							return false;
						}
					}
					$this->error("密码重置失败，重置码无效！");
				}
			}
		} else {
			$hash = I('get.hash', '', 'trim');
			if($hash) {
				$users_model = D("Common/Users");
				$find_user = $users_model->where(array('user_activation_key'=>$hash))->field('id,user_status,user_activation_key,active_key_expire')->find();
				if(empty($find_user)){
					$this->error('重置码无效！',__ROOT__.'/');
				} else if(time() - $find_user['active_key_expire'] > 86400) {
					$this->error('重置邮件已过期, 请重新发送', __ROOT__.'/');
				} else if($find_user['user_status'] == 0) {
					$this->error('账号已被禁用！', __ROOT__.'/');
				} else {
					$this->assign('user', $find_user);
					$this->display(":password_reset");
				}
			} else {
				$this->error('参数错误', __ROOT__."/");
			}
		}
	}
	
	
    //登录验证
    function dologin() {
    	if(!sp_check_verify_code()){
    		$this->error("验证码错误！");
    	}
		$username = I('post.username', '', 'trim');
		$password = I('post.password', '', 'trim');
		if(empty($username)) {
			$this->error("手机号/邮箱/用户名不能为空！");
		}
		if(empty($password)) {
			$this->error("密码不能为空！");
		}
    	
		$where = array();
		//if(preg_match('/^1[3|4|5|7|8]\d{9}$/', $username)) {//手机号登录
			//$where['mobile'] = $username;
		//} elseif(preg_match('/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/', $username)) {//邮箱登陆
           // $where['user_email'] = $username;
        //} else {
            $where['user_login'] = $username;
        //}
		$users_model = D("Common/Users");
        $result = $users_model->where($where)->find();
        
        if(!empty($result)) {

        	$password = md5($password.$result['pass_salt']);
            if($password == $result['user_pass']) {
                if($result['user_status'] == 1) {
					sp_user_login($result);
					//写入此次登录信息
					$data = array(
						'last_login_time' => date("Y-m-d H:i:s"),
						'last_login_ip' => get_client_ip(),
					);
					$users_model->where(array('id'=>$result["id"]))->save($data);
					$referer = session('login_http_referer');
					$redirect = empty($referer) ? __ROOT__."/" : $referer;
					session('login_http_referer', null);
					$this->success("登录成功！", $redirect);
				} else {
					$this->error("账号被禁用！");
				}
            } else {
                $this->error("密码错误！");
            }
        } else {
            $this->error("用户不存在！");
        }
    }
}