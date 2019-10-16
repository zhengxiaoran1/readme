<?php
/**
 * Created by PhpStorm.
 * Author: kaodou
 * Date: 2018/3/8
 * Time: 11:18
 */

namespace App\Engine;

use App\Api\OA\Workflow\Models\FlowConfig;

class WorkFlow
{
    //采购申请按钮配置
    public static function getPurchaseButtonConfig(){
        $purchaseButtonConfig = [
            '1' => '通过',
            '2' => '老板通过',
            '3' => '进行采购',
            '4' => '进行入库',
        ];

    }

    //采购申请
    public static function getPurchaseButtonByProcessId($oaFlowConfigProcessId){
        $flowConfigProcessButtonObj = \App\Eloquent\Oa\FlowConfigProcessButton::where(['oa_flow_config_process_id'=>$oaFlowConfigProcessId])->first();
        if($flowConfigProcessButtonObj){
            return $flowConfigProcessButtonObj->button_value;
        }
        return '';
    }

    //退货申请
    public static function getReturnPurchaseButtonByProcessId($oaFlowConfigProcessId){
        $flowConfigProcessButtonObj = \App\Eloquent\Oa\FlowConfigProcessButton::where(['oa_flow_config_process_id'=>$oaFlowConfigProcessId])->first();
        if($flowConfigProcessButtonObj){
            return $flowConfigProcessButtonObj->button_value;
        }
        return '';
    }

    //如果企业未配置相关申请流程，自动创建
    public static function companyWorkFlowCreate($companyId){
        //采购申请流程
        $flowId = 26;
        $flowName = '采购申请';

        if(!\App\Eloquent\Oa\FlowConfig::where(['oa_company_id'=>$companyId,'oa_flow_id'=>$flowId])->first()){
            $flowConfigObj = \App\Eloquent\Oa\FlowConfig::firstOrNew(['id'=>0]);
            $createRow = [
                'oa_flow_id' => $flowId,
                'oa_company_id' => $companyId,
                'name' => $flowName,
                'description' => $flowName,
                'is_use' => 1,
                'display' => 1,
            ];
            $flowConfigObj->fill($createRow);
            $flowConfigObj->save();
        }

        //退货申请流程
        $flowId = 27;
        $flowName = '退货申请';

        if(!\App\Eloquent\Oa\FlowConfig::where(['oa_company_id'=>$companyId,'oa_flow_id'=>$flowId])->first()){
            $flowConfigObj = \App\Eloquent\Oa\FlowConfig::firstOrNew(['id'=>0]);
            $createRow = [
                'oa_flow_id' => $flowId,
                'oa_company_id' => $companyId,
                'name' => $flowName,
                'description' => $flowName,
                'is_use' => 1,
                'display' => 1,
            ];
            $flowConfigObj->fill($createRow);
            $flowConfigObj->save();
        }

    }

}