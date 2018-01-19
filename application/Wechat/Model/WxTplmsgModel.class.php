<?php
namespace Wechat\Model;
use Common\Model\CommonModel;

class WxTplmsgModel extends CommonModel {
	
	protected $tableName = 'wechat_tplmsg';

	public function dealWechatTplmsg($msg) {
		
		return $msg;
	}
}