<?php
/**
 * Created by PhpStorm.
 * Author: kaodou
 * Date: 2018/3/9
 * Time: 14:18
 */

namespace App\Http\Admin\Routes;

use Illuminate\Contracts\Routing\Registrar;

class BuyersRoute
{
    public function map(Registrar $router)
    {
        $router->group(['namespace' => 'Buyers\Controllers', 'prefix' => 'buyers'], function ($router) {

            $router->any('list', 'IndexController@getList');
            
            $router->any('custom-save', 'IndexController@customSave');

        });
    }
}