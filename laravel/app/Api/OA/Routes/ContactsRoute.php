<?php
/**
 * Created by PhpStorm.
 * User: wenwenbin
 * Date: 2018/1/8
 * Time: 18:18
 */
namespace App\Api\OA\Routes;

use Dingo\Api\Routing\Router;

class ContactsRoute
{
    public function map(Router $router)
    {
        $router->group(['namespace' => 'Contacts\Controllers', 'prefix' => 'contacts'], function ($router) {
            $router->post('get-data', 'ContactsController@addressList');
            $router->post('company-framework', 'ContactsController@getCompanyFramework');

            // 获取用户公司列表
            $router->post('get-user-company-list', 'ContactsController@getUserCompanyList');
            // 设置用户当前公司
            $router->post('set-use-company', 'ContactsController@setUseCompany');
        });
    }
}