<?php
/**
 * created by hjn
 * date: 2019/7/19 14:36
 */

namespace App\Api\Middleware;

use Closure;
use \App\Eloquent\Ygt\WarehouseBill;
use \App\Eloquent\Ygt\OrderProcessMaterialSubmit;
use \App\Eloquent\Ygt\OrderProcess;
use \App\Eloquent\Ygt\OrderProcessCourse;
use \App\Eloquent\Ygt\OrderProcessProcessProductSubmit;
use \App\Eloquent\Ygt\ChanpinOrder;

class Termination{

    public function handle($request, Closure $next)
    {

        //接收参数，以备提取参数做校验
        $data = $request->input();

        //获取当前目标路由名称
        $route = app('request')->route();
        if ($route) {
            $action = app('request')->route()->getAction();
            $controller = '@';
            if (isset($action['controller'])) {
                $controller = class_basename($action['controller']);
            }
        }
        $user_id = \App\Engine\Func::getHeaderValueByName('userid');
        switch ($controller){
            //交货单已终止
//            case 'IndexController@orderProcessManageReceiveMaterial':
//                if(OrderProcess::where('id','=',$data['order_process_id'])->value('is_end'))
//                    return ['code'=>'1','message'=>'工单已终止无法领取材料，无法操作！'];
//                break;
            case 'IndexController@createChaninSellerOrderPre':
            case 'IndexController@createChaninSellerOrderSubmit':

                if(ChanpinOrder::where(['id'=>$data['chanpin_order_id']])->value('status') == 5)
                    return ['code'=>'1','message'=>'订单已终止，无法操作！'];

                break;
            case 'SendController@sendCreateCheck':
            case 'SendController@sendCreateMerge':
            case 'OutController@confirm':
                    if(WarehouseBill::where('id','=',$data['warehouse_bill_id'])->value('is_end'))
                        return ['code'=>'1','message'=>'发货单已终止，无法操作！'];
                break;
            case 'IndexController@receiveOrderProcessProduct';
            case 'IndexController@addOrderProcessProduct';
                if( OrderProcess::where('id','=',$data['order_process_id'])->value('status') == 21)
                        return ['code'=>'1','message'=>'工单终止已经终止'];
                break;

            case "IndexController@orderMaterialReceiveGradationSubmit";
                if(OrderProcessCourse::where('id','=',$data['order_process_course_id'])->value('status') == 21){
                    return ['code'=>'1','message'=>'工单终止已经终止'];
                }
                break;
            //工单终止submitProcessProductResidualNumber
            case 'IndexController@orderProcessManageUseMaterialSubmit':
                if(OrderProcessCourse::where('id','=',$data['order_process_course_id'])->value('status') == 21){
                    if(OrderProcessMaterialSubmit::where(['order_process_course_id'=>$data['order_process_course_id'],'uid'=>$user_id])->count() > 0)
                        return ['code'=>'1','message'=>'工单终止后只允许提交一次，您已提交'];
                }
                break;
            case "OrderController@submitProcessProductResidualNumber":
                if(OrderProcessCourse::where('id','=',$data['order_process_course_id'])->value('status') == 21){
                    if(OrderProcessProcessProductSubmit::where(['order_process_course_id'=>$data['order_process_course_id'],'uid'=>$user_id])->count() > 0)
                        return ['code'=>'1','message'=>'工单终止后只允许提交一次，您已提交'];
                }
            case "IndexController@submitOrderProcessCourseGradation":
                $OrderProcessCourseInfo = OrderProcessCourse::where('id','=',$data['order_process_course_id'])->first();
                if($OrderProcessCourseInfo['status'] == 21){
                    if($OrderProcessCourseInfo['finish_number'] > 0)
                        return ['code'=>'1','message'=>'工单终止后只允许提交一次，您已提交'];
                }
                break;
        }

        return $next($request);
    }


}