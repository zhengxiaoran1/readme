<?php
/**
 * Created by PhpStorm.
 * Author: kaodou
 * Date: 2017/12/19
 * Time: 14:20
 */

namespace App\Http\Admin\Routes;

use Illuminate\Contracts\Routing\Registrar;

class StorehouseRoute
{
    public function map(Registrar $router)
    {
        $router->group(['namespace' => 'Storehouse\Controllers', 'prefix' => 'storehouse'], function ($router) {
            $router->any('list', 'IndexController@index');

            $router->any('storehouse-save', 'IndexController@save');

            $router->any('del','IndexController@delete_storehouse');
            $router->any('product-list','IndexController@product-list');
            $router->any('stroeHouseManager','IndexController@stroeHouseManager');
            $router->any('saveStorehouseAdmin','IndexController@saveStorehouseAdmin');

            //仓库管理员列表
            $router->any('stroeHouseManagerList','IndexController@stroeHouseManagerList');
            
            ////获得集合材料列表
            $router->any('getAssemblageMaterialList','IndexController@getAssemblageMaterialList');

            $router->any('saveStorehouseManager','IndexController@saveStorehouseManager');
            $router->any('getStoreHouseJson','IndexController@getStoreHouseJson');

            //给库管添加管理整个公司的权限;
            $router->any('allRoleCompany','IndexController@allRoleCompany');
            
            //给管理员添加一个公司的权限;
            $router->any('allRoleStoreHouse','IndexController@allRoleStoreHouse');

            //仓库加废品
            $router->any('SaveRoleStoreHouseWaste','IndexController@SaveRoleStoreHouseWaste');


            //给仓库和仓库管理员添加权限;
            
            $router->any('SaveRoleStoreHouseUser','IndexController@SaveRoleStoreHouseUser');

            $router->any('getTree','IndexController@getTree');
            
            $router->any('SaveRoleAction','IndexController@SaveRoleAction');

            $router->any('SaveRoleToplevelAction','IndexController@SaveRoleToplevelAction');

            $router->any('SaveCompanyRole','IndexController@SaveCompanyRole');

            
            $router->any('searchStorehouse','IndexController@searchStorehouse');
            $router->any('CheckStoreHouse','IndexController@CheckStoreHouse');

            $router->any('getCompanyInfo','IndexController@getCompanyInfo');
            
            

        });
    }
}