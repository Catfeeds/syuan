<?php
namespace Common\Model;

use Common\Model\CommonModel;

class SmsValidateModel extends CommonSmsModel {
	
	/**
	 * 生成验证码, 发送成功返回数字验证码, 失败返回
	 * @param integer $type
	 * @param string  $mobile
	 * @param string  $client_ip
	 * return array
	 */
	function generate($type, $mobile, $client_ip) {
		$result = array('success' => false, 'msg' => '发送失败');
		if($this->isTypeExists($type)) {	
			if($this->checkMobile($type, $mobile)) {
				if($this->checkClientip($client_ip)) {
					$time = time();
					$second = date('s', $time);
					do {
						$code = substr_replace(mt_rand(pow(10, 3), pow(10, 4) - 1), $second, rand(0, 2), 2);
						$where = array(
							'mobile' => $mobile,
							'type' => $type,
							'code' => $code,
							'create_at' => array('gt', date('Y-m-d H:i:s', $time - 86400))
						);
					} while($this->where($where)->count() > 0);
					$data = array(
						'type' => $type,
						'mobile' => $mobile,
						'code' => $code,
						'create_at' => date('Y-m-d H:i:s', $time),
						'client_ip' => $client_ip,
					);
					if($this->add($data)) {
						$result['success'] = true;
						$result['msg'] = $code;
					}
				} else {
					$result['msg'] = 'IP限制';
				}
			} else {
				$result['msg'] = '手机限制';
			}
		} else {
			$result['msg'] = '参数错误';
		}
		return $result;
	}
	
	//短信验证码验证
	function validate($type, $mobile, $code) {
		if($this->isTypeExists($type)) {
			$time = time();
			$where = array(
				'mobile' => $mobile,
				'type' => $type,
				'code' => $code,
				'create_at' => array('gt', date('Y-m-d H:i:s', $time - 86400)),
				'validate_at' => array('lt', '2000-12-12 12:12:12')
			);
			$sms = $this->where($where)->order('id desc')->find();
			if($sms) {
				if($this->where(array('id' => $sms['id']))->save(array('validate_at' => date('Y-m-d H:i:s', $time)))) {
					return true;
				}
			}
		}
		return false;
	}
	
	public function isTypeExists($type) {
		return isset($this->types[$type]);
	}
	
	//单个手机号一日3条
	public function checkMobile($type, $mobile) {
		$where = array(
			'mobile' => $mobile,
			'type' => $type,
			'create_at' => array('gt', date('Y-m-d H:i:s', time() - 86400))
		);
		return $this->where($where)->count() < 3;
	}
	
	//单个IP一日10条
	public function checkClientip($client_ip) {
		$where = array(
			'client_ip' => $client_ip,
			'create_at' => array('gt', date('Y-m-d H:i:s', time() - 86400))
		);
		return $this->where($where)->count() < 10;
	}
}