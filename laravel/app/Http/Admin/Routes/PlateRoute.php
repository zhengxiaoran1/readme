<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/11 0011
 * Time: 上午 11:27
 */

namespace App\Http\Admin\Routes;

use Illuminate\Contracts\Routing\Registrar;

class PlateRoute
{
    public function map(Registrar $router)
    {
        $router->group(['namespace' => 'Plate\Controllers', 'prefix' => 'plate'], function ($router) {
            $router->any('list', 'IndexController@getList');

            $router->any('save', 'IndexController@save');

            $router->any('del', 'IndexController@del');

            $router->any('export', 'IndexController@export');

            $router->any('import', 'IndexController@import');

            $router->any('custom-save', 'IndexController@customSave');

            $router->any('color', 'IndexController@color_list');

            $router->any('color-save', 'IndexController@color_save');

            $router->any('color-del', 'IndexController@color_del');

            $router->any('check_color_name', 'IndexController@check_color_name');
            
        });
    }
}