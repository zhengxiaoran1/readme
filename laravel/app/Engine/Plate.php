<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/29 0029
 * Time: 下午 4:40
 */

namespace App\Engine;

use App\Eloquent\Zk\Plate as PlateEloquent;

class Plate
{
    /**
     * @param $plateId 版ID
     * @param $finishNumber 完工数量
     * 订单完工更新板信息
     */
    public static function orderComplete($plateId,$finishNumber){

        $plateModel = new PlateEloquent();
        $plateInfo = $plateModel->getOneData(['id'=>$plateId]);
        $updateData = [
            'last_time'=>time(),
            'warning_time'=>time()+(86400*$plateInfo['warning_days']),
        ];
        PlateEloquent::where('id',$plateId)->increment('order_times');
        PlateEloquent::where('id',$plateId)->increment('finish_number', $finishNumber, $updateData);
        return true;
    }

    public static function getPlateInfo($plateId){
        return PlateEloquent::withTrashed()->where('id',$plateId)->first();
    }

    public static function getNameById($id){
        return PlateEloquent::getOneValueById($id,'plate_name');
    }

    /**
     * 生成版编号
     */
    public static function createPlateNo(){
        $info = PlateEloquent::withTrashed()->orderBy('id','desc')->first();
        $maxId = isset($info['id'])?$info['id']:0;
        $no = 'BAN'.str_pad(($maxId+1),6,"0",STR_PAD_LEFT );
        return $no;
    }

}