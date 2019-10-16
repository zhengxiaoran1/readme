<?php
/**
 * Created by PhpStorm.
 * User: sojo
 * Date: 2018/1/5
 * Time: 20:29
 */

namespace App\Api\OA\Workflow\Models;

use App\Api\OA\Personnel\Models\Personnel;
use App\Eloquent\Oa\FlowConfigProcess;
use App\Eloquent\Oa\FlowConfigProcessButton;
use App\Eloquent\Oa\FlowFile;
use App\Eloquent\Oa\WorkflowCopy;
use App\Http\Admin\Administration\Models\Flow;
use App\Http\Admin\Administration\Models\WorkflowDict;
use App\Repositories\OA\WorkflowRepository;
//use Framework\BaseClass\Api\Model;
use App\Eloquent\Oa\Contacts;
use \App\Engine\YgtLabel;

//class Workflow extends Model
class Workflow
{
    public function getDetails($contactsId, $id,$isHideWorkFlowLog=0)
    {
        // 数据库查询
        $rey = new WorkflowRepository();
        $workflow = $rey->find(['id' => $id], ['workflowLogList.fileList']);
        $workflow['title'] = $workflow['creatorInfo']['name'] . '的'
            . \App\Http\Admin\Administration\Models\Workflow::getRelatedTypeStr($workflow['related_type']);
        $workflow['time'] = $workflow['created_at']->format('Y-m-d H:i');
        $workflow['status'] = WorkflowDict::getDescriptionByDisposeCode($workflow['dispose_code']);
        unset($workflow['creatorInfo']);

        if ($workflow['dispose_code'] === 1200) {
            $workflow['status'] = 2;
            $workflow['status_str'] = '审批被拒';
        } elseif ($workflow['dispose_code'] === 1100) {
            $workflow['status'] = 1;
            $workflow['status_str'] = '审批通过';
        } elseif ($workflow['dispose_code'] === 1000 && $workflow['creator_id'] === (int)$contactsId) {
            $workflow['status'] = 4;
            $workflow['status_str'] = '审批中';
        } else {
            $workflow['status'] = 0;
            $workflow['status_str'] = '审批中';

            if ($workflow['assignee_id'] == (int)$contactsId) $workflow['status'] = 3;
        }




        // 流程图
        foreach ($workflow['workflowLogList'] as $log) {
            $log['operator_name'] = $log['operator_id'] == 0 ? 'admin' : $log['operatorInfo']['name'];
            $log['assignee_name'] = $log['assignee_id'] == 0 ? 'admin' : $log['assigneeInfo']['name'];
            $log['created_at_str'] = $log['created_at']->format('Y-m-d H:i');
            unset($log['operatorInfo'], $log['assigneeInfo']);
            $imageList = [];
            $enclosureList = [];
            foreach ($log['fileList'] as $file) {
                if ($file['type'] == 1) {
                    $imageList[] = env('APP_URL') . $file['url'];
                } elseif ($file['type'] == 2) {
                    $enclosureList[] = env('APP_URL') . $file['url'];
                }
            }
            $log['image_list'] = $imageList;
            $log['enclosure_list'] = $enclosureList;
            unset($log['fileList']);
        }



        // 不同流程独立数据
        $content = [];
        $masterFileList = [];
        $branchFileList = [];
        switch ($workflow['related_type']) {
            case 1: // 公文
                $content[] = '标题: ' . $workflow->documentFlowInfo['title'];
                $c = $workflow->documentFlowInfo['content'];
                if (strlen($c) > 10) $c = substr($c, 0, 10) . '...';
                $content[] = '内容: ' . $c;

                $masterFileList = FlowFile::where([
                    'oa_workflow_id' => $workflow->id,
                    'oa_flow_master_id' => $workflow->documentFlowInfo->id
                ])->get(['url', 'type']);

                foreach ($masterFileList as $master) {
                    $master->url = env('APP_URL') . $master->url;
                }

                unset($workflow->documentFlowInfo);
                break;
            case 2: //请假
                $content[] = '请假类型: ' . Flow::getLeaveTypeStr($workflow->leaveFlowInfo['type']);
                $content[] = '开始时间: ' . date('Y-m-d H:i', $workflow->leaveFlowInfo['start_time']);
                $content[] = '结束时间: ' . date('Y-m-d H:i', $workflow->leaveFlowInfo['end_time']);
                $content[] = '时长: ' . $workflow->leaveFlowInfo['days'] . '天';
                $content[] = '事由: ' . $workflow->leaveFlowInfo['reasons'];

                $masterFileList = FlowFile::where([
                    'oa_workflow_id' => $workflow->id,
                    'oa_flow_master_id' => $workflow->leaveFlowInfo->id
                ])->get(['url', 'type']);

                foreach ($masterFileList as $master) {
                    $master->url = env('APP_URL') . $master->url;
                }

                unset($workflow->leaveFlowInfo);
                break;
            case 3: // 用章
                $content[] = '申章部门: ' . $workflow->sealFlowInfo->departmentInfo['name'];
                $content[] = '经办人: ' . $workflow->sealFlowInfo['operator'];
                $content[] = '用章文件名:' . $workflow->sealFlowInfo['file_name'];
                $content[] = '文件份数:' . $workflow->sealFlowInfo['file_number'];
                $content[] = '文件类型:' . Flow::getSealFileTypeStr($workflow->sealFlowInfo['file_type']);
                $content[] = '加盖何种章:' . Flow::getSealTypeStr($workflow->sealFlowInfo['seal_type']);
                $content[] = '备注: ' . $workflow->sealFlowInfo['note'];

                $masterFileList = FlowFile::where([
                    'oa_workflow_id' => $workflow->id,
                    'oa_flow_master_id' => $workflow->sealFlowInfo->id
                ])->get(['url', 'type']);

                foreach ($masterFileList as $master) {
                    $master->url = env('APP_URL') . $master->url;
                }

                unset($workflow->sealFlowInfo);
                break;
            case 4: // 用车
                $content[] = '申章部门:' . $workflow->carFlowInfo->departmentInfo['name'];
                $content[] = '用车事由:' . $workflow->carFlowInfo['reasons'];
                $content[] = '始发地点:' . $workflow->carFlowInfo['start_place'];
                $content[] = '返回地点:' . $workflow->carFlowInfo['return_place'];
                $content[] = '用车日期:' . date('Y-m-d H:i', $workflow->carFlowInfo['start_time']);
                $content[] = '返回日期:' . date('Y-m-d H:i', $workflow->carFlowInfo['return_time']);

                $masterFileList = FlowFile::where([
                    'oa_workflow_id' => $workflow->id,
                    'oa_flow_master_id' => $workflow->carFlowInfo->id
                ])->get(['url', 'type']);

                foreach ($masterFileList as $master) {
                    $master->url = env('APP_URL') . $master->url;
                }

                $i = 1;
                foreach ($workflow->carFlowInfo->flowCarVehicleList as $key => $flowCarVehicle) {
                    $content[] = '#' . $i;
                    $content[] = '车辆类型: ' . $flowCarVehicle->type;
                    $content[] = '数量（辆）:' . $flowCarVehicle->number;
                    $content[] = '其它要求:' . $flowCarVehicle->request;
                    $i++;

                    $branchFileList[$key] = FlowFile::where([
                        'oa_workflow_id' => $workflow->id,
                        'oa_flow_branch_id' => $flowCarVehicle->id
                    ])->get(['url', 'type']);

                    if (!$branchFileList[$key]->isEmpty()) {
                        foreach ($branchFileList[$key] as $branchFile) {
                            $branchFile->url = env('APP_URL') . $branchFile->url;
                        }
                    } else {
                        $branchFileList = [];
                    }
                }
                $content[] = '备注: ' . $workflow->carFlowInfo['note'];
                unset($workflow->carFlowInfo);
                break;
            case 5: // 离职 暂无
                break;
            case 6: // 岗位调动
                $userInfo = new Personnel();
                $userInfo = $userInfo->getEmployeeInfo($workflow->creator_id);
                $content[] = '姓名: ' . $userInfo->name;
                $content[] = '工号: ' . $userInfo->employee_number;
                $content[] = '身份证号: ' . $userInfo->credential_number;
                $content[] = '所属部门: ' . $userInfo->department_name;
                $content[] = '岗位名称: ' . $userInfo->position;
                $content[] = '入职日期: ' . $userInfo->entry_time;
                $content[] = '工龄: ' . $userInfo->working_years;

                $content[] = '调岗原因: ' . $workflow->transferPostFlowInfo['reasons'];
                $content[] = '新职位: ' . $workflow->transferPostFlowInfo['new_department'];
                $content[] = '新部门: ' . $workflow->transferPostFlowInfo['new_position'];
                $content[] = '调整日期: ' . date('Y-m-d', $workflow->transferPostFlowInfo['adjust_time']);

                $masterFileList = FlowFile::where([
                    'oa_workflow_id' => $workflow->id,
                    'oa_flow_master_id' => $workflow->transferPostFlowInfo->id
                ])->get(['url', 'type']);

                foreach ($masterFileList as $master) {
                    $master->url = env('APP_URL') . $master->url;
                }

                unset($workflow->transferPostFlowInfo);
                break;
            case 7: // 报销
                $masterFileList = FlowFile::where([
                    'oa_workflow_id' => $workflow->id,
                    'oa_flow_master_id' => $workflow->reimburseFlowInfo->id
                ])->get(['url', 'type']);

                foreach ($masterFileList as $master) {
                    $master->url = env('APP_URL') . $master->url;
                }

                $i = 1;
                foreach ($workflow->reimburseFlowInfo->reimburseItemList as $key => $item) {
                    $content[] = '#' . $i;
                    $content[] = '报销金额(元):' . $item['money'];
                    $content[] = '报销类别:' . $item['type'];
                    $content[] = '费用明细:' . $item['details'];
                    $i++;

                    $branchFileList[$key] = FlowFile::where([
                        'oa_workflow_id' => $workflow->id,
                        'oa_flow_branch_id' => $item->id
                    ])->get(['url', 'type']);

                    if (!$branchFileList[$key]->isEmpty()) {
                        foreach ($branchFileList[$key] as $branchFile) {
                            $branchFile->url = env('APP_URL') . $branchFile->url;
                        }
                    } else {
                        $branchFileList = [];
                    }
                }

                unset($workflow->reimburseFlowInfo);
                break;
            case 8: // 外出
                $content[] = '开始时间:' . date('Y-m-d', $workflow->goOutFlowInfo['start_time']);
                $content[] = '结束时间:' . date('Y-m-d', $workflow->goOutFlowInfo['end_time']);
                $content[] = '时长:' . $workflow->goOutFlowInfo['duration'] . '小时';
                $content[] = '外出事由:' . $workflow->goOutFlowInfo['reasons'];

                $masterFileList = FlowFile::where([
                    'oa_workflow_id' => $workflow->id,
                    'oa_flow_master_id' => $workflow->goOutFlowInfo->id
                ])->get(['url', 'type']);

                foreach ($masterFileList as $master) {
                    $master->url = env('APP_URL') . $master->url;
                }

                unset($workflow->goOutFlowInfo);
                break;
            case 9: // 出差
                $content[] = '原因:' . $workflow->businessTripFlowInfo['reasons'];

                $masterFileList = FlowFile::where([
                    'oa_workflow_id' => $workflow->id,
                    'oa_flow_master_id' => $workflow->businessTripFlowInfo->id
                ])->get(['url', 'type']);

                foreach ($masterFileList as $master) {
                    $master->url = env('APP_URL') . $master->url;
                }

                $i = 1;
                foreach ($workflow->businessTripFlowInfo->travelList as $key => $travel) {
                    $content[] = '#' . $i;
                    $content[] = '出差地点: ' . $travel->destination;
                    $content[] = '开始时间:' . date('Y-m-d', $travel['start_time']);
                    $content[] = '结束时间:' . date('Y-m-d', $travel['end_time']);
                    $content[] = '时长:' . $travel['duration'] . '天';
                    $i++;

                    $branchFileList[] = FlowFile::where([
                        'oa_workflow_id' => $workflow->id,
                        'oa_flow_branch_id' => $travel->id
                    ])->get(['url', 'type']);

                    if (!$branchFileList[$key]->isEmpty()) {
                        foreach ($branchFileList[$key] as $branchFile) {
                            $branchFile->url = env('APP_URL') . $branchFile->url;
                        }
                    } else {
                        $branchFileList = [];
                    }
                }

                unset($workflow->businessTripFlowInfo);
                break;
            case 10: // 调休
                $content[] = '开始时间:' . date('Y-m-d', $workflow->daysOffFlowInfo['start_time']);
                $content[] = '结束时间:' . date('Y-m-d', $workflow->daysOffFlowInfo['end_time']);
                $content[] = '时长:' . $workflow->daysOffFlowInfo['duration'] . '小时';

                $masterFileList = FlowFile::where([
                    'oa_workflow_id' => $workflow->id,
                    'oa_flow_master_id' => $workflow->daysOffFlowInfo->id
                ])->get(['url', 'type']);

                foreach ($masterFileList as $master) {
                    $master->url = env('APP_URL') . $master->url;
                }

                unset($workflow->daysOffFlowInfo);
                break;
            case 11: // 补勤
                $content[] = '补勤时间:' . date('Y-m-d', $workflow->supplementFlowInfo['time']);
                $content[] = '缺勤原因:' . $workflow->supplementFlowInfo['reasons'];

                $masterFileList = FlowFile::where([
                    'oa_workflow_id' => $workflow->id,
                    'oa_flow_master_id' => $workflow->supplementFlowInfo->id
                ])->get(['url', 'type']);

                foreach ($masterFileList as $master) {
                    $master->url = env('APP_URL') . $master->url;
                }

                unset($workflow->supplementFlowInfo);
                break;
            case 12:    // 加班费
                // TODO 返回流程对应的全部信息
                $content[] = '开始时间:' . date('Y-m-d', $workflow->overtimeWorkFeeFlowInfo['start_time']);
                $content[] = '结束时间:' . date('Y-m-d', $workflow->overtimeWorkFeeFlowInfo['end_time']);
                $content[] = '时长:' . $workflow->overtimeWorkFeeFlowInfo['duration'];
                $content[] = '工作内容:' . $workflow->overtimeWorkFeeFlowInfo['work_content'];
                $content[] = '工作地点:' . $workflow->overtimeWorkFeeFlowInfo['workplace'];
                $content[] = '加班费:' . $workflow->overtimeWorkFeeFlowInfo['overtime_pay'];
                $content[] = '误餐费:' . $workflow->overtimeWorkFeeFlowInfo['table_money'];
                $masterFileList = FlowFile::where([
                    'oa_workflow_id' => $workflow->id,
                    'oa_flow_master_id' => $workflow->overtimeWorkFeeFlowInfo->id
                ])->get(['url', 'type']);

                foreach ($masterFileList as $master) {
                    $master->url = env('APP_URL') . $master->url;
                }

                unset($workflow->overtimeWorkFeeFlowInfo);
                break;
            case 13: // 领用申请
                $content[] = '物品名:' . $workflow->receiveApplyForInfo['name'];
                $content[] = '规格:' . $workflow->receiveApplyForInfo['specification'];
                $content[] = '数量:' . $workflow->receiveApplyForInfo['number'];
                $content[] = '用途:' . $workflow->receiveApplyForInfo['use'];
                $content[] = '申报日期:' . date('Y-m-d', $workflow->receiveApplyForInfo['date']);


                $masterFileList = FlowFile::where([
                    'oa_workflow_id' => $workflow->id,
                    'oa_flow_master_id' => $workflow->receiveApplyForInfo->id
                ])->get(['url', 'type']);

                foreach ($masterFileList as $master) {
                    $master->url = env('APP_URL') . $master->url;
                }
                unset($workflow->receiveApplyForInfo);
                break;
            case 14: // 采购
                $content[] = '物品名:' . $workflow->procurementFlowInfo['name'];
                $content[] = '规格:' . $workflow->procurementFlowInfo['format'];
                $content[] = '数量:' . $workflow->procurementFlowInfo['number'];
                $content[] = '单价(元):' . $workflow->procurementFlowInfo['prise'];
                $content[] = '合计(元):' . $workflow->procurementFlowInfo['total'];
                $content[] = '用途:' . $workflow->procurementFlowInfo['purpose'];
                $content[] = '供货商:' . $workflow->procurementFlowInfo['supplier'];
                $content[] = '保质期(过期时间):' . date('Y-m-d', $workflow->procurementFlowInfo['deadline']);

                $masterFileList = FlowFile::where([
                    'oa_workflow_id' => $workflow->id,
                    'oa_flow_master_id' => $workflow->procurementFlowInfo->id
                ])->get(['url', 'type']);

                foreach ($masterFileList as $master) {
                    $master->url = env('APP_URL') . $master->url;
                }

                unset($workflow->procurementFlowInfo);
                break;
            case 15: // 合同
                $content[] = '合同名:' . $workflow->contractFlowInfo['name'];
                $content[] = '用途:' . $workflow->contractFlowInfo['purpose'];


                $masterFileList = FlowFile::where([
                    'oa_workflow_id' => $workflow->id,
                    'oa_flow_master_id' => $workflow->contractFlowInfo->id
                ])->get(['url', 'type']);

                foreach ($masterFileList as $master) {
                    $master->url = env('APP_URL') . $master->url;
                }

                unset($workflow->contractFlowInfo);
                break;
            case 16: // 备用金
                $content[] = '金额(元):' . $workflow->pettyCashFlowInfo['money'];
                $content[] = '用途:' . $workflow->pettyCashFlowInfo['purpose'];
                $content[] = '申报日期:' . date('Y-m-d', $workflow->pettyCashFlowInfo['apply_date']);

                $masterFileList = FlowFile::where([
                    'oa_workflow_id' => $workflow->id,
                    'oa_flow_master_id' => $workflow->pettyCashFlowInfo->id
                ])->get(['url', 'type']);

                foreach ($masterFileList as $master) {
                    $master->url = env('APP_URL') . $master->url;
                }

                unset($workflow->pettyCashFlowInfo);
                break;
            case 17: // 制度方案
                $content[] = '名称:' . $workflow->systemSolutionsFlowInfo['name'];
                $content[] = '内容:' . $workflow->systemSolutionsFlowInfo['content'];
                $content[] = '执行时间:' . $workflow->systemSolutionsFlowInfo['do_time'];
                $content[] = '执行时间:' . date('Y-m-d', $workflow->systemSolutionsFlowInfo['do_time']);

                $masterFileList = FlowFile::where([
                    'oa_workflow_id' => $workflow->id,
                    'oa_flow_master_id' => $workflow->systemSolutionsFlowInfo->id
                ])->get(['url', 'type']);

                foreach ($masterFileList as $master) {
                    $master->url = env('APP_URL') . $master->url;
                }

                unset($workflow->systemSolutionsFlowInfo);
                break;
            case 18: // 招聘需求
                $content[] = '岗位名称:' . $workflow->recruitmentNeedsFlowInfo['name'];
                $content[] = '人数:' . $workflow->recruitmentNeedsFlowInfo['person_number'];
                $content[] = '部门:' . $workflow->recruitmentNeedsFlowInfo['deparment'];
                $content[] = 'requirement:' . $workflow->recruitmentNeedsFlowInfo['requirement'];
                $content[] = '招聘时间:' . date('Y-m-d', $workflow->recruitmentNeedsFlowInfo['requirement_time']);

                $masterFileList = FlowFile::where([
                    'oa_workflow_id' => $workflow->id,
                    'oa_flow_master_id' => $workflow->recruitmentNeedsFlowInfo->id
                ])->get(['url', 'type']);

                foreach ($masterFileList as $master) {
                    $master->url = env('APP_URL') . $master->url;
                }

                unset($workflow->recruitmentNeedsFlowInfo);
                break;
            case 19: // 奖罚申报
                $usernames = Contacts::select('id', 'name')->where('id', '=', $workflow->rewardAndPunishFlowInfo['oa_contact_id'])->first();
                $content[] = '姓名:' . $usernames['name'];
                $content[] = '奖罚方式:' . $workflow->rewardAndPunishFlowInfo['type'];
                $content[] = '原因:' . $workflow->rewardAndPunishFlowInfo['reason'];
                $content[] = '奖罚时间:' . date('Y-m-d', $workflow->rewardAndPunishFlowInfo['time']);

                $masterFileList = FlowFile::where([
                    'oa_workflow_id' => $workflow->id,
                    'oa_flow_master_id' => $workflow->rewardAndPunishFlowInfo->id
                ])->get(['url', 'type']);

                foreach ($masterFileList as $master) {
                    $master->url = env('APP_URL') . $master->url;
                }

                unset($workflow->rewardAndPunishFlowInfo);
                break;
            case 20: // 离职
                $usernames = Contacts::select('id', 'name')->where('id', '=', $workflow->dimissionFlowInfo['oa_contact_id'])->first();
                $content[] = '姓名:' . $usernames['name'];
                $content[] = '原因:' . $workflow->dimissionFlowInfo['reason'];
                $content[] = '离职时间:' . date('Y-m-d', $workflow->dimissionFlowInfo['time']);

                $masterFileList = FlowFile::where([
                    'oa_workflow_id' => $workflow->id,
                    'oa_flow_master_id' => $workflow->dimissionFlowInfo->id
                ])->get(['url', 'type']);

                foreach ($masterFileList as $master) {
                    $master->url = env('APP_URL') . $master->url;
                }

                unset($workflow->dimissionFlowInfo);
                break;
            case 21: // 转正晋升调薪
                $usernames = Contacts::select('id', 'name')->where('id', '=', $workflow->becomePromoteSalaryFlowInfo['oa_contact_id'])->first();

                $content[] = '姓名:' . $usernames['name'];
                $content[] = '变动类型:' . $workflow->becomePromoteSalaryFlowInfo['type'];
                $content[] = '变动原因:' . $workflow->becomePromoteSalaryFlowInfo['reason'];
                $content[] = '现工资:' . $workflow->becomePromoteSalaryFlowInfo['current_salary'];
                $content[] = '调薪工资:' . $workflow->becomePromoteSalaryFlowInfo['adjustment_salary'];
                $content[] = '备注:' . $workflow->becomePromoteSalaryFlowInfo['note'];

                $masterFileList = FlowFile::where([
                    'oa_workflow_id' => $workflow->id,
                    'oa_flow_master_id' => $workflow->becomePromoteSalaryFlowInfo->id
                ])->get(['url', 'type']);

                foreach ($masterFileList as $master) {
                    $master->url = env('APP_URL') . $master->url;
                }

                unset($workflow->becomePromoteSalaryFlowInfo);
                break;
            case 22: // 补签
                switch ($workflow->replenishSignFlowInfo['type']) {
                    case 1:
                        $type = '上班';
                        break;
                    case 2:
                        $type = '上班';
                        break;
                    default:
                        $type = '其它';
                }

                $content[] = '补签种类:' . $type;
                $content[] = '补签时间:' . date('Y-m-d', $workflow->replenishSignFlowInfo['time']);
                $content[] = '缺卡原因:' . $workflow->replenishSignFlowInfo['reason'];

                unset($workflow->replenishSignFlowInfo);
                break;
            case 23: // 面试
                $content[] = '备注:' . $workflow->interviewFlowInfo['note'];
                $workflow['resume_id'] = $workflow->interviewFlowInfo['job_resume_id'];

                if ($workflow->interviewFlowInfo->resumeInfo) {
                    $workflow['title'] = $workflow->interviewFlowInfo->resumeInfo->full_name . '的'
                        . \App\Http\Admin\Administration\Models\Workflow::getRelatedTypeStr($workflow['related_type']);
                }

                unset($workflow->interviewFlowInfo);
                break;
            case 24: // 入职
                $content[] = '备注:' . $workflow->takingWorkFlowInfo['note'];
                $workflow['resume_id'] = $workflow->takingWorkFlowInfo['job_resume_id'];

                unset($workflow->takingWorkFlowInfo);
                break;
            case 26: //采购申请
                //获取审批人信息
                $operatorName = '';
                if ($workflow['assignee_id']) {
                    $operatorName = \App\Eloquent\Ygt\DepartmentUser::getCurrentInfo($workflow['assignee_id'])->truename;
                }
                $workflow['operator_name'] = $operatorName;


                //获取采购申请的详情
                $purchaseId = $workflow['related_id'];
                $purchaseRow = \App\Eloquent\Ygt\Purchase::where(['id' => $purchaseId])->first()->toArray();
                $purchaseMaterialList = \App\Eloquent\Ygt\PurchaseMaterial::where(['purchase_id' => $purchaseId])->get()->toArray();

                //获取供应商信息
                $supplierName = config('default-value.purchase_list_default_supplier_name');
                $tmpObj = \App\Eloquent\Ygt\SellerCompany::where(['id'=>$purchaseRow['supplier_id']])->first();
                if($tmpObj){
                    $supplierName = $tmpObj->title;
                }

                $workflow['supplier_name'] = $supplierName;
                $workflow['finished_date'] = $purchaseRow['finished_date'];
                $workflow['payment_method'] = $purchaseRow['payment_method'];
                $workflow['remark'] = $purchaseRow['content'];


                $materialList = [];
                $allSumMoney = 0;//所有材料总价
                foreach ($purchaseMaterialList as $purchaseMaterialRow) {
                    //获取材料名称
//                    $materialRow = \App\Engine\Product::getProductInfo($purchaseMaterialRow['material_id']);
//
//                    //追加材料图片地址
//                    if ($materialRow['img_id']) {
//                        $materialRow['img_url'] = \App\Eloquent\Ygt\ImgUpload::getImgUrlById($materialRow['img_id']);
//                    }
//
//                    //追加材料自定义属性
//                    $ProductFieldsModel = new \App\Eloquent\Ygt\ProductFields();
//                    $where = ['product_id' => $materialRow['id']];
//                    $productFields = $ProductFieldsModel->getData($where);
//
//                    $productFields = $productFields->map(function ($item) {
//                        $data['field_name'] = $item->field_name;
//                        $comumnName = \App\Engine\Product::getFieldColumn($item->field_type);
//
//                        $data['field_value'] = $item->$comumnName.$item['unit'];
//                        return $data;
//                    });
//
//                    $materialRow['custom_fields'] = $productFields;

                    $materialRow = \App\Engine\Material::getMaterialDealInfo($purchaseMaterialRow['material_id']);
                    if(!$materialRow){
                        continue;
                    }

                    if(!$purchaseMaterialRow['in_number']){
                        $purchaseMaterialRow['in_number'] = 0;
                    }

                    $showMaterialRow = [
                        'product_name' => $materialRow['product_name'],
                        // 返回图片ID，BY SOJO
                        'img_id' => $materialRow['image_id'],
                        'img_url' => $materialRow['image_path'],
                        'custom_fields' => $materialRow['custom_fields'],
                        'price' =>  '¥'.$purchaseMaterialRow['price'],
                        'num' => '×'.$purchaseMaterialRow['total_number']."({$materialRow['unit']})",
                        'sum_money' => $purchaseMaterialRow['price']*$purchaseMaterialRow['total_number'],//总价
                        'in_number' => "已入库{$purchaseMaterialRow['in_number']}({$materialRow['unit']})",//已入库数量
                    ];

                    //如果不是采购员，隐藏采购金额
                    //获取登陆用户信息
                    $isHideAllSumMoney = 0;
                    $userId = \App\Engine\Func::getHeaderValueByName('userid');
                    $userInfo = \App\Eloquent\Ygt\DepartmentUser::getCurrentInfo($userId)->toArray();
                    $companyId = $userInfo['company_id'];
                    $privilegeIdList = \App\Engine\OrderEngine::getPrivilegeByNode($companyId, 10);

                    //Linwei 20190701 判断当前是否是在采购审批这个步骤里
                    $flowConfigProcessButtonValue = FlowConfigProcessButton::where(['oa_flow_config_process_id'=>$workflow['oa_flow_config_process_id']])->first()->buttonValue;

                    if ( $flowConfigProcessButtonValue!= 1 && !in_array($userInfo['privilege_id'], $privilegeIdList)) {

                        $showMaterialRow['price'] = '';

                        //兼容就版本 v1.1之后的才调整
                        $version = request()->header('version');
                        if($version && ($version >= '1.1')){
                            $showMaterialRow['sum_money'] = '';
                        }

                        $allSumMoney = '---';
                        $isHideAllSumMoney = 1;
                    }else{
                        $allSumMoney += $showMaterialRow['sum_money'];
                    }


                    $materialList[] = $showMaterialRow;
                }

                //按钮-如果步骤执行人不是自己，隐藏按钮
                if($workflow['assignee_id'] != $contactsId){
                    $buttonValue = '';
                }else{
                    $buttonValue = \App\Engine\WorkFlow::getPurchaseButtonByProcessId($workflow['oa_flow_config_process_id']);
                }
                if(!$workflow['assignee_id']){//流程走完了不需要展示按钮
                    $buttonValue = '';
                }

                ////仓管员进行入库操作时，修改状态
                if($buttonValue == 4){
                    $workflow['status_str'] = '验收中';
                }


                $fact_money = $purchaseRow['fact_money']==false?0:$purchaseRow['fact_money'];
                $content = [
                    'material_list' => $materialList,//采购材料名称（所有）
                    'all_sum_money' => '¥'.$allSumMoney,//所有材料总价
                    'is_hide_all_sum_money'=>$isHideAllSumMoney,
                    'purchase_content' => $purchaseRow['content'],//采购申请备注
                    'button_value' => $buttonValue,//
                    'fact_money' => '¥' . $fact_money,//
//                    '1' => '采购通过',
//                    '2' => '老板通过',
//                    '3' => '进行采购',
//                    '4' => '进行入库',

//                    'supplier_name' => '供应商企业',//采购供应商企业
//                    'purchase_number'=> $purchaseRow['purchase_number'],//采购编号
//                    'create_date'=> date('Y-m-d H:i',$purchaseRow['created_at']),//生成日期
//                    'status'=> $workflow['status'],//状态值
//                    'status_str'=> $workflow['status_str'],//状态提示
//                    'is_warning'=> rand(0,1),//状态提示
//                    'image_list'=> $imageList,//图片列表
                ];




                if(!empty($workflow['workflowLogList'])){
                    //隐藏审批流程
                    if($isHideWorkFlowLog){
                        while($workflow['workflowLogList']->isNotEmpty()){
                            $workflow['workflowLogList']->pop();
                        }
                    }else{
                        //追加流程中的审批人名字
                        foreach ($workflow['workflowLogList'] as $k => $tmpRow){
                            $operatorName = '';
                            if ($tmpRow['operator_id']) {
                                $operatorName = \App\Eloquent\Ygt\DepartmentUser::getCurrentInfo($tmpRow['operator_id'])->truename;
                            }
                            $workflow['workflowLogList'][$k]['operator_name'] = $operatorName;
                        }

                        ////追加一个当前审批状态 20180528  zhuyujun
                        //先判断是否都完成了
                        if($workflow['assignee_id']){//流程没走完需要加一个
                            $operatorName = \App\Eloquent\Ygt\DepartmentUser::getCurrentInfo($workflow['assignee_id'])->truename;
                            $flowConfigProcessName = FlowConfigProcess::where(['id'=>$workflow['oa_flow_config_process_id']])->first()->description;

                            $workflow['workflowLogList'] = $workflow['workflowLogList']->push([
                                'id'    => '0',//固定
                                'oa_workflow_id'    => $workflow->id,//固定
                                'operator_id'    => $workflow->assignee_id,
                                'assignee_id'    => 0,//固定
                                'dispose_code'    => $workflow->dispose_code,
                                'created_at_str'    => '',
//                        'note'    => $flowConfigProcessName.'·审批中',
                                'note'    => $flowConfigProcessName,
                                'opinion'    => '',
                                'operator_name'    => $operatorName,
                            ]);
                        }
                    }
                }


                //修改采购流程的状态
                $purchase_row = \App\Eloquent\Ygt\Purchase::where(['id'=>$workflow['related_id']])->first();
                if( $purchase_row ){
                    $purchase_row = $purchase_row->toArray();
                    if( $purchase_row['is_able_in'] == 0 ){
                        $workflow['status_str']= '待入库';
                    }

                    if( $purchase_row['is_able_in'] == 1 ){
                        $workflow['status_str']= '入库中';
                    }

                    if( $purchase_row['is_all_in'] == 1 ){
                        $workflow['status_str']= '已入库';
                    }

                    if ($workflow['dispose_code'] == 1100 ) {
                        $workflow['status_str'] = '采购完成';
                    }else{
                        if( $purchase_row['is_able_in'] == 0 ){
                            $workflow['status_str']= '待入库';
                        }

                        if( $purchase_row['is_able_in'] == 1 ){
                            $workflow['status_str']= '入库中';
                        }

                        if( $purchase_row['is_all_in'] == 1 ){
                            $workflow['status_str']= '已入库';
                        }
                    }

                }







                break;
            case 27: //退货申请
                //获取审批人信息
                $operatorName = '';
                if ($workflow['assignee_id']) {
                    $operatorName = \App\Eloquent\Ygt\DepartmentUser::getCurrentInfo($workflow['assignee_id'])->truename;
                }
                $workflow['operator_name'] = $operatorName;

                //获取采购申请的详情
                $purchaseId = $workflow['related_id'];
                $purchaseRow = \App\Eloquent\Ygt\ReturnPurchase::where(['id' => $purchaseId])->first()->toArray();

                //获取供应商信息
                $payment_method = '';
                $supplierName = config('default-value.purchase_list_default_supplier_name');
                $tmpObj = \App\Eloquent\Ygt\SellerCompany::where(['id'=>$purchaseRow['supplier_id']])->first();
                if($tmpObj){
                    $supplierName = $tmpObj->title;
                    if($tmpObj->pay_type){
                        $payment_method = $tmpObj->pay_type;
                    }
                }

                $workflow['supplier_name'] = $supplierName;
                $workflow['payment_method'] = $payment_method;

                $purchaseMaterialList = \App\Eloquent\Ygt\ReturnPurchaseMaterial::where(['purchase_id' => $purchaseId])->get()->toArray();
                $materialList = [];
                $allSumMoney = 0;//所有材料总价
                foreach ($purchaseMaterialList as $purchaseMaterialRow) {
//                    //获取材料名称
//                    $materialRow = \App\Engine\Product::getProductInfo($purchaseMaterialRow['material_id']);
//
//                    //追加材料图片地址
//                    if ($materialRow['img_id']) {
//                        $materialRow['img_url'] = \App\Eloquent\Ygt\ImgUpload::getImgUrlById($materialRow['img_id']);
//                    }
//
//                    //追加材料自定义属性
//                    $ProductFieldsModel = new \App\Eloquent\Ygt\ProductFields();
//                    $where = ['product_id' => $materialRow['id']];
//                    $productFields = $ProductFieldsModel->getData($where);
//
//                    $productFields = $productFields->map(function ($item) {
//                        $data['field_name'] = $item->field_name;
//                        $comumnName = \App\Engine\Product::getFieldColumn($item->field_type);
//
//                        $data['field_value'] = $item->$comumnName;
//                        return $data;
//                    });
//
//                    $materialRow['custom_fields'] = $productFields;

                    $materialRow = \App\Engine\Material::getMaterialDealInfo($purchaseMaterialRow['material_id']);
                    if(!$materialRow){
                        continue;
                    }


                    $showMaterialRow = [
                        'product_name' => $materialRow['product_name'],
                        'img_url' => $materialRow['image_path'],
                        'custom_fields' => $materialRow['custom_fields'],
                        'price' =>  '¥'.$purchaseMaterialRow['price'],
                        'num' => '×'.$purchaseMaterialRow['num']."({$materialRow['unit']})",
                        'sum_money' => $purchaseMaterialRow['price']*$purchaseMaterialRow['num'],//总价
                        'in_number' => "",//兼容采购流程字段
                    ];

                    //如果不是采购员，隐藏采购金额
                    //获取登陆用户信息
                    $userId = \App\Engine\Func::getHeaderValueByName('userid');
                    $userInfo = \App\Eloquent\Ygt\DepartmentUser::getCurrentInfo($userId)->toArray();
                    $companyId = $userInfo['company_id'];
                    $privilegeIdList = \App\Engine\OrderEngine::getPrivilegeByNode($companyId, 10);
                    if (!in_array($userInfo['privilege_id'], $privilegeIdList)) {
                        $showMaterialRow['price'] = '';

                        //兼容就版本 v1.1之后的才调整
                        $version = request()->header('version');
                        if($version && ($version >= '1.1')){
                            $showMaterialRow['sum_money'] = '';
                        }

                        $allSumMoney = '---';
                    }else{
                        $allSumMoney += $showMaterialRow['sum_money'];
                    }



                    $materialList[] = $showMaterialRow;
                }

                //按钮-如果步骤执行人不是自己，隐藏按钮
                if($workflow['assignee_id'] != $contactsId){
                    $buttonValue = '';
                }else{
                    $buttonValue = \App\Engine\WorkFlow::getReturnPurchaseButtonByProcessId($workflow['oa_flow_config_process_id']);
                }
                if(!$workflow['assignee_id']){//流程走完了不需要展示按钮
                    $buttonValue = '';
                }


                $content = [
                    'material_list' => $materialList,//采购材料名称（所有）
                    'all_sum_money' => $allSumMoney,//所有材料总价
                    'is_hide_all_sum_money'=>0,
                    'purchase_content' => $purchaseRow['content'],//采购申请备注
                    'button_value' => $buttonValue,//
//                    '1' => '采购通过',
//                    '2' => '老板通过',
//                    '3' => '进行采购',
//                    '4' => '进行入库',

//                    'supplier_name' => '供应商企业',//采购供应商企业
//                    'purchase_number'=> $purchaseRow['purchase_number'],//采购编号
//                    'create_date'=> date('Y-m-d H:i',$purchaseRow['created_at']),//生成日期
//                    'status'=> $workflow['status'],//状态值
//                    'status_str'=> $workflow['status_str'],//状态提示
//                    'is_warning'=> rand(0,1),//状态提示
//                    'image_list'=> $imageList,//图片列表
                ];

                //追加流程中的审批人名字
                if(!empty($workflow['workflowLogList'])){
                    foreach ($workflow['workflowLogList'] as $k => $tmpRow){
                        $operatorName = '';
                        if ($tmpRow['operator_id']) {
                            $operatorName = \App\Eloquent\Ygt\DepartmentUser::getCurrentInfo($tmpRow['operator_id'])->truename;
                        }
                        $workflow['workflowLogList'][$k]['operator_name'] = $operatorName;
                    }
                }


                ////追加一个当前审批状态 20180528  zhuyujun
                //先判断是否都完成了
                if($workflow['assignee_id']){//流程没走完需要加一个
                    $operatorName = \App\Eloquent\Ygt\DepartmentUser::getCurrentInfo($workflow['assignee_id'])->truename;
                    $flowConfigProcessName = FlowConfigProcess::where(['id'=>$workflow['oa_flow_config_process_id']])->first()->description;

                    $workflow['workflowLogList'] = $workflow['workflowLogList']->push([
                        'id'    => '0',//固定
                        'oa_workflow_id'    => $workflow->id,//固定
                        'operator_id'    => $workflow->assignee_id,
                        'assignee_id'    => 0,//固定
                        'dispose_code'    => $workflow->dispose_code,
                        'created_at_str'    => '',
//                        'note'    => $flowConfigProcessName.'·审批中',
                        'note'    => $flowConfigProcessName,
                        'opinion'    => '',
                        'operator_name'    => $operatorName,
                    ]);
                }

                break;
            default:
                xThrow(ERR_PARAMETER);
        }
        $workflow['content'] = $content;

        $workflow->master_file_list = $masterFileList;
        $workflow->branch_file_list = $branchFileList;

        unset($workflow['creatorInfo']);
        return $workflow;
    }

    public function getPagingList($contactsId, $scene, $type, $isRead, $page, $pageSize,$status)
    {
//        $columns = ['id', 'related_id', 'creator_id'];
        $columns = ['*'];
        $touchColumns = ['*'];
//        switch ($type) {
//            case 'document':
//                $touchColumns = ['id', 'serial_number', 'title', 'content', 'enclosure_url'];
//                break;
//            case 'leave':
//                $touchColumns = ['*'];
//                break;
//            default:
//                xThrow(ERR_PARAMETER);
//        }
        $workflow = new WorkflowRepository();
        list($workflowList, $total) = $workflow->getPagingListByContactsId($contactsId, $scene, $type, $isRead, $page, $pageSize, $columns, $touchColumns,$status);

        foreach ($workflowList as $workflow) {
            $content = [];
            $workflow['title'] = $workflow['creatorInfo']['name'] . '的'
                . \App\Http\Admin\Administration\Models\Workflow::getRelatedTypeStr($workflow['related_type']);
            $workflow['time'] = $this->getRecentTime($workflow['created_at']->timestamp);

            if ($workflow['dispose_code'] === 1200) {
                $workflow['status'] = 2;
                $workflow['status_str'] = '审批被拒';
            } elseif ($workflow['dispose_code'] === 1100) {
                $workflow['status'] = 1;
                $workflow['status_str'] = '审批通过';
            } elseif ($workflow['dispose_code'] === 1000 && $workflow['creator_id'] === (int)$contactsId) {
                $workflow['status'] = 4;
                $workflow['status_str'] = '审批中';
            } else {
                $workflow['status'] = 0;
                $workflow['status_str'] = '审批中';

                if ($workflow['assignee_id'] == $contactsId) $workflow['status'] = 3;
            }
            switch ($workflow['related_type']) {
                case 1: // 公文
//                    $workflow->document_flow_id = $workflow->documentFlowInfo->id;
//                    $workflow->serial_number = $workflow->documentFlowInfo->serial_number;
//                    $workflow->title = $workflow->documentFlowInfo->title;
//                    $workflow->content = $workflow->documentFlowInfo->content;
//                    $workflow->enclosure_url = env('APP_URL') . $workflow->documentFlowInfo->enclosure_url;
                    $content[] = '标题: ' . $workflow->documentFlowInfo['title'];
                    $c = $workflow->documentFlowInfo['content'];
                    if (strlen($c) > 10) $c = substr($c, 0, 10) . '...';
                    $content[] = '内容: ' . $c;
                    unset($workflow->documentFlowInfo);
                    break;
                case 2: //请假
                    $content[] = '请假类型: ' . Flow::getLeaveTypeStr($workflow->leaveFlowInfo['type']);
                    $content[] = '开始时间: ' . date('Y-m-d H:i', $workflow->leaveFlowInfo['start_time']);
                    $content[] = '结束时间: ' . date('Y-m-d H:i', $workflow->leaveFlowInfo['end_time']);
                    $content[] = '时长: ' . $workflow->leaveFlowInfo['days'] . '天';
                    unset($workflow->leaveFlowInfo);
                    break;
                case 3: // 用章
                    $content[] = '申章部门: ' . $workflow->sealFlowInfo->departmentInfo['name'];
                    $content[] = '经办人: ' . $workflow->sealFlowInfo['operator'];
                    $content[] = '日期: ' . date('Y-m-d H:i', $workflow->sealFlowInfo['created_at']->timestamp);
                    unset($workflow->sealFlowInfo);
                    break;
                case 4: // 用车
                    $content[] = '用车日期:' . date('Y-m-d', $workflow->carFlowInfo['start_time']);
                    $content[] = '返回日期:' . date('Y-m-d', $workflow->carFlowInfo['return_time']);
                    unset($workflow->carFlowInfo);
                    break;
                case 5: // 离职 暂无
                    break;
                case 6: // 岗位调动
                    $content[] = '调整时间: ' . date('Y-m-d H:i', $workflow->transferPostFlowInfo['adjust_time']);
                    $content[] = '新职位: ' . $workflow->transferPostFlowInfo['new_department'];
                    $content[] = '新部门: ' . $workflow->transferPostFlowInfo['new_position'];
                    unset($workflow->transferPostFlowInfo);
                    break;
                case 7: // 报销
                    $money = 0;
                    foreach ($workflow->reimburseFlowInfo->reimburseItemList as $item) {
                        $money += $item['money'];
                    }
                    $content[] = '报销金额(元):' . $money;
                    unset($workflow->reimburseFlowInfo);
                    break;
                case 8: // 外出
                    $content[] = '开始时间:' . date('Y-m-d', $workflow->goOutFlowInfo['start_time']);
                    $content[] = '结束时间:' . date('Y-m-d', $workflow->goOutFlowInfo['end_time']);
                    unset($workflow->goOutFlowInfo);
                    break;
                case 9: // 出差
                    $content[] = '原因:' . $workflow->businessTripFlowInfo['reasons'];
                    unset($workflow->businessTripFlowInfo);
                    break;
                case 10: // 调休
                    $content[] = '开始时间:' . date('Y-m-d', $workflow->daysOffFlowInfo['start_time']);
                    $content[] = '结束时间:' . date('Y-m-d', $workflow->daysOffFlowInfo['end_time']);
                    unset($workflow->daysOffFlowInfo);
                    break;
                case 11: // 补勤
                    $content[] = '补勤时间:' . date('Y-m-d', $workflow->supplementFlowInfo['time']);
                    unset($workflow->supplementFlowInfo);
                    break;
                case 12:    // 加班费
                    // 获取workflow关联的加班费流程表的数据，命名格式：加班费+Info
//                    $content[] = '补勤时间:' . date('Y-m-d', $workflow->supplementFlowInfo['time']);
//                    unset($workflow->xxxxxxxxxx);
                    $content[] = '开始时间:' . date('Y-m-d', $workflow->overtimeWorkFeeFlowInfo['start_time']);
                    $content[] = '结束时间:' . date('Y-m-d', $workflow->overtimeWorkFeeFlowInfo['end_time']);
                    unset($workflow->overtimeWorkFeeFlowInfo);
                    break;
                case 13: // 领用申请
                    $content[] = '申报日期:' . date('Y-m-d', $workflow->receiveApplyForInfo['date']);
                    $content[] = '物品名:' . $workflow->receiveApplyForInfo['name'];
                    unset($workflow->receiveApplyForInfo);
                    break;
                case 14: // 采购
                    $content[] = '保质期(过期时间):' . date('Y-m-d', $workflow->procurementFlowInfo['deadline']);
                    $content[] = '物品名:' . $workflow->procurementFlowInfo['name'];
                    unset($workflow->procurementFlowInfo);
                    break;
                case 15: // 合同
                    $content[] = '合同名:' . $workflow->contractFlowInfo['name'];
                    $content[] = '用途:' . $workflow->contractFlowInfo['purpose'];
                    unset($workflow->contractFlowInfo);
                    break;
                case 16: // 备用金
                    $content[] = '申报日期:' . date('Y-m-d', $workflow->procurementFlowInfo['apply_date']);
                    $content[] = '金额(元):' . $workflow->procurementFlowInfo['money'];
                    unset($workflow->pettyCashFlowInfo);
                    break;
                case 17: // 制度方案
                    $content[] = '执行时间:' . date('Y-m-d', $workflow->systemSolutionsFlowInfo['do_time']);
                    $content[] = '名称:' . $workflow->systemSolutionsFlowInfo['name'];
                    unset($workflow->systemSolutionsFlowInfo);
                    break;
                case 18: // 招聘需求
                    $content[] = '招聘时间:' . date('Y-m-d', $workflow->recruitmentNeedsFlowInfo['requirement_time']);
                    $content[] = '岗位名称:' . $workflow->recruitmentNeedsFlowInfo['name'];
                    unset($workflow->recruitmentNeedsFlowInfo);
                    break;
                case 19: // 奖罚申报
                    $content[] = '奖罚时间:' . date('Y-m-d', $workflow->rewardAndPunishFlowInfo['time']);
                    $content[] = '奖罚方式:' . $workflow->rewardAndPunishFlowInfo['type'];
                    unset($workflow->rewardAndPunishFlowInfo);
                    break;
                case 20: // 离职
                    $content[] = '离职时间:' . date('Y-m-d', $workflow->dimissionFlowInfo['time']);
                    unset($workflow->dimissionFlowInfo);
                    break;
                case 21: // 转正晋升调薪
                    $content[] = '现工资:' . date('Y-m-d', $workflow->becomePromoteSalaryFlowInfo['current_salary']);
                    $content[] = '调薪工资:' . date('Y-m-d', $workflow->becomePromoteSalaryFlowInfo['adjustment_salary']);
                    unset($workflow->becomePromoteSalaryFlowInfo);
                    break;
                case 22: // 补签
                    switch ($workflow->replenishSignFlowInfo['type']) {
                        case 1:
                            $type = '上班';
                            break;
                        case 2:
                            $type = '上班';
                            break;
                        default:
                            $type = '其它';
                    }

                    $content[] = '补签种类:' . $type;
                    $content[] = '补签时间:' . date('Y-m-d', $workflow->replenishSignFlowInfo['time']);
                    unset($workflow->replenishSignFlowInfo);
                    break;
                case 23: // 面试
                    $content[] = '备注:' . $workflow->interviewFlowInfo['note'];
                    $workflow['resume_id'] = $workflow->interviewFlowInfo['job_resume_id'];

                    if ($workflow->interviewFlowInfo->resumeInfo) {
                        $workflow['title'] = $workflow->interviewFlowInfo->resumeInfo->full_name . '的'
                            . \App\Http\Admin\Administration\Models\Workflow::getRelatedTypeStr($workflow['related_type']);
                    }

                    unset($workflow->interviewFlowInfo);
                    break;
                case 24: // 入职
                    $content[] = '备注:' . $workflow->takingWorkFlowInfo['note'];
                    $workflow['resume_id'] = $workflow->takingWorkFlowInfo['job_resume_id'];

                    unset($workflow->takingWorkFlowInfo);
                    break;
                case 26: // 采购申请

                    //by lwl 2019 05 13 采购列表—更改状态文案；
                    $purchaseTmp = \App\Eloquent\Ygt\Purchase::where(['id'=>$workflow['related_id']])->first();
                    if( $purchaseTmp ){
                        $purchaseTmp = $purchaseTmp->toArray();
                        $workflow['status_str']= '待入库';

                        if( $purchaseTmp['is_able_in'] == 1 ){
                            $workflow['status_str']= '入库中';
                        }

                        if( $purchaseTmp['is_all_in'] == 1 || $workflow['dispose_code'] == 1100){
                            $workflow['status_str']= '已入库';

                            if($workflow['dispose_code'] == 1100)
                                $workflow['status_str'] = '采购完成';
                        }
                        //hjn 20190819 重复代码
//                        if ($workflow['dispose_code'] == 1100 ) {
//                            $workflow['status_str'] = '采购完成';
//                        }else{
//                            if( $purchaseTmp['is_able_in'] == 0 ){
//                                $workflow['status_str']= '待入库';
//                            }
//
//                            if( $purchaseTmp['is_able_in'] == 1 ){
//                                $workflow['status_str']= '入库中';
//                            }
//
//                            if( $purchaseTmp['is_all_in'] == 1 ){
//                                $workflow['status_str']= '已入库';
//                            }
//                        }

                    }

                    //by lwl 2019 05 13 采购列表—更改状态文案；end;
                    //获取采购申请的详情
                    $purchaseId = $workflow['related_id'];
                    $purchaseRow = \App\Eloquent\Ygt\Purchase::where(['id' => $purchaseId])->first()->toArray();
                    $purchaseMaterialList = \App\Eloquent\Ygt\PurchaseMaterial::where(['purchase_id' => $purchaseId])->get()->toArray();
                    $materialNameListStr = '';
                    $imageList = [];

                    $purchaseMoney = 0;
                    foreach ($purchaseMaterialList as $purchaseMaterialRow) {
                        //获取材料名称
                        $materialRow = \App\Engine\Product::getProductInfo($purchaseMaterialRow['material_id']);

                        //追加材料图片地址
                        if ($materialRow['img_id']) {
                            $imageList[] = \App\Eloquent\Ygt\ImgUpload::getImgUrlById($materialRow['img_id']);
                        }

                        //新增采购金额
                        $purchaseMoney += $purchaseMaterialRow['num'] * $purchaseMaterialRow['price'];


                        $materialNameListStr .= $materialRow['product_name'] . '、';
                    }

                    $materialNameListStr = trim($materialNameListStr, '、');

                    //获取供应商信息
                    $supplierName = config('default-value.purchase_list_default_supplier_name');
                    $tmpObj = \App\Eloquent\Ygt\SellerCompany::where(['id'=>$purchaseRow['supplier_id']])->first();
                    if($tmpObj){
                        $supplierName = $tmpObj->title;
                    }



                    $purchaseRow['purchase_number'] = \App\Engine\Common::changeSnCode($purchaseRow['purchase_number']);

                    $content = [
                        'supplier_name' => $supplierName,//采购供应商企业
                        'material_name_list_str' => "材料:".$materialNameListStr,//采购材料名称（所有）
                        'purchase_number' => YgtLabel::getLabel(YgtLabel::$LABEL_WAIT_PURCHASE).$purchaseRow['purchase_number'],//采购编号
                        'create_date' => date('Y-m-d', $purchaseRow['created_at']),//生成日期
                        'finished_date' => "交货日期:".$purchaseRow['finished_date'],//交货日期
                        'status' => $workflow['status'],//状态值
                        'status_str' => $workflow['status_str'],//状态提示
                        'is_warning' => 0,//状态提示
                        'image_list' => $imageList,//图片列表
                        'purchase_money' => "采购金额：¥".$purchaseMoney,//金额
                    ];

//                    $content[] = '备注:' . $workflow->takingWorkFlowInfo['note'];
//                    $workflow['resume_id'] = $workflow->takingWorkFlowInfo['job_resume_id'];
//
//                    unset($workflow->takingWorkFlowInfo);
                    break;
                case 27: // 退货申请
                    //获取退货申请的详情
                    $purchaseId = $workflow['related_id'];
                    $purchaseRow = \App\Eloquent\Ygt\ReturnPurchase::where(['id' => $purchaseId])->first()->toArray();
                    //by lwl 20190 05 15 修改退货日期，oa_workflow 表 related_type = 27 退货，related_id = $purchaseRow['id] dispose_code = 1100
                    $TmpReturnDate = \App\Eloquent\Oa\Workflow::where(['related_type'=>27,'related_id'=>$purchaseRow['id'],'dispose_code'=>1100])->first();
                    $ReturnDate = '';//退货日期；
                    if($TmpReturnDate){
                        $TmpReturnDate = $TmpReturnDate->toArray();
                        $ReturnDate = date("Y-m-d-H",$TmpReturnDate['updated_at']);
                    }

                    //by lwl 20190 05 15 修改退货日期，oa_workflow 表 related_type = 27 退货，related_id = $purchaseRow['id] dispose_code = 1100 end;
                    $purchaseMaterialList = \App\Eloquent\Ygt\ReturnPurchaseMaterial::where(['purchase_id' => $purchaseId])->get()->toArray();
                    $materialNameListStr = '';
                    $imageList = [];
                    $purchaseMoney = 0;
                    foreach ($purchaseMaterialList as $purchaseMaterialRow) {
                        //获取材料名称
                        $materialRow = \App\Engine\Product::getProductInfo($purchaseMaterialRow['material_id']);

                        //追加材料图片地址
                        if ($materialRow['img_id']) {
                            $imageList[] = \App\Eloquent\Ygt\ImgUpload::getImgUrlById($materialRow['img_id']);
                        }

                        //新增退货金额
                        $purchaseMoney += $purchaseMaterialRow['num'] * $purchaseMaterialRow['price'];


                        $materialNameListStr .= $materialRow['product_name'] . '、';
                    }

                    $materialNameListStr = trim($materialNameListStr, '、');

                    //获取供应商信息
                    $supplierName = config('default-value.purchase_list_default_supplier_name');
                    $tmpObj = \App\Eloquent\Ygt\SellerCompany::where(['id'=>$purchaseRow['supplier_id']])->first();
                    if($tmpObj){
                        $supplierName = $tmpObj->title;
                    }

                    $purchaseRow['purchase_number'] = \App\Engine\Common::changeSnCode($purchaseRow['purchase_number']);
                    $content = [
                        'supplier_name' => $supplierName,//采购供应商企业
                        'material_name_list_str' => "材料:".$materialNameListStr,//采购材料名称（所有）
                        'purchase_number' => YgtLabel::getLabel(YgtLabel::$LABEL_QUIT_PURCHASE).$purchaseRow['purchase_number'],//采购编号
                        'create_date' => date('Y-m-d', $purchaseRow['created_at']),//生成日期
//                        'finished_date' => '',//交货日期
                        //这个字段作为退货日期展示
                        'finished_date' => "退货日期：".$ReturnDate,//退货日期  //by lwl 20119 05 15 修改退货日期
                        'status' => $workflow['status'],//状态值
                        'status_str' => $workflow['status_str'],//状态提示
                        'is_warning' => 0,//状态提示
                        'image_list' => $imageList,//图片列表
                        'purchase_money' => "退货金额：¥".$purchaseMoney,//金额
                    ];

                    break;
                default:
                    xThrow(ERR_PARAMETER);
            }
            $workflow['content'] = $content;
            unset($workflow['creatorInfo']);
        }
        return [$workflowList, $total];
    }

    // 2.7抄送已读状态更变
    public function copyRead($workflowId = 0, $contactsId = 0, $copyId = 0, $isRead = 1)
    {
        $copy = WorkflowCopy::where('oa_workflow_id', '=', $workflowId)
            ->where('copy_to_id', '=', $contactsId)
            ->first();
        if (empty($copy)) {
            $copy = WorkflowCopy::find($copyId);
            if (empty($copy)) xThrow(ERR_PARAMETER, 'copy not found');
        }
        $copy['is_read'] = $isRead;
        xAssert($copy->save());
        return;
    }

    // 流程菜单
    public function permission()
    {
        $data = [
            [
                'name' => '人事行政',
                'submenu' => [
                    [
                        'id' => 6,
                        'name' => '外出',
                        'url' => env('APP_URL') . '/assets/api/images/workflow-icon/icon-wc@2x.png',
                    ],
                    [
                        'id' => 7,
                        'name' => '出差',
                        'url' => env('APP_URL') . '/assets/api/images/workflow-icon/icon-cc@2x.png',
                    ],
                    [
                        'id' => 8,
                        'name' => '调休',
                        'url' => env('APP_URL') . '/assets/api/images/workflow-icon/icon-tx@2x.png',
                    ],
                    [
                        'id' => 1,
                        'name' => '请假',
                        'url' => env('APP_URL') . '/assets/api/images/workflow-icon/icon-qj@2x.png',
                    ],
                    [
                        'id' => 9,
                        'name' => '补勤',
                        'url' => env('APP_URL') . '/assets/api/images/workflow-icon/icon-bqi@2x.png',
                    ],
                ],
            ],
            [
                'name' => '财务',
                'submenu' => [
                    [
                        'id' => 5,
                        'name' => '报销',
                        'url' => env('APP_URL') . '/assets/api/images/workflow-icon/icon-bx@2x.png',
                    ],
                ],
            ],
            [
                'name' => '工作流程',
                'submenu' => [
//                    [
//                        'id'   => 7,
//                        'name' => '公文管理',
//                        'url'  => env('APP_URL') . '/assets/api/images/workflow-icon/icon-gwgl@2x.png',
//                    ],
                    [
                        'id' => 3,
                        'name' => '用车管理',
                        'url' => env('APP_URL') . '/assets/api/images/workflow-icon/icon-ycgl@2x.png',
                    ],
                    [
                        'id' => 2,
                        'name' => '用章管理',
                        'url' => env('APP_URL') . '/assets/api/images/workflow-icon/icon-yzgl@2x.png',
                    ],
                ],
            ],
        ];
        return $data;
    }

    public function getRecentTime($time)
    {
        $now = time();
        if (!is_numeric($time)) $time = strtotime($time);
        $unit = floor(($now - $time) / (86400 * 365));
        if ($unit > 0) return $unit . '年前';
        $unit = floor(($now - $time) / (86400 * 30));
        if ($unit > 0) return $unit . '月前';
        $unit = floor(($now - $time) / (86400));
        if ($unit > 0) return $unit . '天前';
        $unit = floor(($now - $time) / (3600));
        if ($unit > 0) return $unit . '小时前';
        $unit = floor(($now - $time) / (60));
        if ($unit > 0) return $unit . '分钟前';
        $unit = $now - $time;
        return $unit . '秒前';


//        $now = Carbon::now();
//        if (!is_numeric($time)) $time = strtotime($time);
//        $time = Carbon::createFromTimestamp($time);
//        $unit = $now->year - $time->year;
//        if ($unit > 0) return $unit . '年前';
//        $unit = $now->month - $time->month;
//        if ($unit > 0) return $unit . '月前';
//        $unit = $now->day - $time->day;
//        if ($unit > 0) return $unit . '天前';
//        $unit = $now->hour - $time->hour;
//        if ($unit > 0) return $unit . '小时前';
//        $unit = $now->minute - $time->minute;
//        if ($unit > 0) return $unit . '分钟前';
//        $unit = $now->second - $time->second;
//        return $unit . '秒前';
    }
}