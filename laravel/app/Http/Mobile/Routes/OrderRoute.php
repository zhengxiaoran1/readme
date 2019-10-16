<?php
/**
 * Created by PhpStorm.
 * User: zhuyujun
 * Date: 2017/11/13
 * Time: 11:29
 */

namespace App\Http\Mobile\Routes;

use Illuminate\Contracts\Routing\Registrar;


class OrderRoute
{
    public function map(Registrar $router)
    {
        $router->group(['namespace' => 'Order\Controllers', 'prefix' => 'order'], function ($router) {
            //工单报表
            $setFunction = 'orderReport';
            $router->get('/' . $setFunction, 'IndexController@' . $setFunction);
            $router->post('/' . $setFunction, 'IndexController@' . $setFunction);

        });
    }

}