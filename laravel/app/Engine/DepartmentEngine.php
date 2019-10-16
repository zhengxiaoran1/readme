<?php
/**
 * Created by PhpStorm.
 * Author: kaodou
 * Date: 2018/3/15
 * Time: 17:13
 */

namespace App\Engine;

use App\Eloquent\Ygt\Department as ygtDepartment;
use App\Eloquent\Ygt\DepartmentUser as ygtDepartmentUser;

class DepartmentEngine
{
    public static function getDepartmentUserList($comanyId)
    {
        $where = ['company_id' => $comanyId];
        $fields = 'id,title';
        $departmentCollection = ygtDepartment::getList($where, $fields);
        $result = [];
        if (!$departmentCollection->isEmpty()) {
            $departmentList = $departmentCollection->toArray();
            $fields = 'id,user_id,department_id,truename,mobile';
            $userCollection = ygtDepartmentUser::getList($where, $fields);
            $userListArr = $userCollection->groupBy('department_id');
            foreach ($departmentList as $key => $val) {
                $tempArr = ['name' => $val['title']];
                $departmentId = $val['id'];
                if (isset($userListArr[$departmentId])) {
                    $tempDataList = $userListArr[$departmentId];
                    $tmpUserInfo = [];
                    foreach ($tempDataList as $tempData){
                        $tmpUserInfo[]=[
                            "id" => $tempData->user_id,
                            "oa_department_id" => $tempData->department_id,
                            "name" => $tempData->truename,
                            "mobile" => $tempData->mobile,
                            "position" => "员工",
                            "email" => "",
                        ];
                    }
                    $tempArr['contacts_list'] = $tmpUserInfo;
                    $result[] = $tempArr;
                }
            }
        }
        return $result;
    }
}