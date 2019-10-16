<?php
/**
 * Created by PhpStorm.
 * User: sxy
 * Date: 2017/12/13
 * Time: 11:03
 */

namespace App\Api\OA\ConferenceRoom\Models;

use Framework\BaseClass\Api\Model;
use App\Eloquent\Oa\ConferenceRoom as EloquentConferenceRoom;
use App\Eloquent\Oa\ConferenceRoomRecord as EloquentConferenceRoomRecord;
use Illuminate\Support\Facades\DB;
class ConferenceRoom extends Model
{
    /**
     * @Author sxy
     * 获取会议室列表
     * @return mixed
     */
    public function getConferenceRoomList($oa_company_id)
    {

        $data = EloquentConferenceRoom::select('id', 'name')
            ->where('oa_company_id','=',$oa_company_id)->where('end_time','<',time())->get();

        return $data;
    }

    /**
     * @Author sxy
     * 预约会议室
     * @return mixed
     */
    public function conferenceReservation($id,$reservations,$reservations_phone,$start_time,$end_time,$remarks,$user_id)
    {
        DB::beginTransaction();
        $conferenceRoomRecord = EloquentConferenceRoomRecord::insert([
            'user_id'=>$user_id,
            'meeting_room_id'=>$id,
            'reservations'=>$reservations,
            'reservations_phone'=>$reservations_phone,
            'start_time'=>$start_time,
            'end_time'=>$end_time,
            'remarks'=>$remarks,
            'created_at' => time(),
            'updated_at' => time()
        ]);
        if ($conferenceRoomRecord == false) {
            DB::rollBack();
            return false;
        }

        $conferenceRoom=EloquentConferenceRoom::where('id','=',$id)->update(['start_time' => $start_time,'end_time'=>$end_time]);
        if ($conferenceRoom == false) {
            DB::rollBack();
            return false;
        }

        DB::commit();
        return true;
    }



    /**
     * @Author sxy
     * 获取会议室预约记录
     * @return mixed
     */
    public function getConferenceRoomRecord($user_id)
    {

        $data = EloquentConferenceRoomRecord::where('user_id','=',$user_id)->get();

        return $data;
    }


}