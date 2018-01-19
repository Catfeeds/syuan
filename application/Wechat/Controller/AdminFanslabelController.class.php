<?php

namespace Wechat\Controller;

use Common\Controller\AdminbasewechatController;
use Wechat\Logic\WechatLogic;

class AdminFanslabelController extends AdminbasewechatController {
	
	protected $menu_types = array();
	
	function _initialize() {
		parent::_initialize();
		$this->label_model = D("Wechat/WxFansLabel");
	}
	
	/**
	 * 粉丝标签管理
	 */
	function index(){

		$list = $this->label_model->where(array('original_id' => $this->wechatmp['original_id']))->select();
		$this->assign('list', $list);
		$this->display();
	}
	
	/**
	 * 添加
	 */
	function add() {
		$this->display();
	}
	
	/**
	 * 提交添加
	 */
	function add_post() {
		if (IS_POST) {
			if ($this->label_model->create()) {
				$this->label_model->original_id = $this->wechatmp['original_id'];
				$wechat = D('Wechat/Wechat', 'Logic')->init($this->wechatmp);
				try {
					$result = $wechat->getApi()->user_tag->create($this->label_model->name);
				} catch (\Exception $e) {
					$this->error('创建标签失败, 错误信息: code:'.$e->getCode().', msg:'.$e->getMessage());
					return false;
				}
				$this->label_model->labelid = $result['tag']['id'];
				$id = $this->label_model->add();
				if ($id !== false) {
					$this->success("添加成功！");
				} else {
    				$this->error("添加失败！");
    			}
			} else {
    			$this->error($this->label_model->getError());
    		}
		}
	}
	
	/**
	 * 修改
	 */
	function edit() {
		$id = intval(I("get.id"));
		$label = $this->label_model->where(array('original_id' => $this->wechatmp['original_id'], 'id' => $id))->find();
		if(!$label) {
			$this->error("标签信息不存在!");
		}
		$this->assign('label', $label);
		$this->display();
	}
	
	/**
	 * 提交修改
	 */
	function edit_post() {
		if (IS_POST) {
			if ($this->label_model->create()) {
				$wechat = D('Wechat/Wechat', 'Logic')->init($this->wechatmp);
				try {
					$result = $wechat->getApi()->user_tag->update($this->label_model->labelid, $this->label_model->name);
				} catch (\Exception $e) {
					$this->error('修改标签失败, 错误信息: code:'.$e->getCode().', msg:'.$e->getMessage());
					return false;
				}
				if ($this->label_model->save()!==false) {
					$this->success("保存成功！");
				} else {
					$this->error("保存失败！");
				}
			} else {
				$this->error($this->label_model->getError());
			}
		}
	}
	
	/**
	 * 删除
	 */
	function delete() {
		$id = intval(I("get.id"));
		$label = $this->label_model->where(array('original_id' => $this->wechatmp['original_id'], 'id' => $id))->find();
		if(!$label) {
			$this->error("标签信息不存在!");
		}
		$wechat = D('Wechat/Wechat', 'Logic')->init($this->wechatmp);
		try {
			$result = $wechat->getApi()->user_tag->delete($label['labelid']);
		} catch (\Exception $e) {
			$this->error('删除标签失败, 错误信息: code:'.$e->getCode().', msg:'.$e->getMessage());
			return false;
		}
		if ($this->label_model->where(array(
				'original_id' => $this->wechatmp['original_id'],
				'id' => $id
				))->delete()!==false) {
			$this->success("删除成功！");
		} else {
			$this->error("删除失败！");
		}
	}
	
	//同步标签
	function sync() {
		
		$wechat = D('Wechat/Wechat', 'Logic')->init($this->wechatmp);
		try {
			$result = $wechat->getApi()->user_tag->lists();
		} catch (\Exception $e) {
			$this->error('获取标签失败, 错误信息: code:'.$e->getCode().', msg:'.$e->getMessage());
		}
		if(isset($result['tags']) && is_array($result['tags'])) {
			foreach($result['tags'] as $tag) {
				$id = $this->label_model->where(array('original_id' => $this->wechatmp['original_id'], 'labelid' => $tag['id']))->getField('id');
				if($id > 0) {
					$this->label_model->where('id='.$id)->save(array(
						'name' => $tag['name'],
						'count' => $tag['count'],
						'update_at' => date('Y-m-d H:i:s', TIMESTAMP)
					));
				} else {
					$this->label_model->add(array(
						'original_id' => $this->wechatmp['original_id'],
						'labelid' => $tag['id'],
						'name' => $tag['name'],
						'count' => $tag['count'],
						'update_at' => date('Y-m-d H:i:s', TIMESTAMP)
					), array(), true);
				}
				$this->label_model->where(array(
					'original_id' => $this->wechatmp['original_id'], 
					'update_at' => array('lt', date('Y-m-d H:i:s', TIMESTAMP))))
				->delete();
			}
		}
		$this->success("标签同步成功!");
	}
	
	//同步粉丝标签,目前用户数量不多，可以一次性同步完
	//TODO: 粉丝过万后需要优化
	function syncuserlabel() {
		$labelid = I('get.labelid', 0, 'intval');
		$where = array('original_id' => $this->wechatmp['original_id']);
		$where['labelid'] = array('gt', $labelid);
		$label = $this->label_model->where($where)->order('labelid asc')->find();
		if(!$label) {
			$this->success("用户标签已全部同步成功!", U('AdminFanslabel/index'));
			return false;
		}
		$wechat = D('Wechat/Wechat', 'Logic')->init($this->wechatmp);
		try {
			$result = $wechat->getApi()->user_tag->usersOfTag($label['labelid']);
		} catch (\Exception $e) {
			$this->error('获取标签失败, 错误信息: code:'.$e->getCode().', msg:'.$e->getMessage());
		}
		if(isset($result['count'])) {
			$labelrelation_model = D('Wechat/WxFansLabelRelation');
			$labelrelation_model->where(array('original_id' => $this->wechatmp['original_id'], 'labelid' => $label['labelid']))->delete();
			if(isset($result['data']['openid'])) {
				$list = array();
				foreach($result['data']['openid'] as $openid) {
					$list[] = array(
						'original_id' => $this->wechatmp['original_id'],
						'labelid' => $label['labelid'],
						'openid' => $openid
					);
				}
				$labelrelation_model->addAll($list, array(), true);
			}
		}
		$this->success('已更新标签: '.$label['name'].' 的粉丝信息, 跳转继续更新下一标签, 请勿关闭页面!!!!', U('AdminFanslabel/syncuserlabel', array('labelid' => $label['labelid'])));
	}
}