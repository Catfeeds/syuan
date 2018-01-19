<?php
namespace Wechat\Controller;

use Common\Controller\AdminbasewechatController;
use Wechat\Logic\WechatLogic;

class AdminSpecialmenuController extends AdminbasewechatController {
	
	protected $menu_types = array();
	
	function _initialize() {
		parent::_initialize();
		$this->specialmenu_class_model = D('Wechat/WxSpecialmenuClass');
		$this->client_platform_type = $this->specialmenu_class_model->getClientPlatformTypes();
		$options = get_site_options();
		$this->wxmenu_model = D("Wechat/WxMenu");
		$this->wxmenu_model->setHost($options['site_host']);
		$this->menu_types = $this->wxmenu_model->getTypes();
	}
	
	/**
	 * 个性化规则列表
	 */
	function class_index() {
		$specialmenu_class_label_view_Model = D("Wechat/WxSpecialmenuClassLabelView");
		
		$list = $specialmenu_class_label_view_Model->where(array('original_id' => $this->wechatmp['original_id']))->order('create_at desc')->select();
		
		$this->assign('client_platform_type', $this->client_platform_type);
		$this->assign('list', $list);
		$this->display();
	}
	
	/**
	 * 个性化规则添加
	 */
	function class_add() {
		$wechat_fans_label_db = M('WechatFansLabel');
		
		$labels = $wechat_fans_label_db->where(array('original_id' => $this->wechatmp['original_id']))->select();
		
		$this->assign('labels', $labels);
		$this->assign('client_platform_type', $this->client_platform_type);
		$this->display();
	}

	function class_add_post() {
		if(IS_POST) {
			if($this->specialmenu_class_model->create()) {
				$this->specialmenu_class_model->original_id = $this->wechatmp['original_id'];
				$this->specialmenu_class_model->create_at = date('Y-m-d H:i:s');
				$result = $this->specialmenu_class_model->add();
				if ($result !== false) {
					$this->success('添加成功！');
				} else {
    				$this->error('添加失败！');
    			}
			} else {
				$this->error($this->specialmenu_class_model->getError());
			}
		}
	}
	
	/**
	 * 个性化规则编辑
	 */
	function class_edit() {
		$wechat_fans_label_db = M('WechatFansLabel');
		
		$catid = I('catid', 0, 'intval');
		$classinfo = $this->specialmenu_class_model->where(array('original_id' => $this->wechatmp['original_id'], 'catid' => $catid))->find();
		if(!$classinfo) {
			$this->error('规则不存在！');
		}
		$labels = $wechat_fans_label_db->where(array('original_id' => $this->wechatmp['original_id']))->select();
		
		$this->assign('classinfo', $classinfo);
		$this->assign('labels', $labels);
		$this->assign('client_platform_type', $this->client_platform_type);
		
		$this->display();
	}

	function class_edit_post() {
		if(IS_POST) {
			if($this->specialmenu_class_model->create()) {
				$result = $this->specialmenu_class_model->save();
				if ($result !== false) {
					$this->success('修改成功！');
				} else {
    				$this->error('添加失败！');
    			}
			} else {
				$this->error($this->specialmenu_class_model->getError());
			}
		}
	}
	
	/**
	 * 个性化规则删除
	 */
	function class_delete() {
		$catid = I('catid', 0, 'intval');
		$classinfo = $this->specialmenu_class_model->where(array('original_id' => $this->wechatmp['original_id'], 'catid' => $catid))->find();
		if(!$classinfo) {
			$this->error('个性化规则不存在！');
		}
		$flag = true;
		if($classinfo['menuid']) {
			$flag = false;
			/*调用接口删除菜单*/
			$wechat = D('Wechat/Wechat', 'Logic')->init($this->wechatmp);
			try {
				$result = $wechat->getApi()->menu->destroy($classinfo['menuid']);
				if($result['errcode'] == 0) {
					$flag = true;
				}
			} catch (\Exception $e) {
				$this->error('删除菜单失败, 错误信息: code:'.$e->getCode().', msg:'.$e->getMessage());
			}
		}
		if($flag) {
			$this->specialmenu_class_model->where("catid = {$catid}")->delete();
			$this->wxmenu_model->where(array('original_id' => $this->wechatmp['original_id'],'catid' => $catid))->delete();
			$this->success('删除菜单成功!');
		} else {
			$this->error('删除菜单失败, 错误信息: code:'.$result['errcode'].', msg:'.$result['errmsg']);
		}
	}
	
	/**
	 * 个性化菜单列表
	 */
	function menu_index() {
		$catid = I('catid', 0, 'intval');
		$classinfo = $this->specialmenu_class_model->where(array('original_id' => $this->wechatmp['original_id'], 'catid' => $catid))->find();
		if(!$classinfo) {
			$this->error('规则不存在！');
		}
		$result = $this->wxmenu_model->where(array('original_id' => $this->wechatmp['original_id'], 'catid' => $catid))
			->order(array("listorder"=>"asc"))->select();
		
		$tree = new \Tree();
		$tree->icon = array('&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ ');
		$tree->nbsp = '&nbsp;&nbsp;&nbsp;';
		foreach ($result as $r) {
			$r['str_manage'] = '<a href="' . U("AdminSpecialmenu/menu_edit", array("id" => $r['id'])) . '">'.L('EDIT').'</a> | <a class="js-ajax-delete" href="' . U("AdminSpecialmenu/menu_delete", array("id" => $r['id'])) . '">'.L('DELETE').'</a> ';
			$r['typename'] = '一级菜单';
			if($r['type']) {
				$r['typename'] = $this->menu_types[$r['type']];
			}
			switch($r['type']) {
				case 'view':
					$r['content'] = '<a href="'.$this->wxmenu_model->dealUrl($r['url']).'" target="_blank">'.$r['url'].'</a>';
					break;
				case 'click':
					$r['content'] = $r['click_key'];
			}
			
			/*判断一级菜单是否有子菜单*/
			if($r['parentid'] == 0) {
				$childcount = $this->wxmenu_model->where(array(
					'original_id' => $this->wechatmp['original_id'], 
					'parentid' => $r['id']
					)
				)->count();
				if($childcount > 0) {
					$r['typename'] = '一级菜单';
					$r['content'] = '';
				}
			}
			$r['status'] = $r['status'] ? '<i class="fa fa-check green" aria-hidden="true"></i>' : '<i class="fa fa-times red" aria-hidden="true"></i>';
			$array[$r['id']] = $r;
		}
		
		$tree->init($array);
		$str = "<tr>
					<td><input name='listorders[\$id]' type='text' size='3' value='\$listorder' class='input input-order'></td>
					<td>\$spacer\$name</td>
					<td>\$typename</td>
	    			<td>\$content</td>
					<td>\$status</td>
					<td>\$str_manage</td>
				</tr>";
		$this->assign('catid', $catid);
		$this->assign('classinfo', $classinfo);
		$this->assign("taxonomys", $tree->get_tree(0, $str));
		$this->display();
	}
	
	/**
	 * 添加个性化菜单
	 */
	function menu_add() {
		$catid = I('catid', 0, 'intval');
		if(!$catid) {
			$this->error('规则不存在！');
		}
		$parent = $this->wxmenu_model->where(array('original_id' => $this->wechatmp['original_id'], 'parentid' => 0, 'catid' => $catid))
			->order(array("listorder"=>"asc"))->getField('id,name');
		$this->assign('catid', $catid);
		$this->assign('parent', $parent);
		$this->assign('types', $this->menu_types);
		$this->display();
	}
	
	/**
	 * 提交添加
	 */
	function menu_add_post() {
		if (IS_POST) {
			if ($this->wxmenu_model->create()) {
				$this->wxmenu_model->original_id = $this->wechatmp['original_id'];
				$content = I('post.content', '', 'trim');
				switch($this->wxmenu_model->type) {
					case 'view':
						$this->wxmenu_model->url = $content;
						break;
					case 'click':
						$this->wxmenu_model->click_key = $content;
						break;
				}
				$id = $this->wxmenu_model->add();
				if ($id !== false) {
					$this->success("添加成功！");
				} else {
    				$this->error("添加失败！");
    			}
			} else {
    			$this->error($this->wxmenu_model->getError());
    		}
		}
	}
	
	/**
	 * 修改个性化
	 */
	function menu_edit() {
		$id = intval(I("get.id"));
		$menu = $this->wxmenu_model->where(array('original_id' => $this->wechatmp['original_id'], 'id' => $id))->find();
		if (!$menu) {
			$this->error("菜单不存在！");
		}
		switch($menu['type']) {
			case 'view':
				$menu['content'] = $menu['url'];
				break;
			case 'click':
				$menu['content'] = $menu['click_key'];
				break;
		}
		$parent = $this->wxmenu_model->where(array('original_id' => $this->wechatmp['original_id'], 'parentid' => 0))
			->order(array("listorder"=>"asc"))->getField('id,name');
		$this->assign('parent', $parent);
		$this->assign('types', $this->menu_types);
		$this->assign('menu', $menu);
		$this->display();
	}
	
	/**
	 * 提交修改
	 */
	function menu_edit_post() {
		if (IS_POST) {
			$parentid = intval(I("post.parentid"));
			$id = intval(I("post.id"));
			$where = array(
						'original_id' => $this->wechatmp['original_id'],
						'parentid' => $id
						);
			if($parentid > 0 && $this->wxmenu_model->where($where)->count() > 0) {
				$this->error("该菜单下含有子菜单不能设置为二级菜单!");
			} else {
				if ($this->wxmenu_model->create()) {
					$content = I('post.content', '', 'trim');
					switch($this->wxmenu_model->type) {
						case 'view':
							$this->wxmenu_model->url = $content;
							break;
						case 'click':
							$this->wxmenu_model->click_key = $content;
							break;
					}
					if ($this->wxmenu_model->save()!==false) {
						$this->success("保存成功！");
					} else {
						$this->error("保存失败！");
					}
				} else {
					$this->error($this->wxmenu_model->getError());
				}

			}
		}
	}
	
	/* 排序 */
	public function menu_listorders() {
		$status = parent::_listorders($this->wxmenu_model);
		if ($status) {
			$this->success("排序更新成功！");
		} else {
			$this->error("排序更新失败！");
		}
	}
	
	/**
	 * 个性化菜单删除
	 */
	function menu_delete() {
		$id = intval(I("get.id"));
		$where = array(
					'original_id' => $this->wechatmp['original_id'],
					'parentid' => $id
					);
		if($this->wxmenu_model->where($where)->count() > 0) {
			$this->error('该菜单下含有子菜单不能删除！');
		} else {
			if ($this->wxmenu_model->where(array(
					'original_id' => $this->wechatmp['original_id'],
					'id' => $id
					))->delete()!==false) {
				$this->success('删除成功！');
			} else {
				$this->error('删除失败！');
			}
		}
	}
	
	/* 生成个性化菜单 */
	function makemenu() {
		$catid = I('catid', 0, 'intval');
		$specialmenu_class_model = D('Wechat/WxSpecialmenuClass');
		$classinfo = $specialmenu_class_model->where("catid = {$catid}")->find();
		if(!$classinfo) {
			$this->error('个性化规则不存在！');
		}
		$buttons = $this->wxmenu_model->getWxMenu($this->wechatmp['original_id'], $catid);		
		if(!$buttons) {
			$this->error("请先添加菜单");
		} else {
			$wechat = D('Wechat/Wechat', 'Logic')->init($this->wechatmp);
			try {
				/*个性化配置*/
				$matchrule = array(
					"group_id"             => $classinfo['group_id'],
					"sex"                  => $classinfo['sex'],
					"country"              => "中国",
					"client_platform_type" => $classinfo['client_platform_type']
				);
				$result = $wechat->getApi()->menu->add($buttons, $matchrule);
			} catch (\Exception $e) {
				$this->error('生成菜单失败, 错误信息: code:'.$e->getCode().', msg:'.$e->getMessage());
			}
			if(isset($result['menuid']) && $result['menuid']) {
				$specialmenu_class_model->save(array('catid' => $classinfo['catid'], 'menuid' => $result['menuid'], 'state' => 1));
				$this->success('生成菜单成功!');
			} else {
				$this->error('生成菜单失败, 错误信息: code:'.$result['errcode'].', msg:'.$result['errmsg']);
			}
		}
	}
	
	/* 删除个性化菜单 */
	function delmenu() {
		$catid = I('catid', 0, 'intval');
		$specialmenu_class_model = D('Wechat/WxSpecialmenuClass');
		$classinfo = $specialmenu_class_model->where("catid = {$catid}")->find();
		if(!$classinfo) {
			$this->error('个性化规则不存在！', U('AdminSpecialmenu/menu_index', array('catid' => $catid)));
		}
		if(!$classinfo['menuid']) {
			$this->error('请生成菜单！', U('AdminSpecialmenu/menu_index', array('catid' => $catid)));
		}
		$wechat = D('Wechat/Wechat', 'Logic')->init($this->wechatmp);
		try {
			$result = $wechat->getApi()->menu->destroy($classinfo['menuid']);
		} catch (\Exception $e) {
			$this->error('删除菜单失败, 错误信息: code:'.$e->getCode().', msg:'.$e->getMessage(), U('AdminSpecialmenu/menu_index', array('catid' => $catid)));
		}
		if($result['errcode'] == 0) {
			$specialmenu_class_model->save(array('catid' => $classinfo['catid'], 'menuid' => '', 'state' => 0));
			$this->success('删除菜单成功!', U('AdminSpecialmenu/menu_index', array('catid' => $catid)));
		} else {
			$this->error('删除菜单失败, 错误信息: code:'.$result['errcode'].', msg:'.$result['errmsg'], U('AdminSpecialmenu/menu_index', array('catid' => $catid)));
		}
	}
}