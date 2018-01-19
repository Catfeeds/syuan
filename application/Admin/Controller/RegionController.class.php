<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;

class RegionController extends AdminbaseController{
	
	protected $region_model;
	function _initialize() {
		parent::_initialize();
		$this->region_model = D("Common/Region");
	}
	
	function index(){
		$pid = I('pid', 0, 'intval');
		if($pid > 0) {
			$parent = $this->region_model->find($pid);
		} else {
			$parent = array('pid' => 0, 'name' => '一级');
		}
		$list = $this->region_model->where(array('pid' => $pid))->order(array("listorder"=>"asc"))->select();

		$this->assign('pid', $pid);
		$this->assign("list", $list);
		$this->assign("parent", $parent);
		$this->display();
	}
	
	function add(){
		$pid = I("pid", 0, 'intval');
		if($pid > 0) {
			$parent = $this->region_model->find($pid);
		} else {
			$parent = array('region_id' => 0, 'name' => '一级地区');
		}
		if(!$parent) {
			$this->error('父级信息不存在');
		}
		$this->assign('parent', $parent);
		$this->display();
	}
	
	function add_post(){
		if(IS_POST){
			if($this->region_model->create()) {
				if ($this->region_model->add()!==false) {
					$this->success("添加成功！", U("link/index"));
				} else {
					$this->error("添加失败！");
				}
			} else {
				$this->error($this->region_model->getError());
			}
		
		}
	}
	
	function edit(){
		$region_id = I("region_id", 0, 'intval');
		$region = $this->region_model->find($region_id);
		if(!$region) {
			$this->error('信息不存在');
		}
		$pid = $region['pid'];
		if($pid > 0) {
			$parent = $this->region_model->find($pid);
		} else {
			$parent = array('region_id' => 0, 'name' => '一级地区');
		}
		if(!$parent) {
			$this->error('父级信息不存在');
		}
		$this->assign('parent', $parent);
		$this->assign('region', $region);
		$this->display();
	}
	
	function edit_post(){
		if (IS_POST) {
			$region_id = I("region_id", 0, 'intval');
			$region = $this->region_model->find($region_id);
			if(!$region) {
				$this->error('信息不存在');
			}
			if($this->region_model->create()) {
				if ($this->region_model->save()!==false) {
					$this->success("保存成功！");
				} else {
					$this->error("保存失败！");
				}
			} else {
				$this->error($this->region_model->getError());
			}
		}
	}
	
	//排序
	public function listorders() {
		if(parent::_listorders($this->region_model)) {
			$this->success("排序更新成功！");
		} else {
			$this->error("排序更新失败！");
		}
	}
	
	//删除
	function delete(){
		$region_id = I("region_id", 0, 'intval');
		$region = $this->region_model->find($region_id);
		if(!$region) {
			$this->error('信息不存在');
		} else if($this->region_model->where(array('pid' => $region['region_id']))->count() > 0) {
			$this->error('该条记录有子记录, 不可删除!');
		} else {
			if ($this->region_model->delete($region_id)) {
				$this->success("删除成功！");
			} else {
				$this->error("删除失败！");
			}
		}
	}
}