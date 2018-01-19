<?php

// +----------------------------------------------------------------------

// | BainiuCMS [ WE CAN DO IT MORE SIMPLE ]

// +----------------------------------------------------------------------

// | Copyright (c) 2013-2014 http://www.bainiu.com All rights reserved.

// +----------------------------------------------------------------------

// | Author: lezhizhe_net <lezhizhe_net@163.com>

// +----------------------------------------------------------------------

/**

 * 功    能：结合ThinkSDK完成腾讯,新浪微博,人人等用户的第三方登录

 * 修改日期：2013-12-11

 */

namespace Api\Controller;

use Common\Controller\HomebaseController;

class OauthController extends HomebaseController {



	function _initialize() {}



	//登录地址

	public function login($type = null) {

		empty($type) && $this->error('参数错误');

        $http_referer = urlencode(I('server.HTTP_REFERER', '', 'trim'));

        $referer = I('referer', $http_referer, 'trim');

		session('login_http_referer', $referer);

		if($type == 'wechat') {//微信登录

			return $this->_wechat_login();

		}

		//加载ThinkOauth类并实例化一个对象

		import("ThinkOauth");

		$sns  = \ThinkOauth::getInstance($type);

		//跳转到授权页面

		redirect($sns->getRequestCodeURL());

	}



	//授权回调地址

	public function callback($type = null, $code = null) {



		(empty($type)) && $this->error('参数错误');

		if($type == 'wechat') {

			return $this->_wechat_callback();

		}

		if(empty($code)) {

			redirect(__ROOT__."/");

		}



		//加载ThinkOauth类并实例化一个对象

		import("ThinkOauth");

		$sns  = \ThinkOauth::getInstance($type);



		//腾讯微博需传递的额外参数

		$extend = null;

		if($type == 'tencent') {

			$extend = array('openid' => I("get.openid"), 'openkey' => I("get.openkey"));

		}

		//请妥善保管这里获取到的Token信息，方便以后API调用

		//调用方法，实例化SDK对象的时候直接作为构造函数的第二个参数传入

		//如： $qq = ThinkOauth::getInstance('qq', $token);

		$token = $sns->getAccessToken($code , $extend);

		//获取当前登录用户信息

		if(is_array($token)){

			$user_info = A('Type', 'Event')->$type($token);

			if(sp_is_user_login()) {
                if(session('oauth_bang')) {
                    $this->_bang_handle($user_info, $type, $token);
                } else {
                    redirect(urldecode($this->_get_login_redirect()));
                }
			} else {

				$this->_login_handle($user_info, $type, $token);

			}

		} else {

			$this->success('登录失败！',$this->_get_login_redirect());

		}

	}





	function bang($type="") {

		if(sp_is_user_login()) {

			empty($type) && $this->error('参数错误');

			//加载ThinkOauth类并实例化一个对象

			import("ThinkOauth");

			$sns  = \ThinkOauth::getInstance($type);

			//跳转到授权页面

			session('oauth_bang', 1);

			redirect($sns->getRequestCodeURL());

		} else {

			$this->error("您还没有登录！", UU('user/login/index'));

		}

	}



	/**

	 * 微信登录页面

	 */

	private function _wechat_login() {

		if(sp_is_mobile() && sp_is_weixin()) {

			$wechat_account = I('get.mp', '', 'trim');

			if($wechat_account) {

				$wechatmp = D('Wechat/Wechat', 'Logic')->getMpByAccount($wechat_account);

				if($wechatmp) {

					if($wechatmp['type'] == 3) {

						$wechatmp['oauth'] = array(

							'scopes' => array('snsapi_userinfo'),

							'callback' => leuu('Api/Oauth/callback', array('type' => 'wechat', 'mp' => $wechat_account))

						);

						$this->wechat = D('Wechat/Wechat', 'Logic')->init($wechatmp);

						$response = $this->wechat->getApi()->oauth->redirect()->send();

					} else {

						$this->error("该公众号无权使用网页授权!");

					}

				} else {

					$this->error("公众号错误-1!");

				}

			} else {

				$this->error("参数错误!");

			}

		} else {

			$this->error("请在微信中打开该页面!");

		}

		return false;

	}



	/**

	 * 微信授权回调页面

	 */

	private function _wechat_callback() {

		if(sp_is_mobile() && sp_is_weixin()) {

			$wechat_account = I('get.mp', '', 'trim');

			if($wechat_account) {

				$wechatmp = D('Wechat/Wechat', 'Logic')->getMpByAccount($wechat_account);

				if($wechatmp) {

					if($wechatmp['type'] == 3) {

						$this->wechat = D('Wechat/Wechat', 'Logic')->init($wechatmp);

						$user = $this->wechat->getApi()->oauth->user();

						if($user->getId()) {

							$user_info = array('name' => $user->getName(), 'head' => $user->getAvatar());

							$token_info = $user->getToken()->toArray();

							$openid = $user->getId();

							$token = array('openid' => $openid, 'access_token' => $token_info['access_token'], 'refresh_token' => $token_info['refresh_token'], 'expires_in' => $token_info['expires_in']);

							$type = 'wechat';

							if(sp_is_user_login()) {

								$this->_bang_handle($user_info, $type, $token);

							} else {

								$fromuid = 0;

								$fans = D('Wechat/WxFans')->getByOpenid($wechatmp['original_id'], $openid);

								if($fans && $fans['from_wechatid'] > 0) {

									$fromfans = D('Wechat/WxFans')->find($fans['from_wechatid']);

									if($fromfans) {

										$fromuid = intval(M('OauthUser')->where(array("from" => $type, "openid" => $fromfans['openid'], 'status' => 1))->getField('uid'));

									}

								}

								$this->_login_handle($user_info, $type, $token, $fromuid);

							}

						} else {

							$this->error("获取用户授权信息失败!", $referer);

						}

					} else {

						$this->error("该公众号无权使用网页授权!");

					}

				} else {

					$this->error("公众号错误-2!");

				}

			} else {

				$this->error("参数错误!");

			}

		} else {

			$this->error("请在微信中打开该页面!");

		}

		return false;

	}



	private function _get_login_redirect() {

		$login_http_referer = urldecode(session('login_http_referer'));

		return empty($login_http_referer) ? __ROOT__."/" : $login_http_referer;

	}



	//绑定第三方账号

	private function _bang_handle($user_info, $type, $token) {

		session('oauth_bang', null);

		$current_uid = sp_get_current_userid();

		$type = strtolower($type);

		$find_oauth_user = M('OauthUser')->where(array("from" => $type, "openid" => $token['openid']))->find();





		if($find_oauth_user && $find_oauth_user['status'] == 1) {

			if($find_oauth_user['uid'] == $current_uid) {

				exit('您之前已经绑定过此账号！');

				$this->error("您之前已经绑定过此账号！", U('user/profile/bang'));

			} else {

				exit('该帐号已被本站其他账号绑定！');

				$this->error("该帐号已被本站其他账号绑定！", U('user/profile/bang'));

			}

		}



		if($current_uid) {

			if($this->_refresh_oauth_user($type, $user_info, $token, $current_uid)) {

				$this->success("绑定成功！",U('user/profile/bang'));

			} else {

				$this->error("绑定失败！",U('user/profile/bang'));

			}

		} else {

			$this->error("绑定失败！", U('user/profile/bang'));

		}

	}



	//登录

	private function _login_handle($user_info, $type, $token, $fromuid = 0) {

		$type = strtolower($type);

		$find_oauth_user = M('OauthUser')->where(array("from" => $type, "openid" => $token['openid']))->find();



		if($find_oauth_user && $find_oauth_user['status'] == 1) {

			$find_user = M('Users')->where(array("id" => $find_oauth_user['uid']))->find();

			if($find_user) {

				if($find_user['user_status'] == '0'){

					$this->error('您可能已经被列入黑名单，请联系网站管理员！');

				} else {

					sp_user_login($find_user);

					$login_data = array(

							'last_login_time' => date("Y-m-d H:i:s"),

							'last_login_ip' => get_client_ip(0, true),

						);

					M("Users")->where(array('id' => $find_user['id']))->save($login_data);

					$login_data['login_times'] = $find_oauth_user['login_times'] + 1;

					M('OauthUser')->where('id='.$find_oauth_user['id'])->save($login_data);

					redirect(urldecode($this->_get_login_redirect()));

					return false;

				}

			}

		}

		$users_model = M("Users");

		$user_login = mb_substr($user_info['name'], 0, 12, 'UTF-8');


       // while(!empty($users_model->where(array('user_login' => $user_login))->find())) {
       //     $user_login .= sp_random_string(1);
       // }

		//while($users_model->where("user_login = CONVERT('{$user_login}' USING utf8) COLLATE utf8mb4_general_ci")->count() > 0) {

		//	$user_login .= sp_random_string(1);

		//}

		//本地用户中创建对应一条数据

		$new_user_data = array(

			'user_login' => $user_login,

			'mobile' => '',

			'user_email' => '',

			'user_nicename' => $user_info['name'],

			'user_pass' => md5($user_info['name']),

			'pass_salt' => sp_random_string(6),

			'fromuid' => $fromuid,

			'avatar' => $user_info['head'],

			'last_login_time' => date("Y-m-d H:i:s", TIMESTAMP),

			'last_login_ip' => get_client_ip(0, true),

			'create_time' => date("Y-m-d H:i:s", TIMESTAMP),

			'user_status' => '1',

			"user_type"	  => '2'

		);



		$uid = $users_model->add($new_user_data);



		if($uid) {

			$oauthid = $find_oauth_user ? $find_oauth_user['id'] : 0;

			if($this->_refresh_oauth_user($type, $user_info, $token, $uid, $oauthid)) {

				$new_user_data['id'] = $uid;

				sp_user_login($new_user_data);

				redirect($this->_get_login_redirect());

			} else {

				$users_model->where(array("id" => $uid))->delete();

				$this->error("登录失败", $this->_get_login_redirect());

			}

		} else {

			$this->error("登录失败", $this->_get_login_redirect());

		}

	}



	/**

	 * 更新第三方用户数据, 如果oauthid为

	 * @param array $oauth_user 第三方登录用户接口信息

	 * @param integer $oauthid 第三方登录用户ID

	 * @return  mixed 插入成功返回oauthid, 更新成功返回true, 否则返回false

	 */

	private function _refresh_oauth_user($type, $oauth_user, $token, $uid = 0, $oauthid = 0) {

		$oauth_user_data = array(

			'from' => $type,

			'name' => $oauth_user['name'],

			'head_img' => $oauth_user['head'],

			'last_login_time' => date("Y-m-d H:i:s", TIMESTAMP),

			'last_login_ip' => get_client_ip(0, true),

			'status' => 1,

			'access_token' => $token['access_token'],

			'expires_date' => TIMESTAMP + $token['expires_in']

		);

		if($uid > 0) {

			$oauth_user_data['uid'] = $uid;

		}

		if($oauthid > 0) {

			M('OauthUser')->where('id='.$oauthid)->setInc('login_times', 1);

			return M('OauthUser')->where(array('id' => $oauthid))->save($oauth_user_data);

		} else {

			$oauth_user_data['login_times'] = 1;

			$oauth_user_data['openid'] = $token['openid'];

			$oauth_user_data['create_time'] = date("Y-m-d H:i:s", TIMESTAMP);

			return M('OauthUser')->add($oauth_user_data);

		}



	}

}

