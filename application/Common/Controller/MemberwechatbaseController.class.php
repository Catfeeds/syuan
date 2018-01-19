<?php
namespace Common\Controller;
use Common\Controller\HomebaseController;

class MemberwechatbaseController extends HomebaseController {
	
	protected $wechatmp = array();//微信公众号
    protected $fans = array();
    protected $openid = '';

	function _initialize() {
		parent::_initialize();
        $this->wechatmp = D("Wechat/WxMp")->where(array('type' => 3))->order('mpid desc')->find();
        if(!$this->wechatmp) {
            $this->error('请先后台设置微信认证服务公众号');
        } else {
            if($this->is_login()) {
                $this->check_user();
                $this->init_fans();//微信用户信息
            } else {                
                if(IS_AJAX) {
                    $url = UU('api/oauth/login', array('type' => 'wechat', 'mp' => $this->wechatmp['wechat_account'], 'referer' => urlencode(I('server.HTTP_REFERER', ''))), false, true);
                    $this->error('请先微信登录授权系统', $url);
                } else {
                    $url = UU('api/oauth/login', array('type' => 'wechat', 'mp' => $this->wechatmp['wechat_account'], 'referer' => urlencode(sp_get_current_url())), false, true);
                    redirect($url);
                }
            }

            $this->assign('openid', $this->openid);
            $this->assign('fans', $this->fans);
            $this->assign('wechatmp', $this->wechatmp);
        }
	}

    /*手机号验证*/
    protected function check_mobile() {
        if(!$this->user['mobile_status']) {
            $referer = urlencode(sp_get_current_url());
            $url = UU('Wechat/User/regmobile', array('referer' => $referer), false, true);
            $this->tip('请先绑定您的手机号码', $url, 3);
        }
    }

    /**
     * 初始化微信用户
     */
    protected function init_fans() {
		$openid =  M('OauthUser')->where(array('uid' => $this->user['id'], 'status' => 1))->getField('openid');
        if($openid) {
            $where = array('original_id' => $this->wechatmp['original_id'], 'openid' => $openid);
            $this->openid = $openid;
            $this->fans = D("Wechat/WxFans")->where($where)->find();
        }
    }

    /**
     * 检查JSAPI微信支付网页跳转回来后微信支付结果
     * @param string $trade_no 订单编号
     * @param string $pay_result JS订单处理结果
     * @return array
     */
    protected function check_jspay_callback($trade_no, $pay_result = '') {
        $skey = session('jspay_callback_'.$trade_no);
        if(!$skey || $skey != $trade_no) {
            return false;
        }
        session('jspay_callback_'.$trade_no, null);
        $result = array('success' => false, 'msg' => '', 'charge' => array());
        if($pay_result == 'get_brand_wcpay_request:cancel') {
            $result['msg']  = '支付过程中用户取消';
        } else if($pay_result == 'get_brand_wcpay_request:fail') {
            $result['msg']  = '支付失败';
        } else {
            $charge = D('Finance/PaymentCharge')->where(array('trade_no' => $trade_no, 'uid' => $this->user['id']))->find();
            if($charge) {
                if($charge['status'] == 0) {
                    $payment = M('PaymentConfig')->where(array('type'=>'wechat','status'=>1,'enable'=>1))->find();
                    if($payment) {
                        $paymentconfig = unserialize($payment['config']);
                        $config = array(
                            'appid' => $paymentconfig['appid'],
                            'appsecret' => $paymentconfig['appsecret'],
                            'payment' => array(
                                'merchant_id' => $paymentconfig['merchant_id'],
                                'key' => $paymentconfig['key'],
                                'cert_path' => SITE_PATH.$paymentconfig['apiclient_cert'],
                                'key_path' => SITE_PATH.$paymentconfig['apiclient_key'],
                            )
                        );
                        try {
                            $wechat = D('Wechat/Wechat', 'Logic')->init($config);
                            $response = $wechat->getApi()->payment->query($trade_no);
                            if($response && $response->return_code == 'SUCCESS') {
                                if($response->result_code == 'SUCCESS' && $reponse->trade_state == 'SUCCESS' && $trade_no == $response->out_trade_no) {
                                    $real_total =  defined('PAY_DEBUG') && PAY_DEBUG ? $charge['total'] : $response->cash_fee;
                                    $pay_sn = $response->transaction_id;
                                    $paid_at = date('Y-m-d H:i:s', strtotime($response->time_end));
                                    $data = array();
                                    $data['real_total'] = $real_total;
                                    $data['pay_sn'] = $pay_sn;
                                    $data['paid_at'] = $paid_at;
                                    $data['status'] = 1;
                                    foreach($data as $key => $val) {
                                        $charge[$key] = $val;
                                    }
                                    if(D('Finance/PaymentCharge')->where(array('trade_no' => $trade_no, 'uid' => $this->user['id'], 'status' => $charge['status']))->save($data)) {
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
                                    if($real_total == $charge['total']) {
                                        $result['charge'] = $charge;
                                        $result['success'] = true;
                                        $result['msg'] = '支付成功';
                                    } else {
                                        $result['msg'] = '支付金额不足';
                                    }
                                } else {
                                    return false;
                                }
                            } else {
                                return false;
                            }
                        } catch (\Exception $e) {
                            $log = '微信日志接口错误, File:'.$e->getFile().', Line: '.$e->getLine().', errno:'.$e->getCode().', err:'.$e->getMessage();
                            \Think\Log::write($log, 'ERR');
                            return false;
                        }
                    } else {
                        return false;
                    }
                } elseif($charge['status'] == 1) {
                    if($charge['total'] == $charge['real_total']) {
                        $result['charge'] = $charge;
                        $result['success'] = true;
                        $result['msg'] = '支付成功';
                    } else {
                        $result['msg'] = '支付金额不足';
                    }
                } else {
                    $result['msg'] = '支付失败';
                }
            } else {
                return false;
            }
        }
        return $result;
    }
}