<?php
/**
 * Created by PhpStorm.
 * User: LuLingFeng
 * Date: 2018/1/3
 * Time: 19:28
 */
namespace App\Api\OA\Attendance\Models;

use App\Eloquent\Oa\AttendanceStatistics;
use App\Http\Admin\Personnel\Models\AttendanceFactor;
use Framework\BaseClass\Eloquent\Model;
use App\Eloquent\Oa\AttendanceExceptionDate;
use App\Eloquent\Oa\AttendanceLog;
use App\Eloquent\Oa\AttendanceRule;
use App\Eloquent\Oa\Contacts;

class Attendance extends Model
{
    public function checkInStatus($contactsId, $lat, $lng, $isInternalDataNeeded = false)
    {
        $contacts = Contacts::with(['departmentInfo', 'companyInfo'])->find($contactsId);
        if (empty($contacts)) xThrow(ERR_API_ATTENDANCE_CONTACTS_NOT_READY);
        $time = time();

        // 测试代码
//        $time = strtotime('2018-01-06 13:00');
//        $lat = 1;
//        $lng = 1;

        $dayStartTime = strtotime(date('Y-m-d', $time));
        $timeOfDay = $time - $dayStartTime;
        $data = [
            'name'            => $contacts['name'],
            'department_name' => $contacts['departmentInfo']['name'],
            'company_name'    => $contacts['companyInfo']['name'],
            'date'            => date('Y-m-d', $time),
            'time_of_day'     => \App\Http\Admin\Personnel\Models\Attendance::timeToStr($timeOfDay),
        ];

        $rule = AttendanceRule::where('oa_company_id', '=', $contacts['oa_company_id'])
            ->where('oa_department_id', '=', $contacts['oa_department_id'])
            ->where('start_time', '<=', $time)
            ->where('end_time', '>=', $time)
            ->first();
        if (empty($rule)) xThrow(ERR_API_ATTENDANCE_RULE_NOT_READY);

        // 规则检查
        $dayOfWeek = date('N', $time);
        if ($dayOfWeek == 7 || ($dayOfWeek == 6 && $rule['is_sat_workday'] === 0)) {
            $isCheckInNeeded = 0;
            $checkInIntro = '休息日';
            $checkInRemark = '休息日, 无需打卡';
        } else {
            $isCheckInNeeded = 1;
            $checkInIntro = '工作日';
            $checkInRemark = '工作日';
        }

        // 检查特殊日, 覆盖默认规则
        $exception = AttendanceExceptionDate::where('oa_department_id', '=', $contacts['oa_department_id'])
            ->where('oa_company_id', '=', $contacts['oa_company_id'])
            ->where('time', '=', $time)
            ->orderBy('oa_contacts_id', 'asc')   // 优先使用部门特殊日
            ->get();
        if ($exception->count() !== 0) {
            /*
             * 是特殊日, 采用特殊日信息
             */
            $isCheckInNeeded = $exception[0]['is_workday'];
            $checkInIntro = $exception[0]['remark'];
            $checkInRemark = $exception[0]['remark'] . ', 无需打卡';
        }

        /*
         * 检查其他因素, 不覆盖
         */
        $fm = new AttendanceFactor();
        // 检查请假
        if ($isCheckInNeeded) {
            $check = $fm->checkLeave($contactsId, $time);
            if ($check['is_on']) {
                $isCheckInNeeded = 0;
                $checkInIntro = '请假中';
                $checkInRemark = '请假中,无需打卡';
            }
        }

        // 检查出差()
        if ($isCheckInNeeded) {
            $check = $fm->checkBusinessTrip($contactsId, $time);
            if ($check['is_on']) {
                $isCheckInNeeded = 0;
                $checkInIntro = '出差中';
                $checkInRemark = '出差中,无需打卡';
            }
        }

        // 检查加班
        if ($isCheckInNeeded) {
            $check = $fm->checkOvertime($contactsId, $time);
            if ($check['is_on']) {
                $isCheckInNeeded = 0;
                $checkInIntro = '加班中';
                $checkInRemark = '加班中,无需打卡';
            }
        }

        $clockInData = [
            'supposed_time'  => \App\Http\Admin\Personnel\Models\Attendance::timeToStr($rule['clock_in_time']),
            'time'  => '无',
            'is_done' => 0,
            'result_str' => '',
            'address' => '无',
        ];
        $clockOutData = [
            'supposed_time' => \App\Http\Admin\Personnel\Models\Attendance::timeToStr($rule['clock_out_time']),
            'time' => '无',
            'is_done' => 0,
            'result_str' => '',
            'address' => '无',
        ];
        $checkInStatus = 0; // 无需打卡
        $checkInStatusRemark = '';
        if ($isCheckInNeeded) {
            $logList = AttendanceLog::where('oa_contacts_id', '=', $contacts['id'])
                ->where('oa_department_id', '=', $contacts['oa_department_id'])
                ->where('oa_company_id', '=', $contacts['oa_company_id'])
                ->where('log_time', '>', $dayStartTime)
                ->where('log_time', '<', $dayStartTime + 86400)
                ->where('result_type', '>', 0) // 有效打卡
                ->orderBy('log_time', 'asc')
                ->get();

            foreach ($logList as $log) {
                // 早晚打卡都有了, 退出
                if ($clockInData['is_done'] === 1 && $clockOutData['is_done'] === 1) break;
                // 检查早打卡
                if (in_array($log['result_type'], \App\Http\Admin\Personnel\Models\AttendanceLog::LOG_RESULT_TYPE_CLOCK_IN)) {
                    $clockInData['is_done'] = 1;
                    $clockInData['time'] = date('H:i:s', $log['log_time']);
                    $clockInData['result_str'] = $log['result_str'];
                    $clockInData['address'] = $log['address'];
                }
                // 检查晚打卡
                if (in_array($log['result_type'], \App\Http\Admin\Personnel\Models\AttendanceLog::LOG_RESULT_TYPE_CLOCK_OUT)) {
                    $clockOutData['is_done'] = 1;
                    $clockOutData['time'] = date('H:i:s', $log['log_time']);
                    $clockOutData['result_str'] = $log['result_str'];
                    $clockOutData['address'] = $log['address'];
                }
            }

            if ($timeOfDay < $rule['clock_in_start_time']) {
                // 早打卡之前
                $checkInStatus = -1;
                $checkInStatusRemark = '未到早打卡时间';
            } elseif ($timeOfDay < $rule['clock_in_last_time']) {
                // 早打卡时间段
                if ($clockInData['is_done'] === 1) {
                    // 早打卡已完成, 可以晚打卡
                    if ($clockOutData['is_done'] === 1) {
                        // 晚打卡也完成了
                        $checkInStatus = 2; // 晚打卡
                        $checkInStatusRemark = '您已完成今日打卡';
                    } else {
                        $checkInStatus = 2; // 晚打卡
                        $checkInStatusRemark = '晚打卡'; // 晚打卡
                    }
                } else {
                    $checkInStatus = 1; // 早打卡
                    $checkInStatusRemark = '早打卡';
                }
            } elseif ($timeOfDay < $rule['clock_out_last_time']) {
                if ($clockInData['is_done'] === 0) {
                    $clockInData['is_done'] = 1;
                    $clockInData['result_str'] = '漏打';
                }
                // 晚打卡时间段
                if ($clockOutData['is_done'] === 1) {
                    // 晚打卡已完成
                    $checkInStatus = 2; // 晚打卡
                    $checkInStatusRemark = '您已完成今日打卡';
                } else {
                    //晚打卡未完成
                    $checkInStatus = 2; // 晚打卡
                    $checkInStatusRemark = '晚打卡'; // 晚打卡
                }
            } else {
                // 晚打卡之后
                $checkInStatus = -2;
                if ($clockInData['is_done'] === 0) {
                    $clockInData['is_done'] = 1;
                    $clockInData['result_str'] = '漏打';
                }
                if ($clockOutData['is_done'] === 0) {
                    $clockOutData['is_done'] = 1;
                    $clockOutData['result_str'] = '漏打';
                }
                $checkInStatusRemark = '今日打卡已结束';
            }
        }

        /*
         * 打卡检查
         */
        $isCheckInReady = 1;
        $checkInReadyRemark = '打卡';
        $distance = 0;
        if ($checkInStatus !== 0) {
            // 检查地理位置
            $company = $contacts['companyInfo'];
            $latitude = $company['latitude'];
            $longitude = $company['longitude'];
            $distance = round(6378.138*2*asin(sqrt(pow(sin(($lat*pi()/180-$latitude*pi()/180)/2),2)+cos($lat*pi()/180)*COS($latitude*pi()/180)*pow(sin(($lng*pi()/180-$longitude*pi()/180)/2),2)))*1000);
            if ($distance > $rule['check_in_distance']) {
//                $isCheckInReady = 0;
//                $checkInReadyRemark = '超过打卡距离';

                $checkInReadyRemark = '外勤打卡';
            }
        }

        $checkInData = [
            'check_in_status'        => $checkInStatus,
            'check_in_status_remark' => $checkInStatusRemark,
            'is_check_in_ready'      => $isCheckInReady,
            'check_in_ready_remark'  => $checkInReadyRemark,
            'distance'               => $distance,
            'clock_in_data'          => $clockInData,
            'clock_out_data'         => $clockOutData,
        ];

        $data = array_merge($data, [
            'is_check_in_needed' => $isCheckInNeeded,
            'check_in_remark'    => $checkInRemark,
            'check_in_data'      => $checkInData,
            'distance'           => $distance
        ]);

        // 暂时, 考勤组名
        $data['check_in_group_name'] = 'test';
        $data['avatar_url'] = '';

        if ($isInternalDataNeeded) {
            $data['contacts'] = $contacts;
            $data['rule'] = $rule;
            $data['distance'] = $distance;
            $data['checkInStatus'] = $checkInStatus;
            $data['timeData'] = [
                'time'         => $time,
                'timeOfDay'    => $timeOfDay,
                'dayStartTime' => $dayStartTime
            ];
        } else {
            // 重新格式化
            $checkInArr = [];
            if ($isCheckInNeeded === 0) {
                $checkInArr[] = $data['check_in_data']['clock_in_data'];
                $checkInArr[0]['check_in_status_remark'] = $checkInIntro;
                $checkInArr[0]['is_check_in_ready'] = 0;
                $checkInArr[0]['check_in_ready_remark'] = $data['check_in_remark'];
            } elseif ($checkInStatus === 1 || $checkInStatus === -1) {
                $checkInArr[] = $data['check_in_data']['clock_in_data'];
                $checkInArr[0]['check_in_status_remark'] = '早打卡';
                if ($checkInStatus === -1) {
                    $checkInArr[0]['is_check_in_ready'] = 0;
                    $checkInArr[0]['check_in_ready_remark'] = $data['check_in_data']['check_in_status_remark'];
                } else {
                    $checkInArr[0]['is_check_in_ready'] = $data['check_in_data']['is_check_in_ready'];
                    $checkInArr[0]['check_in_ready_remark'] = $data['check_in_data']['check_in_ready_remark'];
                }
            } elseif ($checkInStatus === 2 || $checkInStatus === -2) {
                $checkInArr[] = $data['check_in_data']['clock_in_data'];
                $checkInArr[] = $data['check_in_data']['clock_out_data'];
                $checkInArr[0]['check_in_status_remark'] = '早打卡';
                $checkInArr[0]['is_check_in_ready'] = 0;
                $checkInArr[0]['check_in_ready_remark'] = '不在早打卡时间';
                $checkInArr[1]['check_in_status_remark'] = '晚打卡';
                if ($checkInStatus === -2) {
                    $checkInArr[1]['is_check_in_ready'] = 0;
                    $checkInArr[1]['check_in_ready_remark'] = $data['check_in_data']['check_in_status_remark'];
                } else {
                    $checkInArr[1]['is_check_in_ready'] = $data['check_in_data']['is_check_in_ready'];
                    $checkInArr[1]['check_in_ready_remark'] = $data['check_in_data']['check_in_ready_remark'];
                }
            }
            $data['check_in_data'] = $checkInArr;
        }

        return $data;
    }

    public function checkIn($contactsId, $lat, $lng, $remark = '')
    {
        $log = AttendanceLog::where('oa_contacts_id', '=', $contactsId)
            ->orderBy('log_time', 'desc')
            ->first();
        if (!empty($log) && (time() - $log['log_time']) < 15) {
            xThrow(ERR_API_ATTENDANCE_CHECK_IN_FAILED, '15秒内不可重复打卡');
        }

        $status = $this->checkInStatus($contactsId, $lat, $lng, true);
        if ($status['is_check_in_needed'] === 0)
            xThrow(ERR_API_ATTENDANCE_CHECK_IN_FAILED, $status['check_in_remark']);
        if ($status['check_in_data']['check_in_status'] <= 0)
            xThrow(ERR_API_ATTENDANCE_CHECK_IN_FAILED, $status['check_in_data']['check_in_status_remark']);
        if ($status['check_in_data']['is_check_in_ready'] === 0)
            xThrow(ERR_API_ATTENDANCE_CHECK_IN_FAILED, $status['check_in_data']['check_in_ready_remark']);

        /**
         * @var $contacts
         * @var $timeData
         * @var $rule
         * @var $checkInStatus
         */
        extract($status);

        /**
         * @var $timeOfDay
         * @var $time
         * @var $dayStartTime
         */
        extract($timeData);

        // 计算打卡结果
        $resultType = 0;
        $resultStr = '';
        $addToStatistics = [
            'clock_in_on_time'  => 0,
            'clock_in_late'     => 0,
            'clock_in_absence'  => 0,
            'clock_out_early'   => 0,
            'clock_out_on_time' => 0,
            'field_check_in' => 0,
            'patch_check_in' => 0,
        ];
        if ($checkInStatus === 1) {
            // 早打卡
            if ($timeOfDay > $rule['clock_in_start_time'] && $timeOfDay <= $rule['clock_in_time']) {
                $resultType = \App\Http\Admin\Personnel\Models\AttendanceLog::LOG_RESULT_TYPE_CLOCK_IN_ON_TIME;
                $resultStr = '早打卡准时';
                $addToStatistics['clock_in_on_time'] = 1;
            } elseif ($timeOfDay > $rule['clock_in_time'] && $timeOfDay <= $rule['absence_time']) {
                $resultType = \App\Http\Admin\Personnel\Models\AttendanceLog::LOG_RESULT_TYPE_CLOCK_IN_LATE;
                $resultStr = '早打卡迟到';
                $addToStatistics['clock_in_late'] = 1;
            } elseif ($timeOfDay > $rule['absence_time'] && $timeOfDay <= $rule['clock_in_last_time']) {
                $resultType = \App\Http\Admin\Personnel\Models\AttendanceLog::LOG_RESULT_TYPE_CLOCK_IN_ABSENCE;
                $resultStr = '早打卡旷工';
                $addToStatistics['clock_in_absence'] = 1;
            } else {
                xThrow(ERR_UNKNOWN, '早打卡失败');
            }
        } elseif ($checkInStatus === 2) {
            if ($timeOfDay < $rule['clock_out_time']) {
                $resultType = \App\Http\Admin\Personnel\Models\AttendanceLog::LOG_RESULT_TYPE_CLOCK_OUT_EARLY;
                $resultStr = '晚打卡早退';
                $addToStatistics['clock_out_early'] = 1;
            } elseif ($timeOfDay >= $rule['clock_out_time'] && $timeOfDay <= $rule['clock_out_last_time']) {
                $resultType = \App\Http\Admin\Personnel\Models\AttendanceLog::LOG_RESULT_TYPE_CLOCK_OUT_ON_TIME;
                $resultStr = '晚打卡准时';
                $addToStatistics['clock_out_on_time'] = 1;
            } else {
                xThrow(ERR_UNKNOWN, '晚打卡失败');
            }
        } else {
            xThrow(ERR_UNKNOWN, '打卡失败');
        }

        $checkInType = \App\Http\Admin\Personnel\Models\AttendanceLog::LOG_CHECK_IN_TYPE_NORMAL;
        $checkInRelateId = 0;

        // 外勤打卡,检查地理位置
        $company = $contacts['companyInfo'];
        $latitude = $company['latitude'];
        $longitude = $company['longitude'];
        $distance = round(6378.138*2*asin(sqrt(pow(sin(($lat*pi()/180-$latitude*pi()/180)/2),2)+cos($lat*pi()/180)*COS($latitude*pi()/180)*pow(sin(($lng*pi()/180-$longitude*pi()/180)/2),2)))*1000);
        if ($distance > $rule['check_in_distance']) {
            $checkInType = \App\Http\Admin\Personnel\Models\AttendanceLog::LOG_CHECK_IN_TYPE_FIELD;
            $addToStatistics['field_check_in'] += 1;
        }

        // 记录打卡结果
        $log = new AttendanceLog([
            'oa_contacts_id'     => $contacts['id'],
            'oa_department_id'   => $contacts['oa_department_id'],
            'oa_company_id'      => $contacts['oa_company_id'],
            'type'               => $checkInStatus,
            'log_time'           => $time,
            'relate_id'          => $rule['id'],
            'check_in_type'      => $checkInType,
            'check_in_relate_id' => $checkInRelateId,
            'result_type'        => $resultType,
            'result_str'         => $resultStr,
            'latitude'           => $lat,
            'longitude'          => $lng,
            'address'            => $this->getAddress($lat . ',' . $lng),
            'remark'             => $remark
        ]);
        try {
            \DB::beginTransaction();
            xAssert($log->save());

            $monthStartTime = strtotime(date('Y-m', $time) . '-01');
            $yearStartTime = strtotime(date('Y', $time) . '-01-01');
            $dayStartTime = strtotime(date('Y-m-d', $time));
            // 添加统计信息
            $whereData = [
                'oa_contacts_id' => $contacts['id'],
                'oa_department_id' => $contacts['oa_department_id'],
                'oa_company_id' => $contacts['oa_company_id'],
                'time' => $monthStartTime,
                'type' => \App\Http\Admin\Personnel\Models\Attendance::ATTENDANCE_STATISTICS_TYPE_MONTH
            ];
            $monthStatistics = AttendanceStatistics::firstOrCreate($whereData);
            $whereData['time'] = $yearStartTime;
            $whereData['type'] = \App\Http\Admin\Personnel\Models\Attendance::ATTENDANCE_STATISTICS_TYPE_YEAR;
            $yearStatistics = AttendanceStatistics::firstOrCreate($whereData);
            $whereData['time'] = $dayStartTime;
            $whereData['type'] = \App\Http\Admin\Personnel\Models\Attendance::ATTENDANCE_STATISTICS_TYPE_DAY;
            $dayStatistics = AttendanceStatistics::firstOrCreate($whereData);
            $monthStatistics = $this->addToStatistics($monthStatistics, $addToStatistics);
            $yearStatistics = $this->addToStatistics($yearStatistics, $addToStatistics);
            $dayStatistics = $this->addToStatistics($dayStatistics, $addToStatistics);
            $monthStatistics->save();
            $yearStatistics->save();
            $dayStatistics->save();

            \DB::commit();
        } catch (\Throwable $e) {
            \DB::rollBack();
            throw $e;
        }

        return $this->checkInStatus($contactsId, $lat, $lng);
    }

    /**
     * 补签
     * @author LuLingFeng
     * @param int $contactsId 联系人id
     * @param int $time 时间戳
     * @param int $type 1/2 早打卡 晚打卡
     * @param string $remark  可选, 备注
     * @param null|object $contacts 可选, 联系人对象
     * @return bool
     * @throws \Throwable
     */
    public function checkInPatch($contactsId, $time, $type, $remark = '', $contacts = null)
    {
        /*
         * 检查
         */
        // 检查id
        if (empty($contacts)) {
            $contacts = Contacts::find($contactsId);
            if (empty($contacts)) return false;
        }

        // 检查打卡类型
        if (!in_array($type, [
            \App\Http\Admin\Personnel\Models\AttendanceLog::LOG_TYPE_CLOCK_IN,
            \App\Http\Admin\Personnel\Models\AttendanceLog::LOG_TYPE_CLOCK_OUT
        ])) return false; // 不是早打卡也不是晚打卡

        // 检查是否需要打卡
        // 查找规则
        $rule = AttendanceRule::where('oa_company_id', '=', $contacts['oa_company_id'])
            ->where('oa_department_id', '=', $contacts['oa_department_id'])
            ->where('start_time', '<=', $time)
            ->where('end_time', '>=', $time)
            ->first();
        if (empty($rule)) return false; // 没规则不需要打卡

        // 检查特殊日
        $exception = AttendanceExceptionDate::where('oa_department_id', '=', $contacts['oa_department_id'])
            ->where('oa_company_id', '=', $contacts['oa_company_id'])
            ->where('time', '=', $time)
            ->orderBy('oa_contacts_id', 'asc')   // 优先使用部门特殊日
            ->get();
        if ($exception->count() !== 0) {
            // 是特殊日, 直接采用特殊日信息
            $isCheckInNeeded = $exception[0]['is_workday'];
        } else {
            // 非特殊日, 根据设置来
            // 规则检查
            $dayOfWeek = date('N', $time);
            if ($dayOfWeek == 7 || ($dayOfWeek == 6 && $rule['is_sat_workday'] === 0)) {
                $isCheckInNeeded = 0;
            } else {
                $isCheckInNeeded = 1;
            }
        }
        // 无需打卡退出
        if (!$isCheckInNeeded) return false;

        /*
         * 打卡
         */
        // 设置时间
        $dayStartTime = strtotime(date('Y-m-d', $time));
        $timeOfDay = $time - $dayStartTime;

        // 添加到统计
        $addToStatistics = [
            'clock_in_on_time'  => 0,
            'clock_in_late'     => 0,
            'clock_in_absence'  => 0,
            'clock_out_early'   => 0,
            'clock_out_on_time' => 0,
            'field_check_in'    => 0,
            'patch_check_in'    => 1,   // 补签
        ];

        // 获取当天对应类型现存的打卡日志
        $logList = AttendanceLog::where('oa_contacts_id', '=', $contacts['id'])
            ->where('log_time', '>', $dayStartTime)
            ->where('log_time', '<', $dayStartTime + 86400)
            ->where('type', '=', $type)
            ->where('result_type', '>', 0) // 有效打卡
            ->get();
        if (!empty($logList)) {
            $logList->each(function ($item, $key) use (&$addToStatistics){
                // 去除原统计也要对应删除
                switch ($item['result_type']) {
                    case \App\Http\Admin\Personnel\Models\AttendanceLog::LOG_RESULT_TYPE_CLOCK_IN_ON_TIME:
                        $addToStatistics['clock_in_on_time'] += -1;
                        break;
                    case \App\Http\Admin\Personnel\Models\AttendanceLog::LOG_RESULT_TYPE_CLOCK_IN_LATE:
                        $addToStatistics['clock_in_late'] += -1;
                        break;
                    case \App\Http\Admin\Personnel\Models\AttendanceLog::LOG_RESULT_TYPE_CLOCK_IN_ABSENCE:
                        $addToStatistics['clock_in_absence'] += -1;
                        break;
                    case \App\Http\Admin\Personnel\Models\AttendanceLog::LOG_RESULT_TYPE_CLOCK_OUT_ON_TIME:
                        $addToStatistics['clock_out_on_time'] += -1;
                        break;
                    case \App\Http\Admin\Personnel\Models\AttendanceLog::LOG_RESULT_TYPE_CLOCK_OUT_EARLY:
                        $addToStatistics['clock_out_early'] += -1;
                        break;
                }
                // 作废原来的打卡(软删除
                $item->delete();
            });
        }

        if ($type == \App\Http\Admin\Personnel\Models\AttendanceLog::LOG_TYPE_CLOCK_IN){
            $resultType = \App\Http\Admin\Personnel\Models\AttendanceLog::LOG_RESULT_TYPE_CLOCK_IN_ON_TIME;
            $resultStr = '早打卡准时';
        } elseif ($type == \App\Http\Admin\Personnel\Models\AttendanceLog::LOG_TYPE_CLOCK_OUT){
            $resultType = \App\Http\Admin\Personnel\Models\AttendanceLog::LOG_RESULT_TYPE_CLOCK_OUT_ON_TIME;
            $resultStr = '晚打卡准时';
        } else return false;

        $checkInType = \App\Http\Admin\Personnel\Models\AttendanceLog::LOG_CHECK_IN_TYPE_PATCH;
        $checkInRelateId = 0;
        // 记录打卡结果
        $log = new AttendanceLog([
            'oa_contacts_id'     => $contacts['id'],
            'oa_department_id'   => $contacts['oa_department_id'],
            'oa_company_id'      => $contacts['oa_company_id'],
            'type'               => $type,
            'log_time'           => $time,
            'relate_id'          => $rule['id'],
            'check_in_type'      => $checkInType,
            'check_in_relate_id' => $checkInRelateId,
            'result_type'        => $resultType,
            'result_str'         => $resultStr,
            'latitude'           => 0,
            'longitude'          => 0,
            'address'            => '',
            'remark'             => $remark
        ]);
        try {
            \DB::beginTransaction();
            xAssert($log->save());

            if ($type == \App\Http\Admin\Personnel\Models\AttendanceLog::LOG_TYPE_CLOCK_IN){
                $addToStatistics['clock_in_on_time'] += 1;
            } elseif ($type == \App\Http\Admin\Personnel\Models\AttendanceLog::LOG_TYPE_CLOCK_OUT){
                $addToStatistics['clock_out_on_time'] += 1;
            }

            $monthStartTime = strtotime(date('Y-m', $time) . '-01');
            $yearStartTime = strtotime(date('Y', $time) . '-01-01');
            $dayStartTime = strtotime(date('Y-m-d', $time));
            // 添加统计信息
            $whereData = [
                'oa_contacts_id' => $contacts['id'],
                'oa_department_id' => $contacts['oa_department_id'],
                'oa_company_id' => $contacts['oa_company_id'],
                'time' => $monthStartTime,
                'type' => \App\Http\Admin\Personnel\Models\Attendance::ATTENDANCE_STATISTICS_TYPE_MONTH
            ];
            $monthStatistics = AttendanceStatistics::firstOrCreate($whereData);
            $whereData['time'] = $yearStartTime;
            $whereData['type'] = \App\Http\Admin\Personnel\Models\Attendance::ATTENDANCE_STATISTICS_TYPE_YEAR;
            $yearStatistics = AttendanceStatistics::firstOrCreate($whereData);
            $whereData['time'] = $dayStartTime;
            $whereData['type'] = \App\Http\Admin\Personnel\Models\Attendance::ATTENDANCE_STATISTICS_TYPE_DAY;
            $dayStatistics = AttendanceStatistics::firstOrCreate($whereData);
            $monthStatistics = $this->addToStatistics($monthStatistics, $addToStatistics);
            $yearStatistics = $this->addToStatistics($yearStatistics, $addToStatistics);
            $dayStatistics = $this->addToStatistics($dayStatistics, $addToStatistics);
            $monthStatistics->save();
            $yearStatistics->save();
            $dayStatistics->save();

            \DB::commit();
        } catch (\Throwable $e) {
            \DB::rollBack();
            throw $e;
        }
    }

    private function addToStatistics($stat, $data)
    {
        $stat['clock_in_on_time'] += $data['clock_in_on_time'];
        $stat['clock_in_late'] += $data['clock_in_late'];
        $stat['clock_in_absence'] += $data['clock_in_absence'];
        $stat['clock_out_early'] += $data['clock_out_early'];
        $stat['clock_out_on_time'] += $data['clock_out_on_time'];
        $stat['field_check_in'] += $data['field_check_in'];
        $stat['patch_check_in'] += $data['patch_check_in'];
        return $stat;
    }

    public function incrementOnStat($name ,$number, $extra, $time, $contactsId, $departmentId, $companyId)
    {
        $monthStartTime = strtotime(date('Y-m', $time) . '-01');
        $yearStartTime = strtotime(date('Y', $time) . '-01-01');
        $dayStartTime = strtotime(date('Y-m-d', $time));
        // 添加统计信息
        $whereData = [
            'oa_contacts_id'   => $contactsId,
            'oa_department_id' => $departmentId,
            'oa_company_id'    => $companyId,
            'time'             => $monthStartTime,
            'type'             => \App\Http\Admin\Personnel\Models\Attendance::ATTENDANCE_STATISTICS_TYPE_MONTH
        ];
        $monthStatistics = AttendanceStatistics::firstOrCreate($whereData);
        $whereData['time'] = $yearStartTime;
        $whereData['type'] = \App\Http\Admin\Personnel\Models\Attendance::ATTENDANCE_STATISTICS_TYPE_YEAR;
        $yearStatistics = AttendanceStatistics::firstOrCreate($whereData);
        $whereData['time'] = $dayStartTime;
        $whereData['type'] = \App\Http\Admin\Personnel\Models\Attendance::ATTENDANCE_STATISTICS_TYPE_DAY;
        $dayStatistics = AttendanceStatistics::firstOrCreate($whereData);
        $monthStatistics->increment($name, $number, $extra);
        $yearStatistics->increment($name, $number, $extra);
        $dayStatistics->increment($name, $number, $extra);
    }

    public function getAddress($location)
    {
        $coordtype = 'gcj02ll';
        $ak = 'UIySoR6WtbBeQuxPupPfh4GMeZOQOQWs';
        $sk = 'lWeOCGLSEl15rMCNI2k0RMmkN3XkP1Xx';
        $output = 'json';

        //以Geocoding服务为例，地理编码的请求url，参数待填
        $url = "http://api.map.baidu.com/geocoder/v2/?location=%s&coordtype=%s&output=%s&ak=%s&sn=%s";

        //get请求uri前缀
        $uri = '/geocoder/v2/';

        //地理编码的请求output参数
        $output = 'json';

        //构造请求串数组
        $querystring_arrays = array (
            'location' => $location,
            'coordtype' => $coordtype,
            'output' => $output,
            // 'pois'  => 1,
            'ak' => $ak,
        );

        //调用sn计算函数，默认get请求
        $sn = self::calculateAKSN($ak, $sk, $uri, $querystring_arrays);

        //请求参数中有中文、特殊字符等需要进行urlencode，确保请求串与sn对应
        $target = sprintf($url, urlencode($location), $coordtype, $output, $ak, $sn);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $target);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $ret = json_decode(curl_exec($ch), true);
        if ($ret['status'] === 0) {
            return $ret['result']['sematic_description'];
        }
        return '';
    }

    public static function calculateAKSN($ak, $sk, $url, $query, $method = 'GET')
    {
        if ($method === 'POST'){
            ksort($query);
        }
        $queryString = http_build_query($query);
        return md5(urlencode($url.'?'.$queryString.$sk));
    }
}
