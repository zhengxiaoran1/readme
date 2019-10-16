<?php
/**
 * Created by PhpStorm.
 * Author: kaodou
 * Date: 2017/10/20
 * Time: 13:49
 */
namespace App\Http\Admin\Routes;

use Illuminate\Contracts\Routing\Registrar;

class CategoryRoute
{
    public function map(Registrar $router)
    {
        $router->group(['namespace' => 'Category\Controllers', 'prefix' => 'category'], function ($router) {
            // 用户管理
            $router->get('list', 'IndexController@getCategoryList');
            $router->post('list', 'IndexController@getCategoryList');

            $router->post('add', 'IndexController@saveCategory');

            $router->post('delete', 'IndexController@deleteCategory');

            $router->any('fields-list', 'FieldsController@getList');

            $router->any('fields-save', 'FieldsController@save');

            $router->any('fields-del', 'FieldsController@del');

        });
    }
}