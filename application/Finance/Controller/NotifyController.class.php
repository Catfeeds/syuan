<?php

/**
 * 会员财务中心
 */
namespace Finance\Controller;
use Common\Controller\AppframeController;
use Wechat\Logic\WechatLogic;
use Finance\Logic\FinanceLogic;
use EasyWeChat\Payment\Order;

class NotifyController extends AppframeController {

    protected $wechatmp = array();

	function _initialize(){
		parent::_initialize();
        $this->wechatmp  = D("Wechat/WxMp")->where(array('type' => 3))->order('mpid desc')->find();
	}
	
	/**
	 * 微信支付成功后异步通知接收
	 */
	function wechat() {
		$payment_config = M('PaymentConfig')->where(array('type'=>'wechat','status'=>1,'enable'=>1))->find();
		define('FINANCE_NOTIFY_PAYMENT_ID', $payment_config['id']);
		$wechat_config = unserialize($payment_config['config']);
		$config = array(
			'appid' => $wechat_config['appid'],
			'appsecret' => $wechat_config['appsecret'],
			'payment' => array(
					'merchant_id' => $wechat_config['merchant_id'],
					'key' => $wechat_config['key'],
					'cert_path' => SITE_PATH.$wechat_config['apiclient_cert'],
					'key_path' => SITE_PATH.$wechat_config['apiclient_key'],
				)
		);
		$wechat = D('Wechat/Wechat', 'Logic')->init($config);
		$response = $wechat->getApi()->payment->handleNotify(function($notify, $successful) {
			$charge = D('Finance/PaymentCharge')->where(array('trade_no' => $notify->out_trade_no, 'type' => 'wechat'))->find();
			if(!$charge) {
				return 'Charge not exist.';
			}
			if($charge['status'] > 0) {
				if($charge['status'] == 1) {
					$this->_wechat_update_action_time($charge['uid']);
				}
				return true;
			}
			$data = array();
			$real_total = defined('PAY_DEBUG') && PAY_DEBUG ? $charge['total'] : $notify->total_fee;
			if($successful) {
				$data['pay_sn'] = $notify->transaction_id;
				$data['real_total'] = $real_total;
				$data['paid_at'] = date('Y-m-d H:i:s', TIMESTAMP);
				$data['status'] = 1;
			} else { // 用户支付失败
				$data['status'] = 2;
				$data['log'] = $notify->return_msg;
			}
			if(D('Finance/PaymentCharge')->where(array('id' => $charge['id'], 'status' => $charge['status']))->save($data)) {
				$module = flase;
				if($charge['from'] && false !== strpos($charge['from'], '-')) {
					$m_a = explode('-', $charge['from']);
					$module = ucwords($m_a[0]);
					$action = $m_a[1];
				}
				if($module && class_exists($module.'\Logic\FinanceLogic') && method_exists(D($module.'/Finance', 'Logic'), $action)) {
	                foreach($data as $key => $val) {
	                    $charge[$key] = $val;
	                }
	                D($module.'/Finance', 'Logic')->$action($charge);
		        } else {
		        	D('Finance/Finance', 'Logic')->charge($charge['uid'], 'wechat', $real_total, '微信支付充值, 充值编号:'.$charge['trade_no'], $charge['id']);
		        }
			}
			if($data['status'] == 1) {
				$this->_wechat_update_action_time($charge['uid']);
			}
			return true; // 返回处理完成
		});
		$response->send();
		return false;
	}

	/**
	 * 支付成功更新微信用户最后操作实际
	 * 用于判断是否可以发送客户消息
	 */
	private function _wechat_update_action_time($uid) {
		$openid =  M('OauthUser')->where(array('uid' => $charge['uid'], 'status' => 1))->getField('openid');
		if(!$openid) {
			return false;
		}
		$fan = array('lastaction_time' => date('Y-m-d H:i:s', TIMESTAMP));
		$wechatmp = D("Wechat/WxMp")->where(array('type' => 3))->order('mpid desc')->find();
		D('Wechat/WxFans')->where(array('original_id' => $wechatmp['original_id'], 'openid' => $openid))->save($fan);
	}
}
