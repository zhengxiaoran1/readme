<?php
/**
 * Created by PhpStorm.
 * User: sojo
 * Date: 2018/1/5
 * Time: 20:29
 */

namespace App\Api\OA\Workflow\Models;

use App\Api\OA\Personnel\Models\Personnel;
//use Framework\BaseClass\Api\Model;

//class FlowConfig extends Model
class FlowConfig
{
    public function getFieldList($contactsId, $flowId, $companyId)
    {
        $fieldList = [];


        if(!$companyId){
            xThrow(ERR_PARAMETER);
        }

        switch ($flowId) {
            case 1:
                $fieldList = [
                    'process_title'      => '请假',
                    'scene'              => 'leave',
                    'process_field_list' => [
                        [
                            'title'            => '请选择请假类型',
                            'field_type'       => 16,
                            'color'            => 'FFFFFF',
                            'background_color' => 'FF0000'
                        ],
                        [
                            'field_name'  => 'type',
                            'title'       => '请假类型',
                            'field_type'  => 3,
                            'is_must'     => 1,
                            'placeholder' => '请选择',
                            'data'        => [
                                [
                                    'id'    => 1,
                                    'title' => '病假'
                                ],
                                [
                                    'id'    => 2,
                                    'title' => '事假'
                                ],
                                [
                                    'id'    => 3,
                                    'title' => '产假'
                                ],
                                [
                                    'id'    => 4,
                                    'title' => '工伤假'
                                ],
                                [
                                    'id'    => 5,
                                    'title' => '陪产假'
                                ],
                                [
                                    'id'    => 0,
                                    'title' => '其它'
                                ]
                            ]
                        ],
                        [
                            'title'            => '请填写请假时间',
                            'field_type'       => 16,
                            'color'            => 'FFFFFF',
                            'background_color' => 'FF0000'
                        ],
                        [
                            'field_name'  => 'start_time',
                            'title'       => '开始时间',
                            'placeholder' => '请选择',
                            'field_type'  => 7,
                            'text_type'   => 0,
                            'is_must'     => 1,
                        ],
                        [
                            'field_name'  => 'end_time',
                            'title'       => '结束时间',
                            'placeholder' => '请选择',
                            'field_type'  => 7,
                            'text_type'   => 0,
                            'is_must'     => 1,
                        ],
                        [
                            'title'            => '请填写请假事由',
                            'field_type'       => 16,
                            'color'            => 'FFFFFF',
                            'background_color' => 'FF0000'
                        ],
                        [
                            'field_name'  => 'reasons',
                            'title'       => '',
                            'field_type'  => 2,
                            'is_must'     => 1,
                            'placeholder' => '请输入请假事由...'
                        ],
                        [
                            'field_name'  => 'image_url',
                            'title'       => '附件上传',
                            'field_type'  => 9,
                            'is_must'     => 0,
                            'placeholder' => '请选择附件'
                        ],
                        [
                            'field_name'  => 'copy_ids',
                            'title'       => '抄送人',
                            'field_type'  => 4,
                            'is_must'     => 0,
                            'placeholder' => '请选择抄送人'
                        ]
                    ],
                    "button_title"       => "确认提交",
                ];
                break;
            case 2:
                $fieldList = [
                    'process_title'      => '用印',
                    'scene'              => 'seal',
                    'process_field_list' => [
                        [
                            'title'            => '请填写用印申请',
                            'field_type'       => 16,
                            'color'            => 'FFFFFF',
                            'background_color' => 'FF0000'
                        ],
                        [
                            'field_name'  => 'oa_department_id',
                            'title'       => '申请部门',
                            'field_type'  => 3,
                            'is_must'     => 1,
                            'placeholder' => '请选择',
                            'data'        => [
                                [
                                    'id'    => 33,
                                    'title' => '技术部'
                                ]
                            ]
                        ],
                        [
                            'field_name'  => 'operator',
                            'title'       => '经办人',
                            'field_type'  => 1,
                            'is_must'     => 1,
                            'placeholder' => '请输入经办人'
                        ],
                        [
                            'field_name'  => 'file_name',
                            'title'       => '用印文件名',
                            'field_type'  => 1,
                            'is_must'     => 1,
                            'placeholder' => '请输入文件名'
                        ],
                        [
                            'field_name'  => 'file_number',
                            'title'       => '文件份数',
                            'field_type'  => 1,
                            'is_must'     => 1,
                            'text_type'   => 1,
                            'placeholder' => '请输入文件份数'
                        ],
                        [
                            'field_name'  => 'file_type',
                            'title'       => '文件类型',
                            'field_type'  => 3,
                            'is_must'     => 1,
                            'placeholder' => '请选择',
                            'data'        => [
                                [
                                    'id'    => 1,
                                    'title' => '公告类'
                                ],
                                [
                                    'id'    => 2,
                                    'title' => '规章制度类'
                                ],
                                [
                                    'id'    => 3,
                                    'title' => '合同类'
                                ],
                                [
                                    'id'    => 0,
                                    'title' => '不明'
                                ]
                            ]
                        ],
                        [
                            'field_name'  => 'seal_type',
                            'title'       => '加盖何种印',
                            'field_type'  => 3,
                            'is_must'     => 1,
                            'placeholder' => '请选择',
                            'data'        => [
                                [
                                    'id'    => 1,
                                    'title' => '公章'
                                ],
                                [
                                    'id'    => 2,
                                    'title' => '合同章'
                                ],
                                [
                                    'id'    => 3,
                                    'title' => '法人章'
                                ],
                                [
                                    'id'    => 0,
                                    'title' => '不明'
                                ]
                            ]
                        ],
                        [
                            'field_name'  => 'note',
                            'title'       => '',
                            'field_type'  => 2,
                            'is_must'     => 0,
                            'placeholder' => '备注'
                        ],
                        [
                            'field_name'  => 'copy_ids',
                            'title'       => '抄送人',
                            'field_type'  => 4,
                            'is_must'     => 0,
                            'placeholder' => '请选择抄送人'
                        ]
                    ],
                    "button_title"       => "确认提交",
                ];
                break;
            case 3:
                $fieldList = [
                    'process_title'      => '用车',
                    'scene'              => 'car',
                    'process_field_list' => [
                        [
                            'title'            => '请填写用车申请',
                            'field_type'       => 16,
                            'color'            => 'FFFFFF',
                            'background_color' => 'FF0000'
                        ],
                        [
                            'field_name'  => 'oa_department_id',
                            'title'       => '申请部门',
                            'field_type'  => 3,
                            'is_must'     => 1,
                            'placeholder' => '请选择',
                            'data'        => [
                                [
                                    'id'    => 33,
                                    'title' => '技术部'
                                ]
                            ]
                        ],
                        [
                            'field_name'  => 'reasons',
                            'title'       => '用车事由',
                            'field_type'  => 2,
                            'is_must'     => 1,
                            'placeholder' => '请输入事由...'
                        ],
                        [
                            'field_name'  => 'start_place',
                            'title'       => '始发地点',
                            'field_type'  => 1,
                            'is_must'     => 1,
                            'placeholder' => '请输入始发地点'
                        ],
                        [
                            'field_name'  => 'return_place',
                            'title'       => '返回地点',
                            'field_type'  => 1,
                            'is_must'     => 1,
                            'placeholder' => '请输入返回地点'
                        ],
                        [
                            'field_name'  => 'start_time',
                            'title'       => '用车日期',
                            'field_type'  => 7,
                            'is_must'     => 1,
                            'text_type'   => 0,
                            'placeholder' => '请选择'
                        ],
                        [
                            'field_name'  => 'return_time',
                            'title'       => '返回日期',
                            'field_type'  => 7,
                            'is_must'     => 1,
                            'text_type'   => 0,
                            'placeholder' => '请选择'
                        ],
                        [
                            'field_name'  => 'type',
                            'title'       => '车辆类型',
                            'field_type'  => 1,
                            'is_must'     => 1,
                            'placeholder' => '请输入类型'
                        ],
                        [
                            'field_name'  => 'number',
                            'title'       => '数量（辆）',
                            'field_type'  => 1,
                            'is_must'     => 1,
                            'text_type'   => 1,
                            'placeholder' => '请输入数量'
                        ],
                        [
                            'field_name'  => 'request',
                            'title'       => '其它要求',
                            'field_type'  => 1,
                            'is_must'     => 1,
                            'placeholder' => '请输入要求'
                        ],
                        [
                            'field_name'  => 'note',
                            'title'       => '',
                            'field_type'  => 2,
                            'is_must'     => 0,
                            'placeholder' => '备注...'
                        ],
                        [
                            'field_name'  => 'copy_ids',
                            'title'       => '抄送人',
                            'field_type'  => 4,
                            'is_must'     => 0,
                            'placeholder' => '请选择抄送人'
                        ],
                    ],
                    "button_title"       => "确认提交",
                ];
                break;
            case 4:
                $contactsInfo = new Personnel();
                $contactsInfo = $contactsInfo->getEmployeeInfo($contactsId);

                $fieldList = [
                    'process_title'      => '岗位调动',
                    'scene'              => 'position',
                    'process_field_list' => [
                        [
                            'title'            => '请填写岗位调动申请',
                            'field_type'       => 16,
                            'color'            => 'FFFFFF',
                            'background_color' => 'FF0000'
                        ],
                        [
                            'title'         => '姓名',
                            'field_type'    => 1,
                            'is_must'       => 1,
                            'default_value' => $contactsInfo->name,
                            'placeholder'   => '请输入姓名'
                        ],
                        [
                            'title'         => '工号',
                            'field_type'    => 1,
                            'is_must'       => 1,
                            'default_value' => $contactsInfo->employee_number,
                            'placeholder'   => '请输入工号'
                        ],
                        [
                            'title'         => '身份证号',
                            'field_type'    => 1,
                            'is_must'       => 1,
                            'default_value' => $contactsInfo->credential_number,
                            'placeholder'   => '请输入身份证号'
                        ],
                        [
                            'title'         => '所属部门',
                            'field_type'    => 1,
                            'is_must'       => 1,
                            'default_value' => $contactsInfo->department_name,
                            'placeholder'   => '请输入当前部门'
                        ],
                        [
                            'title'         => '岗位名称',
                            'field_type'    => 1,
                            'is_must'       => 1,
                            'default_value' => $contactsInfo->position,
                            'placeholder'   => '请输入当前岗位名称'
                        ],
                        [
                            'title'         => '入职日期',
                            'field_type'    => 1,
                            'is_must'       => 1,
                            'default_value' => $contactsInfo->entry_time,
                            'placeholder'   => '请选择'
                        ],
                        [
                            'title'         => '工龄',
                            'field_type'    => 1,
                            'is_must'       => 1,
                            'default_value' => $contactsInfo->working_years,
                            'placeholder'   => '请输入工龄'
                        ],
                        [
                            'field_name'  => 'reasons',
                            'title'       => '调岗原因',
                            'field_type'  => 1,
                            'is_must'     => 1,
                            'placeholder' => '请输入调岗原因'
                        ],
                        [
                            'field_name'  => 'new_department',
                            'title'       => '调整后部门',
                            'field_type'  => 1,
                            'is_must'     => 1,
                            'placeholder' => '请输入调岗后的部门'
                        ],
                        [
                            'field_name'  => 'new_position',
                            'title'       => '调整后职位',
                            'field_type'  => 1,
                            'is_must'     => 1,
                            'placeholder' => '请输入调岗后的职位'
                        ],
                        [
                            'field_name'  => 'adjust_time',
                            'title'       => '调整日期',
                            'field_type'  => 7,
                            'is_must'     => 1,
                            'text_type'   => 0,
                            'placeholder' => '请选择'
                        ],
                        [
                            'field_name'  => 'copy_ids',
                            'title'       => '抄送人',
                            'field_type'  => 4,
                            'is_must'     => 0,
                            'placeholder' => '请选择抄送人'
                        ]
                    ],
                    "button_title"       => "确认提交",
                ];
                break;
            case 5:
                $fieldList = [
                    'process_title'      => '报销',
                    'scene'              => 'reimburse',
                    'process_field_list' => [
                        [
                            'title'            => '请填写报销详情',
                            'field_type'       => 16,
                            'color'            => 'FFFFFF',
                            'background_color' => 'FF0000'
                        ],
                        [
                            'field_name'  => 'money',
                            'title'       => '报销金额（元）',
                            'field_type'  => 1,
                            'is_must'     => 1,
                            'placeholder' => '请输入报销金额'
                        ],
                        [
                            'field_name'  => 'type',
                            'title'       => '报销类别',
                            'field_type'  => 1,
                            'is_must'     => 1,
                            'placeholder' => '如：活动经费'
                        ],
                        [
                            'field_name'  => 'details',
                            'title'       => '费用明细',
                            'field_type'  => 1,
                            'is_must'     => 1,
                            'placeholder' => '请输入费用明细...'
                        ],
                        [
                            'field_name'  => 'image_url',
                            'title'       => '附件上传',
                            'field_type'  => 9,
                            'is_must'     => 0,
                            'placeholder' => '请选择附件'
                        ],
                        [
                            'field_name'  => 'copy_ids',
                            'title'       => '抄送人',
                            'field_type'  => 4,
                            'is_must'     => 0,
                            'placeholder' => '请选择抄送人'
                        ]
                    ],
                    "button_title"       => "确认提交",
                ];
                break;
            case 6:
                $fieldList = [
                    'process_title'      => '外出',
                    'scene'              => 'go_out',
                    'process_field_list' => [
                        [
                            'title'            => '请填写外出时间',
                            'field_type'       => 16,
                            'color'            => 'FFFFFF',
                            'background_color' => 'FF0000'
                        ],
                        [
                            'field_name'  => 'start_time',
                            'title'       => '开始时间',
                            'placeholder' => '请选择',
                            'field_type'  => 7,
                            'is_must'     => 1,
                            'text_type'   => 0,
                        ],
                        [
                            'field_name'  => 'end_time',
                            'title'       => '结束时间',
                            'placeholder' => '请选择',
                            'field_type'  => 7,
                            'is_must'     => 1,
                            'text_type'   => 0,
                        ],
                        [
                            'title'       => '时长（h）',
                            'field_type'  => 1,
                            'is_must'     => 0,
                            'placeholder' => '请输入时长'
                        ],
                        [
                            'title'            => '请填写外出事由',
                            'field_type'       => 16,
                            'color'            => 'FFFFFF',
                            'background_color' => 'FF0000'
                        ],
                        [
                            'field_name'  => 'reasons',
                            'title'       => '',
                            'field_type'  => 2,
                            'is_must'     => 1,
                            'placeholder' => '请输入外出事由...'
                        ],
                        [
                            'field_name'  => 'image_url',
                            'title'       => '附件上传',
                            'field_type'  => 9,
                            'is_must'     => 0,
                            'placeholder' => '请选择附件'
                        ],
                        [
                            'field_name'  => 'copy_ids',
                            'title'       => '抄送人',
                            'field_type'  => 4,
                            'is_must'     => 0,
                            'placeholder' => '请选择抄送人'
                        ]
                    ],
                    "button_title"       => "确认提交",
                ];
                break;
            case 7:
                $fieldList = [
                    'process_title'      => '出差',
                    'scene'              => 'business_trip',
                    'process_field_list' => [
                        [
                            'title'            => '请填写出差申请',
                            'field_type'       => 16,
                            'color'            => 'FFFFFF',
                            'background_color' => 'FF0000'
                        ],
                        [
                            'field_name'  => 'destination',
                            'title'       => '出差地点',
                            'field_type'  => 1,
                            'is_must'     => 1,
                            'placeholder' => '请输入出差地点'
                        ],
                        [
                            'field_name'  => 'start_time',
                            'title'       => '开始时间',
                            'placeholder' => '请选择',
                            'field_type'  => 7,
                            'is_must'     => 1,
                            'text_type'   => 0,
                        ],
                        [
                            'field_name'  => 'end_time',
                            'title'       => '结束时间',
                            'placeholder' => '请选择',
                            'field_type'  => 7,
                            'is_must'     => 1,
                            'text_type'   => 0,
                        ],
                        [
                            'title'       => '时长（h）',
                            'field_type'  => 1,
                            'is_must'     => 0,
                            'placeholder' => '请输入时长'
                        ],
                        [
                            'title'            => '请填写出差事由',
                            'field_type'       => 16,
                            'color'            => 'FFFFFF',
                            'background_color' => 'FF0000'
                        ],
                        [
                            'field_name'  => 'reasons',
                            'title'       => '',
                            'field_type'  => 2,
                            'is_must'     => 1,
                            'placeholder' => '请输入出差事由...'
                        ],
                        [
                            'field_name'  => 'image_url',
                            'title'       => '附件上传',
                            'field_type'  => 9,
                            'is_must'     => 0,
                            'placeholder' => '请选择附件'
                        ],
                        [
                            'field_name'  => 'copy_ids',
                            'title'       => '抄送人',
                            'field_type'  => 4,
                            'is_must'     => 0,
                            'placeholder' => '请选择抄送人'
                        ]
                    ],
                    "button_title"       => "确认提交",
                ];
                break;
            case 8:
                $fieldList = [
                    'process_title'      => '调休',
                    'scene'              => 'days_off',
                    'process_field_list' => [
                        [
                            'title'            => '请填写调休时间',
                            'field_type'       => 16,
                            'color'            => 'FFFFFF',
                            'background_color' => 'FF0000'
                        ],
                        [
                            'field_name'  => 'start_time',
                            'title'       => '开始时间',
                            'placeholder' => '请选择',
                            'field_type'  => 7,
                            'is_must'     => 1,
                            'text_type'   => 0,
                        ],
                        [
                            'field_name'  => 'end_time',
                            'title'       => '结束时间',
                            'placeholder' => '请选择',
                            'field_type'  => 7,
                            'is_must'     => 1,
                            'text_type'   => 0,
                        ],
                        [
                            'title'       => '时长（h）',
                            'field_type'  => 1,
                            'is_must'     => 0,
                            'placeholder' => '请输入时长'
                        ],
                        [
                            'title'            => '请填写调休事由',
                            'field_type'       => 16,
                            'color'            => 'FFFFFF',
                            'background_color' => 'FF0000'
                        ],
                        [
                            'field_name'  => 'reasons',
                            'title'       => '',
                            'field_type'  => 2,
                            'is_must'     => 1,
                            'placeholder' => '请输入调休事由...'
                        ],
                        [
                            'field_name'  => 'image_url',
                            'title'       => '附件上传',
                            'field_type'  => 9,
                            'is_must'     => 0,
                            'placeholder' => '请选择附件'
                        ],
                        [
                            'field_name'  => 'copy_ids',
                            'title'       => '抄送人',
                            'field_type'  => 4,
                            'is_must'     => 0,
                            'placeholder' => '请选择抄送人'
                        ]
                    ],
                    "button_title"       => "确认提交",
                ];
                break;
            case 9:
                $fieldList = [
                    'process_title'      => '补勤',
                    'scene'              => 'supplement',
                    'process_field_list' => [
                        [
                            'title'            => '请填写补勤详情',
                            'field_type'       => 16,
                            'color'            => 'FFFFFF',
                            'background_color' => 'FF0000'
                        ],
                        [
                            'field_name'  => 'time',
                            'title'       => '补勤时间',
                            'placeholder' => '请选择',
                            'field_type'  => 7,
                            'is_must'     => 1,
                            'text_type'   => 0,
                        ],
                        [
                            'field_name'  => 'reasons',
                            'title'       => '缺勤原因',
                            'field_type'  => 2,
                            'is_must'     => 1,
                            'placeholder' => '请输入缺勤原因...'
                        ],
                        [
                            'field_name'  => 'copy_ids',
                            'title'       => '抄送人',
                            'field_type'  => 4,
                            'is_must'     => 0,
                            'placeholder' => '请选择抄送人'
                        ]
                    ],
                    "button_title"       => "确认提交",
                ];
                break;
            case 10:
                break;
            case 11:
                $fieldList = [
                    'process_title'      => '加班',
                    'scene'              => 'overtime_pay',
                    'process_field_list' => [
                        // 纯文件显示
                        [
                            'title'            => '请填写加班详情',
                            'field_type'       => 16,
                            'color'            => 'FFFFFF',
                            'background_color' => 'FF0000'
                        ],
                        [
                            'field_name'  => 'start_time',
                            'title'       => '开始时间',
                            'placeholder' => '请选择',
                            'field_type'  => 7,
                            'text_type'   => 0,
                            'is_must'     => 1,
                        ],
                        [
                            'field_name'  => 'end_time',
                            'title'       => '结束时间',
                            'placeholder' => '请选择',
                            'field_type'  => 7,
                            'text_type'   => 0,
                            'is_must'     => 1,
                        ],
                        [
                            'field_name'  => 'duration',
                            'title'       => '时长（h）',
                            'field_type'  => 1,
                            'is_must'     => 0,
                            'placeholder' => '请输入时长'
                        ],
                        [
                            'field_name'  => 'work_content',
                            'title'       => '工作内容',
                            'field_type'  => 2,
                            'is_must'     => 1,
                            'placeholder' => '请输入工作内容'
                        ],
                        [
                            'field_name'  => 'workplace',
                            'title'       => '工作地点',
                            'field_type'  => 1,
                            'is_must'     => 1,
                            'placeholder' => '请输入工作地点'
                        ],
                        [
                            'field_name'  => 'overtime_pay',
                            'title'       => '加班费（元）',
                            'field_type'  => 1,
                            'is_must'     => 1,
                            'placeholder' => '请输入加班费'
                        ],
                        [
                            'field_name'  => 'table_money',
                            'title'       => '误餐费（元）',
                            'field_type'  => 1,
                            'is_must'     => 1,
                            'placeholder' => '请输入误餐费'
                        ],
                        [
                            'field_name'  => 'copy_ids',
                            'title'       => '抄送人',
                            'field_type'  => 4,
                            'is_must'     => 1,
                            'placeholder' => '请选择抄送人' //必填
                        ]
                    ],
                    "button_title"       => "确认提交",
                ];
                break;
            case 12:
                $fieldList = [
                    'process_title'      => '领用申请',
                    'scene'              => 'receive_apply_for',
                    'process_field_list' => [
                        // 纯文件显示
                        [
                            'title'            => '请填写领用申请',
                            'field_type'       => 16,
                            'color'            => 'FFFFFF',
                            'background_color' => 'FF0000'
                        ],
                        [
                            'field_name'  => 'name',
                            'title'       => '物品名',
                            'placeholder' => '请输入物品名',
                            'field_type'  => 1,
                            'text_type'   => 0,
                            'is_must'     => 1,
                        ],
                        [
                            'field_name'  => 'specification',
                            'title'       => '规格',
                            'placeholder' => '请输入规格',
                            'field_type'  => 1,
                            'text_type'   => 0,
                            'is_must'     => 1,
                        ],
                        [
                            'field_name'  => 'number',
                            'title'       => '数量',
                            'field_type'  => 1,
                            'is_must'     => 0,
                            'placeholder' => '请输入数量'
                        ],
                        [
                            'field_name'  => 'use',
                            'title'       => '用途',
                            'field_type'  => 2,
                            'is_must'     => 1,
                            'placeholder' => '请输入用途'
                        ],
                        [
                            'field_name'  => 'date',
                            'title'       => '申报日期',
                            'placeholder' => '请选择申报日期',
                            'field_type'  => 7,
                            'text_type'   => 0,
                            'is_must'     => 1,
                        ],
                        [
                            'field_name'  => 'copy_ids',
                            'title'       => '抄送人',
                            'field_type'  => 4,
                            'is_must'     => 1,
                            'placeholder' => '请选择抄送人' //必填
                        ]
                    ],
                    "button_title"       => "确认提交",
                ];
                break;
            case 13:
                $fieldList = [
                    'process_title'      => '采购',
                    'scene'              => 'procurement',
                    'process_field_list' => [
                        [
                            'title'            => '请填写采购详情',
                            'field_type'       => 16,
                            'color'            => 'FFFFFF',
                            'background_color' => 'FF0000'
                        ],
                        [
                            'field_name'  => 'name',
                            'title'       => '物品名',
                            'field_type'  => 1,
                            'is_must'     => 1,
                            'placeholder' => '请输入物品名'
                        ],
                        [
                            'field_name'  => 'format',
                            'title'       => '规格',
                            'field_type'  => 1,
                            'is_must'     => 1,
                            'placeholder' => '请输入规格'
                        ],
                        [
                            'field_name'  => 'number',
                            'title'       => '数量',
                            'field_type'  => 1,
                            'is_must'     => 1,
                            'placeholder' => '请输入数量'
                        ],
                        [
                            'field_name'  => 'prise',
                            'title'       => '单价(元)',
                            'field_type'  => 1,
                            'is_must'     => 1,
                            'placeholder' => '请输入单价'
                        ],
                        [
                            'field_name'  => 'total',
                            'title'       => '合计(元)',
                            'field_type'  => 1,
                            'is_must'     => 1,
                            'placeholder' => '请输入合计'
                        ],

                        [
                            'field_name'  => 'purpose',
                            'title'       => '用途',
                            'field_type'  => 1,
                            'is_must'     => 1,
                            'placeholder' => '请输入用途'
                        ],

                        [
                            'field_name'  => 'supplier',
                            'title'       => '供货商',
                            'field_type'  => 1,
                            'is_must'     => 1,
                            'placeholder' => '请输入供货商'
                        ],

                        [
                            'field_name'  => 'deadline',
                            'title'       => '保质期(过期时间)',
                            'placeholder' => '请选择保质期',
                            'field_type'  => 7,
                            'is_must'     => 1,
                            'text_type'   => 0,
                        ],
                        [
                            'field_name'  => 'copy_ids',
                            'title'       => '抄送人',
                            'field_type'  => 4,
                            'is_must'     => 0,
                            'placeholder' => '请选择抄送人'
                        ]
                    ],
                    "button_title"       => "确认提交",
                ];
                break;
            case 14:
                $fieldList = [
                    'process_title'      => '合同',
                    'scene'              => 'contract',
                    'process_field_list' => [
                        [
                            'title'            => '请填写合同详情',
                            'field_type'       => 16,
                            'color'            => 'FFFFFF',
                            'background_color' => 'FF0000'
                        ],
                        [
                            'field_name'  => 'name',
                            'title'       => '合同名',
                            'field_type'  => 1,
                            'is_must'     => 1,
                            'placeholder' => '请输入合同名'
                        ],
                        [
                            'field_name'  => 'purpose',
                            'title'       => '用途',
                            'field_type'  => 1,
                            'is_must'     => 1,
                            'placeholder' => '请输入用途'
                        ],
                        [
                            'field_name'  => 'copy_ids',
                            'title'       => '抄送人',
                            'field_type'  => 4,
                            'is_must'     => 0,
                            'placeholder' => '请选择抄送人'
                        ]
                    ],
                    "button_title"       => "确认提交",
                ];
                break;

            case 15:
                $fieldList = [
                    'process_title'      => '备用金',
                    'scene'              => 'petty_cash',
                    'process_field_list' => [
                        [
                            'title'            => '请填写备用金详情',
                            'field_type'       => 16,
                            'color'            => 'FFFFFF',
                            'background_color' => 'FF0000'
                        ],
                        [
                            'field_name'  => 'money',
                            'title'       => '金额(元)',
                            'field_type'  => 1,
                            'is_must'     => 1,
                            'placeholder' => '请输入金额(元)'
                        ],
                        [
                            'field_name'  => 'purpose',
                            'title'       => '用途',
                            'field_type'  => 1,
                            'is_must'     => 1,
                            'placeholder' => '请输入用途'
                        ],
                        [
                            'field_name'  => 'apply_date',
                            'title'       => '申报日期',
                            'placeholder' => '请选择申报日期',
                            'field_type'  => 7,
                            'is_must'     => 1,
                            'text_type'   => 0,
                        ],
                        [
                            'field_name'  => 'copy_ids',
                            'title'       => '抄送人',
                            'field_type'  => 4,
                            'is_must'     => 0,
                            'placeholder' => '请选择抄送人'
                        ]
                    ],
                    "button_title"       => "确认提交",
                ];
                break;
            case 16:
                $fieldList = [
                    'process_title'      => '制度方案',
                    'scene'              => 'system_solutions',
                    'process_field_list' => [
                        [
                            'title'            => '请填写制度方案详情',
                            'field_type'       => 16,
                            'color'            => 'FFFFFF',
                            'background_color' => 'FF0000'
                        ],
                        [
                            'field_name'  => 'name',
                            'title'       => '名称',
                            'field_type'  => 1,
                            'is_must'     => 1,
                            'placeholder' => '请输入名称'
                        ],
                        [
                            'field_name'  => 'content',
                            'title'       => '内容',
                            'field_type'  => 2,
                            'is_must'     => 1,
                            'placeholder' => '请输入内容'
                        ],
                        [
                            'field_name'  => 'do_time',
                            'title'       => '执行时间',
                            'placeholder' => '请选择执行时间',
                            'field_type'  => 7,
                            'is_must'     => 1,
                            'text_type'   => 0,
                        ],
                        [
                            'field_name'  => 'copy_ids',
                            'title'       => '抄送人',
                            'field_type'  => 4,
                            'is_must'     => 0,
                            'placeholder' => '请选择抄送人'
                        ]
                    ],
                    "button_title"       => "确认提交",
                ];
                break;
            case 17:
                $fieldList = [
                    'process_title'      => '招聘需求',
                    'scene'              => 'recruitment_needs',
                    'process_field_list' => [
                        [
                            'title'            => '请填写合同详情',
                            'field_type'       => 16,
                            'color'            => 'FFFFFF',
                            'background_color' => 'FF0000'
                        ],
                        [
                            'field_name'  => 'name',
                            'title'       => '岗位名称',
                            'field_type'  => 1,
                            'is_must'     => 1,
                            'placeholder' => '请输入岗位名称'
                        ],
                        [
                            'field_name'  => 'person_number',
                            'title'       => '人数',
                            'field_type'  => 1,
                            'is_must'     => 1,
                            'placeholder' => '请输入人数'
                        ],
                        [
                            'field_name'  => 'deparment',
                            'title'       => '部门',
                            'field_type'  => 1,
                            'is_must'     => 1,
                            'placeholder' => '请输入部门'
                        ],
                        [
                            'field_name'  => 'requirement',
                            'title'       => '要求',
                            'field_type'  => 2,
                            'is_must'     => 1,
                            'placeholder' => '请输入要求'
                        ],
                        [
                            'field_name'  => 'requirement_time',
                            'title'       => '招聘时间',
                            'placeholder' => '请选择招聘时间',
                            'field_type'  => 7,
                            'is_must'     => 1,
                            'text_type'   => 0,
                        ],
                        [
                            'field_name'  => 'copy_ids',
                            'title'       => '抄送人',
                            'field_type'  => 4,
                            'is_must'     => 0,
                            'placeholder' => '请选择抄送人'
                        ]
                    ],
                    "button_title"       => "确认提交",
                ];
                break;
            case 18:
                $fieldList = [
                    'process_title'      => '奖罚申报',
                    'scene'              => 'reward_and_punish',
                    'process_field_list' => [
                        [
                            'title'            => '请填写奖罚申报详情',
                            'field_type'       => 16,
                            'color'            => 'FFFFFF',
                            'background_color' => 'FF0000'
                        ],
                        [
                            'field_name'  => 'oa_contact_id',
                            'title'       => '员工',
                            'field_type'  => 4,
                            'is_must'     => 1,
                            'placeholder' => '请选择员工'
                        ],

                        [
                            'field_name'  => 'type',
                            'title'       => '奖罚方式',
                            'field_type'  => 1,
                            'is_must'     => 1,
                            'placeholder' => '请输入奖罚方式'
                        ],
                        [
                            'field_name'  => 'reason',
                            'title'       => '原因',
                            'field_type'  => 2,
                            'is_must'     => 1,
                            'placeholder' => '请输入原因'
                        ],
                        [
                            'field_name'  => 'time',
                            'title'       => '奖罚时间',
                            'placeholder' => '请选择奖罚时间',
                            'field_type'  => 7,
                            'is_must'     => 1,
                            'text_type'   => 0,
                        ],
                        [
                            'field_name'  => 'copy_ids',
                            'title'       => '抄送人',
                            'field_type'  => 4,
                            'is_must'     => 0,
                            'placeholder' => '请选择抄送人'
                        ]
                    ],
                    "button_title"       => "确认提交",
                ];
                break;
            case 19:
                $fieldList = [
                    'process_title'      => '离职',
                    'scene'              => 'dimission',
                    'process_field_list' => [
                        [
                            'title'            => '请填写合同详情',
                            'field_type'       => 16,
                            'color'            => 'FFFFFF',
                            'background_color' => 'FF0000'
                        ],
                        [
                            'field_name'  => 'oa_contact_id',
                            'title'       => '员工',
                            'field_type'  => 4,
                            'is_must'     => 1,
                            'placeholder' => '请选择员工'
                        ],
                        [
                            'field_name'  => 'reason',
                            'title'       => '原因',
                            'field_type'  => 2,
                            'is_must'     => 1,
                            'placeholder' => '请输入原因'
                        ],
                        [
                            'field_name'  => 'time',
                            'title'       => '离职时间',
                            'placeholder' => '请选择离职时间',
                            'field_type'  => 7,
                            'is_must'     => 1,
                            'text_type'   => 0,
                        ],
                        [
                            'field_name'  => 'copy_ids',
                            'title'       => '抄送人',
                            'field_type'  => 4,
                            'is_must'     => 0,
                            'placeholder' => '请选择抄送人'
                        ]
                    ],
                    "button_title"       => "确认提交",
                ];
                break;
            case 20:
                $fieldList = [
                    'process_title'      => '转正，晋升，调薪（变动原因）',
                    'scene'              => 'become_promote_salary',
                    'process_field_list' => [
                        [
                            'title'            => '请选择变动类型',
                            'field_type'       => 16,
                            'color'            => 'FFFFFF',
                            'background_color' => 'FF0000'
                        ],
                        [
                            'field_name'  => 'type',
                            'title'       => '变动类型',
                            'field_type'  => 3,
                            'is_must'     => 1,
                            'placeholder' => '请选择',
                            'data'        => [
                                [
                                    'id'    => 1,
                                    'title' => '转正'
                                ],
                                [
                                    'id'    => 2,
                                    'title' => '晋升'
                                ],
                                [
                                    'id'    => 3,
                                    'title' => '调薪'
                                ]
                            ]
                        ],
                        [
                            'title'            => '请填写合同详情',
                            'field_type'       => 16,
                            'color'            => 'FFFFFF',
                            'background_color' => 'FF0000'
                        ],
                        [
                            'field_name'  => 'oa_contact_id',
                            'title'       => '员工',
                            'field_type'  => 4,
                            'is_must'     => 1,
                            'placeholder' => '请选择员工'
                        ],

                        [
                            'field_name'  => 'reason',
                            'title'       => '变动原因',
                            'field_type'  => 2,
                            'is_must'     => 1,
                            'placeholder' => '请输入变动原因'
                        ],


                        [
                            'field_name'  => 'current_salary',
                            'title'       => '现工资',
                            'field_type'  => 1,
                            'is_must'     => 1,
                            'placeholder' => '请输入现工资'
                        ],
                        [
                            'field_name'  => 'adjustment_salary',
                            'title'       => '调薪工资',
                            'field_type'  => 1,
                            'is_must'     => 1,
                            'placeholder' => '请输入调薪工资'
                        ],
                        [
                            'field_name'  => 'note',
                            'title'       => '备注',
                            'field_type'  => 2,
                            'is_must'     => 1,
                            'placeholder' => '请输入备注'
                        ],
                        [
                            'field_name'  => 'copy_ids',
                            'title'       => '抄送人',
                            'field_type'  => 4,
                            'is_must'     => 0,
                            'placeholder' => '请选择抄送人'
                        ]
                    ],
                    "button_title"       => "确认提交",
                ];
                break;
            case 21:
                $fieldList = [
                    'process_title'      => '补签',
                    'scene'              => 'replenish_sign',
                    'process_field_list' => [
                        [
                            'title'            => '请选择补卡时间',
                            'field_type'       => 16,
                            'color'            => 'FFFFFF',
                            'background_color' => 'FF0000'
                        ],
                        [
                            'field_name'  => 'type',
                            'title'       => '变动类型',
                            'field_type'  => 3,
                            'is_must'     => 1,
                            'placeholder' => '请选择',
                            'data'        => [
                                [
                                    'id'    => 1,
                                    'title' => '上班'
                                ],
                                [
                                    'id'    => 2,
                                    'title' => '下班'
                                ]
                            ]
                        ],
                        [
                            'field_name'  => 'reason',
                            'title'       => '缺卡原因',
                            'field_type'  => 2,
                            'is_must'     => 1,
                            'placeholder' => '请输入缺卡原因'
                        ],
                        [
                            'field_name'  => 'copy_ids',
                            'title'       => '抄送人',
                            'field_type'  => 4,
                            'is_must'     => 0,
                            'placeholder' => '请选择抄送人'
                        ]
                    ],
                    "button_title"       => "确认提交",
                ];
                break;
            case 22:
                break;
            case 23:
                $fieldList = [
                    'process_title'      => '入职',
                    'scene'              => 'taking_work',
                    'process_field_list' => [
                        [
                            'field_name'  => 'note',
                            'title'       => '备注',
                            'field_type'  => 2,
                            'is_must'     => 1,
                            'placeholder' => '请输入备注'
                        ],
                        [
                            'field_name'  => 'image_url',
                            'title'       => '附件上传',
                            'field_type'  => 9,
                            'is_must'     => 0,
                            'placeholder' => '请选择附件'
                        ],
                        [
                            'field_name'  => 'copy_ids',
                            'title'       => '抄送人',
                            'field_type'  => 4,
                            'is_must'     => 0,
                            'placeholder' => '请选择抄送人'
                        ]
                    ],
                    "button_title"       => "确认提交",
                ];
                break;
            case 25:
                $fieldList = [
                    'process_title'      => '客户订单',
                    'scene'              => 'customer_order',
                    'process_field_list' => [
                        [
                            'field_name'  => 'workplace',
                            'title'       => '单位',
                            'field_type'  => 999,
                            'is_must'     => 1,
                            'placeholder' => '请选择单位'
                        ]
                    ],
                    "button_title"       => "确认提交",
                ];
                break;
            case 26:
                //获取审核人（第一步）
                $flowConfigId = \App\Eloquent\Oa\FlowConfig::where(['oa_flow_id'=>$flowId,'oa_company_id'=>$companyId])->first()->id;
                $flowConfigProcessList = \App\Eloquent\Oa\FlowConfigProcess::where(['oa_flow_config_id'=>$flowConfigId])->get()->toArray();
                $firstConfigProcessUid = $firstConfigProcessId = '';
                foreach ($flowConfigProcessList as $flowConfigProcessRow){
                    $firstConfigProcessId = $flowConfigProcessRow['id'];

                    //判断是不是流程第一步--没有
                    if(\App\Eloquent\Oa\FlowConfigProcess::where(['pid'=>$firstConfigProcessId])->count() == 0){
                        $firstConfigProcessUid = $flowConfigProcessRow['operator_id'];
                        break;
                    }
                }

                //获取用户
                $firstConfigProcessUserInfo = \App\Eloquent\Zk\DepartmentUser::getCurrentInfo($firstConfigProcessUid)->toArray();


                $fieldList = [
                    'process_title'      => '创建采购',
                    'scene'              => 'purchase_application',
                    'operator_info' => [
                        'uid' => $firstConfigProcessUserInfo['user_id'],
                        'name' => $firstConfigProcessUserInfo['truename'],
                        ],
                    'process_field_list' => [
//                        [
//                            'field_name'  => 'application_cause',
//                            'title'       => '申请事由',
//                            'field_type'  => 2,
//                            'is_must'     => 1,
//                            'placeholder' => '请输入采购事由'
//                        ],
//                        [
//                            'field_name'  => 'purchase_date',
//                            'title'       => '交货日期',
//                            'field_type'  => 7,
//                            'is_must'     => 1,
//                            'text_type'     => 0,
//                            'placeholder' => date('Y-m-d')
//                        ],
//                        [
//                            'field_name'  => 'payment_method',
//                            'title'       => '支付方式',
//                            'field_type'  => 3,
//                            'is_must'     => 1,
//                            'placeholder' => '请输入',
//                            'data' =>[
//                                [
//                                    'id' => 1,
//                                    'title' => '现金'
//                                ]
//
//                            ]
//                        ],
//                        [
//                            'field_name'  => 'content',
//                            'title'       => '备注',
//                            'field_type'  => 2,
//                            'is_must'     => 1,
//                            'placeholder' => '请输入'
//                        ],
                        [
                            'field_name'  => 'material_list',
                            'title'       => '采购其他材料',
                            'field_type'  => 18,
                            'is_must'     => 1,
                            'placeholder' => ''
                        ],
                    ],
                    "button_title"       => "确认提交",
                ];
                break;
            case 27:
                //获取审核人（第一步）
                $flowConfigId = \App\Eloquent\Oa\FlowConfig::where(['oa_flow_id'=>$flowId,'oa_company_id'=>$companyId])->first()->id;
                $flowConfigProcessList = \App\Eloquent\Oa\FlowConfigProcess::where(['oa_flow_config_id'=>$flowConfigId])->get()->toArray();
                $firstConfigProcessUid = $firstConfigProcessId = '';
                foreach ($flowConfigProcessList as $flowConfigProcessRow){
                    $firstConfigProcessId = $flowConfigProcessRow['id'];

                    //判断是不是流程第一步--没有
                    if(\App\Eloquent\Oa\FlowConfigProcess::where(['pid'=>$firstConfigProcessId])->count() == 0){
                        $firstConfigProcessUid = $flowConfigProcessRow['operator_id'];
                        break;
                    }
                }

                //获取用户
                $firstConfigProcessUserInfo = \App\Eloquent\Zk\DepartmentUser::getCurrentInfo($firstConfigProcessUid)->toArray();

                $fieldList = [
                    'process_title'      => '退货申请',
                    'scene'              => 'return_purchase_application',
                    'operator_info' => [
                        'uid' => $firstConfigProcessUserInfo['user_id'],
                        'name' => $firstConfigProcessUserInfo['truename'],
                    ],
                    'process_field_list' => [
                        [
                            'field_name'  => 'application_cause',
                            'title'       => '申请事由',
                            'field_type'  => 2,
                            'is_must'     => 1,
                            'placeholder' => '请输入退货事由'
                        ],
//                        [
//                            'field_name'  => 'payment_method',
//                            'title'       => '支付方式',
//                            'field_type'  => 3,
//                            'is_must'     => 1,
//                            'placeholder' => '请输入',
//                            'data' =>[
//                                [
//                                    'id' => 1,
//                                    'title' => '现金'
//                                ]
//
//                            ]
//                        ],
                        [
                            'field_name'  => 'content',
                            'title'       => '备注',
                            'field_type'  => 2,
                            'is_must'     => 1,
                            'placeholder' => '请输入'
                        ],
                        [
                            'field_name'  => 'material_list',
                            'title'       => '退货材料列表',
                            'field_type'  => 18,
                            'is_must'     => 1,
                            'placeholder' => ''
                        ],
                    ],
                    "button_title"       => "确认提交",
                ];
                break;
        }



        return $fieldList;
    }
}