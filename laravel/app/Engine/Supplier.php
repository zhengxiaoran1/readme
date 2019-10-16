<?php
/**
 * Created by PhpStorm.
 * Author: kaodou
 * Date: 2018/3/8
 * Time: 11:18
 */

namespace App\Engine;

class Supplier
{
    public static function getAllLevel(){
        return [
            1=>[
                'id'=>'1',
                'title'=>'普通供应商'
            ],
            2=>[
                'id'=>'2',
                'title'=>'优质供应商'
            ]
        ];
    }

    public static function getLevelTitle($level){
        $levels = self::getAllLevel();
        return isset($levels[$level]['title'])?$levels[$level]['title']:'';
    }
}