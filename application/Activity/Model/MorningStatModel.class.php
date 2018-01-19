<?php
namespace Activity\Model;

use Common\Model\CommonModel;

/**
 * 早起报名统计表
 */
class MorningStatModel extends CommonModel{
    protected $tableName = 'activity_morning_stat';

    function getByDate($date) {
        $result = $this->where(array('sign_date' => $date))->find();
        if(!$result) {
            $result = array('sign_at' => $date, 'sign_num' => 0, 'success_num' => 0, 'pay_account' => 0, 'create_at' => $date.' 00:00:00');
        }
        return $result;
    }
}
