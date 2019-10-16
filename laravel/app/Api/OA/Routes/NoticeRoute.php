<?php
/**
 * Created by PhpStorm.
 * Author: Sxy
 * Date: 2017/12/12
 * Time: 15:36
 */

namespace App\Api\OA\Routes;

use Dingo\Api\Routing\Router;

class NoticeRoute
{
    public function map(Router $router)
    {
        $router->group(['namespace' => 'Notice\Controllers', 'prefix' => 'notice'], function ($router) {
            $router->post('list', 'NoticeController@noticeList');//获取公告列表
            $router->post('detail', 'NoticeController@noticeDetail');//获取公告详情
            $router->post('del', 'NoticeController@noticeDel');//删除公告
        });
    }
}