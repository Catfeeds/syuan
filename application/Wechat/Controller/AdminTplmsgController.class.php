<?php
namespace Wechat\Controller;

use Common\Controller\AdminbasewechatController;
use Wechat\Logic\WechatLogic;

class AdminTplmsgController extends AdminbasewechatController {

	function _initialize() {
		parent::_initialize();
		$this->wxtplmsg_model = D('Wechat/WxTplmsg');
	}
	
	/*模板消息列表*/
	function index(){

		$where = array('original_id' => $this->wechatmp['original_id']);
		$count = $this->wxtplmsg_model->field('id')->count();
		$page = $this->page($count, 20);
		$list = $this->wxtplmsg_model->order("id DESC")->limit($page->firstRow . ',' . $page->listRows)->select();
		
		$this->assign('list', $list);
		$this->assign("Page", $page->show('Admin'));
		$this->display();
	}

	/*同步模板消息*/
	function sync() {
		$wechat = D('Wechat/Wechat', 'Logic')->init($this->wechatmp);
		$response = $wechat->getApi()->notice->getPrivateTemplates();
		if($response && isset($response['template_list'])) {
			$data = $this->wxtplmsg_model->dealWechatTplmsg($response['template_list']);
			$template_id = array();
			
			/*接口返回数据*/
			$list = array();
			$regex = "/.*?{{(.*?).DATA}}.*?/";
			foreach($data as $key=>$val) {
				$template_id[] = $val['template_id'];
				if($val['content']) {
					$color = array();
					if($val['content']) {
						if(preg_match_all($regex, $val['content'], $matches)){
							if($matches[1]) {
								foreach($matches[1] as $k=>$v) {
									$color[$v] = '#000000';
								}
							}
						}
					}
					$val['colors'] = serialize($color);
				}
				$val['original_id'] = $this->wechatmp['original_id'];
				$val['update_at'] = date('Y-m-d H:i:s');
				$list[$val['template_id']] = $val;
			}
			
			$result = true;
			/*查找数据库现存消息模板*/
			$olddata = $this->wxtplmsg_model->where(array('original_id' => $this->wechatmp['original_id']))->select();
			if($olddata) {
				foreach($olddata as $key=>$val) {
					/*查看旧数据，没有的话删除，有的话更新*/
					if(in_array($val['template_id'], $template_id)) {
						$list[$val['template_id']]['id'] = $val['id'];
						$result = $this->wxtplmsg_model->save($list[$val['template_id']]);
						unset($list[$val['template_id']]);
					} else {
						$this->wxtplmsg_model->where(array('id' => $val['id']))->delete();
					}
				}
			}
			if($list) {
				$msgdata = array_values($list);
				$result = $this->wxtplmsg_model->addAll($msgdata);
			}
			if($result !== false) {
				$this->success('更新成功!', U('AdminTplmsg/index'));
			} else {
				$this->error('更新失败!', U('AdminTplmsg/index'));
			}
		} else {
			$this->wxtplmsg_model->where(array('original_id' => $this->wechatmp['original_id']))->delete();
		}
	}
	
	/*设置颜色*/
	function setcolor() {
		$id = I('id', 0, 'intval');
		$tplmsginfo = $this->wxtplmsg_model->where(array('id' => $id, 'original_id' => $this->wechatmp['original_id']))->find();
		if(!$tplmsginfo) {
			$this->error('模板消息不存在！');
		}
		if($tplmsginfo['colors']) {
			$tplmsginfo['colors'] = unserialize($tplmsginfo['colors']);
		} else {
			$this->error('颜色不存在，请更新！');
		}
		$this->assign('tplmsginfo', $tplmsginfo);
		$this->display();
	}
	function setcolor_post() {
		if(IS_POST) {
			$id = I('id', 0, 'intval');
			$tplmsginfo = $this->wxtplmsg_model->where(array('id' => $id, 'original_id' => $this->wechatmp['original_id']))->find();
			if(!$tplmsginfo) {
				$this->error('模板消息不存在！');
			}
			$color = I('color');
			if($color) {
				if($this->wxtplmsg_model->create()) {
					foreach($color as $key=>$val) {
						$color[$key] = '#'.$val;
					}
					$this->wxtplmsg_model->colors = serialize($color);
					$result = $this->wxtplmsg_model->save();
					if($result !== false) {
						$this->success('设置成功！');
					} else {
						$this->error('设置失败！');
					}
				} else {
					$this->error($this->wxtplmsg_model->getError());
				}
			} else {
				$this->error('请设置颜色！');
			}
		}
	}
	
	/*删除模板消息*/
	function delete() {
		$id = I('id', 0, 'intval');
		$tplmsginfo = $this->wxtplmsg_model->where(array('id' => $id, 'original_id' => $this->wechatmp['original_id']))->find();
		if(!$tplmsginfo) {
			$this->error('模板消息不存在！');
		}
		if(!$tplmsginfo['template_id']) {
			$this->error('请更新模板消息！');
		}
		$wechat = D('Wechat/Wechat', 'Logic')->init($this->wechatmp);
		try {
			$result = $wechat->getApi()->notice->deletePrivateTemplate($tplmsginfo['template_id']);
			if($result['errcode'] == 0) {
				$this->wxtplmsg_model->where(array('id' => $id))->delete();
			}
		} catch (\Exception $e) {
			$this->error('删除失败, 错误信息: code:'.$e->getCode().', msg:'.$e->getMessage());
		}
		$this->success('删除成功!');
	}
}