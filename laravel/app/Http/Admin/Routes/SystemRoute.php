<?php
/**
 * created by zzy
 * date: 2017/11/21 10:30
 */

namespace App\Http\Admin\Routes;
use Illuminate\Contracts\Routing\Registrar;


class SystemRoute
{
    public function map(Registrar $router)
    {
        $router->group(['namespace' => 'System\Controllers', 'prefix' => 'system'], function ($router) {
            //企业管理
            $router->get('company-list', 'CompanyController@lists');
            $router->post('company-list', 'CompanyController@lists');
            $router->get('company-edit', 'CompanyController@edit');
            $router->post('company-edit', 'CompanyController@edit');
            $router->post('company-delete', 'CompanyController@delete');
            //节点管理
            $router->get('menu-list', 'MenuController@lists');
            $router->post('menu-list', 'MenuController@lists');
            $router->get('menu-edit', 'MenuController@edit');
            $router->post('menu-edit', 'MenuController@edit');
            $router->post('menu-delete', 'MenuController@delete');
            //角色管理
            $router->get('role-list', 'RoleController@lists');
            $router->post('role-list', 'RoleController@lists');
            $router->get('role-edit', 'RoleController@edit');
            $router->post('role-update', 'RoleController@update');
            $router->post('role-delete', 'RoleController@delete');
            $router->get('role-menu', 'RoleController@menu');
            $router->post('role-menu', 'RoleController@menu');
            //角色账号管理
            $router->get('adminuser-list', 'AdminuserController@lists');
            $router->post('adminuser-list', 'AdminuserController@lists');
            $router->get('adminuser-edit', 'AdminuserController@edit');
            $router->post('adminuser-update', 'AdminuserController@update');
            $router->post('adminuser-delete', 'AdminuserController@delete');
            //复制管理
            $router->get('copy-index', 'CopyController@index');
            $router->post('copy-index', 'CopyController@index');
            $router->post('copy-process-list', 'CopyController@processList');
            $router->get('copy-copy', 'CopyController@copy');
            $router->post('copy-copy', 'CopyController@copy');
            //材料管理
            $router->any('dictionary-list', 'DictionaryController@lists');
            $router->any('dictionary-edit', 'DictionaryController@edit');
            $router->any('dictionary-delete', 'DictionaryController@delete');

            //清理工单等数据
            $router->any('cleanData', 'CompanyController@cleanData');

        });
    }
}