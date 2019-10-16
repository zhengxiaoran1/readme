<?php
/**
 * Created by PhpStorm.
 * User: LuLingFeng
 * Date: 2017/12/28
 * Time: 20:48
 */
namespace App\Eloquent\Oa;

use Framework\BaseClass\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FlowFile extends Model
{
    use SoftDeletes;
    protected $table = 'oa_flow_file';
    protected $dates = ['deleted_at'];
}