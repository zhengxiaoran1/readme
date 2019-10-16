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

class FlowReimburse extends Model
{
    use SoftDeletes;
    protected $table = 'oa_flow_reimburse';
    protected $dates = ['deleted_at'];

    public function reimburseItemList()
    {
        return $this->hasMany('App\Eloquent\Oa\FlowReimburseItem', 'oa_flow_reimburse_id', 'id');
    }

    public function fileList()
    {
        return $this->hasMany('App\Eloquent\Oa\FlowFile', 'oa_flow_master_id', 'id');
    }
}
