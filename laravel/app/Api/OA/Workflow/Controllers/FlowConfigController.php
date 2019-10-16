<?php
/**
 * Created by PhpStorm.
 * User: sojo
 * Date: 2018/1/5
 * Time: 20:05
 */

namespace App\Api\OA\Workflow\Controllers;

use App\Api\OA\Workflow\Models\FlowConfig;
use Framework\BaseClass\Api\Controller;

class FlowConfigController extends Controller
{
    public function getFieldList()
    {
        ////易管通项目获取方式不同
//        $userInfo = app('token')->checkToken();

        $userId = \App\Engine\Func::getHeaderValueByName('userid');
        $userInfo = \App\Eloquent\Ygt\DepartmentUser::getCurrentInfo($userId)->toArray();
        $userInfo['oa_contacts_id'] = $userId;


        $params = $this->getRequestParameters(['flow_id']);
        $params['company_id'] = $userInfo['company_id'];
        $model = new FlowConfig();

        $result = $model->getFieldList($userInfo['oa_contacts_id'], $params['flow_id'], $params['company_id']);


        return $result;
    }
}