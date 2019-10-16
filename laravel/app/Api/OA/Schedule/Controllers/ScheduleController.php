<?php
/**
 * Created by PhpStorm.
 * User: wenwenbin
 * Date: 2017/1/8
 * Time: 18:02
 */

namespace App\Api\OA\Schedule\Controllers;

use Framework\BaseClass\Api\Controller;
use App\Api\OA\Schedule\Models\Schedule;

class ScheduleController extends Controller
{
    /**
     * 获取日程数据
     * @author wenwenbin
     * @return array
     */
    public function data()
    {
        $token = app('token')->checkToken();
        $params = $this->getRequestParameters(['year', 'month'], ['page', 'page_size']);
        $params['page'] = isset($params['page']) ? $params['page'] : null;
        $params['page_size'] = isset($params['page_size']) ? $params['page_size'] : null;
        $schedule = new Schedule();
        $result = $schedule->data($token['oa_company_id'], $token['oa_contacts_id'],
            $params['year'], $params['month'], $params['page'], $params['page_size']);
        return $this->pagingData($result['list'], $result['total']);
    }

    /**
     * 创建日程
     * @author wenwenbin
     */
    public function create()
    {
        $token = app('token')->checkToken();
        $params = $this->getRequestParameters(['start_time', 'end_time', 'title', 'content']);
        $schedule = new Schedule();
        $result = $schedule->create($token['oa_company_id'], $token['oa_contacts_id'], $params['start_time'],
            $params['end_time'], $params['title'], $params['content']);
        xAssert($result);
        return;
    }

    /**
     * 编辑日程
     * @author wenwenbin
     */
    public function edit()
    {
        $token = app('token')->checkToken();
        $params = $this->getRequestParameters(['id', 'start_time', 'end_time', 'title', 'content']);
        $schedule = new Schedule();
        $result = $schedule->edit($params['id'], $token['oa_company_id'], $token['oa_contacts_id'],
            $params['start_time'], $params['end_time'], $params['title'], $params['content']);
        xAssert($result);
        return;
    }

    /**
     * 删除日程
     * @author wenwenbin
     */
    public function delete()
    {
        $token = app('token')->checkToken();
        $params = $this->getRequestParameters(['id']);
        $schedule = new Schedule();
        $result = $schedule->delete($params['id'], $token['oa_company_id'], $token['oa_contacts_id']);
        xAssert($result);
        return;
    }
}