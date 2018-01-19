<?php

/**
 * 拆红包
 */
namespace Qrcode\Controller;
use Common\Controller\MemberwechatbaseController;
class RedbagController extends MemberwechatbaseController {

	//跳转到企业信息页面
	public function index() {
		$code = I('code', '', 'trim');
		if($code) {
			$redbag = M('Redbag')->where(array('code'=>$code))->find();
			if(!$redbag) {
				$this->error('红包不存在！',UU('Qrcode/company/preview',array('hide'=>1,'id'=>$redbag['uid'])));
			}
			if($redbag['winner'] && $redbag['pay_status'] == 1) {
				$nick = M('oauth_user')->where(array('openid'=>$redbag['winner']))->getField('name');
				$this->error('红包已被微信用户'.mb_substr($nick,0,2,'utf-8').'**拆开！',UU('Qrcode/company/preview',array('hide'=>1,'id'=>$redbag['uid'])));
			}
			$start = strtotime($redbag['starttime']);
			$end   = strtotime($redbag['endtime'])+86400;
			if(time() < $start) {
				$this->error('活动还未开始，不能领取！',UU('Qrcode/company/preview',array('hide'=>1,'id'=>$redbag['uid'])));
			}
			if(time() > $end) {
				$this->error('活动已经结束，不能领取！',UU('Qrcode/company/preview',array('hide'=>1,'id'=>$redbag['uid'])));
			}
			if(!$this->openid) {
				$result['msg'] = '请用微信扫码打开！';
				$this->ajaxReturn($result);
			}
			$this->assign('redbag', $redbag);
			$this->display();
		} else {
			$this->error('红包不存在！',UU('Qrcode/company/preview',array('hide'=>1,'id'=>$redbag['uid'])));
		}
	}

	public function open() {
		$code   = I('code', '', 'trim');
		$result = array('result'=>false,'msg'=>'打开失败！');
		if($code) {
			$redbag = M('Redbag')->where(array('code'=>$code))->find();
			if(!$redbag) {
				$result['msg'] = '红包不存在！';
				$this->ajaxReturn($result);
			}
			if($redbag['winner'] && $redbag['pay_status'] == 1) {
				$nick = M('oauth_user')->where(array('openid'=>$redbag['winner']))->getField('name');
				$result['msg'] = '红包已被微信用户'.mb_substr($nick,0,2,'utf-8').'**拆开！';
				$this->ajaxReturn($result);
			}
			$start = strtotime($redbag['starttime']);
			$end   = strtotime($redbag['endtime'])+86400;
			if(time()<$start) {
				$result['msg'] = '活动还未开始，不能领取！';
				$this->ajaxReturn($result);
			}
			if(time() > $end) {
				$result['msg'] = '活动已经结束，不能领取！';
				$this->ajaxReturn($result);
			}
			if(!$this->openid) {
				$result['msg'] = '请用微信扫码打开！';
				$this->ajaxReturn($result);
			}
			//转账
			$wechatmp = D("Wechat/WxMp")->where(array('type' => 3))->order('mpid desc')->find();
			if(!$wechatmp) {
				$result['msg'] = '微信公众号未配置！';
				$this->ajaxReturn($result);
			}
			$payment = M('PaymentConfig')->where(array('type'=>'wechat','status'=>1,'enable'=>1))->find();
			if($payment) {
				$paymentconfig = unserialize($payment['config']);
				$wechatmp['payment'] = array(
					'merchant_id' => $paymentconfig['merchant_id'],
					'key' => $paymentconfig['key'],
					'cert_path' => SITE_PATH.$paymentconfig['apiclient_cert'],
					'key_path' => SITE_PATH.$paymentconfig['apiclient_key'],
				);
				$wechat = D('Wechat/Wechat', 'Logic')->init($wechatmp);
			} else {
				$result['msg'] = '微信支付未配置！';
				$this->ajaxReturn($result);
			}
			$data = array('partner_trade_no' => 'QRCODE'.date('YmdHis').rand(10000, 99999), 'pay_status' => 0, 'winner' => $this->openid);
			try {
				$response = $wechat->getApi()->merchant_pay->send(array(
					'partner_trade_no' => $data['partner_trade_no'],
					'openid'           => $this->openid,
					'check_name'       => 'NO_CHECK',
					'amount'           => $redbag['amount'],
					'desc'             => $redbag['wish'],
					'spbill_create_ip' => '127.0.0.1'
				));
				if($response->return_code == 'SUCCESS') {
					if($response->result_code == 'SUCCESS') {
						$data['payment_no'] = $response->payment_no;
						$data['pay_status'] = 1;
						$data['payment_time'] = $response->payment_time;
					} else {
						$data['err_code'] = $response->err_code;
						$data['err_code_des'] = $response->err_code_des;
					}
				} else {
					$data['err_code'] = $response->return_code;
					$data['err_code_des'] = $response->return_msg;
				}
			} catch(\Exception $e) {
				$data['err_code'] = $e->getCode();
				$data['err_code_des'] = $e->getMessage();
				$log = '微信日志接口错误, File:'.$e->getFile().', Line: '.$e->getLine().', errno:'.$e->getCode().', err:'.$e->getMessage();
				\Think\Log::write($log, 'ERR');
			}
			M('Redbag')->where(array('code'=>$code))->save($data);
			if($data['pay_status']) {
				//成功
				$result['msg']     = '发放成功！';
				$result['success'] = true;
				$this->ajaxReturn($result);
			} else {
				$result['msg']     = '对不起，拆开失败，请稍候再来！';
				$this->ajaxReturn($result);
			}
		} else {
			$result['msg'] = '红包不存在！';
			$this->ajaxReturn($result);
		}
	}
}