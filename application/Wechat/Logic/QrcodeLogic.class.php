<?php

namespace Wechat\Logic;

/**
 * 微信二维码服务类
 */
class QrcodeLogic {
		
	protected $wechat = null;//WechatLogic实例

	public function setContext(WechatLogic $wechat) {
		$this->wechat = $wechat;
		return $this;
	}
	
	/**
	 * 获取粉丝二维码推荐临时二维码
	 * @param string $openid 粉丝OPENid
	 * @param boolean $forever 是否生成永久二维码
	 * @return array
	 */
	public function getRecommendQrcode($openid, $forever = false) {
		$category = 'recommend';
		$result = array('sceneid' => 0, 'url' => '');

		$qrcode = D('Wechat/WxQrcode')->getByCategoryOpenid($this->wechat->original_id, $category, $openid, $forever);
		if($forever) {
			if(!$qrcode) {
				$qrcode = $this->forever($category, $openid);
			}
		} else {			
			if(!$qrcode || ($qrcode['type'] == 1 && $qrcode['expire'] <= time() + 14 * 86400)) {
				if($qrcode) {//删除之前的二维码
					D('Wechat/WxQrcode')->$qrcode['qrid'];
				}
				$qrcode = $this->temporary($category, $openid);	
			}
		}
		if(false === $qrcode) {
			return false;
		}
		$result['sceneid'] = $qrcode['sceneid'];
		$result['url'] = $qrcode['url'];

		return $result;
	}

	/**
	 * 获取临时二维码
	 * @param string $category 二维码分类
	 * @param string $openid 粉丝OPENid
	 * @param string|integer $sceneid 场景ID
	 * @param integer $expire 二维码有效时间
	 * @param array $data 二维码对应数据
	 * @return boolean
	 */
	public function temporary($category, $openid = '', $sceneid = null, $data = array(), $expire = 30 * 86400) {
		$timestamp = time();
		$qrcode = array(
				'original_id' => $this->wechat->original_id,
				'openid' => $openid,
				'category' => $category,
				'type' => 1,
				'sceneid' => $sceneid,
				'expire' => $timestamp + $expire,
				'url' => '',
				'data' => serialize($data),
				'create_at' => date('Y-m-d H:i:s', $timestamp),
				'update_at' => date('Y-m-d H:i:s', $timestamp)
			);
		$reset_sceneid = false;
		if(is_null($sceneid) || $sceneid === '') {
			$reset_sceneid = true;
			$qrcode['sceneid'] = intval(microtime(true) * 10000).rand(100000, 999999);
		}
		$qrid = D('Wechat/WxQrcode')->add($qrcode);
		if($qrid) {
			if($reset_sceneid) {
				$sceneid = $qrid;
			}
			try {
				$result = $this->wechat->getApi()->qrcode->temporary($sceneid, $expire);
				if(isset($result->ticket) && $result->ticket) { 
					$url = $this->wechat->getApi()->qrcode->url($result->ticket);
					if(D('Wechat/WxQrcode')->save(array('qrid' => $qrid, 'sceneid' => $sceneid,'expire' => $timestamp + $result->expire_seconds, 'url' => $url))) {
						$qrcode['qrid'] = $qrid;
						$qrcode['sceneid'] = $sceneid;
						$qrcode['expire'] = $timestamp + $result->expire_seconds;
						$qrcode['url'] = $url;
						return $qrcode;
					} else {
						D('Wechat/WxQrcode')->delete($qrid);
					}
				}
			} catch (\Exception $e) {
				D('Wechat/WxQrcode')->delete($qrid);
				$log = '微信日志接口错误, File:'.$e->getFile().', Line: '.$e->getLine().', errno:'.$e->getCode().', err:'.$e->getMessage();
                \Think\Log::write($log, 'ERR');
			}
		}
		return false;
	}

	/**
	 * 生成永久二维码
	 * @param string $category 二维码分类
	 * @param string $openid 粉丝OPENid
	 * @param mix $sceneid 场景ID
	 * @param array $data 二维码对应数据
	 * @return boolean
	 */
	public function forever($category, $openid = '', $sceneid = '', $data = array()) {
		$timestamp = time();
		$qrcode = array(
				'original_id' => $this->wechat->original_id,
				'openid' => $openid,
				'category' => $category,
				'type' => 3,
				'sceneid' => $sceneid,
				'url' => '',
				'data' => serialize($data),
				'create_at' => date('Y-m-d H:i:s', $timestamp),
				'update_at' => date('Y-m-d H:i:s', $timestamp)
			);
		$reset_sceneid = false;		
		if(is_numeric($sceneid) && substr($sceneid, 0, 1) !== '0' && intval($sceneid) == $sceneid && $sceneid <= 100000) {//永久二维码时最大值为100000（目前参数只支持1--100000）
			$data['type'] = 2;
		} else if(is_null($sceneid) || $sceneid === '') {
			$reset_sceneid = true;
			$qrcode['sceneid'] = intval(microtime(true) * 10000).rand(100000, 999999);
		}
		$qrid = D('Wechat/WxQrcode')->add($qrcode);
		if($qrid) {
			if($reset_sceneid) {
				$sceneid = 'f-'.$qrid;
			}
			try {
				$result = $this->wechat->getApi()->qrcode->forever($sceneid);
				if(isset($result->ticket) && $result->ticket) {
					$url = $this->wechat->getApi()->qrcode->url($result->ticket);
					if(D('Wechat/WxQrcode')->save(array('qrid' => $qrid, 'sceneid' => $sceneid, 'url' => $url))) {
						$qrcode['qrid'] = $qrid;
						$qrcode['sceneid'] = $sceneid;
						$qrcode['url'] = $url;
						return $qrcode;
					} else {
						D('Wechat/WxQrcode')->delete($qrid);
					}
				}
			} catch (\Exception $e) {
				D('Wechat/WxQrcode')->delete($qrid);
				$log = '微信日志接口错误, File:'.$e->getFile().', Line: '.$e->getLine().', errno:'.$e->getCode().', err:'.$e->getMessage();
                \Think\Log::write($log, 'ERR');
			}
		}
		return false;
	}
}
