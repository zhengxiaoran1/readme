<?php
/**
 * created by zzy
 * date: 2017/10/24 9:28
 */
namespace App\Http\Admin\Routes;

use Illuminate\Contracts\Routing\Registrar;

class CompanyRoute
{
    public function map(Registrar $router)
    {
        $router->group(['namespace' => 'Company\Controllers', 'prefix' => 'company'], function ($router) {
            // 企业设置
            $router->any('edit', 'IndexController@edit');
            // 部门管理
            $router->any('depart-lists', 'DepartmentController@lists');
            $router->any('depart-edit', 'DepartmentController@edit');
            $router->any('depart-delete', 'DepartmentController@delete');
            //  员工管理
            $router->any('depart-user-lists', 'DepartmentController@userLists');
            $router->any('depart-user-privilege', 'DepartmentController@userPrivilege');
            $router->any('depart-user-edit', 'DepartmentController@userEdit');
            $router->any('depart-user-delete', 'DepartmentController@userDelete');
            //权限集合管理 权限角色管理
            $router->any('privilege-lists', 'PrivilegeController@lists');
            $router->any('privilege-permission', 'PrivilegeController@permission');
            $router->any('privilege-work', 'PrivilegeController@work');
            $router->any('privilege-node', 'PrivilegeController@node');
            $router->any('privilege-contact', 'PrivilegeController@contact');
            $router->any('privilege-edit', 'PrivilegeController@edit');
            $router->any('privilege-delete', 'PrivilegeController@delete');
            //注册用户管理
            $router->any('webuser-lists', 'UserController@lists');
            $router->any('webuser-edit', 'UserController@edit');
            $router->any('webuser-delete', 'UserController@delete');
            $router->any('webuser-set', 'UserController@set');
            //地址
            $router->any('address-lists', 'AddressController@lists');
            $router->any('address-edit', 'AddressController@edit');
            $router->any('address-ajaxAreaList', 'AddressController@ajaxAreaList');
            $router->any('address-delete', 'AddressController@delete');
            //物流
            $router->any('logistics-lists', 'LogisticsController@lists');
            $router->any('logistics-edit', 'LogisticsController@edit');
            $router->any('logistics-delete', 'LogisticsController@delete');
            $router->any('notes-list', 'LogisticsController@notesList');
            $router->any('notes-edit', 'LogisticsController@notesEdit');

            //员工-采购员-权限管理
            $router->any('userPurchaseManage', 'DepartmentController@userPurchaseManage');
            $router->any('setUserPurchaseManage', 'DepartmentController@setUserPurchaseManage');

        });
    }
}