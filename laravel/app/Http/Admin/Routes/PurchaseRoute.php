<?php
/**
 * Created by PhpStorm.
 * Author: kaodou
 * Date: 2017/12/19
 * Time: 14:20
 */

namespace App\Http\Admin\Routes;

use Illuminate\Contracts\Routing\Registrar;

class PurchaseRoute
{
    public function map(Registrar $router)
    {
        $router->group(['namespace' => 'Purchase\Controllers', 'prefix' => 'purchase'], function ($router) {
            $router->any('list', 'IndexController@getList');

            $router->any('add', 'IndexController@add');

        });
    }
}