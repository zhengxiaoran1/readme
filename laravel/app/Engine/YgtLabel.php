<?php
/**
 * 工单类，提供与工单相关的各种方法
 * Created by PhpStorm.
 * User: zhuyujun
 * Date: 2017/10/18
 * Time: 14:25
 */

namespace App\Engine;


class YgtLabel
{
    public static $LABEL_WAIT_PURCHASE = 101;
    public static $LABEL_QUIT_PURCHASE = 102;

    public static function getLabel($labelType){
        $returnStr = "";
        switch($labelType) {
            case YgtLabel::$LABEL_WAIT_PURCHASE:
                $returnStr =  "单号:";
                break;
            case YgtLabel::$LABEL_QUIT_PURCHASE:
                $returnStr =  "单号:";
                break;
        }

        return $returnStr;
    }
}