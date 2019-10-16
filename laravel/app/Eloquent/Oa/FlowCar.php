<?php
/**
 * Created by PhpStorm.
 * @author wenwenbin
 * Date: 2017/12/22
 * Time: 10:32
 */
namespace App\Eloquent\Oa;

use Framework\BaseClass\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FlowCar extends Model
{
    use SoftDeletes;
    protected $table = 'oa_flow_car';
    protected $dates = ['deleted_at'];

    public function flowCarVehicleList()
    {
        return $this->hasMany('App\Eloquent\Oa\FlowCarVehicle', 'oa_flow_car_id', 'id');
    }

    public function departmentInfo()
    {
        return $this->belongsTo('App\Eloquent\Oa\Department', 'oa_department_id', 'id');
    }
}
