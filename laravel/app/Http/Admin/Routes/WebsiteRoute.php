<?php
/**
 * Created by PhpStorm.
 * Author: Sojo
 * Date: 2017/2/16
 * Time: 19:33
 */
namespace App\Http\Admin\Routes;

use Illuminate\Contracts\Routing\Registrar;

class WebsiteRoute
{
    public function map(Registrar $router)
    {
        $router->group(['namespace' => 'Website\Controllers'], function ($router) {
            $router->get('/', 'IndexController@index');

            $router->get('login', 'IndexController@loginHome');
            $router->post('login', 'IndexController@loginHome');
            $router->get('login-timeout', 'IndexController@loginTimeout');
            $router->post('login-timeout', 'IndexController@loginTimeout');
            $router->get('logout', 'IndexController@logout');

            $router->post('side-menu-list/{id}', 'IndexController@getSideMenuList')->where('id', '[0-9]+');

            $router->group(['prefix' => 'setting'], function ($router) {
                $router->get('menu-manager', 'SettingController@menuManager');
            });

            $router->get('user-list', 'IndexController@getUserList');

            //菜单节点管理
            $router->get('menu-list', 'MenuController@lists');
            $router->post('menu-list', 'MenuController@lists');
        });
    }
}
