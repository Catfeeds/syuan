<?php

/**
 * 溯源二维码
 */
namespace Qrcode\Controller;

use Common\Controller\HomebaseController;
class CompanyController extends HomebaseController {
	function _initialize() {
		parent::_initialize();
		if(sp_is_weixin()) {
			/*兼容老域名*/
			if(strpos($_SERVER['HTTP_HOST'], 'suyuan.hryw888.com') !== false) {
				redirect('http://suyuan.dyyx123.com'.$_SERVER['REQUEST_URI']);
			}
			//微信扫码需要授权登录
			$this->wechatmp = D("Wechat/WxMp")->where(array('type' => 3))->order('mpid desc')->find();
	        if(!$this->wechatmp) {
	            $this->error('请先后台设置微信认证服务公众号');
	        } else {
	            if($this->is_login()) {
	                $this->check_user();
	                $this->init_fans();//微信用户信息
	            } else {                
	                if(IS_AJAX) {
	                    $url = UU('api/oauth/login', array('type' => 'wechat', 'mp' => $this->wechatmp['wechat_account'], 'referer' => urlencode(I('server.HTTP_REFERER', ''))), false, true);
	                    $this->error('请先微信登录授权系统', $url);
	                } else {
	                    $url = UU('api/oauth/login', array('type' => 'wechat', 'mp' => $this->wechatmp['wechat_account'], 'referer' => urlencode(sp_get_current_url())), false, true);
	                    redirect($url);
	                }
	            }

	            $this->assign('openid', $this->openid);
	            $this->assign('fans', $this->fans);
	            $this->assign('wechatmp', $this->wechatmp);
	        }
		}
	}
	
	/**
     * 初始化微信用户
     */
    protected function init_fans() {
		$openid =  M('OauthUser')->where(array('uid' => $this->user['id'], 'status' => 1))->getField('openid');
        if($openid) {
            $where = array('original_id' => $this->wechatmp['original_id'], 'openid' => $openid);
            $this->openid = $openid;
            $this->fans = D("Wechat/WxFans")->where($where)->find();
        }
    }

    public function _transmoney($redbag) {
		//转账
		$wechatmp = D("Wechat/WxMp")->where(array('type' => 3))->order('mpid desc')->find();
		if(!$wechatmp) {
			return false;
		}
		$payment = M('PaymentConfig')->where(array('type'=>'wechat','status'=>1,'enable'=>1))->find();
		if($payment) {
			$paymentconfig = unserialize($payment['config']);
			$wechatmp['payment'] = array(
				'merchant_id' => $paymentconfig['merchant_id'],
				'key' => $paymentconfig['key'],
				'cert_path' => SITE_PATH.$paymentconfig['apiclient_cert'],
				'key_path' => SITE_PATH.$paymentconfig['apiclient_key'],
			);
			$wechat = D('Wechat/Wechat', 'Logic')->init($wechatmp);
		} else {
			return false;
		}
		$data = array('partner_trade_no' => 'QRCODE'.date('YmdHis').rand(10000, 99999), 'pay_status' => 0, 'winner' => $this->openid);
		try {
			$response = $wechat->getApi()->merchant_pay->send(array(
				'partner_trade_no' => $data['partner_trade_no'],
				'openid'           => $this->openid,
				'check_name'       => 'NO_CHECK',
				'amount'           => $redbag['amount'],
				'desc'             => $redbag['wish'],
				'spbill_create_ip' => '127.0.0.1'
			));
			if($response->return_code == 'SUCCESS') {
				if($response->result_code == 'SUCCESS') {
					$data['payment_no'] = $response->payment_no;
					$data['pay_status'] = 1;
					$data['payment_time'] = $response->payment_time;
				} else {
					$data['err_code'] = $response->err_code;
					$data['err_code_des'] = $response->err_code_des;
				}
			} else {
				$data['err_code'] = $response->return_code;
				$data['err_code_des'] = $response->return_msg;
			}
		} catch(\Exception $e) {
			$data['err_code'] = $e->getCode();
			$data['err_code_des'] = $e->getMessage();
			$log = '微信日志接口错误, File:'.$e->getFile().', Line: '.$e->getLine().', errno:'.$e->getCode().', err:'.$e->getMessage();
			\Think\Log::write($log, 'ERR');
		}
		M('Redbag')->where(array('code'=>$redbag['code']))->save($data);
		if($data['pay_status']) {
			return true;
		} else {
			return false;
		}
    }

    public function openbag() {
    	$id  = I('id', 0, 'int');
    	$code = I('code', '', 'trim');
    	$msg = array('result' => false, 'msg'=> '祝君下次中奖');
    	if($id > 0) {
    		$qrcode = M('Order_qrcode')->find($id);
    		if(!$qrcode) {
    			$msg['msg']    = '产品不存在！';
				$this->ajaxReturn($msg);
			}
			if($qrcode['code'] != $code) {
				$msg['msg']    = '红包不存在（校验码不正确）！';
				$this->ajaxReturn($msg);
			}
			$order = M('Order')->find($qrcode['orderid']);
			if($order['bindred'] > 0) {
				if($qrcode['bindbag']=='0') {
					//修改二维码红包是否拆开
					$qrcode_result = M('Order_qrcode')->where(array('id' => $id))->save(array('bindbag' => 1));
					//绑定红包
					$k = mt_rand(1,100);
					$bag = array();
					//中奖了
					if($order['chance'] >= $k) {
						//取一个红包
						$bag = M('Redbag')->where(array('orderid'=>$order['bindred'],'winner'=>0,'pay_status'=>0))->find();
						//红包是否过期
						if($bag) {
							$redbag = M('RedbagOrder')->where(array('id'=>$bag['orderid']))->find();
							if($redbag) {
								if(strtotime($redbag['endtime'].' 23:59:59') < time()) {
									$msg['msg']    = '红包已过期！';
									$this->ajaxReturn($msg);
								}
							} else {
								$msg['msg']    = '红包不存在！';
								$this->ajaxReturn($msg);
							}
						}
					}
					//判断模式、再判断是否中奖
					if($order['space'] == '9999') {
						//同批次只可领取一个
						$c = M('Redbag')->where(array('bindbatch'=>$order['id'],'winner'=>$this->openid))->count();
						if(!$c) {
							if($bag) {
								//可显示红包
								if($this->_transmoney($bag)) {
									M('Redbag')->where(array('orderid'=>$order['bindred'],'code'=>$bag['code']))->save(array(
										'bindbatch' => $order['id'],'qrcode'=>$code
									));
									$msg['result'] = true;
									$msg['msg']    = '恭喜您，中奖了！';
									$this->ajaxReturn($msg);
								}else{
                                    $this->ajaxReturn($msg);
                                }
							}
						} else {
							$msg['msg']    = '很抱歉，该批次只能领取一次！';
						}
					} else if ($order['space'] > 0) {
						//间隔小时
						$c = M('Redbag')->where(array('bindbatch'=>$order['id'],'winner'=>$this->openid))->order('id desc')->find();
						if(!$c) {
							if($bag) {
								if($this->_transmoney($bag)) {
									M('Redbag')->where(array('orderid'=>$order['bindred'],'code'=>$bag['code']))->save(array(
										'bindbatch' => $order['id'],'qrcode'=>$code
									));
									$msg['result'] = true;
									$msg['msg']    = '恭喜您，中奖了！';
									$this->ajaxReturn($msg);
								}else{
                                    $this->ajaxReturn($msg);
                                }
							}
						} else {
							$hour = (time()-strtotime($c['payment_time'])) / 3600;
							if(intval($hour) > $order['space']) {
								if($bag) {
									if($this->_transmoney($bag)) {
										M('Redbag')->where(array('orderid'=>$order['bindred'],'code'=>$bag['code']))->save(array(
											'bindbatch' => $order['id'],'qrcode'=>$code
										));
										$msg['result'] = true;
										$msg['msg']    = '恭喜您，中奖了！';
										$this->ajaxReturn($msg);
									}else{
                                        $this->ajaxReturn($msg);
                                    }
								}
							} else {
								$msg['msg'] = '很抱歉，'.$order['space'].'个小时内只能领取一次！';
							}
						}
					} else {
						if($bag) {
							//无限制
							if($this->_transmoney($bag)) {
								M('Redbag')->where(array('orderid'=>$order['bindred'],'code'=>$bag['code']))->save(array(
									'bindbatch' => $order['id'],'qrcode'=>$code
								));
								$msg['result'] = true;
								$msg['msg']    = '恭喜您，中奖了！';
								$this->ajaxReturn($msg);
							}else{
                                $this->ajaxReturn($msg);
                            }
						}
					}
				} else {
				    //查询被谁拆开的
                    $redbag = M('Redbag')->where(array('qrcode'=>$code))->find();

                    $nicename = M('oauth_user')->where(array('openid'=>$redbag['winner']))->getField('name');
                    if($nicename){
					$msg['msg']    = '该红包已被拆开！（于'.$redbag['payment_time'].'被用户'.$nicename.'领取！）';
                    }
				}
			} else {
				$msg['msg']    = '未绑定红包！';
			}
		} else {
			$msg['msg']    = '参数错误！';
		}
		$this->ajaxReturn($msg);
    }

    /**
     *
     */
    public function index() {
		$id   = I('id', 0, 'int');
		$code = I('code', '', 'trim');
		if($id > 0) {
			$qrcode = M('Order_qrcode')->find($id);
			if(!$qrcode) {
				//查询备份表
                $qrcode_bak = M('Order_qrcode_bak')->find($id);
                if($qrcode_bak){
                    $uuid = $qrcode_bak['uid'];
                  //  $orderid = $qrcode_bak['orderid'];
                    if($uuid > 0) {
                        $company = M('Users_company')->where(array('uid'=>$uuid))->find();
                        $company_user = M('Users')->find($uuid);
                        $pics    = M('Users_ad')->where(array('uid'=>$uuid))->getField('pics');

                        $this->assign('hide', I('hide', 1, 'int'));
                        $this->assign('company', $company);
                        $this->assign('company_user', $company_user);
                        $this->assign('pics', json_decode($pics, true));
                        $this->display();

                        exit(0);
                    } else {
                        $this->error('信息不存在！请确认是否为伪冒产品！');
                    }

                }else{
                    $this->error('信息不存在！请确认是否为伪冒产品！');
                }
			}

            $del = $qrcode['isdel'];
            if($del!=0) {
                $uuid = $qrcode['uid'];
                if ($uuid > 0) {
                    $company = M('Users_company')->where(array('uid' => $uuid))->find();
                    $company_user = M('Users')->find($uuid);
                    $pics = M('Users_ad')->where(array('uid' => $uuid))->getField('pics');

                    $this->assign('hide', I('hide', 1, 'int'));
                    $this->assign('company', $company);
                    $this->assign('company_user', $company_user);
                    $this->assign('pics', json_decode($pics, true));
                    $this->display();
                    exit(0);
                }
            }

			if($qrcode['code'] != $code) {
				$this->error('信息不存在！请确认是否为伪冒产品！');
			}
			$auth = M('Qrcode_bind')->where(array('company'=>$qrcode['uid'],'uid'=>$this->user['id'],'status'=>1))->find();
	   		if($auth && sp_is_weixin()) {
	   			$company = M('Qrcode_company')->find($auth['bindid']);
	   			if($company['status'] == 0) {
	   				//查看一个店是否已经溯源
	   				$istrace = M('Order_trace')->where(array('qrcode' => $qrcode['id'],'company' => $auth['bindid']))->count();
	   				if($istrace < 1) {
						if(M('Order_trace')->where(array('uid'=>$this->user['id'],'company' => $auth['bindid'], 'qrcode'=>$qrcode['id']))->count()==0) {
				   			$data = array(
								'uid'        => $this->user['id'],
								'company'    => $auth['bindid'],
								'qrcode'     => $id,
								'createtime' => date('Y-m-d H:i:s'),
				   			);
				   			if(M('Order_trace')->add($data)) {
				   				M('Order_qrcode')->where(array('id'=>$id))->save(array('trace'=>1));
				   				$this->success('溯源成功！',UU('index',array('id'=>$id, 'code'=>$code)));
				   				exit(0);
				   			}
			   			}
	   				}
		   		}
	   		}
			$order = M('Order')->find($qrcode['orderid']);
			//浏览记录
            $view_id = 0;
			if(sp_is_weixin() && !$auth) {
				if(M('Order_view')->where(array('qrcode'=>$id,'uid'=>$this->user['id']))->count()==0) {///一个用户只统计一次
					$hits = M('Order_qrcode')->where(array('id'=>$id,'isdel'=>0))->setInc('hits');
					if($hits){
                        $ipaddr = get_client_ip();
                        $provin = $city = '';
                        if($ipaddr) {
                            $area   = taobaoIP($ipaddr);
                            $provin = $area['province'];
                            $city   = $area['city'];
                        }
                        $view_id = M('Order_view')->add(array(
                            'qrcode'     => $id,
                            'orderid'    => $qrcode['orderid'],
                            'company'    => $qrcode['uid'],
                            'uid'        => $this->user['id'],
                            'ip'         => $ipaddr,
                            'province'   => $provin,
                            'city'       => $city,
                            'createtime' => date('Y-m-d H:i:s'),
                        ));
                    }
				}
			}
			if(sp_is_weixin()) {
			    $wechat = D('Wechat/Wechat', 'Logic')->init($this->wechatmp);
			    $wechat_js = $wechat->getApi()->js;
			    $this->assign('wechat_js', $wechat_js);
			}
			$this->assign('view_id', $view_id);
			//红包
			$prize = false;
			$xianshi = 1;//前台红包显示，1有红包正常显示，2红包被领取完了，显示来晚了无红包，
			$redbag = array();
			if($order['bindred'] && !$auth) {
				//溯源管理员不显示红包
				$redbag = M('RedbagOrder')->where(array('id'=>$order['bindred']))->find();
				$redbag['winnum'] = M('Redbag')->where(array('orderid' => $order['bindred'], 'winner' => array('neq', '0')))->count();//中奖数量

                $red = M('Redbag')->where(array('qrcode'=>$code))->find();

                if($redbag['num']==$redbag['winnum']){
                    $xianshi = 2;
                }

                if($red){//已经被人领取过了
                    $xianshi = 3;
                    $nicename = M('oauth_user')->where(array('openid'=>$red['winner']))->getField('name');
                    $redbag['msg'] = '该红包已被拆开！（于'.$red['payment_time'].'被用户'.$nicename.'领取！）';
                }



                $redbag['leftnum'] = $redbag['num'] - $redbag['winnum'];//剩余数量

				$prize = true;
			}
			$company  = M('Users_company')->find($order['uid']);
			$company_user = M('Users')->find($order['uid']);
			$product  = M('Product')->find($order['pid']);
			$category = M('Product_category')->find($product['category']);
			$pics     = M('Users_ad')->where(array('uid'=>$order['uid']))->getField('pics');
			//溯源历史
			$trace = M("Order_trace")->where(array('qrcode'=>$id))->order('createtime asc')->select();
 			$comid = $comname = array();
			foreach($trace as $k => $v) {
				$comid[$v['company']] = $v['company'];
			}
			if($comid) {
				$comname = M('Qrcode_company')->where(array('id'=>array('in',$comid)))->getField('id,name,uid');
			}
			$this->assign('product', $product);
			$this->assign('comname', $comname);
			$this->assign('trace', $trace);
			$this->assign('company', $company);
			$this->assign('company_user', $company_user);
			$this->assign('pics', json_decode($pics, true));
			$this->assign('category', $category);
			$this->assign('order', $order);
			$this->assign('qrcode', $qrcode);
			$this->assign('prize', $prize);
			$this->assign('xianshi',$xianshi);
			$this->assign('redbag', $redbag);
			$this->display();
		} else {
			$this->error('信息不存在！请确认是否为伪冒产品！');
		}
	}

	public function preview() {
		$id = I('id', 0, 'int');
		$orderid = I('orderid', 0, 'int');
		if($id > 0) {
			$company = M('Users_company')->where(array('uid'=>$id))->find();
			$company_user = M('Users')->find($id);
			$pics    = M('Users_ad')->where(array('uid'=>$id))->getField('pics');
			if($orderid) {
				$order    = M('Order')->find($orderid);
				$product  = M('Product')->find($order['pid']);
				$category = M('Product_category')->find($product['category']);
				//$order['typesdate'] = strtotime($order['typesdate']);
				if(!$order['bindred']) {
					$prize = false;
				} else {
					$prize = true;
				}
				$this->assign('prize', $prize);
				$this->assign('order', $order);
				$this->assign('category', $category);
				$this->assign('product', $product);
			}

			$this->assign('hide', I('hide', 0, 'int'));
			$this->assign('company', $company);
			$this->assign('company_user', $company_user);
			$this->assign('pics', json_decode($pics, true));
			$this->display();
		} else {
			$this->error('信息不存在！');
		}
	}

	public function bind() {
		if(!sp_is_weixin()) {
			$this->error('请在微信打开！');
		}
		if(empty($_POST)){
			$id   = I('id', 0, 'int');
			$key  = I('key', '', 'trim');
			if(md5($id)!=$key) {
				$this->error('链接无效！');
			}
			$info = M('Qrcode_company')->find($id);
			$name = M('Users_company')->where(array('uid'=>$info['uid']))->getField('name');
			$this->assign('name', $name);
			$this->assign('info', $info);
			$this->display();
		} else {
			$id = I('id', 0, 'int');
			$mobile = I('mobile', '', 'trim');
			if($id > 0) {
				if(strlen($mobile)!=11||!preg_match('/1[34589]\d{9}$/', $mobile)) {
					$this->error('请输入手机号！');
				}
                $company = M('Qrcode_company')->where(array('id'=>$id))->getField('uid');

				$bind = M('Qrcode_bind')->where(array('company'=>$company,'uid'=>$this->user['id']))->find();//一家产品只绑定一个（之前为一个溯源点 渠道商）

				if($bind['status']==1) {
					$this->error('已经绑定过此公司！');
				}else if($bind['status']==0 && $bind){
                    $this->error('已经绑定过此公司！正在审核！');
                }

				$data = array(
					'bindid'     => $id,
					'company'    => $company,
					'uid'        => $this->user['id'],
					'createtime' => date('Y-m-d H:i:s'),
				);
				if(M('Qrcode_bind')->add($data)) {
					M('Users')->where(array('id'=>$this->user['id']))->save(array('mobile'=>$mobile));
					if(!sp_is_weixin()) {
					    $this->success('绑定成功！请关闭！');
					}    else {
					    $this->success('绑定成功！请关闭！');
					}
				} else {
					$this->error('绑定失败！');
				}
			}
		}	
	}
	/*更新访问地址*/
	function update_view() {
	    $view_id = I('view_id', 0, 'intval');
	    $city = I('city', '', 'trim');
	    $province = I('province', '', 'trim');
	    $latitude = I('latitude', '', 'trim');
	    $longitude = I('longitude', '', 'trim');
	    if($view_id && $city && $province) {
	        $info = M('Order_view')->find($view_id);
	        if($info) {
	            $result = M('Order_view')->where(array('id' => $view_id))->save(array(
	                'city' => $city,
                    'province' => $province,
                    'latitude' => $latitude,
                    'longitude' => $longitude
                ));
	        }
	    }
	    return false;
	}

	function update_zhenwei(){
	    $id = I('id','','trim');
	    $code = I('code','','trim');
	    if($id && $code){
            M('Order_qrcode')->where(array('id' => $id))->save(array(
                'zhenpin_time' => time()
            ));
        $result['msg'] = '查证成功，您购买的是正品！';
        $result['time'] = date("Y-m-d H:i:s" ,time());
        $this->ajaxReturn($result);
        }
    }
}
