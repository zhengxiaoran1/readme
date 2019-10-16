<?php
/**
 * Created by PhpStorm.
 * User: zhuyujun
 * Date: 2019/04/23
 * Time: 11:14
 */

namespace App\Engine;

class Purchase

{
    /**
     * 采购单数据转换为采购单通用格式
     */
    public static function dealPurchaseList($purchase_list,$waite_purchase_aggregate_id=0){
        $re = [];
        foreach ($purchase_list as $purchase_row){
            $purchaseId = $purchase_row['id'];
            $purchaseRow = $purchase_row->toArray();

            //获取相关的审批流程数据
            $where = [];
            $where[] = ['related_type','=',26];
            $where[] = ['related_id','=',$purchaseId];
            $workflow = \App\Eloquent\Oa\Workflow::where($where)->first();

            $contactsId = 0;
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

            //处理相关数据
            $purchaseMaterialList = \App\Eloquent\Ygt\PurchaseMaterial::where(['purchase_id' => $purchaseId])->get()->toArray();
            $materialNameListStr = '';
            $imageList = [];

            $purchaseMoney = 0;

            //采购数量
            $purchase_num = "";
            $purchase_in_num = "";
            $material_id = 0;
            if($waite_purchase_aggregate_id){
                //获取对应的材料ID
                $where = [];
                $where[] = ['id','=',$waite_purchase_aggregate_id];
                $waite_purchase_aggregate_row = \App\Eloquent\Ygt\WaitePurchaseAggregate::where($where)->first();
                if($waite_purchase_aggregate_row){
                    $material_id = $waite_purchase_aggregate_row->material_id;
                    //获取采购数量
                }
            }

            foreach ($purchaseMaterialList as $purchaseMaterialRow) {
                //获取材料名称
                $materialRow = \App\Engine\Product::getProductInfo($purchaseMaterialRow['material_id']);

                //追加材料图片地址
                if ($materialRow['img_id']) {
                    $imageList[] = \App\Eloquent\Ygt\ImgUpload::getImgUrlById($materialRow['img_id']);
                }

                //新增采购金额
                $purchaseMoney += $purchaseMaterialRow['num'] * $purchaseMaterialRow['price'];

                //统计采购数量
                if($material_id && ($purchaseMaterialRow['material_id'] == $material_id) ){
                    $purchase_num = "采购:{$purchaseMaterialRow['total_number']}({$materialRow['unit']})";
                    $purchase_in_num = "已入库:{$purchaseMaterialRow['in_number']}({$materialRow['unit']})";
                }


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
                //2019-07-19 , 取消采购单号
                'purchase_number' =>  YgtLabel::getLabel(YgtLabel::$LABEL_WAIT_PURCHASE).$purchaseRow['purchase_number'],//采购编号
                'create_date' => date('Y-m-d', $purchaseRow['created_at']),//生成日期
                'finished_date' => "交货日期:".$purchaseRow['finished_date'],//交货日期
                'status' => $workflow['status'],//状态值
                'status_str' => $workflow['status_str'],//状态提示
                'is_warning' => 0,//状态提示
                'image_list' => $imageList,//图片列表
                'purchase_money' => "采购金额：¥".$purchaseMoney,//金额
                'purchase_num' => $purchase_num,//采购数量
                'purchase_in_num' => $purchase_in_num,//已入库数量
            ];

            $workflow['content'] = $content;
            $re[] = $workflow;
        }

        return $re;
    }


    /**
     * 获取采购员（非主管）的负责的材料
     * 包括集合材料和sku材料
     */
    public static function getPurchaseAuthorityList($userId,$companyId){

        $manage_assemblage_material_id_list = [];//有采购权限的集合材料
        $manage_material_id_list = [];//有采购权限的sku材料
        //获取配置的材料分类
        $purchase_manage_row = \App\Eloquent\Ygt\PurchaseManage::where(['company_id'=>$companyId,'uid'=>$userId])->first();
        if($purchase_manage_row['category_ids']){
            $category_id_list = explode(',',$purchase_manage_row['category_ids']);
            $manage_assemblage_material_id_list = \App\Eloquent\Ygt\AssemblageMaterial::whereIn('category_id',$category_id_list)->get()->pluck('id')->toArray();
            $manage_material_id_list = \App\Eloquent\Ygt\Product::whereIn('category_id',$category_id_list)->get()->pluck('id')->toArray();
        }

        return [
            'manage_assemblage_material_id_list' =>$manage_assemblage_material_id_list,
            'manage_material_id_list' =>$manage_material_id_list,
        ];

    }

}