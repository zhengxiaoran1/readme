<?php
/**
 * Created by PhpStorm.
 * Author: Sxy
 * Date: 2017/12/12
 * Time: 15:36
 */

namespace App\Api\OA\Routes;

use Dingo\Api\Routing\Router;

class NewsPolicyRoute
{
    public function map(Router $router)
    {
        $router->group(['namespace' => 'NewsPolicy\Controllers', 'prefix' => 'news-policy'], function ($router) {
            $router->post('home-list', 'NewsPolicyController@homeNewsPolicyList');//获取首页新闻政策列表
            $router->post('list', 'NewsPolicyController@newsPolicyList');//获取新闻政策列表
            $router->post('detail', 'NewsPolicyController@newsPolicyDetail');//获取新闻政策详情
        });
    }
}