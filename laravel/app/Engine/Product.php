<?php
/**
 * Created by PhpStorm.
 * User: zhuyujun
 * Date: 2017/10/18
 * Time: 17:22
 */

namespace App\Engine;

use App\Eloquent\Zk\Product as ProductModel;
use App\Eloquent\Zk\ProductFields;
use App\Eloquent\Zk\StorehouseRes;
use Framework\Services\QRCode\SimpleQrCode\SimpleQrCodeService;

class Product
{
    public static function getProductInfo($id,$field='*'){
        //集合材料
        if(strstr($id,'A')){
            $tmpAssemblageMaterialId = str_replace('A','',$id);
            return \App\Eloquent\Zk\AssemblageMaterial::withTrashed()->where(['id'=>$tmpAssemblageMaterialId])->first();
        }
        else{
            return ProductModel::withTrashed()->where('id',$id)->select($field)->first();
        }
    }

    //获取未删除的材料 20180605 朱雨骏
    public static function getProductInfoExist($id,$field='*'){
        return ProductModel::where('id',$id)->select($field)->first();
    }

    //获取删除的材料 20180605 朱雨骏
    public static function getProductInfoTrash($id,$field='*'){
        return ProductModel::onlyTrashed()->where('id',$id)->select($field)->first();
    }

    public static function getProductUnit($id){
        return self::getProductInfo($id,'unit');
    }

    //获取库存
    public static function getProductNumber($id){
        return self::getProductInfo($id,'number');
    }

    public static function getProductQrcode($productNo){

        return '<img src = "'.url('/admin/qrcode/png/'.'m:'.$productNo).'">';

        /*$qrCode = new SimpleQrCodeService();
        $qrCode = $qrCode->setSize(200);
        $qrCode = $qrCode->setErrorCorrection('H');
//        $qrCode = $qrCode->setMerge(storage_path('upload\nopic.png'), 0.2, true);
        $result = $qrCode->generate('m:'.$productNo);
        return $result;
        return base64_encode($result);
        return '<img src = "data:image/png;base64, ' . base64_encode($result) . '">';*/
    }

    /**
     * 字段类型
     * 跟   config('process.process_field_type_list'); 的类型需要一致，app用了一样的逻辑
     */
    public static function fieldsType(){
        return [
            '1'=>[
                'title'=>'字符串',
                'column'=>'varchar',
            ],
            '12'=>[
                'title'=>'数字',
                'column'=>'numerical',
            ]
        ];
    }

    public static function getFieldColumn($type){
        $fieldsTypes = self::fieldsType();
        return $fieldsTypes[$type]['column'];
    }

    public static function setFieldInfo(){

    }

    /**
     * @param $result
     * @return array
     * 组装产品详细信息
     */
    public static function composeInfo($result){
        $result->img_url = $result->imageInfo ? Func::getImgUrlHttp($result->imageInfo->img_url) : asset('upload/global/default_product_image.png');

        $result = [
            'id'=>$result->id,
            'product_name'=>$result->product_name,
            'product_no'=>$result->product_no,
            'barcode'=>$result->barcode,
            'number'=>$result->number,
            'unit'=>$result->unit,
            'weight'=>$result->weight,
            'category_id'=>$result->category_id,
            'category_name'=> isset($result->category->cat_name)?$result->category->cat_name:'',
            'price'=>$result->price,
            'img_id'=>$result->img_id,
            'img_url'=>$result->img_url,
            'spec'=>$result->spec,
            'weight'=>$result->weight,
        ];

        $ProductFieldsModel = new ProductFields();
        $where = ['product_id'=>$result['id']];
        $productFields = $ProductFieldsModel->getData($where);

        $productFields = $productFields->map(function($item){
            $data['field_name'] = $item->field_name;
            $comumnName = self::getFieldColumn($item->field_type);

            $data['field_value'] = $item->$comumnName;
            return $data;
        });

        $result['custom_fields'] = $productFields;


        return $result;
    }

    public static function getInfoById($productId){
        $where = ['id'=>$productId];

        $productModel = new Product();
        $info = $productModel->getProductInfo($where);

        return self::composeInfo($info);
    }


    /**
     * 生成集合产品编号
     */
    public static function createAssemblageMaterialNo(){
        $info = \App\Eloquent\Zk\AssemblageMaterial::withTrashed()->orderBy('id','desc')->first();
        $maxId = isset($info['id'])?$info['id']:0;
        $no = 'JHCL'.str_pad(($maxId+1),6,"0",STR_PAD_LEFT );
        return $no;
    }

    /**
     * 生成产品编号
     */
    public static function createProductNo(){
        $info = ProductModel::withTrashed()->orderBy('id','desc')->first();
        $maxId = isset($info['id'])?$info['id']:0;
        $no = 'CL'.str_pad(($maxId+1),6,"0",STR_PAD_LEFT );
        return $no;
    }


    public static function composeList($productList){

        if (! is_array(reset($productList))) {
            $productList = [$productList];
        }

        $productList->transform(function ($item, $key) {
            $temp = $item->toArray();
            $item['image_path'] = $item['img_url'] ? Func::getImgUrlHttp($item['img_url']) : asset('upload/global/default_product_image.png');
            $item['is_warning'] = $item['warning_number'] > $item['number'] ? 1 : 0;
            $item['is_new'] = (time() - $temp['created_at']) < 86400*7 ? 1 : 0;

            if($item->number > $item->warning_number){
                $item->circle_front_color='#00AAEE';
                $item->circle_back_color='#E4F6FD';
            }else if($item->number > 0){
                $item->circle_front_color='#FFB401';
                $item->circle_back_color='#FFF7E4';
            }else{
                $item->circle_front_color='#FF5252';
                $item->circle_back_color='#FFEDED';
            }

            $item->custom_fields = $item->fields->map(function($item){
                $comumnName = self::getFieldColumn($item->field_type);

                $re = [
                    'field_name'=> $item->field_name,
//                    'field_type'=> $item->field_type,
                    'field_value'=> $item->$comumnName,
                    //by lwl 2019 05 21 小秘书-》工单所需用料-》新增属性单位
                    'unit'=>$item->unit,
                ];
                return $re;
            });
            $firstText              = $secondText = $thirdText = '';
            $customFields           = $item->custom_fields;
            if(isset($customFields[0]))
            {
                $firstText          = $customFields[0]['field_name'].': '.$customFields[0]['field_value'].$customFields[0]['unit'];//by lwl 2019 05 21 小秘书-》工单所需用料-》新增属性单位
            }
            if(isset($customFields[1]))
            {
                $secondText          = $customFields[1]['field_name'].': '.$customFields[1]['field_value'].$customFields[0]['unit'];//by lwl 2019 05 21 小秘书-》工单所需用料-》新增属性单位
            }
            if(isset($customFields[2]))
            {
                $thirdText          = $customFields[2]['field_name'].': '.$customFields[2]['field_value'].$customFields[0]['unit'];//by lwl 2019 05 21 小秘书-》工单所需用料-》新增属性单位
            }

            $customFieldsText             = $firstText.' '.$secondText.' '.$thirdText;

            $item->first_text       = $firstText;
            $item->second_text      = $secondText;
            $item->third_text       = $thirdText;
            $item->custom_fields_text       = $customFieldsText;



            $ratio              = 0;
            if($item->full_number>0)
            {
                $ratio          = ($item->number/$item->full_number)*100;
            }
            $item->ratio = $ratio;

            //追加供应商信息和客户信息
            $tmpObj = \App\Eloquent\Zk\Customer::where(['id'=>$item['customer_id']])->first();
            $item['customer_name'] = '';
            if($tmpObj){
                $item['customer_name'] =  $tmpObj->customer_name;
            }

            $tmpObj = \App\Eloquent\Zk\SellerCompany::where(['id'=>$item['seller_company_id']])->first();
            $item['seller_company_name'] = '';
            if($tmpObj){
                $item['seller_company_name'] =  $tmpObj->title;
            }

            return $item;
        });

        return $productList;
    }

    //获取材料处理后的信息
    public static function getDealMaterialRow($materialId){
        $materialRow = self::getProductInfoExist($materialId);
        if (!$materialRow) {
            $materialRow = self::getProductInfoTrash($materialId);
            $materialRow['product_name'] = $materialRow['product_name'] . "[已删除]";
        }

        //追加材料图片地址
        $materialRow['img_url'] = '';
        if ($materialRow['img_id']) {
            $materialRow['img_url'] = \App\Eloquent\Zk\ImgUpload::getImgUrlById($materialRow['img_id']);
        }

        //追加材料自定义属性
        $ProductFieldsModel = new \App\Eloquent\Zk\ProductFields();
        $where = ['product_id' => $materialRow['id']];
        $productFields = $ProductFieldsModel->getData($where);

        $productFields = $productFields->map(function ($item) {
            $data['field_name'] = $item->field_name;
            $comumnName = \App\Engine\Product::getFieldColumn($item->field_type);

            $data['field_value'] = $item->$comumnName;
            return $data;
        });

        $materialRow['custom_fields'] = $productFields;

        return $materialRow;
    }

    public static function dealMaterialRow($materialRow,$isAssemblageMaterial = 0){
        $materialRow = $materialRow->toArray();

        //追加材料图片地址
        $materialRow['img_url'] = '';
        if ($materialRow['img_id']) {
            $materialRow['img_url'] = \App\Eloquent\Zk\ImgUpload::getImgUrlById($materialRow['img_id']);
        }

        //追加材料自定义属性

        //hjn 20190829 增加可用库存
        $storehouseWhere = ['res_id'    =>  $materialRow['id']];
        if($isAssemblageMaterial){
            $where = ['assemblage_material_id' => $materialRow['id']];
            $storehouseWhere['res_type'] = 5;
        }else{
            $where = ['assemblage_material_id' => $materialRow['assemblage_material_id']];
            $storehouseWhere['res_type'] = 1;
        }
        $materialRow['available_number'] = StorehouseRes::where($storehouseWhere)->value('available_number');
//        $ProductFieldsModel = new \App\Eloquent\Ygt\ProductFields();
//        $productFields = $ProductFieldsModel->getData($where);

        $productFields = \App\Eloquent\Zk\ProductFields::where($where)->get();

        $productFields = $productFields->map(function ($item) {
            $data['field_name'] = $item->field_name;
            $comumnName = \App\Engine\Product::getFieldColumn($item->field_type);

            $data['unit'] = $item->unit;
            $data['field_value'] = $item->$comumnName.$data['unit'];
            $data['is_compute'] = $item->is_compute;
            $data['id'] = $item->id;
            return $data;
        });

        $materialRow['custom_fields'] = $productFields;

        $firstText              = $secondText = $thirdText = '';
        $customFields           = $productFields->toArray();

        if(isset($customFields[0]))
        {
            $firstText          = $customFields[0]['field_name'].': '.$customFields[0]['field_value'];
        }
        if(isset($customFields[1]))
        {
            $secondText          = $customFields[1]['field_name'].': '.$customFields[1]['field_value'];
        }
        if(isset($customFields[2]))
        {
            $thirdText          = $customFields[2]['field_name'].': '.$customFields[2]['field_value'];
        }

        $customFieldsText             = $firstText.' '.$secondText.' '.$thirdText;


        /**处理计量属性的值**/
        if(!empty($customFields)){
            $isChange = false;
            foreach ($customFields as $tmpKey => $tmpRow){
                if($tmpRow['is_compute']){
                    $isChange = true;

                    if($isAssemblageMaterial){
                        $tmpObj = \App\Eloquent\Zk\ProductFieldsCompute::where(['type'=>2,'material_id'=>$materialRow['id'],'product_fields_id'=>$tmpRow['id']])->first();
                    }else{
                        $tmpObj = \App\Eloquent\Zk\ProductFieldsCompute::where(['type'=>1,'material_id'=>$materialRow['id'],'product_fields_id'=>$tmpRow['id']])->first();
                    }

                    if($tmpObj){
                        if($tmpObj['number']){
                            $customFields[$tmpKey]['field_value'] = $tmpObj['number'].$tmpRow['unit'];
                        }else{
                            //过滤这个字段
                            unset($customFields[$tmpKey]);
                        }
                    }else{
                        //过滤这个字段
                        unset($customFields[$tmpKey]);
                    }
                }
            }

            //如果有计量属性调整重新处理字段
            if($isChange){
                $firstText = $secondText = $thirdText = '';
                if(isset($customFields))
                {
                    $fieldsList         = array_values($customFields);
                    $customFields = $fieldsList;//保存被过滤的结果

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
                }
                $customFieldsText                       = $firstText.' '.$secondText.' '.$thirdText;
            }
        }



        $materialRow['custom_fields_text'] = $customFieldsText;
        //追加供应商信息和客户信息
        $materialRow['customer_name'] = '';
        if(isset($materialRow['customer_id'])){
            $tmpObj = \App\Eloquent\Zk\Customer::where(['id'=>$materialRow['customer_id']])->first();
            if($tmpObj) $materialRow['customer_name'] =  $tmpObj->customer_name;
        }

        $materialRow['seller_company_name'] = '不限供应商';
        if(isset($materialRow['seller_company_id'])){
            $tmpObj = \App\Eloquent\Zk\SellerCompany::where(['id'=>$materialRow['seller_company_id']])->first();
            if($tmpObj)$materialRow['seller_company_name'] =  $tmpObj->title;
        }

        //处理库存饼图信息
        if($materialRow['number'] > $materialRow['warning_number']){
            $materialRow['circle_front_color']='#00AAEE';
            $materialRow['circle_back_color']='#E4F6FD';
        }else if($materialRow['number'] > 0){
            $materialRow['circle_front_color']='#FFB401';
            $materialRow['circle_back_color']='#FFF7E4';
        }else{
            $materialRow['circle_front_color']='#FF5252';
            $materialRow['circle_back_color']='#FFEDED';
        }

        $ratio              = 0;
        if($materialRow['full_number']>0)
        {
            $ratio          = ($materialRow['number']/$materialRow['full_number'])*100;
        }
        $materialRow['ratio'] = $ratio;

        return $materialRow;
    }

}