<?php
/**
 * Created by PhpStorm.
 * Author: Sojo
 * Date: 2017/2/16
 * Time: 19:33
 */
namespace App\Http\Admin\Routes;

use Illuminate\Contracts\Routing\Registrar;

class UserRoute
{
    public function map(Registrar $router)
    {
        $router->group(['namespace' => 'User\Controllers', 'prefix' => 'user'], function ($router) {
            // 用户管理
            $router->get('list', 'IndexController@getUserList');
            $router->post('list', 'IndexController@getUserList');
            $router->post('save', 'IndexController@saveUser');
            $router->post('delete', 'IndexController@delUser');
            // 修改密码
            $router->get('change-password', 'IndexController@changePassword');
            $router->post('change-password', 'IndexController@changePassword');
            // 角色管理
            $router->get('role-assignment', 'IndexController@roleAssignment');
            $router->post('role-assignment', 'IndexController@roleAssignment');
            $router->post('save-role-assignment', 'IndexController@saveRoleAssignment');
            //角色权限
            $router->get('authority-management', 'IndexController@authorityManagement');
            $router->post('authority-management', 'IndexController@authorityManagement');
            $router->post('save-role', 'IndexController@saveRole');
            $router->post('del-role', 'IndexController@delRole');
            //分配权限
            $router->get('permission-assignment', 'IndexController@permissionAssignment');
            $router->post('permission-assignment', 'IndexController@permissionAssignment');
            $router->post('save-permission-assignment', 'IndexController@savePermissionAssignment');
        });
    }
}