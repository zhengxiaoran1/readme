<?php
/**
 * Created by PhpStorm.
 * Author: kaodou
 * Date: 2018/3/15
 * Time: 17:13
 */

namespace App\Engine;

use App\Eloquent\Ygt\Buyers as BuyerEloquent;

class Buyers
{
    public static function getNameById($id){
        $tmpObj = \App\Eloquent\Ygt\Buyers::withTrashed()->where(['id'=>$id])->first();
        $buyerName = '';
        if($tmpObj){
            $buyerName = $tmpObj->buyer_name;
        }
        return $buyerName;

//        return BuyerEloquent::getOneValueById($id,'buyer_name');
    }
}