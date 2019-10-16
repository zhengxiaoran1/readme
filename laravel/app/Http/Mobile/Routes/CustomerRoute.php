<?php
/**
 * Created by PhpStorm.
 * User: wei
 * Date: 2019/09/24
 * Time: 11:29
 */

namespace App\Http\Mobile\Routes;

use Illuminate\Contracts\Routing\Registrar;


class CustomerRoute
{
    public function map(Registrar $router)
    {
        $router->group(['namespace' => 'Customer\Controllers', 'prefix' => 'customer'], function ($router) {

            //交货单列表
            $setFunction = 'outBill';
            $router->any('/'.$setFunction, 'IndexController@' . $setFunction);

            //打印交货单详情
            $setFunction = 'printDetail';
            $router->any('/'.$setFunction, 'IndexController@' . $setFunction);

        });
    }

}