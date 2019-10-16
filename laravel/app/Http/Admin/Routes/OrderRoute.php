<?php
/**
 * Created by PhpStorm.
 * User: zhuyujun
 * Date: 2017/11/9
 * Time: 14:42
 */

namespace App\Http\Admin\Routes;

use Illuminate\Contracts\Routing\Registrar;

class OrderRoute
{
    public function map(Registrar $router)
    {
        $router->group(['namespace' => 'Order\Controllers', 'prefix' => 'order'], function ($router) {
            // 工单列表基础页面
            $setFunction = 'getOrderList';
            $router->get('/'.$setFunction,'IndexController@'.$setFunction);
            $router->post('/'.$setFunction,'IndexController@'.$setFunction);

            // 工单列表数据
            $setFunction = 'getOrderListData';
            $router->get('/'.$setFunction,'IndexController@'.$setFunction);
            $router->post('/'.$setFunction,'IndexController@'.$setFunction);

            //工单详细数据
            $setFunction = 'getOrderDetail';
            $router->get('/'.$setFunction,'IndexController@'.$setFunction);
            $router->post('/'.$setFunction,'IndexController@'.$setFunction);

            //工单工序列表基础页面
            $setFunction = 'getOrderProcessList';
            $router->get('/'.$setFunction,'IndexController@'.$setFunction);
            $router->post('/'.$setFunction,'IndexController@'.$setFunction);

            //工单工序列表数据
            $setFunction = 'getOrderProcessListData';
            $router->get('/'.$setFunction,'IndexController@'.$setFunction);
            $router->post('/'.$setFunction,'IndexController@'.$setFunction);

            //工单工序详细数据
            $setFunction = 'getOrderProcessDetail';
            $router->get('/'.$setFunction,'IndexController@'.$setFunction);
            $router->post('/'.$setFunction,'IndexController@'.$setFunction);

            //创建工单
            $setFunction = 'createOrder';
            $router->get('/'.$setFunction,'IndexController@'.$setFunction);
            $router->post('/'.$setFunction,'IndexController@'.$setFunction);

            //工单类型提交
            $setFunction = 'orderTypeSubmit';
            $router->get('/'.$setFunction,'IndexController@'.$setFunction);
            $router->post('/'.$setFunction,'IndexController@'.$setFunction);

            //创建工单-添加配送地址
            $setFunction = 'createOrderDistribution';
            $router->get('/'.$setFunction,'IndexController@'.$setFunction);
            $router->post('/'.$setFunction,'IndexController@'.$setFunction);

            //省市区联动，获取市区信息
            $setFunction = 'getCityAreaList';
            $router->get('/'.$setFunction,'IndexController@'.$setFunction);
            $router->post('/'.$setFunction,'IndexController@'.$setFunction);

            //创建工单-添加配送地址-提交
            $setFunction = 'createOrderDistributionSubmit';
            $router->get('/'.$setFunction,'IndexController@'.$setFunction);
            $router->post('/'.$setFunction,'IndexController@'.$setFunction);

            //工单创建详情页
            $setFunction = 'createOrderDetail';
            $router->get('/'.$setFunction,'IndexController@'.$setFunction);
            $router->post('/'.$setFunction,'IndexController@'.$setFunction);

            //工单创建-挑选材料
            $setFunction = 'pickMaterial';
            $router->get('/'.$setFunction,'IndexController@'.$setFunction);
            $router->post('/'.$setFunction,'IndexController@'.$setFunction);

            //后台工单创建提交
            $setFunction = 'createOrderSubmit';
            $router->get('/'.$setFunction,'IndexController@'.$setFunction);
            $router->post('/'.$setFunction,'IndexController@'.$setFunction);

            //下单人工单分配类型
            $setFunction = 'getOrderManageAssignmentType';
            $router->get('/'.$setFunction,'IndexController@'.$setFunction);
            $router->post('/'.$setFunction,'IndexController@'.$setFunction);

            //后台工单确认派发-基础页面
            $setFunction = 'orderConfirmAssignmentBaseInfo';
            $router->get('/'.$setFunction,'IndexController@'.$setFunction);
            $router->post('/'.$setFunction,'IndexController@'.$setFunction);

            //后台工单确认派发-提交
            $setFunction = 'orderConfirmAssignment';
            $router->get('/'.$setFunction,'IndexController@'.$setFunction);
            $router->post('/'.$setFunction,'IndexController@'.$setFunction);

            //工单再来一单
            $setFunction = 'copyOrder';
            $router->get('/'.$setFunction,'IndexController@'.$setFunction);
            $router->post('/'.$setFunction,'IndexController@'.$setFunction);

            //工单报表类型列表
            $setFunction = 'orderReportType';
            $router->get('/'.$setFunction,'IndexController@'.$setFunction);
            $router->post('/'.$setFunction,'IndexController@'.$setFunction);

            //工单报表类型列表-数据获取
            $setFunction = 'getOrderReportTypeListData';
            $router->get('/'.$setFunction,'IndexController@'.$setFunction);
            $router->post('/'.$setFunction,'IndexController@'.$setFunction);

            //工单报表类型编辑
            $setFunction = 'eidtOrderReportType';
            $router->get('/'.$setFunction,'IndexController@'.$setFunction);
            $router->post('/'.$setFunction,'IndexController@'.$setFunction);

            //工单报表类型编辑-数据提交
            $setFunction = 'eidtOrderReportTypeSubmit';
            $router->get('/'.$setFunction,'IndexController@'.$setFunction);
            $router->post('/'.$setFunction,'IndexController@'.$setFunction);

            //工单报表类型删除
            $setFunction = 'delOrderReportType';
            $router->get('/'.$setFunction,'IndexController@'.$setFunction);
            $router->post('/'.$setFunction,'IndexController@'.$setFunction);

            //工单类型设置报表内容
            $setFunction = 'chooseOrderReportCell';
            $router->get('/'.$setFunction,'IndexController@'.$setFunction);
            $router->post('/'.$setFunction,'IndexController@'.$setFunction);

            //工单类型设置报表内容-数据提交
            $setFunction = 'chooseOrderReportCellSubmit';
            $router->get('/'.$setFunction,'IndexController@'.$setFunction);
            $router->post('/'.$setFunction,'IndexController@'.$setFunction);

            //工单类型设置报表发送用户
            $setFunction = 'chooseSendUser';
            $router->get('/'.$setFunction,'IndexController@'.$setFunction);
            $router->post('/'.$setFunction,'IndexController@'.$setFunction);

            //工单类型设置报表发送用户-数据提交
            $setFunction = 'chooseSendUserSubmit';
            $router->get('/'.$setFunction,'IndexController@'.$setFunction);
            $router->post('/'.$setFunction,'IndexController@'.$setFunction);

            //员工订单列表
            $setFunction = 'getOrderProcessCourseList';
            $router->any('/'.$setFunction,'IndexController@'.$setFunction);

            //员工订单列表-数据获取
            $setFunction = 'getOrderProcessCourseListData';
            $router->any('/'.$setFunction,'IndexController@'.$setFunction);

            //测试
            $setFunction = 'test';
            $router->any('/'.$setFunction,'IndexController@'.$setFunction);
            //设置工资
            $router->any('/coursePrice','CourseController@price');
            $router->any('/courseSetPrice','CourseController@setPrice');


        });
    }

}