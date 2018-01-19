<?php
namespace Wechat\Controller;

use Common\Controller\AppframeController;

/**
 * 微信公众号API接口
 * 接口URL地址: index.php?g=Wechat&m=Api&a=index&mp=公众号微信号
 */
class ApiController extends AppframeController {
	
	private $wechat = null;
	protected $fansLogic = null;
	
	function index() {
		$wechat_account = I('get.mp', '', 'trim');
		if($wechat_account) {
			$wechatmp = D('Wechat/Wechat', 'Logic')->getMpByAccount($wechat_account);
			if($wechatmp) {
				$this->wechat = D('Wechat/Wechat', 'Logic')->init($wechatmp);
				$this->wechat->getApi()->server->setMessageHandler(array($this, '_messageHandler'));
				$response = $this->wechat->getApi()->server->serve();
				D('Wechat/Fans', 'Logic')->logFansResponse($response);
				$response->send();
			}
		}
		return false;
	}

	/**
	 * 处理自动回复消息
	 */
	public function _messageHandler($message) {
		$openid = $message->FromUserName;
		$this->fansLogic = D('Wechat/Fans', 'Logic')->setContext($this->wechat);
		$fan = $this->fansLogic->syncFans($openid);
		$this->fansLogic->logFansMessage($message);
		$keyword = false;
		switch ($message->MsgType) {
			case 'event':
				if($message->Event == 'CLICK') {//自定义菜单事件
					$keyword = $message->EventKey;
				} else {
					return D('Wechat/Event', 'Logic')->setContext($this->wechat, $this->fansLogic, $message)->reply();
				}
				break;
			case 'text':
				$keyword = $message->Content;
				break;
			case 'image':
				break;
			case 'voice':
				if(isset($message->Recognition) && $message->Recognition) {
					$keyword = $message->Recognition;
				}
				break;
			case 'video':
				break;
			case 'shortvideo':
				break;
			case 'location':
				break;
			case 'link':
				break;
		}
		if($keyword !== false) {
			return D('Wechat/Message', 'Logic')->setContext($this->wechat, $this->fansLogic)->reply($keyword);
		}
		//转发客服消息
		if($this->wechat->type == 3) {
			return new \EasyWeChat\Message\Transfer();	
		}
		return '';
	}
}
