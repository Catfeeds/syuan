<?php
namespace Common\Model;
use Common\Model\CommonModel;
class UsersModel extends CommonModel
{
	
	protected $_validate = array(
		//array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
		array('user_login', 'require', '用户名称不能为空！', 1, 'regex', CommonModel:: MODEL_INSERT  ),
		array('user_login', '/^[\x{4e00}-\x{9fa5}\-\w]{5,15}$/u', '用户名格式错误, 支持中文、字母、数字、"-" "_"的组合, 5-15个字符', 0, 'regex', CommonModel:: MODEL_BOTH  ),
		array('user_pass', 'require', '密码不能为空！', 1, 'regex', CommonModel:: MODEL_INSERT ),
		array('user_pass', '/((?=.*[0-9])(?=.*[A-z]))|((?=.*[A-z])(?=.*[^A-z0-9]))|((?=.*[0-9])(?=.*[^A-z0-9]))^.{6,20}$/', '密码格式不正确, 请使用字母、数字和符号两种以上的组合, 6-20个字符', 0, 'regex', CommonModel:: MODEL_BOTH ),
		array('user_login', 'require', '用户名称不能为空！', 0, 'regex', CommonModel:: MODEL_UPDATE  ),
		array('user_pass', 'require', '密码不能为空！', 0, 'regex', CommonModel:: MODEL_UPDATE  ),
		array('user_login','','用户名已经存在！',0,'unique',CommonModel:: MODEL_BOTH ), // 验证user_login字段是否唯一
		array('user_email','','邮箱帐号已经存在！',0,'unique',CommonModel:: MODEL_BOTH ), // 验证user_email字段是否唯一
		array('mobile','','手机号已经存在！',0,'unique',CommonModel:: MODEL_BOTH ), // 验证Mobile字段是否唯一
		array('user_email','email','邮箱格式不正确！',0,'',CommonModel:: MODEL_BOTH ), // 验证user_email字段格式是否正确
	);
	
	protected $_auto = array(
	    array('create_time', 'getDateTime',CommonModel:: MODEL_INSERT, 'callback')
	);
}