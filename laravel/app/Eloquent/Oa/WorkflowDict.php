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

class WorkflowDict extends Model
{
    use SoftDeletes;
    protected $table = 'oa_workflow_dict';
    protected $dates = ['deleted_at'];
}