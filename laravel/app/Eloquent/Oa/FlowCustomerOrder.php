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

class FlowCustomerOrder extends Model
{
    use SoftDeletes;
    protected $table = 'ygt_customer_order';
    protected $dates = ['deleted_at'];

    public function ChanpinOrder(){
        return $this->belongsTo('App\Eloquent\Ygt\ChanpinOrder', 'chanpin_order_id', 'id');
    }


}
