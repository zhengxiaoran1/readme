<?php
/**
 * Created by PhpStorm.
 * User: sojo
 * Date: 2018/1/5
 * Time: 20:05
 */

namespace App\Api\OA\Workflow\Controllers;

use App\Api\OA\Workflow\Models\Workflow;
use App\Http\Admin\Administration\Models\Flow;
use Framework\BaseClass\Api\Controller;

//易管通
use App\Engine\Func;

class IndexController extends Controller
{
    // 获取列表
    public function getPagingList()
    {

        //易管通获取员工id的方式
//        $token = app('token')->checkToken();
//        if (empty($token['oa_contacts_id'])) xThrow(ERR_OA_FUNCTION_NOT_QUALIFIED);
//        $contactsId = $token['oa_contacts_id'];

        $userId = Func::getHeaderValueByName('userid');
        $contactsId = $userId;

        $params = $this->getRequestParameters(['page', 'page_size', 'scene', 'type','is_read','status']);
        $params['is_read'] = $params['is_read'] ?: 0;

        $workflow = new Workflow();
        list($workflowList, $total) = $workflow->getPagingList($contactsId, $params['scene'], $params['type'], $params['is_read'], $params['page'], $params['page_size'],$params['status']);

        $pagingData         = [
            'page'         => $params['page'],
            'pageSize'     => $params['page_size'],
            'total'        => $total,
            'keyword'       => ''
        ];

        return $this->pagingData($workflowList, $pagingData);

    }

    // 获取详情
    public function getDetails()
    {
//        $token = app('token')->checkToken();

        //易管通获取对应参数
        $userId = \App\Engine\Func::getHeaderValueByName('userid');
        $userInfo = \App\Eloquent\Ygt\DepartmentUser::getCurrentInfo($userId)->toArray();
        $token['oa_contacts_id'] = $userId;
        $token['oa_company_id'] = $userInfo['company_id'];

        if (empty($token['oa_contacts_id'])) xThrow(ERR_OA_FUNCTION_NOT_QUALIFIED);
        $params = $this->getRequestParameters(['workflow_id'],['is_hide_workflowlog']);

        $isHideWorkFlowLog = request('is_hide_workflowlog',0);

        $workflow = new Workflow();
        $workflowInfo = $workflow->getDetails($token['oa_contacts_id'], $params['workflow_id'],$isHideWorkFlowLog);
        return $workflowInfo;
    }

    // 流程菜单
    public function permission()
    {
        $token = app('token')->checkToken();
        if (empty($token['oa_contacts_id'])) xThrow(ERR_OA_FUNCTION_NOT_QUALIFIED);
        $workflow = new Workflow();
        $data = $workflow->permission();
        return $data;
    }

    // 2.7抄送已读状态更变
    public function copyRead()
    {
        $token = app('token')->checkToken();
        if (empty($token['oa_contacts_id'])) xThrow(ERR_OA_FUNCTION_NOT_QUALIFIED);
        $contactsId = $token['oa_contacts_id'];
        // 都可选, 通过两种方式获取copy
        $workflowId = request('workflow_id', 0);
        $copyId = request('copy_id', 0);
        $workflow = new Workflow();
        $workflow->copyRead($workflowId, $contactsId, $copyId);
        return;
    }

    // 创建
    public function create()
    {
//        $token = app('token')->checkToken()
        //易管通获取对应参数
        $userId = \App\Engine\Func::getHeaderValueByName('userid');
        $userInfo = \App\Eloquent\Ygt\DepartmentUser::getCurrentInfo($userId)->toArray();
        $token['oa_contacts_id'] = $userId;
        $token['oa_company_id'] = $userInfo['company_id'];

        if (empty($token['oa_contacts_id'])) xThrow(ERR_OA_FUNCTION_NOT_QUALIFIED);
        $userId = $token['oa_contacts_id'];
        $companyId = $token['oa_company_id'];
        $params = request()->all();
        if (!isset($params['image_url']) || empty($params['image_url'])) $params['image_url'] = '';
        if (!isset($params['enclosure_url']) || empty($params['enclosure_url'])) $params['enclosure_url'] = '';
        if (!isset($params['reasons']) || empty($params['reasons'])) $params['reasons'] = '';
        if (!isset($params['copy_ids']) || empty($params['copy_ids'])) $params['copy_ids'] = '';
        if (!isset($params['note']) || empty($params['note'])) $params['note'] = '';
        $flow = new Flow();
        $int = Flow::getFlowType($params['scene']);
        if ($int < 0 || $int > 28) xThrow(ERR_PARAMETER, 'scene not supported');
        $type = Flow::getFlowAbbr($int);

        if (isset($params['branch_data'])) $params['branch_data'] = json_decode($params['branch_data'], true);

        $flow->newFlow($type, $params, $userId, $companyId);

        return [];
    }

    // 处理
    public function handle()
    {
//        $token = app('token')->checkToken();
//        if (empty($token['oa_contacts_id'])) xThrow(ERR_OA_FUNCTION_NOT_QUALIFIED);
//        $operatorId = $token['oa_contacts_id'];

        //易管通获取参数
        $userId = \App\Engine\Func::getHeaderValueByName('userid');
        $userInfo = \App\Eloquent\Ygt\DepartmentUser::getCurrentInfo($userId)->toArray();
        $token['oa_contacts_id'] = $userId;
        $token['oa_company_id'] = $userInfo['company_id'];

        if (empty($token['oa_contacts_id'])) xThrow(ERR_OA_FUNCTION_NOT_QUALIFIED);
        $operatorId = $token['oa_contacts_id'];


        $params = $this->getRequestParametersNew([
            'workflow_id', 'operation_type'
        ], [
            'image_url', 'enclosure_url', 'assignee_id', 'opinion','is_stop_flow'
        ]);
        $disposeCode = 0;
        switch ($params['operation_type']) {
            case 'agree':
                $disposeCode = 1100;
                $params['assignee_id'] = 0;
                break;
            case 'reject':
                $disposeCode = 1200;
                $params['assignee_id'] = 0;
                break;
            case 'redeploy':
                $disposeCode = 1001;
                break;
            case 'user_cancel':
                $disposeCode = 1201;
                break;
            default:
                xThrow(ERR_PARAMETER);
        }
        if ($disposeCode === 0) xThrow(ERR_PARAMETER, 'invalid operation type provided');
        $workflow = new \App\Http\Admin\Administration\Models\Workflow();
        $workflow->handle($params['workflow_id'], $disposeCode, $operatorId, $params['assignee_id'], $params['opinion'], $params['image_url'], $params['enclosure_url'],$params);
        return [];
    }

    public function createTemplate()
    {
        $token = app('token')->checkToken();
        if (empty($token['oa_contacts_id'])) xThrow(ERR_OA_FUNCTION_NOT_QUALIFIED);
        $params = $this->getRequestParameters(['scene']);
        $int = Flow::getFlowType($params['scene']);
        if ($int < 0 || $int > 27) xThrow(ERR_PARAMETER, 'scene not supported');
        $type = Flow::getFlowAbbr($int);
        $data = Flow::getTemplate($type);
        return $data;
    }

    public function getRequestParametersNew($required = [], $optional = [])
    {
        $request = request();

        if (is_string($required)) {
            $required = func_get_args();
            $optional = [];
        }

        // $params为空直接获取所有提交数据
        if (empty($required) && empty($optional)) return $request->all();

        if (!is_array($required) || !is_array($optional)) xThrow(ERR_PARAMETER);

        $data = [];
        // 验证必填参数并获取
        if (!empty($required)) {
            $data = $request->only($required);

            // 传递的必须参数的值不能为NULL或空
            if (in_array(null, $data, true) || in_array('', $data, true)) xThrow(ERR_PARAMETER);
        }

        // 获取扩展可选参数
        if (!empty($optional)) {
            $extendData = [];
            foreach ($optional as $key => $value) {
                $name = is_int($key) ? $value : $key;
                $default = is_int($key) ? null : $value;
                $extendData[$name] = $request->get($name, $default);
            }

            $data = array_merge($data, $extendData);
        }

        return $data;
    }

}