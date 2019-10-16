<?php
/**
 * Created by PhpStorm.
 * Author: zhuyujun
 * Date: 2019/3/27
 * Time: 17:10
 * Desc: 仓库相关通用方法
 */

namespace App\Engine;
use App\Eloquent\Ygt\DepartmentUser;
use App\Engine\Func;
class Storehouse
{

    public $materialId = 0;
    public $declareNumber = 0;
    public $resType = 0;


    public function insAvailableNumber(){
        return 1;
    }

    //可用库存操作
    //$resType 1、集合材料 | 材料
    public function declareNumber($materialId,$declareNumber,$resType,$setType="-",$storehouse_id=0){

        $default_storehouse_id = $storehouse_id;
        if(!$storehouse_id){
            $userId = Func::getHeaderValueByName('userid');
            $userInfo = DepartmentUser::getCurrentInfo($userId)->toArray();
            $companyId = $userInfo['company_id'];

            //20190403 多仓库仓管消息调整
            //获取企业的默认仓库
            $default_storehouse_id = \App\Api\Service\Storehouse\Storehouse\Storehouse::getCompanyDefaultStorehouse($companyId)->getId();
        }

        $this->materialId = $materialId;
        $this->declareNumber = $declareNumber;
        $this->resType = $resType;

        switch ($resType){
            case "1":
                if(strstr($materialId,'A')){
                    $resType = 5;
                    $materialId = str_replace('A','',$materialId);
                }
                break;
        }
        $where = [
            'res_type'=>  $resType,
            'res_id'  =>  $materialId,
            'storehouse_id' =>  $default_storehouse_id
        ];

        $StorehouseResRow = \App\Eloquent\Ygt\StorehouseRes::where($where)->first();

        if($setType == "-"){
            $saveData['available_number'] = $StorehouseResRow->available_number - $declareNumber;
        }else{
            $saveData['available_number'] = $StorehouseResRow->available_number + $declareNumber;
        }



        $StorehouseResRow->fill($saveData);
        $StorehouseResRow->save();

        //材料sku操作数量时 同步操作集合材料数量
        if($resType == 1){
            $assemblageMaterialId = \App\Eloquent\Ygt\Product::where(['id'=>$materialId])->value('assemblage_material_id');
            if($assemblageMaterialId){
                $where = [
                    'res_type'=>  5,
                    'res_id'  =>  $assemblageMaterialId,
                    'storehouse_id' =>  $default_storehouse_id
                ];

                $StorehouseResRow = \App\Eloquent\Ygt\StorehouseRes::where($where)->first();
                if($setType == "-"){
                    $saveData['available_number'] = $StorehouseResRow->available_number - $declareNumber;
                }else{
                    $saveData['available_number'] = $StorehouseResRow->available_number + $declareNumber;
                }
                $StorehouseResRow->fill($saveData);
                $StorehouseResRow->save();

            }
        }
        self::exception($where);

        return $this;

    }

    public static function exception($info){
        $uid = \App\Engine\Func::getHeaderValueByName('userid');
        $userInfo = \App\Eloquent\Ygt\DepartmentUser::getCurrentInfo($uid)->toArray();

        $abnormalRuleParameterId = \App\Eloquent\Ygt\AbnormalRultParameter::where('name','=','可用库存')
            ->pluck('id')->first();
        $abnormalFieldNameArr = \App\Eloquent\Ygt\AbnormalField::where('field_value','=',$abnormalRuleParameterId)
            ->where(function ($query) use ($info){
                if ($info['res_type'] == 1 OR $info['res_type']  == 5){//材料 || 集合材料
                    $query->where('ygt_abnormal_field.field_type','=','material');
                }else if ($info['res_type'] == 2){//半成品
                    $query->where('ygt_abnormal_field.field_type','=','product_aggretage');
                }else if ($info['res_type'] == 3){//成品
                    $query->where('ygt_abnormal_field.field_type','=','product');
                }else if ($info['res_type'] == 6){//退品
                    $query->where('ygt_abnormal_field.field_type','=','return_product');
                }
            })
            ->pluck('field_name')->toArray();

        $abnormalArr = \App\Eloquent\Ygt\Abnormal::leftJoin('ygt_abnormal_user','ygt_abnormal_user.abnormal_id','=','ygt_abnormal.id')
            ->where('company_id','=',$userInfo['company_id'])
            ->where('ygt_abnormal.rule','like','%可用库存%')
            ->where(function ($query) use ($info){
                if ($info['res_type'] == 1 OR $info['res_type']  == 5){//材料 || 集合材料
                    $query->where('ygt_abnormal.type','=','material');
                }else if ($info['res_type'] == 2){//半成品
                    $query->where('ygt_abnormal.type','=','product_aggretage');
                }else if ($info['res_type'] == 3){//成品
                    $query->where('ygt_abnormal.type','=','product');
                }else if ($info['res_type'] == 6){//退品
                    $query->where('ygt_abnormal.type','=','return_product');
                }
            })
            ->where(function ($query) use ($abnormalFieldNameArr){
                if (!empty($abnormalFieldIdArr)){
                    foreach ($abnormalFieldIdArr as $value){
                        $query->where('ygt_abnormal.rule','like',"%$value%");
                    }
                }
            })
            ->select(['ygt_abnormal.*','ygt_abnormal_user.gte_number','ygt_abnormal_user.lte_number','ygt_abnormal_user.uid','ygt_abnormal_user.abnormal_id'])->get()->toArray();

        if (empty($abnormalArr)) return false;

        foreach ($abnormalArr as $value){
            $tips = '';
            $intro = '';
            $type = config('abnormal');
            $top  = \App\Eloquent\Ygt\AbnormalType::where('id','=',$value['abnormal_type_id'])->pluck('sort')->first();
            $title = "可用库存-".$type[$top][$value['type']];
            $where = [];

            if ($info['res_type'] == 1 OR $info['res_type'] == 6){//领取材料   OR    退品
                if ($info['res_type'] == 6){//如果为退品
                    $returnMaterialId = \App\Eloquent\Ygt\MaterialRetreat::where('id','=',$info['res_id'])->pluck('material_id')->first();
                    $category_id = \App\Eloquent\Ygt\Product::where('id', '=', $returnMaterialId)->pluck('category_id')->first();
                    $materialInfo = \App\Eloquent\Ygt\Product::leftJoin('ygt_seller_company', 'ygt_seller_company.id', '=', 'ygt_product.seller_company_id')
                        ->Where('ygt_product.id','=',$returnMaterialId)
                        ->select(['product_name', 'title'])->first()->toArray();
                }elseif ($info['res_type'] == 1){//如果是材料
                    $category_id = \App\Eloquent\Ygt\Product::where('id', '=', $info['res_id'])->pluck('category_id')->first();
                    $materialInfo = \App\Eloquent\Ygt\Product::leftJoin('ygt_seller_company', 'ygt_seller_company.id', '=', 'ygt_product.seller_company_id')
                        ->Where('ygt_product.id','=',$info['res_id'])
                        ->select(['product_name', 'title'])->first()->toArray();
                }
                if (!checkMaterial($category_id, $value['id'])) {
                    continue;
                }//检测材料是否在异常设置的范围中
            }
            else if ($info['res_type'] == 5){//集合材料
                $category_id = \App\Eloquent\Ygt\AssemblageMaterial::where('id', '=', $info['res_id'])->pluck('category_id')->first();
                $materialInfo = \App\Eloquent\Ygt\AssemblageMaterial::where('id', '=', $info['res_id'])->first()->toArray();
                if (!checkMaterial($category_id, $value['id'])) {
                    continue;
                }//检测材料是否在异常设置的范围中
            }
            else if ($info['res_type'] == 2){//半成品
                $materialInfo = \App\Eloquent\Ygt\ProcessProduct::where('id', '=', $info['res_id'])->first()->toArray();
                //检测范围
                if ($value['relation_id'] != "product_aggretage_all") {
                    if (!in_array($info['res_id'], $value['relation_id'])) {
                        continue;
                    }
                }
            }
            else if ($info['res_type'] == 3){//成品
                $productInfo = \App\Eloquent\Ygt\Warehouse::where('id','=',$info['res_id'])->first()->toArray();//获取成品详情
                if ($value['relation_id'] != "product_all") {
                    $orderTypeCategoryId = \App\Eloquent\Ygt\OrdertypeCategory::leftJoin('ygt_order_type', 'ygt_order_type.category_id', '=', 'ygt_ordertype_category.id')
                        ->leftJoin('ygt_order','ygt_order.order_type','=','ygt_order_type.id')
                        ->leftJoin('ygt_warehouse','ygt_warehouse.order_id','=','ygt_order.id')
                        ->where('ygt_warehouse.id', '=', $info['res_id'])
                        ->where('ygt_order_type.company_id','=',$userInfo['company_id'])
                        ->pluck('ygt_ordertype_category.id')->first();
                    if (!in_array($orderTypeCategoryId,explode(',',$value['relation_id']))){
                        continue;//检测成品是否在 异常设置的范围中
                    }
                }
            }
            if ($value['field_id']) {
                $where['material_id'] = $info['res_id'];
                $where['res_type'] = $info['res_type'];
                if ($info['res_type'] == 3){//成品调库 需 要 仓库id
                    $where['storehouse_id'] = $info['storehouse_id'];
                }else{//其他类型时  仓库id   Arr
                    $where['stockArr'][] = $info['storehouse_id'];
                }
                if (ruleComparison($where, $value['rule'], $value)) {//触发异常
                    $baseline = ReturnRuleFormula($where, $value['rule']);
                    //领取材料  退品  集合材料
                    if ($info['res_type'] == 1 OR $info['res_type'] == 6 OR $info['res_type'] == 5){
                        $tips .= "# " . $materialInfo['product_name'] . "（";
                        if (isset($materialInfo['title'])) {
                            $tips .= $materialInfo['title'];
                        } else {
                            $tips .= "集合材料";
                        }
                        $tips .= "）" . " 可用库存为【" . $baseline . "】rnrn";
                    }
                    //半成品
                    else if ($info['res_type'] == 2){
                        $tips .= "# " . $materialInfo['title'] . "（" . $materialInfo['product_no'] . "）" . " 可用库存为【" . $baseline . "】rnrn";
                    }
                    //成品
                    else if ($info['res_type'] == 3){
                        $tips .= "# ".$productInfo['product_name']."（".$productInfo['product_no']. "）可用库存为【".$baseline."】rnrn";
                    }


                    //生成异常消息提示
                    if (empty($tips)) {
                        continue;
                    } else {
                        $intro .= $tips;
                    }
                    $intro .= "rnrn当前预警设置参数";
                    if ($value['gte_number'])
                        $intro .= "rnrn 大于：" . $value['gte_number'];

                    if ($value['lte_number'])
                        $intro .= "rnrn 小于：" . $value['lte_number'];
                    $sendSmsData1[] = [
                        'uid' => $value['uid'],
                        'title' => $title,
                        'intro' => $intro,
                        'abnormal_id' => $value['abnormal_id'],
                        'created_at' => time(),
                        'updated_at' => time(),
                        'is_see' => 0
                    ];
                    if (!empty($tips)) {
                        \App\Eloquent\Ygt\AbnormalUserMessage::insert($sendSmsData1);
                    }
                }
            }
        }



    }



    //获取企业相关
    public static function getDefaultStorehouse($companyId){


        $where = [
            'company_id' => $companyId,
            'is_default' => '1',
        ];
        $storehouseRow = \App\Eloquent\Ygt\Storehouse::where($where)->first();
        if($storehouseRow){
            return $storehouseRow['id'];
        }

        return false;
    }



    public static function calculateMaterialAmount($param,$MaterialList){

        $set_material_formula_obj =  new \App\Eloquent\Ygt\SetMaterialFormula();
        $set_material_formula_obj->GetOrderValue($param['order_id']);


        foreach ($MaterialList as $tmpMaterialRow){
            $assemblage_material_id = 0;
            $material_id = $tmpMaterialRow['material_id'];//集合材料ID
            if(strstr($tmpMaterialRow['material_id'],'A')){
                $assemblage_material_id = str_replace('A','',$tmpMaterialRow['material_id']);
            }else{
                //获取sku材料ID
                $product_row = \App\Eloquent\Ygt\Product::find($material_id);
                if($product_row){
                    $assemblage_material_id = $product_row['assemblage_material_id'];
                }
            }

            //统计
            $process_id = $tmpMaterialRow['process_type'];
            $formula = $set_material_formula_obj->returnFormula($process_id,$assemblage_material_id);

            /*功能块：计算待采购数量需减去下单时的半成品选择数量 zhuyujun 20190624*/
            $select_process_product_number = 0;//下单选择半成品的数量（如果有多个取最小值）
            $tmp_order_process_id = $tmpMaterialRow['order_process_id'];
            $tmp_order_process_row = \App\Eloquent\Ygt\OrderProcess::find($tmp_order_process_id);

            //获取工序里是半成品的字段
            $field_list = \App\Engine\OrderEngine::getOrderProcessFieldByType($param['company_id'],22);
            foreach ($field_list as $field_row){
                if(isset($tmp_order_process_row[$field_row['field_name']]) && $tmp_order_process_row[$field_row['field_name']] ){
                    $tmpCreateOrderProcessProductRow = \App\Eloquent\Ygt\CreateOrderProcessProduct::where(['id'=>$tmp_order_process_row[$field_row['field_name']]])->first();
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

            $cur_need_puchase_number = ($param['order_num'] - $select_process_product_number) * $formula;
            //如果没有计算出来待采购数量，默认为1，用于标记待采购
            if($cur_need_puchase_number <= 0) $cur_need_puchase_number = 1;
            $material[$material_id] = $cur_need_puchase_number;

        }

        return $material;

    }






}