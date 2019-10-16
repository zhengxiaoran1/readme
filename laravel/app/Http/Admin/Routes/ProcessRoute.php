<?php
/**
 * Created by PhpStorm.
 * Author: zhuyujun
 * Date: 20171020
 * Time: 10:50
 */
namespace App\Http\Admin\Routes;

use Illuminate\Contracts\Routing\Registrar;

class ProcessRoute
{
    public function map(Registrar $router)
    {
        $router->group(['namespace' => 'Process\Controllers','prefix' => 'process'], function ($router) {
            $router->get('/', 'IndexController@index');
            $router->post('/', 'IndexController@index');

            //工序
            $router->get('list', 'IndexController@lists');
            $router->post('list', 'IndexController@lists');
            $router->get('edit', 'IndexController@edit');
            $router->post('edit', 'IndexController@edit');
            $router->get('delete', 'IndexController@delete');
            $router->post('delete', 'IndexController@delete');
            $router->any('copy', 'IndexController@copy');
            //工序动作权限
            $router->get('permission-list', 'PermissionController@lists');
            $router->post('permission-list', 'PermissionController@lists');
            $router->get('permission-edit', 'PermissionController@edit');
            $router->post('permission-edit', 'PermissionController@edit');
            //工序关联字段
            $router->get('field-edit', 'SettingController@fieldEdit');
            $router->post('field-edit', 'SettingController@fieldEdit');
            $router->get('field-manage', 'SettingController@fieldManage');
            $router->get('field-manage-edit', 'SettingController@fieldManageEdit');
            $router->post('field-manage-edit', 'SettingController@fieldManageEdit');
            $router->get('field-manage-delete', 'SettingController@fieldManageDelete');
            $router->post('field-manage-delete', 'SettingController@fieldManageDelete');
            $router->get('field-set', 'SettingController@fieldSet');
            $router->post('field-set', 'SettingController@fieldSet');
            $router->get('field-set-delete', 'SettingController@fieldSetDelete');
            $router->post('field-set-delete', 'SettingController@fieldSetDelete');
            //工序关联工序
            $router->get('process-edit', 'SettingController@processEdit');
            $router->post('process-edit', 'SettingController@processEdit');
            //工序字典
            $router->get('dict-list', 'DictController@lists');
            $router->post('dict-list', 'DictController@lists');
            $router->get('dict-edit', 'DictController@edit');
            $router->post('dict-edit', 'DictController@edit');;
            $router->post('dict-delete', 'DictController@delete');
            $router->get('dict-category', 'DictController@category');
            $router->post('dict-category', 'DictController@category');

            $router->get('dict-category-list', 'DictController@categoryList');
            $router->post('dict-category-list', 'DictController@categoryList');
            $router->post('dict-category-list-ajax', 'DictController@categoryListAjax');

            $router->any('field-category-list', 'CategoryController@lists');
            $router->any('field-category-edit', 'CategoryController@edit');
            $router->any('field-category-delete', 'CategoryController@delete');
            //创建工单第一步
            $router->get('order-dict', 'OrderController@lists');
            $router->post('order-dict', 'OrderController@lists');
            $router->get('order-edit', 'OrderController@edit');
            $router->post('order-edit', 'OrderController@edit');
            $router->post('order-delete', 'OrderController@delete');
//            $router->get('order-set', 'OrderController@companyList');
//            $router->post('order-set', 'OrderController@companyList');
//            $router->post('order-set-edit', 'OrderController@companyEdit');
            //创建工单第一步字段设置
            $router->get('order-field-set', 'OrderFieldController@set');
            $router->post('order-field-set', 'OrderFieldController@set');
            $router->post('order-field-delete', 'OrderFieldController@delete');
            $router->post('order-field-set-delete', 'OrderFieldController@delete');
        });
    }
}
