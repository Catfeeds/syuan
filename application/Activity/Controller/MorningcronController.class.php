<?php
namespace Activity\Controller;

use Common\Controller\ClibaseController;
use EasyWeChat\Message\Text;
use EasyWeChat\Message\Image;
use Browser\Casper;
use Common\Lib\Queue\Queue;


class MorningcronController extends ClibaseController {

	protected $option_name = 'activity_morning';
    protected $config = array();
    protected $starttime = 0;
    protected $wechatmp = array();
    protected static $wechat = null;

	function _initialize(){
        $this->starttime = microtime(true);
		parent::_initialize();
    	$option = M('Options')->where(array('option_name' => 'activity_morning'))->find();
        if($option) {
            $this->config = json_decode($option['option_value'], true);
        }
        $this->config['pay_amount'] = C('ACTIVITY.MORNING_PAY_AMOUNT');
        $this->wechatmp = D("Wechat/WxMp")->where(array('type' => 3))->order('mpid desc')->find();
        if(self::$wechat === null) {
            self::$wechat = D('Wechat/Wechat', 'Logic')->init($this->wechatmp);
        }
    }

    function index() {
        echo $this->config['pay_amount'];
        return false;
    }

    /*将打卡用户数据重新加入队列*/
    function initshare() {
        $sign_at = date('Y-m-d');
        $queue = new \Common\Lib\Queue\Queue();
        $queuename = 'activity-morning-share-'.md5(C('DB_HOST').C('DB_NAME').C('DB_PREFIX'));
        $queue->initProvider('Memcached', 'Fifo', array('queuename' => $queuename, 'expire' => 6 * 3600));
		$where = array('sign_at' => $sign_at, 'success' => 1);
		$list = D('Activity/MorningSign')->where($where)->select();
		if($list) {
			foreach($list as $val) {
				$queue->push(array('uid' => $val['uid'], 'sign_at' => $sign_at, 'openid' => $val['openid']));
			}
		}
        return false;
    }

    function share() {
        $queue = new \Common\Lib\Queue\Queue();
        $queuename = 'activity-morning-share-'.md5(C('DB_HOST').C('DB_NAME').C('DB_PREFIX'));
        $queue->initProvider('Memcached', 'Fifo', array('queuename' => $queuename, 'expire' => 6 * 3600));

        $today = date('Y-m-d', time());
        $endtime = strtotime($today.' '.$this->config['endtime'].':00');

        $yesterday = date('Y-m-d', time() - 86400);
		$yesterdaypath = SITE_PATH.'data/upload/activity/morning/'.$yesterday;
        if(file_exists($yesterdaypath)) {
			array_map("unlink", glob($yesterdaypath."/*.png"));
			rmdir($yesterdaypath);
		}

        $dir = 'data/upload/activity/morning/'.$today;
        $path = SITE_PATH.$dir;
        if(!file_exists($path)) {
            if(!mkdir($path, 0777, true)) {
                echo '新建目录失败!';
                return false;
            }
        }
        while(true) {
            while($item = $queue->pop()) {
                $uid = $item['uid'];
                $sign_at = $item['sign_at'];
                $openid = $item['openid'];
                $fans = self::$wechat->getFans($openid);
                if($fans && time() - strtotime($fans['lastaction_time']) > 72 * 3600 - 10) {
                    continue;
                }
                $filename = $uid.'.png';
                $casper = new Casper();
                $url = UU('Activity/Public/morningshare', array('uid' => $uid, 'sign_at' => $sign_at), true, true);
                $casper->start($url);
                $casper->setUserAgent('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_2) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.97 Safari/537.11');
                $casper->setViewPort(420, 810);
                $casper->capture(
                    array(
                        'top' => 0,
                        'left' => 0,
                        'width' => 420,
                        'height' => 810
                    ),
                    $path.'/'.$filename
                );
                $casper->run();
                if(file_exists($path.'/'.$uid.'.png')) {
                    $imagepath = $path.'/'.$filename;
                    try {
                        $result = self::$wechat->getApi()->material_temporary->uploadImage($imagepath);
                        if($result && isset($result->media_id)) {
                            $message = new Image(['media_id' => $result->media_id]);
                            try {
                                $response = self::$wechat->getApi()->staff->message($message)->to($openid)->send();
                            } catch(\Exception $e) {
                                $log = '微信日志接口错误, File:'.$e->getFile().', Line: '.$e->getLine().', errno:'.$e->getCode().', err:'.$e->getMessage();
                                \Think\Log::write($log, 'ERR');
                            }
                        } 
                    } catch(\Exception $e) {
                        $log = '微信日志接口错误, File:'.$e->getFile().', Line: '.$e->getLine().', errno:'.$e->getCode().', err:'.$e->getMessage();
                        \Think\Log::write($log, 'ERR');
                    }
                }
            }
            if(time() > $endtime + 60) {
                break;
            } else {
                sleep(3);
            }
        }
        return false;
    }

    //晚上提醒, 9:00开始执行
    function nightbell() {
        if($this->config['status'] == 0) {//活动关闭则不提醒
            return false;
        }
        $sign_at = date('Y-m-d', time() + 86400);
        $max_uid = M('Users')->max('id');
        $url = UU('Activity/Morning/index', array(), true, true);
        for($uid = 1; $uid <= $max_uid; $uid++) {
            if(D('Activity/Join')->isJoin($uid, 'morning') && !D('Activity/MorningSign')->getByUidSignAt($uid, $sign_at)) {
                $openid =  M('OauthUser')->where(array('uid' => $uid, 'status' => 1))->getField('openid');
                if($openid) {
                    $fans = self::$wechat->getFans($openid);
                    if($fans && time() - strtotime($fans['lastaction_time']) < 72 * 3600) {
                        $message = $this->config['need_sign_tip'];
                        $message .= "\r\n<a href=\"{$url}\">➜ 参加挑战</a>";
                        $this->_send_staff_msg($openid, $message);
                    }
                }
            }
        }
        echo "执行完毕, 总计用时: ".(microtime(true) - $this->starttime)."s\n";
        return false;
    }
    
    //8点统计
    function stat() {
        $sign_at = date('Y-m-d', time());
        $where = array('sign_at' => $sign_at);
        $sign_num = D('Activity/MorningSign')->where($where)->count();
        if($this->config['status'] == 0) {
			echo "任务关闭了";
            return false;
        }
        if(D('Activity/MorningStat')->where(array('sign_at' => $sign_at))->count() < 1) {
            $where['success'] = 1;
            $success_num = D('Activity/MorningSign')->where($where)->count();
            $pay_account = floor(($this->config['pay_amount'] * $sign_num * 100) / $success_num);
            D('Activity/MorningStat')->add(array(
                    'sign_at' => $sign_at,
                    'sign_num' => $sign_num,
                    'success_num' => $success_num,
                    'pay_account' => $pay_account,
                    'create_at' => date('Y-m-d H:i:s', time())
                ));
        }
        $top100 = $users = $uids = array();
        $list = D('Activity/MorningSign')->field('uid,sum(success) as num')->group('uid')->having('num > 0')->order('num DESC')->limit(0, 100)->select();
        foreach($list as $key => $val) {         
            $item = $val;
			/*
            if($key > 0) {                
                if($top100[$key - 1]['num'] == $item['num']) {
                    $item['order'] = $top100[$key - 1]['order'];
                } else {
					$item['order'] = $key + 1;//$top100[$key - 1]['order'] + 1;
                }
            } else {
                $item['order'] = 1;
            }
			*/
			$item['order'] = $key + 1;
            $uids[] = $val['uid'];
            $top100[$key] = $item;
        }
        if($uids) {
            $users = D('Users')->where(array('id' => array('IN', $uids)))->getField('id,user_nicename,avatar');
            foreach($users as $key => $val) {
                $val['avatar'] = empty($val['avatar']) ? '' : sp_get_user_avatar_url($val['avatar']);
                $users[$key] = $val;
            }
            foreach($top100 as $key => $item) {
                $item['user'] = $users[$item['uid']];
                $top100[$key] = $item;
            }
        }
        S('activity_morning_top100'.$this->cache_tail, $top100, 3 * 86400);
        echo "执行完毕, 总计用时: ".(microtime(true) - $this->starttime)."s\n";
        return false;
    }

    //当日任务类型type: bell早起提醒 fail失败提醒 pay支付
    //bell: 打卡提醒, 开始打卡时执行
    //fail: 打卡失败提醒, 打卡结束后执行
    //pay: 打卡成功支付奖金, 打卡结束后执行
    function run($type, $sign_at = '') {
        if(!in_array($type, array('bell', 'fail', 'pay'))) {
            echo "not allow type[bell/fail/pay]\n";
            return false;
        }
		if(empty($sign_at)) {
			$sign_at = date('Y-m-d', time());
		}
        $where = array('sign_at' => $sign_at);
        $stat = D('Activity/MorningSign')->where($where)->field('min(id) as minid, max(id) as maxid')->find();
        $minid = $startid = intval($stat['minid']);
        $maxid = intval($stat['maxid']);
        $step = 50;
        $url = UU('Activity/Morning/index', array(), true, true);
        if($maxid > 0 && $maxid >= $minid) {
            if($type == 'fail') {//失败统计
                $fail_where = $where;
                $amount = $this->config['pay_amount'] * intval(D('Activity/MorningSign')->where($fail_where)->count());
                $fail_where['success'] = 1;
                $ok_num = D('Activity/MorningSign')->where($fail_where)->count();
                $this->config['fail_sign_tip'] = str_replace('#date#', $sign_at, $this->config['fail_sign_tip']);
                $this->config['fail_sign_tip'] = str_replace('#amount#', $amount, $this->config['fail_sign_tip']);
                $this->config['fail_sign_tip'] = str_replace('#ok_num#', $ok_num, $this->config['fail_sign_tip']);
            } elseif($type == 'pay') {
                $pay_account = D('Activity/MorningStat')->where(array('sign_at' => $sign_at))->getField('pay_account');
                if(!$pay_account) {
                    $sign_num = D('Activity/MorningSign')->where(array('sign_at' => $sign_at))->count();
                    $success_num = D('Activity/MorningSign')->where(array('sign_at' => $sign_at, 'success' => 1))->count();
                    $pay_account = floor(($this->config['pay_amount'] * $sign_num * 100) / $success_num);
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
					self::$wechat = D('Wechat/Wechat', 'Logic')->init($this->wechatmp);
				}
            } elseif($type == 'bell') {
                $this->config['wake_sign_tip'] = str_replace('#endtime#', $this->config['endtime'], $this->config['wake_sign_tip']);
            }
            do {
                $endid = $startid + $step;
                if($endid > $maxid) {
                    $endid = $maxid;
                }
				$where = array('sign_at' => $sign_at);
                $where['id'] = array('BETWEEN', array($startid, $endid));
                if(in_array($type, array('bell', 'fail'))) {
                    $where['success'] = 0;
                } elseif($type == 'pay') {
                    $where['success'] = 1;
                    $where['pay_status'] = 0;
                }
                $list = D('Activity/MorningSign')->where($where)->field('id,uid,openid,success,getup_at,days')->order('id asc')->select();
                if($list) {
                    foreach($list as $val) {
                        $fans = self::$wechat->getFans($val['openid']);
                        switch($type) {
                            case 'bell'://wake_sign_tip
                                if($fans && time() - strtotime($fans['lastaction_time']) < 72 * 3600) {
    								$timeleft = '';
    								$endtime = strtotime($today.' '.$this->config['endtime'].':00');
    								$diffminute = intval(($endtime - time()) / 60);
    								if($diffminute > 0) {
    									$hours = intval($diffminute / 60);
    									$minutes = $diffminute % 60;
    									if($hours > 0) {
    										$timeleft .= $hours.'小时';
    									}
    									if($minutes > 0) {
    										$timeleft .= $minutes.'分钟';
    									}
    									$this->config['wake_sign_tip'] = str_replace('#timeleft#', $timeleft, $this->config['wake_sign_tip']);
										$message = $this->config['wake_sign_tip'];
										$message .= "\r\n<a href='{$url}'>➜ 去打卡</a>";
										$this->_send_staff_msg($val['openid'], $message);
									}
								}
                                break;
                            case 'fail'://fail_sign_tip
                                if($fans && time() - strtotime($fans['lastaction_time']) < 72 * 3600) {
                                    $message = $this->config['fail_sign_tip'];
                                    $message .= "\r\n<a href='{$url}'>➜ 不服！继续挑战</a>";
                                    $this->_send_staff_msg($val['openid'], $message);
                                }
                                $data = array('id' => $val['id'], 'days' => 0);
                                D('Activity/MorningSign')->save($data);
                                break;
                            case 'pay'://模板消息
                                if(self::$wechat) {
                                    $data = array('id' => $val['id'], 'partner_trade_no' => 'PN'.date('YmdHis').rand(10000, 99999), 'amount' => $pay_account, 'pay_status' => 0);
									try {
										$response = self::$wechat->getApi()->merchant_pay->send(array(
												'partner_trade_no' => $data['partner_trade_no'],
												'openid' => $val['openid'],
												'check_name' => 'NO_CHECK',
												'amount' => $pay_account,
												'desc' => '打卡奖励-早起打卡',
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
									D('Activity/MorningSign')->save($data);
									$this->_log_pay_data($sign_at, $data);
									if($data['pay_status'] && $fans && time() - strtotime($fans['lastaction_time']) < 72 * 3600) {
										$money = $pay_account / 100;
										$message = "您的早起打卡奖励{$money}元已到账, 请在微信钱包中查收。";
										$message .= "\r\n<a href='{$url}'>➜ 加油！参加下次挑战</a>";
										$this->_send_staff_msg($val['openid'], $message);
									}
                                }
                                break;
                        }
                    }
                }
                $startid = $endid + 1;
            } while($maxid >= $startid);
        }
        echo "执行完毕, 总计用时: ".(microtime(true) - $this->starttime)."s\n";
        return false;
    }

    /**
     * 发送微信客服消息
     * @param string $openid
     * @param string $message 消息内容
     */
    private function _send_staff_msg($openid, $message) {
        if(self::$wechat === null) {
            self::$wechat = D('Wechat/Wechat', 'Logic')->init($this->wechatmp);
        }
        if(self::$wechat) {
            $message = new Text(['content' => $message]);
			try {
				self::$wechat->getApi()->staff->message($message)->to($openid)->send();
			} catch(\Exception $e) {
				$log = '微信日志接口错误, File:'.$e->getFile().', Line: '.$e->getLine().', errno:'.$e->getCode().', err:'.$e->getMessage();
				\Think\Log::write($log, 'ERR');
			}
        }
    }

	private function _log_pay_data($sign_at, $data) {
		$file = LOG_DIR.'morning-pay-'.$sign_at.'.php';
		if(!file_exists($file)) {
			file_put_contents($file, "<?php\r\n");
		}
		$content = $pre = '';
		foreach($data as $field => $value) {
			$content .= $pre.$field.'=>'.$value;
			$pre = '|';
		}
		file_put_contents($file, $content."\r\n", FILE_APPEND);
	}
}
