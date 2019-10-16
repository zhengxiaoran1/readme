<?php
/**
 * Created by PhpStorm.
 * User: wenwenbin
 * Date: 2018/1/6
 * Time: 11:06
 */

namespace App\Api\OA\Personnel\Models;

use App\Eloquent\Oa\Contacts;
use App\Eloquent\Oa\Department;
use App\Eloquent\Oa\FlowResignation;
use App\Eloquent\Oa\FlowTransferPost;
use App\Eloquent\Oa\PersonnelFile;
use App\Eloquent\Oa\WorkHistory;
use Framework\BaseClass\Api\Model;

class Personnel extends Model
{
    /**
     * 获取没有子集的部门列表
     * @author wenwenbin
     * @param int $companyId 公司id
     * @return \Illuminate\Support\Collection
     */
    public function getDepartmentList($companyId)
    {
        return Department::where([['oa_company_id', $companyId], ['is_parent', 0]])->get(['id', 'name']);
    }

    /**
     * 获取岗位调动时的人员回显信息
     * @author wenwenbin
     * @param int $contactsId 联系人id
     * @return \Illuminate\Database\Eloquent\Model|null|static
     */
    public function getEmployeeInfo($contactsId)
    {
        $employee = Contacts::with('employeeNumberInfo', 'workHistoryList')->find($contactsId);
        if (is_null($employee)) xThrow(ERR_OA_CONTACTS_NOT_EXIST);
        if (is_null($employee->employeeNumberInfo)) xThrow(ERR_OA_EMPLOYEE_NUMBER_NOT_EXIST);
        $personnelFile = PersonnelFile::where([['oa_contacts_id', $employee->id], ['oa_company_id', $employee->oa_company_id]])
            ->with('companyInfo', 'departmentInfo')
            ->first(['oa_company_id', 'oa_department_id', 'oa_contacts_id', 'position', 'name', 'credential_number', 'created_at']);
        $personnelFile->company_name = $personnelFile->companyInfo->name;
        $personnelFile->department_name = $personnelFile->departmentInfo->name;
        $personnelFile->entry_time = date('Y-m-d', $personnelFile->created_at->timestamp);
        $personnelFile->employee_number = $employee->employeeNumberInfo ? $employee->employeeNumberInfo->employee_number : '';
        $time = 0;
        foreach ($employee->workHistoryList as $workHistory) {
            if ($workHistory->end_time > $workHistory->start_time) {
                $time += $workHistory->end_time - $workHistory->start_time;
            } else {
                $time += time() - $workHistory->start_time;
            }
        }
        $personnelFile->working_years = floor($time / 86400 / 365) . '年' . floor($time % (86400 * 365) / (30.42 * 86400)) . '月';
        unset($personnelFile->created_at, $personnelFile->oa_company_id, $personnelFile->oa_department_id,
            $personnelFile->companyInfo, $personnelFile->departmentInfo);
        return $personnelFile;
    }

    public function transferPostApply($oa_contacts_id, $company_id, $department_id, $post, $reason, $adjust_time)
    {
        $apply = new FlowTransferPost();
        $apply->fill([
            'oa_contacts_id' => $oa_contacts_id,
            'oa_company_id' => $company_id,
            'oa_department_id' => $department_id,
            'post' => $post,
            'reason' => $reason,
        ]);
        return $apply->save();
    }

    /**
     * 离职申请
     * @author wenwenbin
     * @param int $contactsId 通讯录id
     * @param int $companyId 公司id
     * @return bool
     */
    public function resignationApply($contactsId, $companyId)
    {
        $apply = new FlowResignation;
        $apply->fill([
            'oa_contacts_id' => $contactsId,
            'oa_company_id'  => $companyId
        ]);
        return $apply->save();
    }
}