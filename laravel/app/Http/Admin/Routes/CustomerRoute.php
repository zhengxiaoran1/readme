<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/9 0009
 * Time: 下午 3:06
 */

namespace App\Http\Admin\Routes;

use Illuminate\Contracts\Routing\Registrar;

class CustomerRoute
{
    public function map(Registrar $router)
    {
        $router->group(['namespace' => 'Customer\Controllers', 'prefix' => 'customer'], function ($router) {
            $router->any('list', 'IndexController@getList');

            $router->any('set-password', 'IndexController@setPassword');

            $router->any('save', 'IndexController@save');

            $router->any('del', 'IndexController@del');

            $router->any('export', 'IndexController@export');

            $router->any('customEdit', 'IndexController@customEdit');
            $router->any('custom-save', 'IndexController@customSave');

            $router->any('product-list', 'ProductController@getList');

            $router->any('invoice-list', 'InvoiceController@getList');
            $router->any('invoice-save', 'InvoiceController@save');
            $router->any('invoice-del', 'InvoiceController@del');
            $router->any('invoice-custom-save', 'InvoiceController@customSave');

            $router->any('address-list', 'AddressController@getList');
            $router->any('address-save', 'AddressController@save');
            $router->any('address-del', 'AddressController@del');
            $router->any('address-custom-save', 'AddressController@customSave');

                       //结算方式;
            $setFunction = 'ajaxPayFirst';
            $router->any('/'.$setFunction,'IndexController@'.$setFunction);

        });
    }
}