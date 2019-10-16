<?php
/**
 * created by zzy
 * date: 2017/10/24 9:28
 */
namespace App\Api\Service\Routes;

use Dingo\Api\Routing\Router;

class UserRoute
{
    public function map(Router $router)
    {
        $router->group(['namespace' => 'User\Controllers', 'prefix'=>'user'], function ($router) {

            $router->any('userSet', 'UserController@userSet');

        });
    }
}