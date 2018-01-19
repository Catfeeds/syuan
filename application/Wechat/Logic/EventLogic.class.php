<?php

namespace Wechat\Logic;

use EasyWeChat\Message\Text;
use EasyWeChat\Message\News;

/**
 * 事件回复服务类
 */
class EventLogic {
		
	protected $wechat = null;//WechatLogic实例
	protected $fansLogic = null;//FansLogic 实例
	protected $message = null;//事件

	public function setContext(WechatLogic $wechat, FansLogic $fansLogic, $message) {
		$this->wechat = $wechat;
		$this->fansLogic = $fansLogic;
		$this->message = $message;
		return $this;
	}
	
	public function reply() {
		switch($this->message->Event) {
	
			case 'subscribe'://关注
				$this->_subscribe();
				if(isset($this->message->EventKey) && $this->message->EventKey) {
					return $this->_qrcodeScan(true);
				} else if($this->wechat->reply_subscribe) {
					return D('Wechat/Message', 'Logic')->parseReply(unserialize($this->wechat->reply_subscribe));
				}
				break;

			case 'unsubscribe'://取消关注
				$this->_unsubscribe();
				return '';
				break;

			case 'LOCATION'://上报地理位置
				$this->_setLocation();
				break;

			case 'SCAN':
				return $this->_qrcodeScan();
				break;

			/*case 'CLICK':
				# code...
				break;*/

			case 'VIEW':
				#code
				break;

			default:
				# code...
				break;
		}
		return '';
	}

	/**
	 * 扫码事件
	 * @param boolean $issubscribe 是否是关注扫码
	 * @return 消息回复
	 */
	private function _qrcodeScan($issubscribe = false) {
		$scene_value = $this->message->EventKey;
		if(false !== strpos($scene_value, 'qrscene_')) {
			$scene_value = str_replace('qrscene_', '', $scene_value);
		}
		$qrcode = D('Wechat/WxQrcode')->getBySceneid($this->wechat->original_id, $scene_value);
		if($qrcode) {
			$category = $qrcode['category'];
			$type = $qrcode['type'];
			$openid = $qrcode['openid'];
			$data = unserialize($qrcode['data']);
			if($category == 'recommend' && $issubscribe && $this->fansLogic->from_wechatid < 1 && $this->fansLogic->openid != $openid) {
				$from_wechatid = D('Wechat/WxFans')->where(array('original_id' => $this->wechat->original_id, 'openid' => $openid))->getField('wechatid');
				if($from_wechatid > 0) {
					D('Wechat/WxFans')->where(array('original_id' => $this->wechat->original_id, 'openid' => $this->fansLogic->openid))->save(array('from_wechatid' => $from_wechatid));
				}
			}
		} else {
			/*
			$ticket = $this->message->Ticket;
			*/
		}
		//return new Text(array('content' => var_export($scene_value, true)));
	}

	/**
	 * 地理位置上报, 更新用户地理位置信息
	 */
	private function _setLocation() {

	}

	/**
	 * 用户关注处理流程
	 * @return [type] [description]
	 */
	private function _subscribe() {
		$this->fansLogic->subscribe();
	}

	/**
	 * 用户取消关注处理流程
	 * @return [type] [description]
	 */
	private function _unsubscribe() {
		$this->fansLogic->unsubscribe();
	}
}
