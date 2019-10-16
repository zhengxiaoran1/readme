<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/16 0016
 * Time: 下午 3:10
 */

namespace App\Http\Admin\Routes;

use Illuminate\Contracts\Routing\Registrar;

class BranchRoute
{
    public function map(Registrar $router)
    {
        $router->group(['namespace' => 'Branch\Controllers', 'prefix' => 'branch'], function ($router) {
            $router->any('list', 'IndexController@getList');

            $router->any('save', 'IndexController@save');

            $router->any('del', 'IndexController@del');

            $router->any('export', 'IndexController@export');

            $router->any('custom-save', 'IndexController@customSave');

        });
    }
}