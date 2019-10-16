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

class FlowBusinessTrip extends Model
{
    use SoftDeletes;
    protected $table = 'oa_flow_business_trip';
    protected $dates = ['deleted_at'];

    public function travelList()
    {
        return $this->hasMany('App\Eloquent\Oa\FlowBusinessTripTravel', 'oa_flow_business_trip_id', 'id');
    }
}
