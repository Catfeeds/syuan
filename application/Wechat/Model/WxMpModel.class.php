<?php
namespace Wechat\Model;
use Common\Model\CommonModel;

class WxMpModel extends CommonModel {
	
	protected $tableName = 'wechat_mp';
	
	//公众号类型
	protected $_type = array(1 => '认证订阅号', 2 => '未认证订阅号', 3 => '认证服务号', 4 => '未认证服务号');
	
	//公众号消息加密类型
	protected $_encrypt = array(1 => '明文', 2 => '兼容模式', 3 => '安全模式');
	
	//自动验证
	protected $_validate = array(
			//array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
			array('name', 'require', '公众号名称不能重复！', 1, 'unique', 3),
			array('wechat_account', 'require', '微信号不能重复！', 1, 'unique', 3),
			array('original_id', 'require', '原始ID不能重复！', 1, 'unique', 3),
			array('appid', 'require', '公众号AppID不能为空！', 1),
			array('appsecret', 'require', '公众号AppSecret不能为空！', 1),
			array('token', 'require', '公众号token不能为空！', 1),
			array('type', array(1, 2, 3, 4), '公众号类型错误！', 1, 'in'),
			array('encrypt', array(1, 2, 3), '消息加密类型错误！', 1, 'in')
	);
	
	//自动完成
	protected $_auto = array (
         array('create_at', 'getDateTime', 1, 'callback'),
         array('update_at', 'getDateTime', 3, 'callback'), 
	);
	
	public function getTypes() {
		return $this->_type;
	}
	
	public function getEncrypts() {
		return $this->_encrypt;
	}
}