<?php
/**
 * Created by PhpStorm.
 * Author: kaodou
 * Date: 2017/10/17
 * Time: 14:58
 */
namespace App\Http\Admin\Routes;

use Illuminate\Contracts\Routing\Registrar;

class StockRoute
{
    public function map(Registrar $router)
    {
        $router->group(['namespace' => 'Stock\Controllers', 'prefix' => 'stock'], function ($router) {
            $router->get('list', 'IndexController@getStockList');
            $router->post('list', 'IndexController@getStockList');

            $router->any('in', 'IndexController@stockIn');

            $router->any('info', 'IndexController@purchaseInfo');

        });
    }
}