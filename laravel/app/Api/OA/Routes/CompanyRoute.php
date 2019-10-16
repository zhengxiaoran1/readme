<?php
/**
 * Created by PhpStorm.
 * User: sojo
 * Date: 2018/3/3
 * Time: 18:50
 */
namespace App\Api\OA\Routes;

use Dingo\Api\Routing\Router;

class CompanyRoute
{
    public function map(Router $router)
    {
        $router->group(['namespace' => 'Company\Controllers', 'prefix' => 'company'], function ($router) {
            $router->post('regime-list', 'IndexController@regimeList');
            $router->post('regime-info', 'IndexController@regimeInfo');
        });
    }
}