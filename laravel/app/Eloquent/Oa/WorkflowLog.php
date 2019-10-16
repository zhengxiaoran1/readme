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

class WorkflowLog extends Model
{
    use SoftDeletes;
    protected $table = 'oa_workflow_log';
    protected $dates = ['deleted_at'];

    public function operatorInfo()
    {
        return $this->belongsTo('App\Eloquent\Oa\Contacts', 'operator_id', 'id');
    }

    public function assigneeInfo()
    {
        return $this->belongsTo('App\Eloquent\Oa\Contacts', 'assignee_id', 'id');
    }

    public function fileList()
    {
        return $this->hasMany('App\Eloquent\Oa\WorkflowFile', 'oa_workflow_log_id', 'id');
    }
}