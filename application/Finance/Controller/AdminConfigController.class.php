<?php

/**
 * 在线支付配置
*/
 
namespace Finance\Controller;

use Common\Controller\AdminbaseController;

class AdminConfigController extends AdminbaseController {

	protected $payment_model = null;
	
	function _initialize() {
		parent::_initialize();
		$this->payment_model = M("PaymentConfig");
	}
	
	/**
	 * 支付方式列表
	 */
	function index() {
		$list = $this->payment_model->where(array('status' => 1))->select();
		$this->assign('list', $list);
		$this->display();
	}
	
	/**
	 * 支付方式查看
	 */
	function setting() {
		$type = I('get.type', '', 'trim');
		$payment = $this->payment_model->where(array('type' => $type, 'status' => 1))->find();
		if(!$payment) {
			$this->error('支付方式不存在');
		}
		if(isset($payment['config']) && !empty($payment['config'])) {
			$payment['config'] = unserialize($payment['config']);
		}
		$this->assign('payment', $payment);
		$this->display('setting_'.$type);
	}
	
	/**
	 * 支付方式配置
	 */
	function setting_post() {
		if(IS_POST) {
			$type = I('post.type', '', 'trim');
			$payment = $this->payment_model->where(array('type' => $type, 'status' => 1))->find();
			if(!$payment) {
				$this->error('支付方式不存在');
			}
			if(isset($payment['config']) && !empty($payment['config'])) {
				$payment['config'] = unserialize($payment['config']);
			}
			switch($type) {
				case 'wechat':
					$config = $payment['config'];
					$config['appid'] = I('post.appid', '', 'trim');
					$config['appsecret'] = I('post.appsecret', '', 'trim');
					$config['merchant_id'] = I('post.merchant_id', '', 'trim');
					$config['key'] = I('post.key', '', 'trim');
					$upload = new \Think\Upload(array(
								'maxSize'    =>    11048576,
								'rootPath'   =>    SITE_PATH.'data/upload/keys/',
								'savePath'   =>    '',
								'saveName'   =>    array('uniqid', ''),
								'exts'       =>    array('pem'),
								'replace'	=>		true,
								'autoSub'    =>    true,
								'subName'    =>    $config['appid'],
							), 'Local');// 实例化上传类
					$info	=	$upload->upload();
					if(!$info) {
						$this->error($upload->getError());
					} else {
						if(isset($info['apiclient_cert'])) {
							$config['apiclient_cert'] = 'data/upload/keys/'.$config['appid'].'/'.$info['apiclient_cert']['savename'];
						}
						if(isset($info['apiclient_key'])) {
							$config['apiclient_key'] = 'data/upload/keys/'.$config['appid'].'/'.$info['apiclient_key']['savename'];
						}
					}
					break;
			}
			if($this->payment_model->where(array('type' => $type))->setField('config', serialize($config))) {
				$this->success("保存成功！");
			} else {
				$this->error('保存失败！');
			}
		}
		return false;
	}
	
	/**
	* 添加支付方式
	*/
	function add() {
		
		$this->display();
	}
	function add_post() {
		if(IS_POST) {
			$data['name'] = I('name');
			$data['remark'] = I('remark');
			if(!$data['name'] || !$data['remark']) {
				$this->error('请补充信息！');
			}
			/*计算手动添加的支付方式的个数*/
			$count = $this->payment_model->where("type like 'offline-%'")->count();
			$data['type'] = 'offline-'.($count+1);
			/*验证TYPE是唯一的*/
			$typecount = $this->payment_model->where("type = '{$data['type']}'")->count();
			if($typecount > 0) {
				$this->error('支付类型已经存在了！');
			}
			$data['enable'] = 1;
			$data['status'] = 1;
			$result = $this->payment_model->add($data);
			if($result !== false) {
				$this->success('添加成功！');
			} else {
				$this->error('添加失败！');
			}
		}
	}
	/*编辑手动支付方式*/
	function edit() {
		$type = I('type', '', 'trim');
		$info = $this->payment_model->where(array('type' => $type, 'status' => 1))->find();
		if($info) {
			$this->assign('info', $info);
			$this->display();
		} else {
			$this->error('支付方式不存在！');
		}
	}
	function edit_post() {
		if(IS_POST) {
			$data['id'] = I('id', 0, 'intval');
			$info = $this->payment_model->where($data)->find();
			if(!$info) {
				$this->error('支付方式不存在！');
			}
			$data['name'] = I('name');
			$data['remark'] = I('remark');
			if(!$data['name'] || !$data['remark']) {
				$this->error('请补充信息');
			}
			$result = $this->payment_model->save($data);
			if($result !== false) {
				$this->success('编辑成功！');
			} else {
				$this->error('编辑失败！');
			}
		}
	}
	
	/**
	 * 启用
	 */
	function enable() {
		$type = I('get.type', '', 'trim');
		$payment = $this->payment_model->where(array('type' => $type))->find();
		if($payment && !$payment['enable']) {
			if($this->payment_model->where(array('type' => $type))->setField('enable', 1)) {
				$this->success("启用成功！");
			}
		}
		$this->error('启用失败！');
	}
	
	/**
	 * 关闭
	 */
	function disable() {
		$type = I('get.type', '', 'trim');
		$payment = $this->payment_model->where(array('type' => $type))->find();
		if($payment && $payment['enable']) {
			if($this->payment_model->where(array('type' => $type))->setField('enable', 0)) {
				$this->success("禁用成功！");
			}
		}
		$this->error('禁用失败！');
	}
}