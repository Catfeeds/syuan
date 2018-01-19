<?php
namespace Activity\Controller;
use Common\Controller\AdminbaseController;

class AdminMorningController extends AdminbaseController {

	protected $option_name = 'activity_morning';

    function index() {
        $where = array();
        $uid = I('uid', 0, 'intval');
        $sign_at = I('sign_at', '', 'trim');
        $status = I('status', -1, 'intval');
        $paystatus = I('paystatus', -1, 'intval');
        if($uid > 0) {
            $where['uid'] = $uid;
        } else {
            $uid = '';
        }
        if($sign_at) {
            $where['sign_at'] = $sign_at;
        }
        if($status > -1) {
            $where['success'] = $status;
        }
        if($paystatus > -1) {
            $where['pay_status'] = $paystatus;
        }

        $count = D('Activity/MorningSign')->where($where)->count();
        $page = $this->page($count, 20);
        $list = D('Activity/MorningSign')->where($where)->order('sign_at desc')->limit($page->firstRow . ',' . $page->listRows)->select();
        if($list) {
            $option = M('Options')->where(array('option_name' => $this->option_name))->find();
            if($option){
                $config = json_decode($option['option_value'], true);
            }
            $today = date('Y-m-d', TIMESTAMP);
            $endtime = strtotime($today.' '.$config['endtime'].':00');
            $starttime = strtotime($today.' '.$config['starttime'].':00');
            foreach($list as $key => $val) {
                $val['status'] = '-';
                $val['payment'] = '-';
                $val['pay_desc'] = '-';
                if($val['sign_at'] < $today || ($val['sign_at'] == $today && TIMESTAMP >= $endtime) || ($val['sign_at'] == $today && TIMESTAMP < $endtime && TIMESTAMP > $starttime && $val['success'])) {
                    $val['status'] = $val['success'] ? '成功' : '失败';
                    $val['getup_at'] = $val['success'] ? date('Y-m-d H:i:s', substr($val['getup_at'], 0, 10)) : '-';
                    if($val['success']) {
                        $val['amount'] = $val['amount'] / 100;
                        if($val['pay_status']) {
                            $val['payment'] = '支付成功';
                            $val['pay_desc'] = '微信支付交易单号:'.$val['payment_no'];
                        } else {
                            $val['payment'] = '支付失败';
                            $val['pay_desc'] = '原因:'.$val['err_code'].', '.$val['err_code_des'];
                        }
                    }
                } else {
					$val['payment_time'] = '-';
					$val['amount'] = '-';
					$val['days'] = '-';
					$val['getup_at'] = '-';
				}
                $user = M('Users')->where(array('id' => $val['uid']))->field('user_nicename,avatar')->find();
                $val['nicename'] = $user['user_nicename'];
                $val['avatar'] = empty($user['avatar']) ? '' : sp_get_user_avatar_url($user['avatar']);
                $list[$key] = $val;
            }
        }
        $this->assign('list', $list);
        $this->assign('status', $status);
        $this->assign('paystatus', $paystatus);
        $this->assign('sign_at', $sign_at);
        $this->assign('uid', $uid);
        $this->assign("Page", $page->show('Admin'));
        $this->display();
    }

    function stat() {
        $where = array();
        $count = D('Activity/MorningStat')->where($where)->count();
        $page = $this->page($count, 20);
        $list = D('Activity/MorningStat')->where($where)->order('sign_at desc')->limit($page->firstRow . ',' . $page->listRows)->select();
		if($list) {
            foreach($list as $key => $val) {
				$val['fail_num'] = $val['sign_num'] - $val['success_num'];
				$val['pay_account'] = $val['pay_account'] / 100;
                $list[$key] = $val;
			}
		}
        $this->assign('list', $list);
        $this->assign("Page", $page->show('Admin'));
        $this->display();
    }

    function config() {
		$options = array();
		$where = array('option_name' => $this->option_name);
    	$option = M('Options')->where($where)->find();
    	if($option){
    		$config = json_decode($option['option_value'], true);
    	} else {
            $config = array(
                'status' => 0,
                'starttime' => '5:00',
                'endtime' => '8:00',
                'success_sign_tip' => "挑战已启动！\r\n请于#datetime#早起打卡，完成挑战。",
                'need_sign_tip' => "明早的“早起挑战”可以报名参加了！\r\n\和小伙伴们一起早起，不辜负清晨的好时光🌞",
                'wake_sign_tip' => "快起来！打卡啦！\r\n离挑战结束还有#timeleft#, 请于#endtime#前完成早起打卡。",
                'ok_sign_tip' => "#datetime#完成早起打卡\r\n连续#days#挑战成功！你将豫其他成功者均分所有参战金。奖励会在24小时内发送至微信钱包。",
                'fail_sign_tip' => "#date#打卡挑战失败！\r\n当你和周公牵手，神游太虚时，#ok_num#挑战成功, 分走了全部#amount#元参战金...",
                'share_message' => '晨光是手中的金子',
                'share_backgroundimages' => array()
            );
            M('Options')->add(array('option_name' => $this->option_name, 'option_value' => json_encode($config), 'autoload' => 0));
        }
        if(IS_POST) {
            $config = array(
                'status' => I('status', 0, 'intval'),
                'starttime' => I('starttime', '', 'trim'),
                'endtime' => I('endtime', '', 'trim'),
                'success_sign_tip' => I('success_sign_tip', '', 'trim'),
                'need_sign_tip' => I('need_sign_tip', '', 'trim'),
                'wake_sign_tip' => I('wake_sign_tip', '', 'trim'),
                'ok_sign_tip' => I('ok_sign_tip', '', 'trim'),
                'fail_sign_tip' => I('fail_sign_tip', '', 'trim'),
                'share_message' => I('share_message', '', 'trim'),
                'share_backgroundimages' => array()
            );
            if(!empty($_POST['photos_url'])){
                foreach ($_POST['photos_url'] as $key=>$url){
                    $photourl=sp_asset_relative_url($url);
                    if($photourl) {
                        $config['share_backgroundimages'][] = $photourl;
                    }
                }
            }
            $start_arr = explode(':', $config['starttime']);
            if(count($start_arr) != 2) {
                $this->error('请填写正确格式的开始时间, 时间范围3:00~10:00');
            }
            if(substr($start_arr[1], 0, 1) === '0') {
                $start_arr[1] = substr($start_arr[1], 1);
            }
            if($start_arr[0] < 3 || $start_arr[0] > 10 || $start_arr[1] < 0 || $start_arr[1] > 59) {
                $this->error('请填写正确格式的开始时间, 时间范围3:00~10:00');
            }
            $end_arr = explode(':', $config['endtime']);
            if(count($end_arr) != 2) {
                $this->error('请填写正确格式的结束时间, 时间范围3:00~10:00');
            }
            if(substr($end_arr[1], 0, 1) === '0') {
                $end_arr[1] = substr($end_arr[1], 1);
            }
            if($end_arr[0] < 3 || $end_arr[0] > 10 || $end_arr[1] < 0 || $end_arr[1] > 59) {
                $this->error('请填写正确格式的结束时间, 时间范围3:00~10:00');
            }
            if($start_arr[0] > $end_arr[0] || ($start_arr[0] == $end_arr[0] && $start_arr[1] >= $end_arr[1])) {
                $this->error('开始时间不能晚于结束时间');
            }
            $config = json_encode($config);
            M('Options')->where(array('option_name' => $this->option_name))->save(array('option_value' => $config));
            $this->success('保存成功');
        } else {
            $this->assign('config', $config);
            $this->display();
        }
    }
}
