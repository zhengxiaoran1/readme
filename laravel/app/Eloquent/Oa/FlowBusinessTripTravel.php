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

class FlowBusinessTripTravel extends Model
{
    use SoftDeletes;
    protected $table = 'oa_flow_business_trip_travel';
    protected $dates = ['deleted_at'];
}
