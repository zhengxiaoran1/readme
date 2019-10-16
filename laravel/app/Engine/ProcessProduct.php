<?php
/**
 * Created by PhpStorm.
 * Author: zhuyujun
 * Date: 2019/05/28
 * Time: 15:45
 */

namespace App\Engine;


use App\Eloquent\Ygt\ChanpinOrderInfo;

class ProcessProduct
{

    //获取半成品的相关信息
    //目前半成品品名（型号），工序，主要用材，
    public static function getProcessProductInfoByID($process_prodcut_id){

        $re = [];
        //半成品的基础信息
        $process_prodcut_title = $unit = '';
        $ordertype_process_id = 0;//工序工单ID，目前用来查主要用材
        $process_product_row = \App\Eloquent\Ygt\ProcessProduct::find($process_prodcut_id);

        if($process_product_row){
            $process_prodcut_title = $process_product_row['title'];
            $unit  = $process_product_row['unit'] ? $process_product_row['unit'] : '';
            $ordertype_process_id  = $process_product_row['ordertype_process_id'];
        }

        $re['title'] = $process_prodcut_title;
        $re['unit'] = $unit;



        //获取半成品的品名型号，片料规格，袋类工序，主要用材
        $product_name = $product_model = $chip_specification = $order_type_title = $main_material =  '';
        if($process_product_row['buyer_product_title']) $product_name = $process_product_row['buyer_product_title'];
        if($process_product_row['product_model_title']) $product_model = $process_product_row['product_model_title'];

        $order_process_product_row = \App\Eloquent\Ygt\OrderProcessProduct::where(['process_product_id'=>$process_prodcut_id])->first();
        if($order_process_product_row){
            $order_id = $order_process_product_row['order_id'];
            $order_row = \App\Eloquent\Ygt\Order::find($order_id);

            $order_type_title = \App\Engine\OrderType::getOneValueById($order_row['order_type'], 'title');
            $product_name  = \App\Engine\OrderEngine::getOrderFiledValueTrue($order_row['product_name'], 20);
            $chip_specification = \App\Engine\OrderEngine::getOrderFiledValueTrue($order_row['chip_specification_length'], 15);


            //品名型号
            $tmpCreateOrderProductNameRow = \App\Eloquent\Ygt\CreateOrderExtend::where(['id'=>$order_row['product_name']])->first();
            $productNameInfo = json_decode(htmlspecialchars_decode($tmpCreateOrderProductNameRow['content']), true);;
            if(!empty($productNameInfo['model_list'])){
                foreach ($productNameInfo['model_list'] as $modelRow){
                    $product_model = $modelRow['model_name'];
                }
            }
            if($productNameInfo['chanpin_id']){
                $ChanpinOrderInfo = ChanpinOrderInfo::where(['id'=>$productNameInfo['chanpin_id']])->first();
                if($ChanpinOrderInfo['xh']) $product_model = $ChanpinOrderInfo['xh'];
                if($ChanpinOrderInfo['pm']) $product_name = $ChanpinOrderInfo['pm'];
            }
            //主要用材
            if($ordertype_process_id){
                //从工序工单中获取
                $where = [];
                $where['order_id'] = $order_id;
                $where['ordertype_process_id'] = $ordertype_process_id;
                $tmp_order_process_row = \App\Eloquent\Ygt\OrderProcess::where($where)->first();

                //配置的主要用材
                $where = [];
                $where['process_ordertype_id'] = $ordertype_process_id;
                $tmp_ordertype_process_main_material_row = \App\Eloquent\Ygt\OrdertypeProcessMainMaterial::where($where)->first();
                if($tmp_ordertype_process_main_material_row) {
                    $tmp_field_id_list_str = $tmp_ordertype_process_main_material_row['main_material'];
                    if ($tmp_field_id_list_str) {
                        $tmp_field_id_list = explode(',', $tmp_field_id_list_str);
                        $where = [];
                        $where['company_id'] = $order_process_product_row['company_id'];
                        $tmp_field_list = \App\Eloquent\Ygt\ProcessFieldCompany::where($where)->whereIn('field_id', $tmp_field_id_list)->get();
                        foreach ($tmp_field_list as $tmp_field_row) {
                            if (isset($tmp_order_process_row[$tmp_field_row['field_name']]) && $tmp_order_process_row[$tmp_field_row['field_name']]) {
                                //获取材料对应的信息
                                $material_id_list = explode(',', $tmp_order_process_row[$tmp_field_row['field_name']]);

                                $materialList = [];
                                foreach ($material_id_list as $materialId) {
                                    //考虑集合材料的问题
                                    if (strstr($materialId, 'A')) {
                                        $tmp_assemblage_material_id = str_replace('A', '', $materialId);
                                        $material_row = \App\Eloquent\Ygt\AssemblageMaterial::withTrashed()->where(['id' => $tmp_assemblage_material_id])->first();
                                        if ($material_row) {
                                            $main_material .= $material_row['product_name'].",";
                                        }
                                    } else {
                                        $material_row = \App\Engine\Product::getProductInfo($materialId);
                                        if ($material_row) {
                                            $main_material .= $material_row['product_name'].",";
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        //过滤多余的,
        if($main_material){
            $main_material = rtrim($main_material,',');
        }

        $re['buyer_name'] = $product_name;
        $re['plate_name'] = $product_name;
        $re['plate_model'] = $product_model;
        $re['chip_specification'] = $chip_specification;
        $re['order_type_title'] = $order_type_title;
        $re['main_material'] = $main_material;

        return $re;
    }

    //获取工序对应的半成品列表
    //如果未创建对应的半成品就新建
    //获取工序工单对应的半成品
    static public function getOrderProcessProcessProductList($order_row,$order_process_row){
        $process_product_list = [];
        /*先统一获取可能需要的值*/
        /*1、工序名称*/
        $ordertype_process_id = $order_process_row['ordertype_process_id'];
        $process_ordertype_row = \App\Eloquent\Ygt\ProcessOrdertype::find($ordertype_process_id);
        //$order_type_id = $process_ordertype_row['ordertype_id'];//工序ID
        $order_type_id = $process_ordertype_row['process_id'];//工序ID
//        $order_type_row = \App\Eloquent\Ygt\OrderType::find($order_type_id);
        //$order_type_title = $order_type_row->title;//工序名称
        //获取工序名称 通过id
        $order_type_title = \App\Eloquent\Ygt\Process::getOneValueById($order_type_id,'title');

        /* 2、产品名称【品名，品名型号】*/
        $buyer_product_id = 0;//品名ID
        $buyer_product_title = '';//品名名称
        $product_model_id = 0;//品名型号ID
        $product_model_title = '';//品名型号名称
        $create_order_product_name_id = $order_row['product_name'];
        $create_order_extend_row = \App\Eloquent\Ygt\CreateOrderExtend::where(['id'=>$create_order_product_name_id])->first();
        $create_order_extend_content = json_decode(htmlspecialchars_decode($create_order_extend_row['content']),true);
        $buyer_product_id = isset($create_order_extend_content['product_name_id']) ? $create_order_extend_content['product_name_id'] : 0;//品名ID
        $buyer_product_row = \App\Eloquent\Ygt\BuyersProduct::where(['id'=>$buyer_product_id])->first();


        if ($buyer_product_row) {
            $buyer_product_title = $buyer_product_row['name'];//品名名称
            if(isset($create_order_extend_content['model_list'])){
                if(isset($create_order_extend_content['model_list'][0])){
                    $product_model_id = $create_order_extend_content['model_list'][0]['model_id'];//品名型号ID
                    $product_model_title = $create_order_extend_content['model_list'][0]['model_name'];//品名型号名称
                }
            }
        }
        if(!$buyer_product_title || !$product_model_title){
            //选择半成品时品名不现实问题 hjn 20190911
            $ChanpinOrderInfo = ChanpinOrderInfo::where(['id'=>$create_order_extend_content['chanpin_id']])->first();
            if($ChanpinOrderInfo){
                $buyer_product_title = $ChanpinOrderInfo->pm?$ChanpinOrderInfo->pm:$buyer_product_title;
                $product_model_title = $ChanpinOrderInfo->xh?$ChanpinOrderInfo->xh:$product_model_title;
            }
        }



        /*2.5、产品编号*/
        $product_number = $order_row['weaving_type'] ? $order_row['weaving_type'] : '';

        /*3、主要用材*/
        $order_process_main_material_list = \App\Engine\OrderEngine::getOrderProcessMainMaterial($order_process_row);
        $main_material_id_list = [];
        $main_material_list = [];
        foreach ($order_process_main_material_list as $main_material_row){
            $main_material_id_list[] = $main_material_row['id'];
            $main_material_list[] = $main_material_row['title'];
        }
        $main_material_id = implode(',',$main_material_id_list);//多个材料ID用','隔开
        $main_material = implode(',',$main_material_list);//材料名称

        /*4、客户名称*/
        $customer_title = '';
        $customer_id = $order_row['customer_name'] ? $order_row['customer_name'] : '';
        if($customer_id){
            $customer_row = \App\Eloquent\Ygt\Customer::find($customer_id);
            if($customer_row){
                $customer_title = $customer_row['customer_name'];
            }
        }

        /*5、单位名称*/
        $buyer_id = $order_row['field_name_23'] ? $order_row['field_name_23'] : '';
        $buyer_title = \App\Engine\Buyers::getNameById($buyer_id);

        /*6、片料规格*/
        $chip_specification = $order_row['chip_specification_length'];

        /*7、成品规格*/
        $finished_specification = $order_row['finished_specification'];



        $where = [];
        $where[] = ['ordertype_process_id','=',$ordertype_process_id];
        $process_product_aggregate_list = \App\Eloquent\Ygt\ProcessProductAggregate::where($where)->get();
        foreach ($process_product_aggregate_list as $process_product_aggregate_row){

            $select_attribute = $process_product_aggregate_row->select_attribute;
            //判断半成品sku是否存在
            $where = [];
            $where['ordertype_process_id'] = $process_product_aggregate_row['ordertype_process_id'];
            $where['company_id'] = $process_product_aggregate_row['company_id'];
            $where['process_product_aggregate_id'] = $process_product_aggregate_row['id'];
            $tmp_process_product_row = \App\Eloquent\Ygt\ProcessProduct::where($where);
            $select_attribute_list = explode(',',$select_attribute);

            //匹配半成品逻辑修复 zhuyujun 20190613
//            foreach ($select_attribute_list as $select_attribute_row){
//                if(!$select_attribute_row){//过滤异常
//                    continue;
//                }
//
//                if($select_attribute_row == 'buyer_product_id'){//品名特殊处理下，因为还有品名型号
//                    $where = [];
//                    $where['buyer_product_id'] = $buyer_product_id;
//                    $where['product_model_id'] = $product_model_id;
//                    $tmp_process_product_row = $tmp_process_product_row->where($where);
//                }else{
//                    $where = [];
//                    $where[$select_attribute_row] = $$select_attribute_row;
//                    $tmp_process_product_row = $tmp_process_product_row->where($where);
//                }
//            }
            $select_attribute_list_o = config('process-product.select_attribute');
            foreach ($select_attribute_list_o as $select_attribute_field_o => $select_attribute_title_o){
                if(!$select_attribute_field_o){//过滤异常
                    continue;
                }

                if($select_attribute_field_o == 'buyer_product_id') {//品名特殊处理下，因为还有品名型号
                    if(in_array($select_attribute_field_o,$select_attribute_list)){//配置了这个属性
                        $where = [];
                        $where['buyer_product_id'] = $buyer_product_id;
                        $where['product_model_id'] = $product_model_id;
                        $tmp_process_product_row = $tmp_process_product_row->where($where);
                    }else{//未配置这个属性
                        $where = [];
                        $where['buyer_product_id'] = 0;
                        $where['product_model_id'] = 0;
                        $tmp_process_product_row = $tmp_process_product_row->where($where);
                    }
                }else{
                    if(in_array($select_attribute_field_o,$select_attribute_list)){//配置了这个属性
                        $where = [];
                        $where[$select_attribute_field_o] = $$select_attribute_field_o;
                        $tmp_process_product_row = $tmp_process_product_row->where($where);

                    }else{//未配置这个属性
                        $where = [];
                        $where[$select_attribute_field_o] = 0;
                        $tmp_process_product_row = $tmp_process_product_row->where($where);

                    }
                }

            }





            $tmp_process_product_row = $tmp_process_product_row->first();


            if($tmp_process_product_row){
                $process_product_list[] = $tmp_process_product_row->toArray();
            }else{
                //创建新的半成品
                $tmp_process_product_row = new \App\Eloquent\Ygt\ProcessProduct;
                //通用属性
                $tmp_process_product_row->title = $process_product_aggregate_row->title;//标题
                $tmp_process_product_row->ordertype_process_id = $process_product_aggregate_row->ordertype_process_id;//工序工单ID
                $tmp_process_product_row->company_id = $process_product_aggregate_row->company_id;//企业ID
                $tmp_process_product_row->process_product_aggregate_id = $process_product_aggregate_row->id;//半成品集合ID
                $tmp_process_product_row->select_attribute = $process_product_aggregate_row->select_attribute;//半成品配置的属性

                $tmp_process_product_row->process_id = $order_process_row['process_type'];//关联的工序ID

                $company_id = $process_product_aggregate_row->company_id;
                $product_no = \App\Engine\Sn::createProcessProductProductNo($company_id);
                $tmp_process_product_row->product_no = $product_no;//半成品编号

                $measurement_unit = \App\Eloquent\Ygt\Process::getOneValueById($order_process_row['process_type'], 'measurement_unit');
                if(!$measurement_unit){
                    $measurement_unit = '';
                }
                $tmp_process_product_row->unit = $measurement_unit;//半成品编号


                //通过设置的属性，关联属性到半成品sku
                foreach ($select_attribute_list as $select_attribute_row){
                    if($select_attribute_row == 'order_type_id'){
                        $tmp_process_product_row->order_type_id = $order_type_id;
                        $tmp_process_product_row->order_type_title = $order_type_title;
                    }elseif($select_attribute_row == 'buyer_product_id'){
                        $tmp_process_product_row->buyer_product_id = $buyer_product_id;
                        $tmp_process_product_row->buyer_product_title = $buyer_product_title;
                        $tmp_process_product_row->product_model_id = $product_model_id;
                        $tmp_process_product_row->product_model_title = $product_model_title;
                    }elseif($select_attribute_row == 'product_number'){
                        $tmp_process_product_row->product_number = $product_number;
                    }elseif($select_attribute_row == 'main_material_id'){
                        $tmp_process_product_row->main_material_id = $main_material_id;
                        $tmp_process_product_row->main_material = $main_material;
                    }elseif($select_attribute_row == 'customer_id'){
                        $tmp_process_product_row->customer_id = $customer_id;
                        $tmp_process_product_row->customer_title = $customer_title;
                    }elseif($select_attribute_row == 'buyer_id'){
                        $tmp_process_product_row->buyer_id = $buyer_id;
                        $tmp_process_product_row->buyer_title = $buyer_title;
                    }elseif($select_attribute_row == 'chip_specification'){
                        $tmp_process_product_row->chip_specification = $chip_specification;
                    }elseif($select_attribute_row == 'finished_specification'){
                        $tmp_process_product_row->finished_specification = $finished_specification;
                    }
                }

                $tmp_process_product_row->save();
                $process_product_list[] = $tmp_process_product_row->toArray();
            }
        }

        return $process_product_list;
    }



    //获取工序对应的半成品（目前用于创建工单时选择半成品） zhuyujun 20190624
    static public function getRelateProcessProductList($relate_data){
        $process_product_id_list = [];
        /*先统一获取可能需要的值*/
        /*1、工序名称*/
        $ordertype_process_id = $relate_data['ordertype_process_id'];
        $process_ordertype_row = \App\Eloquent\Ygt\ProcessOrdertype::find($ordertype_process_id);
        $order_type_id = $relate_data['ordertype_id'];//工序ID
        $order_type_row = \App\Eloquent\Ygt\OrderType::find($order_type_id);
        $order_type_title = $order_type_row->title;//工序名称

        /* 2、产品名称【品名，品名型号】*/
        $buyer_product_id = 0;//品名ID
        $buyer_product_title = '';//品名名称
        $product_model_id = 0;//品名型号ID
        $product_model_title = '';//品名型号名称
        $create_order_product_name_id = $relate_data['product_name'];
        $create_order_extend_row = \App\Eloquent\Ygt\CreateOrderExtend::where(['id'=>$create_order_product_name_id])->first();
        $create_order_extend_content = json_decode(htmlspecialchars_decode($create_order_extend_row['content']),true);
        $buyer_product_id = isset($create_order_extend_content['product_name_id']) ? $create_order_extend_content['product_name_id'] : 0;//品名ID
        $buyer_product_row = \App\Eloquent\Ygt\BuyersProduct::where(['id'=>$buyer_product_id])->first();
        if ($buyer_product_row) {
            $buyer_product_title = $buyer_product_row['name'];//品名名称
            if(isset($create_order_extend_content['model_list'])){
                if(isset($create_order_extend_content['model_list'][0])){
                    $product_model_id = $create_order_extend_content['model_list'][0]['model_id'];//品名型号ID
                    $product_model_title = $create_order_extend_content['model_list'][0]['model_name'];//品名型号名称
                }
            }
        }



        /*2.5、产品编号*/
        $product_number = $relate_data['weaving_type'] ? $relate_data['weaving_type'] : '';

        /*3、主要用材*/
        $order_process_main_material_list = \App\Engine\OrderEngine::getOrderProcessMainMaterial($relate_data['order_process_row']);
        $main_material_id_list = [];
        $main_material_list = [];
        foreach ($order_process_main_material_list as $main_material_row){
            $main_material_id_list[] = $main_material_row['id'];
            $main_material_list[] = $main_material_row['title'];
        }
        $main_material_id = implode(',',$main_material_id_list);//多个材料ID用','隔开
        $main_material = implode(',',$main_material_list);//材料名称

        /*4、客户名称*/
        $customer_title = '';
        $customer_id = $relate_data['customer_name'] ? $relate_data['customer_name'] : '';
        if($customer_id){
            $customer_row = \App\Eloquent\Ygt\Customer::find($customer_id);
            if($customer_row){
                $customer_title = $customer_row['customer_name'];
            }
        }

        /*5、单位名称*/
        $buyer_id = $relate_data['field_name_23'] ? $relate_data['field_name_23'] : '';
        $buyer_title = \App\Engine\Buyers::getNameById($buyer_id);

        /*6、片料规格*/
        $chip_specification = $relate_data['chip_specification_length'];

        /*7、成品规格*/
        $finished_specification = $relate_data['finished_specification'];


        $where = [];
        $where[] = ['ordertype_process_id','=',$ordertype_process_id];
        $process_product_aggregate_list = \App\Eloquent\Ygt\ProcessProductAggregate::where($where)->get();
        foreach ($process_product_aggregate_list as $process_product_aggregate_row) {

            $select_attribute = $process_product_aggregate_row->select_attribute;
            //判断半成品sku是否存在
            $where = [];
            $where['ordertype_process_id'] = $process_product_aggregate_row['ordertype_process_id'];
            $where['company_id'] = $process_product_aggregate_row['company_id'];
            $where['process_product_aggregate_id'] = $process_product_aggregate_row['id'];
            $tmp_process_product_row = \App\Eloquent\Ygt\ProcessProduct::where($where);
            $select_attribute_list = explode(',', $select_attribute);

            $select_attribute_list_o = config('process-product.select_attribute');
            foreach ($select_attribute_list_o as $select_attribute_field_o => $select_attribute_title_o) {
                if (!$select_attribute_field_o) {//过滤异常
                    continue;
                }

                if ($select_attribute_field_o == 'buyer_product_id') {//品名特殊处理下，因为还有品名型号
                    if (in_array($select_attribute_field_o, $select_attribute_list)) {//配置了这个属性
                        $where = [];
                        $where['buyer_product_id'] = $buyer_product_id;
                        $where['product_model_id'] = $product_model_id;
                        $tmp_process_product_row = $tmp_process_product_row->where($where);
                    } else {//未配置这个属性
                        $where = [];
                        $where['buyer_product_id'] = 0;
                        $where['product_model_id'] = 0;
                        $tmp_process_product_row = $tmp_process_product_row->where($where);
                    }
                } else {
                    if (in_array($select_attribute_field_o, $select_attribute_list)) {//配置了这个属性
                        $where = [];
                        $where[$select_attribute_field_o] = $$select_attribute_field_o;
                        $tmp_process_product_row = $tmp_process_product_row->where($where);

                    } else {//未配置这个属性
                        $where = [];
                        $where[$select_attribute_field_o] = 0;
                        $tmp_process_product_row = $tmp_process_product_row->where($where);

                    }
                }

            }


            $tmp_process_product_row = $tmp_process_product_row->first();
            if($tmp_process_product_row){
                $process_product_id_list[] = $tmp_process_product_row['id'];
            }
        }

        return $process_product_id_list;

    }





    //获取半成品的属性 zhuyujun 20190605
    //$deal_main_material 是否特殊处理主要用材
    static public function getProcessProductDetail($process_product_row,$deal_main_material=true){
        //半成品配置的关系
        $process_product_attribute_config_list = config('process-product.select_attribute');

        //通过配置的属性来半成品属性
        $show_attribute_list = [];
        $main_material = '';
        $select_attribute_list_str = $process_product_row['select_attribute'];
        $select_attribute_list = explode(',',$select_attribute_list_str);
        foreach ($select_attribute_list as $select_attribute_row){
//            p($select_attribute_row);
            if($select_attribute_row == 'main_material_id'){//主要用材特别处理
                if(!$deal_main_material){
                    $show_attribute_list [] = [
                        'attribute_key' => $process_product_attribute_config_list[$select_attribute_row],
                        'attribute_value' => $process_product_row['main_material'],
                    ];
                }
                $main_material = $process_product_row['main_material'];
            }elseif($select_attribute_row == 'order_type_id'){
                $show_attribute_list [] = [
                    'attribute_key' => $process_product_attribute_config_list[$select_attribute_row],
                    'attribute_value' => $process_product_row['order_type_title'],
                ];

            }elseif($select_attribute_row == 'buyer_product_id'){
                $tmp_buyer_product_title = $process_product_row['buyer_product_title'];
                if($process_product_row['product_model_id'] || $process_product_row['product_model_title']){
                    $tmp_buyer_product_title.= "【{$process_product_row['product_model_title']}】";
                }

                $show_attribute_list[] = [
                    'attribute_key' => $process_product_attribute_config_list[$select_attribute_row],
                    'attribute_value' => $tmp_buyer_product_title,
                ];
            }elseif($select_attribute_row == 'product_number'){
                $tmp_product_number = $process_product_row['product_number'];

                $show_attribute_list[] = [
                    'attribute_key' => $process_product_attribute_config_list[$select_attribute_row],
                    'attribute_value' => $tmp_product_number,
                ];
            }elseif($select_attribute_row == 'customer_id'){
                $show_attribute_list [] = [
                    'attribute_key' => $process_product_attribute_config_list[$select_attribute_row],
                    'attribute_value' => $process_product_row['customer_title'],
                ];
            }elseif($select_attribute_row == 'buyer_id'){
                $show_attribute_list [] = [
                    'attribute_key' => $process_product_attribute_config_list[$select_attribute_row],
                    'attribute_value' => $process_product_row['buyer_title'],
                ];
            }elseif($select_attribute_row == 'chip_specification'){
                $tmp_chip_specification_show = \App\Engine\OrderEngine::getOrderFiledValueTrue($process_product_row['chip_specification'],15);
                $show_attribute_list [] = [
                    'attribute_key' => $process_product_attribute_config_list[$select_attribute_row],
                    'attribute_value' => $tmp_chip_specification_show,
                ];
            }elseif($select_attribute_row == 'finished_specification'){
                $tmp_finished_specification_show = \App\Engine\OrderEngine::getOrderFiledValueTrue($process_product_row['finished_specification'],16);
                $show_attribute_list [] = [
                    'attribute_key' => $process_product_attribute_config_list[$select_attribute_row],
                    'attribute_value' => $tmp_finished_specification_show,
                ];
            }
        }

        return [
            'show_attribute_list'=>$show_attribute_list,
            'main_material'=>$main_material,
        ];

    }







    //占位方法
    static public function tmp(){

    }





}