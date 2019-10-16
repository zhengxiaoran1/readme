<?php
/**
 * Created by PhpStorm.
 * Author: kaodou
 * Date: 2017/10/17
 * Time: 14:58
 */
namespace App\Http\Admin\Routes;

use Illuminate\Contracts\Routing\Registrar;

class ProcessProductRoute
{
    public function map(Registrar $router)
    {
        $router->group(['namespace' => 'ProcessProduct\Controllers', 'prefix' => 'pro-pro'], function ($router) {
            $router->any('lists', 'IndexController@lists');

            //码设置
            $router->any('code-set-lists', 'CodeSetController@lists');
            $router->any('code-set-add', 'CodeSetController@add');
            $router->any('process-product-list-for-select', 'CodeSetController@processProductLists');

            $router->any('edit', 'CodeSetController@edit');
            $router->any('delete', 'CodeSetController@delete');
        });
    }
}