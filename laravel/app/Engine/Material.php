<?php
/**
 * Created by PhpStorm.
 * User: zhuyujun
 * Date: 2018/10/23
 * Time: 10:40
 */


//材料通用方法类

namespace App\Engine;

class Material
{
    public static function getMaterialPrice($materialId)
    {
        //如果是集合材料取最近一批材料的采购价
        if(strstr($materialId,'A')){
            $tmpAssemblageMaterialId = str_replace('A','',$materialId);
            $productIdList = \App\Eloquent\Ygt\Product::where(['assemblage_material_id'=>$tmpAssemblageMaterialId])->pluck('id');
        }else{
            $productIdList = [$materialId];
        }


        $price = 0;
        $tmpPurchaseMaterialRow = \App\Eloquent\Ygt\PurchaseMaterial::whereIn('material_id',$productIdList)->orderBy('created_at','desc')->first();
        if($tmpPurchaseMaterialRow){
            $price = $tmpPurchaseMaterialRow['price'];
        }

        return $price;
    }

    public static function getMaterialDealInfo($materialId){
        $materialRow = \App\Eloquent\Ygt\Product::where(['id'=>$materialId])->first();

        if(!$materialRow){
            $materialRow = \App\Eloquent\Ygt\Product::withTrashed()->where(['id'=>$materialId])->first();
            if(!$materialRow){
                return false;
            }else{
                $materialRow['product_name'] = $materialRow['product_name']."[已删除]";
            }
        }

        //图片路径
        $defaultImgUrl      = asset('upload/global/default_product_image.png');
        $valImgUrl       = isset($materialRow['img_id']) ? $materialRow['img_id'] : '';
        if($valImgUrl){
            $valImgUrl = \App\Eloquent\Ygt\ImgUpload::getImgUrlById($valImgUrl);
        }
        $imagePath       = $valImgUrl;
        $imagePath       = $imagePath ? $imagePath : $defaultImgUrl;
        $materialRow['image_path']       = $imagePath;
        // 返回图片ID，BY SOJO
        $materialRow['image_id']       = isset($materialRow['img_id']) ? $materialRow['img_id'] : '';

        //环形图颜色
        $circleFrontColor = "#FF5252";
        $circleBackColor = "#FFEDED";
        $materialRow['circle_front_color']   = $circleFrontColor;
        $materialRow['circle_back_color']    = $circleBackColor;

        $valNumber       = $materialRow['number'];
        $valFullNumber   = $materialRow['full_number'];
        $ratio           = 0;
        if($valFullNumber > 0)
        {
            $ratio       = round(($valNumber/$valFullNumber)*100);
        }
        $materialRow['ratio']        = $ratio;

        //供应商&所有权
        $supplierName = $customerName = '';
        if($materialRow['seller_company_id']){
            $tmpSellerCompanyRow = \App\Eloquent\Ygt\SellerCompany::where(['id'=>$materialRow['seller_company_id']])->first();
            if($tmpSellerCompanyRow){
                $supplierName = $tmpSellerCompanyRow['title'];
            }
        }

        if($materialRow['customer_id']){
            $tmpCustomerRow = \App\Eloquent\Ygt\Customer::where(['id'=>$materialRow['customer_id']])->first();
            if($tmpCustomerRow){
                $customerName = $tmpCustomerRow['customer_name'];
            }
        }

        $materialRow['seller_company_name'] = $supplierName;
        $materialRow['customer_name'] = $customerName;

        if(array_key_exists('customer_id',$materialRow) && $materialRow['customer_id']==0)
        {
            $materialRow['customer_name']   = '本公司';
        }

        //追加材料自定义属性
        $ProductFieldsModel = new \App\Eloquent\Ygt\ProductFields();
        //调整完集合材料后
        if(isset($materialRow['assemblage_material_id'])){
            $tmpAssemblageMaterialId = $materialRow['assemblage_material_id'];
        }else{
            $tmpAssemblageMaterialId = $materialRow['id'];
        }

        $where = ['assemblage_material_id' => $tmpAssemblageMaterialId];
        $productFields = $ProductFieldsModel::where($where)->get();
        $productFields = $productFields->map(function ($item) {
            $data['field_name'] = $item->field_name;
            $comumnName = \App\Engine\Product::getFieldColumn($item->field_type);

            $data['field_value'] = $item->$comumnName.$item['unit'];
            return $data;
        });
        $materialRow['custom_fields'] = $productFields;

        $firstText      = $secondText = $thirdText = $customFieldsText = '';
        $fieldsList         = $productFields;
        if(!empty($fieldsList))
        {
            if(isset($fieldsList[0]))
            {
                $firstText   = $fieldsList[0]['field_name'].': '.$fieldsList[0]['field_value'];
            }
            if(isset($fieldsList[1]))
            {
                $secondText  = $fieldsList[1]['field_name'].': '.$fieldsList[1]['field_value'];
            }
            if(isset($fieldsList[2]))
            {
                $thirdText   = $fieldsList[2]['field_name'].': '.$fieldsList[2]['field_value'];
            }
        }

        $customFieldsText                       = $firstText.' '.$secondText.' '.$thirdText;
        $materialRow['custom_fields']        = $fieldsList;
        $materialRow['first_text']           = $firstText;
        $materialRow['second_text']          = $secondText;
        $materialRow['third_text']           = $thirdText;
        $materialRow['custom_fields_text']   = $customFieldsText;

        return $materialRow;
    }

    //获取换算后材料计量属性的值
    public static function getMaterialComputeNum($materialId,$materialNum){

        /**如果一级分类是'筒料 膜 纸 无纺布'，会进行计算**/
        //获取一级分类
        $tmpProdct = \App\Eloquent\Ygt\Product::where(['id'=>$materialId])->first();
        $materialComputeNum = 0;//返回的计量属性值
        if($tmpProdct){
            $categoryRow = \App\Eloquent\Ygt\Category::where(['id'=>$tmpProdct['category_id']])->first();
            if($categoryRow){
                $categoryPidRow = \App\Eloquent\Ygt\Category::where(['id'=>$categoryRow['pid']])->first();
                if($categoryPidRow){
                    $categoryPidName = $categoryPidRow['cat_name'];
                    if(in_array($categoryPidName,['筒料','膜','纸','无纺布'])){
                        //获取材料的属性
                        $tmpProductFieldsList = \App\Eloquent\Ygt\ProductFields::where(['assemblage_material_id'=>$tmpProdct['assemblage_material_id']])->get();

                        if($categoryPidName == '筒料'){
                            $tmpGramWeight = 0;
                            $tmpWidth = 0;
                            foreach ($tmpProductFieldsList as $tmpProductFieldsRow){
                                if($tmpProductFieldsRow['field_name'] == '克重'){
                                    $tmpGramWeight = $tmpProductFieldsRow['varchar'];
                                }
                                if($tmpProductFieldsRow['field_name'] == '宽度'){
                                    $tmpWidth = $tmpProductFieldsRow['varchar'];
                                }
                            }
                            if($tmpGramWeight && $tmpWidth){
                                //筒料余品米数=余品重量(kg)*1000/克重(g/㎡)/宽度(cm)*100/2
                                $materialComputeNum = $materialNum*1000/$tmpGramWeight/$tmpWidth*100/2;
                                $materialComputeNum = sprintf('%.2f',$materialComputeNum);
                                return $materialComputeNum;
                            }else{
                                return false;
                            }
                        }elseif($categoryPidName == '膜'){
                            $tmpThickness = 0;
                            $tmpWidth = 0;
                            foreach ($tmpProductFieldsList as $tmpProductFieldsRow){
                                if($tmpProductFieldsRow['field_name'] == '厚度'){
                                    $tmpThickness = $tmpProductFieldsRow['varchar'];
                                }
                                if($tmpProductFieldsRow['field_name'] == '宽度'){
                                    $tmpWidth = $tmpProductFieldsRow['varchar'];
                                }
                            }

                            if($tmpThickness && $tmpWidth){
                                // 膜余品米数=余品重量(kg)*1000/厚度(丝)/9.1/宽度(cm)*100
                                $materialComputeNum = $materialNum*1000/$tmpThickness/9.1/$tmpWidth*100;
                                $materialComputeNum = sprintf('%.2f',$materialComputeNum);
                                return $materialComputeNum;
                            }else{
                                return false;
                            }
                        }elseif($categoryPidName == '纸'){
                            $tmpGramWeight = 0;
                            $tmpWidth = 0;
                            foreach ($tmpProductFieldsList as $tmpProductFieldsRow){
                                if($tmpProductFieldsRow['field_name'] == '克重'){
                                    $tmpGramWeight = $tmpProductFieldsRow['varchar'];
                                }
                                if($tmpProductFieldsRow['field_name'] == '宽度'){
                                    $tmpWidth = $tmpProductFieldsRow['varchar'];
                                }
                            }
                            if($tmpGramWeight && $tmpWidth){
                                // 纸余品米数=余品重量(kg)*1000/克重(g/㎡)/宽度(cm)*100
                                $materialComputeNum = $materialNum*1000/$tmpGramWeight/$tmpWidth*100;
                                $materialComputeNum = sprintf('%.2f',$materialComputeNum);
                                return $materialComputeNum;
                            }else{
                                return false;
                            }
                        }elseif($categoryPidName == '无纺布'){
                            $tmpGramWeight = 0;
                            $tmpWidth = 0;
                            foreach ($tmpProductFieldsList as $tmpProductFieldsRow){
                                if($tmpProductFieldsRow['field_name'] == '克重'){
                                    $tmpGramWeight = $tmpProductFieldsRow['varchar'];
                                }
                                if($tmpProductFieldsRow['field_name'] == '宽度'){
                                    $tmpWidth = $tmpProductFieldsRow['varchar'];
                                }
                            }
                            if($tmpGramWeight && $tmpWidth){
                                //无纺布余品米数=余品重量(kg)*1000/克重(g/㎡)/宽度(cm)*100
                                $materialComputeNum = $materialNum*1000/$tmpGramWeight/$tmpWidth*100;
                                $materialComputeNum = sprintf('%.2f',$materialComputeNum);
                                return $materialComputeNum;
                            }else{
                                return false;
                            }
                        }else{
                            return false;
                        }

                    }else{
                        return false;
                    }
                }
            }
        }
    }


    //获取材料属性（含集合材料、sku材料）
    public static function getMaterialField($assemblageMaterialId){
        //追加材料自定义属性
        $ProductFieldsModel = new \App\Eloquent\Ygt\ProductFields();
        $where = ['assemblage_material_id' => $assemblageMaterialId];
        $productFields = \App\Eloquent\Ygt\ProductFields::where($where)->get();

        $productFields = $productFields->map(function ($item) {
            $data['field_name'] = $item->field_name;
            $data['is_compute'] = $item->is_compute;
            $comumnName = \App\Engine\Product::getFieldColumn($item->field_type);
            $data['field_value'] = $item->$comumnName;
            $data['unit'] = $item->unit;
            return $data;
        });

        //过滤计量属性
        $productFields = $productFields->toArray();
        foreach ($productFields as $key => $row){
            if($row['is_compute']){
                unset($productFields[$key]);
            }
        }
        $productFields = array_values($productFields);

        $fieldsList         = $productFields;
        $firstText = $secondText = $thirdText ='';
        if(!empty($fieldsList))
        {
            if(isset($fieldsList[0]))
            {
                $firstText   = $fieldsList[0]['field_name'].': '.$fieldsList[0]['field_value'].$fieldsList[0]['unit'];
            }
            if(isset($fieldsList[1]))
            {
                $secondText  = $fieldsList[1]['field_name'].': '.$fieldsList[1]['field_value'].$fieldsList[1]['unit'];
            }
            if(isset($fieldsList[2]))
            {
                $thirdText   = $fieldsList[2]['field_name'].': '.$fieldsList[2]['field_value'].$fieldsList[2]['unit'];
            }
        }
        $customFieldsText                       = $firstText.' '.$secondText.' '.$thirdText;


        $re = [];
        $re['custom_fields_text']   = $customFieldsText;
        $re['custom_fields'] = $productFields;
        return $re;
    }

}