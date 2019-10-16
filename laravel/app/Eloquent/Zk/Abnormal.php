<?php
/**
 * Created by PhpStorm.
 * User: huangjiangnan
 * Date: 2019/8/8
 * Time: 14:10
 */

namespace App\Eloquent\Zk;
use Illuminate\Database\Eloquent\SoftDeletes;

class Abnormal extends DbEloquent
{

    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'ygt_abnormal';


    public function AbnormalUser(){
        return $this->hasMany('App\Eloquent\Ygt\AbnormalUser', 'abnormal_id', 'id');
    }
    public function AbnormalType(){
        return $this->hasMany('App\Eloquent\Ygt\AbnormalType', 'id', 'abnormal_type_id');
    }


}