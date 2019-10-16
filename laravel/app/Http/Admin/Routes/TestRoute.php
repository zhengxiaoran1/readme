<?php
/**
 * Created by PhpStorm.
 * Author: zhuyujun
 * Date: 2017/10/17
 * Time: 16:22
 */
namespace App\Http\Admin\Routes;

use Illuminate\Contracts\Routing\Registrar;

class TestRoute
{
    public function map(Registrar $router)
    {
        $router->group(['namespace' => 'Test\Controllers', 'prefix' => 'test'], function ($router) {
            // 用户管理
            $router->get('/', 'IndexController@test');


            $router->get('/simple', 'IndexController@test');

            //上传图片
            $router->get('/upload', 'IndexController@upload');
            $router->post('/upload', 'IndexController@upload');
            //更改成品书局
            $router->get('changeWarehouse', 'IndexController@changeWarehouse');

            $router->get('saveWarsehousePid', 'IndexController@saveWarsehousePid');

            $router->get('saveWarsehouseAggregate', 'IndexController@saveWarsehouseAggregate');
            
        });
    }
}