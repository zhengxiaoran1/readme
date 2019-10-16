<?php
/**
 * created by hjn
 * date: 2019/7/18 10:37
 */

namespace App\Engine;

use App\Eloquent\Zk\Order;
use App\Eloquent\Zk\OrderListRelation;
use App\Eloquent\Zk\OrderProcess;
use App\Eloquent\Zk\OrderProcessCourse;
use App\Eloquent\Zk\Process;
use App\Eloquent\Zk\UserMessage;


//实现平台所有暂停、开启业务
class Halt{

    public static function workSheet($orderId,$status,$msg){

        $user_id = \App\Engine\Func::getHeaderValueByName('userid');

        $orderInfo = Order::find($orderId);
//        if($orderInfo->status == $status) return ['code'=>'1','message'=>'工单已'.$msg];
//        if($orderInfo->status != 2 && $status == 22) return ['code'=>'1','message'=>'当前工单无法'.$msg];
        if($orderInfo->halt_uid && $orderInfo->halt_uid != $user_id)
            return ['code'=>'1','message'=>'您无权对此工单进行继续开工操作'];

        $orderInfo->halt_uid = $status == 22 ? $user_id : 0;
//        $orderInfo->status = $status;
        if(!$orderInfo->save()) return ['code'=>'1','message'=>'工单'.$msg.'失败'];
        $orderInfo = Order::find($orderId);
        $OrderListRelationInfo = OrderListRelation::where(['relate_id' =>  $orderId,'type' =>  1,])->first();

        if($OrderListRelationInfo->halt_uid && $OrderListRelationInfo->halt_uid != $user_id)
            return ['code'=>'1','message'=>'您无权对此工单进行继续开工操作'];

        $OrderListRelationInfo->halt_uid = $status == 22 ? $user_id : 0;
//        $OrderListRelationInfo->status = $status;
        if(!$OrderListRelationInfo->save()) return ['code'=>'1','message'=>'工单关联数据'.$msg.'失败'];

        return $orderInfo;
    }

    /*
     * 工序工单暂停
     * */
    public static function seedWorkSheet($orderInfo,$status,$msg,$orderProcessId=0){

        $user_id = \App\Engine\Func::getHeaderValueByName('userid');
        $orderTitle = \App\Engine\Common::changeSnCode($orderInfo['order_title']);//获取工单号

        $where['order_id']  =  $orderInfo['id'];

//        if($orderProcessId) $where['id']        =  $orderProcessId;
        $OrderProcessData = OrderProcess::where($where)->whereNotNull('uid')->get();
        $id = $orderProcessId?$orderProcessId:0;
        $orderProcessId = $id?[$id]:array_column($OrderProcessData->toArray(),'id');
        $orderProcessCourseData = OrderProcessCourse::whereIn('order_process_id',$orderProcessId)->get();
        $orderProcessCourseId = array_column($orderProcessCourseData->toArray(),'id');

//x($OrderProcessData->toArray());
        $orderListRelationList = OrderListRelation::where(function($query) use ($orderProcessId){
            $query->whereIn('relate_id',$orderProcessId)->whereIn('type',[2,3]);
        })->orWhere(function($query) use ($orderProcessCourseId){
            $query->where(['type'=>4])->whereIn('relate_id',$orderProcessCourseId);
        })->get();
        //工序暂停
        foreach ($OrderProcessData as $OrderProcess){

            if($OrderProcess->halt_uid && $OrderProcess->halt_uid != $user_id)
                return ['code'=>'1','message'=>'您无权对此工单进行继续开工操作'];

            $process_title[$OrderProcess['id']] = Process::getOneValueById($OrderProcess['process_type'], 'title');
            $message_content = $orderTitle . "rnrn" . $process_title[$OrderProcess['id']] . '工序已'.$msg;
            $titleTheme = "工序工单".$msg;

            if(in_array($OrderProcess['id'],$orderProcessId)){
                $OrderProcess->halt_uid = $status == 22 ? $user_id : 0;
                if(!$OrderProcess->save()) return ['code'=>'1','message'=>'工序工单'.$msg.'失败'];
                //下单人受到操作消息
                if($user_id != $orderInfo['uid']){
                    $data = [
                        'company_id' => $orderInfo['company_id'],
                        'privilege_id' => '',
                        'form_user_id' => $user_id,
                        'to_user_id' => $orderInfo['uid'],
                        'foreign_key' => $orderInfo['customer_order_id'],
                        'type' => 35,//只做展示不做跳转用
                        'type_id' => $OrderProcess['id'],
                        'title' => $titleTheme,
                        'content' => $message_content,
                        'theme' => $titleTheme,
                    ];
                    if(!UserMessage::sendCustomerOrderMessage($data))
                        return ['code'=>'1','message'=>'小秘书发送消息失败'];
                }
            }

            //当前工序主管不受到消息
            if($user_id == $OrderProcess['uid']) continue;

            //其他工序主管
            $data = [
                'company_id' => $orderInfo['company_id'],
                'privilege_id' => '',
                'form_user_id' => $user_id,
                'to_user_id' => $OrderProcess['uid'],
                'foreign_key' => $orderInfo['customer_order_id'],
                'type' => 35,//只做展示不做跳转用
                'type_id' => $OrderProcess['id'],
                'title' => $titleTheme,
                'content' => $message_content,
                'theme' => $titleTheme,
            ];
            if(!UserMessage::sendCustomerOrderMessage($data))
                return ['code'=>'1','message'=>'小秘书发送消息失败'];


        }

        //工单暂停
        foreach ($orderProcessCourseData as $orderProcessCourse){
            if($orderProcessCourse->halt_uid && $orderProcessCourse->halt_uid != $user_id)
                return ['code'=>'1','message'=>'您无权对此工单进行继续开工操作'];
            $orderProcessCourse->halt_uid = $status == 22 ? $user_id : 0;
            if(!$orderProcessCourse->save()) return ['code'=>'1','message'=>'员工工单'.$msg.'失败'];
            $process_title[$orderProcessCourse['id']] = $process_title[$orderProcessCourse['order_process_id']];
        }

        //关联列表状态修改
        foreach ($orderListRelationList as $orderListRelation){
            $orderListRelation->halt_uid = $status == 22 ? $user_id : 0;
            if(!$orderListRelation->save()) return ['code'=>'1','message'=>'员工工单关联数据'.$msg.'失败'];

            //不发送给操作人
            if($user_id == $orderListRelation['uid']) continue;

            $message_content = $orderTitle . "rnrn" . $process_title[$orderListRelation['relate_id']] . ($orderListRelation['type'] == 4 ? "员工工单-已" . $msg : "工单-已" . $msg);
            $titleTheme = $orderListRelation['type'] == 4 ? "员工工单" . $msg : "工序工单" . $msg;

            $data = [
                'company_id' => $orderInfo['company_id'],
                'privilege_id' => '',
                'form_user_id' => $user_id,
                'to_user_id' => $orderListRelation['uid'],
                'foreign_key' => $orderInfo['customer_order_id'],
                'type' => 35,//只做展示不做跳转用
                'type_id' => $orderListRelation['id'],
                'title' => $titleTheme,
                'content' => $message_content,
                'theme' => $titleTheme,
            ];
            if (!UserMessage::sendCustomerOrderMessage($data))
                return ['code' => '1', 'message' => '小秘书发送消息失败'];

        }

        return false;

    }

}