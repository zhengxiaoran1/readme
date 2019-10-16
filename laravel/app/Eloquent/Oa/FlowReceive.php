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

class FlowReceive extends Model
{
    use SoftDeletes;
    protected $table = 'oa_flow_receive';
    protected $dates = ['deleted_at'];
}
