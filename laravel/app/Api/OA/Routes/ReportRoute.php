<?php
/**
 * Created by PhpStorm.
 * User: LuLingFeng
 * Date: 2018/1/3
 * Time: 19:18
 */
namespace App\Api\OA\Routes;

use Dingo\Api\Routing\Router;

class ReportRoute
{
    public function map(Router $router)
    {
        $router->group(['namespace' => 'Report\Controllers', 'prefix' => 'report'], function ($router) {
            // 获取日志模块
            $router->post('permission', 'ReportController@permission');
            // 提交报告
            $router->post('post', 'ReportController@post');
        });
    }
}