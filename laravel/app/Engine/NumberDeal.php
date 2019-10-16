<?php
/**
 * Created by PhpStorm.
 * User: zhuyujun
 * Date: 2019/5/14
 * Time: 16:10
 * 数字相关的处理
 */

namespace App\Engine;

class NumberDeal
{
    //处理计算后，有多位小数的问题(最多保留2位小数)
    public static function filterDecimal($number){
        $deal_number = round($number*100);
        $deal_number = $deal_number/100;
        return $deal_number;
    }

    //保留2位小数
    public static function twoDecimal($number){
        $deal_number = sprintf("%.2f",$number);
        return $deal_number;
    }
}