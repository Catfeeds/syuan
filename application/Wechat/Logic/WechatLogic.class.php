<?php

namespace Wechat\Logic;

use EasyWeChat\Foundation\Application;
use Wechat\Model\WxmpModel;

/**
 * 微信服务类
 */
class WechatLogic {
	
	protected $wechatmp = null;//微信公众号数据
	
	protected $options = array(
			/**
			 * Debug 模式，bool 值：true/false
			 * 当值为 false 时，所有的日志都不会记录
			 */
			'debug' => false,
			 /**
			  * 账号基本信息，请从微信公众平台/开放平台获取
			  */
			'app_id'  => '',         // AppID
			'secret'  => '',     // AppSecret
			'token'   => '',          // Token
			'aes_key' => '',
			'log' => array(
				'level'=> 'debug',
				'file' => ''
			),
			/**
			 * OAuth 配置
			 *
			 * scopes：公众平台（snsapi_userinfo / snsapi_base），开放平台：snsapi_login
			 * callback：OAuth授权完成后的回调页地址
			 */
			'oauth' => array(
				'scopes'   => array('snsapi_userinfo'),
				'callback' => '',
			),
			/**
			 * 微信支付
			 */
			'payment' => array(
				'merchant_id'        => '',
				'key'                => '',
				'cert_path'          => '', // XXX: 绝对路径！！！！
				'key_path'           => '', // XXX: 绝对路径！！！！
				'device_info'     => '',
				'sub_app_id'      => '',
				'sub_merchant_id' => '',
			),
		);
		
	protected $api = null;
	
	/**
	 * 初始化公众号信息
	 */
	public function init($wechatmp) {
		if(defined('APP_DEBUG') && APP_DEBUG) {
			$this->options['debug'] = APP_DEBUG;
			
			$this->options['log']['level'] = C('WECHAT_API_LOG_LEVEL');
			$this->options['log']['file'] = C('WECHAT_API_LOG_PATH').$wechatmp['appid'].'.php';
		}
		$this->options['app_id'] = $wechatmp['appid'];
		$this->options['secret'] = $wechatmp['appsecret'];
		if(isset($wechatmp['token'])) {
			$this->options['token'] = $wechatmp['token'];
		}
		if(isset($wechatmp['encrypt']) && $wechatmp['encrypt'] == 3) {
			$this->options['aes_key'] = $wechatmp['aeskey'];
		}
		if(isset($wechatmp['oauth'])) {
			$this->options['oauth'] = $wechatmp['oauth'];
		}
		if(isset($wechatmp['payment'])) {
			$this->options['payment'] = $wechatmp['payment'];
		}
		$this->api = new Application($this->options);
		$this->wechatmp = $wechatmp;
		return $this;
	}
	
	/**
	 * 获取公众号接口类
	 */
	public function getApi() {
		return $this->api;
	}
	
	/**
	 * 获取微信公众号数据
	 * @param string $wechat_accoount 公众号微信号
	 */
	public function getMpByAccount($wechat_account) {

		return D("Wechat/WxMp")->where(array('wechat_account' => $wechat_account))->find();
	}

	/**
	 * 获取粉丝信息 
	 * @param string $openid 粉丝ID
	 * @return array 粉丝信息
	 */
	public function getFans($openid) {
		$fans = array();
		$result = D('Wechat/WxFans')->getByOpenid($this->original_id, $openid);
		if($result && $result['subscribe'] == 1) {
			$fans = $result;
		}
		return $fans;
	}

	/**
	 * 获取公众号数据
	 */
	public function getMp() {
		return $this->wechatmp;
	}
	
	public function __get($property) {
		if(isset($this->wechatmp[$property])) {
			return $this->wechatmp[$property];
		} else if($property == 'api') {
			return $this->getApi();
		}
		return null;
	}
}
