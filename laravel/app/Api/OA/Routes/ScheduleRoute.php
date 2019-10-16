<?php
/**
 * Created by PhpStorm.
 * User: wenwenbin
 * Date: 2018/1/8
 * Time: 18:18
 */
namespace App\Api\OA\Routes;

use Dingo\Api\Routing\Router;

class ScheduleRoute
{
    public function map(Router $router)
    {
        $router->group(['namespace' => 'Schedule\Controllers', 'prefix' => 'schedule'], function ($router) {
            $router->post('data', 'ScheduleController@data');
            $router->post('create', 'ScheduleController@create');
            $router->post('edit', 'ScheduleController@edit');
            $router->post('delete', 'ScheduleController@delete');
        });
    }
}