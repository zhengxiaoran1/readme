<?php
/**
 * Created by PhpStorm.
 * Author: kaodou
 * Date: 2018/3/1
 * Time: 14:40
 */

namespace App\Http\Admin\Routes;

use Illuminate\Contracts\Routing\Registrar;

class AjaxRoute
{
    public function map(Registrar $router)
    {
        $router->group(['namespace' => 'Ajax\Controllers', 'prefix'=>'ajax'], function ($router) {
            $router->any('address', 'IndexController@address');
        });
    }
}