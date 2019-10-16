<?php
/**
 * created by zzy
 * date: 2017/10/24 9:28
 */
namespace App\Http\Admin\Routes;

use Illuminate\Contracts\Routing\Registrar;

class AppsetRoute
{
    public function map(Registrar $router)
    {
        $router->group(['namespace' => 'Appset\Controllers', 'prefix' => 'appset'], function ($router) {
            // 工作界面管理
            $router->get('work-list', 'IndexController@lists');
            $router->post('work-list', 'IndexController@lists');
            $router->get('work-edit', 'IndexController@edit');
            $router->post('work-edit', 'IndexController@edit');
            $router->post('work-delete', 'IndexController@delete');

        });
    }
}