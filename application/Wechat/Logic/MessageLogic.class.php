<?php

namespace Wechat\Logic;

use Wechat\Model\WxAutoReply;
use EasyWeChat\Message\Text;
use EasyWeChat\Message\News;
/**
 * 消息回复服务类
 */
class MessageLogic {
		
	protected $wechat = null;//WechatLogic实例
	protected $fansLogic = null;//FansLogic 实例

	public function setContext(WechatLogic $wechat, FansLogic $fansLogic) {
		$this->wechat = $wechat;
		$this->fansLogic = $fansLogic;
		return $this;
	}
	
	/**
	 * 根据关键词返回自动回复内容
	 */
	public function reply($keyword = '') {
		$result = array();
		if($keyword) {
			$result = D("Wechat/WxAutoReply")->getReplyByKeyword($this->wechat->original_id, $keyword);
			if(!$result) {//查询新闻内容
				$result = D('Portal/Posts')->getWechatMsgByKeyword($keyword);
			}
		}
		if(!$result && $this->wechat->reply_noanswer) {//回答不上了
			$result = unserialize($this->wechat->reply_noanswer);
		}
		if($result) {
			return $this->parseReply($result);
		}
		if($this->wechat->type == 3) {
			return new \EasyWeChat\Message\Transfer();	
		}
		return '';
	}

	/**
	 * 解析自动回复数据表数据
	 * @param  array $result array
	 * @return EasyWeChat\Message
	 */
	public function parseReply($result) {
		switch ($result['type']) {
			case '4':	//多图文
				$news = array();
				foreach ($result['content'] as $item) {
					$news[] = new News(array(
				        'title'       => $item['title'],
				        'description' => $item['subtitle'],
				        'url'         => wechat_reply_url($item['oid'], $result['content']['from']),
				        'image'       => wechat_get_img_url($item['thumb'])
			    	));
				}
				return $news;
				break;
			case '3':	//单图文
				return new News(array(
				        'title'       => $result['content']['title'],
				        'description' => $result['content']['subtitle'],
				        'url'         => wechat_reply_url($result['content']['oid'], $result['content']['from']),
				        'image'       => wechat_get_img_url($result['content']['thumb'])
			    	));
				break;
			case '2':	//图片
				# code...
				# return new Image(['media_id' => $mediaId]);
				break;
			case '1':	//文本
				$result['content'] = html_entity_decode($result['content']);
				return new Text(array('content' => $result['content']));
				break;
		}
	}
}