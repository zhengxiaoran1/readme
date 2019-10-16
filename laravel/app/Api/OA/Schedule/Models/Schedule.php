<?php
/**
 * Created by PhpStorm.
 * User: wenwenbin
 * Date: 2018/1/6
 * Time: 11:06
 */

namespace App\Api\OA\Schedule\Models;

use Framework\BaseClass\Api\Model;
use App\Eloquent\Oa\Schedule as EloquentSchedule;

class Schedule extends Model
{
    /**
     * 获取日程数据
     * @author wenwenbin
     * @param int $oa_company_id 公司id
     * @param int $oa_contacts_id 联系人id
     * @param string $year 年份
     * @param string $month 月份
     * @param int $page 页码
     * @param int $page_size 页面数据量
     * @return array
     */
    public function data($oa_company_id, $oa_contacts_id, $year, $month, $page, $page_size)
    {
        $start_time = strtotime($year . '-' . $month . '-1');
        $end_time = strtotime("+1 month", $start_time);
        $temp = EloquentSchedule::where([['oa_company_id', $oa_company_id], ['oa_contacts_id', $oa_contacts_id],
            ['start_time', '>=', $start_time], ['end_time', '<', $end_time]]);
        $count = $temp->count();
        if ($page && $page_size) $temp = $temp->offset(($page - 1) * $page_size)->take($page_size);
        $scheduleList = $temp->get(['id', 'start_time', 'end_time', 'title', 'content as decs']);
        foreach ($scheduleList as $schedule) {
            $schedule->date = date('Y-m-d', $schedule->start_time);
            $schedule->startTime = date('H:i:s', $schedule->start_time);
            $schedule->endTime = date('H:i:s', $schedule->end_time);
            $schedule->Remind = 0;
            $schedule->hit = 0;
            $schedule->event_type = 0;
            $schedule->status = '';
            unset($schedule->start_time, $schedule->end_time);
        }
        return $result = ['list' => $scheduleList, 'total' => $count];
    }

    /**
     * 创建日程
     * @author wenwenbin
     * @param int $oa_company_id 公司id
     * @param int $oa_contacts_id 联系人id
     * @param string $start_time 开始时间
     * @param string $end_time 结束时间
     * @param string $title 标题
     * @param string $content 内容
     * @return bool
     */
    public function create($oa_company_id, $oa_contacts_id, $start_time, $end_time, $title, $content)
    {
        if ($oa_company_id == 0 || $oa_contacts_id == 0) xThrow(ERR_OA_FUNCTION_NOT_QUALIFIED);
        $data = new EloquentSchedule();
        $data->fill([
            'oa_company_id' => $oa_company_id,
            'oa_contacts_id' => $oa_contacts_id,
            'start_time' => strtotime($start_time),
            'end_time' => strtotime($end_time),
            'title' => $title ?: '',
            'content' => $content ?: ''
        ]);
        return $data->save();
    }

    /**
     * 编辑日程
     * @author wenwenbin
     * @param int $id 日程id
     * @param int $oa_company_id 公司id
     * @param int $oa_contacts_id 联系人id
     * @param string $start_time 开始时间
     * @param string $end_time 结束时间
     * @param string $title 标题
     * @param string $content 内容
     * @return bool
     */
    public function edit($id, $oa_company_id, $oa_contacts_id, $start_time, $end_time, $title, $content)
    {
        if ($oa_company_id == 0 || $oa_contacts_id == 0) xThrow(ERR_OA_FUNCTION_NOT_QUALIFIED);
        $data = EloquentSchedule::where([['oa_company_id', $oa_company_id], ['oa_contacts_id', $oa_contacts_id]])->find($id);
        if (is_null($data)) xThrow(ERR_OA_SCHEDULE_NOT_EXIST);
        $start_time = strtotime($start_time);
        $end_time = strtotime($end_time);
        $data->fill([
            'start_time' => $start_time,
            'end_time' => $end_time,
            'title' => $title ?: '',
            'content' => $content ?: ''
        ]);
        return $data->save();
    }

    /**
     * 删除日程
     * @author wenwenbin
     * @param int $id 日程id
     * @param int $oa_company_id 公司id
     * @param int $oa_contacts_id 联系人id
     * @return bool|null
     * @throws \Exception
     */
    public function delete($id, $oa_company_id, $oa_contacts_id)
    {
        if ($oa_company_id == 0 || $oa_contacts_id == 0) xThrow(ERR_OA_FUNCTION_NOT_QUALIFIED);
        $data = EloquentSchedule::where([['oa_company_id', $oa_company_id], ['oa_contacts_id', $oa_contacts_id]])->find($id);
        if (is_null($data)) {
            xThrow(ERR_OA_SCHEDULE_NOT_EXIST);
        } else {
            return $data->delete();
        }
    }
}