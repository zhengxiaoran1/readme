<?php
/**
 * Created by PhpStorm.
 * User: zhuyujun
 * Date: 2017/11/13
 * Time: 11:29
 */

namespace App\Http\Mobile\Routes;

use Illuminate\Contracts\Routing\Registrar;


class ProductRoute
{
    public function map(Registrar $router)
    {
        $router->group(['namespace' => 'Product\Controllers', 'prefix' => 'product'], function ($router) {
            $router->any('/list', 'IndexController@indexlist');
            $router->any('/choseType', 'IndexController@choseType');
            $router->any('/edit', 'IndexController@edit');
            $router->any('/createOrder', 'IndexController@createOrder');
            $router->any('/orderList', 'IndexController@orderList');
            $router->any('/orderInfo', 'IndexController@orderInfo');
        });
    }

}