<?php
/**
 * Created by PhpStorm.
 * User: wenwenbin
 * Date: 2018/1/6
 * Time: 11:01
 */

namespace App\Api\OA\Personnel\Controllers;

use Framework\BaseClass\Api\Controller;
use App\Api\OA\Personnel\Models\Personnel;

class PersonnelController extends Controller 
{
    /**
     * 获取部门列表
     * @author wenwenbin
     * @return \Illuminate\Support\Collection
     */
    public function departmentList()
    {
        $token = app('token')->checkToken();
        $department = new Personnel();
        return $departmentList = $department->getDepartmentList($token['oa_company_id']);
    }

    /**
     * 获取员工信息
     * @author wenwenbin
     * @return \Illuminate\Database\Eloquent\Model|null|static
     */
    public function employeeInfo()
    {
        $token = app('token')->checkToken();
        $employee = new Personnel();
        return $employee->getEmployeeInfo($token['oa_contacts_id']);
    }
    
    public function transferPostApply()
    {
        $token = app('token')->checkToken();
        $params = $this->getRequestParameters(['oa_contacts_id', 'company_id', 'department_id', 'post', 'reason', 'adjust_time']);
        $apply = new Personnel();
        $applyInfo = $apply->transferPostApply($params['oa_contacts_id'], $params['company_id'],
            $params['department_id'], $params['post'], $params['reason'], $params['adjust_time']);
        xAssert($applyInfo);
        return;
    }

    /**
     * 离职申请
     * @author wenwenbin
     */
    public function resignationApply()
    {
        $token = app('token')->checkToken();
        $apply = new Personnel();
        $applyInfo = $apply->resignationApply($token['oa_contacts_id'], $token['company_id']);
        xAssert($applyInfo);
        return;
    }
}