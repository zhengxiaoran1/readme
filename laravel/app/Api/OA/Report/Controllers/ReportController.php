<?php
/**
 * Created by PhpStorm.
 * User: LuLingFeng
 * Date: 2018/1/6
 * Time: 10:29
 */

namespace App\Api\OA\Report\Controllers;

use App\Api\OA\Report\Models\Report;
use Framework\BaseClass\Api\Controller;

class ReportController extends Controller
{

    /**
     * 获取日志模块
     */
    public function permission()
    {
        $token = app('token')->checkToken();
        if ($token['oa_contacts_id'] === 0) xThrow(ERR_OA_FUNCTION_NOT_QUALIFIED);
        $model = new Report();
        $data=$model->getPermission();
        return $data;
    }

    /**
     * 提交日志
     * @return mixed
     */
    public function post()
    {
        $token = app('token')->checkToken();
        if ($token['oa_contacts_id'] === 0) xThrow(ERR_OA_FUNCTION_NOT_QUALIFIED);
        $contactsId = $token['oa_contacts_id'];
        $params = $this->getRequestParameters(['type', 'content'], ['enclosure_url']);
        $params['enclosure_url'] = is_null($params['enclosure_url']) ? '' : $params['enclosure_url'];
        $manager = new Report();
        $report = $manager->post($contactsId, $params['type'], $params['content'], $params['enclosure_url']);
        return $report['id'];
    }
}