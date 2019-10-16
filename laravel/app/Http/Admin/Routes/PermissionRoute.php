<?php
/**
 * Created by PhpStorm.
 * Author: zhuyujun
 * Date: 20171020
 * Time: 10:50
 */
namespace App\Http\Admin\Routes;

use Illuminate\Contracts\Routing\Registrar;

class PermissionRoute
{
    public function map(Registrar $router)
    {
        $router->group(['namespace' => 'Permission\Controllers','prefix' => 'permission'], function ($router) {

            $router->any('list', 'IndexController@getPermissionList');

            $router->any('look-action-set', 'IndexController@lookActionSet');

            $router->any('base-fields-set', 'IndexController@baseFieldsSet');

            $router->any('need-piece-set', 'IndexController@needPieceSet');

            //设置成品入库
            $router->any('finished-product-set', 'IndexController@finishedProductSet');

            $router->any('privilege-sort-set', 'IndexController@privilegeSortSet');

            $router->any('need-switch-set', 'IndexController@needSwitchSet');

            $router->any('need-baler-set', 'IndexController@needBalerSet');

            

        });
    }
}
