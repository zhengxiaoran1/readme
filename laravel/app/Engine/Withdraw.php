<?php
/**
 * created by hjn
 * date: 2019/7/18 10:37
 */

namespace App\Engine;

use App\Eloquent\Zk\CustomerOrder;
use App\Eloquent\Zk\Order;
use App\Eloquent\Zk\ChanpinOrder;
use App\Eloquent\Zk\OrderProcess;
use App\Eloquent\Zk\OrderListRelation;
use App\Eloquent\Zk\WaitPurchase;
use App\Eloquent\Zk\WaitPurchaseMaterial;
use App\Eloquent\Zk\WaitePurchaseAggregate;
use App\Eloquent\Zk\OrderProcessCourse;
use App\Eloquent\Zk\OrderMaterialCourse;
use App\Eloquent\Zk\AssemblageMaterial;
use App\Eloquent\Zk\Product;
use App\Eloquent\Zk\OrderProcessProductReceive;
use App\Eloquent\Zk\ProcessProduct;
use App\Eloquent\Zk\ProcessProductWater;
use App\Eloquent\Zk\Stock;
use App\Eloquent\Zk\DepartmentUser;
use App\Api\Service\Storehouse\Storehouse\Storehouse;
use App\Eloquent\Zk\Process;
use App\Eloquent\Zk\UserMessage;
use App\Eloquent\Zk\StorehouseRes;

//订单、工单、生产单撤回
class Withdraw{

    /*
     * 生产订单撤回
     * */
    public static function productionSheetWithdraw($customerOrderId){
        $CustomerOrderObj = CustomerOrder::firstOrNew(['id'=>$customerOrderId]);
        $statusStrMsg = [
            4   =>  '生产中',
            5   =>  '已完工',
            6   =>  '已发货',
            7   =>  '已收讫'
        ];

        $productionSheetInfo = $CustomerOrderObj->toArray();
        if(!$productionSheetInfo) return ['code'=>'1','message'=>'当前生产单不存在'];
        if($productionSheetInfo['is_delete']) return ['code'=>'1','message'=>'当前生产单已撤回'];

        if($productionSheetInfo['status'] > 3 && $productionSheetInfo['status'] < 100)
            return ['code'=>'1','message'=>'生产单无法撤回，当前已处于'.$statusStrMsg[$productionSheetInfo['status']]];

        $CustomerOrderObj->fill(['deleted_at'=>time(),'is_delete'=>1]);
        if(!$CustomerOrderObj->save()) return ['code'=>'1','message'=>'生产单撤回出错，请重试!'];

        //返回生成单数据，方便后续操作
        return $productionSheetInfo;

    }

    //根据情况回退产品单中的操作数量
    public static function productBackNumber($param){

        $chanpinOrderObj = ChanpinOrder::firstOrNew(['id'=>$param['chanpinOrderId']]);
        if(!$chanpinOrderObj->toArray()) return ['code'=>'1','message'=>'数据不存在'];
        //TODO 预留对产品订单状态判断 比如：当前产品订单终止、撤回等

        $productionNumber = explode(',',$param['productionNumber']);
        $saveData['is_plan_number'] = $chanpinOrderObj->is_plan_number - $productionNumber[0];
        $saveData['no_plan_number'] = $chanpinOrderObj->number - $saveData['is_plan_number'];
        /*
         * 1 | 100 工单未生成无后续操作
         * 2、待派发，生成工单为派发
         * */
        switch ($param['productionSheetStatus']){
            case 2:
            case 3:
                $saveData['no_product_number'] = $chanpinOrderObj->no_product_number - $productionNumber[0];
                break;
        }
        $chanpinOrderObj->fill($saveData);
        if(!$chanpinOrderObj->save()) return ['code'=>'1','message'=>'产品订单数据撤回异常'];

        return false;
    }

    /*
     * 工单撤回
     * */
    public static function workSheetWithdraw($where,$status=0){

        $orderInfo = Order::where($where)->first();
        if(!$orderInfo) return ['code'=>'2','message'=>'工单不存在'];
        if(!in_array($orderInfo['status'],array(4,1,101))) return ['code'=>'2','message'=>'当前工单无法撤回'];
        if($status && $orderInfo['status'] == 4) return ['code'=>'2','message'=>'工单已撤回请勿重新操作'];

        $orderInfo = $orderInfo->toArray();
        $orderObj = Order::firstOrNew($where);
        $updateData = $status ? ['status'=>4] : ['deleted_at'=>time(),'is_delete'=>1];

        $orderObj->fill($updateData);
        if(!$orderObj->save()) return ['code'=>'1','message'=>'工单撤回失败'];

        return $orderInfo;

    }

    /*
     * 子工单撤回
     * */
    public static function seedWorkSheetWithdraw($workSheetInfo,$status=0){

        $where['order_id'] = $workSheetInfo['id'];
        $seedWorkSheetData = OrderProcess::where($where)->get();
        $user_id = \App\Engine\Func::getHeaderValueByName('userid');
        $orderTitle = \App\Engine\Common::changeSnCode($workSheetInfo['order_title']);//获取工单号
        //工单工序撤回
        $saveData = $status ? ['uid'=>"",'status'=>1] : ['deleted_at'=>time(),'is_delete'=>1];
        foreach ($seedWorkSheetData as $seedWorkSheet){
            if(!in_array($seedWorkSheet['status'],[101,1,2])) return ['code'=>'1','message'=>'当前订单工序已开工无法撤回'];
            $seedWorkSheet->fill($saveData);
            if(!$seedWorkSheet->save()) return ['code'=>'1','message'=>'子工单撤回失败'];
            $processTitle[$seedWorkSheet['id']] = Process::getOneValueById($seedWorkSheet['process_type'], 'title');
        }


        //员工工单撤回
        $OrderProcessCourseData = OrderProcessCourse::whereIn('order_process_id',array_column($seedWorkSheetData->toArray(),'id'))->get();
        foreach ($OrderProcessCourseData as $OrderProcessCourse){
            $OrderProcessCourse->fill(['deleted_at'=>time(),'is_delete'=>1]);
            if(!$OrderProcessCourse->save()) return ['code'=>'1','message'=>'员工工单撤回失败，请重试'];
            $processTitle[$OrderProcessCourse['id']] = $processTitle[$OrderProcessCourse['order_process_id']];
        }

        //工单关联数据列表
        if($status){
            //工单撤回至待派发状态特殊处理
            if(!OrderListRelation::where(['type'=>1,'relate_id'=>$workSheetInfo['id']])->update(['status'=>4]))
                return ['code'=>'1','message'=>'工单工序撤回失败，请重试'];
        }

        $orderId = $workSheetInfo['id'];
        $orderProcessId = array_column($seedWorkSheetData->toArray(),'id');
        $orderProcessCourseId = array_column($OrderProcessCourseData->toArray(),'id');

        $OrderListRelationRow = OrderListRelation::where(function($query) use ($orderId,$orderProcessId,$orderProcessCourseId,$status){
            if($status){
                $query->where(function($query) use ($orderProcessId){
                    $query->whereIn('relate_id',$orderProcessId)->whereIn('type',[2,3]);
                })->orWhere(function($query) use ($orderProcessCourseId){
                    $query->where(['type'=>4])->whereIn('relate_id',$orderProcessCourseId);
                });
            }else{
                $query->where(['type'=>1,'relate_id'=>$orderId])->orWhere(function($query)use ($orderProcessId,$orderProcessCourseId){
                    $query->where(function($query) use ($orderProcessId){
                        $query->whereIn('relate_id',$orderProcessId)->whereIn('type',[2,3]);
                    })->orWhere(function($query) use ($orderProcessCourseId){
                        $query->where(['type'=>4])->whereIn('relate_id',$orderProcessCourseId);
                    });
                });
            }
        })->get();

        foreach ($OrderListRelationRow as $OrderListRelation){
            $OrderListRelation->fill(['deleted_at'=>time(),'is_delete'=>1]);
            if(!$OrderListRelation->save()) return ['code'=>'1','message'=>'工单工序关联撤回失败，请重试'];
            if($OrderListRelation['type'] != 1){
                $message_content = $orderTitle . "rnrn" . $processTitle[$OrderListRelation['relate_id']] . ( $OrderListRelation['type'] == 4 ? "工序工单-已撤回" : "员工工单-已撤回" );
                $titleTheme = $OrderListRelation['type'] == 4 ? "工序工单-已撤回" : "员工工单-已撤回";

                $data = [
                    'company_id' => $workSheetInfo['company_id'],
                    'privilege_id' => '',
                    'form_user_id' => $user_id,
                    'to_user_id' => $OrderListRelation['uid'],
                    'foreign_key' => $workSheetInfo['customer_order_id'],
                    'type' => 35,//只做展示不做跳转用
                    'type_id' => $OrderListRelation['id'],
                    'title' => $titleTheme,
                    'content' => $message_content,
                    'theme' => $titleTheme,
                ];

                if(!UserMessage::sendCustomerOrderMessage($data))
                    return ['code'=>'1','message'=>'小秘书发送消息失败'];
            }


        }

        return $seedWorkSheetData;
    }

    //待采购中需采购数量撤回
    public static function purchaseBack($orderInfo){

        if(!$orderInfo) return ['code'=>'1','message'=>'代采购数量撤回异常'];
        $orderId = $orderInfo['id'];
        $user_id = \App\Engine\Func::getHeaderValueByName('userid');
        $orderTitle = \App\Engine\Common::changeSnCode($orderInfo['order_title']);//获取工单号

        $WaitPurchaseData = WaitPurchase::firstOrNew(['order_id'=>$orderId]);

        //需求不明确暂时注释，后续需要时开启
        if($WaitPurchaseData->status == 2) return ['code'=>'1','message'=>'待采购单已完成采购无法撤回'];

        $WaitPurchaseMaterialData = WaitPurchaseMaterial::where(['wait_purchase_id'=>$WaitPurchaseData->id])->get()->toArray();
        foreach ($WaitPurchaseMaterialData as $v){
            $where['material_id']    = ltrim($v['material_id'],"A");
            $where['type'] = strpos($v['material_id'],'A') !== false?2:1;

            $purchaseDb = WaitePurchaseAggregate::firstOrNew($where);

            //防止数据出现负数
            $save['all_number'] = $purchaseDb->all_number - $v['number'] > 0 ? $purchaseDb->all_number - $v['number'] : 0;//需采购总量
            $save['now_number'] = $purchaseDb->now_number - $v['number'] > 0 ? $purchaseDb->now_number - $v['number'] : 0;//需采购剩余总量

            //TODO 此处预留对已采购的采购数量处理

            $purchaseDb->fill($save);
            if(!$purchaseDb->save()) return ['code'=>'1','message'=>'待采购单撤回失败，请重试'];

            //代采购材料单撤回
            $WaitPurchaseMaterialDb = WaitPurchaseMaterial::firstOrNew(['id'=>$v['id']]);
            $WaitPurchaseMaterialDb->fill(['deleted_at'=>time()]);
            if(!$WaitPurchaseMaterialDb->save()) return ['code'=>'1','message'=>'代采购材料单撤回失败，请重试'];

        }

        $WaitPurchaseData->fill(['deleted_at'=>time()]);
        if(!$WaitPurchaseData->save()) return ['code'=>'1','message'=>'待采购单撤回失败，请重试'];

        $privilegeList = \App\Engine\OrderEngine::getPrivilegeByNode($orderInfo['company_id'], 10);
        $message_content = $orderTitle."rnrn";
        $message_content .= "工单-已撤回，待采购材料取消";

        //未派发不发送撤回消息

        if($orderInfo['status'] == 4) return false;
        $data = [
            'company_id' => $orderInfo['company_id'],
            'form_user_id' => $user_id,
            'to_user_id' => '',
            'foreign_key' => $orderInfo['customer_order_id'],
            'type' => 35,//只做展示不做跳转用
            'type_id' => $orderId,
            'title' => '工单撤回',
            'content' => $message_content,
            'theme' => '工单撤回',
        ];
        foreach ($privilegeList as $privilegeId){
            $data['privilege_id'] =  $privilegeId;
            if(!DepartmentUser::where(['company_id'=>$orderInfo['company_id'],'privilege_id'=>$privilegeId])->get()) continue;
            if(!UserMessage::sendCustomerOrderMessage($data))
                return ['code'=>'1','message'=>'待采购单撤回小秘书发送失败'];
        }



        return false;

    }

    //材料撤回
    public static function materialBackNumber($seedWorkSheetInfo,$orderInfo){

        //ygt_order_material_course 材料领取汇总表
        //ygt_stock 材料流水表
        //ygt_product 材料表
        //ygt_assemblage_material 集合材料
        //ygt_storehouse_res 仓库

        $companyId = $orderInfo['company_id'];
        $user_id = \App\Engine\Func::getHeaderValueByName('userid');
        $orderTitle = \App\Engine\Common::changeSnCode($orderInfo['order_title']);//获取工单号

        $OrderProcessIds = array_column($seedWorkSheetInfo->toArray(),'id');
        $storehouseId = Storehouse::getCompanyDefaultStorehouse($companyId)->getId();

        $OrderMaterialCourseData = OrderMaterialCourse::whereIn('order_process_id',$OrderProcessIds)->get()->toArray();
        if(!$OrderMaterialCourseData) return false;
        $additional = 0;
        foreach ($OrderMaterialCourseData as $v){

            if(!$v['receive_number']) continue;

            //TODO 预留集合材料数量处理业务判断

            $ProductDb = Product::firstOrNew(['id'=>$v['material_id']]);
            $receiveNumber = $v['receive_number'];
            //增加材料撤回记录
            $Stock[] = [
                'storehouse_id'         =>  isset($storehouseId) ? $storehouseId : '',//仓库ID
                'relate_type'           =>  2,
                'water_no'              =>  \App\Engine\Stock::createWaterNo($additional),
                'number'                =>  $v['receive_number'],
                'product_id'            =>  $v['material_id'],
                'last_product_number'   =>  $ProductDb->number,
                'company_id'            =>  $companyId,
                'type'                  =>  1,//入库
                'operate_uid'           =>  $user_id,
                'created_at'            =>  time(),
            ];
            $additional++;
            $ProductDb->number     = $ProductDb->number + $receiveNumber;
            if(!$ProductDb->save()) return ['code'=>'1','message'=>'材料撤回失败，请重试！'];

            //同步修改集合材料数量，app端列表显示问题
            $AssemblageMaterialDb = AssemblageMaterial::firstOrNew(['id'=>$ProductDb->assemblage_material_id]);
            $AssemblageMaterialDb->number = $AssemblageMaterialDb->number + $receiveNumber;
            if(!$AssemblageMaterialDb->save()) return ['code'=>'1','message'=>'材料撤回失败，请重试！'];

            $productId = $ProductDb->id;
            $assemblageMaterialId = $ProductDb->assemblage_material_id;
            //修改仓库相关数量
            $StorehouseResDb = StorehouseRes::whereIn('res_id',[$productId,$assemblageMaterialId])->where(['storehouse_id'=>$storehouseId,'res_type'=>1])->get();
            foreach ($StorehouseResDb as $StorehouseRes){
                $StorehouseRes->out_number = $StorehouseRes->out_number - $receiveNumber;
                $StorehouseRes->number = $StorehouseRes->number + $receiveNumber;
                if(!$StorehouseRes->save()) return ['code'=>'1','message'=>'仓库材料撤回失败，请重试！'];
            }

        }
        //领取材料记录作废以防重复撤回数量
        if(!OrderMaterialCourse::whereIn('order_process_id',$OrderProcessIds)->update(['deleted_at'=>time()]))
            return ['code'=>'1','message'=>'领取材料记录撤回失败，请重试'];

        //添加材料退回记录
        if(isset($Stock)){
            if(!Stock::insert($Stock)) return ['code'=>'1','message'=>'材料退回流水记录失败'];
        }

        $privilegeList = \App\Engine\OrderEngine::getPrivilegeByNode($companyId, 7);
        $message_content = $orderTitle."rnrn";
        $message_content .= "工单-已撤回，已领取材料退回";

        $data = [
            'company_id' => $orderInfo['company_id'],
            'form_user_id' => $user_id,
            'to_user_id' => "",
            'foreign_key' => $orderInfo['customer_order_id'],
            'type' => 35,//只做展示不做跳转用
            'type_id' => $orderInfo['id'],
            'title' => '工单撤回-用料退回',
            'content' => $message_content,
            'theme' => '工单撤回-用料退回',
        ];
        foreach ($privilegeList as $privilegeId){
            $data['privilege_id'] =  $privilegeId;
            if(!UserMessage::sendCustomerOrderMessage($data))
                return ['code'=>'1','message'=>'用料退回小秘书发送失败'];
        }


        return false;
    }

    //半成品数量撤回
    public static function semiFinishedBackNumber($seedWorkSheetInfo,$orderInfo){

        //ygt_order_process_product_receive 领取记录半成品表
        //ygt_process_product 半成品表
        //ygt_process_product_water 半成品流水表
        //ygt_storehouse_res 仓库数量

        $companyId = $orderInfo['company_id'];
        $userId = \App\Engine\Func::getHeaderValueByName('userid');
        $orderTitle = \App\Engine\Common::changeSnCode($orderInfo['order_title']);//获取工单号
        $privilegeId = DepartmentUser::getCurrentInfo($userId)->value('privilege_id');
        $storehouseId = Storehouse::getCompanyDefaultStorehouse($companyId)->getId();
        $OrderProcessIds = array_column($seedWorkSheetInfo->toArray(),'id');

        $OrderMaterialCourseData = OrderProcessProductReceive::whereIn('order_process_id',$OrderProcessIds)->get()->toArray();
        if(!$OrderMaterialCourseData) return false;
        $additional = 0;
        foreach ($OrderMaterialCourseData as $v){

            if(!$v['number']) continue;

            $ProcessProductDb = ProcessProduct::firstOrNew(['id'=>$v['process_product_id']]);

            //回退数量
            // 'out_number'    =>  $ProcessProductDb->out_number - $v['number'], 总出库量处理预留  原有老流程未做处理
            $saveData = [
                'number'        =>  $ProcessProductDb->number + $v['number'],
            ];

            //增加半成品撤回流水记录
            $ProcessProductWaterData[] = [
                'process_product_id'    =>  $v['process_product_id'],
                'uid'                   =>  $userId,
                'sn'                    =>  \App\Engine\Sn::createProcessProductWaterSn($additional),
                'number'                =>  $v['number'],
                'unit'                  =>  $ProcessProductDb->unit,
                'residual_number'       =>  $saveData['number'],
                'company_id'            =>  $companyId,
                'privilege_id'          =>  $privilegeId,
                'type'                  =>  3,
                'storehouse_id'         =>  $storehouseId,
                'created_at'            =>  time(),
            ];
            $additional++;
            $ProcessProductDb->fill($saveData);
            if(!$ProcessProductDb->save()) return ['code'=>'1','message'=>'半成品撤回数量失败'];

            //仓库数量回退
            $StorehouseResDb = StorehouseRes::where(['res_type'=>2,'res_id'=>$v['process_product_id'],'storehouse_id'=>$storehouseId])->get();
            foreach ($StorehouseResDb as $StorehouseRes){
                $StorehouseRes->out_number  = $StorehouseRes->out_number - $v['number'];
                $StorehouseRes->number      = $StorehouseRes->number + $v['number'];
                if(!$StorehouseRes->save()) return ['code'=>'1','message'=>'仓库材料撤回失败，请重试！'];
            }

        }
        if(!OrderProcessProductReceive::whereIn('order_process_id',$OrderProcessIds)->update(['deleted_at'=>time()]))
            return ['code'=>'1','message'=>'领取记录半成品作废失败'];

        //添加半成品退回记录
        if(isset($ProcessProductWaterData) && $ProcessProductWaterData){
            if(!ProcessProductWater::insert($ProcessProductWaterData))
                return ['code'=>'1','message'=>'半成品退回流水添加失败'];
        }

        $privilegeList = \App\Engine\OrderEngine::getPrivilegeByNode($companyId, 7);
        $message_content = $orderTitle."rnrn";
        $message_content .= "工单-已撤回，已领取半成品材料退回";

        $data = [
            'company_id' => $orderInfo['company_id'],
            'form_user_id' => $userId,
            'to_user_id' => "",
            'foreign_key' => $orderInfo['customer_order_id'],
            'type' => 35,//只做展示不做跳转用
            'type_id' => $orderInfo['id'],
            'title' => '工单撤回',
            'content' => $message_content,
            'theme' => '工单撤回',
        ];

        foreach ($privilegeList as $privilegeId){
            $data['privilege_id'] =  $privilegeId;
            if(!DepartmentUser::where(['company_id'=>$orderInfo['company_id'],'privilege_id'=>$privilegeId])->get()) continue;
            if(!UserMessage::sendCustomerOrderMessage($data))
                return ['code'=>'1','message'=>'用料退回小秘书发送失败'];
        }
        return false;

    }

}