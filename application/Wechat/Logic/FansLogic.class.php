<?php

namespace Wechat\Logic;

use Wechat\Model\WxFansModel;


/**
 * 微信粉丝服务类
 */
class FansLogic {
		
	protected $wechat = null;//WechatLogic实例
	
	protected $currentfan = array();//当前对话粉丝信息
	

	public function setContext(WechatLogic $wechat) {
		$this->wechat = $wechat;
		return $this;
	}
	
	/**
	 * 获取微信公众号数据
	 * @param string $wechat_accoount 公众号微信号
	 */
	public function getByOpenid($openid) {
		return D('Wechat/WxFans')->getByOpenid($this->wechat->original_id, $openid);
	}
	
	/**
	 * 关注后用户相关信息处理,!!!!粉丝信息已经更新
	 * @return [type] [description]
	 */
	public function subscribe() {
		return D('Wechat/WxFans')->where(array('wechatid' => $this->wechatid, 'subscribe' => 0))->save(array('subscribe' => 1));
	}

	/**
	 * 取消关注后相关信息处理, !!!！粉丝信息已经更新
	 * @return [type] [description]
	 */
	public function unsubscribe() {
		return D('Wechat/WxFans')->where(array('wechatid' => $this->wechatid, 'subscribe' => 1))->save(array('subscribe' => 0));
	}

	/**
	 * 同步粉丝信息, 关注用户距离上次活跃时间超过5分钟更新一次,返回微信粉丝
	 * @param string $openid 粉丝OPENid
	 * @return array $fan
	 */
	public function syncFans($openid) {
		$this->currentfan = D('Wechat/WxFans')->getByOpenid($this->wechat->original_id, $openid);
		if(!$this->currentfan || TIMESTAMP - strtotime($this->currentfan['lastaction_time']) >= 300) {
			try {
				$fan = $this->wechat->getApi()->user->get($openid);
				$fan = D('Wechat/WxFans')->dealWechatFans($fan->toArray());
				if($this->currentfan) {
					$fan['lastaction_time'] = date('Y-m-d H:i:s', TIMESTAMP);
					D('Wechat/WxFans')->where(array('wechatid' => $this->currentfan['wechatid']))->save($fan);
					$fan = array_merge($this->currentfan, $fan);
				} else {
					$fan['original_id'] = $this->wechat->original_id;
					$fan['create_at'] = date('Y-m-d H:i:s', TIMESTAMP);
					$fan['lastaction_time'] = isset($fan['subscribe_time']) && $fan['subscribe_time'] ? $fan['subscribe_time'] : date('Y-m-d H:i:s', TIMESTAMP);
					$fan['wechatid'] = D('Wechat/WxFans')->add($fan);
				}
				$this->currentfan = $fan;
			} catch (\Exception $e) {
				$log = '微信日志接口错误, File:'.$e->getFile().', Line: '.$e->getLine().', errno:'.$e->getCode().', err:'.$e->getMessage();
                \Think\Log::write($log, 'ERR');
			}
		}
	}
	
	/**
	 * 记录粉丝消息发送内容
	 * @param object $message
	 */
	public function logFansMessage($message) {
		$type = 1;
		if($message->MsgType == 'event') {
			$type = 2;
		}
		$replyid = M('WechatFansMessage')->add(array(
				'wechatid' => $this->wechatid,
				'type' => $type,
				'message' => $this->wechat->getApi()->server->getRequest()->getContent(),
				'create_at' => date('Y-m-d H:i:s', TIMESTAMP)
			));
		if($replyid) {
			$this->currentfan['replyid'] = $replyid;
		}
	}
	
	/**
	 * 记录粉丝消息发送内容
	 * @param object $response
	 */
	public function logFansResponse($response) {
		if($this->replyid) {
			M('WechatFansMessage')->where(array('replyid' => $this->replyid))->save(array('reply' => $response->getContent()));
		}
	}
	
	/**
	 * 获取当前微信用户
	 */
	public function getCurrentFan() {
		return $this->currentfan;
	}
	
	public function __get($property) {
		if(isset($this->currentfan[$property])) {
			return $this->currentfan[$property];
		}
		return null;
	}
}
