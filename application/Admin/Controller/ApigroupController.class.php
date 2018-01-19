<?php
namespace Admin\Controller;

use Common\Controller\AdminbaseController;

class ApigroupController extends AdminbaseController{

    function index() {

        $list = M('AppApiGroup')->order('listorder asc')->select();

        $this->assign('list', $list);
        $this->display();
    }

    function listorder() {
        if(IS_POST) {
            if(parent::_listorders(M('AppApiGroup'))) {
                $this->success("排序更新成功!");
            } else {
                $this->error("排序更新失败!");
            }
        }
    }

    function add() {
        $this->display();
    }

    function add_post() {
        if(IS_POST) {
            $data = array(
                'name' => I('name', '', 'trim'),
                'introduce' => I('introduce', '', 'trim'),
                'listorder' => I('listorder', 0, 'intval'),
            );
            if(empty($data['name'])) {
                $this->error('请填写分类名称');
            } else if(empty($data['introduce'])) {
                $this->error('请填写分类介绍');
            } else {
                if(M('AppApiGroup')->add($data)) {
                    $this->success('保存成功');
                } else {
                    $this->error('添加错误');
                }
            }
        }
    }

    function edit() {
        $gid = I('gid', 0, 'intval');
        $group = M('AppApiGroup')->find($gid);
        if(!$group) {
            $this->error('分类不存在!');
        }
        $this->assign('group', $group);
        $this->display();
    }

    function edit_post() {
        if(IS_POST) {
            $gid = I('gid', 0, 'intval');
            $group = M('AppApiGroup')->find($gid);
            if(!$group) {
                $this->error('分类不存在!');
            }
            $data = array(
                'gid' => $gid,
                'name' => I('name', '', 'trim'),
                'introduce' => I('introduce', '', 'trim'),
                'listorder' => I('listorder', 0, 'intval'),
            );
            if(empty($data['name'])) {
                $this->error('请填写分类名称');
            } else if(empty($data['introduce'])) {
                $this->error('请填写分类介绍');
            } else {
                if(M('AppApiGroup')->save($data)) {
                    $this->success('保存成功');
                } else {
                    $this->error('添加错误');
                }
            }
        }
    }

    function del() {
        $gid = I('gid', 0, 'intval');
        $group = M('AppApiGroup')->find($gid);
        if(!$group) {
            $this->error('分类不存在!');
        }
        if(D('Common/Api')->where(array('gid' => $gid))->count() > 0) {
            $this->error('该分类下有API接口, 无法删除!');
        } else {
            if(M('AppApiGroup')->delete($gid)) {
                $this->success('删除成功!');
            } else {
                $this->error('删除失败!');
            }
        }
    }
}
