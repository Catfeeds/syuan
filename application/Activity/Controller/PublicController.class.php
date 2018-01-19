<?php
namespace Activity\Controller;
use Common\Controller\HomebaseController; 


class PublicController extends HomebaseController {
	
	public function index() {
    	$this->display(":index");
    }

    function morningshare() {
    	$uid = I('get.uid', 0, 'intval');
    	$sign_at = I('get.sign_at', '', 'trim');
    	if($uid < 1 || empty($sign_at)) {
    		$this->error('参数错误!');
    	}
    	$sign = D('Activity/MorningSign')->getByUidSignAt($uid, $sign_at);
    	if(!$sign) {
    		$this->error('今日为签到打卡!');
    	}
    	$where = array('sign_at' => $sign_at);
        $total = D('Activity/MorningSign')->where($where)->count();
        $where['success'] = 1;
        $where['getup_at'] = array('LT', $sign['getup_at']);
        $before = intval(D('Activity/MorningSign')->where($where)->count());
        $sign['percent'] = 100;
        if($before > 0) {
        	$percent = round((($before * 10000) / $total));
        	if($percent > 0) {
        		$percent = $percent / 100;
        	} else {
        		$percent = 99.99;
        	}
        	$sign['percent'] = 100 - $percent;
        }

        $wechatmp = D("Wechat/WxMp")->where(array('type' => 3))->order('mpid desc')->find();
        $wechat = D('Wechat/Wechat', 'Logic')->init($wechatmp);
        $qrcodeLogic = D('Wechat/Qrcode', 'Logic')->setContext($wechat);
        $qrcode = $qrcodeLogic->getRecommendQrcode($sign['openid']);
        if($qrcode) {
			$sign['qrcode'] = $qrcode['url'];
		}

        $background = '';
        $tip = '';
        $option = M('Options')->where(array('option_name' => 'activity_morning'))->find();
        if($option) {
            $config = json_decode($option['option_value'], true);
            if(isset($config['share_backgroundimages']) && is_array($config['share_backgroundimages']) && $config['share_backgroundimages']) {
                $background = $config['share_backgroundimages'][mt_rand(0, count($config['share_backgroundimages']) - 1)];
                $background = sp_get_asset_upload_path($background);
            }
            if(isset($config['share_message']) && $config['share_message']) {
                $config['share_message'] = explode("\r\n", $config['share_message']);
                $tip = $config['share_message'][mt_rand(0, count($config['share_message']) - 1)];
            }
        }

        $sign['name'] = $wechatmp['name'];
		$user = M('Users')->where(array('id' => $sign['uid']))->field('user_nicename,avatar')->find();
		$sign['avatar'] = empty($user['avatar']) ? '' : sp_get_user_avatar_url($user['avatar']);
    	$sign['getup_at'] = date('H:i', substr($sign['getup_at'], 0, 10));
    	$this->assign('sign', $sign);
    	$this->assign('background', $background);
        $this->assign('tip', $tip);
    	$this->display();
    }
}


