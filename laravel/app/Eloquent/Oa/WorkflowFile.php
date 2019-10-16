<?php
/**
 * Created by PhpStorm.
 * User: LuLingFeng
 * Date: 2018/1/9
 * Time: 15:29
 */
namespace App\Eloquent\Oa;

use Framework\BaseClass\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkflowFile extends Model
{
    use SoftDeletes;
    protected $table = 'oa_workflow_file';
    protected $dates = ['deleted_at'];
}