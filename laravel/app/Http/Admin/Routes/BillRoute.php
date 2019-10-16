<?php
/**
 * Created by PhpStorm.
 * User: zhuyujun
 * Date: 2019/03/08
 * Time: 19:10
 */

namespace App\Http\Admin\Routes;

use Illuminate\Contracts\Routing\Registrar;

class BillRoute
{
    public function map(Registrar $router)
    {
        $router->group(['namespace' => 'Bill\Controllers', 'prefix' => 'bill'], function ($router) {
            // 采购账单列表
            $router->any('/getPurchaseBillList','IndexController@getPurchaseBillList');
            // 采购账单列表-数据
            $router->any('/getPurchaseBillListData','IndexController@getPurchaseBillListData');
            //导入采购账单页面
            $router->any('/importPurchaseBillPre','IndexController@importPurchaseBillPre');
            //处理导入账单
            $router->any('/importPurchaseBill','IndexController@importPurchaseBill');

            /**销售账单**/
            // 采购账单列表
            $router->any('/getSellerBillList','IndexController@getSellerBillList');
            // 采购账单列表-数据
            $router->any('/getSellerBillListData','IndexController@getSellerBillListData');
            //导入采购账单页面
            $router->any('/importSellerBillPre','IndexController@importSellerBillPre');
            //处理导入账单
            $router->any('/importSellerBill','IndexController@importSellerBill');


        });
    }

}