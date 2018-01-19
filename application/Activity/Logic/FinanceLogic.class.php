<?php

namespace Activity\Logic;
use EasyWeChat\Message\Text;
/**
 * 活动模块财务相关业务处理类
 */
class FinanceLogic {
		
    /**
     * 早起打卡
     */
    function morning($charge) {
    	if($charge['status'] == 1 && $charge['total'] == $charge['real_total']) {
    		$date = $charge['orderid'];
    		if(!D('Activity/MorningSign')->getByUidSignAt($charge['uid'], $date)) {
    			$yesterday = date('Y-m-d', strtotime($date) - 86400);
    			$yesterday_sign = D('Activity/MorningSign')->getByUidSignAt($charge['uid'], $yesterday);
    			$openid =  M('OauthUser')->where(array('uid' => $charge['uid'], 'status' => 1))->getField('openid');
    			$data = array('uid' => $charge['uid'], 'openid' => $openid, 'sign_at' => $date, 'sign_at_time' => date('Y-m-d H:i:s', TIMESTAMP), 'days' => 0);
    			if($yesterday_sign && $yesterday_sign['success']) {
                    $data['days'] = $yesterday_sign['days'];
                }
                if(D('Activity/MorningSign')->add($data)) {
                	$option = M('Options')->where(array('option_name' => 'activity_morning'))->find();
			        if($option) {
			            $config = json_decode($option['option_value'], true);
			        	$wechatmp = D("Wechat/WxMp")->where(array('type' => 3))->order('mpid desc')->find();
			        	if($wechatmp) {
                            $wechat = D('Wechat/Wechat', 'Logic')->init($wechatmp);
                            $fans = $wechat->getFans($openid);
                            if($fans) {
                                $content = $config['success_sign_tip'];
                                $datetime = date('n月j日', strtotime($date)).$config['starttime'].'-'.$config['endtime'];
                                $content = str_replace('#datetime#', $datetime, $content);
                                $url = UU('Activity/Morning/index', array(), true, true);
                                $content .= "\r\n<a href='{$url}'>➜ 去打卡</a>";
                                $message = new Text(['content' => $content]);
                                try {
                                    $wechat->getApi()->staff->message($message)->to($openid)->send();
                                } catch(\Excetion $e) {
                                    $log = '微信日志接口错误, File:'.$e->getFile().', Line: '.$e->getLine().', errno:'.$e->getCode().', err:'.$e->getMessage();
                                    \Think\Log::write($log, 'ERR');
                                }
                            }
			        	}
			        }
                }
    		}
    	}
    }
}
