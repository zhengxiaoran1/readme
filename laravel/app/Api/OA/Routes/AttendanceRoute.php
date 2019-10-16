<?php
/**
 * Created by PhpStorm.
 * User: LuLingFeng
 * Date: 2018/1/3
 * Time: 19:18
 */
namespace App\Api\OA\Routes;

use Dingo\Api\Routing\Router;

class AttendanceRoute
{
    public function map(Router $router)
    {
        $router->group(['namespace' => 'Attendance\Controllers', 'prefix' => 'attendance'], function ($router) {
            // 打卡
            $router->post('check-in', 'AttendanceController@CheckIn');
            // 当前打卡状态
            $router->post('check-in-status', 'AttendanceController@CheckInStatus');
            // 当前打卡状态
            $router->any('test', 'AttendanceController@test');
        });
    }
}