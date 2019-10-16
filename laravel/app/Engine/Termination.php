<?php
/**
 * created by hjn
 * date: 2019/7/18 10:37
 */

namespace App\Engine;

use App\Eloquent\Ygt\WarehouseBill;
use App\Eloquent\Ygt\WarehouseSend;
use App\Eloquent\Ygt\WarehouseOut;
use App\Eloquent\Ygt\Order;
use App\Eloquent\Ygt\ChanpinOrder;
use App\Eloquent\Ygt\OrderListRelation;
use App\Eloquent\Ygt\OrderProcess;
use App\Eloquent\Ygt\OrderProcessCourse;
use App\Eloquent\Ygt\Process;
use App\Eloquent\Ygt\UserMessage;
use App\Eloquent\Ygt\CustomerOrder;

//终止
class Termination{
    /*
     * 订单终止
     * */
    public static function orderTermination($orderId){

        $ChanpinOrderDb = ChanpinOrder::where(['id'=>$orderId])->first();
        if($ChanpinOrderDb->status == 5) return ['code'=>'1','message'=>'订单已终止，无需重复操作'];

        if(CustomerOrder::where(['chanpin_order_id'=>$orderId])->count())
            return ['code'=>'1','message'=>'当前订单已转生产单无法终止'];

        $ChanpinOrderDb->status = 5;
        if(!$ChanpinOrderDb->save()) return ['code'=>'1','message'=>'操作失败，请重试'];

        return false;
    }


    /*
     * 工单终止
     * */
    public static function workSheet($orderId){

        $status = 21;//终止状态码

        $orderInfo = Order::find($orderId);
        if($orderInfo->status == $status) return ['code'=>'1','message'=>'工单已终止'];
        if($orderInfo->status == 3) return ['code'=>'1','message'=>'工单已开工，无法终止'];

        $orderInfo->status = $status;
        if(!$orderInfo->save()) return ['code'=>'1','message'=>'工单终止失败'];

        $OrderListRelationInfo = OrderListRelation::where(['relate_id' =>  $orderId,'type' =>  1,])->first();
        $OrderListRelationInfo->status = $status;
        if(!$OrderListRelationInfo->save()) return ['code'=>'1','message'=>'工单关联数据终止失败'];

        return $orderInfo;
    }

    /*
     * 工序工单终止【预留对单个工序工单进行终止动作(暂无需求)】
     * */
    public static function seedWorkSheet($orderInfo){

        $status = 21;//终止状态码
        $user_id = \App\Engine\Func::getHeaderValueByName('userid');
        $orderTitle = \App\Engine\Common::changeSnCode($orderInfo['order_title']);//获取工单号

        $OrderProcessData = OrderProcess::where(['order_id'=>$orderInfo['id']])->whereNotNull('uid')->get();
        $orderProcessId = array_column($OrderProcessData->toArray(),'id');

        $orderProcessCourseData = OrderProcessCourse::whereIn('order_process_id',$orderProcessId)->get();
        $orderProcessCourseId = array_column($orderProcessCourseData->toArray(),'id');

        $orderListRelationList = OrderListRelation::where(function($query) use ($orderProcessId){
                                    $query->whereIn('relate_id',$orderProcessId)->whereIn('type',[2,3]);
                                })->orWhere(function($query) use ($orderProcessCourseId){
                                    $query->where(['type'=>4])->whereIn('relate_id',$orderProcessCourseId);
                                })->get();

        foreach ($OrderProcessData as $DbObj){
            $DbObj->status = $status;
            if(!$DbObj->save()) return ['code'=>'1','message'=>'工序工单终止失败'];
            $process_title[$DbObj['id']] = Process::getOneValueById($DbObj['process_type'], 'title');
        }

        foreach ($orderProcessCourseData as $dbObj){
            $dbObj->status = $status;
            if(!$dbObj->save()) return ['code'=>'1','message'=>'员工工单终止失败'];
            $process_title[$dbObj['id']] = $process_title[$dbObj['order_process_id']];
        }

        foreach ($orderListRelationList as $DbObj){
            $DbObj->status = $status;
            if(!$DbObj->save()) return ['code'=>'1','message'=>'员工工单关联数据终止失败'];

            $message_content = $orderTitle . "rnrn" . $process_title[$DbObj['relate_id']] . ( $DbObj['type'] == 4 ? "员工工单-已终止" : "工单-已终止" );
            $titleTheme = $DbObj['type'] == 4 ? "员工工单终止" : "工序工单终止";

            $data = [
                'company_id' => $orderInfo['company_id'],
                'privilege_id' => '',
                'form_user_id' => $user_id,
                'to_user_id' => $DbObj['uid'],
                'foreign_key' => $orderInfo['customer_order_id'],
                'type' => 35,//只做展示不做跳转用
                'type_id' => $DbObj['id'],
                'title' => $titleTheme,
                'content' => $message_content,
                'theme' => $titleTheme,
            ];
            if(!UserMessage::sendCustomerOrderMessage($data))
                return ['code'=>'1','message'=>'小秘书发送消息失败'];

        }
        return false;

    }

    /*
     * 交货单终止
     * */
    public static function deliveryOrder($param){

        $where['id'] = $param;
        $warehouseBillData = WarehouseBill::getInfo($where);

        $checkStatus = self::checkOrderStatus($warehouseBillData);
        if($checkStatus) return $checkStatus;

        $result = WarehouseBill::where($where)->update(array('is_end'=>1));
        return self::checkWithdrawStatus($result,$warehouseBillData);
    }


    /*
     * 发货单终止
     * */
    public static function deliveryGoodsOrder($param){
        $where['ygt_warehouse_send_relation.warehouse_bill_id'] = $param;
        $WarehouseSendData = WarehouseSend::where($where)->select('ygt_warehouse_send.*')
            ->leftJoin('ygt_warehouse_send_relation','ygt_warehouse_send_relation.warehouse_send_id','=','ygt_warehouse_send.id')
            ->where($where)
            ->first()->toArray();

        $checkStatus = self::checkOrderStatus($WarehouseSendData);
        if($checkStatus) return $checkStatus;

        $result = WarehouseSend::where(['id'=>$WarehouseSendData['id']])->update(array('is_end'=>1));

        return self::checkWithdrawStatus($result,$WarehouseSendData);

    }

    /*
     * 出库单终止
     * */
    public static function placingOrder($param){

        $where['ygt_warehouse_out_relation.warehouse_bill_id'] = $param;
        $WarehouseOutData = WarehouseOut::select('ygt_warehouse_out.*')
            ->leftJoin('ygt_warehouse_out_relation','ygt_warehouse_out_relation.warehouse_out_id','=','ygt_warehouse_out.id')
            ->where($where)
            ->first()->toArray();
        $checkStatus = self::checkOrderStatus($WarehouseOutData);
        if($checkStatus) return $checkStatus;

        $result = WarehouseOut::where(['id'=>$WarehouseOutData['id']])->update(array('is_end'=>1));

        return self::checkWithdrawStatus($result,$WarehouseOutData);
    }

    public static function checkWithdrawStatus($result,$data){
        if(!$result){
            return array(
                'code'    =>  0,
                'massage'       =>  '操作失败，请重试'
            );
        }

        return array(
            'code'    =>  1,
            'data'       =>  $data
        );
    }

    /*
     * 校验各种单是否终止
     * */
    public static function checkOrderStatus($orderData){

        if(!$orderData)
            return array(
                'code'    =>  '0',
                'massage'       =>  '校验订单出错'
            );

        if( (isset($orderData['is_end']) && $orderData['is_end'] == 1) ||
            (isset($orderData['status']) && $orderData['status'] == 21)
        )
            return array(
                'code'    =>  '1',
                'data'      =>  $orderData,
                'massage'       =>  '此单已终止'
            );


        return false;
    }

}