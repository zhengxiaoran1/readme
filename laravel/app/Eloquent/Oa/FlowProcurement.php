<?php
/**
 * Created by PhpStorm.
 * User: LuLingFeng
 * Date: 2017/12/28
 * Time: 13:13
 */
namespace App\Eloquent\Oa;

use Framework\BaseClass\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FlowProcurement extends Model
{
    use SoftDeletes;
    protected $table = 'oa_flow_procurement';
    protected $dates = ['deleted_at'];
}