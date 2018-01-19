<?php
namespace Activity\Model;

use Common\Model\CommonModel;

/**
 * 早起报名Model
 */
class MorningSignModel extends CommonModel{
    protected $tableName = 'activity_morning_sign';

    /**
     * 获取用户签到记录
     * @param integer 用户ID
     * @param date $sign_at 参加挑战日期
     * @return array|null
     */
    function getByUidSignAt($uid, $sign_at) {

        $where = array('uid' => $uid, 'sign_at' => $sign_at);
        return $this->where($where)->find();
    }
}
