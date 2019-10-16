<?php
/**
 * Created by PhpStorm.
 * Author: kaodou
 * Date: 2018/2/28
 * Time: 14:25
 */

namespace App\Engine;

use App\Eloquent\Ygt\Customer as CustomerEloquent;

class Customer
{
    public static function getAllLevel(){
        return [
            1=>[
                'id'=>'1',
                'title'=>'普通客户'
            ],
            2=>[
                'id'=>'2',
                'title'=>'重要客户'
            ],
            3=>[
                'id'=>'3',
                'title'=>'VIP客户'
            ],
        ];
    }

    public static function getLevelTitle($level){
        $levels = self::getAllLevel();
        return isset($levels[$level]['title'])?$levels[$level]['title']:'';
    }

    public static function getNameById($id){

        $tmpObj = \App\Eloquent\Ygt\Customer::withTrashed()->where(['id'=>$id])->first();
        $customerName = '';
        if($tmpObj){
            $customerName = $tmpObj->customer_name;
        }
        return $customerName;

//        return CustomerEloquent::getOneValueById($id,'customer_name');
    }

    public static function orderComplete($customerId)
    {
        $data = ['last_order_time'=>time()];
        return CustomerEloquent::where('id', $customerId)->update($data);
    }

    }