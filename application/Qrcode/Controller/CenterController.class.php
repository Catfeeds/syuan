<?php

/**
 * 会员中心
 */
namespace Qrcode\Controller;
use Common\Controller\MemberbaseController;
class CenterController extends MemberbaseController {

	function _initialize(){
		parent::_initialize();
        if($this->user['user_type'] != 3) {
            $this->error('只有企业用户能够使用该功能！');
        }
        $this->status_arr = array(
            '0' => '开始印刷',
            '1' => '待审核',
            '2' => '印刷中',
            '3' => '已完成',
            '4' => '已取消'
        );
	}

    //会员中心
	public function index() {
    	$this->display(':center');
    }

    public function category() {
    	$list = M('Product_category')->where(array('uid'=>$this->user['id']))->order('id desc')->select();

    	$this->assign('list', $list);
    	$this->assign('do','cat');
    	$this->display();
    }

    public function recover_view(){
        $id = I('id', 0, 'int');

        $this->assign('orderid',$id);

	    $this->display();
    }
    public function recover_deal(){

        if(IS_POST) {

            $mobile = I('post.mobile', '', 'trim');

            $mobile_verify = I('post.mobile_verify', '', 'trim');

            $orderid = I('post.orderid','','trim');

//            if(empty($mobile_verify)
//
//                || !preg_match('/^\d{4}$/', $mobile_verify)
//
//                || (!session('regok_'.$mobile.$mobile_verify) && !sp_check_mobile_verify_code(3, $mobile, $mobile_verify))) {
//
//                $this->error('短信验证码错误');
//
//            } else {

                $upd=M('Order_qrcode')->where(array('orderid'=>$orderid))->save(array('hits'=>0));

                $del= M('order_view')->where(array('orderid'=>$orderid))->delete();

                if($del && $upd){

                    $this->success('删除成功！',UU('center/order'));

                } else if($del){
                    $this->success('删除成功！',UU('center/order'));
                } else if($upd){
                    $this->success('删除成功！',UU('center/order'));
                }else{

                    $this->error('删除失败');

                }

//           }

        }else{
            $this->error('非法参数！');
        }


    }
    public function add_cat() {
    	if(empty($_POST)) {
	    	$this->assign('do','add');
	    	$this->display();
	    } else {
	    	$name   = I('name', '', 'trim');
            $field1 = I('field1', '', 'trim');
            $field2 = I('field2', '', 'trim');
            $mr1 = I('mr1', '', 'trim');
            $mr2 = I('mr2', '', 'trim');
            $xxlb    = I('xxlb', '', 'trim');
	    	if(!$name) {
	    		$this->error('请输入名称！');
	    	}
            if(!$mr1) {
                $this->error('请输入默认字段名称1！');
            }
            if(!$mr2) {
                $this->error('请输入默认字段名称2！');
            }
            if(!$xxlb) {
                $this->error('请输入信息类别！');
            }
	    	$data = array(
				'name'       => $name,
				'uid'        => $this->user['id'],
                'field2'     => $field2,
                'field1'     => $field1,
                'mr1'        => $mr1,
                'mr2'        => $mr2,
                'xxlb'   => $xxlb,
 				'createtime' => time(),
	    	);
	    	if(M('Product_category')->add($data)) {
	    		$this->success('添加成功！',UU('center/category'));
	    	} else {
	    		$this->error('删除失败！');
	    	}
	    }
    }

    public function edit_cat() {
    	if(empty($_POST)) {
    		$id = I('id', 0, 'int');
    		$info = M('Product_category')->where(array('id'=>$id,'uid'=>$this->user['id']))->find();
    		$this->assign('info', $info);
	    	$this->assign('do','add');
	    	$this->display();
	    } else {
            $name   = I('name', '', 'trim');
            $id     = I('id', '', 'trim');
            $field1 = I('field1', '', 'trim');
            $field2 = I('field2', '', 'trim');
            $mr1 = I('mr1', '', 'trim');
            $mr2 = I('mr2', '', 'trim');
            $xxlb    = I('xxlb', '', 'trim');
            if(!$name) {
                $this->error('请输入名称！');
            }
            if(!$mr1) {
                $this->error('请输入默认字段名称1！');
            }
            if(!$mr2) {
                $this->error('请输入默认字段名称2！');
            }
            if(!$xxlb) {
                $this->error('请输入信息类别！');
            }
	    	$data = array(
                'name'   => $name,
                'field2' => $field2,
                'field1' => $field1,
                'mr1'    => $mr1,
                'mr2'    => $mr2,
                'xxlb'   => $xxlb,
	    	);
	    	if(M('Product_category')->where(array('uid'=>$this->user['id'],'id'=> $id))->save($data)!==false) {
	    		$this->success('修改成功！',UU('center/category'));
	    	} else {
	    		$this->error('修改失败！');
	    	}
	    }
    }

    public function del_cat() {
    	$id = I('id', 0, 'int');
    	if($id > 0) {
    		if(M('Product_category')->delete($id)!==false) {
    			$this->success('删除成功！');
    		} else {
    			$this->error('删除失败！');
    		}
    	}
    }

    public function product() {
    	$where = array('uid'=>$this->user['id'],'status'=>1);
        $cat   = I('category', 0, 'int');
        $name  = I('name', '', 'trim');
        if($cat > 0) {
            $condition['cat']  = $cat;
            $where['category'] = $cat;
        }
        if($name) {
            $condition['name'] = $name;
            $where['name']     = array('like', '%'.$name.'%');
        }
    	$totalsize = M('Product')->where($where)->count();
    	import('Page');
		if($pagesize == 0) {
			$pagesize = 20;
		}
		$PageParam = C("VAR_PAGE");
		$page = new \Page($totalsize,$pagesize);
		$page->setLinkWraper("li");
		$page->__set("PageParam", $PageParam);
		$page->SetPager('default', $pagetpl, array("listlong" => "9", "first" => "首页", "last" => "尾页", "prev" => "上一页", "next" => "下一页", "list" => "*", "disabledclass" => ""));
    	$list = M('Product')->where($where)->order('pid desc')->limit($page->firstRow . ',' . $page->listRows)->select();

        $category = M('Product_category')->where(array('uid'=>$this->user['id']))->getField('id,name,id');
        $this->assign('category', $category);
    	$this->assign('list', $list);
    	$this->assign('do', 'list');
        $this->assign('condition', $condition);
    	$this->assign('page', $page->show('default'));
    	$this->display();
    }

    public function edit_pro() {
        if(empty($_POST)) {
            $id = I('id', 0, 'int');
            if($id > 0) {
                $info = M('Product')->find($id);
                $category = M('Product_category')->where(array('uid'=>$this->user['id']))->order('id desc')->select();
                $this->assign('category', $category);
                $this->assign('info', $info);
                $this->assign('do','add');
                $this->display();
            }
        } else {
            $name        = I('name', '', 'trim');
            $category    = I('category', 0, 'int');
            $description = I('description');
            $id          = I('id', 0, 'int');
            $unitCode    = I('UnitCode');
            if(!$name) {
                $this->error('请输入产品名称！');
            }
            if(!$category) {
                $this->error('请选择产品分类！');
            }
            if(!$description) {
                $this->error('请输入产品介绍！');
            }
            if($unitCode) {
                if (strlen($unitCode) != 11 || !preg_match('/^[0-9]{1,11}$/', $unitCode)) {
                    $this->error('请输入11位的数字！');
                }
            }

            $data = array(
                'name'        => $name,
                'category'    => $category,
                'description' => $description,
                'unitCode'    => $unitCode,
            );
            if(M('Product')->where(array('pid'=>$id))->save($data)!==false) {
                $this->success('修改成功！',UU('center/product'));
            } else {
                $this->error('修改失败！');
            }
        }
    }

    public function add_pro() {
    	if(empty($_POST)) {
    		$category = M('Product_category')->where(array('uid'=>$this->user['id']))->order('id desc')->select();
    		$this->assign('category', $category);
    		$this->assign('do','add');
    		$this->display();
    	} else {
            $name        = I('name', '', 'trim');
            $category    = I('category', 0, 'int');
            $description = I('description');
            $unitCode    = I('UnitCode');
            if(!$name) {
                $this->error('请输入产品名称！');
            }
            if(!$category) {
                $this->error('请选择产品分类！');
            }
            if(!$description) {
                $this->error('请输入产品介绍！');
            }
            if($unitCode) {
                if (strlen($unitCode) != 11 || !preg_match('/^[0-9]{1,11}$/', $unitCode)) {
                    $this->error('请输入11位的数字！');
                }
            }
            $data = array(
                'name'        => $name,
                'category'    => $category,
                'description' => $description,
                'uid'         => $this->user['id'],
                'unitCode'    => $unitCode,
                'createtime'  => time(),
            );
            if(M('Product')->add($data)) {
                $this->success('添加成功！',UU('center/product'));
            } else {
                $this->error('添加失败！');
            }
     	}
    }
    
    public function del_pro() {
    	$id = I('id', 0, 'int');
        if(M('Product')->where(array('pid'=>$id))->save(array('status'=>2))!==false) {
            $this->success('删除成功！');
        } else {
            $this->error('删除失败！');
        }
    }

    public function add_order() {
        if(empty($_POST)) {
            $id = I('id', 0, 'int');
            if($id > 0) {
                $red  = M('Redbag_order')->where(array('status'=>2, 'uid'=>$this->user['id'], 'endtime' => array('gt', date('Y-m-d'))))->select();
                if($red) {
                    foreach($red as $key=>$val) {
                        $red[$key]['left'] = M('Redbag')->field('id')->where(array('orderid' => $val['id'],'winner' => 0))->count();
                    }
                }
                $info = M('Product')->find($id);
                $cat  = M('Product_category')->find($info['category']);
                $this->assign('info', $info);
                $this->assign('red', $red);
                $this->assign('date', date('Y-m-d'));
                $this->assign('cat', $cat);
                $this->display();
            }
        } else {
            $typedate = I('typedate');
            $num      = I('num', 0, 'int');
            $depart   = I('depart', '', 'trim');
            $tech     = I('tech');
            $id       = I('id', 0, 'int');
            $remark   = I('remark');
            $field2   = I('field2');
            $field1   = I('field1');
            $types    = I('types');
            $typesdate= I('typesdate');
            $prefix   = I('prefix');
            $block    = I('block', 1, 'int');
            $chance   = I('chance', '0', 'int');
            $space    = I('space', 0, 'int');
            $bindred  = I('bindred', 0, 'int');
            $showdraw  = I('showdraw', 0, 'int');
            $showleft  = I('showleft', 0, 'int');
            $showendtime  = I('showendtime', 0, 'int');
            if(!$depart) {
                $this->error('请填写部门！');
            }
            if(!$tech) {
                $this->error('请填写工艺流程！');
            }
            if(strlen($prefix) > 5 || !preg_match('/^[a-zA-Z]{1,5}$/', $prefix)) {
                $this->error('请输入5位以内英文前缀！');
            }
            if($block < 0) {
                $this->error('分组数量错误');
            }
            if($bindred > 0) {
                $red = M('Redbag_order')->find($bindred);
                if(!$red) {
                    $this->error('红包不存在！');
                }
                if($red['status']!=2) {
                    $this->error('应用红包必须审核通过！');
                }
                if($chance <=0 || $chance > 100) {
                    $this->error('中奖概率必须是1-100之间');
                }
                if($space < 0 || $space > 9999) {
                    $this->error('领取红包间隔时间范围是0-9999');
                }
            }
            $data = array(
                'typedate'   => $typedate,
                'num'        => $num,
                'depart'     => $depart,
                'tech'       => $tech,
                'pid'        => $id,
                'uid'        => $this->user['id'],
                'status'     => 0,
                'createtime' => time(),
                'batch'      => '',
                'remark'     => $remark,
                'field1'     => $field1,
                'field2'     => $field2,
                'code'       => sp_random_string(12),
                'types'      => $types,
                'typesdate'  => $typesdate,
                'block'      => $block,
                'bindred'    => $bindred,
                'space'      => $space,
                'chance'     => $chance,
                'showdraw'   => $showdraw,
                'showleft'   => $showleft,
                'showendtime'=> $showendtime,
            );
            if($id = M('Order')->add($data)) {
                M('Order')->where(array('id'=>$id))->save(array('batch'=>$prefix.$id));
                $this->success('添加成功！',UU('center/product'));
            } else {
                $this->error('添加失败！');
            }
        }
    }

    public function order() {
        $where = array('uid'=>$this->user['id']);
        $cat   = I('category', 0, 'int');
        $name  = I('name', '', 'trim');
        if($cat > 0) {
            $pids = M('Product')->where(array('category'=>$cat,'uid'=>$this->user['id']))->getField('pid');
            if($pids) {
                $condition['cat'] = $cat;
                $where['pid'] = array('in', $pids);
            }
        }
        if($name) {
            $pid = M('Product')->where(array('name'=>array('like', '%'.$name.'%','uid'=>$this->user['id'])))->getField('pid');
            if($pid) {
                $condition['name'] = $name;
                $where['pid'] = array('in', $pid);
            }
        }
        $totalsize = M('Order')->where($where)->count();
        import('Page');
        if($pagesize == 0) {
            $pagesize = 20;
        }
        $PageParam = C("VAR_PAGE");
        $page = new \Page($totalsize,$pagesize);
        $page->setLinkWraper("li");
        $page->__set("PageParam", $PageParam);
        $page->SetPager('default', $pagetpl, array("listlong" => "9", "first" => "首页", "last" => "尾页", "prev" => "上一页", "next" => "下一页", "list" => "*", "disabledclass" => ""));
        $list = M('Order')->where($where)->order('id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
        $pids = $pro = array();
        foreach($list as $k => $v) {
            $list[$k]['count'] = M('Order_qrcode')->where(array('orderid'=>$v['id'],'isdel'=>0))->count();
            $list[$k]['isdel'] = M('Order_qrcode')->where(array('orderid'=>$v['id'],'isdel'=>1))->count();
            $subQuery = M('order_view')->field('id')->where(array('orderid'=>$v['id']))->group('qrcode')->buildSql();
            $times = M('order_view')->table($subQuery.' a')->count();
            $list[$k]['times'] = $times;
            if($list[$k]['count']) {
                $list[$k]['percent'] = round($times/$list[$k]['count'], 4);
            } else {
                $list[$k]['percent'] = 0;
            }
            $pids[$v['pid']] = $v['pid'];
        }
        if($pids) {
            $pro = M('Product')->where(array('pid'=>array('in',$pids)))->getField('pid,name');
        }
        $category = M('Product_category')->where(array('uid'=>$this->user['id']))->select();
        $this->assign('category', $category);
        $this->assign('pro', $pro);
        $this->assign('list', $list);
        $this->assign('name', $name);
        $this->assign('condition', $condition);
        $this->assign('page', $page->show('default'));
        $this->display();
    }

    public function edit_order() {
        if(empty($_POST)) {
            $id = I('id', 0, 'int');
            if($id > 0) {
                $order = M('Order')->find($id);
                $info  = M('Product')->find($order['pid']);
                $cat   = M('Product_category')->find($info['category']);

                $timeout_redbagids = array();
                //查找过期红包ID，去除当前绑定的红包
                $timeout_redbagids = $red  = M('Redbag_order')->where(array('uid'=>$this->user['id'], 'endtime' => array('lt', date('Y-m-d'))))->getField('id', true);
                if($order['bindred'] && $timeout_redbagids) {
                    foreach($timeout_redbagids as $key=>$val) {
                        if($order['bindred'] == $val) {
                            unset($timeout_redbagids[$key]);break;
                        }
                    }
                    if($timeout_redbagids) {
                        $timeout_redbagids = array_values($timeout_redbagids);
                    }
                }
                if($timeout_redbagids) {
                    $red   = M('Redbag_order')->where(array('status'=>2, 'uid'=>$this->user['id'], 'id' => array('not in', $timeout_redbagids)))->select();   
                } else {
                    $red   = M('Redbag_order')->where(array('status'=>2, 'uid'=>$this->user['id']))->select();   
                }
               
                if($red) {
                    foreach($red as $key=>$val) {
                        $red[$key]['left'] = M('Redbag')->field('id')->where(array('orderid' => $val['id'], 'winner' => 0))->count();
                    }
                }
                $this->assign('red', $red);
                $this->assign('info', $info);
                $this->assign('cat', $cat);
                $this->assign('order', $order);
                $this->display();
            }
        } else {
            $typedate = I('typedate');
            $depart   = I('depart', '', 'trim');
            $tech     = I('tech');
            $id       = I('id', 0, 'int');
            $field2   = I('field2');
            $field1   = I('field1');
            $remark   = I('remark');
            $types    = I('types');
            $typesdate= I('typesdate');
            $chance   = I('chance', '0', 'int');
            $space    = I('space', 0, 'int');
            $bindred  = I('bindred', 0, 'int');
            $showdraw  = I('showdraw', 0, 'int');
            $showleft  = I('showleft', 0, 'int');
            $showendtime  = I('showendtime', 0, 'int');

            if(!$depart) {
                $this->error('请填写部门！');
            }
            if(!$tech) {
                $this->error('请填写工艺流程！');
            }
            if($bindred > 0) {
                $red = M('Redbag_order')->find($bindred);
                if(!$red) {
                    $this->error('红包不存在！');
                }
                if($red['status']!=2) {
                    $this->error('应用红包必须审核通过！');
                }
                if($chance <=0 || $chance > 100) {
                    $this->error('中奖概率必须是1-100之间');
                }
                if($space < 0 || $space > 9999) {
                    $this->error('领取红包间隔时间范围是0-9999');
                }
            }
            $data = array(
                'depart'    => $depart,
                'tech'      => $tech,
                'remark'    => $remark,
                'field1'    => $field1,
                'field2'    => $field2,
                'types'     => $types,
                'typesdate' => $typesdate,
                'bindred'   => $bindred,
                'space'     => $space,
                'chance'    => $chance,
                'showdraw'   => $showdraw,
                'showleft'   => $showleft,
                'showendtime'=> $showendtime,
            );
            if($typedate) {
                $data['typedate'] = $typedate;
            } else {				$data['typedate'] = '0000-00-00';			}
            if(M('Order')->where(array('uid'=>$this->user['id'],'id'=>$id))->save($data)!==false) {
                $this->success('修改成功！',UU('center/order'));
            } else {
                $this->error('添加失败！');
            }
        }
    }

    public function del_order() {
        $id = I('id', 0, 'int');
        $info = M('Order')->find($id);
        if($info && $info['status'] == 0) {
            if(M('Order')->where(array('uid'=>$this->user['id'],'id'=>$id))->delete()!==false) {
                M('Order_qrcode')->where(array('orderid'=>$id))->delete();
                $this->success('删除成功！');
            } else {
                $this->error('删除失败！');
            }
        } else {
            $this->error('状态错误，不能删除！');
        }
    }

    public function del_qrcode() {
        if(empty($_POST)) {
            $product  = M('Product')->where(array('uid'=>$this->user['id']))->select();
            $company  = M('Qrcode_company')->where(array('uid'=>$this->user['id'],'status'=>0))->select();
            $province = M('Region')->where(array('type'=>1))->select();
            $this->assign('province', $province);
            $this->assign('company', $company);
            $this->assign('date', date('Y-m-d'));
            $this->assign('product', $product);
            $this->assign('id', I('id', 0, 'int'));
            $this->display();
        } else {
            $method   = I('method', 0, 'int');
            $batch    = I('batch', 0, 'int');
            $type     = I('type', 0, 'int');
            $result   = array('msg'=>'', 'result'=>'false');
            $datalist = array();
            if(!$batch) {
                $result['msg'] = '请选择批次信息！';
                $this->ajaxReturn($result);
            }
            if(!$method) {
                $result['msg'] = '请选择二维码批量查找方式！';
                $this->ajaxReturn($result);
            }

            if($type==0){
                $order = M('Order')->find($batch);
                $status = $order['status'];
                if(!$status){
                    $result['msg'] = '当前状态不可以删除！';
                    $this->ajaxReturn($result);
                }elseif ($status != 1){
                    $result['msg'] = '当前状态不可以删除！';
                    $this->ajaxReturn($result);
                }
            }

            $insql = "INSERT INTO cmf_order_qrcode_bak(id,code,orderid,uid,batch,listorder,block,isdel,hits,trace,bindbag,createtime,deltime) SELECT id,code,orderid,uid,batch,listorder,block,isdel,hits,trace,bindbag,createtime,SYSDATE()  FROM cmf_order_qrcode ";

            $Model = M();

            if($method == 1) {
                $qrcode = I('qrcode', '', 'trim');
                if(!$qrcode) {
                    $result['msg'] = '请输入二维码！';
                    $this->ajaxReturn($result);
                }
                $qrcode = explode("\r\n", $qrcode);
                foreach($qrcode as $k => $v) {
                    $code  = explode('-', $v);
                    $batch = M('Order')->where(array('batch'=>$code[0]))->getField('id');
                    if($type == 2) {
                        M('Order_qrcode')->where(array('block'=>$code[1],'listorder'=>$code[2],'orderid'=>$batch,'uid'=>$this->user['id']))->save(array('isdel'=>1));
                    } else {
                        $insql = $insql." where block='".$code[1]."' and listorder='".$code[2]."' and orderid='".$batch."' and uid='".$this->user['id']."'";

                        $Model->execute($insql);

                        /*获取二维码ID，删除浏览记录*/
                        $qrcodeids = M('Order_qrcode')->where(array('block'=>$code[1],'listorder'=>$code[2],'orderid'=>$batch,'uid'=>$this->user['id']))->getField('id', true);
                        if($qrcodeids) {
                            $delete_qrcode = M('Order_qrcode')->where(array('id' => array('in', $qrcodeids)))->delete();
                            $delete_view = M('Order_view')->where(array('qrcode' => array('in', $qrcodeids)))->delete();
                        }
                    }
                }
            } else if($method == 2) {
                $sblock = I('sblock', 0, 'int');
                $eblock = I('eblock', 0, 'int');
                if(!$sblock || !$eblock) {
                    $result['msg'] = '请输入分组！';
                    $this->ajaxReturn($result);
                }
                $result['sblock'] = $sblock;
                $result['eblock'] = $eblock;
                $where = array(
                    'orderid' => $batch,
                    'block'   => array(array('egt',$sblock), array('elt', $eblock)),//egt >=    ,   elt  <=
                    'uid'     => $this->user['id'],
                );
                if($type == 2) {
                    M('Order_qrcode')->where($where)->save(array('isdel'=>1));
                } else {

                    $insql = $insql." where orderid='".$batch."' and uid='".$this->user['id']."' and block >= '".$sblock."' and block <= '".$eblock."'";

                    $Model->execute($insql);

                    /*获取二维码ID，删除浏览记录*/
                    $qrcodeids = M('Order_qrcode')->where($where)->getField('id', true);
                    if($qrcodeids) {
                        $delete_qrcode = M('Order_qrcode')->where(array('id' => array('in', $qrcodeids)))->delete();
                        $delete_view = M('Order_view')->where(array('qrcode' => array('in', $qrcodeids)))->delete();
                    }
                }
            } else if($method == 3) {
                $slistorder = I('slistorder', 0, 'int');
                $elistorder = I('elistorder', 0, 'int');
                if(!$slistorder || !$elistorder) {
                    $result['msg'] = '请输入序列号！';
                    $this->ajaxReturn($result);
                }
                $result['slistorder'] = $slistorder;
                $result['elistorder'] = $elistorder;
                $where = array(
                    'orderid'   => $batch,
                    'listorder' => array(array('egt',$slistorder), array('elt', $elistorder)),
                    'uid'       => $this->user['id'],
                );
                if($type == 2) {
                    M('Order_qrcode')->where($where)->save(array('isdel'=>1));
                } else {

                    $insql = $insql." where orderid='".$batch."' and uid='".$this->user['id']."' and listorder >= '".$slistorder."' and listorder <= '".$elistorder."'";

                    $Model->execute($insql);

                    /*获取二维码ID，删除浏览记录*/
                    $qrcodeids = M('Order_qrcode')->where($where)->getField('id', true);
                    if($qrcodeids) {
                        $delete_qrcode = M('Order_qrcode')->where(array('id' => array('in', $qrcodeids)))->delete();
                        $delete_view = M('Order_view')->where(array('qrcode' => array('in', $qrcodeids)))->delete();
                    }
                }
            }
            $result['result'] = true;
            $this->ajaxReturn($result);
        }   
    }

    public function start_order() {
        $id = I('id', 0, 'int');
        $info = M('Order')->find($id);
        if($info && $info['status'] == 0) {
            if(M('Order')->where(array('uid'=>$this->user['id'],'id'=>$id))->save(array('status'=>1))!==false) {
                $this->success('已经进入审核流程！');
            } else {
                $this->error('保存失败！');
            }
        } else {
            $this->error('状态错误，不能开始！');
        }
    }

    public function redbag() {
        $where = array('uid'=>$this->user['id']);
        $totalsize = M('Redbag_order')->where($where)->count();
        import('Page');
        if($pagesize == 0) {
            $pagesize = 20;
        }
        $PageParam = C("VAR_PAGE");
        $page = new \Page($totalsize,$pagesize);
        $page->setLinkWraper("li");
        $page->__set("PageParam", $PageParam);
        $page->SetPager('default', $pagetpl, array("listlong" => "9", "first" => "首页", "last" => "尾页", "prev" => "上一页", "next" => "下一页", "list" => "*", "disabledclass" => ""));
        $list = M('Redbag_order')->where($where)->order('id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
        foreach($list as $k => $v) {
            $list[$k]['isend'] = strtotime($v['endtime'].' 23:59:59') < time() ? 1 : 0;
            $list[$k]['used'] = M('Redbag')->where(array('orderid'=>$v['id'],'pay_status'=>1))->count('id');
            $list[$k]['scount'] = ($v['amount']/100)*$v['num'];
        }
        $this->assign('list', $list);
        $this->assign('do', 'list');
        $this->assign('page', $page->show('default'));
        $this->display();
    }

    public function qrcode_detail() {
        $id = I('id', 0, 'int');
        if($id > 0) {
            $list = M('Redbag')->where(array('uid'=>$this->user['id'],'orderid'=>$id,'pay_status'=>1))->select();
            foreach($list as $k => $v) {
                $list[$k]['nick'] = M('oauth_user')->where(array('openid'=>$v['winner']))->getField('name');
            }
            $this->assign('list', $list);
            $this->display();
        }
    }

    public function pack_qrcode() {
        $id    = I('id', 0, 'int');
        $order = M('Redbag_order')->where(array('uid'=>$this->user['id'],'id'=>$id))->find();
        if($order) {
            $min = M('Redbag')->where(array('orderid'=>$id))->min('listorder');
            $max = M('Redbag')->where(array('orderid'=>$id))->max('listorder');
            header("Content-type:application/octet-stream"); 
            header("Accept-Ranges:bytes"); 
            header("Content-Disposition: attachment; filename=".$order['id'].'.txt');
            header("Expires:0"); 
            header("Cache-Control:must-revalidate,post-check=0,pre-check=0 "); 
            header("Pragma:public "); 
            for($i=$min;$i<=$max;$i++) {
                $list = M('Redbag')->where(array('orderid'=>$id,'listorder'=>array('egt',$i)))->order('listorder asc')->limit(100)->select();
                if($list) {
                    foreach($list as $k => $v) {
                        echo UU('qrcode/redbag/index',array('id'=>$v['id'],'code'=>$v['code']),true,true).','.$v['listorder']."\r\n";
                        $i = $v['listorder'];
                    }
                } else {
                    $i = $i+20;
                }
            }
            exit(0);
            /*$dir  = './data/qrcode/'.$id.'/';
            $file = './data/zip/'.$id.'.zip';
            if(!file_exists($file)) {
                $zip = new \ZipArchive;
                if($zip->open($file, \ZipArchive::CREATE) === TRUE) {
                    foreach(glob($dir.'*.png') as $filename) {
                        $zip->addFile($filename, basename($filename));
                    }
                    $zip->close();
                }
            }
            header("Cache-Control: public");
            header("Content-Description: File Transfer"); 
            header('Content-disposition: attachment; filename='.basename($file));  
            header("Content-Type: application/zip");
            header("Content-Transfer-Encoding: binary");
            header('Content-Length: '. filesize($file));
            readfile($file);*/
        } else {
            $this->error('未创建该红包！');
        }
    }

    public function add_redbag() {
        if(empty($_POST)) {
            $this->assign('do', 'add');
            $this->display();
        } else {
            $num       = I('num', 0, 'int');
            $amount    = I('amount', 0, 'floatval');
            $starttime = I('starttime');
            $endtime   = I('endtime');
            $wish      = I('wish', 0, 'trim');
            if(!$num || $num < 0) {
                $this->error('请输入红包个数！');
            }
            if(!$amount || $amount < 0) {
                $this->error('请输入单个红包金额');
            }
            if($amount < 1) {
                $this->error('红包金额不能低于1元');
            }
            if(!$starttime) {
                $this->error('请输入开始时间！');
            }
            if(!$endtime) {
                $this->error('请输入结束时间!');
            }
            if(!$wish) {
                $this->error('请输入中奖提示语！');
            }
            $data = array(
                'uid'        => $this->user['id'],
                'num'        => $num,
                'amount'     => $amount*100,
                'starttime'  => $starttime,
                'endtime'    => $endtime,
                'wish'       => $wish,
                'status'     => 1,
                'createtime' => date('Y-m-d H:i:s'),
            );
            if(M('Redbag_order')->add($data)) {
                $this->success('添加成功！请等待管理员审核',UU('redbag'));
            } else {
                $this->error('添加失败！');
            }
        }
    }

    public function ad() {
        if(empty($_POST)) {
            $info = M('Users_ad')->where(array('uid'=>$this->user['id']))->find();
            if(!$info) {
                $info = array(
                    'uid'        => $this->user['id'],
                    'pics'       => '',
                    'createtime' => date('Y-m-d H:i:s'),
                );
                $info['id'] = M('Users_ad')->add($info);
            } else {
                $info['pics'] = json_decode($info['pics'], true);
            }
            $this->assign('info', $info);
            $this->display();
        } else {
            $pics = I('pics', '');
            $data = array();
            $id   = I('id', 0, 'int');
            if(!empty($pics)) {
                $image = array();
                foreach($pics as $key=>$url) {
                    $photourl = sp_asset_relative_url($url);
                    $image[] = array("url"=>$photourl);
                }
                $data['pics'] = json_encode($image);
            } else {
                $this->error('请上传图片！');
            }
            if(M('Users_ad')->where(array('id'=>$id,'uid'=>$this->user['id']))->save($data)!==false) {
                $this->success('保存成功！');
            } else {
                $this->error('保存失败！');
            }
        }
    }

    public function order_view() {
        $id = I('id', 0, 'int');
        if($id > 0) {
            $where = array('qrcode'=>$id);
            $totalsize = M('order_view')->where($where)->count();
            import('Page');
            if($pagesize == 0) {
                $pagesize = 20;
            }
            $PageParam = C("VAR_PAGE");
            $page = new \Page($totalsize,$pagesize);
            $page->setLinkWraper("li");
            $page->__set("PageParam", $PageParam);
            $page->SetPager('default', $pagetpl, array("listlong" => "9", "first" => "首页", "last" => "尾页", "prev" => "上一页", "next" => "下一页", "list" => "*", "disabledclass" => ""));

            $list = M('order_view')->where($where)->limit($page->firstRow . ',' . $page->listRows)->select();
            foreach($list as $k => $v) {
                $list[$k]['nick'] = M('Users')->where(array('id'=>$v['uid']))->getField('user_login');
            }
            $this->assign('page', $page->show('default'));
            $this->assign('list', $list);
            $this->display();
        }
    }

    public function generate() {
        if(empty($_POST)) {
            $id = I('id', 0, 'int');
            $batch = M('Order')->find($id);
            if($batch['status'] > 1) {
                $this->error('不能再生成了！');
            }
            $start = M('Order_qrcode')->where(array('orderid'=>$batch['id']))->order('listorder desc')->find();
            $this->assign('start', $start);
            $this->assign('batch', $batch);
            $this->assign('id', $id);
            $this->display();
        } else {
            $id    = I('id', 0, 'int');
            $num   = I('num', 0, 'int');
            $start = M('Order_qrcode')->where(array('orderid'=>$id))->max('listorder');
            if(!$id) {
                $this->error('请输入批次');
            }
            if(!$num) {
                $this->error('请输入数量！');
            }
            if($num > 10000) {
                $this->error('生成数量超过10000个');
            }
            $batch = M('Order')->find($id);
            $product = M('Product')->find($batch['pid']);
            $datalist = array();
            for($i=0;$i<$num;$i++) {
                ++$start;
                $block = 1;
                if($batch['block'] > 1) {
                    $block = ceil($start/$batch['block']);
                }
                $datalist[] = array(
                    'code'       => sp_random_string(12),
                    'orderid'    => $id,
                    'block'      => $block,
                    'listorder'  => $start,
                    'uid'        => $this->user['id'],
                    'scunitcode' => $product['unitcode']?$product['unitcode'].time().sp_random_num(10):'',
                    'createtime' => date('Y-m-d H:i:s'),
                );
            }
            if($datalist) {
                M('Order_qrcode')->addAll($datalist);
                $this->success('添加成功！', UU('order'));
            }
        }
    }

    public function qrcode() {
        $id = I('id', 0, 'int');
        $batch = M('Order')->where(array('id'=>$id))->getField('batch');
        $where = array('orderid'=>$id, 'uid' => $this->user['id']);
        $listorder = I('listorder', 0, 'int');
        $isdel     = I('isdel', -1, 'int');
        $trace     = I('trace', -1, 'int');
        $sort     = I('sort', '', 'trim');
        if($listorder) {
            $where['listorder'] = $listorder;
        }
        if($isdel > -1) {
            $where['isdel'] = $isdel;
        }
        if($trace > -1) {
            $where['trace'] = $trace;
        }
        $totalsize = M('Order_qrcode')->where($where)->count();
        import('Page');
        if($pagesize == 0) {
            $pagesize = 20;
        }
        $order = 'id desc';
        if($sort && in_array($sort, array('asc', 'desc'))) {
            $order = 'hits '.$sort;
            if($sort == 'desc') {
                $sort = 'asc';
            } else {
                $sort = 'desc';
            }
        }
        $PageParam = C("VAR_PAGE");
        $page = new \Page($totalsize,$pagesize);
        $page->setLinkWraper("li");
        $page->__set("PageParam", $PageParam);
        $page->SetPager('default', $pagetpl, array("listlong" => "9", "first" => "首页", "last" => "尾页", "prev" => "上一页", "next" => "下一页", "list" => "*", "disabledclass" => ""));
        $list = M('Order_qrcode')->where($where)->order($order)->limit($page->firstRow . ',' . $page->listRows)->select();
       
        if(!$sort) {
            $sort = 'desc';
        }

        $this->assign('id', $id);
        $this->assign('batch', $batch);
        $this->assign('sort', $sort);
        $this->assign('list', $list);
        $this->assign('page', $page->show('default'));
        $this->display();
    }

    public function trans() {
        if(empty($_POST)) {
            $id = I('id', 0, 'int');
            $batch = M('Order')->find($id);
            $list  = M('Order')->field('id,batch')->where(array('pid'=>$batch['pid'],'uid'=>$this->user['id']))->select();
            $this->assign('list', $list);
            $categorys = M('Product_category')->where(array('uid' => $this->user['id']))->select();
            $this->assign('categorys', $categorys);
            $this->assign('batch', $batch);
            $this->display();
        } else {
            $start = I('start', 0, 'int');
            $end   = I('end', 0, 'int');
            $id    = I('id', 0, 'int');
            $batch = I('batch', '', 'trim');
            if(!$start || !$end || $start < 0 || $end < 0) {
                $this->error('请输入码段');
            }
            if($end < $start) {
                $this->error('结束码小于开始码！');
            }
            $min = M('Order_qrcode')->where(array('orderid'=>$id, 'uid' => $this->user['id']))->min('listorder');
            $max = M('Order_qrcode')->where(array('orderid'=>$id, 'uid' => $this->user['id']))->max('listorder');
            if($min > $start || $max < $end) {
                $this->error('码段不存在！');
            }
            $trans = M('Order')->where(array('batch'=>$batch))->find();
            if(!$trans) {
                $this->error('批次不存在！');
            }
            $old = M('Order')->find($id);
            			/*只能转移未转移过的二维码*/
            for($i=$start;$i<=$end;$i++) {
                $savedata = array('orderid' => $trans['id']);
                $old_qrcode = M('Order_qrcode')->where(array('listorder'=>$i,'orderid'=>$id,'uid'=>$this->user['id']))->find();
                if($old_qrcode) {
                    if(!$old_qrcode['batch']){
                        $savedata['batch'] = $old['batch'];
                    }
                    M('Order_qrcode')->where(array('id'=>$old_qrcode['id']))->save($savedata);
                }
            }
            $this->success('转移成功！');
        }
    }

    public function export() {
        $id = I('id', 0, 'int');
        if($id > 0) {
            $order = M('Order')->find($id);
            $min   = M('Order_qrcode')->where(array('orderid'=>$id))->min('listorder');
            $max   = M('Order_qrcode')->where(array('orderid'=>$id))->max('listorder');
            header("Content-type:application/octet-stream"); 
            header("Accept-Ranges:bytes"); 
            header("Content-Disposition: attachment; filename=".$order['batch'].'.txt');
            header("Expires:0"); 
            header("Cache-Control:must-revalidate,post-check=0,pre-check=0 "); 
            header("Pragma:public "); 
            for($i=$min;$i<=$max;$i++) {
                $list = M('Order_qrcode')->where(array('orderid'=>$id,'listorder'=>array('egt',$i)))->order('listorder asc')->limit(100)->select();
                if($list) {
                    foreach($list as $k => $v) {
                        echo UU('Qrcode/company/index',array('id'=>$v['id'],'code'=>$v['code']),true,true).','.(empty($v['batch']) ? $order['batch'] : $v['batch']).'-'.$v['block'].'-'.$v['listorder']."\r\n";
                        $i = $v['listorder'];
                    }
                } else {
                    $i = $i+20;
                }
            }
            exit(0);
        }
    }

    public function add_trace() {
        $method   = I('method', 0, 'int');
        $batch    = I('batch', 0, 'int');
        $identity = I('identity', '', 'trim');
        $company  = I('company', '', 'trim');
        $city     = I('city', '', 'trim');
        $province = I('province', '', 'trim');
        $sendtime = I('sendtime', '', 'trim');
        $result   = array('msg'=>'', 'result'=>'false');
        $datalist = array();
        if(!$batch) {
            $result['msg'] = '请选择批次信息！';
            $this->ajaxReturn($result);
        }
        if(!$method) {
            $result['msg'] = '请选择二维码批量查找方式！';
            $this->ajaxReturn($result);
        }
        if(!$identity) {
            $result['msg'] = '请选择身份';
            $this->ajaxReturn($result);
        }
        if(!$company) {
            $result['msg'] = '请选择公司';
            $this->ajaxReturn($result);
        }
        if(!$city) {
            $result['msg'] = '请选择城市';
            $this->ajaxReturn($result);
        }
        if(!$sendtime) {
            $result['msg'] = '请选择时间';
            $this->ajaxReturn($result);
        }
        if($method == 1) {
            $qrcode = I('qrcode', '', 'trim');
            if(!$qrcode) {
                $result['msg'] = '请输入二维码！';
                $this->ajaxReturn($result);
            }
            $qrcode = explode("\r\n", $qrcode);
            foreach($qrcode as $k => $v) {
                $code  = explode('-', $v);
                $batch = M('Order')->where(array('batch'=>$code[0]))->getField('id');
                $id    = M('Order_qrcode')->where(array('block'=>$code[1],'listorder'=>$code[2],'orderid'=>$batch,'uid'=>$this->user['id']))->getField('id');
                if($identity == '厂家') {
                    if(M('Order_trace')->where(array('qrcode'=>$id, 'identity' => '厂家'))->count()) {
                        M('Order_trace')->where(array('qrcode'=>$id, 'identity' => '厂家'))->save(array(
                            'identity' => $identity,
                            'company'  => $company,
                            'city'     => $city,
                            'province' => $province,
                            'sendtime' => $sendtime,
                            'uid'      => $this->user['id'],
                            'qrcode'   => $id,
                            'createtime' => date('Y-m-d H:i:s'),
                        ));
                        continue;
                    }
                }
                $datalist[] = array(
                    'identity' => $identity,
                    'company'  => $company,
                    'city'     => $city,
                    'province' => $province,
                    'sendtime' => $sendtime,
                    'uid'      => $this->user['id'],
                    'qrcode'   => $id,
                    'createtime' => date('Y-m-d H:i:s'),
                );
                M('Order_qrcode')->where(array('id'=>$id))->save(array('trace'=>1));
                if(count($datalist) > 100) {
                    M('Order_trace')->addAll($datalist);
                    $datalist = array();
                }
            }
            M('Order_trace')->addAll($datalist);
        } else if($method == 2) {
            $sblock = I('sblock', 0, 'int');
            $eblock = I('eblock', 0, 'int');
            if(!$sblock || !$eblock) {
                $result['msg'] = '请输入分组！';
                $this->ajaxReturn($result);
            }
            $result['sblock'] = $sblock;
            $result['eblock'] = $eblock;
            $where = array(
                'orderid' => $batch,
                'block'   => array(array('egt',$sblock), array('elt', $eblock)),
                'uid'     => $this->user['id'],
            );
            $qrcode = M('Order_qrcode')->where($where)->select();
            foreach($qrcode as $k => $v) {
                if($identity == '厂家') {
                    if(M('Order_trace')->where(array('qrcode'=>$v['id'], 'identity' => '厂家'))->count()) {
                        M('Order_trace')->where(array('qrcode'=>$v['id'], 'identity' => '厂家'))->save(array(
                            'identity' => $identity,
                            'company'  => $company,
                            'city'     => $city,
                            'province' => $province,
                            'sendtime' => $sendtime,
                            'uid'      => $this->user['id'],
                            'qrcode'   => $v['id'],
                            'createtime' => date('Y-m-d H:i:s'),
                        ));
                        continue;
                    }
                }
                $datalist[] = array(
                    'identity' => $identity,
                    'company'  => $company,
                    'city'     => $city,
                    'province' => $province,
                    'sendtime' => $sendtime,
                    'uid'      => $this->user['id'],
                    'qrcode'   => $v['id'],
                    'createtime' => date('Y-m-d H:i:s'),
                );
                M('Order_qrcode')->where(array('id'=>$id))->save(array('trace'=>1));
                if(count($datalist) > 100) {
                    M('Order_trace')->addAll($datalist);
                    $datalist = array();
                }
            }
            M('Order_trace')->addAll($datalist);
        } else if($method == 3) {
            $slistorder = I('slistorder', 0, 'int');
            $elistorder = I('elistorder', 0, 'int');
            if(!$slistorder || !$elistorder) {
                $result['msg'] = '请输入序列号！';
                $this->ajaxReturn($result);
            }
            $result['slistorder'] = $slistorder;
            $result['elistorder'] = $elistorder;
            $where = array(
                'orderid'   => $batch,
                'listorder' => array(array('egt',$slistorder), array('elt', $elistorder)),
                'uid'       => $this->user['id'],
            );
            $qrcode = M('Order_qrcode')->where($where)->select();
            foreach($qrcode as $k => $v) {
                if($identity == '厂家') {
                    if(M('Order_trace')->where(array('qrcode'=>$v['id'], 'identity' => '厂家'))->count()) {
                        M('Order_trace')->where(array('qrcode'=>$v['id'], 'identity' => '厂家'))->save(array(
                            'identity' => $identity,
                            'company'  => $company,
                            'city'     => $city,
                            'province' => $province,
                            'sendtime' => $sendtime,
                            'uid'      => $this->user['id'],
                            'qrcode'   => $v['id'],
                            'createtime' => date('Y-m-d H:i:s'),
                        ));
                        continue;
                    }
                }
                $datalist[] = array(
                    'identity' => $identity,
                    'company'  => $company,
                    'city'     => $city,
                    'province' => $province,
                    'sendtime' => $sendtime,
                    'uid'      => $this->user['id'],
                    'qrcode'   => $v['id'],
                    'createtime' => date('Y-m-d H:i:s'),
                );
                M('Order_qrcode')->where(array('id'=>$id))->save(array('trace'=>1));
                if(count($datalist) > 100) {
                    M('Order_trace')->addAll($datalist);
                    $datalist = array();
                }
            }
            M('Order_trace')->addAll($datalist);
        }
        $result['result'] = true;
        $this->ajaxReturn($result);
    }

    public function batch_trace() {
        if(empty($_POST)) {
            $product  = M('Product')->where(array('uid'=>$this->user['id'], 'status' => 1))->select();
            $company  = M('Qrcode_company')->where(array('uid'=>$this->user['id'],'status'=>0))->select();
            $province = M('Region')->where(array('type'=>1))->select();
            $this->assign('province', $province);
            $this->assign('date', date('Y-m-d'));
            $this->assign('company', $company);
            $this->assign('product', $product);
            $this->display();
        } else {
            $method = I('method', 0, 'int');
            $batch  = I('batch', 0, 'int');
            $status = I('status',1,'int');
            $result = array('msg'=>'', 'result'=>'false');
            $list   = array();
            if(!$batch) {
                $result['msg'] = '请选择批次信息！';
                $this->ajaxReturn($result);
            }
            if(!$method) {
                $result['msg'] = '请选择二维码批量查找方式！';
                $this->ajaxReturn($result);
            }
            if($method == 1) {
                $qrcode = I('qrcode', '', 'trim');
                if(!$qrcode) {
                    $result['msg'] = '请输入二维码！';
                    $this->ajaxReturn($result);
                }
                $order = M('Order')->find($batch);
                $status = $order['status'];
                $result['qrcode'] = $qrcode;
                $codes  = explode("\r\n", $qrcode);
                foreach($codes as $k => $v) {
                    $keys  = explode('-', $v);
                    $order = M('Order')->where(array('batch'=>$keys[0]))->find();
                    if(!$order) {
                        continue;
                    }
                    $qrcode = M('Order_qrcode')->where(array('orderid'=>$order['id'],'block'=>$keys[1],'listorder'=>$keys[2]))->find();
                    if(!$qrcode) {
                        continue;
                    }
                    $c = M('Order_trace')->where(array('qrcode'=>$qrcode['id']))->count();
                    $old_batch = $order['batch'];
                    $words = '';
                    if($qrcode['batch']) {
                        $old_batch = $qrcode['batch'];
                        $words = '（转移码）';
                    }
                    $list[] = array(
                        'already' => $c ? true : false,
                        'code' =>   $old_batch.'-'.$qrcode['block'].'-'.$qrcode['listorder'].$words,
                        'isdel' => $qrcode['isdel'],
                    );
                }
                $count = count($list);
            } else if($method == 2) {
                $sblock = I('sblock', 0, 'int');
                $eblock = I('eblock', 0, 'int');
                if(!$sblock || !$eblock) {
                    $result['msg'] = '请输入分组！';
                    $this->ajaxReturn($result);
                }
                if($eblock < 0 || $sblock < 0) {
                    $result['msg'] = '请输入正数';
                    $this->ajaxReturn($result);
                }
                $result['sblock'] = $sblock;
                $result['eblock'] = $eblock;
                $where = array(
                    'orderid' => $batch,
                    'block'   => array(array('egt',$sblock), array('elt', $eblock)),
                    'uid'     => $this->user['id'],
                );
                $order = M('Order')->find($batch);
                $status = $order['status'];
                $count = M('Order_qrcode')->where($where)->count('id');
                import('Page');
                $page  = new \Page($count,25);
                $query = M('Order_qrcode')->where($where)->limit($page->firstRow . ',' . $page->listRows)->select();
                $old_batch = $order['batch'];

                foreach($query as $k => $v) {
                    $c = M('Order_trace')->where(array('qrcode'=>$v['id']))->count();
                    $words = '';
                    if($v['batch']) {
                        $old_batch = $v['batch'];
                        $words = '（转移码）';
                    }
                    $list[] = array(
                        'already' => $c ? true : false,
                        'code' => $old_batch.'-'.$v['block'].'-'.$v['listorder'].$words,
                        'isdel' => $v['isdel'],
                    );
                }
            } else if($method == 3) {
                $slistorder = I('slistorder', 0, 'int');
                $elistorder = I('elistorder', 0, 'int');
                if(!$slistorder || !$elistorder) {
                    $result['msg'] = '请输入序列号！';
                    $this->ajaxReturn($result);
                }
                if($slistorder < 0 || $elistorder < 0) {
                    $result['msg'] = '请输入正数';
                    $this->ajaxReturn($result);
                }
                $result['slistorder'] = $slistorder;
                $result['elistorder'] = $elistorder;
                $where = array(
                    'orderid'   => $batch,
                    'listorder' => array(array('egt',$slistorder), array('elt', $elistorder)),
                    'uid'       => $this->user['id'],
                );
                $order = M('Order')->find($batch);
                $status = $order['status'];
                $count = M('Order_qrcode')->where($where)->count('id');
                import('Page');
                $page  = new \Page($count,25);
                $query = M('Order_qrcode')->where($where)->limit($page->firstRow . ',' . $page->listRows)->select();
                $old_batch = $order['batch'];

                foreach($query as $k => $v) {
                    $c = M('Order_trace')->where(array('qrcode'=>$v['id']))->count();
                    $words = '';
                    if($v['batch']) {
                        $old_batch = $v['batch'];
                        $words = '（转移码）';
                    }
                    $list[] = array(
                        'already' => $c ? true : false,
                        'code' => $old_batch.'-'.$v['block'].'-'.$v['listorder'].$words,
                        'isdel' => $v['isdel'],
                    );
                }
            }
            $result['count']  = $count;
            $result['status'] = $status;
            $result['list']   = $list;
            $result['result'] = true;
            $result['method'] = $method;
            $this->ajaxReturn($result);
        }
    }

    public function ajax_batch() {
        $pid    = I('pid', 0, 'int');
        $list   = M('Order')->where(array('pid'=>$pid,'status'=>3))->order('id desc')->select();
        $result = array();
        foreach($list as $k => $v) {
            array_push($result, $v);
        }
        echo json_encode($result);
    }

    public function ajax_product() {
	    $category    = I('category', 0, 'intval');
	    $list   = M('Product')->where(array('category'=>$category))->order('pid desc')->select();
	    $result = array();
	    foreach($list as $k => $v) {
	        array_push($result, $v);
	    }
	    echo json_encode($result);
	}

    public function get_region() {
        $pid    = I('pid', 0, 'int');
        $list   = M('Region')->where(array('pid'=>$pid))->select();
        $result = array();
        foreach($list as $k => $v) {
            array_push($result, $v);
        }
        echo json_encode($result);
    }
}
