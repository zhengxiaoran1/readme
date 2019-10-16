<?php
/**
 * Created by PhpStorm.
 * Author: Sojo
 * Date: 2017/2/25
 * Time: 16:17
 */

namespace App\Repositories\OA;

use App\Eloquent\Oa\Workflow;
use Framework\BaseClass\Repositories\Repository;

class WorkflowRepository extends Repository
{
    const RELATED_TYPE_DOCUMENT = 1;
    const RELATED_TYPE_LEAVE = 2;
    const RELATED_TYPE_SEAL =3;
    const RELATED_TYPE_CAR = 4;

    public function model()
    {
        return Workflow::class;
    }

    public function getPagingListByContactsId($contactsId, $scene, $type, $isRead, $page, $pageSize, array $columns = ['*'], array $touchColumns = ['*'],$status=0)
    {
        $condition = [];
        $relation = null;
        $relationCallback = null;

        switch ($scene) {
            case 'todo':
                $condition = [
                    ['assignee_id', '=', $contactsId],
                    ['dispose_code', '<>', 1100],
                    ['dispose_code', '<>', 1200],
                    ['dispose_code', '<>', 1201],
                ];
                break;
            case 'done':
                $relation = 'workflowLogList';
                $condition = [
                    ['creator_id', '!=', $contactsId],
                ];
                $relationCallback = function ($query) use ($contactsId) {
                    $query->where('operator_id', $contactsId);
                };
                break;
            case 'my':
                $condition = [
                    ['creator_id', '=', $contactsId],
                ];
                break;
            case 'copy':
                $relation = 'workflowCopyList';
                $relationCallback = function ($query) use ($contactsId, $isRead) {
                    $query->where('copy_to_id', $contactsId);
                    if ($isRead == 1) {
                        $query->where('is_read', '=', 1);
                    } elseif ($isRead == 2) {
                        $query->where('is_read', '=', 0);
                    }
                };
                break;
            default:
                xThrow(ERR_PARAMETER);
        }

        $touch = ['creatorInfo' => function ($query) {
            $query->select([
                'id', 'profile_photo_url', 'name'
            ]);
        }];
        $touchCallback = function ($query) use ($touchColumns) {
            $query->select($touchColumns);
        };

        switch ($type) {
            case 1:
                $condition[] = ['related_type', '=', WorkflowRepository::RELATED_TYPE_DOCUMENT];
                $touch['documentFlowInfo'] = $touchCallback;
                break;
            case 2:
                $condition[] = ['related_type', '=', WorkflowRepository::RELATED_TYPE_LEAVE];
                $touch['leaveFlowInfo'] = $touchCallback;
                break;
            case 3:
                $condition[] = ['related_type', '=', 3];
                break;
            case 4:
                $condition[] = ['related_type', '=', 4];
                break;
            case 5:
                $condition[] = ['related_type', '=', 5];
                break;
            case 6:
                $condition[] = ['related_type', '=', 6];
                break;
            case 7:
                $condition[] = ['related_type', '=', 7];
                break;
            case 8:
                $condition[] = ['related_type', '=', 8];
                break;
            case 9:
                $condition[] = ['related_type', '=', 9];
                break;
            case 10:
                $condition[] = ['related_type', '=', 10];
                break;
            case 11:
                $condition[] = ['related_type', '=', 11];
                break;
            default:
                $condition[] = ['related_type', '=', $type];

        }

        $query = $this->model;
//x($query);
        //如果是采购申请或者退货申请，显示自己发起的 || 取消显示自己发起的20180524
        if(in_array($type,array(26,27))){
            $params = request(['keyword']);
            if($params['keyword']){
                //如果传了关键子，进行关键字筛选，目前只有搜索材料20180515
                $userId = \App\Engine\Func::getHeaderValueByName('userid');
                $userInfo = \App\Eloquent\Ygt\DepartmentUser::getCurrentInfo($userId)->toArray();
                $companyId = $userInfo['company_id'];

//                \DB::connection()->enableQueryLog(); // 开启查询日志
                if($type == 26){
                    //获取材料id
                    $materialsIds = \App\Eloquent\Ygt\PurchaseMaterial::where(['company_id'=>$companyId])->get()->pluck('material_id');
                    $materialsLikeIds = \App\Eloquent\Ygt\Product::where([['product_name','like', '%' . $params['keyword'] . '%']])->whereIn('id',$materialsIds)->get()->pluck('id');
                    $purchaseIds = \App\Eloquent\Ygt\PurchaseMaterial::whereIn('material_id',$materialsLikeIds)->get()->pluck('purchase_id');
//                    $query = $query->where(['related_type'=>'26'])->whereIn('related_id',$purchaseIds);

                    //搜索供应商
                    $SellCompanyIds = \App\Eloquent\Ygt\SellerCompany::where([['title','like', '%' . $params['keyword'] . '%']])->where('company_id',$companyId)->get()->pluck('id');
                    $purchaseIds2 = \App\Eloquent\Ygt\Purchase::whereIn('supplier_id',$SellCompanyIds)->get()->pluck('id');

                    $purchaseIds = array_merge($purchaseIds->toArray(),$purchaseIds2->toArray());

                    $query = $query->where(['related_type'=>'26'])->whereIn('related_id',$purchaseIds);

                }elseif($type == 27){
                    $materialsIds = \App\Eloquent\Ygt\ReturnPurchaseMaterial::where(['company_id'=>$companyId])->get()->pluck('material_id');
                    $materialsLikeIds = \App\Eloquent\Ygt\Product::where([['product_name','like', '%' . $params['keyword'] . '%']])->whereIn('id',$materialsIds)->get()->pluck('id');
                    $purchaseIds = \App\Eloquent\Ygt\ReturnPurchaseMaterial::whereIn('material_id',$materialsLikeIds)->get()->pluck('purchase_id');
//                    $query = $query->where(['related_type'=>'27'])->whereIn('related_id',$purchaseIds);

                    //搜索供应商
                    $SellCompanyIds = \App\Eloquent\Ygt\SellerCompany::where([['title','like', '%' . $params['keyword'] . '%']])->where('company_id',$companyId)->get()->pluck('id');
                    $purchaseIds2 = \App\Eloquent\Ygt\ReturnPurchase::whereIn('supplier_id',$SellCompanyIds)->get()->pluck('id');

                    $purchaseIds = array_merge($purchaseIds->toArray(),$purchaseIds2->toArray());

                    $query = $query->where(['related_type'=>'27'])->whereIn('related_id',$purchaseIds);

//                $queries = \DB::getQueryLog(); // 获取查询日志
//                print_r($queries); die();// 即可查看执行的sql，传入的参数等等
                }

//                $queries = \DB::getQueryLog(); // 获取查询日志
//                print_r($queries); die();// 即可查看执行的sql，传入的参数等等

            }else{
                if (!empty($condition)) $query = $query->where($condition);
//                $query = $query->orWhere([
//                    ['creator_id', '=', $contactsId],['related_type', '=', $type]
//                ]);



            }
        }else{
            if (!empty($condition)) $query = $query->where($condition);
        }

        //ygt_purchase
        //hjn 20190819 采购列表增加筛选
        if($type == 26 && in_array($status,[1,2,3])) {
            $query->join('ygt_purchase', 'ygt_purchase.id', '=', 'oa_workflow.related_id');

            switch ($status){
                case "1"://待入库
                    $purchase = [
                        'ygt_purchase.is_able_in'   =>  0,
                        'ygt_purchase.is_all_in'   =>  0,
                    ];
                    break;
                case "2"://入库中
                    $purchase = [
                        'ygt_purchase.is_able_in'   =>  1,
                        'ygt_purchase.is_all_in'   =>  0,
                        'oa_workflow.dispose_code'  =>  1002,
                    ];
                    break;
                case "3"://已入库
                    $purchase = [
                        'ygt_purchase.is_able_in'   =>  0,
                        'ygt_purchase.is_all_in'   =>  1,
                    ];

                    break;
            }

            $query->where(function($query)use ($status,$purchase){
                $query->where($purchase);
                if($status == 3)
                    $query->orWhere('oa_workflow.dispose_code','1100');
            });
        }

        if (!empty($touch)) $query = $query->with($touch);
        if ($relation && $relationCallback) $query = $query->whereHas($relation, $relationCallback);
        $totalQuery = clone $query;

        $query = $query->forPage($page, $pageSize);
        $query->orderBy('oa_workflow.created_at', 'desc'); //by lwl 2019 05 13 oa_workflow
        $data = $query->select('oa_workflow.*')->get($columns);
        return [$data, $totalQuery->count('oa_workflow.id')]; //by lwl 2019 05 13 oa_workflow
    }

    public function find($id, array $touch = [], array $columns = ['*'], array $touchColumns = ['*'])
    {
        $id = is_array($id) ? $id : ['id' => $id];

        $query = $this->model->where($id);
        if (!empty($touch)) $query = $query->with($touch);

        return $query->first($columns);
    }

}


if (!function_exists('request')) {
    /**
     * Get an instance of the current request or an input item from the request.
     *
     * @param  array|string $key
     * @param  mixed $default
     * @return \Illuminate\Http\Request|string|array
     */
    function request($key = null, $default = null)
    {
        if (is_null($key)) {
            return app('request');
        }

        if (is_array($key)) {
            return app('request')->only($key);
        }

        return data_get(app('request')->all(), $key, $default);
    }
}