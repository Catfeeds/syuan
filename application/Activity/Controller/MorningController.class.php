<?php
namespace Activity\Controller;

use Common\Controller\MemberwechatbaseController;
use EasyWeChat\Message\Text;
use Common\Lib\Queue\Queue;

class MorningController extends MemberwechatbaseController {

	protected $option_name = 'activity_morning';
    protected $config = array();

	function _initialize() {
		parent::_initialize();
    	$option = M('Options')->where(array('option_name' => 'activity_morning'))->find();
        if($option) {
            $this->config = json_decode($option['option_value'], true);
        } else {
            $this->error('活动尚未配置启用, 请联系管理员!');
        }
        $this->config['pay_amount'] = C('ACTIVITY.MORNING_PAY_AMOUNT');
        $this->assign('config', $this->config);
        $activity = 'morning';
        if(!D('Activity/Join')->isJoin($this->user['id'], $activity)) {
            D('Activity/Join')->doJoin($this->user['id'], $activity);
        }
    }

    function index() {
        $today = date('Y-m-d', TIMESTAMP);

        $pay_callback = false;
        $pay_success = false;
        $pay_success_date = '明天';
        //支付回调
        $trade_no = I('get.trade_no', '', 'trim');
        if($trade_no) {
            $ret = $this->check_jspay_callback($trade_no, I('get.pay_result', '', 'trim'));
            if($ret) {
                $pay_callback = true;
                if($ret['success'] && $ret['charge']['from'] = 'activity-morning') {
				    $pay_success = true;
                    if($charge['orderid'] == $today) {
                        $pay_success_date = '今天';
                    }
                }
            }
        }

        $today_sign = D('Activity/MorningSign')->getByUidSignAt($this->user['id'], $today);
        $tomorrow = date('Y-m-d', TIMESTAMP + 86400);
        $tomorrow_sign = D('Activity/MorningSign')->getByUidSignAt($this->user['id'], $tomorrow);

        $fromtime = strtotime($today.' '.$this->config['starttime'].':00');
        $endtime = strtotime($today.' '.$this->config['endtime'].':00');
        
        $btn = 'sign';//按钮功能 sign 参加挑战 getup起床打卡
        if(TIMESTAMP < $fromtime) {//开始打卡前
            $step = 'before';
            if($today_sign) {
                $btn = 'getup';
            }
        } elseif(TIMESTAMP < $endtime) {//允许打卡时间
            $step = 'doing';
            if($today_sign && $today_sign['success'] == 0) {
                $btn = 'getup';
            } elseif($tomorrow_sign) {
                $btn = 'getup';
            }
        } else {//打卡结束时间
            $step = 'after';
            if($tomorrow_sign) {
                $btn = 'getup';
            }
        }
        $today_need_sign_total = intval(D('Activity/MorningSign')->where(array('sign_at' => $today))->count());
        $total = $today_need_sign_total;
        $tip = $total.'人参加今早挑战, 累计挑战金'.($this->config['pay_amount'] * $total).'元';
        if($tomorrow_sign || TIMESTAMP > $endtime) {
            $total = intval(D('Activity/MorningSign')->where(array('sign_at' => $tomorrow))->count());
            $tip = $total.'人参加明早挑战, 累计挑战金'.($this->config['pay_amount'] * $total).'元';
        }
        $record = array('sign_at' => $today, 'text' => date('n月j日', TIMESTAMP), 'success_num' => 0, 'fail_num' => 0);
        if(TIMESTAMP < $fromtime) {
            $yesterday = date('Y-m-d', TIMESTAMP - 86400);
            $record['sign_at'] = $yesterday;
            $record['text'] = date('n月j日', TIMESTAMP - 86400);
            $record['success_num'] = intval(D('Activity/MorningSign')->where(array('sign_at' => $yesterday, 'success' => 1))->count());
            $record['fail_num'] = intval(D('Activity/MorningSign')->where(array('sign_at' => $yesterday, 'success' => 0))->count());
        } else {
            $record['success_num'] = intval(D('Activity/MorningSign')->where(array('sign_at' => $today, 'success' => 1))->count());
            $record['fail_num'] = $today_need_sign_total - $record['success_num'];
            if(TIMESTAMP < $endtime) {
                $record['fail_num'] = 0;
            }
        }

        $wechat = D('Wechat/Wechat', 'Logic')->init($this->wechatmp);
		$debug = defined('WECHAT_DEBUG') ? WECHAT_DEBUG : false;
        $jsconfig = $wechat->getApi()->js->config(array('hideMenuItems', 'onMenuShareAppMessage'), $debug, false, true);

        $subcribe_tip = 0;
        if((($today_sign && TIMESTAMP <= $fromtime) || $tomorrow_sign) && false == $pay_success && !$wechat->getFans($this->openid)) {
            $subcribe_tip = 1;
            $this->wechatmp['qrcode'] = sp_get_asset_upload_path($this->wechatmp['qrcode']);
            $this->assign('wechatmp', $this->wechatmp);
        }
        $this->assign('subcribe_tip', $subcribe_tip);

        $this->assign('jsconfig', $jsconfig);
        $this->assign('link', UU('Activity/Morning/index', array(), true, true));

        $this->assign('btn', $btn);
        $this->assign('tip', $tip);
        $this->assign('record', $record);
        $this->assign('tomorrow_sign', $tomorrow_sign);
        $this->assign('today_sign', $today_sign);
        $this->assign('pay_callback', $pay_callback);
        $this->assign('pay_success', $pay_success);
        $this->assign('pay_success_date', $pay_success_date);
        $this->assign('starttime', $fromtime);
        $this->display();
    }

    function home() {
        $success_num = intval(D('Activity/MorningSign')->where(array('uid' => $this->user['id'], 'success' => 1))->count());
        $amount = intval(D('Activity/MorningSign')->where(array('uid' => $this->user['id'], 'success' => 1))->sum('amount')) / 100;

        $wechat = D('Wechat/Wechat', 'Logic')->init($this->wechatmp);
		$debug = defined('WECHAT_DEBUG') ? WECHAT_DEBUG : false;
        $jsconfig = $wechat->getApi()->js->config(array('hideMenuItems', 'onMenuShareAppMessage'), $debug, false, true);

        $this->assign('jsconfig', $jsconfig);
        $this->assign('link', UU('Activity/Morning/index', array(), true, true));
        $this->assign('success_num', $success_num);
        $this->assign('amount', $amount);
        $this->display();
    }

    function sign() {
        if(IS_POST) {
            $result = array('status' => false, 'info' => '', 'url' => '');
            if($this->config['status'] == 0) {
                $result['info'] = '早起挑战已经关闭!';
            } else {
                $today = date('Y-m-d', TIMESTAMP);
                $fromtime = strtotime($today.' '.$this->config['starttime'].':00');
                $endtime = strtotime($today.' '.$this->config['endtime'].':00');
                
                $today_sign = D('Activity/MorningSign')->getByUidSignAt($this->user['id'], $today);
                
                $tomorrow = date('Y-m-d', TIMESTAMP + 86400);
                $tomorrow_sign = D('Activity/MorningSign')->getByUidSignAt($this->user['id'], $tomorrow);
                $sign_at = false;
                if(TIMESTAMP < $fromtime) {
                    if($today_sign) {
                        $result['info'] = '您已参加今早的挑战, 请勿重复参加!';
                    } else {
                        $sign_at = $today;//挑战今早
                    }
                } elseif(TIMESTAMP < $endtime) {
                    if($today_sign && $today_sign['success'] == 0 ) {
                        $result['msg'] = '请先早起打卡后再参加挑战';
                    } else {
                        if($tomorrow_sign) {
                            $result['info'] = '您已参加挑战, 请勿重复参加!';
                        } else {
                            $sign_at = $tomorrow;//挑战明早
                        }
                    }
                } else {
                    if($tomorrow_sign) {
                        $result['info'] = '您已参加挑战, 请勿重复参加!';
                    } else {
                        $sign_at = $tomorrow;//挑战明早
                    }
                }
                if($sign_at) {
                    $paymentconfig = M('PaymentConfig')->where(array('type'=>'wechat','status'=>1,'enable'=>1))->find();
                    if($paymentconfig) {
                        $trade_no = 'MORNINGSIGN'.date('YmdHis', TIMESTAMP).rand(10000, 9999);
                        $charge = array(
                            'uid' => $this->user['id'],
                            'type' => 'wechat',
                            'total' => $this->config['pay_amount'] * 100,
                            'trade_no' => $trade_no,
                            'subject' => '参战金',
                            'detail' => '早起挑战-参战金',
                            'pay_sn' => '',
                            'from' => 'activity-morning',
                            'orderid' => $sign_at,
                            'status' => 0,
                            'create_at' => date('Y-m-d H:i:s', TIMESTAMP)
                        );
                        if(D('Finance/PaymentCharge')->add($charge)) {
                            $result['status'] = true;
                            $callback = UU('Activity/Morning/index', array('trade_no' => $trade_no));
                            $result['info'] = '支付成功';
                            $result['url'] = UU('Finance/Wechat/jspay', array(), true, true);
                            $params = array('trade_no' => $trade_no, 'callback' => urlencode($callback));
                            $result['url'] .= '?'.http_build_query($params);                            
                        } else {
                            $result['info'] = '数据异常, 请重试';
                        }
                    } else {
                        $result['info'] = '微信支付未开启';
                    }
                }
            }
            if($result['status']) {
                $this->success($result['info'], $result['url']);
            } else {
                $this->error($result['info']);
            }
        }
        return false;
    }

    function getup() {
        if(IS_POST) {
            $getup_at = intval(microtime(true) * 10000);
            $timestamp = substr($getup_at, 0, 10);
            $result = array('status' => false, 'msg' => '', 'url' => '');
            //$this->success(3, $result['url']);
            $fromtime = strtotime($today.' '.$this->config['starttime'].':00');
            $endtime = strtotime($today.' '.$this->config['endtime'].':00');
            if($timestamp < $fromtime) {
                $result['msg'] = '请在今天'.$this->config['starttime'].'-'.$this->config['endtime'].'之间打卡';
            } elseif($timestamp < $endtime) {
                $today = date('Y-m-d', TIMESTAMP);
                $today_sign = D('Activity/MorningSign')->getByUidSignAt($this->user['id'], $today); 
                if($today_sign) {
                    if($today_sign['success']) {
                        $tomorrow = date('Y-m-d', TIMESTAMP + 86400);
                        $tomorrow_sign = D('Activity/MorningSign')->getByUidSignAt($this->user['id'], $tomorrow); 
                        if($tomorrow_sign) {
                            $result['msg'] = '请在明天'.$this->config['starttime'].'-'.$this->config['endtime'].'之间打卡';
                        } else {
                            $result['status'] = true;
                            $result['msg'] = $today_sign['days'];
                        }
                    } else {
                        $data = array('getup_at' => $getup_at, 'success' => 1, 'days' => $today_sign['days'] + 1); 
                        $where = array('uid' => $this->user['id'], 'sign_at' => $today, 'success' => 0);
                        if(D('Activity/MorningSign')->where($where)->save($data)) {
                            $result['status'] = true;
                            $result['msg'] = $data['days'];                            
                            $wechat = D('Wechat/Wechat', 'Logic')->init($this->wechatmp);
                            $fans = $wechat->getFans($this->openid);
                            if($fans) {//发送客服消息
                                $queue = new \Common\Lib\Queue\Queue();
                                $queuename = 'activity-morning-share-'.md5(C('DB_HOST').C('DB_NAME').C('DB_PREFIX'));
                                $queue->initProvider('Memcached', 'Fifo', array('queuename' => $queuename, 'expire' => 6 * 3600));
                                $queue->push(array('uid' => $this->user['id'], 'sign_at' => $today, 'openid' => $this->openid));
                                $content = $this->config['ok_sign_tip'];
                                $content = str_replace('#days#', $data['days'], $content);
                                $content = str_replace('#datetime#', date('H时i分', $timestamp), $content);
                                $url = UU('Activity/Morning/index', array(), true, true);
                                $content .= "\r\n<a href='{$url}'>➜ 参加明日打卡</a>";
                                $message = new Text(['content' => $content]);                            
                                try {
                                    $wechat->getApi()->staff->message($message)->to($this->openid)->send();
                                } catch(\Exception $e) {
                                    $log = '微信日志接口错误, File:'.$e->getFile().', Line: '.$e->getLine().', errno:'.$e->getCode().', err:'.$e->getMessage();
                                    \Think\Log::write($log, 'ERR');
                                }
                            }
                        } else {
                            $result['msg'] = '打卡失败, 请稍后重试!';
                        }
                    }
                } else {
                    $tomorrow = date('Y-m-d', TIMESTAMP + 86400);
                    $tomorrow_sign = D('Activity/MorningSign')->getByUidSignAt($this->user['id'], $tomorrow); 
                    if($tomorrow_sign) {
                        $result['msg'] = '请在明天'.$this->config['starttime'].'-'.$this->config['endtime'].'之间打卡';    
                    } else {
                        $result['msg'] = '您未参加今早打卡挑战';
                    }
                }
            } else {
                $tomorrow = date('Y-m-d', TIMESTAMP + 86400);
                $tomorrow_sign = D('Activity/MorningSign')->getByUidSignAt($this->user['id'], $tomorrow); 
                if($tomorrow_sign) {
                    $result['msg'] = '请在明天'.$this->config['starttime'].'-'.$this->config['endtime'].'之间打卡';    
                } else {
                    $result['url'] = UU('Activity/Morning/index');
                    $today = date('Y-m-d', TIMESTAMP);
                    $today_sign = D('Activity/MorningSign')->getByUidSignAt($this->user['id'], $today);
                    if($today_sign) {
                        if($today_sign['success']) {
                            $result['msg'] = $today_sign['days'];
                        } else {
                            $result['msg'] = '今日早起挑战失败';
                        }
                    } else {
                        $result['msg'] = '您还没有参加打卡挑战, 请刷新页面参加明日打卡挑战!';
                    }
                }
            }
            if($result['status']) {
                $this->success($result['msg'], $result['url']);
            } else {
                $this->error($result['msg'], $result['url']);
            }
        }
        return false;
    }

    /**
     * 每日打卡记录
     */
    function getuplist() {
        if(!IS_POST) {
            return false;
        }
        $data = array('status' => true, 'info' => array('total' => 0, 'point' => 0, 'html' => ''));
        $today = date('Y-m-d', TIMESTAMP);
        $point = I('point', 0, 'intval');
        $sign_at = I('sign_at', $today, 'trim');
        $where = array('sign_at' => $sign_at, 'success' => 1);
        $fromtime = strtotime($sign_at.' '.$this->config['starttime'].':00');
        $endtime = strtotime($sign_at.' '.$this->config['endtime'].':00');
        if($point >= $fromtime * 10000) {
            $where['getup_at'] = array('LT', $point);
        }
        $list = D('Activity/MorningSign')->where($where)->order('getup_at desc')->field('uid,openid,getup_at,days')->limit(20)->select();
        $end = 0;
        if($list) {
            $data['info']['total'] = count($list);
            if(TIMESTAMP >= $endtime && !isset($where['getup_at'])) {
                $end = 1;
            }
            foreach($list as $key => $val) {
                $val['time'] = date('H:i', substr($val['getup_at'], 0, 10));
                $val['total'] = intval(D('Activity/MorningSign')->where(array('uid' => $val['uid'], 'success' => 1))->sum('amount')) / 100;
                $user = M('Users')->where(array('id' => $val['uid']))->field('user_nicename,avatar')->find();
                $val['nicename'] = $user['user_nicename'];
                $val['avatar'] = empty($user['avatar']) ? '' : sp_get_user_avatar_url($user['avatar']);
                $data['info']['point'] = $val['getup_at'];
                $list[$key] = $val;
            }
        } else {
            if(TIMESTAMP >= $endtime && !isset($where['getup_at'])) {
                $end = 1;
            }
        }
        $this->assign('end', $end);
        $this->assign('endtime', $endtime);
        $this->assign('list', $list);
        $data['info']['html'] = $this->fetch();
        $this->ajaxReturn($data, 'JSON');
    }

    /**
     * 个人中心每月打卡记录
     */
    function monthlist() {
        if(!IS_POST) {
            return false;
        }
        $today = date('Y-m-d', TIMESTAMP);
        $thisyear = date('Y', TIMESTAMP);
        $year = I('year', 0, 'intval');
        $month = I('month', 0, 'intval');
        if($year < 2017 || $year > $thisyear) {
            $this->error('参数错误');
        } elseif($month < 1 || $month > 12) {
            $this->error('参数错误');
        } else {
            $data = array('status' => true, 'info' => array('year' => $year, 'month' => $month, 'list' => array()));
            $list = D('Activity/MorningSign')->where(array('uid' => $this->user['id'], 'sign_at' => array('BETWEEN', array($year.'-'.$month.'-01', $year.'-'.$month.'-31'))))->field('sign_at,getup_at,success,days')->order('sign_at desc')->limit(31)->select();
            if($list) {
                foreach($list as $val) {
                    if(strtotime($val['sign_at']) > TIMESTAMP) {
                        continue;
                    }
                    $item = array(
                            'sign_at' => $val['sign_at'],
                            'success' => $val['success'],
                            'total' => 0
                        );
                    if(!$val['success']) {
                        $item['total'] = D('Activity/MorningSign')->where(array('sign_at' => $item['sign_at'], 'success' => 1))->count();
                    } else {
                        $item['days'] = $val['days'];
                        $item['waketime'] = date('H:i', substr($val['getup_at'], 0, 10));
                    }
                    $j = date('j', strtotime($item['sign_at']));
                    $data['info']['list'][$j] = $item;
                }
            }
            $this->ajaxReturn($data, 'JSON');
        }
    }

    function top() {
        $top100 = S('activity_morning_top100'.$this->cache_tail);
        $wechat = D('Wechat/Wechat', 'Logic')->init($this->wechatmp);
        $success_num = intval(D('Activity/MorningSign')->where(array('uid' => $this->user['id'], 'success' => 1))->count());
        $rank = '--';
        if($success_num > 0) {
			$subquery = D('Activity/MorningSign')->field('uid')->group('uid')->having('sum(success) > '.$success_num)->select(false);
			$rank = D('Activity/MorningSign')->table('('.$subquery.') as S')->count() + 1;
        }
        $debug = defined('WECHAT_DEBUG') ? WECHAT_DEBUG : false;
        $jsconfig = $wechat->getApi()->js->config(array('hideMenuItems', 'onMenuShareAppMessage'), $debug, false, true);
        $this->assign('jsconfig', $jsconfig);
        $this->assign('link', UU('Activity/Morning/index', array(), true, true));
        $this->assign('top100', $top100);
        $this->assign('success_num', $success_num);
        $this->assign('rank', $rank);
        $this->display();
    }

    function share() {        
        $uid = I('uid', 0, 'intval');
        if($uid > 0 && $this->user['id'] != $uid) {
            $user = D('Users')->find($uid);
            $openid =  M('OauthUser')->where(array('uid' => $uid, 'status' => 1))->getField('openid');
        } else {
            $uid = $this->user['id'];
            $openid = $this->openid;
            $user = $this->user;
        }

        $link = UU('Activity/Morning/share', array('uid' => $uid), true, true);
        
        $wechat = D('Wechat/Wechat', 'Logic')->init($this->wechatmp);
        $qrcodeLogic = D('Wechat/Qrcode', 'Logic')->setContext($wechat);
        $result = $qrcodeLogic->getRecommendQrcode($openid);
        $qrcode = '';
        if($result) {
            $qrcode = $result['url'];
        }

        $debug = defined('WECHAT_DEBUG') ? WECHAT_DEBUG : false;
        $jsconfig = $wechat->getApi()->js->config(array('hideMenuItems', 'onMenuShareAppMessage', 'onMenuShareTimeline'), $debug, false, true);

        $success_num = intval(D('Activity/MorningSign')->where(array('uid' => $this->user['id'], 'success' => 1))->count());

        $recommend_num = intval(D('Users')->where(array('fromuid' => $uid))->count());

        $this->assign('qrcode', $qrcode);
        $this->assign('success_num', $success_num);
        $this->assign('recommend_num', $recommend_num);
        $this->assign('jsconfig', $jsconfig);
        $this->assign('user', $user);
        $this->assign('link', $link);
        $this->assign('login_uid', $this->user['id']);
        $this->display();
    }
}
