<?php

/**
 * 会员
 */
namespace User\Controller;
use Common\Controller\AdminbaseController;
class IndexadminController extends AdminbaseController {

    function index() {
        $where = array('user_status'=>1);
        
        $uid = I('uid', -1, 'intval');
        $name = I('name', '', 'trim');
        $nick = I('nick', '', 'trim');
        $mobile = I('mobile', '', 'trim');
        $email = I('email', '', 'trim');
        $status = I('status', -1, 'intval');
        if($uid > 0) {
            $where['id'] = $uid;
        } else {
            $uid = '';
        }
        if($name) {
            $where['user_login'] = array('like', '%'.$name.'%');
        }
        if($nick) {
            $where['user_nicename'] = array('like', '%'.$nick.'%');
        }
        if($mobile) {
            $where['mobile'] = $mobile;
        }
        if($email) {
            $where['email'] = $email;
        }
        if($status > -1) {
            $where['user_status'] = $status;
        }
        $where['user_type'] = 3;
    	$count = M("Users")->where($where)->count();
    	$page = $this->page($count, 20);
    	$lists = M("Users")->where($where)->order("create_time DESC")->limit($page->firstRow . ',' . $page->listRows)->select();
        if($lists) {
            $uids = array();
            foreach($lists as $key => $value) {
                if($value['fromuid'] > 0) {
                    $uids[$value['fromuid']] = $value['fromuid'];
                }
            }
            if($uids) {
                $recommendlist = M('Users')->where(array('id' => array('in', $uids)))->getField('id,user_login');
                if($recommendlist) {
                    foreach($lists as $key => $value) {
                        $value['fromuser'] = '';
                        if($value['fromuid'] > 0 && isset($recommendlist[$value['fromuid']])) {
                            $value['fromuser'] = $recommendlist[$value['fromuid']];
                        }                        
                        $lists[$key] = $value;
                    }
                }
            }
        }
    	$this->assign('lists', $lists);
    	$this->assign('page', $page->show('Admin'));
    	$this->assign('uid', $uid);
        $this->assign('name', $name);
        $this->assign('nick', $nick);
        $this->assign('mobile', $mobile);
        $this->assign('email', $email);
        $this->assign('status', $status);
    	$this->display(":index");
    }
    
	function password() {
		if(IS_POST) {
			$password = I('post.password', '', 'trim');
			$repassword = I('post.repassword', '', 'trim');
			$uid = I('post.uid', 0, 'intval');
    		if(empty($password)){
    			$this->error("新密码不能为空！");
    		} else if(!preg_match('/((?=.*[0-9])(?=.*[A-z]))|((?=.*[A-z])(?=.*[^A-z0-9]))|((?=.*[0-9])(?=.*[^A-z0-9]))^.{6,20}$/', $password)) {
				$this->error("新密码密码格式错误");
			} else if($password != $repassword) {
				$this->error("新密码不能和原始密码相同！");
			}
			$salt = sp_random_string(6);
			$data['pass_salt'] = $salt;
			$data['user_pass'] = md5(md5($password).$salt);
			$data['id'] = $uid;
			if(M("Users")->save($data)) {
				$this->success("修改成功！");
			} else {
				$this->error("修改失败！");
			}
		}
		$id = I('get.id', 0, 'intval');
		$user = M("Users")->where(array("id"=>$id, "user_type"=>2))->find();
		if($user) {
			$this->assign('user', $user);
    		$this->display(":password");
    	} else {
    		$this->error('会员不存在！');
    	}
	}
	
    function ban(){
    	$id=intval($_GET['id']);
    	if ($id) {
    		$rst = M("Users")->where(array("id"=>$id))->setField('user_status','0');
    		if ($rst) {
    			$this->success("会员拉黑成功！", U("indexadmin/index"));
    		} else {
    			$this->error('会员拉黑失败！');
    		}
    	} else {
    		$this->error('数据传入失败！');
    	}
    }
    
    function cancelban(){
    	$id=intval($_GET['id']);
    	if ($id) {
    		$rst = M("Users")->where(array("id"=>$id))->setField('user_status','1');
    		if ($rst) {
    			$this->success("会员启用成功！", U("indexadmin/index"));
    		} else {
    			$this->error('会员启用失败！');
    		}
    	} else {
    		$this->error('数据传入失败！');
    	}
    }

    //添加会员
    public function adduser() {
        if(empty($_POST)) {
            $this->display(':adduser');
        } else {
            $nice   = I('user_nicename', '', 'trim');
            $login  = I('user_login', '', 'trim');
            $pass   = I('user_pass', '', 'trim');
            $remark = I('remark', '', 'trim');
            if(!$nice) {
                $this->error('请输入公司名称！');
            }
            if(!$login) {
                $this->error('请输入用户名！');
            }
            if(!$pass) {
                $this->error('请输入密码！');
            }
            if(!preg_match('/((?=.*[0-9])(?=.*[A-z]))|((?=.*[A-z])(?=.*[^A-z0-9]))|((?=.*[0-9])(?=.*[^A-z0-9]))^.{6,20}$/', $pass)) {
                $this->error('密码格式不正确, 请使用字母、数字和符号两种以上的组合, 6-20个字符');
            }
            if(M('Users')->where(array('user_login' => $login))->count()) {
                $this->error('账号已经存在！');
            }
            if(M('Users')->where(array('user_nicename' => $nice,'user_status' => 1))->count()) {
                $this->error('公司名称已经存在！');
            }
            $salt = sp_random_string(6);
            $data = array(
                'user_login'      => $login,
                'user_email'      => '',
                'mobile'          => '',
                'user_nicename'   => $nice,
                'user_pass'       => md5(md5($pass).$salt),
                'pass_salt'       => $salt,
                'last_login_ip'   => '127.0.0.1',
                'create_time'     => date("Y-m-d H:i:s"),
                'last_login_time' => date("Y-m-d H:i:s"),
                'user_status'     => 1,
                'mobile_status'   => 1,
                'email_status'    => 0,
                'user_type'       => 3,//会员
                'remark'          => $remark
            );
            if(preg_match('/^1[3|4|5|7|8]\d{9}$/', $login)) {
                $data['mobile'] = $login;
            }
            $uid = M('Users')->add($data);
            if($uid > 0) {
                $data = array(
                    'uid'     => $uid,
                    'name'    => $nice,
                    'phone'   => '',
                    'about'   => '',
                    'culture' => '',
                    'honor'   => '',
                    'pics'    => '',
                );
                M('Users_company')->add($data);
                $this->success('添加成功！', UU('index'));
            } else {
                $this->error('添加失败！');
            }
        }
    }

    //添加会员
    public function edituser() {
        if(empty($_POST)) {
            $id = I('id', 0, 'int');
            if($id > 0) {
                $info = M('Users')->find($id);
                $this->assign('info', $info);
                $this->display(':edituser');
            }
        } else {
            $nice   = I('user_nicename', '', 'trim');
            $pass   = I('user_pass', '', 'trim');
            $remark = I('remark', '', 'trim');
            $id     = I('id', 0, 'int');
            if(!$nice) {
                $this->error('请输入公司名称！');
            }
            if(M('Users')->where(array('user_nicename' => $nice, 'user_status' => 1, 'id' => array('neq', $id)))->count()) {
                $this->error('公司名称已经存在！');
            }
            if($pass) {
                if(!preg_match('/((?=.*[0-9])(?=.*[A-z]))|((?=.*[A-z])(?=.*[^A-z0-9]))|((?=.*[0-9])(?=.*[^A-z0-9]))^.{6,20}$/', $pass)) {
                    $this->error('密码格式不正确, 请使用字母、数字和符号两种以上的组合, 6-20个字符');
                }
                $salt = sp_random_string(6);
                $data['pass_salt']     = $salt;
                $data['user_pass']     = md5(md5($pass).$salt);
            }
            $data['id']            = $id;
            $data['user_nicename'] = $nice;
            $data['remark']        = $remark;
            if(M("Users")->save($data)!==false) {
                $data = array(
                    'uid'     => $id,
                    'name'    => $nice,
                );
                M('Users_company')->save($data);
                $this->success("修改成功！", UU('index'));
            } else {
                $this->error("修改失败！");
            }
        }
    }
}
