<?php
/**
 * created by HJN
 * date: 2019/7/25 18:32
 */

namespace App\Api\Middleware;

use Closure;
use \App\Eloquent\Ygt\CustomerOrder;
use \App\Eloquent\Ygt\OrderProcessCourse;
use \App\Eloquent\Ygt\OrderProcess;
use \App\Eloquent\Ygt\Order;
use \App\Eloquent\Ygt\WaitPurchase;
use \App\Eloquent\Ygt\UserMessage;

class Withdraw{

    public function handle($request, Closure $next)
    {

        //接收参数，以备提取参数做校验
        $param = $request->input();

        //获取当前目标路由名称
        $route = app('request')->route();
        if ($route) {
            $action = app('request')->route()->getAction();
            $controller = '@';
            if (isset($action['controller'])) {
                $controller = class_basename($action['controller']);
            }
        }

        switch ($controller){
            //生产单转工单时验证是否已撤回
            case "OrderController@getOrderMaterialDetailStorehouse":
            case 'IndexController@manageOrderCourseAssignmentSubmitV2':
                if(!Order::where('id',$param['order_id'])->first())
                    return ['code'=>'1','message'=>'当前工单已撤回'];
                break;
            case 'IndexController@createChaninManageOrderPre':
            case 'OrderController@historyList':
            case 'IndexController@createChaninManageOrderSubmit':
            case 'IndexController@getCustomerOrderDetail'://小秘书消息中消息点击
                if(CustomerOrder::onlyTrashed()->where(['id'=>$param['customer_order_id']])->get()->toArray())
                    return ['code'=>'1','message'=>'当前生产单已撤回'];
                break;
            case 'IndexController@getOrderDetail':
            case 'IndexController@orderMaterialReceiveGradationSubmit':
            case 'IndexController@orderProcessManageReceiveMaterial':
            case 'IndexController@orderCourseAssignmentSubmit':
                if(isset($param['order_process_course_id']) && $param['order_process_course_id']){
                    if(OrderProcessCourse::onlyTrashed()->where(['id'=>$param['order_process_course_id']])->get()->toArray())
                        return ['code'=>'1','message'=>'当前工单已撤回'];
                }elseif(isset($param['order_process_id']) && $param['order_process_id']){
                    if(OrderProcess::onlyTrashed()->where(['id'=>$param['order_process_id']])->get()->toArray()
                        || !OrderProcess::where(['id'=>$param['order_process_id']])->value('uid')
                    )
                        return ['code'=>'1','message'=>'当前工序工单已撤回'];
                }
                break;
            case "IndexController@getOrderMaterialDetail":
                if(WaitPurchase::onlyTrashed()->where(['id'=>$param['wait_purchase_id']])->get()->toArray())
                    return ['code'=>'1','message'=>'当前待采购数量已撤回'];
                break;
//            case "OrderController@getOrderMaterialDetailStorehouse":
//                if(!WaitPurchase::where(['order_id'=>$param['order_id']])->get()->toArray())
//                    return ['code'=>'1','message'=>'当前工单用料已撤回'];
//                break;
            case "IndexController@afterOrderDetail":
            case "IndexController@confirmStart":
                if(isset($param['order_process_id']) && $param['order_process_id']){
                    if(OrderProcess::onlyTrashed()->where(['id'=>$param['order_process_id']])->get()->toArray()
                        || !OrderProcess::where(['id'=>$param['order_process_id']])->value('uid')
                    )
                        return ['code'=>'1','message'=>'当前工序工单已撤回'];
                }
                break;
            case "OrderController@start":
                $message            = UserMessage::getInfo(['id'=>$param['message_id']]);
                if(!$message) return ['code'=>'1','message'=>'消息出错'];
                if(!Order::where('id',$message->type_id)->first())
                    return ['code'=>'1','message'=>'当前工单已撤回'];
                break;
            case "OrderController@uploadSample":
            case "IndexController@checkConfirmStart":
                if(OrderProcessCourse::onlyTrashed()->where(['id'=>$param['order_process_course_id']])->get()->toArray())
                    return ['code'=>'1','message'=>'当前工单已撤回'];
                break;
            case "IndexController@create":
                if(isset($param['wait_purchase_id']) && WaitPurchase::onlyTrashed()->where(['id'=>$param['wait_purchase_id']])->get()->toArray())
                    return ['code'=>'1','message'=>'需采购已撤回，无需提交'];
                break;

        }

        return $next($request);
    }


}