<?php
namespace Wechat\Model;

use Think\Model\ViewModel;

class WxFansLabelViewModel extends ViewModel {
	
	public $viewFields = array(
		'WechatFans' => array('*'),
		'WechatFansLabelRelation'=>array('labelid',
					'_on'=>'WechatFans.original_id=WechatFansLabelRelation.original_id and WechatFans.openid=WechatFansLabelRelation.openid'),
	);
}