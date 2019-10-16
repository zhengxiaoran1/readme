<?php
/**
 * Created by PhpStorm.
 * User: huangjiangnan
 * Date: 2019/8/9
 * Time: 15:03
 */

namespace App\Http\Admin\Abnormal\Models;

use Framework\BaseClass\Http\Admin\Model;
use App\Eloquent\Ygt\Abnormal;
use App\Eloquent\Ygt\AbnormalType;
use App\Eloquent\Ygt\AbnormalRultParameter;
use App\Eloquent\Ygt\Department;

class Models extends Model
{


    public static function getDdepartmentList($where){
        return Department::where($where)->get();
    }

    public static function getAbnormalRultParameter($where){
        return AbnormalRultParameter::where($where)->get();
    }

    public static function delAbnormal($where){
        if(!Abnormal::where($where)->first()) return true;
        return Abnormal::where($where)->delete() ? true : false ;
    }

    public static function updateAbnormal($where,$data){
        return Abnormal::where($where)->update($data)?true:false;
    }

    public static function getAbnormalList($where){

        return Abnormal::with(['AbnormalType'])->where($where)->get();

    }

    public static function getAbnormalType(){
        return AbnormalType::get();
    }

    public static function insAbnormal($data){
        return Abnormal::insert($data)?true:false;
    }

}