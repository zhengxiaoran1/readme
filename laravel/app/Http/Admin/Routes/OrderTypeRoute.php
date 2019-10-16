<?php
/**
 * Created by PhpStorm.
 * Author: kaodou
 * Date: 2017/10/17
 * Time: 14:58
 */
namespace App\Http\Admin\Routes;

use Illuminate\Contracts\Routing\Registrar;

class OrderTypeRoute
{
    public function map(Registrar $router)
    {
        $router->group(['namespace' => 'OrderType\Controllers', 'prefix' => 'order-type'], function ($router) {

            $router->get('list', 'IndexController@getOrderTypeList');
            $router->post('list', 'IndexController@getOrderTypeList');

            $router->get('set-process', 'IndexController@setOrderTypeProcess');
            $router->post('set-process', 'IndexController@setOrderTypeProcess');

            $router->get('add', 'IndexController@addOrderType');
            $router->post('add', 'IndexController@addOrderType');

            $router->get('edit', 'IndexController@editOrderType');
            $router->post('edit', 'IndexController@editOrderType');

            $router->post('del', 'IndexController@deleteOrderType');

            //获取工艺中，工序列表，异步
            $router->post('process-list', 'IndexController@getOrderTypeProcessListWhitNextStep');

            //删除工艺中的工序
            $router->post('process-del', 'IndexController@deleteOrderTypeProcess');

            //工艺 增加工序
            $router->any('process-add', 'IndexController@addOrderTypeProcess');

            //工艺中某个工序的编辑，设置步骤
            $router->post('process-edit', 'IndexController@editOrderTypeProcess');

            $router->post('process-prev-step', 'IndexController@editOrderTypeProcess');

            //设置主要用材
            $router->any('set-main-material', 'IndexController@setMainMaterial');

            //设置下一步
            $router->any('set-next-process', 'IndexController@setOrderTypeProcessStep');

            //设置动作
            $router->any('set-process-action', 'IndexController@setOrderTypeProcessAction');


            //分类
            $router->any('category-list', 'CategoryController@getList');
            $router->any('category-save', 'CategoryController@save');
            $router->any('category-del', 'CategoryController@del');

            //工序废品设置
            $router->any('set-process-waste', 'IndexController@setOrderTypeProcessWaste');
            //工序废品设置-数据
            $router->any('process-waste-list', 'IndexController@processWasteList');
            //工序废品-编辑
            $router->any('process-waste-edit', 'IndexController@processWasteEdit');
            //工序废品-删除
            $router->any('process-waste-del', 'IndexController@processWasteDelete');

            //工序半成品列表
            $router->any('set-process-product', 'IndexController@setOrderTypeProcessProduct');


            //工序半成品列表-数据
            $router->any('process-product-list', 'IndexController@processProductList');
            //工序半成品-编辑
            $router->any('process-product-edit', 'IndexController@processProductEdit');
            //工序废品-删除
            $router->any('process-product-del', 'IndexController@processProducDelete');

            //工序半成品列表-配置属性
            $router->any('setProcessProductAttribute', 'IndexController@setProcessProductAttribute');

            //废品原因列表
            $router->any('getWasteReasonList', 'WastereasonController@getWasteReasonList');
            $router->any('wasteReasonEdit', 'WastereasonController@wasteReasonEdit');
            $router->any('wasteReasonDel', 'WastereasonController@wasteReasonDel');


        });
    }
}