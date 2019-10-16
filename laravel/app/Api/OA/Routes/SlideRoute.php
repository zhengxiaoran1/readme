<?php
/**
 * Created by PhpStorm.
 * User: wenwenbin
 * Date: 2018/2/6
 * Time: 18:01
 */
namespace App\Api\OA\Routes;

use Dingo\Api\Routing\Router;

class SlideRoute
{
    public function map(Router $router)
    {
        $router->group(['namespace' => 'Slide\Controllers', 'prefix' => 'slide'], function ($router) {
            // 获取轮播图
            $router->post('slide-list', 'SlideController@slideList');
            // 获取公共模板幻灯片内容
            $router->post('slide-content', 'SlideController@slideContent');
        });
    }
}