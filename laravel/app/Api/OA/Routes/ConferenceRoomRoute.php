<?php
/**
 * Created by PhpStorm.
 * Author: Sxy
 * Date: 2017/12/12
 * Time: 15:36
 */

namespace App\Api\OA\Routes;

use Dingo\Api\Routing\Router;

class ConferenceRoomRoute
{
    public function map(Router $router)
    {
        $router->group(['namespace' => 'ConferenceRoom\Controllers', 'prefix' => 'conference-room'], function ($router) {
            $router->post('conference-room-list', 'ConferenceRoomController@conferenceRoomList');//获取会议室列表
            $router->post('reservation', 'ConferenceRoomController@conferenceRoomReservation');//预约会议室

            $router->post('record', 'ConferenceRoomController@getConferenceRoomRecord');//预约会议室
        });
    }
}