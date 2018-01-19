<?php

namespace Wechat\Controller;

use Common\Controller\AdminbasewechatController;
use Wechat\Logic\WechatLogic;
use Wechat\Logic\QrcodeLogic;

class AdminFansController extends AdminbasewechatController {
	
	protected $menu_types = array();
	
	function _initialize() {
		parent::_initialize();
		$this->fans_model = D("Wechat/WxFans");
	}
	
	/**
	 * 粉丝管理
	 */
	function index(){
		
		$view = 
		C('TOKEN_ON', false);
		$where = array('original_id' => $this->wechatmp['original_id']);
		
		$labelid = I('get.labelid', 0,'intval');
		$subscribe = I('get.subscribe', -1,'intval');
		$startdate = I('get.startdate', '', 'trim');
		$enddate = I('get.enddate', '', 'trim');
		$keyword = I('get.keyword', '', 'trim');
		if($subscribe >= 0) {
			$where['subscribe'] = $subscribe;
		}
		if($labelid > 0) {
			$this->fans_model = D('Wechat/WxFansLabelView');
			$where['labelid'] = array('eq', $labelid);
		}
		if($startdate && $enddate) {
			$where['subscribe_time'] = array('BETWEEN', array($startdate, date('Y-m-d H:i:s', strtotime($enddate) + 86400)));
		} else {
			if($startdate) {
				$where['subscribe_time'] = array('egt', $startdate);
			}
			if($enddate) {
				$where['subscribe_time'] = array('elt', date('Y-m-d H:i:s', strtotime($enddate) + 86400));
			}
		}
		
		if($keyword) {
			$map = array();
			$map['nickname'] = array('like', '%'.$keyword.'%');
			$map['remark'] = array('like', '%'.$keyword.'%');
			$map['_logic'] = 'or';
			$where['_complex'] = $map;
		}
		$label_model = D("Wechat/WxFansLabel");
		$labels = $label_model->where(array('original_id' => $this->wechatmp['original_id']))->getField('labelid,name');
		
		$count = $this->fans_model->where($where)->count();
		$page = $this->page($count, 20);
		$list = $this->fans_model->where($where)->order("wechatid DESC")->limit($page->firstRow . ',' . $page->listRows)->select();

		foreach($list as $key => $val) {
			if($val['labelids']) {
				$val['labelids'] = explode(",", $val['labelids']);
				$list[$key] = $val;
			}
		}
		$this->assign("formget", array('labelid' => $labelid, 'keyword' => $keyword, 'subscribe' => $subscribe, 'startdate' => $startdate, 'enddate' => $enddate));
		$this->assign('list', $list);
		$this->assign('labels', $labels);
		$this->assign("Page", $page->show('Admin'));
		$this->display();
	}
	
	/*生成永久推广二维码, 同时删除临时二维码*/
	function forever_qr() {
		$wechatid = intval(I("get.wechatid"));
		$fan = $this->fans_model->where(array('wechatid' => $wechatid, 'original_id' => $this->wechatmp['original_id']))->find();
		if (!$fan) {
			$this->error("粉丝信息不存在！");
		}
		$wechat = D('Wechat/Wechat', 'Logic')->init($this->wechatmp);
        $qrcodeLogic = D('Wechat/Qrcode', 'Logic')->setContext($wechat);
        if($qrcodeLogic->getRecommendQrcode($fan['openid'], true)) {
			D('Wechat/WxQrcode')->where(array('original_id' => $this->wechatmp['original_id'], 'category' => 'recommend', 'openid' => $fan['openid'], 'type' => 1))->delete();//删除临时二维码
			$this->success("永久推荐码已生成!");
		} else {
			$this->error("Sorry, 永久推荐码生成失败!");
		}	
	}

	/**
	 * 修改
	 */
	function edit() {
		$wechatid = intval(I("get.wechatid"));
		$fan = $this->fans_model->where(array('wechatid' => $wechatid, 'original_id' => $this->wechatmp['original_id']))->find();
		if (!$fan) {
			$this->error("粉丝信息不存在！");
		}
		if(TIMESTAMP - strtotime($fan['update_at']) > 3600) {
			$wechat = D('Wechat/Wechat', 'Logic')->init($this->wechatmp);
			try {
				$fan = $wechat->getApi()->user->get($fan['openid']);
			} catch (\Exception $e) {
				$this->error('获取用户信息失败, 错误信息: code:'.$e->getCode().', msg:'.$e->getMessage());
			}
			$fan = $this->fans_model->dealWechatFans($fan);
			$this->fans_model->where(array('original_id' => $this->wechatmp['original_id'], 'openid' => $fan['openid']))->save($fan);
			$fan = $this->fans_model->where(array('original_id' => $this->wechatmp['original_id'], 'wechatid' => $wechatid))->find();
		}
		if($fan['labelids']) {
			$fan['labelids'] = explode(",", $fan['labelids']);
		} else {
			$fan['labelids'] = array();
		}
		
		$label_model = D("Wechat/WxFansLabel");
		$labels = $label_model->where(array('original_id' => $this->wechatmp['original_id']))->getField('labelid,name');
		$this->assign('labels', $labels);
		$this->assign('fan', $fan);
		$this->display();
	}
	
	/**
	 * 提交修改
	 */
	function edit_post() {
		if (IS_POST) {
			$wechatid = intval(I("post.wechatid"));
			$where = array(
				'wechatid' => $wechatid,
				'original_id' => $this->wechatmp['original_id']
			);
			$fan = $this->fans_model->where($where)->find();
			if (!$fan) {
				$this->error("粉丝信息不存在！");
			}
			$data = array();
			$label = I('post.label', array());
			if(is_array($label) && count($label) > 3) {
				$this->error("一个用户最多可以设置三个标签！");
			}
			$remark = I('post.remark', '', 'trim');
			if($fan['subscribe']) {
				$wechat = D('Wechat/Wechat', 'Logic')->init($this->wechatmp);
				if($remark != $fan['remark']) {
					try {
						$wechat->getApi()->user->remark($fan['openid'], $remark);
					} catch (\Exception $e) {
						$this->error('修改用户备注失败, 错误信息: code:'.$e->getCode().', msg:'.$e->getMessage());
					}
				}
				if($fan['labelids']) {
					$fan['labids'] = explode(",", $fan['labids']);
					foreach($fan['labelids'] as $labelid) {
						try {
							$wechat->getApi()->user_tag->batchUntagUsers(array($fan['openid']), $labelid);
						} catch (\Exception $e) {
							$this->error('修改用户标签失败, 错误信息: code:'.$e->getCode().', msg:'.$e->getMessage());
						}
					}
				}
				if($label) {
					foreach($label as $labelid) {
						try {
							$wechat->getApi()->user_tag->batchTagUsers(array($fan['openid']), $labelid);
						} catch (\Exception $e) {
							$this->error('修改用户标签失败, 错误信息: code:'.$e->getCode().', msg:'.$e->getMessage());
						}
					}
				}
			}
			$data = array(
				'labelids' => implode(',', $label),
				'remark' => $remark,
				'update_at' => date('Y-m-d H:i:s', TIMESTAMP)
			);
			$this->fans_model->where($where)->save($data);
			$this->success("保存成功!");
		}
	}
	
	/**
	 * 刷新粉丝信息
	 */
	function refresh() {
		$openid = I("get.id", '', 'trim');
		if(!$openid) {
			$this->error("参数不存在!");
		}
		$wechat = D('Wechat/Wechat', 'Logic')->init($this->wechatmp);
		try {
			$fan = $wechat->getApi()->user->get($openid);
		} catch (\Exception $e) {
			$this->error('获取用户信息失败, 错误信息: code:'.$e->getCode().', msg:'.$e->getMessage());
		}
		$fan = $this->fans_model->dealWechatFans($fan);
		$this->fans_model->where(array('original_id' => $this->wechatmp['original_id'], 'openid' => $fan['openid']))->save($fan);
		$this->success("刷新成功!");
	}
	
	//同步粉丝标签
	function sync() {
		$page = I('get.page', 1);
		$nextopenid = I('get.nextopenid', null);
		$wechat = D('Wechat/Wechat', 'Logic')->init($this->wechatmp);
		try {
			$result = $wechat->getApi()->user->lists($nextopenid);
		} catch (\Exception $e) {
			$this->error('获取用户列表失败, 错误信息: code:'.$e->getCode().', msg:'.$e->getMessage());
		}
		$redirect = false;
		if($result && isset($result['total'])) {
			if($result['count'] > 1000) {//大于1000个用户
				for($i = 0; $i < 1000; $i += 100) {
					$openids = array_slice($result['data']['openid'], 0, 1000);
				}
				$redirect = true;
			} else {
				$openids = $result['data']['openid'];
			}
			$count = count($openids);
			for($i = 0; $i < $count; $i += 100) {
				$ids = array_slice($openids, $i, 100);
				try {
					$users = $wechat->getApi()->user->batchGet($ids);
				} catch (\Exception $e) {
					$this->error('获取用户列表失败, 错误信息: code:'.$e->getCode().', msg:'.$e->getMessage());
				}
				if($users && isset($users['user_info_list'])) {
					$where = array('original_id' => $this->wechatmp['original_id']);
					$where['openid'] = array('in', $ids);
					$includeids = $this->fans_model->where($where)->getField('wechatid,openid');
					$addlist = array();
					foreach($users['user_info_list'] as $fan) {
						$fan = $this->fans_model->dealWechatFans($fan);
						if($includeids && in_array($fan['openid'], $includeids)) {
							$this->fans_model->where(array('original_id' => $this->wechatmp['original_id'], 'openid' => $fan['openid']))->save($fan);
						} else {
							$fan['original_id'] = $this->wechatmp['original_id'];
							$fan['create_at'] = date('Y-m-d H:i:s', TIMESTAMP);
							$fan['lastaction_time'] = $fan['subscribe_time'];
							$addlist[] = $fan;
						}
					}
					if($addlist) {
						$this->fans_model->addAll($addlist);
					}
				}
				
			}
			if($redirect) {
				$nextopenid = array_pop($openids);
				$this->success('已更新 '.$page.' 条粉丝信息, 跳转继续更新粉丝信息, 请勿关闭页面!!!!', U('AdminFans/sync', array('page' => $page + 1, 'nextopenid' => $nextopenid)));
			} else {
				$this->success('粉丝信息更新成功!', U('AdminFans/index'));
			}
			
		} else {
			$this->error('获取用户列表失败!');
		}
		return flase;
	}
}