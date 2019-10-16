<?php
/**
 * Created by PhpStorm.
 * Author: kaodou
 * Date: 2017/12/19
 * Time: 14:20
 */

namespace App\Http\Admin\Routes;

use Illuminate\Contracts\Routing\Registrar;

class SupplierRoute
{
    public function map(Registrar $router)
    {
        $router->group(['namespace' => 'Supplier\Controllers', 'prefix' => 'supplier'], function ($router) {
            $router->any('list', 'IndexController@getList');

            $router->any('save', 'IndexController@save');

            $router->any('del', 'IndexController@del');

            $router->any('custom-save', 'IndexController@customSave');

            $router->any('export', 'IndexController@export');

        });
    }
}