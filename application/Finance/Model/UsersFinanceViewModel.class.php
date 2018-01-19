<?php
namespace Finance\Model;

use Think\Model\ViewModel;

class UsersFinanceViewModel extends ViewModel {
	
	public $viewFields = array(
		'UsersFinance' => array('id', 'uid', 'orderid', 'money', 'type', 'category', 'accountbefore', 'accountafter', 'create_at', 'remark'),
		'Users' => array('user_nicename', 'user_login', 'mobile',
					'_on'=>'UsersFinance.uid=Users.id'),
	);
}