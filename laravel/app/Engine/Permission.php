<?php
/**
 * Created by PhpStorm.
 * User: jinhangtao
 * Date: 2017/11/28
 */

namespace App\Engine;
use App\Eloquent\Ygt\Permission as PermissionModel;
use App\Eloquent\Ygt\ProcessOrdertype as ProcessOrderTypeModel;
use App\Eloquent\Ygt\DepartmentUser as DepartmentUserModel;
use App\Eloquent\Ygt\FieldLimit;
use App\Eloquent\Ygt\Process as ygtProcess;
use App\Eloquent\Ygt\Privilege;
use App\Eloquent\Ygt\OrderFieldCompany;

class Permission
{
    public static function getPrivilege($nodeId,$typeId,$typeActionId){
        $result = PermissionModel::where(['node_id'=>$nodeId,'type_id'=>$typeId,'type_action'=>$typeActionId])->get();
        return $result;
    }

    public static function getPrivilegeIds($nodeId,$typeId,$typeActionId){
        $result = self::getPrivilege($nodeId,$typeId,$typeActionId);
        return $result->pluck('privilege_id');
    }

    /**
     * @param $typeAction 动作类型 1:查看;2:可写;3:分配;4:提交;5:转发;
     * @param $stepId
     * @return static
     * 获取工艺步骤 动作权限
     */
    public static function getStepActionPrivilegeIds($typeAction, $stepId){
        return self::getPrivilegeIds($stepId,1,$typeAction);
    }

    /**
     * @param $orderTypeId
     * @param $processId
     * @return Permission
     * 获取工艺步骤 经办权限 此方法要改成根据 步骤id来获取
     */
    public static function getStepSubmitActionPrivilegeIds($orderTypeId, $processId){
        return self::getStepActionPrivilegeIdsByOrderTypeProcess($orderTypeId, $processId, 4);
    }

    /**
     * @param $orderTypeId
     * @param $processId
     * @param $typeAction
     * 获取工艺步骤 动作权限的角色
     */
    public static function getStepActionPrivilegeIdsByOrderTypeProcess($orderTypeId, $processId, $typeAction){
        $processOrderTypeInfo = ProcessOrderTypeModel::where([['ordertype_id',$orderTypeId],['process_id',$processId]])->first();
        return self::getStepActionPrivilegeIds($typeAction,$processOrderTypeInfo['id']);
    }


    public static function getUserStepPrivilegeByProcess($userId, $processId, $orderTypeId){
        $stepInfo = ProcessOrderTypeModel::where([['ordertype_id',$orderTypeId],['process_id',$processId]])->first();
        $stepId = $stepInfo['id'];

        return self::getUserStepPrivilege($userId, $stepId);
    }

    /**
     * @param $userId
     * @param $stepId
     * @return \Illuminate\Support\Collection
     * 更具步骤id、用户id、获取这个用户此步骤的动作权限
     */
    public static function getUserStepPrivilege($userId,$stepId){
        $userInfo = DepartmentUserModel::getInfoByUserId($userId);
        $privilegeId = $userInfo['privilege_id'];
        $result = PermissionModel::where('type_id',1)->where('node_id',$stepId)->where('privilege_id',$privilegeId)->get()->pluck('type_action');

        return $result;
    }

    /**
     * @param $processOrdertypeId
     * @param $privilegeId
     * 获取工单工序中 查看权限 对应个角色的 字段
     */
    public static function getlookActionPrivilegeFields($processOrdertypeId, $privilegeId){
        return self::getLimitFields($processOrdertypeId, $privilegeId, 1);
    }

    public static function getLimitFields($processOrdertypeId, $privilegeId, $type = 1){
        $oldFields = FieldLimit::where('process_ordertype_id',$processOrdertypeId)->where('privilege_id',$privilegeId)->where('type',$type)->first();
        if($oldFields['limits'] !== null){
            $limits = explode(',',$oldFields['limits']);
        }else{
            $limits = [];
        }
        return $limits;
    }

    /**
     * @param $orderTypeId
     * @param $processId
     * @param $privilegeId
     * @return array
     * 获取查看权限的可见字段
     */
    public static function getlookActionPrivilegeFieldsByProcessOrderType($orderTypeId,$processId,$privilegeId){
        $processOrderTypeInfo = ProcessOrderTypeModel::where([['ordertype_id',$orderTypeId],['process_id',$processId]])->first();
        $fields = self::getlookActionPrivilegeFields($processOrderTypeInfo['id'], $privilegeId);

        $processFields = collect(ygtProcess::getFieldListByProcessId($processId));

        $result = $processFields->filter(function ($value) use($fields){
            return !in_array($value['id'],$fields);
        });

        return $result->toArray();
    }

    public static function getlookActionPrivilegeFieldsByProcessOrderTypeId($processOrderTypeId,$privilegeId){
        $processOrderTypeInfo = ProcessOrderTypeModel::where('id',$processOrderTypeId)->first();
        $fields = self::getlookActionPrivilegeFields($processOrderTypeId, $privilegeId);

        $processFields = collect(ygtProcess::getFieldListByProcessId($processOrderTypeInfo['process_id']));

        $result = $processFields->filter(function ($value) use($fields){
            return !in_array($value['id'],$fields);
        });

        return $result->toArray();
    }

    /**
     * @param $processOrderTypeId
     * @param $privilegeId
     * @return array
     * 获取查看权限基本信息字段
     */
    public static function getBasePrivilegeFieldsByProcessOrderType($processOrderTypeId,$privilegeId){
        $fields = self::getLimitFields($processOrderTypeId, $privilegeId, 2);

        $companyId = Privilege::where('id',$privilegeId)->first()->company_id;
        $baseFields         = collect(OrderFieldCompany::getActiveFieldList( $companyId ));

        $result = $baseFields->filter(function ($value) use($fields){
            return in_array($value['id'],$fields);
        });

        return $result->toArray();
    }

}