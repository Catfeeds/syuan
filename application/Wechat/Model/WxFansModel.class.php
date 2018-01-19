<?php
namespace Wechat\Model;

use Common\Model\CommonRelationModel;

class WxFansModel extends CommonRelationModel {
	
	protected $tableName = 'wechat_fans';
		
	public function dealWechatFans($fan) {
		if(!isset($fan['unionid'])) {
			$fan['unionid'] = '';
		}
		if(isset($fan['subscribe_time'])) {
			$fan['subscribe_time'] = date('Y-m-d H:i:s', $fan['subscribe_time']);
		}
		$fan['update_at'] = date('Y-m-d H:i:s', time());
		if(isset($fan['tagid_list'])) {
			$fan['labelids'] = implode(',', $fan['tagid_list']);
			unset($fan['tagid_list']);
		}
		return $fan;
	}
	
	/**
	 * 通过OPENID获取关注账号信息
	 */
	public function getByOpenid($original_id, $openid) {
		return $this->where(array('original_id' => $original_id, 'openid' => $openid))->find();	
	}
}