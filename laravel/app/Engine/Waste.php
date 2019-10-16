<?php
/**
 * 工单类，提供与工单相关的各种方法
 * Created by PhpStorm.
 * User: zhuyujun
 * Date: 2017/10/18
 * Time: 14:25
 */

namespace App\Engine;

class Waste
{

    //获取废品处理后的信息
    public static function getWasteName($wasteId){
        $wasteRow = \App\Eloquent\Ygt\Waste::where(['id'=>$wasteId])->first();
        if(!$wasteRow){
            return false;
        }

        if($wasteRow['relate_type'] == 1){//材料
            $materialRow = \App\Eloquent\Ygt\Product::where(['id'=>$wasteRow['relate_id']])->first();
            if(!$materialRow){
                return false;
            }
            return $materialRow['product_name'];

        }elseif($wasteRow['relate_type'] == 2){//工序废品
            $ordertypeProcessWasteRow = \App\Eloquent\Ygt\OrdertypeProcessWaste::where(['id'=>$wasteRow['relate_id']])->first();
            if(!$ordertypeProcessWasteRow){
                return false;
            }
            return $ordertypeProcessWasteRow['title'];
        }

        return false;
    }


}