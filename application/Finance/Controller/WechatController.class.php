<?php
namespace Finance\Controller;

use Common\Controller\MemberwechatbaseController;
use Wechat\Logic\WechatLogic;  
use EasyWeChat\Payment\Order;

class WechatController extends MemberwechatbaseController {

	function _initialize(){
		parent::_initialize();
    }


    /*财务*/
    function balance() {
        $this->display();
    }

    /*财务列表*/
    function balancelist() {
        $page = I('page', 1, 'intval');
        $count = D('Finance/UsersFinance')->where(array("uid" => $this->user['id']))->count();
        $limit = 10;
        $start = (($page - 1) * $limit);
        $totalpages = ceil($count / $limit); //总页数
        if(!empty($totalpages) && $page > $totalpages + 1) {
            $start = (($totalpages - 1) * $limit);
        }

        $list = D('Finance/UsersFinance')->where(array("uid" => $this->user['id']))->order("id DESC")->limit($start.','.$limit)->select();
        if($list) {
            $category = array(2 => array(), 3 => array());
            $category[2] = M("PaymentConfig")->where()->getField('type,name', true);
            $category[3] = C('Finance.CASHOUT_BANK');
            foreach($list as $key=>$val) {
                $val['money'] = D('Finance/UsersFinance')->format_out($val['money']);
                $list[$key] = $val;
            }
        }
        $this->assign('type', D('Finance/UsersFinance')->getFinanceTypes());
        $this->assign("list",$list);

        if($page > 1) {
            $content = $this->fetch('balance_item');
            echo $content;
            return false;
        }
        $nextpage = $page + 1;
        if($nextpage > $totalpages) {
            $nextpage = '0';
        }
        $this->assign('nextpage', $nextpage);
        $this->display();
    }

    /*财务详情*/
    function balancedetail() {
        $id = I('id', 0, 'intval');
        $item = D('Finance/UsersFinance')->where(array('id' => $id, 'uid' => $this->user['id']))->find();
        if(!$item) {
            $this->error('信息不存在!');
        }
        $item['money'] = D('Finance/UsersFinance')->format_out($item['money']);
        $item['accountbefore'] = D('Finance/UsersFinance')->format_out($item['accountbefore']);
        $item['accountafter'] = D('Finance/UsersFinance')->format_out($item['accountafter']);
        $category = array(2 => array(), 3 => array());
        $category[2] = M("PaymentConfig")->where()->getField('type,name', true);
        $category[3] = C('Finance.CASHOUT_BANK');
        $this->assign('type', D('Finance/UsersFinance')->getFinanceTypes());
        $this->assign('item', $item);
        $this->display();
    }

    /*积分*/
    function integral() {
        $this->display();
    }

    /*积分列表*/
    function integrallist() {
        $page = I('page', 1, 'intval');
        $count = D('Finance/UsersGoldLog')->where(array("uid" => $this->user['id']))->count();
        $limit = 10;
        $start = (($page - 1) * $limit);
        $totalpages = ceil($count / $limit); //总页数
        if(!empty($totalpages) && $page > $totalpages + 1) {
            $start = (($totalpages - 1) * $limit);
        }
        $list = D('Finance/UsersGoldLog')->where(array("uid" => $this->user['id']))->order("id DESC")->limit($start.','.$limit)->select();
        $this->assign("list",$list);

        if($page > 1) {
            $content = $this->fetch('integral_item');
            echo $content;
            return false;
        }
        $nextpage = $page + 1;
        if($nextpage > $totalpages) {
            $nextpage = '0';
        }
        $this->assign('nextpage', $nextpage);

        $this->display();
    }

    /*推广收益*/
    function spreadresult() {
        $page = I('page', 1, 'intval');
        $category = 'recommend';
        $count = D('Finance/UsersGoldLog')->where(array("uid" => $this->user['id'], 'category' => $category))->count();
        $total = intval(D('Finance/UsersGoldLog')->where(array("uid" => $this->user['id'], 'type' => 1, 'category' => $category))->sum('gold'));
        $limit = 10;
        $start = (($page - 1) * $limit);
        $totalpages = ceil($count / $limit); //总页数
        if(!empty($totalpages) && $page > $totalpages + 1) {
            $start = (($totalpages - 1) * $limit);
        }

        $list = D('Finance/UsersGoldLog')->where(array("uid" => $this->user['id'], 'category' => $category))->order("id DESC")->limit($start.','.$limit)->select();
        
        $this->assign("total", $total);
        $this->assign("list", $list);

        if($page > 1) {
            $content = $this->fetch('spread_item');
            echo $content;
            return false;
        }
        $nextpage = $page + 1;
        if($nextpage > $totalpages) {
            $nextpage = '0';
        }
        $this->assign('nextpage', $nextpage);

        $this->display();
    }

    /*财务详情*/
    function integraldetail() {
        $id = I('id', 0, 'intval');
        $item = D('Finance/UsersGoldLog')->where(array('id' => $id, 'uid' => $this->user['id']))->find();
        if(!$item) {
            $this->error('信息不存在!');
        }

        $this->assign('type', D('Finance/UsersGoldLog')->getTypes());
        $this->assign('item', $item);
        $this->display();
    }

    /*微信账户充值*/
    function charge() {
        if(IS_POST) {
			$referer = I('referer', '', 'trim');
			if($referer) {
				$referer = urldecode($referer);
			}
            $money = I('money', 0, 'floatval');
            if($money < 1) {
                $this->error('充值金额不能低于1元');
            }
            if($money > 5000) {
                $this->error('充值金额不能高于5000元');
            }
            $paymentconfig = M('PaymentConfig')->where(array('type'=>'wechat','status'=>1,'enable'=>1))->find();
            if($paymentconfig) {
                $trade_no = 'CHARGE'.date('YmdHis', TIMESTAMP).rand(10000, 9999);
                $charge = array(
                    'uid' => $this->user['id'],
                    'type' => 'wechat',
                    'total' => $money * 100,
                    'trade_no' => $trade_no,
                    'subject' => '会员充值',
                    'detail' => '微信支付-会员充值',
                    'pay_sn' => '',
                    'from' => 'wechat-charge',
                    'orderid' => 0,
                    'status' => 0,
                    'create_at' => date('Y-m-d H:i:s', TIMESTAMP)
                );
                if(D('Finance/PaymentCharge')->add($charge)) {
                    $callback = UU('Finance/Wechat/charge_callback', array('trade_no' => $trade_no, 'referer' => urlencode($referer)), false);
                    $url = UU('Finance/Wechat/jspay', array(), true, true);
                    $params = array('trade_no' => $trade_no, 'callback' => urlencode($callback));
                    $url .= '?'.http_build_query($params);
                    $this->success('ok', $url); 
                } else {
                     $this->error('数据异常, 请重试');
                }
            } else {
                 $this->error('微信支付未开启');
            }
        }
        $referer = I('referer', '', 'trim');
        if($referer) {
            $referer = urldecode($referer);
        } else {
            $referer = I('server.HTTP_REFERER', '', 'trim');
        }
        $wechat = D('Wechat/Wechat', 'Logic')->init($this->wechatmp);
        $debug = defined('WECHAT_DEBUG') ? WECHAT_DEBUG : false;
        $apilist = array('hideOptionMenu', 'hideMenuItems', 'hideAllNonBaseMenuItem', 'hideAllNonBaseMenuItem');
        $jsconfig = $wechat->getApi()->js->config($apilist, $debug, false, true);
        $this->assign('jsconfig', $jsconfig);

        $this->assign('referer', urlencode($referer));
        $this->display();
    }

    /*微信账户充值回调*/
    function charge_callback() {
        $referer = I('referer', '', 'trim');
        if($referer) {
            $referer = urldecode($referer);
        }
        //支付回调
        $trade_no = I('get.trade_no', '', 'trim');
        if($trade_no) {
            $ret = $this->check_jspay_callback($trade_no, I('get.pay_result', '', 'trim'));
            if($ret) {
                if($ret['success'] && $ret['charge']['from'] = 'wechat-charge') {
                    $this->success('支付成功', $referer);
                } else {
                    $this->error('支付失败', $referer);
                }                
            } else {
                redirect($referer);    
            }
        } else {
            redirect($referer);
        }
        return false;
    }

    /*微信公众号支付*/
    function jspay() {
        $callback = I('callback', '', 'trim');
        if($callback) {
            $callback = urldecode($callback);
        } else {
            $callback = I('server.HTTP_REFERER', '', 'trim');
        }
        $trade_no = I('trade_no', '', 'trim');
        if(!$trade_no) {
            $this->error('参数错误', $callback);
        }
        $charge = D('Finance/PaymentCharge')->where(array('trade_no' => $trade_no, 'uid' => $this->user['id']))->find();
        if(!$charge) {
            $this->error('订单不存在', $callback);
        } elseif($charge['status'] == 1) {
            redirect($callback);
        } elseif($charge['status'] == 2) {
            $this->error('该订单支付失败, 请重新发起支付', $callback);
        } else {

        }
        $payment = M('PaymentConfig')->where(array('type'=>'wechat','status'=>1,'enable'=>1))->find();
        if($payment) {
            $paymentconfig = unserialize($payment['config']);
            $this->wechatmp['payment'] = array(
                'merchant_id' => $paymentconfig['merchant_id'],
                'key' => $paymentconfig['key'],
                'cert_path' => SITE_PATH.$paymentconfig['apiclient_cert'],
                'key_path' => SITE_PATH.$paymentconfig['apiclient_key'],
            );
            $attributes = array(
                'openid'           => $this->openid,
                'trade_type'       => 'JSAPI',
                'body'             => $charge['subject'],
                'detail'           => $charge['detail'],
                'out_trade_no'     => $trade_no,
                'total_fee'        => defined('PAY_DEBUG') && PAY_DEBUG ? 1 : $charge['total'],
                'notify_url'       => UU('Finance/Notify/wechat', array(), true, true)
            );
            $wechat_order = new Order($attributes);
            try {
                $wechat = D('Wechat/Wechat', 'Logic')->init($this->wechatmp);
                $result = $wechat->getApi()->payment->prepare($wechat_order);
                if($result->return_code == 'SUCCESS' && $result->result_code == 'SUCCESS') {
                    session('jspay_callback_'.$trade_no, $trade_no);
                    $jspayconfig = $wechat->getApi()->payment->configForPayment($result->prepay_id);
                    $debug = defined('WECHAT_DEBUG') ? WECHAT_DEBUG : false;
                    $apilist = array('hideOptionMenu', 'hideMenuItems', 'hideAllNonBaseMenuItem', 'hideAllNonBaseMenuItem', 'chooseWXPay');
					$url = UU('Finance/Wechat/jspay', array(), true, true);
					$wechat->getApi()->js->setUrl($url);
                    $jsconfig = $wechat->getApi()->js->config($apilist, $debug, false, true);
                    $this->assign('callback', $callback);
                    $this->assign('jspayconfig', $jspayconfig);
                    $this->assign('jsconfig', $jsconfig);
                    $this->display(':jspay');
                } else {
                    $error = '微信支付, 下单失败: code:'.$result->return_code.', msg: '.$result->return_msg;
                    $this->error($error, $callback);
                }
            } catch (\Exception $e) {
                $error = '微信支付下单失败: code:'.$e->getCode().', msg:'.$e->getMessage();
                $this->error($error, $callback);
            }
        } else {
            $this->error('微信支付未开启', $callback);
        }
    }
}
