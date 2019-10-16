<?php
/**
 * Created by PhpStorm.
 * User: sxy
 * Date: 2017/12/13
 * Time: 11:02
 */

namespace App\Api\OA\ConferenceRoom\Controllers;


use Framework\BaseClass\Api\Controller;
use Psy\Exception\ErrorException;



use App\Api\OA\ConferenceRoom\Models\ConferenceRoom;

class ConferenceRoomController extends Controller
{
    /**
     * @Author sxy
     * $params type id 会议室id reservations 姓名 reservations_phone 电话 start_time 预约起始时间 end_time 预约结束时间 remarks 备注
     * 预约会议室
     * @return array
     */
    public function conferenceRoomReservation()
    {
        //$token = app('token')->checkToken();
        $user_id=1;//todo 用户id
        $params = $this->getRequestParameters(['id','reservations', 'reservations_phone','start_time','end_time','remarks']);
        $rules = [
            'id' => 'integer|min:1',
            'reservations' => 'string|min:1|max:255',
            'reservations_phone' => 'string|min:1|max:255',
            'start_time' => 'integer|min:1',
            'end_time' => 'integer|min:1',
            'remarks' => 'string|min:1|max:2000',
        ];
        $this->validateRequestParameters($params, $rules);

        $model = new ConferenceRoom();
       $data = $model->conferenceReservation( $params['id'], $params['reservations'],$params['reservations_phone'],$params['start_time'],$params['end_time'],$params['remarks'],$user_id);
        xAssert($data);
    }


    /**
     * @Author sxy
     * 获取会议室列表
     * @return array|mixed
     */
    public function conferenceRoomList()
    {
        //$token = app('token')->checkToken();
        $oa_company_id=1;//todo 公司id
        $model = new ConferenceRoom();
        $data = $model->getConferenceRoomList($oa_company_id);
        return $data;
    }


    /**
     * @Author sxy
     * 获取会议室预约记录
     * @return array|mixed
     */
    public function getConferenceRoomRecord()
    {
        //$token = app('token')->checkToken();
        $user_id=1;//todo 用户id
        $model = new ConferenceRoom();
        $data = $model->getConferenceRoomRecord($user_id);
        return $data;
    }



}