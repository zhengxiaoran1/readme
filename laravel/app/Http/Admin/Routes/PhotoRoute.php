<?php
/**
 * Created by PhpStorm.
 * Author: kaodou
 * Date: 2017/10/17
 * Time: 14:58
 */
namespace App\Http\Admin\Routes;

use Illuminate\Contracts\Routing\Registrar;

class PhotoRoute
{
    public function map(Registrar $router)
    {
        $router->group(['namespace' => 'Photo\Controllers', 'prefix' => 'photo'], function ($router) {
            $router->any('upload', 'IndexController@upload');
        });
    }
}