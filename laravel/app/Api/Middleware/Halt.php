<?php
/**
 * created by hjn
 * date: 2019/7/19 14:36
 */

namespace App\Api\Middleware;

use Closure;
use \App\Eloquent\Ygt\OrderProcess;
use \App\Eloquent\Ygt\OrderProcessCourse;
class Halt{

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

        switch ($controller){
            case 'IndexController@orderProcessManageUseMaterial':
            case 'IndexController@getOrderDetail':
            case 'IndexController@orderProcessManageReceiveMaterial':
//
                if(isset($data['order_process_id']) && $data['order_process_id']){
                    if(OrderProcess::where('id','=',$data['order_process_id'])->value('halt_uid'))
                        return ['code'=>'1','message'=>'当前工单已暂停'];
                }else{
                    if(OrderProcessCourse::where('id','=',$data['order_process_course_id'])->value('halt_uid'))
                        return ['code'=>'1','message'=>'当前工单已暂停'];
                }

                break;
            case 'IndexController@submitOrderProcessCourseGradation':
            case 'IndexController@orderMaterialReceiveGradationSubmit':
            case 'IndexController@employeeFinishedProduct':
                if(OrderProcessCourse::where('id','=',$data['order_process_course_id'])->value('halt_uid'))
                    return ['code'=>'1','message'=>'当前工单已暂停'];
                break;
            case 'IndexController@confirmComplete':
                //苹果与安卓传的键名不同 在此做兼容 wei 20190906
                if (isset($data['order_process_id'])){
                    $orderProcess = $data['order_process_id'];
                }
                if (isset($data['order_process_course_id'])){
                    $orderProcess = $data['order_process_course_id'];
                }
                //end
                if(OrderProcessCourse::where('id','=',$orderProcess)->value('halt_uid'))
                    return ['code'=>'1','message'=>'当前工单已暂停'];
                break;
            case 'IndexController@orderProcessManageUseMaterialSubmit':
                if(OrderProcess::where('id','=',$data['order_process_course_id'])->value('halt_uid'))
                    return ['code'=>'1','message'=>'当前工单已暂停'];
                break;

        }

        return $next($request);
    }


}