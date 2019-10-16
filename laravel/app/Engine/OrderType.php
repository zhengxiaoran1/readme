<?php

namespace App\Engine;

use App\Eloquent\Ygt\OrderType as OrderTypeModel;
use App\Eloquent\Ygt\ProcessOrdertype as ProcessOrderTypeModel;
use App\Eloquent\Ygt\YgtEloquent;
use App\Engine\Permission;
use App\Engine\Func;

class OrderType extends OrderTypeModel
{

    public static function getOrderTypeList($where, $limit = '', $offset = '')
    {
//        return OrderTypeModel::where(['company_id'=>$companyId])->get();
        $obj = OrderTypeModel::where($where)
                            ->orderBy('sort', 'asc')
                            ->orderBy('use_num', 'desc')
                            ->orderBy('id', 'desc')
                            ->with('process');
        // dd($obj);die;
        $obj = $offset == '' ? $obj : $obj->skip($offset);
        $obj = $limit  == '' ? $obj : $obj->take($limit);
        return $obj->get();
    }

    public function process()
    {
        return $this->belongsToMany('App\Eloquent\Ygt\Process', 'ygt_process_ordertype', 'ordertype_id', 'process_id')->withPivot('step_number')->orderBy('step_number');
//        return $this->hasMany('App\Eloquent\Ygt\ProcessOrdertype','ordertype_id');
    }



    /**
     * 获取第一个工序
     */
    public static function getFirstOneProcessId($orderTypeId)
    {
        return ProcessOrderTypeModel::where('ordertype_id', $orderTypeId)->orderBy('step_number', 'asc')->orderBy('process_id', 'asc')->pluck('process_id')->first();
    }

    /**
     * @param $orderTypeId
     * @return array|bool
     * 获取第一个步骤
     */
    public static function getFirstStep($orderTypeId)
    {
        $result = ProcessOrderTypeModel::where('ordertype_id', $orderTypeId)->orderBy('step_number', 'asc')->orderBy('process_id', 'asc')->select('id', 'process_id')->first();
        if ($result) {
            return $result->toArray();
        } else {
            return false;
        }
    }

    /**
     * @param $currentId
     * @return array|bool
     * 获取下一步骤
     */
    public static function getNextStep($currentId)
    {
        $currentStepInfo = ProcessOrderTypeModel::where('id', $currentId)->first();
        if ($currentStepInfo['next_step'] and $nextSteps = explode(',', $currentStepInfo['next_step'])) {
            $result = ProcessOrderTypeModel::whereIn('id', $nextSteps)->select('id', 'process_id')->get();
            if ($result) {
                return $result->toArray();
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 获取下一个工序
     */
    public static function getNextOneProcessId($orderTypeId, $processId, $stepNumber = false)
    {
        if (!$stepNumber) {
            $processOrderTypeInfo = ProcessOrderTypeModel::where([['ordertype_id', $orderTypeId], ['process_id', $processId]])->first();
            if ($processOrderTypeInfo) {
                $stepNumber = $processOrderTypeInfo['step_number'];
            } else {
                return false;
            }
        }

        $result = ProcessOrderTypeModel::where([
            ['ordertype_id', $orderTypeId],
            ['step_number', '>', $stepNumber],
            ['process_id', '<>', $processId]
        ])
            ->orWhere([
                ['ordertype_id', $orderTypeId],
                ['step_number', $stepNumber],
                ['process_id', '>', $processId]
            ])
            ->orderBy('step_number', 'asc')->orderBy('process_id', 'asc')->pluck('process_id')->first();
        return $result ? $result : false;


    }

    /**
     * @param $orderTypeId
     * @param int $stepNumber
     * @return static
     * 获取指定步骤的工序
     */
    public static function getStepProcessBag($orderTypeId, $stepNumber = 1)
    {
        $result = ProcessOrderTypeModel::where([['ordertype_id', $orderTypeId], ['step_number', $stepNumber]])->get();
        return $result->pluck('process_id');
    }

    /**
     * @param $orderTypeId
     * @return static
     * 获取第一个工序包
     */
    public static function getFirstProcessBag($orderTypeId)
    {
        return self::getStepProcessBag($orderTypeId, 1);
    }

    /**
     * 获取下一个工序包
     */
    public static function getNextProcessBag()
    {

    }

    /**
     * @param $orderTypeId
     * @return static
     * 获取工艺步骤
     */
    public static function getOrderTypeSteps($orderTypeId)
    {
        $result = ProcessOrderTypeModel::where('ordertype_id', $orderTypeId)->select(['step_number'])->groupBy('step_number')->get()->pluck('step_number');
        return $result;
    }

    /**
     * 获取所有工序
     */
    public static function getAllOrderTypeProcess($orderTypeId)
    {
        $result = ProcessOrderTypeModel::where('ordertype_id', $orderTypeId)->orderBy('step_number', 'asc')->orderBy('process_id', 'asc')->get()->pluck('process_id');
        return $result;

    }

    /**
     * @param $orderTypeId
     * 获取所有步骤 带分配权限
     */
    public static function getAllStepsWithDistribution($orderTypeId)
    {
        $result = ProcessOrderTypeModel::leftJoin('ygt_process', 'ygt_process.id', '=', 'process_id')->where('ordertype_id', $orderTypeId)->orderBy('step_number', 'asc')->select('ygt_process_ordertype.id', 'process_id', 'step_number', 'ygt_process.title')->get();
        $result->transform(function ($item) {
            $item->distribution = Permission::getStepActionPrivilegeIds(3, $item->id);
            return $item;
        });
        return $result;
    }
    public static function getAllStepsWithDistribution2($orderTypeId,$process_id)
    {

        $result = ProcessOrderTypeModel::leftJoin('ygt_process', 'ygt_process.id', '=', 'process_id')->where('process_id', $process_id)->where('ordertype_id', $orderTypeId)->orderBy('step_number', 'asc')->select('ygt_process_ordertype.id', 'process_id', 'step_number', 'ygt_process.title')->get();

        $result->transform(function ($item) {
            $item->distribution = Permission::getStepActionPrivilegeIds(3, $item->id);
            return $item;
        });
        return $result;
    }

    public static function getAllStepsWithActionPrivilegeIds($orderTypeId,$action)
    {
        $result = ProcessOrderTypeModel::leftJoin('ygt_process', 'ygt_process.id', '=', 'process_id')->where('ordertype_id', $orderTypeId)->orderBy('step_number', 'asc')->select('ygt_process_ordertype.id', 'process_id', 'step_number', 'ygt_process.title')->get();
        $result->transform(function ($item) use($action) {
            $item->distribution = Permission::getStepActionPrivilegeIds($action, $item->id);
            return $item;
        });
        return $result;
    }

    /**
     * 获取工艺中工序的顺序
     */
    public static function getOrderTypeProcessNumber($orderTypeId, $processId, $stepNumber = false)
    {
        if (!$stepNumber) {
            $processOrderTypeInfo = ProcessOrderTypeModel::where([['ordertype_id', $orderTypeId], ['process_id', $processId]])->first();
            if ($processOrderTypeInfo) {
                $stepNumber = $processOrderTypeInfo['step_number'];
            } else {
                return false;
            }
        }
        return ProcessOrderTypeModel::where([
            ['ordertype_id', $orderTypeId],
            ['step_number', '<', $stepNumber],
        ])
            ->orWhere([
                ['ordertype_id', $orderTypeId],
                ['step_number', $stepNumber],
                ['process_id', '<=', $processId]
            ])->count();
    }

    /**
     * @param $orderTypeId
     * @param $processId
     * @param bool $stepNumber
     * @return bool|\Illuminate\Support\Collection
     * 获取上一步骤的所有工序
     */
    public static function getAllPrevOrderProcess($orderTypeId, $processId, $stepNumber = false)
    {
        if (!$stepNumber) {
            $processOrderTypeInfo = ProcessOrderTypeModel::where([['ordertype_id', $orderTypeId], ['process_id', $processId]])->first();
            if ($processOrderTypeInfo) {
                $stepNumber = $processOrderTypeInfo['step_number'];
            } else {
                return false;
            }
        }else{
            $processOrderTypeInfo = ProcessOrderTypeModel::where([['ordertype_id', $orderTypeId], ['process_id', $processId], ['step_number', $stepNumber]])->first();
        }



        //获取上道工序的方法是错误的，进行修复 zhuyujun 20190515
//        return ProcessOrderTypeModel::where([
//            ['ordertype_id', $orderTypeId],
//            ['step_number', $stepNumber - 1]
//        ])->get()->pluck('process_id');
        $where = ['ordertype_id'=>$orderTypeId];
        $tmp_process_ordertype_list = ProcessOrderTypeModel::where($where)->get();
        //取出下一步是当前步骤的工序（获取前一道工序）
        $pre_ordertype_process_id_list = [];
        foreach ($tmp_process_ordertype_list as $tmp_process_ordertype_row){
            $tmp_next_step = $tmp_process_ordertype_row['next_step'];
            if($tmp_next_step){
                $tmpArr = explode(',',$tmp_next_step);
                if(in_array($processOrderTypeInfo['id'],$tmpArr)){
                    $pre_ordertype_process_id_list[] = $tmp_process_ordertype_row['id'];
                }
            }
        }

        //这样处理，效率会有所下降，主要为了兼容旧的使用
        return ProcessOrderTypeModel::whereIn('id',$pre_ordertype_process_id_list)->get()->pluck('process_id');

    }

    /**
     * @param $orderTypeId
     * @param $processId
     * @param bool $stepNumber
     * @return bool|static
     * 获取下一步骤的所有工序(返回process_id)
     */
    public static function getAllNextOrderProcess($orderTypeId, $processId, $stepNumber = false)
    {
        if (!$stepNumber) {
            $processOrderTypeInfo = ProcessOrderTypeModel::where([['ordertype_id', $orderTypeId], ['process_id', $processId]])->first();
            if ($processOrderTypeInfo) {
                $stepNumber = $processOrderTypeInfo['step_number'];
            } else {
                return false;
            }
        }
        return ProcessOrderTypeModel::where([
            ['ordertype_id', $orderTypeId],
            ['step_number', $stepNumber + 1]
        ])->get()->pluck('process_id');
    }

    /**
     * @param $orderTypeId
     * @param $processId
     * @param bool $stepNumber
     * @return bool|static
     * 获取下一步骤的所有工序(返回process_ordertype_id)
     */
    public static function getAllNextOrderTypeProcess($orderTypeId, $processId, $stepNumber = false)
    {
        if (!$stepNumber) {
            $processOrderTypeInfo = ProcessOrderTypeModel::where([['ordertype_id', $orderTypeId], ['process_id', $processId]])->first();
            if ($processOrderTypeInfo) {
                $stepNumber = $processOrderTypeInfo['step_number'];
            } else {
                return false;
            }
        }
        return ProcessOrderTypeModel::where([
            ['ordertype_id', $orderTypeId],
            ['step_number', $stepNumber + 1]
        ])->get()->pluck('id');
    }

    /**
     * @param $orderTypeId
     * @param $processId
     * @return bool|static
     * 获取同一工序步骤的所有工序
     */
    public static function getCurrentOrderProcess($orderTypeId, $processId)
    {
        $processOrderTypeInfo = ProcessOrderTypeModel::where([['ordertype_id', $orderTypeId], ['process_id', $processId]])->first();
        if ($processOrderTypeInfo) {
            $stepNumber = $processOrderTypeInfo['step_number'];
        } else {
            return false;
        }
        return ProcessOrderTypeModel::where([
            ['ordertype_id', $orderTypeId],
            ['step_number', $stepNumber]
        ])->get()->pluck('process_id');
    }

    /**
     * 增加订单类型使用次数
     */
    public static function incrementUseNum($orderTypeId)
    {
        $orderTypeObj = OrderTypeModel::where(['id' => $orderTypeId])->first();
        $orderTypeObj->use_num = $orderTypeObj->use_num + 1;
        $orderTypeObj->save();
    }

    /**
     * @param $currentId 当前工艺工序ID
     * @return mixed
     * 获取当前工序是否可以和下一工序同时开工
     */
    public static function isMeanwhileNextStep($currentId){
        $currentStepInfo = ProcessOrderTypeModel::where('id', $currentId)->first();
        return $currentStepInfo->meanwhile_next_step == 'yes'?true:false;
    }



    //重写方法 20180604 zhuyujun
    public static function getOneValueById( $id, $fileds ){
//        $where              = ['id'=>$id];
//        $result             = self::getOneValue( $where, $fileds );
//        return $result;

        $tmpObj =self::withTrashed()->where(['id'=>$id])->first();
        $result = '';
        if($tmpObj){
            $result = $tmpObj->$fileds;
        }
        return $result;
    }

}