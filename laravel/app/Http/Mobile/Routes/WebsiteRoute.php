<?php
/**
 * Created by PhpStorm.
 * Author: Sojo
 * Date: 2017/2/16
 * Time: 19:33
 */
namespace App\Http\Mobile\Routes;

use Illuminate\Contracts\Routing\Registrar;

class WebsiteRoute
{
    public function map(Registrar $router)
    {
        $router->group(['namespace' => 'Website\Controllers'], function ($router) {
            $router->get('/', 'IndexController@index');
            $router->get('/pwd', 'IndexController@pwd');
            $router->get('/home', 'IndexController@home');
            $router->any('login', 'IndexController@loginHome');
            $router->any('login-timeout', 'IndexController@loginTimeout');
            $router->post('logout', 'IndexController@logout');
            $router->post('side-menu-list/{id}', 'IndexController@getSideMenuList')->where('id', '[0-9]+');
            $router->group(['prefix' => 'setting'], function ($router) {
                $router->get('menu-manager', 'SettingController@menuManager');
            });
            $router->get('user-list', 'IndexController@getUserList');

            //菜单节点管理
            $router->any('menu-list', 'MenuController@lists');
        });
    }
}
