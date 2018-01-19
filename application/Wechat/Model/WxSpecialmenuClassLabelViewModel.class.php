<?php
namespace Wechat\Model;

use Think\Model\ViewModel;

class WxSpecialmenuClassLabelViewModel extends ViewModel {
	
	public $viewFields = array(
		'WechatSpecialmenuClass' => array('*', '_type' => 'LEFT'),
		'WechatFansLabel'=>array('name' => 'groupname',
					'_on'=>'WechatSpecialmenuClass.group_id=WechatFansLabel.labelid'),
	);
}