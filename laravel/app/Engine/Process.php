<?php
/**
 * Created by PhpStorm.
 * User: zhuyujun
 * Date: 2017/10/18
 * Time: 17:22
 */

namespace App\Engine;


use App\Eloquent\Ygt\ImgUpload;
use App\Eloquent\Ygt\OrderDistribution;
use App\Eloquent\Ygt\OrderDistributionPre;

class Process
{
    /**
     * Description:创建工序
     * User: zhuyujun
     */
    public function CreateProcess()
    {

    }

    /**
     * Description:修改工序
     * User: zhuyujun
     */
    public function UpdateProcess()
    {

    }

    /**
     * Description:删除工序
     * User: zhuyujun
     */
    public function DeleteProcess()
    {

    }

    /**
     * Description:获取一条工序记录
     * User: zhuyujun
     */
    public function GetProcessRow()
    {

    }

    /**
     * Description:获取工序列表
     * User: zhuyujun
     */
    public function GetProcessList()
    {

    }
    //by zzy 字段和所填写的内容(订单基础信息,订单工序信息)
    //$data 所填写的内容的数组
    //$fieldList 字段数组
    //$arr 会额外用到的其它信息
    //$arr=['is_pre'=>'1草稿箱0非草稿箱','order_id'=>'工单id']
    public static function setDataByFieldType($data,$fieldList,$arr=[])
    {
        $result             = [];
        foreach ($fieldList as $key => $val) {
            //$fieldList[$key]['process_id'] = $data['process_type'];
            if(isset($data['process_type'])){
                $fieldList[$key]['process_id'] = $data['process_type'];
            }
            else{
                $fieldList[$key]['process_id'] = $data['order_type'];
            }
            //格式统一
            $fieldList[$key]['deal_field_name'] = $fieldList[$key]['field_name'];
            $fieldList[$key]['ordertype_process_id'] = isset($data['ordertype_process_id']) ? $data['ordertype_process_id'] : 0;



            $valFieldName           = $val['field_name'];
            $valFieldType           = $val['field_type'];
            if (isset($data[$valFieldName])) {
                $dataValue          = $data[$valFieldName];
                switch ($valFieldType)
                {
                    case 3:
                        //选择 处理
                        $selectList         = $val['data'];
                        $selectId           = 0;
                        foreach ($selectList as $select) {
                            if ($select['title'] == $dataValue) {
                                $selectId   = $select['id'];
                            }
                        }
                        $fieldList[$key]['default_select_id']   = $selectId;
                        $fieldList[$key]['default_value']       = $dataValue;
                        break;
                    case 4:
                        $materialIdArr      = explode(',', $dataValue);
                        $materialList       = [];

                        //获取优选采购的材料id
                        $recommendMaterialIdArr = \App\Eloquent\Ygt\OrderMaterialPurchaseMark::where('order_id','=',$arr['order_id'])
                            ->where('is_purchase','=',1)->pluck('material_id')->toArray();

                        foreach ($materialIdArr as $materialId) {
                            if(strstr($materialId,'A')){
                                $tmpAssemblageMaterialId = str_replace('A','',$materialId);
                                $materiaRow = \App\Eloquent\Ygt\AssemblageMaterial::withTrashed()->where(['id'=>$tmpAssemblageMaterialId])->first();
                            }else{
                                $materiaRow     = Product::getProductInfo($materialId);
                            }

                            if ($materiaRow) {//过滤异常情况
                                $materiaRow->toArray();
                                if(in_array($materialId,$recommendMaterialIdArr)){
                                    $materiaRow['is_purchase'] = 1;
                                }
                                else{
                                    $materiaRow['is_purchase'] = 0;
                                }

                                $materialList[] = [
                                    'id'             => $materialId,
                                    'product_no'    => $materiaRow['product_no'],
                                    'product_name'  => $materiaRow['product_name'],
                                    'is_purchase'   => $materiaRow['is_purchase'],
                                ];
                            }
                        }
                        $fieldList[$key]['default_product_list']    = $materialList;
                        break;
                    case 5:
                        //填写+单位选择 处理
                        $unitArr            = explode(',', $dataValue);
                        if (count($unitArr) > 1) {
                            $unitValue      = $unitArr[0];
                            $unitTitle      = $unitArr[1];
                            $unitId         = 0;
                            $unitList       = $val['field_unit'];
                            foreach ($unitList as $unitRow) {
                                if ($unitRow['title'] == $unitTitle) {
                                    $unitId = $unitRow['id'];
                                }
                            }
                            $fieldList[$key]['default_value'] = $unitValue;
                            $fieldList[$key]['default_unit_id'] = $unitId;
                            $fieldList[$key]['default_unit_title'] = $unitTitle;
                        }
                        break;
                    case 8:
                        //配送地址处理
//                        $isPre              = $arr['is_pre'];//1草稿箱0非草稿箱
//                        $orderId            = $arr['order_id'];
//                        if ($isPre==1) {
//                            $where          = ['order_id'=>$orderId];
//                            $distribution   = OrderDistributionPre::where($where)->get()->toArray();
//                        } else {
//                            $where          = ['order_id'=>$orderId];
//                            $distribution   = OrderDistribution::where($where)->get()->toArray();
//                        }
//                        $distributionRow    = !empty($distribution[0]) ? $distribution[0] : [];
//                        if(!empty($distributionRow))
//                        {
//                            $fieldList[$key]['default_distribution_row']    = $distributionRow;
//                        }


                        $customerAddressId = $dataValue;

                        $addressWhere = ['id' => $customerAddressId];
                        $customerAddress = \App\Eloquent\Ygt\BuyersAddress::withTrashed()->where($addressWhere)->first();
                        $customerAddressRow = [];
                        $showTitle = '';
                        if ($customerAddress) {
                            $customerAddressRow = $customerAddress->toArray();
                            $showTitle = $customerAddressRow['province_name'] . $customerAddressRow['city_name'] . $customerAddressRow['area_name'];
                        }


                        $fieldList[$key]['default_select_id']   = $customerAddressId;
                        $fieldList[$key]['default_value']       = $showTitle;

                        break;
                    case 9:
                        //图片处理
                        $imgIdArr           = explode(',', $dataValue);
                        $imgUrlArr          = [];
                        foreach ($imgIdArr as $imgId) {
                            //过滤空格 图片id应该不会有0
                            if (!trim($imgId)) { continue; }
                            $imgUrl     = ImgUpload::getImgUrlById($imgId);
                            $imgUrlArr[]= $imgUrl;
                        }

                        $imgUrlStr = '';
                        if(is_array($imgUrlArr)){
                            $imgUrlStr      = implode(',',$imgUrlArr);
                        }
                        $fieldList[$key]['default_img_id']      = $dataValue;
                        $fieldList[$key]['default_img_url']     = $imgUrlStr;
                        break;
                    case 17:
//                        $plateId            = $dataValue;
//                        $plateRow           = Plate::getPlateInfo($plateId);
//                        if ($plateRow) {
//                            $plateValue     = $plateRow['plate_no'];
//                        } else {
//                            $plateValue     = $plateId;
//                        }
//                        $fieldList[$key]['default_select_id']   = $plateId;
//                        $fieldList[$key]['default_value']       = $plateValue;

                        //新的版数据
                        $tmpCreateOrderPlateRow = \App\Eloquent\Ygt\CreateOrderPlate::where(['id'=>$dataValue])->first();
                        $plateInfo = json_decode(htmlspecialchars_decode($tmpCreateOrderPlateRow['content']), true);
                        if(empty($plateInfo)){
                            $plateInfo = null;
                        }

                        $fieldList[$key]['default_value'] = '';
                        $fieldList[$key]['default_plate_list'] = $plateInfo;
                        break;
                    case 18:
                        $selectId           = $dataValue;
                        $customerTitle      = Customer::getNameById($selectId);
                        $customerTitle      = $customerTitle ? $customerTitle : $selectId;
                        $fieldList[$key]['default_select_id']   = $selectId;
                        $fieldList[$key]['default_value']       = $customerTitle;
                        break;
                    case 19:
                        //单位展示
                        $resultValue            = Buyers::getNameById($dataValue);
                        $selectId               = 0;
                        if (!$resultValue) {
                            $resultValue        = $dataValue;
                        }else{
                            $selectId           = $dataValue;
                        }
                        $fieldList[$key]['default_select_id']   = $selectId;
                        $fieldList[$key]['default_value']       = $resultValue;
                        break;
                    case 20:
                        //品名展示
//                        $selectId               = $dataValue;
//                        $buyersProduct = \App\Eloquent\Ygt\BuyersProduct::where(['id' => $selectId])->first();
//                        if ($buyersProduct) {
//                            $showTitle = $buyersProduct['name'];
//                        } else {
//                            $showTitle = $dataValue;
//                        }
//
//                        $fieldList[$key]['default_select_id']   = $selectId;
//                        $fieldList[$key]['default_value']       = $showTitle;

                        //hjn 20190822 产品数据固化 统一处理品名与型号
                        $productNameInfo = \App\Engine\OrderEngine::getOrderFiledValueTrue($dataValue,20,1);


//                        //品名调整新增型号，可以关联版 zhuyujun  20190226
//                        $tmpCreateOrderProductNameRow = \App\Eloquent\Ygt\CreateOrderExtend::where(['id'=>$dataValue])->first();
//                        $productNameInfo = json_decode(htmlspecialchars_decode($tmpCreateOrderProductNameRow['content']), true);;
//                        if(empty($productNameInfo)){
//                            $productNameInfo = null;
//                        }
//                        if(isset($productNameInfo['chanpin_id']) && $productNameInfo['chanpin_id']){

//                        }

                        $fieldList[$key]['default_value'] = $productNameInfo;
//                        $fieldList[$key]['default_proudct_name_list'] = $productNameInfo;
                        $fieldList[$key]['field_type'] = 0;

                        break;
                    case 21:
                        //开票资料 处理
                        $selectId               = $dataValue;
                        $buyersInvoice = \App\Eloquent\Ygt\BuyersInvoice::where(['id' => $selectId])->first();
                        if ($buyersInvoice) {
                            $showTitle = $buyersInvoice['account_name'];
                        } else {
                            $showTitle = $dataValue;
                        }

                        $fieldList[$key]['default_select_id']   = $selectId;
                        $fieldList[$key]['default_value']       = $showTitle;
                        break;
                    case 22:
                        $tmpCreateOrderProcessProductRow = \App\Eloquent\Ygt\CreateOrderProcessProduct::where(['id'=>$dataValue])->first();
                        $processProductInfo = json_decode(htmlspecialchars_decode($tmpCreateOrderProcessProductRow['content']), true);
                        if(empty($processProductInfo)){
                            $processProductInfo = null;
                        }

                        $fieldList[$key]['default_value'] = '';
                        $fieldList[$key]['default_process_product_list'] = $processProductInfo;

                    default:
                        $fieldList[$key]['default_value']   = $data[$val['field_name']];
                }
            }
        }
        return $fieldList;
    }
    //通过不同字段类型转换数据库中字段对应的值 by zzy
    //如 存的是外键id则转换成id对应的其它信息
    //如 有逗号隔开的则取消逗号或者转换成其它符号
    public static function changeDataByFieldType($data,$fieldList)
    {
        foreach ($fieldList as $key => $val) {
            $valFieldName           = $val['field_name'];
            $valFieldType           = $val['field_type'];
            if (isset($data[$valFieldName])) {
                $dataValue          = $data[$valFieldName];
                $resultValue        = '';
                switch ($valFieldType)
                {
                    case 4:
                        //显示每种材料
                        $idArr              = explode(',', $dataValue);
                        $resultValueArr     = [];
                        foreach ($idArr as $val) {
                            $collection     = Product::getProductInfo($val);
                            if ($collection) {//过滤异常情况
                                $info       = $collection->toArray();
                                $resultValueArr[]   = $info['product_name'];
                            }
                        }
                        if(!empty($resultValueArr)){
                            $resultValue        = implode(',',$resultValueArr);
                        }
                        break;
                    case 5:
                        //填写+单位选择-去掉逗号
                        $resultValue    = str_replace(',','',$dataValue);
                        break;
                    case 6:
                        //开关-转换成是否
                        $resultValue    = $dataValue ? '是' : '否';
                        break;
                    case 15:
                        //宽长
                        $resultValueArr     = explode(',', $dataValue);
                        if (!empty($resultValueArr) && (count($resultValueArr) == 2)) {
                            $resultValue    = sprintf("宽度%d*长度%d厘米", $resultValueArr[0], $resultValueArr[1]);
                        }
                        break;
                    case 16:
                        //宽长高
                        $resultValueArr     = explode(',', $dataValue);
                        if (!empty($resultValueArr) && (count($resultValueArr) == 3)) {
                            $resultValue    = sprintf("宽度%d*长度%d*高度%d厘米", $resultValueArr[0], $resultValueArr[1], $resultValueArr[2]);
                        }
                        break;
                    case 17:
                        //跳版选择
                        if($dataValue)
                        {
                            $plate              = Plate::getPlateInfo($dataValue);
                            if ($plate) {
                                $resultValue    = $plate['plate_name'];
                            }
                        }
                        break;
                    case 18:
                        //客户选择
                        $resultValue            = Customer::getNameById($dataValue);
                        break;
                    case 19:
                        //单位展示
                        $resultValue            = Buyers::getNameById($dataValue);
                        if (!$resultValue) {
                            $resultValue        = $dataValue;
                        }
                        break;
                    default:
                        $resultValue            = $dataValue;
                }
                $data[$valFieldName]            = $resultValue;
            }
        }
        return $data;
    }
    //验证字段是必填还是选填 by zzy
    public static function checkDataValue($data,$fieldList)
    {
        $processFieldRules      = [];
        $processFieldMessage    = [];
        $result                 = true;
        foreach ($fieldList as $key => $val) {
            $isMust             = $val['is_must'];
            if ($isMust == 1 && $result===true) {
                $rulesName      = 'required';
                $fieldName      = $val['field_name'];
                $fieldTitle     = $val['title'];
                $fieldMsg       = $fieldName . '.' . $rulesName;
                $processFieldRules[$fieldName] = $rulesName;
                $processFieldMessage[$fieldMsg] = $fieldTitle . '必填';
                $inputValue     = $data[$fieldName];
                if (empty($inputValue)) {
                    $result     = $fieldTitle.'必填';
                    break;
                }
            }
        }
        return $result;
    }
    //转变单位名称字段 原因 该字段存值 有三种
    //1单位的id 2单位的名称 3空
    public static function changeFieldName23($value = '')
    {
        $result = $value;
        if ($value) {
            $result = Buyers::getNameById($value);
            if (!$result) {
                $result = $value;
            }
        }
        return $result;
    }

}