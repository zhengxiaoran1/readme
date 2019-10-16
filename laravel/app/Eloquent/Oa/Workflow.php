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

class Workflow extends Model
{
    use SoftDeletes;
    protected $table = 'oa_workflow';
    protected $dates = ['deleted_at'];

    public function creatorInfo()
    {
        return $this->belongsTo('App\Eloquent\Oa\Contacts', 'creator_id', 'id');
    }

    public function workflowLogList()
    {
        return $this->hasMany('App\Eloquent\Oa\WorkflowLog', 'oa_workflow_id', 'id');
    }

    public function workflowCopyList()
    {
        return $this->hasMany('App\Eloquent\Oa\WorkflowCopy', 'oa_workflow_id', 'id');
    }

    public function documentFlowInfo()
    {
        return $this->hasOne('App\Eloquent\Oa\FlowDocument', 'id', 'related_id');
    }

    public function leaveFlowInfo()
    {
        return $this->hasOne('App\Eloquent\Oa\FlowLeave', 'id', 'related_id');
    }

    public function sealFlowInfo()
    {
        return $this->hasOne('App\Eloquent\Oa\FlowSeal', 'id', 'related_id');
    }

    public function carFlowInfo()
    {
        return $this->hasOne('App\Eloquent\Oa\FlowCar', 'id', 'related_id');
    }

    public function transferPostFlowInfo()
    {
        return $this->hasOne('App\Eloquent\Oa\FlowTransferPost', 'id', 'related_id');
    }

    public function reimburseFlowInfo()
    {
        return $this->hasOne('App\Eloquent\Oa\FlowReimburse', 'id', 'related_id');
    }

    public function goOutFlowInfo()
    {
        return $this->hasOne('App\Eloquent\Oa\FlowGoOut', 'id', 'related_id');
    }

    public function businessTripFlowInfo()
    {
        return $this->hasOne('App\Eloquent\Oa\FlowBusinessTrip', 'id', 'related_id');
    }

    public function daysOffFlowInfo()
    {
        return $this->hasOne('App\Eloquent\Oa\FlowDaysOff', 'id', 'related_id');
    }

    public function supplementFlowInfo()
    {
        return $this->hasOne('App\Eloquent\Oa\FlowSupplement', 'id', 'related_id');
    }

    public function flowFileList()
    {
        return $this->hasMany('App\Eloquent\Oa\FlowFile', 'oa_workflow_id', 'id');
    }

    //加班费
    public function overtimeWorkFeeFlowInfo()
    {
        return $this->hasOne('App\Eloquent\Oa\FlowOvertimeWorkFee', 'id', 'related_id');
    }

    //领用申请
    public function receiveApplyForInfo()
    {
        return $this->hasOne('App\Eloquent\Oa\FlowReceiveApplyFor', 'id', 'related_id');
    }

    //采购
    public function procurementFlowInfo()
    {
        return $this->hasOne('App\Eloquent\Oa\FlowProcurement', 'id', 'related_id');
    }
    // 合同
    public function contractFlowInfo()
    {
        return $this->hasOne('App\Eloquent\Oa\FlowContract', 'id', 'related_id');
    }
    //备用金
    public function pettyCashFlowInfo()
    {
        return $this->hasOne('App\Eloquent\Oa\FlowPettyCash', 'id', 'related_id');
    }
    // 制度方案
    public function systemSolutionsFlowInfo()
    {
        return $this->hasOne('App\Eloquent\Oa\FlowSystemSolutions', 'id', 'related_id');
    }
    //招聘需求
    public function recruitmentNeedsFlowInfo()
    {
        return $this->hasOne('App\Eloquent\Oa\FlowRecruitmentNeeds', 'id', 'related_id');
    }
    //奖罚
    public function rewardAndPunishFlowInfo()
    {
        return $this->hasOne('App\Eloquent\Oa\FlowRewardAndPunish', 'id', 'related_id');
    }
    //离职
    public function dimissionFlowInfo()
    {
        return $this->hasOne('App\Eloquent\Oa\FlowDimission', 'id', 'related_id');
    }
    //转账晋升调薪
    public function becomePromoteSalaryFlowInfo()
    {
        return $this->hasOne('App\Eloquent\Oa\FlowBecomePromoteSalary', 'id', 'related_id');
    }

    // 补签
    public function replenishSignFlowInfo()
    {
        return $this->hasOne('App\Eloquent\Oa\FlowReplenishSign', 'id', 'related_id');
    }

    // 面试
    public function interviewFlowInfo()
    {
        return $this->hasOne('App\Eloquent\Oa\FlowInterview', 'id', 'related_id');
    }

    // 入职
    public function takingWorkFlowInfo()
    {
        return $this->hasOne('App\Eloquent\Oa\FlowTakingWork', 'id', 'related_id');
    }
}