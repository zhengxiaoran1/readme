<?php
/**
 * Created by PhpStorm.
 * Author: kaodou
 * Date: 2017/10/17
 * Time: 14:58
 */
namespace App\Http\Admin\Routes;

use Illuminate\Contracts\Routing\Registrar;

class ProductRoute
{
    public function map(Registrar $router)
    {
        $router->group(['namespace' => 'Product\Controllers', 'prefix' => 'pro'], function ($router) {
            // 用户管理
            $router->get('list', 'IndexController@getProductList');
            $router->post('list', 'IndexController@getProductList');

            $router->get('add', 'IndexController@addProduct');
            $router->post('add', 'IndexController@addProduct');

            $router->get('edit', 'IndexController@editProduct');
            $router->post('edit', 'IndexController@editProduct');

            $router->any('del', 'IndexController@delProduct');

            $router->any('attr-list', 'IndexController@attrList');

            $router->any('attr-save', 'IndexController@attrSave');
            
            $router->any('attr-del', 'IndexController@attrDelete');


            //字段模板列表
            $router->any('fields-module-list', 'FieldsController@getList');
            $router->any('fields-module-save', 'FieldsController@save');
            $router->any('fields-module-del', 'FieldsController@del');


            $router->any('supplier', 'SupplierController@listAll');
            $router->any('supplier-set', 'SupplierController@set');

            $router->any('supplier-list', 'SupplierController@getList');

            $router->any('supplier-custom-save', 'SupplierController@customSave');

            $router->any('export', 'IndexController@export');

            //sn码
            $router->any('sn-code-lists', 'SnCodeController@lists');
            $router->any('sn-add', 'SnCodeController@add');
            $router->any('product-list-for-select', 'SnCodeController@productLists');
            $router->any('select-product-ajax', 'SnCodeController@selectProductAjax');
//            $router->any('edit', 'SnCodeController@edit');
//            $router->any('delete', 'SnCodeController@delete');
        });
    }
}