<?php
/**
 * Created by PhpStorm.
 * Author: kaodou
 * Date: 2018/3/1
 * Time: 14:40
 */

namespace App\Http\Admin\Routes;

use Illuminate\Contracts\Routing\Registrar;

class AddressRoute
{
    public function map(Registrar $router)
    {
        $router->group(['namespace' => 'Address\Controllers', 'prefix' => 'address'], function ($router) {
            $router->any('ajaxAreaList', 'IndexController@ajaxAreaList');
        });
    }
}