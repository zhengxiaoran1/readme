<?php
/**
 * 工单类，提供与工单相关的各种方法
 * Created by PhpStorm.
 * User: zhuyujun
 * Date: 2017/10/18
 * Time: 14:25
 */

namespace App\Engine;

use App\Eloquent\Zk\CustomerOrder;
use Framework\BaseClass\Api\Controller;
use Illuminate\Http\Request;

use App\Eloquent\Zk\Order;
use App\Eloquent\Zk\OrderPre;
use App\Eloquent\Zk\UserMessage;
use App\Eloquent\Zk\OrderBasic;
use App\Eloquent\Zk\OrdertypeCategory;
//use App\Eloquent\Ygt\OrderType;
use App\Eloquent\Zk\OrderDistributionPre;
use App\Eloquent\Zk\OrderDistribution;
use App\Eloquent\Zk\OrderProcessPre;
use App\Eloquent\Zk\OrderProcess;
use App\Eloquent\Zk\OrderMaterial;
use App\Eloquent\Zk\DepartmentUser;
use App\Eloquent\Zk\Message;
use App\Eloquent\Zk\OrderProcessCourse;
use App\Eloquent\Zk\OrderMaterialCourse;
use App\Eloquent\Zk\Privilege;
use App\Eloquent\Zk\OrderReport;
use App\Eloquent\Zk\OrderReportType;
use App\Eloquent\Zk\OrderReportCell;
use App\Eloquent\Zk\OrderListRelation;
use App\Eloquent\Zk\OrderFieldCompany;

use App\Eloquent\Zk\ImgUpload;
use App\Eloquent\Zk\ProcessFieldRelationSelect;
use App\Eloquent\Zk\ProcessFieldRelationUnit;
use App\Eloquent\Zk\ProcessField;

use App\Eloquent\Province;
use App\Eloquent\City;
use App\Eloquent\Area;

//use App\Eloquent\Ygt\Process;
//use App\Eloquent\Ygt\Product;
//use App\Eloquent\Ygt\Permission;
use App\Engine\OrderType;
use App\Engine\Stock;
use App\Engine\Func;
use App\Engine\Product as ProductEngine;
use App\Engine\Permission as PermissionEngine;


use Framework\Services\ImageUpload\imageProcess;

class OrderEngine
{
    /**
     * Description:创建工单-基础页面字段
     * User: zhuyujun
     */
    public function orderBaseFieldList($companyId)
    {
        return OrderFieldCompany::getActiveFieldList($companyId);
//        return [
//            [
//                'field_name' => 'customer_name',
//                'title' => '客户',
//                'field_type' => 1,
//                'placeholder' => '请填写',
//                'relation_id' => '',
//                'is_must' => 0,
//                'field_unit' => [],
//                'data' => [],
//            ],
//            [
//                'field_name' => 'product_name',
//                'title' => '品名',
//                'field_type' => 1,
//                'placeholder' => '请填写',
//                'relation_id' => '',
//                'is_must' => 0,
//                'field_unit' => [],
//                'data' => [],
//            ],
//            [
//                'field_name' => 'contract_number',
//                'title' => '合同编号',
//                'field_type' => 1,
//                'placeholder' => '请填写',
//                'relation_id' => '',
//                'is_must' => 0,
//                'field_unit' => [],
//                'data' => [],
//            ],
//            [
//                'field_name' => 'finished_date',
//                'title' => '交货日期',
//                'field_type' => 7,
//                'placeholder' => '请填写',
//                'relation_id' => '',
//                'is_must' => 0,
//                'field_unit' => [],
//                'data' => [],
//            ],
//            [
//                'field_name' => 'add_distribution_address',
//                'title' => '添加配送地址',
//                'field_type' => 8,
//                'placeholder' => '请填写',
//                'relation_id' => '',
//                'is_must' => 0,
//                'field_unit' => [],
//                'data' => [],
//            ],
//            [
//                'field_name' => 'product_num',
//                'title' => '产品数量',
//                'field_type' => 5,
//                'placeholder' => '请填写',
//                'relation_id' => '',
//                'is_must' => 0,
//                'field_unit' => [
//                    [
//                        'id' => 1,
//                        'title' => '米'
//                    ],
//                    [
//                        'id' => 2,
//                        'title' => '条'
//                    ]
//                ],
//                'data' => [],
//            ],
//            [
//                'field_name' => 'is_priority',
//                'title' => '优先处理',
//                'field_type' => 6,
//                'placeholder' => '',
//                'relation_id' => '',
//                'is_must' => 0,
//                'field_unit' => [],
//                'data' => [],
//            ],
//            [
//                'field_name' => 'production_case_diagram',
//                'title' => '生产实例图',
//                'field_type' => 9,
//                'placeholder' => '请上传',
//                'new_page_title' => '上传企业图标',
//                'relation_id' => '',
//                'is_must' => 0,
//                'field_unit' => [],
//                'data' => [],
//            ]
//        ];
    }


    //获取工序管理员分派列表
    public function distributionAction($orderTypeId)
    {
        $result = OrderType::getAllStepsWithDistribution($orderTypeId);

        $result->transform(function ($item) {
//            $item->distributionUser = Privilege::getWithDepartmentUser( $item->distribution );
            $distributionUser = DepartmentUser::getWithPrivilege($item->distribution);
            $process_id = $item->process_id;
            $distributionUser->transform(function ($item) use ($process_id) {
                $item->process_id = $process_id;
                $item->ygtUser->avatar = $item->ygtUser->avatar ? Func::getImgUrlById($item->ygtUser->avatar) : asset('upload/appicon/logo.png');
                return $item;
            });
            $item->distributionUser = $distributionUser->toArray();
            return $item;
        });

        $result = $result->filter(function ($item) {
            if ($item->distribution->toArray()) return true;
            return false;
        });
        return $result;
    }

    //确认派发
    public function orderConfirmAssignment($orderId, $assignUser, $userId)
    {
        //获取工单详情
        $tmpOrderObj = Order::where(['id' => $orderId])->first();
        $tmpOrderRow = $tmpOrderObj->toArray();
        $orderTitle = $tmpOrderRow['order_title'];
        $orderTypeId = $tmpOrderRow['order_type'];
        $companyId = $tmpOrderRow['company_id'];
        $foreignKey = intval($tmpOrderRow['customer_order_id']);

        //消息内容需要的
        $orderInfoProductNum    = $tmpOrderObj['product_num'];
        $orderInfoProductNum    = str_replace(',','',$orderInfoProductNum);
        $orderInfoSpecification = $tmpOrderObj['finished_specification'];
        $fieldName23            = $this->changeFieldName23($tmpOrderRow['field_name_23']);
        $productName = \App\Engine\OrderEngine::getOrderFiledValueTrue($tmpOrderRow['product_name'],20);

        $messageContent         = "单位名称：{$fieldName23}rnrn品名：{$productName}rnrn";
        $messageContent         .= "成品规格：{$orderInfoSpecification}rnrn数量：{$orderInfoProductNum}rnrn";
        $messageContent         .= "交货日期：{$tmpOrderRow['finished_date']}";

        ////追加功能，给没有设置分派角色的工序增加默认工序管理员（下单人）
        $result = OrderType::getAllStepsWithDistribution($tmpOrderRow['order_type']);
        //获取工序可分派给工序管理员的列表
        $result->transform(function ($item) {
//            $item->distributionUser = Privilege::getWithDepartmentUser( $item->distribution );
            $distributionUser = DepartmentUser::getWithPrivilege($item->distribution);
            $process_id = $item->process_id;
            $distributionUser->transform(function ($item) use ($process_id) {
                $item->process_id = $process_id;
                $item->ygtUser->avatar = $item->ygtUser->avatar ? Func::getImgUrlById($item->ygtUser->avatar) : asset('upload/appicon/logo.png');
                return $item;
            });
            $item->distributionUser = $distributionUser->toArray();
            return $item;
        });

        $noAssignProcessIdList = [];//无需分派的工序id列表
        foreach ($result as $tmpValue) {
            if (empty($tmpValue['distributionUser'])) {
                $noAssignProcessIdList[] = $tmpValue['process_id'];
                //先分配工单给自己
                $orderProcessCollectionList = OrderProcess::where(['order_id' => $orderId, 'process_type' => $tmpValue['process_id']])->get();
                foreach ($orderProcessCollectionList as $orderProcessCollectionRow) {
                    $orderProcessCollectionRow->uid = $tmpOrderRow['uid'];
                    $orderProcessCollectionRow->save();
                }
            }
        }

        //分配用户
        foreach ($assignUser as $assignUserRow) {
            $tmpProcessId = $assignUserRow['process_id'];
            $orderProcess = OrderProcess::firstOrNew(['order_id' => $orderId, 'process_type' => $tmpProcessId]);
            if ($orderProcess->count()) {
                $uid = $assignUserRow['uid'];
                $orderProcess->uid = $uid;
                $orderProcess->save();
            }
        }

        //获取下单人信息
        $userRow = DepartmentUser::getCurrentInfo($tmpOrderRow['uid'])->toArray();

        //获取第一道步骤涉及的工序
        $processFirstList = OrderType::getFirstProcessBag($tmpOrderRow['order_type'])->toArray();
        //获取工单对应的工单工序列表
        $tmpOrderProcessList = OrderProcess::where(['order_id' => $orderId])->get();
        foreach ($tmpOrderProcessList as $tmpOrderProcessRow) {
            //获取工序对应的权限用户
            $processId = $tmpOrderProcessRow['process_type'];
//                $permissionId = Permission::getIdByProcessId($processId);
//                $permissionId = 1;
            if (in_array($tmpOrderProcessRow['process_type'], $processFirstList)) {//给第一道工艺的人发信息
                if (in_array($tmpOrderProcessRow['process_type'], $noAssignProcessIdList)) {//未分派工序管理员，直接分派给员工
                    //获取有提交权限的角色id
                    $privilegeIds = PermissionEngine::getStepActionPrivilegeIdsByOrderTypeProcess($orderTypeId, $processId, 4); //1:查看;2:可写;3:分配;4:提交;5:转发;
                    //获取角色下的用户id
                    $userList = DepartmentUser::getWithPrivilege($privilegeIds);

                    foreach ($userList as $tmpUserRow) {
                        $tmpUid = $tmpUserRow['user_id'];
                        $orderProcessCourseObj = OrderProcessCourse::firstOrNew(['order_process_id' => $tmpOrderProcessRow['id'], 'uid' => $tmpUid]);
                        $tmpInsertRow = [
                            'uid' => $tmpUid,
                            'total_number' => 0,//自动分派，需完成数量默认为0
                            'order_process_id' => $tmpOrderProcessRow['id'],
                            'assign_id' => $userId,//派发人id
                            'status' => 1,//状态 1-未接单
                            'company_id' => $companyId,//企业Id
                        ];
                        $orderProcessCourseObj->fill($tmpInsertRow);
                        $orderProcessCourseObj->save();
                        $orderPorcessCourseId = $orderProcessCourseObj->id;


                        //发送消息
                        //管理员给员工发消息
                        $userInfo = DepartmentUser::getCurrentInfo($userId)->toArray();
                        $data           = [
                            'company_id'=>$companyId,  'privilege_id'=>'',
                            'form_user_id'=>$userId, 'to_user_id'=>$tmpUid,
                            'foreign_key'=>$foreignKey,
                            'type'=>1,'type_id'=>$orderPorcessCourseId,
                            'title'=>$tmpOrderRow['order_title'],'content'=>$messageContent
                        ];
                        UserMessage::sendCustomerOrderMessage($data);
                        //数据存入

                    }

                    //修改工序工单状态为2（已接单）
                    $tmpOrderProcessRow->status = 2;
                    $tmpOrderProcessRow->save();

                } else {//分派给工序管理员
                    //下单人给管理员发消息
                    $data           = [
                        'company_id'=>$tmpOrderProcessRow['company_id'],  'privilege_id'=>'',
                        'form_user_id'=>$userId, 'to_user_id'=>$tmpOrderProcessRow['uid'],
                        'foreign_key'=>$foreignKey,
                        'type'=>2,'type_id'=>$tmpOrderProcessRow['id'],
                        'title'=>$orderTitle,'content'=>$messageContent
                    ];
                    UserMessage::sendCustomerOrderMessage($data);
                }

                //给此步骤有查看权限的人发消息
                $processId = $tmpOrderProcessRow['process_type'];
                $orderTypeId = $tmpOrderRow['order_type'];
                $privilegeIds = PermissionEngine::getStepActionPrivilegeIdsByOrderTypeProcess($orderTypeId, $processId, 1);//1:查看;2:可写;3:分配;4:提交;5:转发;
                //批量给所有角色发
                foreach ($privilegeIds as $val) {
                    $data           = [
                        'company_id'=>$tmpOrderProcessRow['company_id'],'privilege_id'=>$val,
                        'form_user_id'=>$userId, 'to_user_id'=>$tmpOrderProcessRow['uid'],
                        'foreign_key'=>$foreignKey,
                        'type'=>9,'type_id'=>$tmpOrderProcessRow['id'],
                        'title'=>$orderTitle,'content'=>$messageContent
                    ];
                    UserMessage::sendCustomerOrderMessage($data);
                }

            }
        }

        //修改工单状态
        $tmpOrderObj->status = 1;
        $tmpOrderObj->save();

        return [
            'code' => 0,
            'msg' => '操作成功'
        ];
    }


    //导入工单列表关联表
    public static function importOrderListRelation($uid = '', $type = '', $companyId = '')
    {
        ////导入主工单
        $where = [];
        $orderList = Order::where($where)->get()->toArray();
        foreach ($orderList as $orderRow){
            $uid = $orderRow['uid'];
            $type = 1;
            $companyId = $orderRow['company_id'];
            $orderTitle = $orderRow['order_title'];
            $orderCreateTime = $orderRow['created_at'];
            $relateId = $orderRow['id'];

            $orderListRelationObj = OrderListRelation::firstOrCreate(['uid'=>$uid,'type'=>$type,'company_id'=>$companyId,'relate_id'=>$relateId]);
            //存入工单列表关联表的信息
            $insertOrderListRelationRow = [];
            $insertOrderListRelationRow['uid'] = $uid;
            $insertOrderListRelationRow['type'] = $type;
            $insertOrderListRelationRow['company_id'] = $companyId;
            $insertOrderListRelationRow['order_title'] = $orderTitle;
            $insertOrderListRelationRow['order_create_time'] = $orderCreateTime;
            $insertOrderListRelationRow['relate_id'] = $relateId;
            $insertOrderListRelationRow['content'] = serialize($orderRow);
            $insertOrderListRelationRow['customer_id'] = $orderRow['customer_name'];
            $insertOrderListRelationRow['plate_id'] = $orderRow['plate_id'];
            $insertOrderListRelationRow['status'] = $orderRow['status'];

            $orderListRelationObj->fill($insertOrderListRelationRow);
            $orderListRelationObj->save();
        }

        ////导入工序工单&&工序工单能查看的人
        //工序管理员查看的工单
        $where = [];
        $orderProcessList = OrderProcess::where($where)->get()->toArray();
        foreach ($orderProcessList as $orderProcessRow){
            $uid = $orderProcessRow['uid'];
            $type = 2;
            $companyId = $orderProcessRow['company_id'];
            $orderCreateTime = $orderProcessRow['created_at'];
            $relateId = $orderProcessRow['id'];
            $orderId = $orderProcessRow['order_id'];

            //获取对应主工单的信息
            $orderRow = Order::where('id',$orderId)->first();
            if(!$orderRow){
                continue;
            }
            $orderRow = $orderRow->toArray();
            $orderTitle = $orderRow['order_title'];




            $orderListRelationObj = OrderListRelation::firstOrCreate(['uid'=>$uid,'type'=>$type,'company_id'=>$companyId,'relate_id'=>$relateId]);
            //存入工单列表关联表的信息
            $insertOrderListRelationRow = [];
            $insertOrderListRelationRow['uid'] = $uid;
            $insertOrderListRelationRow['type'] = $type;
            $insertOrderListRelationRow['company_id'] = $companyId;
            $insertOrderListRelationRow['order_title'] = $orderTitle;
            $insertOrderListRelationRow['order_create_time'] = $orderCreateTime;
            $insertOrderListRelationRow['relate_id'] = $relateId;
            $insertOrderListRelationRow['content'] = serialize($orderRow);
            $insertOrderListRelationRow['customer_id'] = $orderRow['customer_name'];
            $insertOrderListRelationRow['plate_id'] = $orderRow['plate_id'];
            $insertOrderListRelationRow['status'] = $orderProcessRow['status'];

            $orderListRelationObj->fill($insertOrderListRelationRow);
            $orderListRelationObj->save();


            //查看权限的人查看的工单
            //获取该工单有查看权限的用户
            $processId = $orderProcessRow['process_type'];
            $orderTypeId = $orderRow['order_type'];
            $privilegeIds = PermissionEngine::getStepActionPrivilegeIdsByOrderTypeProcess($orderTypeId, $processId, 1);//1:查看;2:可写;3:分配;4:提交;5:转发;
            //获取角色下的用户id
            $userList = DepartmentUser::getWithPrivilege($privilegeIds);
            foreach ($userList as $userRow){
                $seeUid = $userRow['user_id'];

                //其他限制，同一个工单，工序管理员不需要展示查看权限的工单
                if($seeUid == $uid){
                    continue;
                }

                $type = 3;
                $insertOrderListRelationRow['type'] = $type;
                $insertOrderListRelationRow['uid'] = $seeUid;
                $orderListRelationObj = OrderListRelation::firstOrCreate(['uid'=>$uid,'type'=>$type,'company_id'=>$companyId,'relate_id'=>$relateId]);
                $orderListRelationObj->fill($insertOrderListRelationRow);
                $orderListRelationObj->save();
            }
        }

        ////导入员工工单
        $where = [];
        $orderProcessCourseList = OrderProcessCourse::where($where)->get()->toArray();
        foreach ($orderProcessCourseList as $orderProcessCourseRow){
            $uid = $orderProcessCourseRow['uid'];
            $type = 4;
            $companyId = $orderProcessCourseRow['company_id'];
            $orderCreateTime = $orderProcessCourseRow['created_at'];
            $relateId = $orderProcessCourseRow['id'];
            $orderProcessId = $orderProcessCourseRow['order_process_id'];

            //获取工序工单信息&&主工单信息
            $orderProcessRow = OrderProcess::where('id',$orderProcessId)->first();
            if($orderProcessRow){
                $orderProcessRow = $orderProcessRow->toArray();
            }else{
                continue;
            }
            $orderId = $orderProcessRow['order_id'];
            $orderRow = Order::where('id',$orderId)->first()->toArray();
            $orderTitle = $orderRow['order_title'];
            $processId = $orderProcessRow['process_type'];

            //获取工序字段信息（工单列表需要展示前三个）
            //获取工序对应的字段
//            $tmpFieldList = Process::getFieldListByProcessId($processId);
            $count = 0;//计数
            $firstPropertyName = '成品规格';
            $firstPropertyValue = isset($orderRow['finished_specification']) ? $orderRow['finished_specification'] : '';
            $secondPropertyName = '数量';
            $secondPropertyValue = $orderRow['product_num'];
            $thirdPropertyName = '交货日期';
            $thirdPropertyValue = $orderRow['finished_date'];

//            foreach ($tmpFieldList as $tmpFieldRow) {
//                //值转换
//                if ($tmpFieldRow['field_type'] == 5) {//有单位 数量+单位
//                    if ($orderProcessRow[$tmpFieldRow['field_name']]) {
//                        $tmpArr = explode(',', $orderProcessRow[$tmpFieldRow['field_name']]);
//                        $showNum = $tmpArr[0];
//                        if (isset($tmpArr[1])) {
//                            $unitId = $tmpArr[1];
//                        } else {
//                            $unitId = '';
//                        }
//                        $orderProcessRow[$tmpFieldRow['field_name']] = $showNum . ' ' . $unitId;
//                    } else {
//                        $orderProcessRow[$tmpFieldRow['field_name']] = '';
//                    }
//                } elseif ($tmpFieldRow['field_type'] == 6) {//开关
//                    $orderProcessRow[$tmpFieldRow['field_name']] = $orderProcessRow[$tmpFieldRow['field_name']] ? '是' : '否';
//                } elseif ($tmpFieldRow['field_type'] == 4) {//材料选择暂时不显示在
//                    continue;
//                }
//
//                if ($count == 0) {
//                    $firstPropertyName = $tmpFieldRow['title'];
//                    $firstPropertyValue = $orderProcessRow[$tmpFieldRow['field_name']];
//                } elseif ($count == 1) {
//                    $secondPropertyName = $tmpFieldRow['title'];
//                    $secondPropertyValue = $orderProcessRow[$tmpFieldRow['field_name']];
//                } elseif ($count == 2) {
//                    $thirdPropertyName = $tmpFieldRow['title'];
//                    $thirdPropertyValue = $orderProcessRow[$tmpFieldRow['field_name']];
//                } else {
//                    break;
//                }
//                $count++;
//            }

            $orderListRelationObj = OrderListRelation::firstOrCreate(['uid'=>$uid,'type'=>$type,'company_id'=>$companyId,'relate_id'=>$relateId]);
            //存入工单列表关联表的信息
            $insertOrderListRelationRow = [];
            $insertOrderListRelationRow['uid'] = $uid;
            $insertOrderListRelationRow['type'] = $type;
            $insertOrderListRelationRow['company_id'] = $companyId;
            $insertOrderListRelationRow['order_title'] = $orderTitle;
            $insertOrderListRelationRow['order_create_time'] = $orderCreateTime;
            $insertOrderListRelationRow['relate_id'] = $relateId;
            $insertOrderListRelationRow['content'] = serialize($orderRow);
            $insertOrderListRelationRow['customer_id'] = $orderRow['customer_name'];
            $insertOrderListRelationRow['plate_id'] = $orderRow['plate_id'];
            $insertOrderListRelationRow['status'] = $orderProcessCourseRow['status'];

            $orderListRelationObj->fill($insertOrderListRelationRow);
            $orderListRelationObj->save();
        }
    }

    //导入主工单
    public static function importMainOrder($orderRow){
        $uid = $orderRow['uid'];
        $type = 1;
        $companyId = $orderRow['company_id'];
        $orderCreateTime = $orderRow['created_at'];
        $relateId = $orderRow['id'];
        $orderTitle = $orderRow['order_title'];

        $orderListRelationObj = OrderListRelation::firstOrCreate(['uid'=>$uid,'type'=>$type,'company_id'=>$companyId,'relate_id'=>$relateId]);
        //存入工单列表关联表的信息
        $insertOrderListRelationRow = [];
        $insertOrderListRelationRow['uid'] = $uid;
        $insertOrderListRelationRow['type'] = $type;
        $insertOrderListRelationRow['company_id'] = $companyId;
        $insertOrderListRelationRow['order_title'] = $orderTitle;
        $insertOrderListRelationRow['order_create_time'] = $orderCreateTime;
        $insertOrderListRelationRow['relate_id'] = $relateId;
        $insertOrderListRelationRow['content'] = serialize($orderRow);
        $insertOrderListRelationRow['customer_id'] = $orderRow['customer_name'];
        $insertOrderListRelationRow['plate_id'] = $orderRow['plate_id'];
        $insertOrderListRelationRow['status'] = $orderRow['status'];

        $orderListRelationObj->fill($insertOrderListRelationRow);
        $orderListRelationObj->save();
    }

    //导入工序管理员工单
    public static function importProcessManagerOrder($orderProcessRow){
        $uid = $orderProcessRow['uid'];
        $type = 2;
        $companyId = $orderProcessRow['company_id'];
//        $orderCreateTime = $orderProcessRow['created_at'];
        $orderCreateTime = time();//调整为派发时间
        $relateId = $orderProcessRow['id'];
        $orderId = $orderProcessRow['order_id'];

        //获取对应主工单的信息
        $orderRow = Order::where('id',$orderId)->first();
        if(!$orderRow){
            return false;
        }
        $orderRow = $orderRow->toArray();
        $orderTitle = $orderRow['order_title'];

        $orderListRelationObj = OrderListRelation::firstOrCreate(['uid'=>$uid,'type'=>$type,'company_id'=>$companyId,'relate_id'=>$relateId]);
        //存入工单列表关联表的信息
        $insertOrderListRelationRow = [];
        $insertOrderListRelationRow['uid'] = $uid;
        $insertOrderListRelationRow['type'] = $type;
        $insertOrderListRelationRow['company_id'] = $companyId;
        $insertOrderListRelationRow['order_title'] = $orderTitle;
        $insertOrderListRelationRow['order_create_time'] = $orderCreateTime;
        $insertOrderListRelationRow['relate_id'] = $relateId;
        $insertOrderListRelationRow['content'] = serialize($orderRow);
        $insertOrderListRelationRow['customer_id'] = $orderRow['customer_name'];
        $insertOrderListRelationRow['plate_id'] = $orderRow['plate_id'];
        $insertOrderListRelationRow['status'] = $orderProcessRow['status'];

        //增加派发时间 zhuyujun 20190618
        $insertOrderListRelationRow['distribute_time'] = time();

        $orderListRelationObj->fill($insertOrderListRelationRow);
        $orderListRelationObj->save();
    }

    //导入查看工序工单
    public static function importProcessSeeOrder($orderProcessRow,$privilegeIds){
        $uid = $orderProcessRow['uid'];
        $type = 3;
        $companyId = $orderProcessRow['company_id'];
//        $orderCreateTime = $orderProcessRow['created_at'];
        $orderCreateTime = time();//调整为派发时间
        $relateId = $orderProcessRow['id'];
        $orderId = $orderProcessRow['order_id'];

        //其他限制，同一个工单，工序管理员不需要展示查看权限的工单


        //获取对应主工单的信息
        $orderRow = Order::where('id',$orderId)->first();
        if(!$orderRow){
            return false;
        }
        $orderRow = $orderRow->toArray();
        $orderTitle = $orderRow['order_title'];

        //获取角色下的用户id
        $userList = DepartmentUser::getWithPrivilege($privilegeIds);
        foreach ($userList as $userRow){
            $seeUid = $userRow['user_id'];

            //其他限制，同一个工单，工序管理员不需要展示查看权限的工单
            if($seeUid == $uid){
                continue;
            }

            $insertOrderListRelationRow = [];
            $insertOrderListRelationRow['uid'] = $seeUid;
            $insertOrderListRelationRow['type'] = $type;
            $insertOrderListRelationRow['company_id'] = $companyId;
            $insertOrderListRelationRow['order_title'] = $orderTitle;
            $insertOrderListRelationRow['order_create_time'] = $orderCreateTime;
            $insertOrderListRelationRow['relate_id'] = $relateId;
            $insertOrderListRelationRow['content'] = serialize($orderRow);
            $insertOrderListRelationRow['customer_id'] = $orderRow['customer_name'];
            $insertOrderListRelationRow['plate_id'] = $orderRow['plate_id'];
            $insertOrderListRelationRow['status'] = $orderProcessRow['status'];

            //增加派发时间 zhuyujun 20190618
            $insertOrderListRelationRow['distribute_time'] = time();

            $orderListRelationObj = OrderListRelation::firstOrNew(['uid'=>$seeUid,'type'=>$type,'company_id'=>$companyId,'relate_id'=>$relateId]);
            $orderListRelationObj->fill($insertOrderListRelationRow);
            $orderListRelationObj->save();
        }
    }

    //导入员工工单
    public static function importProcessCourseOrder($orderProcessCourseRow){
        $uid = $orderProcessCourseRow['uid'];
        $type = 4;
        $companyId = $orderProcessCourseRow['company_id'];
        $orderCreateTime = $orderProcessCourseRow['created_at'];
        $relateId = $orderProcessCourseRow['id'];
        $orderProcessId = $orderProcessCourseRow['order_process_id'];

        //获取工序工单信息&&主工单信息
        $orderProcessRow = OrderProcess::where('id',$orderProcessId)->first()->toArray();
        $orderId = $orderProcessRow['order_id'];
        $orderRow = Order::where('id',$orderId)->first()->toArray();
        $orderTitle = $orderRow['order_title'];
        $processId = $orderProcessRow['process_type'];

        //获取工序字段信息（工单列表需要展示前三个）
        //获取工序对应的字段
//        $tmpFieldList = Process::getFieldListByProcessId($processId);
//        $count = 0;//计数
//        $firstPropertyName = '';
//        $firstPropertyValue = '';
//        $secondPropertyName = '';
//        $secondPropertyValue = '';
//        $thirdPropertyName = '';
//        $thirdPropertyValue = '';
//        foreach ($tmpFieldList as $tmpFieldRow) {
//            //值转换
//            if ($tmpFieldRow['field_type'] == 5) {//有单位 数量+单位
//                if ($orderProcessRow[$tmpFieldRow['field_name']]) {
//                    $tmpArr = explode(',', $orderProcessRow[$tmpFieldRow['field_name']]);
//                    $showNum = $tmpArr[0];
//                    if (isset($tmpArr[1])) {
//                        $unitId = $tmpArr[1];
//                    } else {
//                        $unitId = '';
//                    }
//                    $orderProcessRow[$tmpFieldRow['field_name']] = $showNum . ' ' . $unitId;
//                } else {
//                    $orderProcessRow[$tmpFieldRow['field_name']] = '';
//                }
//            } elseif ($tmpFieldRow['field_type'] == 6) {//开关
//                $orderProcessRow[$tmpFieldRow['field_name']] = $orderProcessRow[$tmpFieldRow['field_name']] ? '是' : '否';
//            } elseif ($tmpFieldRow['field_type'] == 4) {//材料选择暂时不显示在
//                continue;
//            }
//
//            if ($count == 0) {
//                $firstPropertyName = $tmpFieldRow['title'];
//                $firstPropertyValue = $orderProcessRow[$tmpFieldRow['field_name']];
//            } elseif ($count == 1) {
//                $secondPropertyName = $tmpFieldRow['title'];
//                $secondPropertyValue = $orderProcessRow[$tmpFieldRow['field_name']];
//            } elseif ($count == 2) {
//                $thirdPropertyName = $tmpFieldRow['title'];
//                $thirdPropertyValue = $orderProcessRow[$tmpFieldRow['field_name']];
//            } else {
//                break;
//            }
//            $count++;
//        }

        $orderListRelationObj = OrderListRelation::firstOrCreate(['uid'=>$uid,'type'=>$type,'company_id'=>$companyId,'relate_id'=>$relateId]);
        //存入工单列表关联表的信息
        $insertOrderListRelationRow = [];
        $insertOrderListRelationRow['uid'] = $uid;
        $insertOrderListRelationRow['type'] = $type;
        $insertOrderListRelationRow['company_id'] = $companyId;
        $insertOrderListRelationRow['order_title'] = $orderTitle;
        $insertOrderListRelationRow['order_create_time'] = $orderCreateTime;
        $insertOrderListRelationRow['relate_id'] = $relateId;
        $insertOrderListRelationRow['content'] = serialize($orderRow);
//        $insertOrderListRelationRow['content'] = serialize([
//            'order_title' => $orderRow['order_title'],
//            'create_date' => $orderRow['created_at'],
//            'customer_name' => $orderRow['customer_name'],
//            'product_name' => $orderRow['product_name'],
//            'finished_date' => $orderRow['finished_date'],
//            'finished_specification' => $orderRow['finished_specification'],//新增成品规格
//            'finished_date' => $orderRow['finished_date'],
//            'product_num' => $orderRow['product_num'],
//            'production_case_diagram' => $orderRow['production_case_diagram'],//生产示例图
//        ]);
        $insertOrderListRelationRow['customer_id'] = $orderRow['customer_name'];
        $insertOrderListRelationRow['plate_id'] = $orderRow['plate_id'];
        $insertOrderListRelationRow['status'] = $orderProcessCourseRow['status'];

        //增加派发时间 zhuyujun 20190618
        $insertOrderListRelationRow['distribute_time'] = time();

        $orderListRelationObj->fill($insertOrderListRelationRow);
        $orderListRelationObj->save();

    }

    //修改订单状态
    public static function changeOrderListRelationStatus($type,$relateId,$status){
        if($type == 2){//工序工单特殊处理，因为工序管理员工单和查看权限的用户对应的工单是同一个工单
            $where = [];
            $where[]=['relate_id','=',$relateId];
            $whereIn = [2,3];
            $orderListRelationList = OrderListRelation::where($where)->whereIn('type',$whereIn)->get();

        }else{
            $where = [];
            $where[]=['type','=',$type];
            $where[]=['relate_id','=',$relateId];
            $orderListRelationList = OrderListRelation::where($where)->get();
        }

        //修改工单状态
        foreach ($orderListRelationList as $orderListRelationRow){
            $orderListRelationRow->status = $status;
            $orderListRelationRow->save();
        }
    }

    //修改工单的开工时间 zhuyujun 20190618
    public static function changeOrderListRelationStartTime($type,$relateId,$start_time){
        if($type == 2){//工序工单特殊处理，因为工序管理员工单和查看权限的用户对应的工单是同一个工单
            $where = [];
            $where[]=['relate_id','=',$relateId];
            $whereIn = [2,3];
            $orderListRelationList = OrderListRelation::where($where)->whereIn('type',$whereIn)->get();

        }else{
            $where = [];
            $where[]=['type','=',$type];
            $where[]=['relate_id','=',$relateId];
            $orderListRelationList = OrderListRelation::where($where)->get();
        }

        //修改工单状态
        foreach ($orderListRelationList as $orderListRelationRow){
            //判断之前有没有设置开工时间，如果设置了不需要再设置（针对管理员设置了预计开工时间，员工开工的情况）
            if(!$orderListRelationRow->start_time){
                $orderListRelationRow->start_time = $start_time;
                $orderListRelationRow->save();
            }
        }

    }




    //订单列表-返回type对应的中文提示
    public static function getOrderListRelationTypeStr($type){
        $typeStrArr = [
            '1' => 'mangerDoneCellFunction',//主订单
            '2' => 'productdirectorWaitCellFunction',//工序订单
            '3' => 'productdirectorDoneCellFunction',//工序订单（查看）
            '4' => 'waitCellFunction',//员工订单
        ];
        return $typeStrArr[$type];
    }

    //订单列表-返回type&stauts对应的中文提示
    public static function getOrderListRelationStatusStr($type,$status){
        $statusStrArr = [
            '1' => [
               '1'=>['title'=>'待接单','function_name'=>'mangerDoneCellFunction'],
               '2'=>['title'=>'进行中','function_name'=>'mangerDoneCellFunction'],
               '3'=>['title'=>'已完成','function_name'=>'mangerDoneCellFunction'],
               '4'=>['title'=>'待派单','function_name'=>'mangerDoneCellFunction'],
               '101'=>['title'=>'待开工','function_name'=>'mangerDoneCellFunction'],
            ],
            '2' => [
                '1'=>['title'=>'待接单','function_name'=>'productdirectorWaitCellFunction'],
                '2'=>['title'=>'待派单','function_name'=>'productdirectorWaitCellFunction'],
                '3'=>['title'=>'进行中','function_name'=>'productdirectorproductIngCellFunction'],
                '4'=>['title'=>'已完成','function_name'=>'productdirectorDoneCellFunction'],
                '101'=>['title'=>'待开工','function_name'=>'productdirectorproductIngCellFunction'],
            ],
            '3' => [
                '1'=>['title'=>'待接单','function_name'=>'productdirectorDoneCellFunction'],
                '2'=>['title'=>'待派单','function_name'=>'productdirectorDoneCellFunction'],
                '3'=>['title'=>'进行中','function_name'=>'productdirectorDoneCellFunction'],
                '4'=>['title'=>'已完成','function_name'=>'productdirectorDoneCellFunction'],
                '101'=>['title'=>'待开工','function_name'=>'productdirectorDoneCellFunction'],
            ],
            '4' => [
                '1'=>['title'=>'待接单','function_name'=>'waitCellFunction'],
                '2'=>['title'=>'待领材','function_name'=>'productIngNoneGetFunction'],
                '3'=>['title'=>'进行中','function_name'=>'productIngNoneDoneFunction'],
                '4'=>['title'=>'已完成','function_name'=>'doneCellFunction'],
            ],
        ];
        return $statusStrArr[$type][$status];
    }


    //修改主订单的创建时间（派发时间），作于下单时间使用
    public static function changeMainOrderTime($relateId,$time){
        $type = 1;
        $orderListRelationObj = OrderListRelation::where(['type'=>$type,'relate_id'=>$relateId])->first();

        //存入工单列表关联表的信息
        $orderListRelationObj->order_create_time = $time;

        //增加派发时间 zhuyujun 20190618 之前的字段先不管
        $orderListRelationObj->distribute_time = $time;
        $orderListRelationObj->save();

    }

    //根据订单id取得该参与该订单的所有员工id
    public static function getCouseIdByOrderId($orderId){
        //获取工单id对应的工序工单id
        $where = ['order_id'=>$orderId];
        $orderProcessIdArr = OrderProcess::where($where)->get()->pluck('id');
        $orderProcessCourseUidArr = OrderProcessCourse::whereIn('order_process_id',$orderProcessIdArr)->get()->pluck('uid');

        return $orderProcessCourseUidArr;
    }

    //工单字段信息转换
    public static function getOrderFiledValueTrue($dataValue,$valFieldType,$show_product_model_name = 0)
    {
        switch ($valFieldType) {
            case 4:
                $resultValue = '';
                //显示每种材料
                $idArr = explode(',', $dataValue);
                $resultValueArr = [];
                foreach ($idArr as $val) {
                    $collection = Product::getProductInfo($val);
                    //考虑集合材料的问题
                    if(strstr($val,'A')){
                        $tmpAssemblageMaterialId = str_replace('A','',$val);
                        $collection = \App\Eloquent\Zk\AssemblageMaterial::withTrashed()->where(['id'=>$tmpAssemblageMaterialId])->first();
                    }else{
                        $collection = ProductEngine::getProductInfo($val);
                    }

                    if ($collection) {//过滤异常情况
                        $info = $collection->toArray();
                        $resultValueArr[] = $info['product_name'];
                    }
                }
                if (!empty($resultValueArr)) {
                    $resultValue = implode(',', $resultValueArr);
                }
                break;
            case 5:
                //填写+单位选择-去掉逗号
                $resultValue = str_replace(',', '', $dataValue);
                break;
            case 6:
                //开关-转换成是否
                $resultValue = $dataValue ? '是' : '否';
                break;
            case 9:
                //图片
                $productionCaseDiagramIds = isset($dataValue) ? $dataValue : '';
                if ($productionCaseDiagramIds) {
                    $tmpArr = explode(',', $productionCaseDiagramIds);
                    $productionCaseDiagramId = reset($tmpArr);
                    $productionCaseDiagram = ImgUpload::getImgUrlById($productionCaseDiagramId);
                    if (!$productionCaseDiagram) {//未获取到id对应的图片
                        $productionCaseDiagram = env('APP_URL') . '/assets/mobile/common/images/order_list_default.png';
                    }

                } else {
                    $productionCaseDiagram = env('APP_URL') . '/assets/mobile/common/images/order_list_default.png';
                }

                $resultValue = $productionCaseDiagram;
                break;
            case 15:
                //宽长-片料规格
                $resultValue = '';
                $resultValueArr = explode(',', $dataValue);
                if (!empty($resultValueArr) && (count($resultValueArr) == 2)) {
                    //$resultValue = sprintf("宽%s×长%s厘米", $resultValueArr[0], $resultValueArr[1]);
                    $resultValue = sprintf("%s×%s厘米", $resultValueArr[0], $resultValueArr[1]);
                }
                //如果数据都为空，显示为空
                $hasData = false;
                foreach ($resultValueArr as $tmpValue){
                    if($tmpValue){
                        $hasData = true;
                    }
                }
                if(!$hasData){
                    $resultValue = '';
                }
                break;
            case 16:
                //宽长高-成品规格
                $resultValue = '';
                $resultValueArr = explode(',', $dataValue);
                if (!empty($resultValueArr) && (count($resultValueArr) == 3)) {
                    $resultValue = sprintf("宽%s×长%s×M边%s厘米", $resultValueArr[0], $resultValueArr[1], $resultValueArr[2]);
                }
                //如果数据都为空，显示为空
                $hasData = false;
                foreach ($resultValueArr as $tmpValue){
                    if($tmpValue){
                        $hasData = true;
                    }
                }
                if(!$hasData){
                    $resultValue = '';
                }
                break;
            case 17:
                //跳版选择
                $resultValue  = '';
                if ($dataValue) {
                    $plateRow = Plate::getPlateInfo($dataValue);
                    if ($plateRow) {
//                        //现在版的名称为品名ID，需要再调整下
//                        $tmpObj = \App\Eloquent\Ygt\BuyersProduct::where(['id'=>$plateRow['plate_name']])->first();
//                        if($tmpObj){
//                            $resultValue = $tmpObj['name'];
//                        }else{
//                            $resultValue = $plateRow['plate_name'];
//                        }
                        $resultValue = $plateRow['plate_no'];
                    }
                }

                break;
            case 18:
                //客户选择
                $resultValue  = '';
                $resultValue = \App\Engine\Customer::getNameById($dataValue);
                //过滤0的情况 zhuyujun 20190703
                if (!$resultValue && $dataValue) {
                    $resultValue = $dataValue;
                }
                break;
            case 19:
                //单位展示
                $resultValue = \App\Engine\Buyers::getNameById($dataValue);
                if (!$resultValue) {
                    $resultValue = $dataValue;
                }
                /*功能块：过滤单位ID为0的情况 zhuyujun 20190711*/
                if(!$resultValue){
                    $resultValue = '';
                }
                break;
            case 20:
                //品名展示
//                $buyersProductId =  $dataValue;

                //判断是否为id,处理某些地方品名处理了2次的问题
                $resultValue = '';
                if(is_numeric($dataValue)){
                    $createOrderProductNameId = $dataValue;
                    $tmpCreateOrderProductNameRow = \App\Eloquent\Zk\CreateOrderExtend::where(['id'=>$createOrderProductNameId])->first();
                    $tmpProductNameRow = json_decode(htmlspecialchars_decode($tmpCreateOrderProductNameRow['content']),true);
                    $buyersProductId = isset($tmpProductNameRow['product_name_id']) ? $tmpProductNameRow['product_name_id'] : '';
                    $buyersProduct = \App\Eloquent\Zk\BuyersProduct::where(['id'=>$buyersProductId])->first();

                    if ($buyersProduct) {

                        //黄江南 20190826 遗弃老流程可选品名型号功能，新增订单产品数据固化流程，兼容老程序数据
//                        $resultValue = $buyersProduct['name'];
                        $ChanpinOrderInfo = \App\Eloquent\Zk\ChanpinOrderInfo::where(['id'=>$buyersProduct['chanpin_id']])->first();
                        $resultValue = $ChanpinOrderInfo['pm'] ? $ChanpinOrderInfo['pm'] : $buyersProduct['name'];



                        //如果需要显示型号（工单），如果有型号的话，显示型号的值
                        //hjn 20190916 所有显示品名的处理都带上信号
//                        if($show_product_model_name){
                            //黄江南 20190826 遗弃老流程可选品名型号功能，新增订单产品数据固化流程，兼容老程序数据
                            if($ChanpinOrderInfo['xh']) $resultValue .= "【".$ChanpinOrderInfo['xh']."】";
                            if(isset($tmpProductNameRow['model_list'])){
                                if(isset($tmpProductNameRow['model_list'][0]) && !isset($ChanpinOrderInfo['xh'])){
                                    $tmp_product_model_name = $tmpProductNameRow['model_list'][0]['model_name'];//品名型号
                                    $resultValue.="【{$tmp_product_model_name}】";
                                }
                            }
//                        }

                    } else {
                        
                        if(!$buyersProductId){
                            //处理未选择品名的情况
                            $resultValue = '';
                            //hjn 20190822 产品数据固化后返回品名和型号
                            if(isset($tmpProductNameRow['chanpin_id']) && $tmpProductNameRow['chanpin_id']){
                                $ChanpinOrderInfo = \App\Eloquent\Zk\ChanpinOrderInfo::where(['id'=>$tmpProductNameRow['chanpin_id']])->first();
                                $resultValue = $ChanpinOrderInfo['pm'];
//                                $show_product_model_name
                                if($ChanpinOrderInfo['xh']) $resultValue .= "【" . $ChanpinOrderInfo['xh'] . "】";
                            }

                        }else{
                            $resultValue = $dataValue;
                        }

                    }
                }else{
                    $resultValue = $dataValue;
                }

                break;
            default:
                $resultValue = $dataValue;
        }

        return $resultValue;

    }


    //转变单位名称字段 原因 该字段存值 有三种
    //1单位的id 2单位的名称 3空
    public function changeFieldName23($value='')
    {
        $result         = $value;
        if($value)
        {
            $result     = Buyers::getNameById($value);
            if (!$result) {
                $result = $value;
            }
        }
        return $result;
    }


    //有版时添加版相关的数据到版-工单关联表中
    public static function getOrderPlateField(){
        return [
            'machine' =>
                [
                    'title' => '机台',
                    'field' => '',
                ],
            'membrane ' =>
                [
                    'title' => '膜',
                    'field' =>'field_name_117',
                ],
            'printing_ink' =>
                [
                    'title' => '油墨',
                    'field' =>'',
                ],
            'auxiliary' =>
                [
                    'title' => '助剂',
                    'field' =>'field_name_78',
                ],
        ];
    }

    //获取对应权限是否需要填件
    public static function getPrivilegePiece($privelegeId,$ordertypeProcessId,$type = '3'){
        $where = ['process_ordertype_id'=>$ordertypeProcessId,'privilege_id'=>$privelegeId,'type'=>$type];
        $needPieceRow = \App\Eloquent\Zk\FieldLimit::where($where)->first();
        if($needPieceRow){
            if($needPieceRow->limits){
                return true;
            }
        }

        return false;
    }

    //获取对应权限能否成品入库
    public static function getPrivilegeFinishedProduct($privelegeId,$ordertypeProcessId){
        $where = ['process_ordertype_id'=>$ordertypeProcessId,'privilege_id'=>$privelegeId,'type'=>4];
        $finishedProductRow = \App\Eloquent\Zk\FieldLimit::where($where)->first();
        if($finishedProductRow){
            if($finishedProductRow->limits){
                return true;
            }
        }

        return false;
    }

    //获取对应权限是否不进行工序数量统计
    public static function getProcessNumNoStatistics($privelegeId){
        $needPiecePrivilegeIds = [150];
        if(in_array($privelegeId,$needPiecePrivilegeIds)){
            return true;
        }else{
            return false;
        }
    }

    //获取uid对应的客户ID
    public static function getCustomerIdByUid($uid){
        $where = ['user_id' => $uid];
        $customerId = '';
        $customerObj = \App\Eloquent\Zk\Customer::where($where)->first();
        if($customerObj){
            $customerId = $customerObj->id;
        }

        return $customerId;
    }

    //获取对应工序是否不显示金额相关数据
    public static function getHideMoneyDataByProcessId($processId){
        $isHideMoneydata = \App\Eloquent\Zk\Process::getOneValueById($processId, 'is_hide_moneydata');
        if(!$isHideMoneydata){
            return 1;
        }else{
            return 0;
        }
    }

    //获取员工的计件工资
    public static function getEmployeePieceWage($orderProcessId,$privilegeId,$orderProcessCourseId){
        //先判断有没有批量设置角色计件工资
        $tmpOrderWage = '';
        $tmpOrderProcessId = $orderProcessId;
        $tmpOrderWageObj = \App\Eloquent\Zk\OrderWage::where(['order_process_id'=>$tmpOrderProcessId,'privilege_id'=>$privilegeId])->first();
        if($tmpOrderWageObj){
            $tmpOrderWage = $tmpOrderWageObj->piece_wage;
        }

        //设置在个人上的计件工资优先级更高
        $tmpOrderWageObj = \App\Eloquent\Zk\OrderWage::where(['order_process_course_id'=>$orderProcessCourseId])->first();
        if($tmpOrderWageObj){
            $tmpOrderWage = $tmpOrderWageObj->piece_wage;
        }

        return $tmpOrderWage;
    }

    //获取订单对应的价格
    public static function getCustomerOrderPriceById($customerOrderId){
        $customerOrderPriceObj = \App\Eloquent\Zk\CustomerOrderPrice::where(['customer_order_id'=>$customerOrderId])->first();
        if($customerOrderPriceObj){
            if(isset($customerOrderPriceObj->deal_price)){
                return $customerOrderPriceObj->deal_price;
            }
        }
        return false;
    }

    //获取工序中选择的材料
    public static function getProcessMaterialIdList($orderProcessId){
        $orderProcessRow = \App\Eloquent\Zk\OrderProcess::where(['id'=>$orderProcessId])->first()->toArray();

        $processFieldList = \App\Eloquent\Zk\Process::getFieldListByProcessId($orderProcessRow['process_type']);
        $returnMaterialList = [];
        foreach ($processFieldList as $processFieldRow) {
            $tmpFiledType = \App\Eloquent\Zk\ProcessField::getFieldTypeByFieldName($processFieldRow['field_name'], $orderProcessRow['company_id']);
            if ($tmpFiledType == 4) {//材料选择暂时不显示在
                $materialList = explode(',', $orderProcessRow[$processFieldRow['field_name']]);
                foreach ($materialList as $materialId){
                    if($materialId){
                        $returnMaterialList[] = $materialId;
                    }
                }
            }
        }

        return $returnMaterialList;
    }


    //修改订单状态
    public static function changeCustomerOrderStatus($customerOrderId,$changeStatus){
        $tmpObj = \App\Eloquent\Zk\CustomerOrder::where(['id'=>$customerOrderId])->first();
        if($tmpObj){
            $tmpObj->fill(['status'=>$changeStatus]);
            $tmpObj->save();
            return true;
        }
        return false;
    }

    /**
     * Description:获取节点对应拥有该权限的角色
     * User: zhuyujun
     * @param $companyId
     * @param $appnodeId
     * $appnodeId ：
     * 1-跳转到我的工单页面 -- 下单人权限
     * 2-？ -- 已弃用
     * 3-库存管理 -- 仓库权限
     * 4-主管向员工派单 -- 工序管理员权限
     * 7-添加新材料消息 -- 仓库权限（会受到添加新材料的消息）
     * 8-工单材料采购 --
     * 9-厂长权限 -- 厂长权限（可以查看所有人）
     * 10-采购员权限 -- 采购员权限(表示对应角色是采购员)
     * 11-财务权限
     * 12-发货员权限
     * 13-销售员权限
     * 14-物流员权限
     * 15-发货员权限
     */

    public static function getPrivilegeByNode($companyId,$appnodeId){
        return \App\Eloquent\Zk\Privilege::getPrivilegeIds( $companyId, $appnodeId );
    }

    //获取角色拥有的权限
    public static function getNodesByPrivilege($privilegeId){
        $appnodeIds = [];
        $privilegeAppnodeList = \App\Eloquent\Zk\PrivilegeAppnode::where(['privilege_id'=>$privilegeId])->get();
        if($privilegeAppnodeList){
            $appnodeIds = $privilegeAppnodeList->pluck('appnode_id')->toArray();
        }

        return $appnodeIds;
    }


    /*
     * 获取指定时间内员工姓名,工单数,累计数量,工资金额
     *
     * 参数
     *   年,月,厂id
     * 返回结果
     *   厂下员工在该月内的
     *   员工姓名,工单数,累计数量,工资金额
     * */
    public static function getCompanyStaffWorkInfo($companyId,$startTime,$endTime){
        $startTimeStamp = strtotime($startTime);
        $endTimeStamp = strtotime($endTime);
        //获取指定企业指定时间区间内所有员工工单
        $where = [];
        $where[] = ['company_id','=',$companyId];
        $where[] = ['submit_time','>=',$startTimeStamp];
        $where[] = ['submit_time','<=',$endTimeStamp];
        $orderProcessCourseGradationList = \App\Eloquent\Zk\OrderProcessCourseGradation::where($where)->get();

        $workInfoList = [];
        foreach ($orderProcessCourseGradationList as $orderProcessCourseGradationRow){
            $tmpOrderProcessCourseRow =  \App\Eloquent\Zk\OrderProcessCourse::where(['id'=>$orderProcessCourseGradationRow['order_process_course_id']])->first();
            $tmpOrderProcessRow = \App\Eloquent\Zk\OrderProcess::where(['id'=>$tmpOrderProcessCourseRow['order_process_id']])->first();

            //获取员工姓名,角色
            $tmpUserInfo = \App\Eloquent\Zk\DepartmentUser::getCurrentInfo($tmpOrderProcessCourseRow['uid'])->toArray();

            //获取员工计件工资
            $tmpUserWage = self::getEmployeePieceWage($tmpOrderProcessCourseRow['order_process_id'],$tmpUserInfo['privilege_id'],$tmpOrderProcessCourseRow['id']);

            //获取工单数，累计数量，工资金额
            if(!isset($workInfoList[$tmpOrderProcessCourseRow['uid']])){
                $workInfoList[$tmpOrderProcessCourseRow['uid']]['uid'] = $tmpUserInfo['user_id'];
                $workInfoList[$tmpOrderProcessCourseRow['uid']]['true_name'] = $tmpUserInfo['truename'];
                $workInfoList[$tmpOrderProcessCourseRow['uid']]['order_id_list'][] = $tmpOrderProcessRow['order_id'];
                $workInfoList[$tmpOrderProcessCourseRow['uid']]['order_num'] = 1;
                $workInfoList[$tmpOrderProcessCourseRow['uid']]['submit_num'] = $orderProcessCourseGradationRow['submit_num'];
                $workInfoList[$tmpOrderProcessCourseRow['uid']]['wages'] = $tmpUserWage*$orderProcessCourseGradationRow['submit_num'];
            }else{
                $workInfoList[$tmpOrderProcessCourseRow['uid']]['submit_num'] += $orderProcessCourseGradationRow['submit_num'];
                $workInfoList[$tmpOrderProcessCourseRow['uid']]['wages'] += $tmpUserWage*$orderProcessCourseGradationRow['submit_num'];
                if(!in_array($tmpOrderProcessRow['order_id'],$workInfoList[$tmpOrderProcessCourseRow['uid']]['order_id_list'])){
                    $workInfoList[$tmpOrderProcessCourseRow['uid']]['order_id_list'][] = $tmpOrderProcessRow['order_id'];
                    $workInfoList[$tmpOrderProcessCourseRow['uid']]['order_num'] ++;
                }
            }
        }

        return $workInfoList;
    }

    /*
     * 获取指定时间内员工的工单
     *
     * 参数
     *   厂id,用户id(员工id),开始时间,结束时间
     * 返回结果
     *   该员工的这段时间内的工单
     *
     * */
    public static function getStaffOrderList($companyId,$userId,$startTime,$endTime){
        $startTimeStamp = strtotime($startTime);
        $endTimeStamp = strtotime($endTime);
        //获取指定企业指定时间区间内所有员工工单
        $where = [];
        $where[] = ['company_id','=',$companyId];
        $where[] = ['uid','=',$userId];
        $where[] = ['submit_time','>=',$startTimeStamp];
        $where[] = ['submit_time','<=',$endTimeStamp];
        $orderProcessCourseIdList = \App\Eloquent\Zk\OrderProcessCourseGradation::where($where)->get()->pluck('order_process_course_id');
        $orderProcessIdList = \App\Eloquent\Zk\OrderProcessCourse::whereIn('id',$orderProcessCourseIdList)->get()->pluck('order_process_id');
        $orderIdList = \App\Eloquent\Zk\OrderProcess::whereIn('id',$orderProcessIdList)->get()->pluck('order_id');
        $orderList = \App\Eloquent\Zk\Order::whereIn('id',$orderIdList)->get()->toArray();

        $tmpOrderList = [];
        foreach ($orderList as $tmpOrderRow) {
            if (isset($tmpOrderRow['product_num'])) {
                $tmpArr = explode(',', $tmpOrderRow['product_num']);
                $tmpOrderRow['product_num'] = '';
                foreach ($tmpArr as $tmpValue) {
                    $tmpOrderRow['product_num'] .= $tmpValue;
                }
            }

            //成品规格数据处理
            if (isset($tmpOrderRow['finished_specification'])) {
                $tmpArr = explode(',', $tmpOrderRow['finished_specification']);
                if (count($tmpArr) == 2) {
                    $tmpOrderRow['finished_specification'] = sprintf("%s×%s厘米", $tmpArr[0], $tmpArr[1]);
                }

                if (count($tmpArr) == 3) {
                    $tmpOrderRow['finished_specification'] = sprintf("%s×%s×%s厘米", $tmpArr[0], $tmpArr[1], $tmpArr[2]);
                }

                //如果数据都为空，显示为空
                $hasData = false;
                foreach ($tmpArr as $tmpValue) {
                    if ($tmpValue) {
                        $hasData = true;
                    }
                }

                if (!$hasData) {
                    $tmpOrderRow['finished_specification'] = '';
                }
            }

            //单位名称显示处理
            if (isset($tmpOrderRow['field_name_23'])) {
                $baseId = $tmpOrderRow['field_name_23'];
                $showTitle = \App\Engine\Buyers::getNameById($tmpOrderRow['field_name_23']);
                if (!$showTitle) {
                    $showTitle = $tmpOrderRow['field_name_23'];
                }
                $tmpOrderRow['field_name_23'] = $showTitle;
            }

            //获取生产实例图
            $productionCaseDiagramIds = isset($tmpOrderRow['production_case_diagram']) ? $tmpOrderRow['production_case_diagram'] : '';
            if ($productionCaseDiagramIds) {
                $tmpArr = explode(',', $productionCaseDiagramIds);
                $productionCaseDiagramId = reset($tmpArr);
                $productionCaseDiagram = \App\Eloquent\Zk\ImgUpload::getImgUrlById($productionCaseDiagramId);
                if (!$productionCaseDiagram) {//未获取到id对应的图片
                    $productionCaseDiagram = env('APP_URL') . '/assets/mobile/common/images/order_list_default.png';
                }

            } else {
                $productionCaseDiagram = env('APP_URL') . '/assets/mobile/common/images/order_list_default.png';
            }

            //订单创建时间（派发时间）
            $orderCreateTime = $tmpOrderRow['created_at'];
            if ($orderCreateTime) {
                $orderCreateTime = date('Y-m-d', $orderCreateTime);
                if ($orderCreateTime < 631152000) {//过滤1970的情况
                    $orderCreateTime = '';
                }
            } else {
                $orderCreateTime = '';
            }

            //获取工序类型名称
            $orderTypeTitle = \App\Engine\OrderType::getOneValueById($tmpOrderRow['order_type'], 'title');

            //获取工单状态
            $status = $tmpOrderRow['status'];
            $orderStatusStr = '';
            $orderStatusColor = 'FFB401';
            if ($status == 1) {//待接单
                $orderStatusStr = '待接单';
                $orderStatusColor = 'FFB401';

            } elseif ($status == 101) {//待开工
                $orderStatusStr = '待开工';
                $orderStatusColor = 'FFB401';
            } elseif ($status == 2) {//生产中
                $orderStatusStr = '生产中';
                $orderStatusColor = 'FE7E57';
            } elseif ($status == 3) {//已完工
                $orderStatusStr = '已完成';
                $orderStatusColor = '04C9B3';
            }


            $productName = \App\Engine\OrderEngine::getOrderFiledValueTrue($tmpOrderRow['product_name'],20);
            $tmpOrderList[] = [
                'ordertype_title' => '工艺:',//工单列表展示字段
                'order_title' => '单号:',//工单列表展示字段
                'unit_name' => isset($tmpOrderRow['field_name_23']) ? $tmpOrderRow['field_name_23'] : '',//新加字段单位
                'product_name' => isset($productName) ? $productName : '',//新加字段品名
                'order_title_value' => $tmpOrderRow['order_title'],//新加字段单号
                'firstPropertyName' => '成品规格',
                'secondPropertyName' => '数量',
                'thirdPropertyName' => '交货日期',
                'thirdPropertyValue' => $tmpOrderRow['finished_date'],
                'secondPropertyValue' => $tmpOrderRow['product_num'],
                'firstPropertyValue' => isset($tmpOrderRow['finished_specification']) ? $tmpOrderRow['finished_specification'] : '',
                'productionCaseDiagram' => $productionCaseDiagram,
                'productOrderTimeValue' => $orderCreateTime,
                'orderTypeTitle' => $orderTypeTitle,
                'relate_id' => $tmpOrderRow['id'],
                'orderStatus' => $orderStatusStr,
                'statusColor' => $orderStatusColor,
            ];
        }

        $orderList = $tmpOrderList;
        return $orderList;
    }


    //处理订单中特殊的字段
    public static function dealOrderFiled($orderRow,$show_product_model_name=0){
        //处理产品数量
        if (isset($orderRow['product_num'])) {
            $tmpArr = explode(',', $orderRow['product_num']);
            $orderRow['product_num'] = '';
            foreach ($tmpArr as $tmpValue) {
                if($tmpValue && ($tmpValue != 'null') ){
                    $orderRow['product_num'] .= $tmpValue;
                }
            }
        }

        //成品规格数据处理
        if (isset($orderRow['finished_specification'])) {
            $tmpArr = explode(',', $orderRow['finished_specification']);
            if (count($tmpArr) == 2) {
                $orderRow['finished_specification'] = sprintf("%s×%s厘米", $tmpArr[0], $tmpArr[1]);
            }

            if (count($tmpArr) == 3) {
                $orderRow['finished_specification'] = sprintf("%s×%s×%s厘米", $tmpArr[0], $tmpArr[1], $tmpArr[2]);
            }

            //如果数据都为空，显示为空
            $hasData = false;
            foreach ($tmpArr as $tmpValue) {
                if ($tmpValue) {
                    $hasData = true;
                }
            }

            if (!$hasData) {
                $orderRow['finished_specification'] = '';
            }
        }

        //单位名称显示处理
        if (isset($orderRow['field_name_23'])) {
            $baseId = $orderRow['field_name_23'];
            $showTitle = \App\Engine\Buyers::getNameById($orderRow['field_name_23']);
            if (!$showTitle) {
                $showTitle = $orderRow['field_name_23'];
            }
            $orderRow['field_name_23'] = $showTitle;
        }

        //品名处理 20180626 zhuyujun
//        $createOrderProductNameId = $orderRow['product_name'];
//        $tmpCreateOrderProductNameRow = \App\Eloquent\Ygt\CreateOrderExtend::where(['id'=>$createOrderProductNameId])->first();
//        $tmpProductNameRow = json_decode(htmlspecialchars_decode($tmpCreateOrderProductNameRow['content']),true);
//        $buyersProductId = isset($tmpProductNameRow['product_name_id']) ? $tmpProductNameRow['product_name_id'] : '';
//
//        $buyersProduct = \App\Eloquent\Ygt\BuyersProduct::where(['id'=>$buyersProductId])->first();

        $productName = \App\Engine\OrderEngine::getOrderFiledValueTrue($orderRow['product_name'],20,$show_product_model_name);

        if ($productName) {
            $orderRow['product_name'] = $productName;
        }


        //工艺名称
        $orderTypeTitle = '';
        if(isset($orderRow['order_type']))
        {
            $orderTypeTitle     = \App\Eloquent\Zk\OrderType::getOneValueById($orderRow['order_type'],'title');
        }
        $orderRow['order_type_title'] = $orderTypeTitle;

        return $orderRow;
    }

    //获取工单对应的订单议定价格 20180728 zhuyujun
    public static function getOrderPrice($orderId){
        //获取订单ID
        $customerOrderId = \App\Eloquent\Zk\Order::where(['id'=>$orderId])->first()->customer_order_id;

        //获取订单的议定价格
        if($customerOrderId){
            $customerOrderPriceRow = \App\Eloquent\Zk\CustomerOrderPrice::where(['customer_order_id'=>$customerOrderId])->first();
            if($customerOrderPriceRow){
                $price =$customerOrderPriceRow->deal_price;

                if(!$price){
                    //如果没有议定价格，获取销售员报价
                    $price =$customerOrderPriceRow->sale_price;
                }
                return $price;
            }

        }
        return false;
    }


    //获取工艺需完成的数量（单位不一致的时候，通过片料规格进行计算）
    public static function getOrderPorcessNeedNum($processId,$orderRow){
        $processRow = \App\Eloquent\Zk\Process::where(['id'=>$processId])->first();

        $productNum = 0;
        $tmpArr = explode(',', $orderRow['product_num']);
        if(isset($tmpArr[0]) && $tmpArr[0] ){
            $productNum = $tmpArr[0];
        }

        if($processRow['is_same_unit']){
            return $productNum;
        }else{
            //通过片料规格的长，计算比例
            if($orderRow['chip_specification_length']){
                $tmpArr = explode(',', $orderRow['chip_specification_length']);
                if(isset($tmpArr[1]) && $tmpArr[1] ){
                    return $tmpArr[1]*$productNum/100;
                }
            }

            return $productNum;
        }
    }

    //获取某个工艺的完成数量
    public static function getOrderPorcessCompleteNum($orderProcessRow){
        $processRow = \App\Eloquent\Zk\Process::getInfo(['id'=>$orderProcessRow['process_type']]);
        $processIsSum = $processRow['is_sum'];//工序数量是否累加 0-不累加 1-累加
        $orderProcessCourseList = \App\Eloquent\Zk\OrderProcessCourse::where(['order_process_id' => $orderProcessRow['id']])->get();
        $preFinishNum = 0;

        $finishUserNumList = [];
        foreach ($orderProcessCourseList as $orderProcessCourseRow) {

            //设定的角色不进行统计数量到工序数量中
            //过滤异常数据
            if (!\App\Eloquent\Zk\DepartmentUser::getCurrentInfo($orderProcessCourseRow['uid'])) {
                continue;
            }
            $tmpUserInfo = \App\Eloquent\Zk\DepartmentUser::getCurrentInfo($orderProcessCourseRow['uid'])->toArray();
            $tmpPrivilegeId = $tmpUserInfo['privilege_id'];
            if (\App\Engine\OrderEngine::getProcessNumNoStatistics($tmpPrivilegeId)) {
                continue;
            }

            $preFinishNum += $orderProcessCourseRow['finish_number'];
            if (!$processIsSum) {//不累加
                $tmpUserInfo = DepartmentUser::getCurrentInfo($orderProcessCourseRow['uid'])->toArray();
                $tmpPrivilegeId = $tmpUserInfo['privilege_id'];
                if (isset($finishUserNumList[$tmpPrivilegeId])) {
                    $finishUserNumList[$tmpPrivilegeId]['finish_number'] += $orderProcessCourseRow['finish_number'];
                } else {
                    $finishUserNumList[$tmpPrivilegeId]['finish_number'] = $orderProcessCourseRow['finish_number'];
                }
            }
        }

        //不累加--未完工时取完成量最小的员工
        if (!empty($finishUserNumList)) {
            $finishMinNum = '';
            foreach ($finishUserNumList as $finishUserNumRow) {
                if ($finishMinNum === 0) {//如果有员工完成数量为0，
                    break;
                }

                if ($finishUserNumRow['finish_number'] < $finishMinNum || $finishMinNum === '') {//如果有员工完成数量更少，或者第一个员工的数据
                    $finishMinNum = $finishUserNumRow['finish_number'];
                }
            }
            $preFinishNum = $finishMinNum;
        }

        /*功能块:工序有设置半成品，且下单的时候有选半成品，重新统计工序工单完成数量，zhuyujun 20190524*/
        //获取工艺工序对应的半成品
        $process_product_list =  \App\Eloquent\Zk\ProcessProduct::where(['ordertype_process_id'=>$orderProcessRow['ordertype_process_id']])->get();
        if(!empty($process_product_list)){
            //计算工序当前所有的半成品情况
            $cur_process_product_list = [];

            //获取工序中半成品的字段
            $filed_id_list = \App\Eloquent\Zk\ProcessFieldRelation::where(['process_id'=>$orderProcessRow['process_type']])->get()->pluck('field_id');
            $field_name_list = \App\Eloquent\Zk\ProcessFieldCompany::where(['field_type'=>22])->whereIn('id',$filed_id_list)->get()->pluck('field_name');

            foreach ($field_name_list as $field_name){
                if(isset($orderProcessRow[$field_name]) && $orderProcessRow[$field_name]){
                    $tmpCreateOrderProcessProductRow = \App\Eloquent\Zk\CreateOrderProcessProduct::where(['id'=>$orderProcessRow[$field_name]])->first();
                    $processProductList = json_decode(htmlspecialchars_decode($tmpCreateOrderProcessProductRow['content']), true);
                    foreach ($processProductList as $processProductRow){
                        //统计下单选择的半成品数量
                        $cur_process_product_list[$processProductRow['process_product_id']]['number'] += $processProductRow['number'];
                    }
                }
            }

            //下单有选半成品才需要重新计算，不然之前的功能已能实现功能
            if(!empty($cur_process_product_list)){
                //获取员工已提交的半成品记录
                //获取关联的提交记录
                $where = [];
                $where[] = ['order_process_id','=',$orderProcessRow['id']];
                $process_product_submit_log_list = \App\Eloquent\Zk\ProcessProductSubmitLog::where($where)->get();
                foreach ($process_product_submit_log_list as $process_product_submit_log_row){
                    //统计员工提交的半成品数量
                    $cur_process_product_list[$process_product_submit_log_row['process_product_id']]['number'] += $process_product_submit_log_row['number'];
                }


                //补上半成品中0的数据
                foreach ($process_product_list as $process_product_row){
                    if(!isset($cur_process_product_list[$process_product_row['id']])){
                        $cur_process_product_list[$process_product_row['id']]['number'] = 0;
                    }
                }



                //半成品数量最小的作为工序完成数量
                $tmp_min_number = false;//记录最小值
                foreach ($cur_process_product_list as $cur_process_product_row){
                    if(!$cur_process_product_row['number']){
                        //如果
                        $tmp_min_number = 0;
                        break;
                    }else{
                        if($tmp_min_number === false){
                            $tmp_min_number = $cur_process_product_row['number'];
                        }else{
                            if($tmp_min_number > $cur_process_product_row['number'] ){
                                $tmp_min_number = $cur_process_product_row['number'];
                            }
                        }
                    }
                }

                if($tmp_min_number){
                    $preFinishNum = $tmp_min_number;
                }else{
                    $preFinishNum = 0;
                }

            }
        }

        return $preFinishNum;
    }

    //获取工单相关进度条对应的类型
    public static function getOrderRadioType($radio){
        if($radio == 0){
            return 1;
        }elseif($radio > 0 && $radio <1){
            return 2;
        }elseif($radio >= 1){
            return 3;
        }
    }


    //工单详情获取版的详情
    public static function getOrderPlateDealInfo($tmpCreateOrderPlateRowContent,$processFieldTitle){
        $re = [];

        /*功能块：增加版的id，用于跳转版详情 zhuyujun 20190712*/
        $plate_id = 0;
        if(isset($tmpCreateOrderPlateRowContent['plate_id']) && $tmpCreateOrderPlateRowContent['plate_id'] ){
            $plate_id = $tmpCreateOrderPlateRowContent['plate_id'];
        }


        /**固定版**/
        $fixedPlateShow = "";
        if(!empty($tmpCreateOrderPlateRowContent['branch']['fixed_plate'])){
            //按排序值排序
            array_multisort(array_column($tmpCreateOrderPlateRowContent['branch']['fixed_plate'],'sort'),SORT_ASC,$tmpCreateOrderPlateRowContent['branch']['fixed_plate']);

            //20190717 调整位置 到判断空的block里
            foreach ($tmpCreateOrderPlateRowContent['branch']['fixed_plate'] as $tmpFixedPlate){
                $fixedPlateShow .= $tmpFixedPlate['name'].',';
            }
        }



        if(!$fixedPlateShow){
            $fixedPlateShow = '无';
        }else{
            $fixedPlateShow = substr($fixedPlateShow,0,-1);
            $fixedPlateShow = "【固定版】".$fixedPlateShow;
        }

        $re[] = [
            '$leftLabelValue' => $processFieldTitle,
            '$rightLabelValue' => $fixedPlateShow,
            /*功能块：增加版的id，用于跳转版详情 zhuyujun 20190712*/
            '$plateId' => $plate_id,
        ];


        /**套版**/
        if(isset($tmpCreateOrderPlateRowContent['branch']['colourplate'])){
            $isFirst = true;
            foreach ($tmpCreateOrderPlateRowContent['branch']['colourplate'] as $tmpColourplate){
                $tmpColourplateShow = "";
                if(isset($tmpColourplate['group'])){
                    if(!empty($tmpColourplate['group'])){
                        foreach ($tmpColourplate['group'] as $tmpGroupRow){
                            $tmpColourplateShow .= $tmpGroupRow['name'].' '.$tmpGroupRow['model'].',';
                        }
                    }else{
                        $tmpColourplateShow = '';
                    }

                }

                if($tmpColourplateShow){
                    $tmpColourplateShow = substr($tmpColourplateShow,0,-1);
                }

                if(!isset($tmpColourplate['num'])){
                    $tmpColourplate['num'] = '';
                }


                if(!$tmpColourplateShow){
                    $tmpColourplateShow = "({$tmpColourplate['num']})".'【无套版组合】';
                }else{
//                                $tmpColourplateShow = "【套版组合】".$tmpColourplateShow."：".$tmpColourplate['num'];
                    $tmpColourplateShow = "({$tmpColourplate['num']})"."【套版组合】".$tmpColourplateShow;

                    //
                    $re[] = [
                        '$leftLabelValue' => '',
                        '$rightLabelValue' => $tmpColourplateShow,
                        /*功能块：增加版的id，用于跳转版详情 zhuyujun 20190712*/
                        '$plateId' => $plate_id,
                    ];
                }




            }
        }

        /**其他版**/
        if(isset($tmpCreateOrderPlateRowContent['branch']['other_plate'])){
            foreach ($tmpCreateOrderPlateRowContent['branch']['other_plate'] as $tmpOtherPlate){
                $tmpOtherPlateShow = $tmpOtherPlate['name'];
                if(!$tmpOtherPlateShow){
                    $tmpOtherPlateShow = '无';
                    continue;
                }else{
//                                $tmpOtherPlateShow = "【套版】".$tmpColourplateShow."：".$tmpColourplateShow['num'];
                    $tmpOtherPlateShow = "({$tmpOtherPlate['num']})"."【套版】".$tmpOtherPlate['name'];
                }


                $re[] = [
//                    '$leftLabelValue' => $processFieldTitle,
                    '$leftLabelValue' => '',
                    '$rightLabelValue' => $tmpOtherPlateShow,
                    /*功能块：增加版的id，用于跳转版详情 zhuyujun 20190712*/
                    '$plateId' => $plate_id,
                ];
            }
        }

        return $re;
    }

    //获取版信息中的套版ID和型号ID
    public static function getOrderPlateInfoDealId($tmpCreateOrderPlateRowContent){
        $plateId = $tmpCreateOrderPlateRowContent['plate_id'];
        $plateName = $tmpCreateOrderPlateRowContent['plate_name'];

        $plateBranchId = "";
        $plateBranchColourplateId = "";
        $plateModelName = "";

        $plateBranchIdArr = [];
        $plateBranchColourplateIdArr = [];
        //固定版
        foreach ($tmpCreateOrderPlateRowContent['branch']['fixed_plate'] as $tmpFixedPlate) {
            //获取固定版对应的ID
            $tmpPlateBranchRow = \App\Eloquent\Zk\PlateBranch::where(['plate_id'=>$plateId, 'type' => 1, 'name' => $tmpFixedPlate['name']])->first();
            if ($tmpPlateBranchRow) {
                $plateBranchIdArr[] = $tmpPlateBranchRow['id'];
            }
        }

        //套版
        if(isset($tmpCreateOrderPlateRowContent['branch']['colourplate'])){
            $isFirst = true;
            foreach ($tmpCreateOrderPlateRowContent['branch']['colourplate'] as $tmpColourplate){
                $tmpColourplateShow = "";
                if(isset($tmpColourplate['group'])){
                    if(!empty($tmpColourplate['group'])){
                        foreach ($tmpColourplate['group'] as $tmpGroupRow){
//                            $tmpColourplateShow .= $tmpGroupRow['name'].' '.$tmpGroupRow['model'].',';
                            $plateModelName .= $tmpGroupRow['name'].' '.$tmpGroupRow['model'].' ';
                            $tmpPlateBranchRow = \App\Eloquent\Zk\PlateBranch::where(['plate_id'=>$plateId, 'type' => 2, 'name' => $tmpGroupRow['name']])->first();
                            if ($tmpPlateBranchRow) {
                                $plateBranchIdArr[] = $tmpPlateBranchRow['id'];
                                $tmpPlateBranchColourplateRow = \App\Eloquent\Zk\PlateBranchColourplate::where(['plate_branch_id'=>$tmpPlateBranchRow['id'], 'name' => $tmpGroupRow['model']])->first();
                                if($tmpPlateBranchColourplateRow){
                                    $plateBranchColourplateIdArr [] = $tmpPlateBranchColourplateRow['id'];
                                }
                            }
                        }
                    }
                }
            }
        }

        /**其他版**/
        if(isset($tmpCreateOrderPlateRowContent['branch']['other_plate'])){
            foreach ($tmpCreateOrderPlateRowContent['branch']['other_plate'] as $tmpOtherPlate){
                $plateModelName .= $tmpOtherPlate['name'] ." ";

                $tmpPlateBranchRow = \App\Eloquent\Zk\PlateBranch::where(['plate_id'=>$plateId, 'type' => 3, 'name' => $tmpOtherPlate['name']])->first();
                if ($tmpPlateBranchRow) {
                    $plateBranchIdArr[] = $tmpPlateBranchRow['id'];
                }
            }
        }

        if(!empty($plateBranchIdArr)){
            $plateBranchId = implode(',',$plateBranchIdArr);
        }
        if(!empty($plateBranchColourplateIdArr)){
            $plateBranchColourplateId = implode(',',$plateBranchColourplateIdArr);
        }

        return [
            'plateBranchId' => $plateBranchId,
            'plateBranchColourplateId' => $plateBranchColourplateId,
            'plateName' => $plateName,
            'plateModelName' => $plateModelName,
        ];
    }


    //按指定需求，处理数组中的指定字段
    //$input 包含要处理数据的数组
    //$field_name 要处理的字段名称
    //$type  处理的类型
    //$extra_info_field_name 处理后添加的额外信息 在input的字段名
    static public function handleInput($input,$field_name,$type,$extra_info_field_name){
        switch($type){
            case 4://材料类型
                $input = self::handleMaterialInput($input,$field_name,$extra_info_field_name);
                break;
            default:
                break;
        }


        return $input;
    }

    static public function handleMaterialInput($input,$field_name,$extra_info_field_name){
        if(isset($input[$field_name])){
            //新结构 包含数据
            if(strstr($input[$field_name],'is_purchase')){
                $tempArr = json_decode(htmlspecialchars_decode($input[$field_name]),true);
                if($tempArr){
                    $tempIdArr = [];
                    foreach($tempArr as $val){
                        $tempIdArr[] = $val['id'];
                    }
                    $input[$field_name] = implode(',',$tempIdArr);
                    if(isset($input[$extra_info_field_name])){
                        $input[$extra_info_field_name]= array_merge($input[$extra_info_field_name],$tempArr);
                    }
                    else{
                        $input[$extra_info_field_name] = $tempArr;
                    }
                }
            }
            //老的 或者新的格式空数组
//            else{
//                $tempArr = json_decode(htmlspecialchars_decode($input[$field_name]),true);
//                //转成功  且为空数组
//                if(!$tempArr){
//                    $input[$field_name] = '';
//                }
//            }
        }
        return $input;
    }

    /**
     * Description:订单（/工单）工艺（/基础）字段默认值统一处理
     * User: zhuyujun
     * 目前使用阶段：订单创建、工单创建、订单议价 20190222
     * @param $fieldList 字段信息列表
     * @param $dataList 工单信息数组
     * @param $userInfo 登陆用户信息
     * @param $materialTyepArr 处理材料优先采购的标记
     */
    static public function defaultFieldDeal($fieldList,$dataList,$userInfo,$materialTyepArr){

        foreach ($fieldList as $key => $row){
            if (isset($dataList[$row['field_name']]) && $dataList[$row['field_name']]) {
                if ($row['field_type'] == 3) {//选择 处理
                    $selectId = self::getSidByTitle($dataList[$row['field_name']], $row['data']);
                    $fieldList[$key]['default_select_id'] = $selectId;
                    $fieldList[$key]['default_value'] = $dataList[$row['field_name']];
                } elseif ($row['field_type'] == 4) {//选择材料页面
                    $materialIdListStr = $dataList[$row['field_name']];
                    $materialIdList = explode(',', $materialIdListStr);

                    switch ($materialTyepArr['type']){
                        case 1: //订单草稿箱
                            $recommendMaterialIdArr = \App\Eloquent\Zk\OrderMaterialPurchaseMark::where('customer_order_pre_id','=',$materialTyepArr['relate_id'])
                                ->where('is_purchase','=',1)->pluck('material_id')->toArray();
                            break;
                        case 2://订单
                            $recommendMaterialIdArr = \App\Eloquent\Zk\OrderMaterialPurchaseMark::where('customer_order_id','=',$materialTyepArr['relate_id'])
                                ->where('is_purchase','=',1)->pluck('material_id')->toArray();
                            break;
                        case 3://工单草稿箱
                            $recommendMaterialIdArr = \App\Eloquent\Zk\OrderMaterialPurchaseMark::where('order_pre_id','=',$materialTyepArr['relate_id'])
                                ->where('is_purchase','=',1)->pluck('material_id')->toArray();
                            break;
                        case 4://工单
                            $recommendMaterialIdArr = \App\Eloquent\Zk\OrderMaterialPurchaseMark::where('order_id','=',$materialTyepArr['relate_id'])
                                ->where('is_purchase','=',1)->pluck('material_id')->toArray();
                            break;
                        default:
                            $recommendMaterialIdArr = [];
                            break;
                    }


                    //获取优选采购的材料id
                    $materialList = [];
                    foreach ($materialIdList as $materialId) {
                        //考虑集合材料的问题
                        if(strstr($materialId,'A')){
                            $tmpAssemblageMaterialId = str_replace('A','',$materialId);
                            $materialRow = \App\Eloquent\Zk\AssemblageMaterial::withTrashed()->where(['id'=>$tmpAssemblageMaterialId])->first();
                            $isAssemblageMaterial = 1;
                        }else{
                            $materialRow = \App\Engine\Product::getProductInfo($materialId);
                            $isAssemblageMaterial = 0;
                        }

                        if ($materialRow) {//过滤异常情况 zq
                            $materialRow = \App\Engine\Product::dealMaterialRow($materialRow,$isAssemblageMaterial);

                            if(in_array($materialId,$recommendMaterialIdArr)){
                                $materialRow['is_purchase'] = 1;
                            }
                            else{
                                $materialRow['is_purchase'] = 0;
                            }

                            $materialList[] = [
                                'id' => $materialId,
                                'product_no' => $materialRow['product_no'],
                                'product_name' => $materialRow['product_name'],
                                'image_path' => $materialRow['img_url'],
                                'is_purchase'=> $materialRow['is_purchase'],
                                'supplier'=> $materialRow['seller_company_name'],
                                'customer'=> $materialRow['customer_name'],
                                'seller_company_name'=> $materialRow['seller_company_name'],
                                'customer_name'=> $materialRow['customer_name'],
                                'custom_fields_text'=> $materialRow['custom_fields_text'],
                            ];
                        }

                    }
                    $fieldList[$key]['default_product_list'] = $materialList;
                } elseif ($row['field_type'] == 5) {//填写+单位选择 处理
                    $oldRow = $dataList[$row['field_name']];
                    $oldRowArr = explode(',', $oldRow);
                    if (count($oldRowArr) > 1) {
                        $defaultValue = $oldRowArr[0];
                        $oldUnitTitle = $oldRowArr[1];
                        $oldUnitId = self::getUnitIdByTitle($oldUnitTitle, $row['field_unit']);
                        if (!$oldUnitId) {
                            if (count($row['field_unit'])) {
                                $oldUnitId = $row['field_unit'][0]['id'];
                                $oldUnitTitle = $row['field_unit'][0]['title'];
                            } else {
                                $oldUnitId = 0;
                                $oldUnitTitle = '';
                            }
                        }

                        $fieldList[$key]['default_value'] = $defaultValue;
                        $fieldList[$key]['default_unit_id'] = $oldUnitId;
                        $fieldList[$key]['default_unit_title'] = $oldUnitTitle;
                    }
                } elseif ($row['field_type'] == 8) {//配送地址 处理
                    $customerAddressId = $dataList[$row['field_name']];

                    $addressWhere = ['id' => $customerAddressId];
                    $customerAddress = \App\Eloquent\Zk\BuyersAddress::withTrashed()->where($addressWhere)->first();
                    $customerAddressRow = [];
                    if ($customerAddress) {
                        $customerAddressRow = $customerAddress->toArray();
                        $showTitle = $customerAddressRow['province_name'] . $customerAddressRow['city_name'] . $customerAddressRow['area_name'];
                    }


                    $fieldList[$key]['default_select_id'] = $customerAddressId;
                    $fieldList[$key]['default_value'] = $showTitle;

                } elseif ($row['field_type'] == 9) {//图片处理
                    $oldImgRow = $dataList[$row['field_name']];
                    $imgIdList = explode(',', $oldImgRow);

                    $imgUrlListStr = '';
                    foreach ($imgIdList as $imgId) {
                        if (!trim($imgId)) {//过滤空格 图片id应该不会有0
                            continue;
                        }
//                        $imgUrl = ImgUpload::getImgUrlById($imgId);
                        $imgUrl = ImgUpload::getImgUrlById($imgId,true,false);//by lwl 2019 05 27 图片变成正常图片
                        $imgUrlListStr .= $imgUrl . ',';
                    }

                    $imgUrlListStr = trim($imgUrlListStr, ',');
                    $fieldList[$key]['default_img_id'] = $oldImgRow;
                    $fieldList[$key]['default_img_url'] = $imgUrlListStr;
                } elseif ($row['field_type'] == 17) {//版 处理

                    //新的版数据
                    $tmpCreateOrderPlateRow = \App\Eloquent\Zk\CreateOrderPlate::where(['id'=>$dataList[$row['field_name']]])->first();
                    $plateInfo = json_decode(htmlspecialchars_decode($tmpCreateOrderPlateRow['content']), true);
                    if(empty($plateInfo)){
                        $plateInfo = null;
                    }

                    //异常处理
                    if(!empty($plateInfo)){
                        if(empty($plateInfo['branch'])){
                            $plateInfo['branch'] = json_decode('{}');
                        }
                    }

                    $fieldList[$key]['default_value'] = '';
                    $fieldList[$key]['default_plate_list'] = $plateInfo;


                } elseif ($row['field_type'] == 18) {//客户 处理
                    $selectId = $dataList[$row['field_name']];
                    $fieldList[$key]['default_select_id'] = $selectId;

                    $customerTitle = \App\Engine\Customer::getNameById($selectId);
                    if ($customerTitle) {
                        $showTitle = $customerTitle;
                    } else {
                        $showTitle = $dataList[$row['field_name']];
                    }
                    $fieldList[$key]['default_value'] = $showTitle;

                    //客户都不能再修改
                    $fieldList[$key]['is_able_edit'] = 0;
                } elseif ($row['field_type'] == 19) {//单位 处理
                    $selectId = $dataList[$row['field_name']];
                    $fieldList[$key]['default_select_id'] = $selectId;

                    $buyersTitle = \App\Engine\Buyers::getNameById($selectId);

                    if ($buyersTitle) {
                        $showTitle = $buyersTitle;
                    } else {
                        $showTitle = $dataList[$row['field_name']];
                    }
                    $fieldList[$key]['default_value'] = $showTitle;

                    //如果是客户创建的订单，下单人或销售员不能修改单位、品名
                    $createUserInfo = \App\Eloquent\Zk\DepartmentUser::getCurrentInfo($dataList['uid'])->toArray();
                    if($createUserInfo['privilege_id'] == 112 && $userInfo['privilege_id'] != 112){
                        $fieldList[$key]['is_able_edit'] = 0;
                    }

                } elseif ($row['field_type'] == 20) {//品名 处理
//                    $selectId = $dataList[$row['field_name']];
//                    $fieldList[$key]['default_select_id'] = $selectId;
//
//                    $buyersProduct = \App\Eloquent\Ygt\BuyersProduct::where(['id' => $selectId])->first();
//
//                    if ($buyersProduct) {
//                        $showTitle = $buyersProduct['name'];
//                    } else {
//                        $showTitle = $dataList[$row['field_name']];
//                    }
//                    $fieldList[$key]['default_value'] = $showTitle;

                    //品名调整新增型号，可以关联版 zhuyujun  20190226
                    $tmpCreateOrderProductNameRow = \App\Eloquent\Zk\CreateOrderExtend::where(['id'=>$dataList[$row['field_name']]])->first();
                    $productNameInfo = json_decode(htmlspecialchars_decode($tmpCreateOrderProductNameRow['content']), true);
                    if(empty($productNameInfo)){
                        $productNameInfo = null;
                    }

                    $fieldList[$key]['default_value'] = '';
                    $fieldList[$key]['default_proudct_name_list'] = $productNameInfo;

                    //如果是客户创建的订单，下单人或销售员不能修改单位、品名
                    $createUserInfo = \App\Eloquent\Zk\DepartmentUser::getCurrentInfo($dataList['uid'])->toArray();
                    if($createUserInfo['privilege_id'] == 112 && $userInfo['privilege_id'] != 112){
                        $fieldList[$key]['is_able_edit'] = 0;
                    }

                } elseif ($row['field_type'] == 21) {//开票资料 处理
                    $selectId = $dataList[$row['field_name']];
                    $fieldList[$key]['default_select_id'] = $selectId;

                    $buyersInvoice = \App\Eloquent\Zk\BuyersInvoice::where(['id' => $selectId])->first();

                    if ($buyersInvoice) {
                        $showTitle = $buyersInvoice['account_name'];
                    } else {
                        $showTitle = $dataList[$row['field_name']];
                    }
                    $fieldList[$key]['default_value'] = $showTitle;
                }elseif ($row['field_type'] == 22) {//半成品展示
                    $tmpCreateOrderProcessProductRow = \App\Eloquent\Zk\CreateOrderProcessProduct::where(['id'=>$dataList[$row['field_name']]])->first();
                    $processProductInfo = json_decode(htmlspecialchars_decode($tmpCreateOrderProcessProductRow['content']), true);
                    if(empty($processProductInfo)){
                        $processProductInfo = null;
                    }else{
                        //获取半成品的其他数据
                        foreach ($processProductInfo as $tmpKey => $tmpProcessProductInfoRow){
                            $processProductId = isset($tmpProcessProductInfoRow['process_product_id']) ? $tmpProcessProductInfoRow['process_product_id'] : 0;

                            //逻辑调整，现在相关数据动态获取
                            $tmpProcessProductRow = \App\Eloquent\Zk\ProcessProduct::where(['id'=>$processProductId])->first();
                            //获取半成品的品名型号，片料规格，袋类工序，主要用材
                            $product_name = $product_model = $chip_specification = $order_type_title = $main_material =  '';

                            $order_process_product_row = \App\Eloquent\Zk\OrderProcessProduct::where(['process_product_id'=>$processProductId])->first();
                            if($order_process_product_row){
                                $order_id = $order_process_product_row['order_id'];
                                $order_row = \App\Eloquent\Zk\Order::find($order_id);

                                $order_type_title = \App\Engine\OrderType::getOneValueById($order_row['order_type'], 'title');
                                $product_name  = \App\Engine\OrderEngine::getOrderFiledValueTrue($order_row['product_name'], 20);
                                $chip_specification = \App\Engine\OrderEngine::getOrderFiledValueTrue($order_row['chip_specification_length'], 15);


                                //品名型号
                                $tmpCreateOrderProductNameRow = \App\Eloquent\Zk\CreateOrderExtend::where(['id'=>$order_row['product_name']])->first();
                                $productNameInfo = json_decode(htmlspecialchars_decode($tmpCreateOrderProductNameRow['content']), true);
                                if(!empty($productNameInfo['model_list'])){
                                    foreach ($productNameInfo['model_list'] as $modelRow){
                                        $product_model = $modelRow['model_name'];
                                    }
                                }
                            }


                            $process_product_row_deal = \App\Engine\ProcessProduct::getProcessProductDetail($tmpProcessProductRow);

                            if($tmpProcessProductRow){
                                $tmpProcessProductInfoDealRow = [
                                    'process_product_id' => $processProductId,
                                    'chip_specification' => $chip_specification,
                                    //数量还是用当时的值
                                    'number' => $tmpProcessProductInfoRow['number'],
                                    'order_type_title' => $order_type_title,
                                    'plate_model' => $product_model,
                                    'plate_name' => $product_name,
                                    'buyer_name' => $product_name,
                                    'main_material' => $process_product_row_deal['main_material'],
                                    'process_product_title' => $tmpProcessProductInfoRow['process_product_title'],
                                    'unit' => $tmpProcessProductRow['unit'] ? $tmpProcessProductRow['unit'] : '',
                                    'show_attribute_list' => $process_product_row_deal['show_attribute_list'],
                                ];

                                $processProductInfo[$tmpKey] = $tmpProcessProductInfoDealRow;
                            }
                        }
                    }


                    $fieldList[$key]['default_value'] = '';
                    $fieldList[$key]['default_process_product_list'] = $processProductInfo;
                }

                else {
                    $fieldList[$key]['default_value'] = $dataList[$row['field_name']];
                }
            }

        }

        return $fieldList;
    }

    /**
     * Description: 订单议价阶段-获取不同角色对应订单的状态
     * User: zhuyujun
     * @param $customerOrderStatus -- 订单状态
     * @param $privilegeId -- 角色ID
     * @param $customerOrderStatusList -- 订单状态配置表
     */
    static public function getCustomerOrderPriceStatusDeal($customerOrderStatus,$privilegeId,$appnodeIdArr,$customerOrderStatusList){
        $dealStatus = [
            'txt' => '',//状态提示
            'color' => 'FFB401',//状态颜色
        ];

        switch ($customerOrderStatus){
            case 1://待报价
                //客户显示待报价，销售、下单人显示待核准，其他角色不显示
                if($privilegeId == 110){
                    $dealStatus = $customerOrderStatusList[$customerOrderStatus];
                }elseif(in_array(1,$appnodeIdArr) || in_array(13,$appnodeIdArr)){
                    $dealStatus['txt'] = '待核单';
                }
                break;
            case 2://待核准
                //客户显示待报价，销售、下单人显示待核准，其他角色不显示
                if($privilegeId == 110){
                    $dealStatus = $customerOrderStatusList[1];
                }elseif(in_array(1,$appnodeIdArr) || in_array(13,$appnodeIdArr)){
                    $dealStatus = $customerOrderStatusList[$customerOrderStatus];
                }
                break;
            case 3://已核准
                //客户显示待报价，销售、下单人显示已核准，其他角色不显示
                if($privilegeId == 110){
                    $dealStatus = $customerOrderStatusList[1];
                }elseif(in_array(1,$appnodeIdArr) || in_array(13,$appnodeIdArr)){
                    $dealStatus = $customerOrderStatusList[$customerOrderStatus];
                }
                break;
            case 4://已报价
                //客户、销售显示已报价，下单人显示已核准
                if($privilegeId == 110 || in_array(13,$appnodeIdArr)){
                    $dealStatus = $customerOrderStatusList[$customerOrderStatus];
                }elseif(in_array(1,$appnodeIdArr)){
                    $dealStatus = $customerOrderStatusList[3];
                }
                break;
            case 5://待核准
                //客户、销售显示待核准，下单人显示已核准
                if($privilegeId == 110 || in_array(13,$appnodeIdArr)){
                    $dealStatus = $customerOrderStatusList[$customerOrderStatus];
                }elseif(in_array(1,$appnodeIdArr)){
                    $dealStatus = $customerOrderStatusList[3];
                }

                break;
            case 6://待核准
                //客户、销售显示待核准，下单人显示已核准
                if($privilegeId == 110 || in_array(13,$appnodeIdArr)){
                    $dealStatus = $customerOrderStatusList[$customerOrderStatus];
                }elseif(in_array(1,$appnodeIdArr)){
                    $dealStatus = $customerOrderStatusList[3];
                }

                break;
            case 100://已签署
                //其他角色不显示
                if($privilegeId == 110 || in_array(1,$appnodeIdArr) || in_array(13,$appnodeIdArr)){
                    $dealStatus = $customerOrderStatusList[$customerOrderStatus];
                }

                break;
            default:
                break;
        }



        return $dealStatus;
    }






    /**
     * Description: 获取不同角色对应订单的状态
     * User: zhuyujun
     * @param $customerOrderStatus -- 订单状态
     * @param $privilegeId -- 角色ID
     * @param $customerOrderStatusList -- 订单状态配置表
     */
    static public function getCustomerOrderStatusDeal($customerOrderStatus,$privilegeId,$appnodeIdArr,$customerOrderStatusList,$customerOrderId=0){
        $dealStatus = [
            'txt' => '',//状态提示
            'color' => 'FFB401',//状态颜色
        ];

//        p($customerOrderId);
        switch ($customerOrderStatus){
            case 1://已签署
                //客户、销售、下单人显示已签署，其他角色显示待开工
                if($privilegeId == 110 || in_array(1,$appnodeIdArr) || in_array(13,$appnodeIdArr)){
                    $dealStatus = $customerOrderStatusList[$customerOrderStatus];
                }else{
                    $dealStatus = $customerOrderStatusList[3];
                }
                break;
            case 2://待派单
                //都显示已开工
                $dealStatus = $customerOrderStatusList[3];
                break;
            case 3://待生产
                $dealStatus = $customerOrderStatusList[$customerOrderStatus];
                break;
            case 4://生产中

                $dealStatus = $customerOrderStatusList[$customerOrderStatus];

                //hjn 20190813 小秘书列表增加暂停终止状态显示
                if($customerOrderId){
                    $orderPprocessCourseStatus = \App\Eloquent\Zk\Order::where(['customer_order_id'=>$customerOrderId])
                                                        ->join('ygt_order_process','ygt_order_process.order_id','=','ygt_order.id')
                                                        ->join('ygt_order_process_course','ygt_order_process.id','=','ygt_order_process_course.order_process_id')
                                                        ->select('ygt_order_process_course.status','ygt_order_process_course.halt_uid')->get()->toArray();

                    if(in_array('21',array_column($orderPprocessCourseStatus,'status'))){
                        $dealStatus = $customerOrderStatusList[21];
                    }else{
                        if(array_filter(array_column($orderPprocessCourseStatus,'halt_uid')))
                            $dealStatus = $customerOrderStatusList[22];
                    }
                }


                break;
            case 5://已完工
                $dealStatus = $customerOrderStatusList[$customerOrderStatus];
                break;
            case 6://已发货
                //客户、销售显示已收货，其他角色显示已完工
                if($privilegeId == 110 || in_array(13,$appnodeIdArr)){
                    $dealStatus = $customerOrderStatusList[$customerOrderStatus];
                }else{
                    $dealStatus = $customerOrderStatusList[5];
                }
                break;
            case 7://已收讫
                $dealStatus = $customerOrderStatusList[$customerOrderStatus];
                break;
            default:
                break;
        }

        return $dealStatus;
    }


    static public function getUnitIdByTitle($unitTitle, $unitList)
    {
        foreach ($unitList as $unitRow) {
            if ($unitRow['title'] == $unitTitle) {
                return $unitRow['id'];
            }
        }
        return false;
    }

    static public function getSidByTitle($dataTitle, $dataList)
    {
        foreach ($dataList as $dataRow) {
            if ($dataRow['title'] == $dataTitle) {
                return $dataRow['id'];
            }
        }
        return false;
    }


    //派发工单后采购相关处理
    static public function afterDistributionPurchaseDeal($messageData,$tmpMaterialList){
        $msgDwmc = $messageData['msgDwmc'];
        $msgPm = $messageData['msgPm'];
        $orderTitle = $messageData['orderTitle'];
        $msgSl = $messageData['msgSl'];
        $msgJhrq = $messageData['msgJhrq'];
        $msgMaterial = $messageData['msgMaterial'];
        $msgJhrq = $messageData['msgJhrq'];
        $companyId = $messageData['companyId'];
        $userId = $messageData['userId'];
        $foreignKey = $messageData['foreignKey'];
        $orderId = $messageData['orderId'];
        $foreignKey = $messageData['foreignKey'];
        $messageContent = $messageData['messageContent'];


        $order_num = isset($messageData['order_num']) ? $messageData['order_num'] : 0;


        //20190403 多仓库仓管消息调整
        //获取企业的默认仓库
        $default_storehouse_id = \App\Api\Service\Storehouse\Storehouse\Storehouse::getCompanyDefaultStorehouse($companyId)->getId();

        //$msgDwmc-单位名称-$msgGxmc-工艺名称-$msgPm-品名-$msgPlgg-片料规格-$msgSl-数量-$msgJhrq-交货日期
        $messageContent = "有新的工单";
        $messageContent .= 'rnrn单位名称:' . $msgDwmc;//单位名称
        //$messageContent .= 'rnrn工艺名称:' . $msgGxmc;//工艺名称
        $messageContent .= 'rnrn品名:' . $msgPm;//品名
        //$messageContent .= 'rnrn片料规格:' . $msgPlgg;//片料规格
        $messageContent .= 'rnrn工单号:'.$orderTitle;
        $messageContent .= 'rnrn数量:' . $msgSl;//数量
        $messageContent .= 'rnrn交货日期:' . $msgJhrq;//交货日期
        $messageContent .= 'rnrn材料列表:'.$msgMaterial;

        $isSendMessageToWarehouse = 0;//是否发送消息给仓库
        $privilegeList = \App\Engine\OrderEngine::getPrivilegeByNode($companyId, 10);


        //生成待采购单
        /** 添加材料到待采购列表中  zhuyujun 20181130 **/
        $tmpObj = \App\Eloquent\Zk\WaitPurchase::firstOrNew(['id'=>'']);
        $tmpInsertRow = [
            'order_id' => $orderId,
            'type' => 1,
            'status' => 1,
            'uid' => $userId,
            'company_id' => $companyId,
//            'purchase_uid' => $tmpDepartmentUserRow['user_id'],
        ];
        $tmpObj->fill($tmpInsertRow);
        $tmpObj->save();
        $tmpWaitPurchaseId = $tmpObj->id;


        $set_material_formula_obj =  new \App\Eloquent\Zk\SetMaterialFormula();
        $set_material_formula_obj->GetOrderValue($orderId);


        foreach ($tmpMaterialList as $tmpMaterialRow){
            $tmpWaitPurchaseMaterialObj = \App\Eloquent\Zk\WaitPurchaseMaterial::firstOrNew(['id'=>'']);
            $tmpInsertRow = [
                'wait_purchase_id' => $tmpWaitPurchaseId,
                'material_id' => $tmpMaterialRow['material_id'],
            ];
            $tmpWaitPurchaseMaterialObj->fill($tmpInsertRow);
            $tmpWaitPurchaseMaterialObj->save();

            //待采购材料集合表处理
            $assemblage_material_id = 0;
            $material_id = $tmpMaterialRow['material_id'];//集合材料ID
            $type = 1;//sku材料
            $material_name = '';
            $supllier_name = '';
            if(strstr($tmpMaterialRow['material_id'],'A')){
                $assemblage_material_id = str_replace('A','',$tmpMaterialRow['material_id']);
                $material_id = $assemblage_material_id;
                $assemblage_material_row = \App\Eloquent\Zk\AssemblageMaterial::find($material_id);
                if($assemblage_material_row){
                    $material_name = $assemblage_material_row['product_name'];
                }
                $type = 2;//集合材料
            }else{
                //获取sku材料ID
                $product_row = \App\Eloquent\Zk\Product::find($material_id);
                if($product_row){
                    $assemblage_material_id = $product_row['assemblage_material_id'];
                    $material_name = $product_row['product_name'];

                    //获取供应商
                    $seller_company_row = \App\Eloquent\Zk\SellerCompany::where(['id'=>$product_row['seller_company_id']])->first();
                    if($seller_company_row){
                        $supllier_name = $seller_company_row['title'];
                    }
                }
            }

            $where = [
                'material_id' => $material_id,
                'type' => $type,
            ];
//            $where[] = ['material_id','=',$tmpMaterialRow['material_id']];
//            $where[] = ['type','=',$type];
            $tmpObj = \App\Eloquent\Zk\WaitePurchaseAggregate::firstOrNew($where);


            //统计
            $process_id = $tmpMaterialRow['process_type'];
            $formula = $set_material_formula_obj->returnFormula($process_id,$assemblage_material_id);


            /*功能块：计算待采购数量需减去下单时的半成品选择数量 zhuyujun 20190624*/
            $select_process_product_number = 0;//下单选择半成品的数量（如果有多个取最小值）
            $tmp_order_process_id = $tmpMaterialRow['order_process_id'];
            $tmp_order_process_row = \App\Eloquent\Zk\OrderProcess::find($tmp_order_process_id);

            //获取工序里是半成品的字段
            $field_list = \App\Engine\OrderEngine::getOrderProcessFieldByType($companyId,22);
            foreach ($field_list as $field_row){
                if(isset($tmp_order_process_row[$field_row['field_name']]) && $tmp_order_process_row[$field_row['field_name']] ){
                    $tmpCreateOrderProcessProductRow = \App\Eloquent\Zk\CreateOrderProcessProduct::where(['id'=>$tmp_order_process_row[$field_row['field_name']]])->first();
                    $processProductInfo = json_decode(htmlspecialchars_decode($tmpCreateOrderProcessProductRow['content']), true);
                    if(!empty($processProductInfo)){
                        //获取半成品的其他数据
                        foreach ($processProductInfo as $tmpKey => $tmpProcessProductInfoRow){
                            if($tmpProcessProductInfoRow['number']){
                                if($select_process_product_number == 0 ){
                                    $select_process_product_number = $tmpProcessProductInfoRow['number'];
                                }elseif($select_process_product_number > $tmpProcessProductInfoRow['number']){
                                    $select_process_product_number = $tmpProcessProductInfoRow['number'];
                                }
                            }
                        }
                    }
                }
            }

//            $cur_need_puchase_number = $order_num * $formula;
            $cur_need_puchase_number = ($order_num - $select_process_product_number) * $formula;


            if($cur_need_puchase_number <= 0){
                $cur_need_puchase_number = 1;//如果没有计算出来待采购数量，默认为1，用于标记待采购
            }



            $tmpOrderNum = $tmpObj->order_number + 1;
            $all_number = $tmpObj->all_number + $cur_need_puchase_number;
            $now_number =  $tmpObj->now_number + $cur_need_puchase_number;

            $seach_keyword = $material_name.",".$supllier_name;

            //统计集合
            $tmpInsertRow = [
                'material_id' => $material_id,
                'type' => $type,
                'now_number' => $now_number,
                'order_number' => $tmpOrderNum,
                'company_id' => $companyId,
                'order_number' => $tmpOrderNum,
                'all_number' => $all_number,
                'now_number' => $now_number,
                'seach_keyword' => $seach_keyword,
            ];
            $tmpObj->fill($tmpInsertRow);
            $tmpObj->save();
            $tmpWaitePurchaseAggregateId = $tmpObj->id;

            //关联待采购材料集合ID
            $tmpWaitPurchaseMaterialObj->number = $cur_need_puchase_number;
            $tmpWaitPurchaseMaterialObj->waite_purchase_aggregate_id = $tmpWaitePurchaseAggregateId;
            $tmpWaitPurchaseMaterialObj->save();
        }


        foreach ($privilegeList as $privilegeId) {
            //获取该角色下的员工
            $tmpDepartmentUserList = \App\Eloquent\Zk\DepartmentUser::where(['company_id'=>$companyId,'privilege_id'=>$privilegeId])->get();
            foreach ($tmpDepartmentUserList as $tmpDepartmentUserRow){

                //采购主管
                if($tmpDepartmentUserRow['is_leader']){

                    $data = [
                        'company_id' => $companyId,
                        'privilege_id' => '',
                        'form_user_id' => $userId,
                        'to_user_id' => $tmpDepartmentUserRow['user_id'],
                        'foreign_key' => $foreignKey,
                        'type' => 23,//发送给采购员的消息
                        'type_id' => $tmpWaitPurchaseId,
                        'title' => $orderTitle,
                        'content' => $messageContent,
                        'type_status' => 1,
                        'theme'=>'工单所需用料'
                    ];
                    \App\Eloquent\Zk\UserMessage::sendCustomerOrderMessage($data);


//                    //给仓库发消息
//                    if(!$isSendMessageToWarehouse){
//                        $privilegeList = \App\Engine\OrderEngine::getPrivilegeByNode($companyId, 7);
//                        foreach ($privilegeList as $privilegeId) {
//                            $data = [
//                                'company_id' => $companyId,
//                                'privilege_id' => $privilegeId,
//                                'form_user_id' => $userId,
//                                'to_user_id' => '',
//                                'foreign_key' => $foreignKey,
//                                'type' => 23,//发送给采购员的消息
//                                'type_id' => $tmpWaitPurchaseId,
//                                'title' => $orderTitle,
//                                'content' => $messageContent,
//                                'type_status' => 1,
//                                'theme'=>'工单所需用料'
//                            ];
//                            \App\Eloquent\Ygt\UserMessage::sendCustomerOrderMessage($data);
//                        }
//                        $isSendMessageToWarehouse = 1;
//                    }


                }else{
                    //采购员工
                    $tmpDepartmentUserRow['user_id'];
                    //获取配置的材料分类
                    $tmpPurchaseManageRow = \App\Eloquent\Zk\PurchaseManage::where(['company_id'=>$companyId,'uid'=>$tmpDepartmentUserRow['user_id']])->first();
                    $assemblageMaterialIdList = [];//集合材料ID
                    $materialIdList = [];//sku材料ID
                    if($tmpPurchaseManageRow['category_ids']){
                        $tmpCategoryIdArr = explode(',',$tmpPurchaseManageRow['category_ids']);
                        $assemblageMaterialIdList = \App\Eloquent\Zk\AssemblageMaterial::whereIn('category_id',$tmpCategoryIdArr)->get()->pluck('id')->toArray();
                        $materialIdList = \App\Eloquent\Zk\Product::whereIn('category_id',$tmpCategoryIdArr)->get()->pluck('id')->toArray();
                    }

                    $tmpMaterialDealList = [];
                    $msgMaterial = '';
                    foreach ($tmpMaterialList as $tmpMaterialRow){
                        if(strstr($tmpMaterialRow['material_id'],'A')){
                            $tmpAssemblageMaterialId = str_replace('A','',$tmpMaterialRow['material_id']);
                            if(in_array($tmpAssemblageMaterialId,$assemblageMaterialIdList)){
                                $tmpMaterialDealList[] = $tmpMaterialRow;

                                $tmpMaterialRow = \App\Engine\Product::getProductInfo($tmpMaterialRow['material_id']);
                                $msgMaterial.=$tmpMaterialRow['product_name'].' ';
                            }
                        }else{
                            if(in_array($tmpMaterialRow['material_id'],$materialIdList)){
                                $tmpMaterialDealList[] = $tmpMaterialRow;

                                $tmpMaterialRow = \App\Engine\Product::getProductInfo($tmpMaterialRow['material_id']);
                                $msgMaterial.=$tmpMaterialRow['product_name'].' ';
                            }
                        }
                    }

                    //修改消息中的材料列表信息
                    $tmpArr = explode('rnrn',$messageContent);
                    array_pop($tmpArr);
                    $messageContentDeal = implode('rnrn',$tmpArr);
                    $messageContentDeal .= 'rnrn材料列表:'.$msgMaterial;

                    if(!empty($tmpMaterialDealList)){
//                        /** 添加材料到待采购列表中  zhuyujun 20181130 **/
//                        $tmpObj = \App\Eloquent\Ygt\WaitPurchase::firstOrNew(['id'=>'']);
//                        $tmpInsertRow = [
//                            'order_id' => $orderId,
//                            'type' => 1,
//                            'status' => 1,
//                            'uid' => $userId,
//                            'company_id' => $companyId,
//                            'purchase_uid' => $tmpDepartmentUserRow['user_id'],
//                        ];
//                        $tmpObj->fill($tmpInsertRow);
//                        $tmpObj->save();
//                        $tmpWaitPurchaseId = $tmpObj->id;
//
//                        foreach ($tmpMaterialDealList as $tmpMaterialRow){
//                            $tmpObj = \App\Eloquent\Ygt\WaitPurchaseMaterial::firstOrNew(['id'=>'']);
//                            $tmpInsertRow = [
//                                'wait_purchase_id' => $tmpWaitPurchaseId,
//                                'material_id' => $tmpMaterialRow['material_id'],
//                            ];
//                            $tmpObj->fill($tmpInsertRow);
//                            $tmpObj->save();
//                        }

                        $data = [
                            'company_id' => $companyId,
                            'privilege_id' => '',
                            'form_user_id' => $userId,
                            'to_user_id' => $tmpDepartmentUserRow['user_id'],
                            'foreign_key' => $foreignKey,
                            'type' => 23,//发送给采购员的消息
                            'type_id' => $tmpWaitPurchaseId,
                            'title' => $orderTitle,
                            'content' => $messageContentDeal,
                            'type_status' => 1,
                            'theme'=>'工单所需用料'
                        ];
                        \App\Eloquent\Zk\UserMessage::sendCustomerOrderMessage($data);
                    }
                }
            }
        }

        //20190403 多仓库仓管消息调整
        //通过材料的归属权限，发送消息给对应的仓管员
        $dealAssemblageMaterial = [];
        $sendStorehouseList = [];//存放需要发送消息仓管的信息（含哪几种材料）
        foreach ($tmpMaterialList as $tmpMaterialRow){
            //获取集合材料ID对应的权限列表
            if(strstr($tmpMaterialRow['material_id'],'A')){
                $tmpAssemblageMaterialId = str_replace('A','',$tmpMaterialRow['material_id']);
                $tmpMaterialRow = \App\Engine\Product::getProductInfo($tmpMaterialRow['material_id']);
                $tmpMaterialRow['product_name'];
            }else{
                $tmpMaterialRow = \App\Engine\Product::getProductInfo($tmpMaterialRow['material_id']);
                $tmpAssemblageMaterialId = $tmpMaterialRow['assemblage_material_id'];
                $tmpMaterialRow['product_name'];
            }

            //获取有权限的用户
            $tmpUserIdList = \App\Api\Service\Storehouse\NodeAssignment\NodeAssignment::getManageMaterialAllUserId($default_storehouse_id,$tmpAssemblageMaterialId);
            foreach ($tmpUserIdList as $tmpUserId){
                if(!isset($sendStorehouseList[$tmpUserId])){
                    $sendStorehouseList[$tmpUserId] = '';
                }
                $sendStorehouseList[$tmpUserId] .= $tmpMaterialRow['product_name'].",";//需要发送的材料名称
            }
        }

        //发送消息给仓库
        foreach ($sendStorehouseList as $tmpSendUserId => $msgMaterial){
            //如果用户没有仓管权限不发消息
            $tmp_user_info = \App\Eloquent\Zk\DepartmentUser::getInfoByCompanyIdAndUserId($companyId,$tmpSendUserId);
            if($tmp_user_info){
                $tmp_user_info = $tmp_user_info->toArray();
            }else{
                continue;
            }

            $privilegeIdList = \App\Engine\OrderEngine::getPrivilegeByNode($companyId, 7);
            if (!in_array($tmp_user_info['privilege_id'], $privilegeIdList)) {
                continue;
            }

            //修改消息中的材料列表信息
            $tmpArr = explode('rnrn',$messageContent);
            array_pop($tmpArr);
            $messageContentDeal = implode('rnrn',$tmpArr);
            $messageContentDeal .= 'rnrn材料列表:'.$msgMaterial;

            $data = [
                'company_id' => $companyId,
                'privilege_id' => '',
                'form_user_id' => $userId,
                'to_user_id' => $tmpSendUserId,
                'foreign_key' => $foreignKey,
                'type' => 46,//发送给采购员的消息
                'type_id' => $orderId,
                'title' => $orderTitle,
                'content' => $messageContentDeal,
                'type_status' => 1,
                'theme'=>'工单所需用料'
            ];
            \App\Eloquent\Zk\UserMessage::sendCustomerOrderMessage($data);
        }

//        //没有材料主管但是有仓库需要发消息
//        if(!$isSendMessageToWarehouse){
//            $privilegeList = \App\Engine\OrderEngine::getPrivilegeByNode($companyId, 7);
//            if(!empty($privilegeList)){
//                /** 添加材料到待采购列表中  zhuyujun 20181130 **/
//                $tmpObj = \App\Eloquent\Ygt\WaitPurchase::firstOrNew(['id'=>'']);
//                $tmpInsertRow = [
//                    'order_id' => $orderId,
//                    'type' => 1,
//                    'status' => 1,
//                    'uid' => $userId,
//                    'company_id' => $companyId,
//                    'purchase_uid' => $tmpDepartmentUserRow['user_id'],
//                ];
//                $tmpObj->fill($tmpInsertRow);
//                $tmpObj->save();
//                $tmpWaitPurchaseId = $tmpObj->id;
//
//
//                foreach ($tmpMaterialList as $tmpMaterialRow){
//                    $tmpObj = \App\Eloquent\Ygt\WaitPurchaseMaterial::firstOrNew(['id'=>'']);
//                    $tmpInsertRow = [
//                        'wait_purchase_id' => $tmpWaitPurchaseId,
//                        'material_id' => $tmpMaterialRow['material_id'],
//                    ];
//                    $tmpObj->fill($tmpInsertRow);
//                    $tmpObj->save();
//                }
//
//                foreach ($privilegeList as $privilegeId) {
//                    $data = [
//                        'company_id' => $companyId,
//                        'privilege_id' => $privilegeId,
//                        'form_user_id' => $userId,
//                        'to_user_id' => '',
//                        'foreign_key' => $foreignKey,
//                        'type' => 23,//发送给采购员的消息
//                        'type_id' => $tmpWaitPurchaseId,
//                        'title' => $orderTitle,
//                        'content' => $messageContent,
//                        'type_status' => 1,
//                        'theme'=>'工单所需用料'
//                    ];
//                    \App\Eloquent\Ygt\UserMessage::sendCustomerOrderMessage($data);
//                }
//
//                $isSendMessageToWarehouse = 1;
//            }
//        }
    }

    static public function distributeData($orderList,$page=1,$limit=1,$count=1,$hide_copy_order=0)
    {



        //获取登陆用户信息
        $userId = \App\Engine\Func::getHeaderValueByName('userid');
        $userInfo = \App\Eloquent\Zk\DepartmentUser::getCurrentInfo($userId)->toArray();

        //判断是否为下单人 zhuyujun 20190620
        $is_single_person = 0;
        if($userInfo['company_id'] != 2){
            $is_order_list_jump = \App\Eloquent\Zk\Privilege::getAppnodeId($userInfo['privilege_id'], 1);//是否为下单人
            $is_supreme_authority = \App\Eloquent\Zk\Privilege::getAppnodeId($userInfo['privilege_id'], 9);//是否为下单人
            if($is_order_list_jump || $is_supreme_authority){
                $is_single_person = 1;
            }
        }

        $returnOrderList = [];
        $dataList = [];
        foreach ($orderList as $orderRow) {
            $type = $orderRow['type'];//工单类型 1-主工单 2-工序管理员工单 3-查看权限用户的工序工单 4-员工工单
            $status = $orderRow['status'];//不同类型工单，状态有所不同
            $orderRowContent = unserialize($orderRow['content']);

            //追加工单进度情况
            $progressBar = [];//各工艺进度条
            $mainOrderId = $mainOrderStatus = '';
            if($type == 1){
                $mainOrderStatus = $status;
                $mainOrderId = $orderRow['relate_id'];

                //进度条
                $tmpOrderProcessList = \App\Eloquent\Zk\OrderProcess::where(['order_id'=>$mainOrderId])->get();

                //获取工单对应的所有工序
                $tmpOrderProcessList = \App\Engine\OrderType::getAllOrderTypeProcess($orderRowContent['order_type'])->toArray();
                foreach ($tmpOrderProcessList as $tmpProcessId) {
                    $tmpOrderProcessRow = \App\Eloquent\Zk\OrderProcess::where(['order_id' => $orderRowContent['id'], 'process_type' => $tmpProcessId])->first();
                    if (!$tmpOrderProcessRow) {
                        continue;
                    }
//                }

//                foreach ($tmpOrderProcessList as $tmpOrderProcessRow){
                    //工序名称
                    $tmpProcessTitle = \App\Eloquent\Zk\Process::getOneValueById($tmpOrderProcessRow['process_type'], 'title');

                    //工序完成度
                    $tmpOrderProcessNeedNum =\App\Engine\OrderEngine::getOrderPorcessNeedNum($tmpOrderProcessRow['process_type'],$orderRowContent);//需完成数量
                    $tmpRadio = 0;
                    if($tmpOrderProcessNeedNum){
                        //by lwl 2019 05 06 修改非数字计算报错和零计算报错
                        if($tmpOrderProcessRow['completed_number']){
                            $tmpRadio = $tmpOrderProcessRow['completed_number'] / intval($tmpOrderProcessNeedNum);
                        }
                        //by lwl 2019 05 06 修改非数字计算报错和零计算报错
                        //$tmpRadio = $tmpOrderProcessRow['completed_number'] / $tmpOrderProcessNeedNum;
                    }

//                    if($tmpRadio > 1){
//                        $tmpRadio = 1;
//                    }

                    $tmpRadio = sprintf('%.2f',$tmpRadio);
                    $tmpType = \App\Engine\OrderEngine::getOrderRadioType($tmpRadio);


                    //增加待派发标记 zhuyujun 20190520
                    $is_assign = 0;
                    //有工序管理员
                    if($tmpOrderProcessRow['uid'] && $tmpOrderProcessRow['uid'] != $orderRowContent['uid']){
                        $is_assign = 1;
                    }

                    //没有工序管理员
                    if($tmpOrderProcessRow['uid'] == $orderRowContent['uid']){
                        $tmp_count = \App\Eloquent\Zk\OrderProcessCourse::where(['order_process_id'=>$tmpOrderProcessRow['id']])->count();
                        if($tmp_count){
                            $is_assign = 1;
                        }
                    }


                    $progressBar[] = [
                        'type' => $tmpType,
                        'radio' => $tmpRadio,
                        'title' => $tmpProcessTitle,
                        'is_assign' => $is_assign,
                    ];
                }

            }elseif($type == 2 || $type == 3){
                //获取主工单状态&ID
                $tmpOrderProcessId = $orderRow['relate_id'];
//                $mainOrderRow = \App\Eloquent\Ygt\OrderProcess::leftJoin('ygt_order','ygt_order.id','ygt_order_process.order_id')->where(['ygt_order_process.id'=>$tmpOrderProcessId])->select(
//                    'ygt_order.id',
//                    'ygt_order.status'
//                )->first();
//                $mainOrderId = $mainOrderRow['id'];
//                $mainOrderStatus = $mainOrderRow['status'];

                //进度条
                $tmpOrderProcessList = \App\Eloquent\Zk\OrderProcess::where(['id'=>$tmpOrderProcessId])->get();
                foreach ($tmpOrderProcessList as $tmpOrderProcessRow){
                    //工序名称
                    $tmpProcessTitle = \App\Eloquent\Zk\Process::getOneValueById($tmpOrderProcessRow['process_type'], 'title');

                    //工序完成度
                    $tmpOrderProcessNeedNum =\App\Engine\OrderEngine::getOrderPorcessNeedNum($tmpOrderProcessRow['process_type'],$orderRowContent);//需完成数量
                    $tmpRadio = 0;
                    if($tmpOrderProcessNeedNum){
                        $tmpRadio = $tmpOrderProcessRow['completed_number'] / $tmpOrderProcessNeedNum;
                    }

                    $tmpRadio = sprintf('%.2f',$tmpRadio);
                    $tmpType = \App\Engine\OrderEngine::getOrderRadioType($tmpRadio);

                    //增加待派发标记 zhuyujun 20190520
                    $is_assign = 0;
                    //有工序管理员
                    if($tmpOrderProcessRow['uid'] && $tmpOrderProcessRow['uid'] != $orderRowContent['uid']){
                        $is_assign = 1;
                    }

                    //没有工序管理员
                    if($tmpOrderProcessRow['uid'] == $orderRowContent['uid']){
                        $tmp_count = \App\Eloquent\Zk\OrderProcessCourse::where(['order_process_id'=>$tmpOrderProcessRow['id']])->count();
                        if($tmp_count){
                            $is_assign = 1;
                        }
                    }


                    $progressBar[] = [
                        'type' => $tmpType,
                        'radio' => $tmpRadio,
                        'title' => $tmpProcessTitle,
                        'is_assign' => $is_assign,
                    ];
                }



            }elseif($type == 4){
                //获取主工单状态&ID
                $tmpOrderProcessCourseId = $orderRow['relate_id'];
                $tmpOrderProcessId = \App\Eloquent\Zk\OrderProcessCourse::where(['id'=>$tmpOrderProcessCourseId])->first()->order_process_id;
//                $mainOrderRow = \App\Eloquent\Ygt\OrderProcess::leftJoin('ygt_order','ygt_order.id','ygt_order_process.order_id')->where(['ygt_order_process.id'=>$tmpOrderProcessId])->select(
//                    'ygt_order.id',
//                    'ygt_order.status'
//                )->first();
//                $mainOrderId = $mainOrderRow['id'];
//                $mainOrderStatus = $mainOrderRow['status'];

                //进度条
                $tmpOrderProcessList = \App\Eloquent\Zk\OrderProcess::where(['id'=>$tmpOrderProcessId])->get();
                foreach ($tmpOrderProcessList as $tmpOrderProcessRow){
                    //工序名称
                    $tmpProcessTitle = \App\Eloquent\Zk\Process::getOneValueById($tmpOrderProcessRow['process_type'], 'title');

                    //工序完成度
                    $tmpOrderProcessNeedNum =\App\Engine\OrderEngine::getOrderPorcessNeedNum($tmpOrderProcessRow['process_type'],$orderRowContent);//需完成数量
                    $tmpRadio = 0;
                    if($tmpOrderProcessNeedNum){
                        $tmpRadio = $tmpOrderProcessRow['completed_number'] / $tmpOrderProcessNeedNum;
                    }

                    $tmpRadio = sprintf('%.2f',$tmpRadio);
                    $tmpType = \App\Engine\OrderEngine::getOrderRadioType($tmpRadio);



                    //增加待派发标记 zhuyujun 20190520
                    $is_assign = 0;
                    //有工序管理员
                    if($tmpOrderProcessRow['uid'] && $tmpOrderProcessRow['uid'] != $orderRowContent['uid']){
                        $is_assign = 1;
                    }

                    //没有工序管理员
                    if($tmpOrderProcessRow['uid'] == $orderRowContent['uid']){
                        $tmp_count = \App\Eloquent\Zk\OrderProcessCourse::where(['order_process_id'=>$tmpOrderProcessRow['id']])->count();
                        if($tmp_count){
                            $is_assign = 1;
                        }
                    }

                    $progressBar[] = [
                        'type' => $tmpType,
                        'radio' => $tmpRadio,
                        'title' => $tmpProcessTitle,
                        'is_assign' => $is_assign,
                    ];
                }
            }

            //过滤掉工艺名称的工序名称
            $tmpOrderTypeTitle = \App\Engine\OrderType::getOneValueById($orderRowContent['order_type'], 'title');//工艺名称
            $tmpDealList = [];
            foreach ($progressBar as $tmpKey => $tmpRow){
                $tmpRow['title'] = str_replace([$tmpOrderTypeTitle."•",$tmpOrderTypeTitle],'',$tmpRow['title']);
                $tmpDealList[$tmpKey] = $tmpRow;
            }
            $progressBar = $tmpDealList;

            //获取生产实例图
            $productionCaseDiagram = \App\Engine\OrderEngine::getOrderFiledValueTrue($orderRowContent['production_case_diagram'], 9);

            //处理字段
            $orderRowContent = \App\Engine\OrderEngine::dealOrderFiled($orderRowContent,$show_product_model_name=1);

            //订单创建时间（派发时间）
            $orderCreateTime = $orderRow['order_create_time'];
            if ($orderCreateTime) {
                $orderCreateTime = date('Y-m-d', $orderCreateTime);
            }

            //获取工序类型名称
            $orderTypeTitle = OrderType::getOneValueById($orderRowContent['order_type'], 'title');

            //工单列表不同类型相同字段，统一管理
            $productName = \App\Engine\OrderEngine::getOrderFiledValueTrue($orderRowContent['product_name'], 20,$show_product_model_name = 1);

            //增加图片ID
            $imgId = $orderRowContent['production_case_diagram'];

            $orderRowContent['order_title'] = \App\Engine\Common::changeSnCode($orderRowContent['order_title']);
            $commonArr = [
                'orderProcessId'  =>  isset($tmpOrderProcessId) && $tmpOrderProcessId ?$tmpOrderProcessId:"",
                'order_list_relation_id' => $orderRow['id'],
                '$customer_title' => '单位名称',//工单列表展示字段
                '$product_title' => '品名',//工单列表展示字段
                '$order_title' => '单号',//工单列表展示字段
                '$customer_name' => isset($orderRowContent['field_name_23']) ? $orderRowContent['field_name_23'] : '',//新加字段客户
                '$product_name' => isset($productName) ? $productName : '',//新加字段品名
                '$order_title_value' => $orderRowContent['order_title'],//新加字段单号
                '$firstPropertyName' => '片料规格',
                '$secondPropertyName' => '数量',
                '$thirdPropertyName' => '交货日期',
                '$thirdPropertyValue' => $orderRowContent['finished_date'],
                '$secondPropertyValue' => $orderRowContent['product_num'],
                '$firstPropertyValue' =>
                    isset($orderRowContent['chip_specification_length']) ? \App\Engine\OrderEngine::getOrderFiledValueTrue($orderRowContent['chip_specification_length'],15) : '',
                //20190730 读成品规格改成读片料规格
                //isset($orderRowContent['finished_specification']) ? $orderRowContent['finished_specification'] : '',

                '$productionCaseDiagram' => $productionCaseDiagram,
                '$productOrderTimeValue' => $orderCreateTime,
                '$orderTypeTitle' => $orderTypeTitle,
                '$orderNumberValue' => $orderTypeTitle,
                '$orderTimeValue' => $orderRowContent['finished_date'],
                '$relate_id' => $orderRow['relate_id'],
                'is_distribute' => isset($orderRow['is_distribute']) ? $orderRow['is_distribute'] : 0,
                'order_type' => isset($orderRow['order_type']) ? $orderRow['order_type'] : 0,
                'img_id' => $imgId,
                'is_single_person' => $is_single_person,//是否为下单人（再来一单功能）
                'progress_bar' => $progressBar,
                'is_delete' => $orderRow['is_delete'],
                'sort'=>$orderRow['sort'],
            ];
            $button_list = [];//新增按钮列表 zhuyujun 20190620
            if ($type == 1) {//1-主工单

                if ($status == 1) {//待接单
                    $tmpArr = [
                        'idName' => 5,
                        '$itemFunctionName' => 'mangerDoneCellFunction' . $orderRow['relate_id'],
                        '$statusCode' => $status,//新增订单状态值
                        '$orderStatus' => '待接单',
                        '$statusColor' => 'FFB401',
                    ];

                    $button_list = [
                        [
                            'id' => $orderRow['relate_id'],
                            'title' => '撤回',
                            'order_category' => 1,//1-主工单 2-工序工单 3-工序工单（纯查看） 4-员工工单
                            'type' => 'recall',
                        ],
                    ];

                    //订单的相关工单列表需动态显示再来一单按钮 zhuyujun 20190620
//                    if($is_single_person && !$hide_copy_order){
//                        $button_list[] = [
//                            'id' => $orderRow['relate_id'],
//                            'title' => '再来一单',
//                            'order_category' => 1,//1-主工单 2-工序工单 3-工序工单（纯查看） 4-员工工单
//                            'type' => 'copy_order',
//                        ];
//                    }


                    $tmpArr['button_list'] = $button_list;

                    $dataList[] = array_merge($commonArr, $tmpArr);

                } elseif ($status == 101) {//待开工
                    $tmpArr = [
                        'idName' => 3,
                        '$itemFunctionName' => 'mangerDoneCellFunction' . $orderRow['relate_id'],
                        '$statusCode' => $status,//新增订单状态值
                        '$orderStatus' => '待开工',
                        '$statusColor' => 'FFB401',
                    ];

                    $button_list = [
                        [
                            'id' => $orderRow['relate_id'],
                            'title' => '撤回',
                            'order_category' => 1,//1-主工单 2-工序工单 3-工序工单（纯查看） 4-员工工单
                            'type' => 'recall',
                        ],
                    ];
                    //订单的相关工单列表需动态显示再来一单按钮 zhuyujun 20190620
//                    if($is_single_person && !$hide_copy_order){
//                        $button_list[] = [
//                            'id' => $orderRow['relate_id'],
//                            'title' => '再来一单',
//                            'order_category' => 1,//1-主工单 2-工序工单 3-工序工单（纯查看） 4-员工工单
//                            'type' => 'copy_order',
//                        ];
//                    }

                    $tmpArr['button_list'] = $button_list;
                    $dataList[] = array_merge($commonArr, $tmpArr);
                } elseif ($status == 2) {//生产中

                    if($orderRow['halt_uid']){
                        $tmpArr = [
                            'idName' => 3,
                            '$itemFunctionName' => 'mangerDoneCellFunction' . $orderRow['relate_id'],
                            '$statusCode' => $status,//新增订单状态值
                            '$orderStatus' => '已暂停',
                            '$statusColor' => 'FE7E57',
                        ];
                        $button_list = [
                            [
                                'id' => $orderRow['relate_id'],
                                'title' => '开始',
                                'order_category' => 1,//1-主工单 2-工序工单 3-工序工单（纯查看） 4-员工工单
                                'type' => 'strat',
                            ],
                        ];
                    }else{
                        $tmpArr = [
                            'idName' => 3,
                            '$itemFunctionName' => 'mangerDoneCellFunction' . $orderRow['relate_id'],
                            '$statusCode' => $status,//新增订单状态值
                            '$orderStatus' => '生产中',
                            '$statusColor' => 'FE7E57',
                        ];
                        $button_list = [
                            [
                                'id' => $orderRow['relate_id'],
                                'title' => '终止',
                                'order_category' => 1,//1-主工单 2-工序工单 3-工序工单（纯查看） 4-员工工单
                                'type' => 'stop',
                            ],
                            [
                                'id' => $orderRow['relate_id'],
                                'title' => '暂停',
                                'order_category' => 1,//1-主工单 2-工序工单 3-工序工单（纯查看） 4-员工工单
                                'type' => 'pause',
                            ],
                        ];
                    }

                    $tmpArr['button_list'] = $button_list;
                    //订单的相关工单列表需动态显示再来一单按钮 zhuyujun 20190620
//                    if($is_single_person && !$hide_copy_order){
//                        $button_list[] = [
//                            'id' => $orderRow['relate_id'],
//                            'title' => '再来一单',
//                            'order_category' => 1,//1-主工单 2-工序工单 3-工序工单（纯查看） 4-员工工单
//                            'type' => 'copy_order',
//                        ];
//                    }

                    $dataList[] = array_merge($commonArr, $tmpArr);
                } elseif ($status == 3) {//已完工
                    $tmpArr = [
                        'idName' => 6,
                        '$itemFunctionName' => 'mangerDoneCellFunction' . $orderRow['relate_id'],
                        '$orderSureName' => 'orderSure' . $orderRow['relate_id'],
                        '$statusCode' => $status,//新增订单状态值
                        '$orderStatus' => '已完成',
                        '$statusColor' => '04C9B3',
                    ];
                    $button_list = [
                        [
                            'id' => $orderRow['relate_id'],
                            'title' => '删除',
                            'order_category' => 1,//1-主工单 2-工序工单 3-工序工单（纯查看） 4-员工工单
                            'type' => 'delete',
                        ],
                    ];

                    //订单的相关工单列表需动态显示再来一单按钮 zhuyujun 20190620
//                    if($is_single_person && !$hide_copy_order){
//                        $button_list[] = [
//                            'id' => $orderRow['relate_id'],
//                            'title' => '再来一单',
//                            'order_category' => 1,//1-主工单 2-工序工单 3-工序工单（纯查看） 4-员工工单
//                            'type' => 'copy_order',
//                        ];
//                    }

                    $tmpArr['button_list'] = $button_list;
                    $dataList[] = array_merge($commonArr, $tmpArr);
                } elseif ($status == 4) {//待派发
//                    continue;//未派发工单另外处理
                    $tmpArr = [
                        'idName' => 7,
                        '$itemFunctionName' => 'itemFunctionName' . $orderRow['relate_id'],
                        '$orderSureName' => 'orderSureName'.$orderRow['relate_id'],
                        '$statusCode' => $status,//新增订单状态值
                        '$orderStatus' => '待派发',
                        '$statusColor' => 'B5B5B5',
                    ];


                    /**只有下单人有权限派发工单**/
                    //获取某权限的用户
                    $companyId = $orderRowContent['company_id'];
                    $privilegeIdList = \App\Engine\OrderEngine::getPrivilegeByNode($companyId, 1);
                    if (!in_array($userInfo['privilege_id'], $privilegeIdList)) {
                        $tmpArr[ 'is_distribute'] = 0;
                    }
                    $button_list = [
                        [
                            'id' => $orderRow['relate_id'],
                            'title' => '确认派发',
                            'order_category' => 1,//1-主工单 2-工序工单 3-工序工单（纯查看） 4-员工工单
                            'type' => 'distribute',
                        ],
                        [
                            'id' => $orderRow['relate_id'],
                            'title' => '删除',
                            'order_category' => 1,//1-主工单 2-工序工单 3-工序工单（纯查看） 4-员工工单
                            'type' => 'delete',
                        ],
                    ];
                    //订单的相关工单列表需动态显示再来一单按钮 zhuyujun 20190620
//                    if($is_single_person && !$hide_copy_order){
//                        $button_list[] = [
//                            'id' => $orderRow['relate_id'],
//                            'title' => '再来一单',
//                            'order_category' => 1,//1-主工单 2-工序工单 3-工序工单（纯查看） 4-员工工单
//                            'type' => 'copy_order',
//                        ];
//                    }


                    $tmpArr['button_list'] = $button_list;

                    $dataList[] = array_merge($commonArr, $tmpArr);
                }elseif ($status == 21) {//终止
                    $tmpArr = [
                        'idName' => 3,
                        '$itemFunctionName' => 'mangerDoneCellFunction' . $orderRow['relate_id'],
                        '$statusCode' => $status,//新增订单状态值
                        '$orderStatus' => '已终止',
                        '$statusColor' => 'FE7E57',
                    ];
                    $button_list = [
                        [
                            'id' => $orderRow['relate_id'],
                            'title' => '删除',
                            'order_category' => 1,//1-主工单 2-工序工单 3-工序工单（纯查看） 4-员工工单
                            'type' => 'delete',
                        ],
                    ];
                    $tmpArr['button_list'] = $button_list;
                    $dataList[] = array_merge($commonArr, $tmpArr);
                }
//                }elseif ($status == 22) {//暂停
//                    $tmpArr = [
//                        'idName' => 3,
//                        '$itemFunctionName' => 'mangerDoneCellFunction' . $orderRow['relate_id'],
//                        '$statusCode' => $status,//新增订单状态值
//                        '$orderStatus' => '已暂停',
//                        '$statusColor' => 'FE7E57',
//                    ];
//                    $button_list = [
//                        [
//                            'id' => $orderRow['relate_id'],
//                            'title' => '开始',
//                            'order_category' => 1,//1-主工单 2-工序工单 3-工序工单（纯查看） 4-员工工单
//                            'type' => 'strat',
//                        ],
//                    ];
//                    $tmpArr['button_list'] = $button_list;
//                    $dataList[] = array_merge($commonArr, $tmpArr);
//                }
            } elseif ($type == 2) {//2-工序管理员工单
                if ($status == 1) {//待接单
                    $tmpArr = [
                        'idName' => 1,
                        '$itemFunctionName' => 'productdirectorWaitCellFunction' . $orderRow['relate_id'],
                        '$orderSureName' => 'productdirectorOrderSure' . $orderRow['relate_id'],
                        '$orderSureBgImage' => 'orderCellStatusButton.png',
                        '$statusCode' => $status,//新增订单状态值
                        '$orderStatus' => '待接单',
                        '$statusColor' => 'FFB401',
                    ];
                    $tmpArr['button_list'] = [];
//                    $button_list = [
//                        [
//                            'id' => $orderRow['relate_id'],
//                            'title' => '删除',
//                            'order_category' => 2,//1-主工单 2-工序工单 3-工序工单（纯查看） 4-员工工单
//                            'type' => 'delete',
//                        ],
//                    ];
//                    $tmpArr['button_list'] = $button_list;
                    $dataList[] = array_merge($commonArr, $tmpArr);
                } elseif ($status == 2) {//生产中-未分配工单
                    $tmpArr = [
                        'idName' => 3,
                        '$itemFunctionName' => 'productdirectorWaitCellFunction' . $orderRow['relate_id'],
                        '$statusCode' => $status,//新增订单状态值
                        '$orderStatus' => '待分配',
                        '$statusColor' => 'FFB401',//待接单,待分派,待开工
                    ];
                    $tmpArr['button_list'] = [];
//                    $button_list = [
//                        [
//                            'id' => $orderRow['relate_id'],
//                            'title' => '撤回',
//                            'order_category' => 2,//1-主工单 2-工序工单 3-工序工单（纯查看） 4-员工工单
//                            'type' => 'recall',
//                        ],
//                    ];
//                    $tmpArr['button_list'] = $button_list;
                    $dataList[] = array_merge($commonArr, $tmpArr);
                } elseif ($status == 101) {//待开工
                    $tmpArr = [
                        'idName' => 3,
                        '$itemFunctionName' => 'productdirectorproductIngCellFunction' . $orderRow['relate_id'],
                        '$statusCode' => $status,//新增订单状态值
                        '$orderStatus' => '待开工',
                        '$statusColor' => 'FFB401',//待接单,待分派,待开工
                    ];
//                    $button_list = [
//                        [
//                            'id' => $orderRow['relate_id'],
//                            'title' => '撤回',
//                            'order_category' => 2,//1-主工单 2-工序工单 3-工序工单（纯查看） 4-员工工单
//                            'type' => 'recall',
//                        ],
//                    ];
//                    $tmpArr['button_list'] = $button_list;
                    $tmpArr['button_list'] = [];
                    $dataList[] = array_merge($commonArr, $tmpArr);
                } elseif ($status == 3) {//生产中-已分配工单
//                    $tmpArr = [
//                        'idName' => 3,
//                        '$itemFunctionName' => 'productdirectorproductIngCellFunction' . $orderRow['relate_id'],
//                        '$statusCode' => $status,//新增订单状态值
//                        '$orderStatus' => '生产中',
//                        '$statusColor' => 'FE7E57',//生产中
//                    ];
//                    $button_list = [
////                        [
////                            'id' => $orderRow['relate_id'],
////                            'title' => '终止',
////                            'order_category' => 2,//1-主工单 2-工序工单 3-工序工单（纯查看） 4-员工工单
////                            'type' => 'stop',
////                        ],
//                        [
//                            'id' => $orderRow['relate_id'],
//                            'title' => '暂停',
//                            'order_category' => 2,//1-主工单 2-工序工单 3-工序工单（纯查看） 4-员工工单
//                            'type' => 'newPause',
//                        ],
//                    ];
//                    $tmpArr['button_list'] = $button_list;

                    if($orderRow['halt_uid']){
                        $tmpArr = [
                            'idName' => 3,
                            '$itemFunctionName' => 'productdirectorproductIngCellFunction' . $orderRow['relate_id'],
                            '$statusCode' => $status,//新增订单状态值
                            '$orderStatus' => '已暂停',
                            '$statusColor' => 'FE7E57',
                        ];
                        $tmpArr['button_list'] = [];
                        if($userId == $orderRow['halt_uid']){
                            $tmpArr['button_list'] = [
                                [
                                    'id' => $orderRow['relate_id'],
                                    'title' => '开始',
                                    'order_category' => 4,//1-主工单 2-工序工单 3-工序工单（纯查看） 4-员工工单
                                    'type' => 'newStrat',
                                ]
                            ];
                        }
                    }else{
                        $tmpArr = [
                            'idName' => 3,
                            '$itemFunctionName' => 'productdirectorproductIngCellFunction' . $orderRow['relate_id'],
                            '$statusCode' => $status,//新增订单状态值
                            '$orderStatus' => '生产中',
                            '$statusColor' => 'FE7E57',//生产中
                        ];
                        $tmpArr['button_list'] = [
                            [
                                'id' => $orderRow['relate_id'],
                                'title' => '暂停',
                                'order_category' => 4,//1-主工单 2-工序工单 3-工序工单（纯查看） 4-员工工单
                                'type' => 'newPause',
                            ]
                        ];
                    }
                    $dataList[] = array_merge($commonArr, $tmpArr);
                } elseif ($status == 4) {//已完工
                    $tmpArr = [
                        'idName' => 4,
                        '$itemFunctionName' => 'productdirectorDoneCellFunction' . $orderRow['relate_id'],
                        '$orderSureName' => 'orderSure' . $orderRow['relate_id'],
                        '$statusCode' => $status,//新增订单状态值
                        '$orderStatus' => '已完成',
                        '$statusColor' => '04C9B3',//已完成
                    ];
//                    $tmpArr['button_list'] = [];
                    $button_list = [
                        [
                            'id' => $orderRow['relate_id'],
                            'title' => '删除',
                            'order_category' => 2,//1-主工单 2-工序工单 3-工序工单（纯查看） 4-员工工单
                            'type' => 'delete',
                        ],
                    ];
                    $tmpArr['button_list'] = $button_list;
                    $dataList[] = array_merge($commonArr, $tmpArr);
                }elseif ($status == 21) {//终止
                    $tmpArr = [
                        'idName' => 4,
                        '$itemFunctionName' => 'productdirectorDoneCellFunction' . $orderRow['relate_id'],
                        '$orderSureName' => 'orderSure' . $orderRow['relate_id'],
                        '$statusCode' => $status,//新增订单状态值
                        '$orderStatus' => '已终止',
                        '$statusColor' => 'FE7E57',
                    ];
                    $tmpArr['button_list'] = [];
//                    $button_list = [
//                        [
//                            'id' => $orderRow['relate_id'],
//                            'title' => '删除',
//                            'order_category' => 2,//1-主工单 2-工序工单 3-工序工单（纯查看） 4-员工工单
//                            'type' => 'delete',
//                        ],
//                    ];
//                    $tmpArr['button_list'] = $button_list;
                    $dataList[] = array_merge($commonArr, $tmpArr);
                }
                //hjn 2019.8.1
//                elseif ($status == 22) {//暂停
//                    $tmpArr = [
//                        'idName' => 4,
//                        '$itemFunctionName' => 'productdirectorDoneCellFunction' . $orderRow['relate_id'],
//                        '$statusCode' => $status,//新增订单状态值
//                        '$orderStatus' => '已暂停',
//                        '$statusColor' => 'FE7E57',
//                    ];
//                    $button_list = [
//                        [
//                            'id' => $orderRow['relate_id'],
//                            'title' => '开始',
//                            'order_category' => 2,//1-主工单 2-工序工单 3-工序工单（纯查看） 4-员工工单
//                            'type' => 'newStrat',
//                        ],
//                    ];
//                    $tmpArr['button_list'] = $button_list;
//                    $dataList[] = array_merge($commonArr, $tmpArr);
//                }
            } elseif ($type == 3) {//3-查看权限用户的工序工单
                $orderStatusArr = [
                    '1' => '待接单',
                    '2' => '待分派',
                    '3' => '生产中',
                    '4' => '已完成',
                    '101' => '待开工',
                    '21' => '已终止',
                    '22' => '已暂停',
                ];
                $orderStatusColorArr = [
                    '1' => 'FFB401',
                    '2' => 'FFB401',
                    '3' => 'FE7E57',
                    '4' => '04C9B3',
                    '101' => 'FFB401',
                    '21' => 'FE7E57',
                    '22' => 'FE7E57',
                ];

                if (!isset($orderStatusArr[$status])) {//异常处理
                    $orderStatusArr[$status] = '异常状态';
                    $orderStatusColorArr[$status] = 'FFB401';
                }

                $tmpArr = [
                    'idName' => 4,
                    '$itemFunctionName' => 'productdirectorDoneCellFunction' . $orderRow['relate_id'],
                    '$orderSureName' => 'orderSure' . $orderRow['relate_id'],
                    '$statusCode' => $status,//新增订单状态值
                    '$orderStatus' => $orderStatusArr[$status],
                    '$statusColor' => $orderStatusColorArr[$status],//待接单,待分派,待开工
                ];
                $button_list = [
                    [
                        'id' => $orderRow['relate_id'],
                        'title' => '删除',
                        'order_category' => 3,//1-主工单 2-工序工单 3-工序工单（纯查看） 4-员工工单
                        'type' => 'delete',
                    ],
                ];
                $tmpArr['button_list'] = $button_list;
                $dataList[] = array_merge($commonArr, $tmpArr);
            } elseif ($type == 4) {//4-员工工单

                if ($status == 1) {//待接单
                    $tmpArr = [
                        'idName' => 1,
                        '$itemFunctionName' => 'waitCellFunction' . $orderRow['relate_id'],
                        '$orderSureName' => 'orderSure' . $orderRow['relate_id'],
                        '$orderSureBgImage' => 'draftsOrderCellStatusButton.png',
                        '$statusCode' => $status,//新增订单状态值
                        '$orderStatus' => '待开工',
                        '$statusColor' => 'FFB401',//待接单,待分派,待开工
                    ];
//                    $button_list = [
//                        [
//                            'id' => $orderRow['relate_id'],
//                            'title' => '删除',
//                            'order_category' => 4,//1-主工单 2-工序工单 3-工序工单（纯查看） 4-员工工单
//                            'type' => 'delete',
//                        ],
//                    ];
//                    $tmpArr['button_list'] = $button_list;
                    $tmpArr['button_list'] = [];
                    $dataList[] = array_merge($commonArr, $tmpArr);
                } elseif ($status == 2) {//生产中-未领取材料
//                    $tmpArr = [
//                        'idName' => 3,
//                        '$itemFunctionName' => 'productIngNoneGetFunction' . $orderRow['relate_id'],
//                        '$statusCode' => $status,//新增订单状态值
//                        '$orderStatus' => '待领料',
//                        '$statusColor' => 'FFB401',//待接单,待分派,待开工
//                    ];
//                    $button_list = [
////                        [
////                            'id' => $orderRow['relate_id'],
////                            'title' => '终止',
////                            'order_category' => 4,//1-主工单 2-工序工单 3-工序工单（纯查看） 4-员工工单
////                            'type' => 'stop',
////                        ],
//                        [
//                            'id' => $orderRow['relate_id'],
//                            'title' => '暂停',
//                            'order_category' => 4,//1-主工单 2-工序工单 3-工序工单（纯查看） 4-员工工单
//                            'type' => 'newPause',
//                        ],
//                    ];
//                    $tmpArr['button_list'] = $button_list;


                    if($orderRow['halt_uid']){
                        $tmpArr = [
                            'idName' => 3,
                            '$itemFunctionName' => 'productIngNoneDoneFunction' . $orderRow['relate_id'],
                            '$statusCode' => $status,//新增订单状态值
                            '$orderStatus' => '已暂停',
                            '$statusColor' => 'FE7E57',
                        ];
                        $tmpArr['button_list'] = [];
                        if($userId == $orderRow['halt_uid']){
                            $tmpArr['button_list'] = [
                                [
                                    'id' => $orderRow['relate_id'],
                                    'title' => '开始',
                                    'order_category' => 4,//1-主工单 2-工序工单 3-工序工单（纯查看） 4-员工工单
                                    'type' => 'newStrat',
                                ]
                            ];
                        }

                    }else{
                        $tmpArr = [
                            'idName' => 3,
                            '$itemFunctionName' => 'productIngNoneGetFunction' . $orderRow['relate_id'],
                            '$statusCode' => $status,//新增订单状态值
                            '$orderStatus' => '待领料',
                            '$statusColor' => 'FFB401',//待接单,待分派,待开工
                        ];
                        $tmpArr['button_list'] = [
                            [
                                'id' => $orderRow['relate_id'],
                                'title' => '暂停',
                                'order_category' => 4,//1-主工单 2-工序工单 3-工序工单（纯查看） 4-员工工单
                                'type' => 'newPause',
                            ]
                        ];
                    }

                    $dataList[] = array_merge($commonArr, $tmpArr);
                } elseif ($status == 3) {//生产中-已领取材料
//                    $tmpArr = [
//                        'idName' => 3,
//                        '$itemFunctionName' => 'productIngNoneDoneFunction' . $orderRow['relate_id'],
//                        '$statusCode' => $status,//新增订单状态值
//                        '$orderStatus' => '生产中',
//                        '$statusColor' => 'FE7E57',//生产中
//                    ];

                    //hjn 20190801
//                    $button_list = [
////                        [
////                            'id' => $orderRow['relate_id'],
////                            'title' => '终止',
////                            'order_category' => 4,//1-主工单 2-工序工单 3-工序工单（纯查看） 4-员工工单
////                            'type' => 'stop',
////                        ],
//                        [
//                            'id' => $orderRow['relate_id'],
//                            'title' => '暂停',
//                            'order_category' => 4,//1-主工单 2-工序工单 3-工序工单（纯查看） 4-员工工单
//                            'type' => 'newPause',
//                        ],
//                    ];

                    if($orderRow['halt_uid']){
                        $tmpArr = [
                            'idName' => 3,
                            '$itemFunctionName' => 'productIngNoneDoneFunction' . $orderRow['relate_id'],
                            '$statusCode' => $status,//新增订单状态值
                            '$orderStatus' => '已暂停',
                            '$statusColor' => 'FE7E57',
                        ];
                        $tmpArr['button_list'] = [];
                        if($userId == $orderRow['halt_uid']){
                            $tmpArr['button_list'] = [
                                [
                                    'id' => $orderRow['relate_id'],
                                    'title' => '开始',
                                    'order_category' => 4,//1-主工单 2-工序工单 3-工序工单（纯查看） 4-员工工单
                                    'type' => 'newStrat',
                                ]
                            ];
                        }

                    }else{
                        $tmpArr = [
                            'idName' => 3,
                            '$itemFunctionName' => 'productIngNoneDoneFunction' . $orderRow['relate_id'],
                            '$statusCode' => $status,//新增订单状态值
                            '$orderStatus' => '生产中',
                            '$statusColor' => 'FE7E57',//生产中
                        ];
                        $tmpArr['button_list'] = [
                            [
                                'id' => $orderRow['relate_id'],
                                'title' => '暂停',
                                'order_category' => 4,//1-主工单 2-工序工单 3-工序工单（纯查看） 4-员工工单
                                'type' => 'newPause',
                            ]
                        ];
                    }
                    $dataList[] = array_merge($commonArr, $tmpArr);
                } elseif ($status == 4) {//已完工
                    $tmpArr = [
                        'idName' => 4,
                        '$itemFunctionName' => 'doneCellFunction' . $orderRow['relate_id'],
                        '$orderSureName' => 'orderSure' . $orderRow['relate_id'],
                        '$statusCode' => $status,//新增订单状态值
                        '$orderStatus' => '已完成',
                        '$statusColor' => '04C9B3',//已完成
                    ];
                    $button_list = [
                        [
                            'id' => $orderRow['relate_id'],
                            'title' => '删除',
                            'order_category' => 4,//1-主工单 2-工序工单 3-工序工单（纯查看） 4-员工工单
                            'type' => 'delete',
                        ],
                    ];
                    $tmpArr['button_list'] = $button_list;
                    $dataList[] = array_merge($commonArr, $tmpArr);
                }elseif ($status == 21) {//终止
                    $tmpArr = [
                        'idName' => 3,
                        '$itemFunctionName' => 'productIngNoneDoneFunction' . $orderRow['relate_id'],
                        '$orderSureName' => 'orderSure' . $orderRow['relate_id'],
                        '$statusCode' => $status,//新增订单状态值
                        '$orderStatus' => '已终止',
                        '$statusColor' => 'FE7E57',
                    ];
//                    $button_list = [
//                        [
//                            'id' => $orderRow['relate_id'],
//                            'title' => '删除',
//                            'order_category' => 4,//1-主工单 2-工序工单 3-工序工单（纯查看） 4-员工工单
//                            'type' => 'delete',
//                        ],
//                    ];
//                    $tmpArr['button_list'] = $button_list;
                    $tmpArr['button_list'] = [];
                    $dataList[] = array_merge($commonArr, $tmpArr);
                }
//                elseif ($status == 22) {//暂停
//                    $tmpArr = [
//                        'idName' => 3,
//                        '$itemFunctionName' => 'productIngNoneDoneFunction' . $orderRow['relate_id'],
//                        '$statusCode' => $status,//新增订单状态值
//                        '$orderStatus' => '已暂停',
//                        '$statusColor' => 'FE7E57',
//                    ];
//                    $button_list = [
//                        [
//                            'id' => $orderRow['relate_id'],
//                            'title' => '开始',
//                            'order_category' => 4,//1-主工单 2-工序工单 3-工序工单（纯查看） 4-员工工单
//                            'type' => 'newStrat',
//                        ],
//                    ];
//                    $tmpArr['button_list'] = $button_list;
//                    $dataList[] = array_merge($commonArr, $tmpArr);
//                }
            }
        }

        //如果是客户账号，不进入工单详情
        if($userInfo['privilege_id'] == 110){
            foreach ($dataList as $tmpKey => $tmpRow){
                $dataList[$tmpKey]['$itemFunctionName'] = '';
                $dataList[$tmpKey]['is_distribute'] = 0;
            }
        }

        //如果没有下单人权限，不能进行派发和再来一单 zhuyujun 20190603
        $companyId = $userInfo['company_id'];
        $privilegeIdList = \App\Engine\OrderEngine::getPrivilegeByNode($companyId, 1);
        if (!in_array($userInfo['privilege_id'], $privilegeIdList)) {
            foreach ($dataList as $tmpKey => $tmpRow){
                $dataList[$tmpKey]['is_distribute'] = 0;
                $dataList[$tmpKey]['is_single_person'] = 0;
            }
        }

        /*功能块：如果不是下单人，不显示按钮 zhuyujun 20190709*/
        /*2019-08-01 发现一个bug,如果销售没有下单人权限，又开了我的工单模块，这块会报错，这里暂时做处理 */
        if(!isset($type) || (!$is_single_person && !in_array($type,array(2,4))) ){
            foreach ($dataList as $tmpKey => $tmpRow){
                $dataList[$tmpKey]['button_list'] = [];
            }
        }

        $returnOrderList['pageID'] = 0;
        $returnOrderList['data']['$tableviewSource'] = $dataList;

        return $returnOrderList;

    }

    //获取工单对应的打包件数（在下级工序工单中）
    static public function getOrderPack($orderId)
    {
        $tmpOrderProcessList = \App\Eloquent\Zk\OrderProcess::where(['order_id' => $orderId])->get();
        foreach ($tmpOrderProcessList as $tmpOrderProcessRow) {
            if ($tmpOrderProcessRow['film_thickness']) {
                //获取对应的值
                $tmpArr = explode(',', $tmpOrderProcessRow['film_thickness']);
                if (isset($tmpArr[0]) && isset($tmpArr[1])) {
                    if ($tmpArr[0] && $tmpArr[1]) {
                        return $tmpArr[0] . $tmpArr[1];
                    }
                }
            }
        }

        return '';
    }

    public static function addSearchKeyword($orderId)
    {

        $order = CustomerOrder::find($orderId);
        if (!$order) return $order;

        // 品名
        $productName = $order->product_name;
        $productName = \App\Engine\OrderEngine::getOrderFiledValueTrue($productName, 20);
        // 工艺类型
        $orderType = $order->order_type;
        $orderTypeName = OrderType::getOneValueById($orderType, 'title');
        // 单位
        $company = $order->field_name_23;
        $companyName = \App\Engine\Buyers::getNameById($company);
        if (!$companyName) $companyName = $company;
        // 单号
        $order_title = $order->order_title;
        // 成品规格

        $finishedSpecification = implode('x', explode(',', $order->finished_specification));
        // 数量
        $number = implode('', explode(',', $order->product_num));
        // 交货日期
        $date = $order->finished_date;

        // 搜索数据拼接
        $searchKeyword = $productName;
        $searchKeyword .= ',' . $orderTypeName;
        $searchKeyword .= ',' . $companyName;
        $searchKeyword .= ',' . $order_title;
        $searchKeyword .= ',' . $finishedSpecification;
        $searchKeyword .= ',' . $number;
        $searchKeyword .= ',' . $date;

        //hjn 20190814 增加客户名称搜索、产品片料规格
        $customerName = \App\Eloquent\Zk\Customer::where(['id'=>$order->customer_name])->value('customer_name');
        $searchKeyword .= ',' . $customerName;
        $searchKeyword .= ',' . implode('x', explode(',', $order->chip_specification_length));

//        $order->update(['search_keyword' => $searchKeyword,'updated_at'=>$order->updated_at]);
        \DB::table('ygt_customer_order')->where(['id'=>$order['id']])->update(['search_keyword' => $searchKeyword]);

        return $order;
    }

    //获取工艺基础信息中指定类型的字段
    //config('process.process_field_type_list')中的配置信息
    static public function getOrderBaseFieldByType($companyId,$type){

        $where = [];
        $where['company_id'] = $companyId;
        $where['field_type'] = $type;
        $order_field_company_list = \App\Eloquent\Zk\OrderFieldCompany::where($where)->get();

        return $order_field_company_list;
    }

    //获取工艺工序信息中指定类型的字段
    //config('process.process_field_type_list')中的配置信息
    /*['id'=>1, 'title'=>'文本'],['id'=>2, 'title'=>'文本域'],
        ['id'=>3, 'title'=>'单选'],['id'=>4, 'title'=>'材料库'],
        ['id'=>5, 'title'=>'前填空后单选'],['id'=>6, 'title'=>'开关'],
        ['id'=>7, 'title'=>'日期'], ['id'=>8, 'title'=>'地址'],
        ['id'=>9, 'title'=>'上传图片'], ['id'=>10, 'title'=>'二维码'],
        ['id'=>11, 'title'=>'联动双选'], ['id'=>13, 'title'=>'多选'],
        ['id'=>14, 'title'=>'非联动双选'],['id'=>15, 'title'=>'宽*长'],
        ['id'=>16, 'title'=>'宽*长*M边'],['id'=>17, 'title'=>'版库'],
        ['id'=>18, 'title'=>'客户库'],['id'=>19, 'title'=>'单位选择'],
        ['id'=>20, 'title'=>'品名选择'],['id'=>21, 'title'=>'开票资料'],
        ['id'=>22, 'title'=>'半成品']*/
    static public function getOrderProcessFieldByType($companyId,$type){

        $where = [];
        $where['company_id'] = $companyId;
        $where['field_type'] = $type;
        $process_field_company_list = \App\Eloquent\Zk\ProcessFieldCompany::where($where)->get();

        return $process_field_company_list;
    }

    //获取某个员工工单的实际完成数量，针对是提交半成品的情况，取所有半成品中，提交数量最小的值（包括0）
    static public function getCurOrderProcessCourseFinishNum($ordertype_process_id,$order_process_course_id){
        $real_order_process_course_finsih_number = 0;

        $tmp_process_product_submit_list = [];//相关的所有半成品及已提交数量
        //获取工艺工序对应的半成品列表

        //逻辑调整，用新的逻辑获取工序对应的待提交半成品列表
//        $tmp_process_product_list = \App\Eloquent\Ygt\ProcessProduct::where(['ordertype_process_id'=>$ordertype_process_id])->get();
        $order_process_course_row = \App\Eloquent\Zk\OrderProcessCourse::find($order_process_course_id);
        $order_process_id = $order_process_course_row->order_process_id;
        $order_process_row = \App\Eloquent\Zk\OrderProcess::find($order_process_id);
        $order_id = $order_process_row->order_id;
        $order_row = \App\Eloquent\Zk\Order::find($order_id);


        $process_product_list = \App\Engine\ProcessProduct::getOrderProcessProcessProductList($order_row,$order_process_row);
        $tmp_process_product_list = $process_product_list;


        foreach ($tmp_process_product_list as $tmp_process_product_row){
            $tmp_process_product_submit_list[$tmp_process_product_row['id']] = [
                'process_product_id' => $tmp_process_product_row['id'],
                'submit_number' => 0
            ];
        }

        //获取已提交的半成品列表
        $tmp_process_product_submit_log_list = \App\Eloquent\Zk\ProcessProductSubmitLog::where(['order_process_course_id'=>$order_process_course_id])->get();
        foreach ($tmp_process_product_submit_log_list as $tmp_process_product_submit_log_row){
            if(isset($tmp_process_product_submit_list[$tmp_process_product_submit_log_row['process_product_id']])){
                $tmp_process_product_submit_list[$tmp_process_product_submit_log_row['process_product_id']]['submit_number'] += $tmp_process_product_submit_log_row['number'];
            }
        }


        $tmp_min_number = false;//记录最小值
        foreach ($tmp_process_product_submit_list as $tmp_process_product_submit_row){
            if(!$tmp_process_product_submit_row['submit_number']){
                //如果
                $tmp_min_number = 0;
                break;
            }else{
                if($tmp_min_number === false){
                    $tmp_min_number = $tmp_process_product_submit_row['submit_number'];
                }else{
                    if($tmp_min_number > $tmp_process_product_submit_row['submit_number'] ){
                        $tmp_min_number = $tmp_process_product_submit_row['submit_number'];
                    }
                }
            }
        }


        if($tmp_min_number){
            $real_order_process_course_finsih_number = $tmp_min_number;
        }


        return $real_order_process_course_finsih_number;
    }


    //获取上道工序信息
    static public function getPreOrderProcessList($order_id,$order_type_id,$process_id){
        $pre_order_process_list = [];
        $pre_process_id_list =  \App\Engine\OrderType::getAllPrevOrderProcess($order_type_id, $process_id)->toArray();
        //上个工序的名称、预计完成时间、半成品相关信息（含半成品来源）
        foreach ($pre_process_id_list as $pre_process_id){

            //获取工序信息
            $tmp_order_process_row = \App\Eloquent\Zk\OrderProcess::where(['order_id' => $order_id, 'process_type' => $pre_process_id])->first();

            if($tmp_order_process_row){
                $tmp_order_process_row = $tmp_order_process_row->toArray();
            }else{
                //异常过滤
                continue;
            }

            $pre_order_process_id = $tmp_order_process_row['id'];

            //获取工序数量是否需要叠加&工序名称
            $where = ['id' => $pre_process_id];
            $process = \App\Eloquent\Zk\Process::getInfo($where)->toArray();
            $pre_process_title = $process['title'];//工序名称

            if ($tmp_order_process_row['status'] == 1) {//待开工
                $pre_process_estimated_time = 0;
                $pre_process_estimated_time_str = '待开工';
                $tmp_process_product_deal_list = [];

            } else {

                //预计完成时间调整为从员工工单获取
                $orderProcessCourseList = \App\Eloquent\Zk\OrderProcessCourse::where(['order_process_id' => $tmp_order_process_row['id']])->get();
                $pre_process_estimated_time = 0;

                foreach ($orderProcessCourseList as $orderProcessCourseRow) {
                    $hour = $orderProcessCourseRow['estimated_time_hour']?$orderProcessCourseRow['estimated_time_hour']:0;
                    $minute = $orderProcessCourseRow['estimated_time_minute']?$orderProcessCourseRow['estimated_time_minute']:0;
                    $tmpPreProcessEstimatedTime = $orderProcessCourseRow['start_time'] + $hour * 3600 + $minute * 60;
//                    $tmpPreProcessEstimatedTime = $orderProcessCourseRow['start_time'] + $orderProcessCourseRow['estimated_time_hour'] * 3600 + $orderProcessCourseRow['estimated_time_minute'] * 60;
                    if ($tmpPreProcessEstimatedTime > $pre_process_estimated_time) {
                        $pre_process_estimated_time = $tmpPreProcessEstimatedTime;
                        $pre_process_estimated_time_str = date('Y年m月d日H时i分', $pre_process_estimated_time);
                    }

                    //过滤异常数据
                    if (!DepartmentUser::getCurrentInfo($orderProcessCourseRow['uid'])) {
                        continue;
                    }
                }


                if ($pre_process_estimated_time < 631152000) {//过滤1970的情况
                    $pre_process_estimated_time_str = '';
                }


                /*功能块：增加半成品情况*/
                //下单人选择的半成品
                //获取工序工单半成品的字段
//                $tmp_process_product_pick = [];
                $tmp_process_product_deal_list = [];//上道工序半成品
                $tmp_company_id = $tmp_order_process_row['company_id'];
                $tmp_process_product_filed_list = \App\Engine\OrderEngine::getOrderProcessFieldByType($tmp_company_id,22);
                foreach ($tmp_process_product_filed_list as $tmp_row){
                    if($tmp_row){
                        $tmpCreateOrderProcessProductRow = \App\Eloquent\Zk\CreateOrderProcessProduct::where(['id'=>$tmp_order_process_row[$tmp_row['field_name']]])->first();
                        $processProductInfo = json_decode(htmlspecialchars_decode($tmpCreateOrderProcessProductRow['content']), true);

                        if(!empty($processProductInfo)){
                            //获取半成品的其他数据
                            foreach ($processProductInfo as $tmpKey => $tmpProcessProductInfoRow){
                                if(!isset($tmp_process_product_deal_list[$tmpProcessProductInfoRow['process_product_id']])){
                                    $tmp_process_product_deal_list[$tmpProcessProductInfoRow['process_product_id']] = [
                                        'process_product_id' => $tmpProcessProductInfoRow['process_product_id'],
                                        'pick_number' => 0,
                                        'submit_number' => 0,
                                        'all_number' => 0,
                                    ];
                                }

                                //统计半成品总和
//                                $tmp_process_product_pick[$tmpProcessProductInfoRow['process_product_id']]['number'] += $tmpProcessProductInfoRow['number'];
                                $tmp_process_product_deal_list[$tmpProcessProductInfoRow['process_product_id']]['pick_number'] += $tmpProcessProductInfoRow['number'];
                                $tmp_process_product_deal_list[$tmpProcessProductInfoRow['process_product_id']]['all_number'] += $tmpProcessProductInfoRow['number'];
                            }
                        }
                    }
                }
//p($tmp_order_process_row);
                //员工提交的半成品记录
//                $tmp_process_product_submit = [];
                $where = [];
                $where['order_process_id'] = $tmp_order_process_row['id'];
                $tmp_process_product_submit_log_list = \App\Eloquent\Zk\ProcessProductSubmitLog::where($where)->get();
//                p($tmp_process_product_submit_log_list->toArray());
                foreach ($tmp_process_product_submit_log_list as $tmp_row){
                    if(!isset($tmp_process_product_deal_list[$tmp_row['process_product_id']])){
                        $tmp_process_product_deal_list[$tmp_row['process_product_id']] = [
                            'process_product_id' => $tmp_row['process_product_id'],
                            'pick_number' => 0,
                            'submit_number' => 0,
                            'all_number' => 0,
                        ];
                    }

                    //统计半成品总和
//                    $tmp_process_product_deal_list[$tmp_row['process_product_id']]['number'] += $tmp_row['number'];
                    $tmp_process_product_deal_list[$tmp_row['process_product_id']]['submit_number'] += $tmp_row['number'];
                    $tmp_process_product_deal_list[$tmp_row['process_product_id']]['all_number'] += $tmp_row['number'];
                }


//                //两种半成品数量统计
//                foreach ($tmp_process_product_pick as $tmp_process_product_pick_row){
//                    $tmp_process_product_deal_list[] = [
//                        'process_product_id' => $tmp_process_product_pick_row['process_product_id'],
//                        'number' => $tmp_process_product_pick_row['number'],
//                        'type' => '1',//下单选择的
//                    ];
//                }
//                foreach ($tmp_process_product_submit as $tmp_process_product_submit_row){
//                    $tmp_process_product_deal_list[] = [
//                        'process_product_id' => $tmp_process_product_submit_row['process_product_id'],
//                        'number' => $tmp_process_product_submit_row['number'],
//                        'type' => '2',//员工提交的
//                    ];
//                }
//                p($tmp_process_product_deal_list);
                //获取半成品的具体信息
                $tmp_process_product_deal_list = array_values($tmp_process_product_deal_list);//去处下标

                foreach ($tmp_process_product_deal_list as $tmp_key => $tmp_row){
                    $tmp_process_product_deal = \App\Engine\ProcessProduct::getProcessProductInfoByID($tmp_row['process_product_id']);
//                    p($tmp_process_product_deal);
                    $tmp_process_product_deal_list[$tmp_key] = array_merge($tmp_row,$tmp_process_product_deal);
                }
            }


            $pre_order_process_list[] = [
                'pre_process_title' => $pre_process_title,
                'pre_process_estimated_time' => $pre_process_estimated_time,
                'pre_process_estimated_time_str' => $pre_process_estimated_time_str,
                'pre_order_process_id' => $pre_order_process_id,
                'process_product_list' => $tmp_process_product_deal_list,
            ];
        }


        return $pre_order_process_list;
    }

    //获取工序工单的主要用材
    static public function getOrderProcessMainMaterial($tmp_order_process_row){

        $main_material_list = [];//存放主材料的ID和名称
        $ordertype_process_id = $tmp_order_process_row['ordertype_process_id'];

        //配置的主要用材
        $where = [];
        $where['process_ordertype_id'] = $ordertype_process_id;
        $tmp_ordertype_process_main_material_row = \App\Eloquent\Zk\OrdertypeProcessMainMaterial::where($where)->first();
        if($tmp_ordertype_process_main_material_row) {
            $tmp_field_id_list_str = $tmp_ordertype_process_main_material_row['main_material'];
            if ($tmp_field_id_list_str) {
                $tmp_field_id_list = explode(',', $tmp_field_id_list_str);
                $where = [];
                $where['company_id'] = $tmp_order_process_row['company_id'];
                $tmp_field_list = \App\Eloquent\Zk\ProcessFieldCompany::where($where)->whereIn('field_id', $tmp_field_id_list)->get();
                foreach ($tmp_field_list as $tmp_field_row) {
                    if (isset($tmp_order_process_row[$tmp_field_row['field_name']]) && $tmp_order_process_row[$tmp_field_row['field_name']]) {
                        //获取材料对应的信息
                        $material_id_list = explode(',', $tmp_order_process_row[$tmp_field_row['field_name']]);

                        $materialList = [];
                        foreach ($material_id_list as $materialId) {
                            //考虑集合材料的问题
                            if (strstr($materialId, 'A')) {
                                $tmp_assemblage_material_id = str_replace('A', '', $materialId);
                                $material_row = \App\Eloquent\Zk\AssemblageMaterial::withTrashed()->where(['id' => $tmp_assemblage_material_id])->first();
                                if ($material_row) {
                                    $main_material_list[] = [
                                        'id' => 'A'.$material_row['id'],
                                        'title' => $material_row['product_name'],
                                    ];
                                }
                            } else {
                                $material_row = \App\Engine\Product::getProductInfo($materialId);
                                if ($material_row) {
                                    $main_material_list[] = [
                                        'id' => $material_row['id'],
                                        'title' => $material_row['product_name'],
                                    ];
                                }
                            }
                        }
                    }
                }
            }
        }

        return $main_material_list;
    }

    //工单撤回，相关待采购处理 zhuyujun 20190624
    static public function OrderRecallWaitPurchaseDeal($order_id){

        $relate_material_id_list = [];
        $wait_purchase_row = \App\Eloquent\Zk\WaitPurchase::where(['order_id'=>$order_id])->first();
        if($wait_purchase_row){
            $wait_purchase_id = $wait_purchase_row->id;
            $wait_purchase_material_list = \App\Eloquent\Zk\WaitPurchaseMaterial::where(['wait_purchase_id'=>$wait_purchase_id])->get();
            foreach ($wait_purchase_material_list as $wait_purchase_material_row){
                $relate_material_id_list [] = $wait_purchase_material_row['material_id'];
                $waite_purchase_aggregate_id = $wait_purchase_material_row['waite_purchase_aggregate_id'];
                $waite_purchase_aggregate_row = \App\Eloquent\Zk\WaitePurchaseAggregate::find($waite_purchase_aggregate_id);
                if($waite_purchase_aggregate_row){
                    /*（1）删除对应的待采购数量*/
                    $waite_purchase_aggregate_row->all_number -= $wait_purchase_material_row['number'];
                    $waite_purchase_aggregate_row->now_number -= $wait_purchase_material_row['number'];
                    $waite_purchase_aggregate_row->order_number -= 1;
                    if($waite_purchase_aggregate_row->all_number < 0){
                        $waite_purchase_aggregate_row->all_number = 0;
                    }
                    if($waite_purchase_aggregate_row->now_number < 0){
                        $waite_purchase_aggregate_row->now_number = 0;
                    }
                    if($waite_purchase_aggregate_row->order_number < 0){
                        $waite_purchase_aggregate_row->order_number = 0;
                    }

                    $waite_purchase_aggregate_row->save();
                }

                /*（2）删除待采购表数据*/
                $wait_purchase_material_row->delete();
            }
            /*（3）删除对应的关联工单ID*/
            $wait_purchase_row->delete();
        }

        /*（4）跟关联的采购发送消息*/
        $relate_purchase_user_id_list = [];
        if(!empty($relate_material_id_list)){
            $material_category_id_list = [];
            foreach ($relate_material_id_list as $relate_material_id){
                $tmp_material_row = \App\Engine\Product::getProductInfo($relate_material_id);
                if($tmp_material_row){
                    $material_category_id_list[] = $tmp_material_row['category_id'];
                }
            }

            //获取需要发消息的用户ID列表
            $company_id = $wait_purchase_row['company_id'];
            $privilegeList = \App\Engine\OrderEngine::getPrivilegeByNode($company_id, 10);//采购员角色列表
            foreach ($privilegeList as $privilegeId) {
                $tmpDepartmentUserList = \App\Eloquent\Zk\DepartmentUser::where(['company_id'=>$company_id,'privilege_id'=>$privilegeId])->get();
                foreach ($tmpDepartmentUserList as $tmpDepartmentUserRow){
                    //采购主管
                    if($tmpDepartmentUserRow['is_leader']){
                        $relate_purchase_user_id_list[] = $tmpDepartmentUserRow['user_id'];
                    }else{
                        $tmp_purchase_manage_row = \App\Eloquent\Zk\PurchaseManage::where(['company_id'=>$company_id,'uid'=>$tmpDepartmentUserRow['user_id']])->first();
                        if($tmp_purchase_manage_row){
                            $tmp_purchase_category_id_list = explode(',',$tmp_purchase_manage_row['category_ids']);
                            if(!empty($tmp_purchase_category_id_list)){
                                $re_arr = array_intersect($tmp_purchase_category_id_list,$material_category_id_list);
                                if(count($re_arr)){
                                    $relate_purchase_user_id_list[] = $tmpDepartmentUserRow['user_id'];
                                }
                            }
                        }
                    }
                }
            }
        }

        return $relate_purchase_user_id_list;
    }

    //2019-07-20 获取接口传过来的字段值，如果为空返回0
    public static function getValidInputField($field){
        if (isset($field)) return $field;
        else return 0;
    }



    //占位方法
    static public function tmp(){

    }







}