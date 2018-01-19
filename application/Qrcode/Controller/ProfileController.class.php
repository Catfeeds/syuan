<?php

/**
 * 会员中心
 */
namespace Qrcode\Controller;

use Common\Controller\MemberbaseController;

class ProfileController extends MemberbaseController {
	
	protected $users_model;
	
	function _initialize() {
		parent::_initialize();
        if($this->user['user_type'] != 3) {
            $this->error('只有企业用户能够使用该功能！');
        }
		$this->users_model=D("Common/Users");
	}
	
    function index() {
        $do = I('do', '', 'trim');
        if(!in_array($do, array('edit', 'avatar', 'password'))) {
            $do = 'edit';
        }

        $this->assign('do', $do);
        $this->{$do}();
        return false;
    }

    /**
	 * 编辑用户资料
	 */
	private function edit() {
    	$this->display('/Profile/edit');
    }
    
    public function edit_post() {
    	if(IS_POST){
            $com = I('com');
            if($com && is_array($com)) {
                /*校验号码*/
                if($com['phone']) {
                    if(!preg_match("/^[0-9\-]*$/", $com['phone'])){  
                        $this->error("客服电话格式错误！");
                    } 
                }
                if(!empty($com['honor'])) {
                    $pics = array();
                    foreach($com['honor'] as $key=>$url) {
                        $photourl = sp_asset_relative_url($url);
                        $pics[] = array("url"=>$photourl);
                    }
                    $com['honor'] = json_encode($pics);
                }
                if(!empty($com['pics'])) {
                    $pics = array();
                    foreach($com['pics'] as $key=>$url) {
                        $photourl = sp_asset_relative_url($url);
                        $pics[] = array("url"=>$photourl);
                    }
                    $com['pics'] = json_encode($pics);
                }
                if(M('Users_company')->where(array('uid' => $this->user['id']))->save($com)!==false) {
                    if ($this->users_model->field('id,user_nicename,sex,birthday,signature,website,wechat')->create()) {
                        $this->users_model->id = $this->user['id'];
                        if ($this->users_model->save()!==false) {
                            $user=$this->users_model->find($this->user['id']);
                            sp_update_current_user($user);
                        } else {
                            $this->error("个人资料保存失败！");
                        }
                    } else {
                        $this->error($this->users_model->getError());
                    }
                    $this->success("保存成功！".$user['wechat'], leuu("profile/index", array('do' => 'edit')));
                } else {
                    $this->error("保存失败！");
                }
            }
    	}
    }
    
	/**
	 * 修改密码
	 */
    private function password() {
    	$this->display('/Profile/password');
    }
    
    public function password_post() {
    	if(IS_POST) {
			$old_password = I('post.old_password', '', 'trim');
    		$password = I('post.password', '', 'trim');
			$repassword = I('post.repassword', '', 'trim');
    		if(empty($old_password)){
    			$this->error("原始密码不能为空！");
    		} elseif(empty($password)){
    			$this->error("新密码不能为空！");
    		} else if(!preg_match('/((?=.*[0-9])(?=.*[A-z]))|((?=.*[A-z])(?=.*[^A-z0-9]))|((?=.*[0-9])(?=.*[^A-z0-9]))^.{6,20}$/', $password)) {
				$this->error("新密码密码格式错误");
			} else if($password != $repassword) {
				$this->error("新密码不能和原始密码相同！");
			}
    		if($this->user['user_pass'] == md5(md5($old_password).$this->user['pass_salt'])) {
    			if($this->user['user_pass'] == md5(md5($password).$this->user['pass_salt'])) {
					$this->error("新密码不能和原始密码相同！");
				} else {
					$salt = sp_random_string(6);
					$data['pass_salt'] = $salt;
					$data['user_pass'] = md5(md5($password).$salt);
					$data['id'] = $this->user['id'];
					if($this->users_model->save($data)) {
						$this->success("修改成功！");
					} else {
						$this->error("修改失败！");
					}
				}
    		} else {
    			$this->error("原始密码不正确！");
    		}
    	}
    }
	
	/**
	 * 会员头像
	 */
    private function avatar() {
        $this->display('/Profile/avatar');
    }
    
    function avatar_upload() {
        $subdir = (intval($this->user['id'] / 1000) + 1).'/';
        $savepath = 'avatar/'.$subdir;
        //上传处理类
        $config = array(
                'rootPath' => './'.C("UPLOADPATH"),
                'savePath' => $savepath,
                'maxSize' => 11048576,
                'saveName'   =>    array('uniqid',''),
                'exts'       =>    array('jpg', 'gif', 'png', 'jpeg',"txt",'zip'),
                'autoSub'    =>    false,
        );
        $upload = new \Think\Upload($config);// 
        $info=$upload->upload();
        //开始上传
        if($info) {
            //上传成功
            //写入附件数据库信息
            $first = array_shift($info);
            if(!empty($first['url'])) {
                $url = $first['url'];
                $avatar = $url;
            } else {
                $url = C("TMPL_PARSE_STRING.__UPLOAD__").$savepath.$first['savename'];
                $avatar = $subdir.$first['savename'];
            }
            if($this->user['avatar']) {
                if(substr($this->user['avatar'], 0, 4) == 'http') {
                    //todo: delete remote
                } else {
                    $filename = SITE_PATH.C("UPLOADPATH").sp_asset_relative_url($this->user['avatar']);
                    if(file_exists($filename)) {
                        @unlink($filename);
                    }
                }
            }
            D('Common/Users')->save(array('id' => $this->user['id'], 'avatar' => $avatar));
            $this->ajaxReturn(sp_ajax_return(array("url" => $url), "上传成功！", 1), "AJAX_UPLOAD");
        } else {
            $this->ajaxReturn(sp_ajax_return(array(), $upload->getError(), 0), "AJAX_UPLOAD");
        }
    }

    function mobile() {
        if($this->user['mobile_status']) {//完善手机号码
            $this->success("您的手机号已经激活认证!", leuu('User/Center/index'));
            return false;
        }
        if(IS_POST) {
            $mobile = I('post.mobile', '', 'trim');
            $mobile_verify = I('post.mobile_verify', '', 'trim');
            if(empty($mobile) || !preg_match('/^1[3|4|5|7|8]\d{9}$/', $mobile)) {
                $this->error('手机号格式错误');
            } else if(empty($mobile_verify)
                        || !preg_match('/^\d{4}$/', $mobile_verify)
                            || (!session('regok_'.$mobile.$mobile_verify) && !sp_check_mobile_verify_code(3, $mobile, $mobile_verify))) {
                $this->error('短信验证码错误');
            } else if(D("Common/Users")->where(array('mobile' => $mobile, 'id' => array('NEQ', $this->user['id'])))->count() > 0) {
                    $this->error('该手机号已存在');
            } else {
                if(D("Common/Users")->save(array('id' => $this->user['id'], 'mobile' => $mobile, 'mobile_status' => 1))) {
                    $this->success('保存成功', leuu('User/Center/index'));
                } else {
                    $this->error('保存失败');
                }
            }
        }
        $this->display();
    }
}