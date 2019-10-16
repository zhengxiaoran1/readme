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

class WorkflowCopy extends Model
{
    use SoftDeletes;
    protected $table = 'oa_workflow_copy';
    protected $dates = ['deleted_at'];
}