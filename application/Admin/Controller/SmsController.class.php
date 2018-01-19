<?php
/**
 * 短信配置
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class SmsController extends AdminbaseController {
	
	protected $option_name = 'sms_setting';

	function _initialize() {
		parent::_initialize();
		$this->sms_tpl_model = D('Common/SmsTpl');
		$this->sms_tpl_type = $this->sms_tpl_model->getTypes();
	}

	/* 短信配置 */
    public function setting() {
		$options = array();
		$where = array('option_name' => $this->option_name);
    	$option = M('Options')->where($where)->find();
    	if($option){
    		$options = json_decode($option['option_value'], true);
    	}
		$this->assign('options', $options);
    	$this->display();
    }
    
    /* 短信配置处理 */
    public function setting_post() {
    	$sms_options_db = M('Options');
		
		$data['option_name'] = $this->option_name;
		$options['status'] = I('status', 0, 'intval');
		$options['signname'] = I('signname', '', 'trim');
		$options['accesskeyid'] = I('accesskeyid', '', 'trim');
		$options['accesssecret'] = I('accesssecret', '', 'trim');
		if(!$options['signname'] || !$options['accesskeyid'] || !$options['accesssecret']) {
			$this->error('请补充信息！');
		}
		$data['option_value'] = json_encode($options);
		
		if($sms_options_db->where("option_name = '{$this->option_name}'")->find()){
    		$result = $sms_options_db->where("option_name = '{$this->option_name}'")->save($data);
    	}else{
    		$result = $sms_options_db->add($data);
    	}
		
    	if($result !== false) {
    		$this->success('保存成功！');
    	} else {
    		$this->error('保存失败！');
    	}
    }
    
    /* 短信模板 */
    public function template(){
    	$type = I('type', 1, 'intval');
		
		$tpl = $this->sms_tpl_model->where("type = {$type}")->find();
		
		$this->assign('tpl', $tpl);
		$this->assign('tpl_type', $this->sms_tpl_type);
		$this->assign('type', $type);
    	$this->display();
    }
    
    public function template_post(){
    	$type = I('type', 1, 'intval');
		
		$tpl_type_ids = array_keys($this->sms_tpl_type);
		if(!in_array($type, $tpl_type_ids)) {
			$this->error('模板类型出错！');
		}
		
		$data['type'] = $type;
		$data['tplcode'] = I('tplcode', '', 'trim');
		$data['remark'] = I('remark', '', 'trim');
		$data['content'] = I('content');
		
		if($this->sms_tpl_model->where("type = {$type}")->find()){
    		$result = $this->sms_tpl_model->where("type = {$type}")->save($data);
    	}else{
			$data['create_at'] = date('Y-m-d H:i:s');
    		$result = $this->sms_tpl_model->add($data);
    	}
		
    	if($result !== false) {
    		$this->success('保存成功！');
    	} else {
    		$this->error('保存失败！');
    	}
    }
	
	/*短信验证列表*/
	function validate_list() {
		$sms_validate_model = D('Common/SmsValidate');
		
		/*处理数据*/
		$where = array();
		$param = I('get.');
		$mobile = isset($param['mobile']) ?  trim($param['mobile']): '';
		$creatstarttime = isset($param['creatstarttime']) ?  $param['creatstarttime'] : '';
		$createndtime = isset($param['createndtime']) ?  $param['createndtime'] : '';
		$valistarttime = isset($param['valistarttime']) ?  $param['valistarttime'] : '';
		$valiendtime = isset($param['valiendtime']) ?  $param['valiendtime'] : '';
		$type = isset($param['type']) ?  intval($param['type']): 0;
		
		if($mobile) {
			$where['mobile'] = $mobile;
		}
		if($type) {
			$where['type'] = $type;
		}
		if($creatstarttime && $createndtime && ($createndtime > $creatstarttime)) {
			$where['create_at'] = array('BETWEEN', array($creatstarttime, date('Y-m-d H:i:s', strtotime($createndtime) + 86400)));
		}
		if($creatstarttime && !$createndtime) {
			$where['create_at'] = array('EGT', $creatstarttime);
		}
		if(!$creatstarttime && $createndtime) {
			$where['create_at'] = array('ELT', $createndtime);
		}
		if($valistarttime && $valiendtime && ($valiendtime > $valistarttime)) {
			$where['validate_at'] = array('BETWEEN', array($valistarttime, date('Y-m-d H:i:s', strtotime($valiendtime) + 86400)));
		}
		if($valistarttime && !$valiendtime) {
			$where['validate_at'] = array('EGT', $valistarttime);
		}
		if(!$valistarttime && $valiendtime) {
			$where['validate_at'] = array('ELT', $valiendtime);
		}
		
		$count = $sms_validate_model->field('id')->where($where)->count();
		$page = $this->page($count, 20);
		$list = $sms_validate_model->where($where)->order("id DESC")->limit($page->firstRow . ',' . $page->listRows)->select();
		
		/*获取类型*/
		$types = $sms_validate_model->getTypes();
		
		$this->assign('types', $types);
		$this->assign('param', $param);
		$this->assign("page", $page->show('Admin'));
		$this->assign("list",$list);
    	$this->display();
	}
	
	/*短信发送日志列表*/
	function log_list() {
		$sms_log_model = D('Common/SmsLog');
		
		/*处理数据*/
		$where = array();
		$param = I('get.');
		$starttime = isset($param['starttime']) ?  $param['starttime'] : '';
		$endtime = isset($param['endtime']) ?  $param['endtime'] : '';
		$type = isset($param['type']) ?  intval($param['type']): 0;
		$mobile = isset($param['mobile']) ?  trim($param['mobile']): '';
		if($mobile) {
			$where['mobile'] = $mobile;
		}
		if($type) {
			$where['type'] = $type;
		}
		if($starttime && $endtime && ($endtime > $starttime)) {
			$where['create_at'] = array('BETWEEN', array($starttime, date('Y-m-d H:i:s', strtotime($endtime) + 86400)));
		}
		if($starttime && !$endtime) {
			$where['create_at'] = array('EGT', $starttime);
		}
		if(!$starttime && $endtime) {
			$where['create_at'] = array('ELT', $endtime);
		}
		$count = $sms_log_model->field('id')->where($where)->count();
		$page = $this->page($count, 20);
		$list = $sms_log_model->where($where)->order("id DESC")->limit($page->firstRow . ',' . $page->listRows)->select();
		
		/*获取类型*/
		$types = $sms_log_model->getTypes();

		$this->assign('types', $types);
		$this->assign('param', $param);
		$this->assign("page", $page->show('Admin'));
		$this->assign("list",$list);
    	$this->display();
	}
}

