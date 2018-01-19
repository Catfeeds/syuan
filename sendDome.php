<?php
/**
 * Created by PhpStorm.
 * User: mayn
 * Date: 2017/12/21
 * Time: 15:46
 */

function sp_random_num($len = 12) {

    $chars = array(

        "0", "1", "2", "3", "4", "5", "6", "7", "8", "9"

    );

    $charsLen = count($chars) - 1;

    shuffle($chars);    // 将数组打乱

    $output = "";

    for ($i = 0; $i < $len; $i++) {

        $output .= $chars[mt_rand(0, $charsLen)];

    }


    return $output;

}

for($i=0;$i<100;$i++) {
    echo time().sp_random_num(10).'<br/>';
}