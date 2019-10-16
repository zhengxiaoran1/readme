<?php
/**
 * Created by PhpStorm.
 * User: zhuyujun
 * Date: 2017/10/18
 * Time: 17:22
 */

namespace App\Engine;

use App\Eloquent\Ygt\Order;
use App\Eloquent\Ygt\ProductCustomer;
use App\Eloquent\Ygt\ProductSupplier;
use App\Eloquent\Ygt\Stock as StockModel;
use App\Eloquent\Ygt\StockOut as StockOutModel;
use App\Eloquent\Ygt\StockIn as StockInModel;
use App\Eloquent\Ygt\Product as ProductModel;
use App\Eloquent\Ygt\Process as ProcessModel;
use App\Eloquent\Ygt\OrderMaterial as OrderMaterialModel;
use App\Eloquent\Ygt\OrderProcess as OrderProcessModel;
use App\Eloquent\Ygt\OrderProcessCourse as OrderProcessCourseModel;
use App\Eloquent\Ygt\OrderMaterialCourse as OrderMaterialCourseModel;
use App\Eloquent\Ygt\DepartmentUser;
use App\Eloquent\Ygt\Message;
use App\Engine\Permission as EnginePermission;
use App\Eloquent\Ygt\ProductFields;
use App\Engine\Product as EngineProduct;
use Illuminate\Support\Facades\DB;

class Stock
{

    /**
     * @param $OrderProcessId
     * @return 工单工序 余料回库
     */
    public static function OrderProcessStockIn($orderProcessCourseId)
    {

        $orderProcessCourseInfo = OrderProcessCourseModel::where('id', $orderProcessCourseId)->first()->toArray();
        $orderProcessInfo = OrderProcessModel::where('id', $orderProcessCourseInfo['order_process_id'])->first()->toArray();

        $processId = $orderProcessInfo['process_type'];
        $orderId = $orderProcessInfo['order_id'];
        $OrderMaterials = OrderMaterialCourseModel::where([['order_process_course_id', $orderProcessCourseId], ['process_type', $processId]])->get()->toArray();

        foreach ($OrderMaterials as $key => $val) {
            if ($val['residual_number']) {
                $orderProcessId = $orderProcessInfo['id'];
                $orderMaterialId = $val['id'];
                $productId = $val['material_id'];
                self::inFactoryStockIn($productId, $val['residual_number'], $orderId, $orderProcessId, $orderMaterialId);
            }
        }
        return true;
    }

    /**
     * @param $productId
     * @param $number
     * @param $orderId
     * @param $orderProcessId
     * @param $orderMaterialId
     * 厂内入库
     */
    private static function inFactoryStockIn($productId, $number, $orderId, $orderProcessId, $orderMaterialId)
    {
        $stockModel = new StockModel();
        $water_no = self::createWaterNo();
        $stockData = ['number' => $number, 'product_id' => $productId, 'water_no' => $water_no, 'type' => 1, 'stock_type' => 1];


        if ($stockId = $stockModel->addStock($stockData)) {

            $stockOutData = [
                'stock_id' => $stockId,
                'order_id' => $orderId,
                'order_process_id' => $orderProcessId,
                'order_material_id' => $orderMaterialId,
                'operator' => '操作员'
            ];

            StockInModel::insert($stockOutData);

            //库存增加
            if (!ProductModel::addNumber($productId, $number)) {
                xThrow(ERR_PRODUCT_INCREMENT_FAIL);
            }
        }
    }

    /**
     * @param $OrderProcessId
     * @return 工单工序 领料出库
     */
    public static function OrderProcessStockOut($orderProcessCourseId)
    {
        $orderProcessCourseInfo = OrderProcessCourseModel::where('id', $orderProcessCourseId)->first()->toArray();
        $orderProcessInfo = OrderProcessModel::where('id', $orderProcessCourseInfo['order_process_id'])->first()->toArray();

        $processId = $orderProcessInfo['process_type'];
        $orderId = $orderProcessInfo['order_id'];
        $OrderMaterials = OrderMaterialCourseModel::where([['order_process_course_id', $orderProcessCourseId], ['process_type', $processId]])->get()->toArray();

        foreach ($OrderMaterials as $key => $val) {
            if ($val['receive_number']) {
                $orderProcessId = $orderProcessInfo['id'];
                $orderMaterialId = $val['id'];
                $productId = $val['material_id'];
                self::inFactoryStockOut($productId, $val['receive_number'], $orderId, $orderProcessId, $orderMaterialId);
            }
        }
        return true;
    }

    /**
     * @param $OrderProcessCourseId
     * @return bool
     * 工单工序员工余料回库
     */
    public static function OrderProcessCourseStockIn($OrderProcessCourseId)
    {
        //获取登陆用户信息
        $user_id = \App\Engine\Func::getHeaderValueByName('userid');
        $user_info = \App\Eloquent\Ygt\DepartmentUser::getCurrentInfo($user_id)->toArray();

        $orderProcessCourseInfo = OrderProcessCourseModel::where('id', $OrderProcessCourseId)->first()->toArray();

        //工序ID
        $orderProcessId = $orderProcessCourseInfo['order_process_id'];
        $companyId = $orderProcessCourseInfo['company_id'];

        //工单工序信息
        $orderProcessInfo = OrderProcessModel::where('id', $orderProcessId)->first();
        $process_type     = $orderProcessInfo->process_type;
        $process          = ProcessModel::getInfo( ['id'=>$process_type] );
        $process_title    = $process->title;
//        $orderId = $orderProcessInfo['id'];
        $orderId = $orderProcessInfo['order_id'];

        $orderInfo = Order::where('id', $orderId)->first()->toArray();
        $orderTypeId = $orderInfo['order_type'];
        $foreignKey = intval($orderInfo['customer_order_id']);

        $OrderMaterials = OrderMaterialCourseModel::where([['order_process_course_id', $OrderProcessCourseId]])->get();

        $stockData = [];
        $product_id_arr     = $product_num_arr = [];
        $stockResult=false;
        $orderMaterialQrLogDealList = [];//按二维码ID分组
        foreach ($OrderMaterials as $key => $val) {
            if($val['residual_number'] >0){
                //修改二维码对应的实际材料的数量

                $orderMaterialQrLogList = \App\Eloquent\Ygt\OrderMaterialQrLog::whereIn('type',[1])->where(['order_process_course_id' => $OrderProcessCourseId, 'material_id' => $val['material_id']])->get();
                foreach ($orderMaterialQrLogList as $orderMaterialQrLogRow){
                    if(isset($orderMaterialQrLogDealList[$orderMaterialQrLogRow['qr_id']])){
                        $orderMaterialQrLogDealList[$orderMaterialQrLogRow['qr_id']]['num'] += $orderMaterialQrLogRow['num'];
                    }else{
                        $orderMaterialQrLogDealList[$orderMaterialQrLogRow['qr_id']]['num'] = $orderMaterialQrLogRow['num'];
                    }
                }

                $water_no = self::createWaterNo();
                //如果是集合材料，取相关联的第一个材料
                //考虑集合材料的问题
                if(strstr($val['material_id'],'A')){
                    $tmpAssemblageMaterialId = str_replace('A','',$val['material_id']);
                    $relateMaterialRow = \App\Eloquent\Ygt\Product::where(['assemblage_material_id'=>$tmpAssemblageMaterialId])->first();
                    $relateMaterialId = $relateMaterialRow['id'];

                    $tmpStockData = [
                        'number' => $val['residual_number'],
                        'product_id' => $relateMaterialId,
                        'company_id' => $companyId,
                        //调整为实际操作的uid zhuyujun 20190514
//                        'operate_uid' => $orderProcessCourseInfo['uid'],
                        'operate_uid' => $user_id,
                        'water_no' => $water_no,
                        'relate_type' => 2,
                        'relate_id' => $OrderProcessCourseId,//员工领料表ID
                        'place_name' => $val['residual_place_name'],//堆位
                    ];

                }else{
                    $tmpStockData = [
                        'number' => $val['residual_number'],
                        'product_id' => $val['material_id'],
                        'company_id' => $companyId,
                        //调整为实际操作的uid zhuyujun 20190514
//                        'operate_uid' => $orderProcessCourseInfo['uid'],
                        'operate_uid' => $user_id,
                        'water_no' => $water_no,
                        'relate_type' => 2,
                        'relate_id' => $OrderProcessCourseId,//员工领料表ID
                        'place_name' => $val['residual_place_name'],//堆位
                    ];
                }

                $temp_product_id = $val['material_id'];
                $temp_product_num = $val['residual_number'];
                $product_id_arr[] = $temp_product_id;
                $product_num_arr[$temp_product_id] = $temp_product_num;

                $stockData[] = $tmpStockData;
                $stockResult = self::addStockData($tmpStockData);

                ////追加流水和被扫码材料的关系
                //获取流水ID
                $stockRow = \App\Eloquent\Ygt\Stock::where(['water_no'=>$water_no])->orderBy('id', 'desc')->first();
                if($stockRow){
                    $stockId = $stockRow->id;

                    //获取需关联的材料二维码ID
                    $orderMaterialQrLogList = \App\Eloquent\Ygt\OrderMaterialQrLog::where(['order_process_course_id'=>$OrderProcessCourseId,'material_id'=>$val['material_id'],'type'=>1])->get();
                    foreach ($orderMaterialQrLogList as $orderMaterialQrLogRow){
                        $qrId = $orderMaterialQrLogRow['qr_id'];

                        //添加数据到关联表
                        $qrcodeLogObj = \App\Eloquent\Ygt\QrcodeLog::firstOrNew(['id'=>'']);
                        $tmpInsertRow = [
                            'type'=> 2,//材料领材
                            'qrcode_id'=> $qrId,
                            'table_name'=> 'ygt_stock',
                            'table_id'=> $stockId,
                        ];
                        $qrcodeLogObj->fill($tmpInsertRow);
                        $qrcodeLogObj->save();
                    }
                }

            }
        }

        //处理二维码对应材料现在的数量
        foreach ($orderMaterialQrLogDealList as $qrId => $orderMaterialQrLogDealRow){
            $qrcodeObj = \App\Eloquent\Ygt\Qrcode::where(['id'=>$qrId])->first();
            if($qrcodeObj){
                $qrcodeObj->now_number = $orderMaterialQrLogDealRow['num'];
                $qrcodeObj->save();
            }
        }

//        if(!empty($stockData)){
//            $stockResult = self::addStockData($stockData);
//        }else{
//            $stockResult=false;
//        }


        if ($stockResult) {

            $stockMessagePrivilegeIds = EnginePermission::getStepActionPrivilegeIdsByOrderTypeProcess($orderTypeId, $orderProcessInfo['process_type'], 6);

            $where                      = ['id'=>['in',$product_id_arr]];
            $product_list_collection    = ProductModel::getData( $where )->toArray();
            $message_content            = '';
            foreach( $product_list_collection as $key=>$val ){
                $product_id             = $val['id'];
                $product_name           = $val['product_name'];
                $product_unit           = $val['unit'];
                $product_num            = $product_num_arr[$product_id];
                $temp_product           = $product_name.' '.$product_num.' '.$product_unit;
                $message_content        .= $temp_product.'rnrn';
            }
            if( $message_content ){
                $message_content        = mb_substr( $message_content, 0, -4 );
            }
            foreach ($stockMessagePrivilegeIds as $privilegeId) {
                $data           = [
                    'company_id'=>$orderProcessInfo['company_id'],
                    'privilege_id'=>$privilegeId,
                    //调整为实际操作的uid zhuyujun 20190514
//                    'form_user_id'=>$orderProcessCourseInfo['uid'],
                    'form_user_id'=>$user_id,
                    'to_user_id'=>'',
                    'foreign_key'=>$foreignKey,
                    'type'=>6,
                    'type_id'=>$OrderProcessCourseId,
                    'title'=>$process_title.'-材料入库流水',
                    'content'=>$message_content,
                    'theme'=>'工单余料',
                ];
                \App\Eloquent\Ygt\UserMessage::sendCustomerOrderMessage($data);
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $OrderProcessCourseId
     * @return bool
     * 工单工序员工余料回库
     */
    public static function OrderMaterialSubmitGradationStockIn($OrderMaterialSubmitGradationId)
    {
        //获取登陆用户信息
        $userId = \App\Engine\Func::getHeaderValueByName('userid');
        $userInfo = \App\Eloquent\Ygt\DepartmentUser::getCurrentInfo($userId)->toArray();


        $tmpObj = \App\Eloquent\Ygt\OrderProcessMaterialSubmit::where(['id'=>$OrderMaterialSubmitGradationId])->first();
        if($tmpObj){
            $OrderProcessCourseId = $tmpObj->order_process_course_id;
        }else{
            return false;
        }


        $orderProcessCourseInfo = OrderProcessCourseModel::where('id', $OrderProcessCourseId)->first()->toArray();

        //工序ID
        $orderProcessId = $orderProcessCourseInfo['order_process_id'];
        $companyId = $orderProcessCourseInfo['company_id'];

        //工单工序信息
        $orderProcessInfo = OrderProcessModel::where('id', $orderProcessId)->first();
        $process_type     = $orderProcessInfo->process_type;
        $process          = ProcessModel::getInfo( ['id'=>$process_type] );
        $process_title    = $process->title;
//        $orderId = $orderProcessInfo['id'];
        $orderId = $orderProcessInfo['order_id'];

        $orderInfo = Order::where('id', $orderId)->first()->toArray();
        $orderTypeId = $orderInfo['order_type'];
        $foreignKey = intval($orderInfo['customer_order_id']);

        $OrderMaterials = \App\Eloquent\Ygt\OrderProcessMaterialSubmitDetail::where(['order_process_material_submit_id'=>$OrderMaterialSubmitGradationId,'type'=>1])->get();

        $stockData = [];
        $product_id_arr     = $product_num_arr = [];
        $stockResult=false;
        $orderMaterialQrLogDealList = [];//按二维码ID分组
        foreach ($OrderMaterials as $key => $val) {
            if($val['residual_number'] >0){

                //延后处理 zhuyujun 20181204
//                //修改二维码对应的实际材料的数量
//                $orderMaterialQrLogList = \App\Eloquent\Ygt\OrderMaterialQrLog::whereIn('type',[1])->where(['order_process_course_id' => $OrderProcessCourseId, 'material_id' => $val['material_id']])->get();
//                foreach ($orderMaterialQrLogList as $orderMaterialQrLogRow){
//                    if(isset($orderMaterialQrLogDealList[$orderMaterialQrLogRow['qr_id']])){
//                        $orderMaterialQrLogDealList[$orderMaterialQrLogRow['qr_id']]['num'] += $orderMaterialQrLogRow['num'];
//                    }else{
//                        $orderMaterialQrLogDealList[$orderMaterialQrLogRow['qr_id']]['num'] = $orderMaterialQrLogRow['num'];
//                    }
//                }

                $water_no = self::createWaterNo();
                //如果是集合材料，取相关联的第一个材料
                //考虑集合材料的问题

                $val['material_id'] = $val['relate_id'];//字段转换
                if(strstr($val['material_id'],'A')){
                    $tmpAssemblageMaterialId = str_replace('A','',$val['material_id']);
                    $relateMaterialRow = \App\Eloquent\Ygt\Product::where(['assemblage_material_id'=>$tmpAssemblageMaterialId])->first();
                    $relateMaterialId = $relateMaterialRow['id'];

                    $tmpStockData = [
                        'number' => $val['residual_number'],
                        'product_id' => $relateMaterialId,
                        'company_id' => $companyId,
                        //调整为实际操作的uid zhuyujun 20190514
//                        'operate_uid' => $orderProcessCourseInfo['uid'],
                        'operate_uid' => $userId,
                        'water_no' => $water_no,
                        'relate_type' => 2,
                        'relate_id' => $OrderProcessCourseId,//员工领料表ID
                        'place_name' => $val['place_name'],//堆位
                    ];

                }else{
                    $tmpStockData = [
                        'number' => $val['residual_number'],
                        'product_id' => $val['material_id'],
                        'company_id' => $companyId,
                        //调整为实际操作的uid zhuyujun 20190514
//                        'operate_uid' => $orderProcessCourseInfo['uid'],
                        'operate_uid' => $userId,
                        'water_no' => $water_no,
                        'relate_type' => 2,
                        'relate_id' => $OrderProcessCourseId,//员工领料表ID
                        'place_name' => $val['place_name'],//堆位
                    ];
                }

                $temp_product_id = $val['material_id'];
                $temp_product_num = $val['residual_number'];
                $product_id_arr[] = $temp_product_id;
                $product_num_arr[$temp_product_id] = $temp_product_num;

                $stockData[] = $tmpStockData;
                $stockResult = self::addStockData($tmpStockData);

                ////追加流水和被扫码材料的关系
                //获取流水ID
                $stockRow = \App\Eloquent\Ygt\Stock::where(['water_no'=>$water_no])->orderBy('id', 'desc')->first();
                if($stockRow){
                    $stockId = $stockRow->id;

                    //获取需关联的材料二维码ID
                    $orderMaterialQrLogList = \App\Eloquent\Ygt\OrderMaterialQrLog::where(['order_process_course_id'=>$OrderProcessCourseId,'material_id'=>$val['material_id'],'type'=>1])->get();
                    foreach ($orderMaterialQrLogList as $orderMaterialQrLogRow){
                        $qrId = $orderMaterialQrLogRow['qr_id'];

                        //添加数据到关联表
                        $qrcodeLogObj = \App\Eloquent\Ygt\QrcodeLog::firstOrNew(['id'=>'']);
                        $tmpInsertRow = [
                            'type'=> 2,//材料领材
                            'qrcode_id'=> $qrId,
                            'table_name'=> 'ygt_stock',
                            'table_id'=> $stockId,
                        ];
                        $qrcodeLogObj->fill($tmpInsertRow);
                        $qrcodeLogObj->save();
                    }
                }
            }
        }

        //处理二维码对应材料现在的数量
        foreach ($orderMaterialQrLogDealList as $qrId => $orderMaterialQrLogDealRow){
            $qrcodeObj = \App\Eloquent\Ygt\Qrcode::where(['id'=>$qrId])->first();
            if($qrcodeObj){
                $qrcodeObj->now_number = $orderMaterialQrLogDealRow['num'];
                $qrcodeObj->save();
            }
        }

//        if(!empty($stockData)){
//            $stockResult = self::addStockData($stockData);
//        }else{
//            $stockResult=false;
//        }


        if ($stockResult) {
            $stockMessagePrivilegeIds = EnginePermission::getStepActionPrivilegeIdsByOrderTypeProcess($orderTypeId, $orderProcessInfo['process_type'], 6);

            $where                      = ['id'=>['in',$product_id_arr]];
            $product_list_collection    = ProductModel::getData( $where )->toArray();
            $message_content            = '';
            foreach( $product_list_collection as $key=>$val ){
                $product_id             = $val['id'];
                $product_name           = $val['product_name'];
                $product_unit           = $val['unit'];
                $product_num            = $product_num_arr[$product_id];
                $temp_product           = $product_name.' '.$product_num.' '.$product_unit;
                $message_content        .= $temp_product.'rnrn';
            }
            if( $message_content ){
                $message_content        = mb_substr( $message_content, 0, -4 );
            }
            foreach ($stockMessagePrivilegeIds as $privilegeId) {
                $data           = [
                    'company_id'=>$orderProcessInfo['company_id'],
                    'privilege_id'=>$privilegeId,
                    //调整为实际操作的uid zhuyujun 20190514
//                    'form_user_id'=>$orderProcessCourseInfo['uid'],
                    'form_user_id'=>$userId,
                    'to_user_id'=>'',
                    'foreign_key'=>$foreignKey,
                    'type'=>6,
                    'type_id'=>$OrderProcessCourseId,
                    'title'=>$process_title.'-材料入库流水',
                    'content'=>$message_content,
                    'theme'=>'工单余料',
                ];
                \App\Eloquent\Ygt\UserMessage::sendCustomerOrderMessage($data);
            }
            return true;
        } else {
            return false;
        }
    }




    /**
     * @param $OrderProcessCourseId
     * @return mixed
     * 工单工序员工领料出库
     */
    public static function OrderProcessCourseStockOut($OrderProcessCourseId)
    {

        //获取登陆用户信息
        $user_id = \App\Engine\Func::getHeaderValueByName('userid');
        $user_info = \App\Eloquent\Ygt\DepartmentUser::getCurrentInfo($user_id)->toArray();

        $orderProcessCourseInfo = OrderProcessCourseModel::where('id', $OrderProcessCourseId)->first()->toArray();

        //工序ID
        $orderProcessId = $orderProcessCourseInfo['order_process_id'];
        $companyId = $orderProcessCourseInfo['company_id'];

        //工单工序信息
        $orderProcessInfo = OrderProcessModel::where('id', $orderProcessId)->first();
        $process_type     = $orderProcessInfo->process_type;
        $process          = ProcessModel::getInfo( ['id'=>$process_type] );
        $process_title    = $process->title;
//        $orderId = $orderProcessInfo['id'];

        $orderId = $orderProcessInfo['order_id'];

        $orderInfo = Order::where('id', $orderId)->first()->toArray();
        $orderTypeId = $orderInfo['order_type'];
        $foreignKey = intval($orderInfo['customer_order_id']);

        $OrderMaterials = OrderMaterialCourseModel::where([['order_process_course_id', $OrderProcessCourseId]])->get();

        $stockData = [];
        $stockResult = false;
        $product_id_arr = $product_num_arr = $product_unit_arr = [];
        foreach ($OrderMaterials as $key => $val) {
            $water_no = self::createWaterNo();
            $tmpStockData = [
                'number' => $val['receive_number'],
                'product_id' => $val['material_id'],
                'company_id' => $companyId,
                //调整为实际操作的uid zhuyujun 20190514
//                'operate_uid' => $orderProcessCourseInfo['uid'],
                'operate_uid' => $user_id,
                'water_no' => $water_no,
                'relate_type' => 1,
                'relate_id' => $OrderProcessCourseId//员工领料表ID
            ];

            $temp_product_id = $val['material_id'];
            $temp_product_num = $val['receive_number'];
            $product_id_arr[] = $temp_product_id;
            $product_num_arr[$temp_product_id] = $temp_product_num;

            $stockData[] = $tmpStockData;
            $stockResult = self::addStockData($tmpStockData);

            ////追加流水和被扫码材料的关系
            //获取流水ID
            $stockRow = \App\Eloquent\Ygt\Stock::where(['water_no'=>$water_no])->orderBy('id', 'desc')->first();
            if($stockRow){
                $stockId = $stockRow->id;

                //获取需关联的材料二维码ID
                $orderMaterialQrLogList = \App\Eloquent\Ygt\OrderMaterialQrLog::where(['order_process_course_id'=>$OrderProcessCourseId,'material_id'=>$val['material_id'],'type'=>4])->get();
                foreach ($orderMaterialQrLogList as $orderMaterialQrLogRow){
                    $qrId = $orderMaterialQrLogRow['qr_id'];

                    //添加数据到关联表
                    $qrcodeLogObj = \App\Eloquent\Ygt\QrcodeLog::firstOrNew(['id'=>'']);
                    $tmpInsertRow = [
                        'type'=> 2,//材料领材
                        'qrcode_id'=> $qrId,
                        'table_name'=> 'ygt_stock',
                        'table_id'=> $stockId,
                    ];
                    $qrcodeLogObj->fill($tmpInsertRow);
                    $qrcodeLogObj->save();
                }
            }
        }



        if ($stockResult) {

            $UserRow = DepartmentUser::getInfo(['user_id' => $orderProcessCourseInfo['uid']])->toArray();

            $stockMessagePrivilegeIds = EnginePermission::getStepActionPrivilegeIdsByOrderTypeProcess($orderTypeId, $orderProcessInfo['process_type'], 6);

            $where                      = ['id'=>['in',$product_id_arr]];
            $product_list_collection    = ProductModel::getData( $where )->toArray();
            $message_content            = '';
            foreach( $product_list_collection as $key=>$val ){
                $product_id             = $val['id'];
                $product_name           = $val['product_name'];
                $product_unit           = $val['unit'];
                $product_num            = $product_num_arr[$product_id];
                $temp_product           = $product_name.' '.$product_num.' '.$product_unit;
                $message_content        .= $temp_product.'rnrn';
            }
            if( $message_content ){
                $message_content        = mb_substr( $message_content, 0, -4 );
            }
            foreach ($stockMessagePrivilegeIds as $privilegeId) {
                //发送消息
                $data           = [
                    'company_id'=>$orderProcessInfo['company_id'],
                    'privilege_id'=>$privilegeId,
                    //调整为实际操作的uid zhuyujun 20190514
//                    'form_user_id'=>$orderProcessCourseInfo['uid'],
                    'form_user_id'=>$user_id,
                    'to_user_id'=>'',
                    'foreign_key'=>$foreignKey,
                    'type'=>5,
                    'type_id'=>$OrderProcessCourseId,
                    'title'=>$process_title.'-材料出库流水',
                    'content'=>$message_content
                ];
                \App\Eloquent\Ygt\UserMessage::sendCustomerOrderMessage($data);
            }
            return true;
        } else {
            return false;
        }
    }





    /**
     * @param $stockData
     * @return bool
     * 增加库存记录 并对库存做相应增减
     */
    public static function addStockData($stockData)
    {
        $stockModel = new StockModel();

        if (!is_array(reset($stockData))) {
            $stockData = [$stockData];
        }

        foreach ($stockData as $key => $val) {

            //20190402 多仓库调整
            //获取企业的默认仓库
            $default_storehouse_id = \App\Api\Service\Storehouse\Storehouse\Storehouse::getCompanyDefaultStorehouse($val['company_id'])->getId();

            $insertData[$key] = [
                'number' => $val['number'],
                'product_id' => $val['product_id'],
                'company_id' => $val['company_id'],
                'operate_uid' => isset($val['operate_uid'])?$val['operate_uid']:0,
                'water_no' => $val['water_no'],
                'relate_type' => $val['relate_type'],
                'relate_id' => $val['relate_id'],
                'place_name' => isset($val['place_name']) ? $val['place_name'] : '',
                'storehouse_id' => isset($default_storehouse_id) ? $default_storehouse_id : '',//仓库ID
            ];

            $RelateType = self::getRelateType($val['relate_type']);

            $absNumber = abs($val['number']);
            if ($RelateType['type'] == 1) {//入库流水，数量增加
                $insertData[$key]['number'] = $absNumber;
                ProductModel::addNumber($val['product_id'], $absNumber, $RelateType['number_field']);

                //多仓库结构后，需要同步更新仓库材料信息
                $materialResObj = \App\Api\Service\Storehouse\NodeAssignment\StorehouseManager::getTempStorehouseManager($val['company_id'])->getMaterialResObj($default_storehouse_id,$val['product_id'])->in($absNumber);

                //更新材料的计量属性
                $tmpProductObj = \App\Eloquent\Ygt\Product::where('id', $val['product_id'])->first();
                $materialComputeNum = \App\Engine\Material::getMaterialComputeNum($val['product_id'],$absNumber);
                if($materialComputeNum){
                    //获取计量属性字段
                    $tmpProductFieldsList = \App\Eloquent\Ygt\ProductFields::where(['assemblage_material_id'=>$tmpProductObj['assemblage_material_id']])->get();
                    foreach ($tmpProductFieldsList as $tmpProductFieldsRow){
                        if($tmpProductFieldsRow['is_compute']){
                            $tmpObj = \App\Eloquent\Ygt\ProductFieldsCompute::firstOrNew(['material_id'=>$val['product_id'],'type'=>1,'product_fields_id'=>$tmpProductFieldsRow['id']]);
                            $tmpObj->number = $tmpObj->number + $materialComputeNum;
                            $tmpObj->save();
                        }
                    }
                }

                //增加相关集合产品的数据
                $tmpProductRow = \App\Eloquent\Ygt\Product::where(['id'=>$val['product_id']])->first();
                if($tmpProductRow){
                    $tmpAssemblageMaterialId = $tmpProductRow['assemblage_material_id'];
                    $tmpAssemblageMaterialRow = \App\Eloquent\Ygt\AssemblageMaterial::where(['id'=>$tmpAssemblageMaterialId])->first();
                    if($tmpAssemblageMaterialRow){
                        $tmpAssemblageMaterialRow->number += $absNumber;
                        $tmpAssemblageMaterialRow->save();

                        //更新集合材料的计量属性
                        if($materialComputeNum){
                            //获取计量属性字段
//                            $tmpProductFieldsList = \App\Eloquent\Ygt\ProductFields::where(['assemblage_material_id'=>$tmpProductObj['assemblage_material_id']])->get();
                            foreach ($tmpProductFieldsList as $tmpProductFieldsRow){
                                if($tmpProductFieldsRow['is_compute']){
                                    $tmpObj = \App\Eloquent\Ygt\ProductFieldsCompute::firstOrNew(['material_id'=>$tmpAssemblageMaterialRow['id'],'type'=>2,'product_fields_id'=>$tmpProductFieldsRow['id']]);
                                    $tmpObj->number = $tmpObj->number + $materialComputeNum;
                                    $tmpObj->save();
                                }
                            }
                        }


                    }
                }

                switch ($RelateType['number_field']){
                    case 'number':
                        if(isset($val['supplier_id'])){

                            $supplierId = $val['supplier_id'];
                            ProductSupplier::updateOrCreate(['supplier_id'=>$supplierId,'product_id'=>$val['product_id']],['number'=>DB::raw("number + $absNumber")]);

//                            $productSupplier = ProductSupplier::firstOrNew(['supplier_id'=>$supplierId,'product_id'=>$val['product_id']]);
//                            $productSupplier->number = ($productSupplier->number + $absNumber);
//                            $productSupplier->save();

//                            ProductSupplier::where([['supplier_id',$supplierId],['product_id',$val['product_id']]])->increment('number', $absNumber);
                        }
                        break;
                    case 'customer_number':
                        if(isset($val['customer_id'])) {
                            $customerId = $val['customer_id'];
                            ProductCustomer::updateOrCreate(['customer_id'=>$customerId,'product_id'=>$val['product_id']],['number'=>DB::raw("number + $absNumber")]);
//                            ProductCustomer::where([['customer_id', $customerId], ['product_id', $val['product_id']]])->increment('number', $absNumber);
                        }
                        break;
                }
            } else {//出库流水，数量减少
                $insertData[$key]['number'] = -$absNumber;
                ProductModel::decreNumber($val['product_id'], $absNumber, $RelateType['number_field']);

                //多仓库结构后，需要同步更新仓库材料信息
                $materialResObj = \App\Api\Service\Storehouse\NodeAssignment\StorehouseManager::getTempStorehouseManager($val['company_id'])->getMaterialResObj($default_storehouse_id,$val['product_id'])->out($absNumber);

                //更新材料的计量属性
                $tmpProductObj = \App\Eloquent\Ygt\Product::where('id', $val['product_id'])->first();;
                $materialComputeNum = \App\Engine\Material::getMaterialComputeNum($val['product_id'],$absNumber);
                if($materialComputeNum){
                    //获取计量属性字段
                    $tmpProductFieldsList = \App\Eloquent\Ygt\ProductFields::where(['assemblage_material_id'=>$tmpProductObj['assemblage_material_id']])->get();
                    foreach ($tmpProductFieldsList as $tmpProductFieldsRow){
                        if($tmpProductFieldsRow['is_compute']){
                            $tmpObj = \App\Eloquent\Ygt\ProductFieldsCompute::firstOrNew(['material_id'=>$val['product_id'],'type'=>1,'product_fields_id'=>$tmpProductFieldsRow['id']]);
                            $tmpObj->number = $tmpObj->number - $materialComputeNum;
                            $tmpObj->save();
                        }
                    }
                }

                //减少相关集合产品的数据
                $tmpProductRow = \App\Eloquent\Ygt\Product::where(['id'=>$val['product_id']])->first();
                if($tmpProductRow){
                    $tmpAssemblageMaterialId = $tmpProductRow['assemblage_material_id'];
                    $tmpAssemblageMaterialRow = \App\Eloquent\Ygt\AssemblageMaterial::where(['id'=>$tmpAssemblageMaterialId])->first();
                    if($tmpAssemblageMaterialRow){
                        $tmpAssemblageMaterialRow->number -= $absNumber;
                        $tmpAssemblageMaterialRow->save();

                        //更新集合材料的计量属性
                        if($materialComputeNum){
                            //获取计量属性字段
                            $tmpProductFieldsList = \App\Eloquent\Ygt\ProductFields::where(['assemblage_material_id'=>$tmpProductObj['assemblage_material_id']])->get();
                            foreach ($tmpProductFieldsList as $tmpProductFieldsRow){
                                if($tmpProductFieldsRow['is_compute']){
                                    $tmpObj = \App\Eloquent\Ygt\ProductFieldsCompute::firstOrNew(['material_id'=>$tmpAssemblageMaterialRow['id'],'type'=>2,'product_fields_id'=>$tmpProductFieldsRow['id']]);
                                    $tmpObj->number = $tmpObj->number - $materialComputeNum;
                                    $tmpObj->save();
                                }
                            }
                        }

                    }
                }

                switch ($RelateType['number_field']){
                    case 'number':
                        if(isset($val['supplier_id'])){
                            $supplierId = $val['supplier_id'];
//                            $productSupplier = ProductSupplier::firstOrNew(['supplier_id'=>$supplierId,'product_id'=>$val['product_id']]);
//                            $productSupplier->number = ($productSupplier->number - $absNumber);
//                            $productSupplier->save();

                            ProductSupplier::updateOrCreate(['supplier_id'=>$supplierId,'product_id'=>$val['product_id']],['number'=>DB::raw("number - $absNumber")]);

//                            ProductSupplier::where([['id',$supplierId],['product_id',$val['product_id']]])->decrement('number', $absNumber);
                        }
                        break;
                    case 'customer_number':
                        if(isset($val['customer_id'])) {
                            $customerId = $val['customer_id'];
//                            ProductCustomer::where([['id', $customerId], ['product_id', $val['product_id']]])->decrement('number', $absNumber);
                            ProductCustomer::updateOrCreate(['customer_id'=>$customerId,'product_id'=>$val['product_id']],['number'=>DB::raw("number - $absNumber")]);
                        }
                        break;
                }
            }
            $lastNumber = ProductModel::where('id',$val['product_id'])->first();
            if($lastNumber){
                $insertData[$key]['stock_type'] = $RelateType['stock_type'];

                $tmpStr = $RelateType['number_field'];
//                $insertData[$key]['last_product_number'] = $lastNumber->$tmpStr;

                //流水中的剩余数量应该是仓库有关的 zhuyujun 20190506
                $cur_storehouse_number = $materialResObj->getNumber();
                $insertData[$key]['last_product_number'] = $cur_storehouse_number;
                $insertData[$key]['type'] = $RelateType['type'];
                $insertData[$key]['created_at'] = time();
            }else{
                //材料不存在的话，不进行处理，不入库
                unset($insertData[$key]);
            }
        }


        return $stockModel->insertData($insertData);

    }

    /**
     * 获取关联类型 对应的 入库类型
     */
    public static function getRelateType($relateTypeId = false)
    {
        $RelateTypes = [
            1 => [
                'title' => '工单领取材料',
                'color' => '#FFB401',
                'stock_type' => 3,//厂内出库
                'type' => 2,//出库
                'number_field' => 'number',//
            ],
            2 => [
                'title' => '工单余料回退',
                'color' => '#04C9B3',
                'stock_type' => 1,//厂内入库
                'type' => 1,//入库
                'number_field' => 'number',//
            ],
            3 => [
                'title' => '单个材料采购入库',
                'color' => '#04C9B3',
                'stock_type' => 2,//厂外入库 采购
                'type' => 1,//入库
                'number_field' => 'number',//
            ],
            4 => [
                'title' => '客户材料入库',
                'color' => '#04C9B3',
                'stock_type' => 2,//厂外入库 客户材料
                'type' => 1,//入库
                'number_field' => 'customer_number',//
            ],
            5 => [
                'title' => '订单领取材料',//领取客户自己的材料
                'color' => '#FFB401',
                'stock_type' => 3,//厂内出库
                'type' => 2,//入库
                'number_field' => 'customer_number',//
            ],
            6 => [
                'title' => '扫描出库',
                'color' => '#04C9B3',
                'stock_type' => 2,//厂外入库 客户材料
                'type' => 2,//入库
                'number_field' => 'number',//
            ],
            7 => [
                'title' => '扫描入库',
                'color' => '#04C9B3',
                'stock_type' => 2,//厂外入库 采购
                'type' => 1,//入库
                'number_field' => 'number',//
            ],
            8 => [
                'title' => '修改提交用料-入库',
                'color' => '#04C9B3',
                'stock_type' => 2,//厂外入库 采购
                'type' => 1,//入库
                'number_field' => 'number',//
            ],
            9 => [
                'title' => '修改提交用料-出库',
                'color' => '#FFB401',
                'stock_type' => 3,//厂内出库
                'type' => 2,//出库
                'number_field' => 'number',//
            ],
        ];
        if ($relateTypeId === false) {
            return $RelateTypes;
        } else {
            if ($relateTypeId) {
                return $RelateTypes[$relateTypeId];
            } else {
                return [
                    'title'=>'未知类型',
                    'color' => '#cccccc',
                    'stock_type'=>0,
                    'type'=>0,
                ];
            }
        }
    }

    /**
     * @param $productId 产品ID
     * @param $number 出库数量
     * @param $orderProcessId 工单工序表ID
     * @param $orderMaterialId 工单领料表ID
     * 出库 损耗 厂内出库
     */
    public static function inFactoryStockOut($productId, $number, $orderId, $orderProcessId, $orderMaterialId)
    {

//        $OrderMaterialInfo = self::getOrderMaterialByOrderIdAndProcessId($orderId, $processId);
//        $productId = $OrderMaterialInfo['material_id'];
//        $orderProcessInfo = OrderProcessModel::where('id',$orderProcessId)->first();
        //开始入库
        $stockModel = new StockModel();
        $water_no = self::createWaterNo();
        $stockData = ['number' => -$number, 'product_id' => $productId, 'water_no' => $water_no, 'type' => 2, 'stock_type' => 3];


        if ($stockId = $stockModel->addStock($stockData)) {

            $stockOutData = [
                'stock_id' => $stockId,
                'order_id' => $orderId,
                'order_process_id' => $orderProcessId,
                'order_material_id' => $orderMaterialId,
                'operator' => '操作员'
            ];

            StockOutModel::insert($stockOutData);

            //库存减少
            if (!ProductModel::decreNumber($productId, $number)) {
                xThrow(ERR_PRODUCT_DECREMENT_FAIL);
            }
        }
    }


    /**
     * 从工单材料表 根据ID 查询产品ID
     */
    private function getOrderMaterialById($orderMaterialId)
    {
        return OrderMaterialModel::where('id', $orderMaterialId)->select('id', 'material_id')->first();
    }

    /**
     * 从工单材料表 根据工单ID、工序ID 查询产品ID
     */
    private function getOrderMaterialByOrderIdAndProcessId($orderId, $processId)
    {
        return OrderMaterialModel::where([['order_id', $orderId], ['process_type', $processId]])->select('id', 'material_id')->first();
    }


    public static function createWaterNo($additional=0)
    {

        $dayStartTime = strtotime(date('Ymd'));
//        $intradayOrderCount = \App\Eloquent\Ygt\WarehouseSend::withTrashed()->where([['created_at', '>=', $dayStartTime]])->get()->count();
        $intradayOrderCount = \App\Eloquent\Ygt\Stock::where([['created_at', '>=', $dayStartTime]])->get()->count();
        $intradayOrderCount = $additional ? ( $intradayOrderCount + $additional ) : $intradayOrderCount;

        $snIndex = '';
        $intradayOrderCount++;
        if ($intradayOrderCount < 10) {
            $snIndex = sprintf('0%d', $intradayOrderCount);
        } else {
            $snIndex = $intradayOrderCount;
        }
        $sn = 'CLLS' . date('ymd') . $snIndex;

        return $sn;

//        $water_no = date('YmdHis') . rand(100000, 999999);
//        return $water_no;
    }
    
    
    public static function stockInPrint($where){
        $data = StockModel::getData($where);
        foreach ($data as $water){
            $productInfo = ProductModel::where('id',$water['product_id'])->first();

            /*$printStr = '';
            $printStr .= Printer::strong($productInfo->product_name,'center');
            $printStr .= Printer::br();
            $printStr .= Printer::normal($productInfo->product_no,'center');
            $printStr .= Printer::br();
            $printStr .= Printer::br();

            $printStr .= Printer::keyValue(['数量'=>$water['number'],'规格'=>$productInfo->spec]);

            $printStr .= Printer::br();
            $printStr .= Printer::br();
            $printStr .= Printer::qrcode($productInfo->product_no);
            $printStr .= Printer::br();
            $printStr .= Printer::normal($water['water_no'],'center');
            Printer::p($printStr);*/
            
            
            KmPrinter::text($productInfo->product_name);
            KmPrinter::text('['.$productInfo->product_no.']');


            $productFields = ProductFields::where('product_id',$water['product_id'])->get();
            foreach ($productFields as $val){
                $comumnName = EngineProduct::getFieldColumn($val->field_type);

                KmPrinter::text($val->field_name.':'.$val->$comumnName);
            }

            $qrcodeInfo = [
                'w'=>$water['water_no'],
                'm'=>$productInfo->product_no
            ];
            foreach ($qrcodeInfo as $key=>$val){
                $tmp[] = $key.':'.$val;
            }
            $qrcode = implode(',',$tmp);

            KmPrinter::qrcode($qrcode);
            KmPrinter::text($water['water_no']);

            switch ($water['relate_type']){
                case 1:
                    break;
                case 2:
                    break;
                case 3:
                    $stockInInfo = StockInModel::where('id',$water['relate_id'])->first();
                    KmPrinter::text('【配送单位】' . $stockInInfo['distribution_company']);
                    break;
            }

            $printInfo = KmPrinter::p();
            return $printInfo;

        }
    }

    public static function composeList($stockList){
        $stockList->transform(function($item){
            $relateTypeInfo = self::getRelateType($item->relate_type);
            $item->relate_type_title = $relateTypeInfo['title'];
            $item->relate_type_color = $relateTypeInfo['color'];
            $item->type_name = $relateTypeInfo['title'];
            $item->type_color = $relateTypeInfo['color'];
            $item->created_time = $item->created_at->formatLocalized('%Y-%m-%d %H:%M:%S');
            $item->operate_name = $item->operate_name?$item->operate_name:'未知';
            //补齐 供应商 归属人 属性
            if($item->customer_id){
                $item->customer_name = \App\Eloquent\Ygt\Customer::getOneValueById($item->customer_id,'customer_name');
            }

            if($item->seller_company_id){
                $item->supplier_name = \App\Eloquent\Ygt\SellerCompany::getOneValueById($item->seller_company_id,'title');
            }

            if($item->assemblage_material_id){
                $tempAttrList = \App\Engine\Material::getMaterialField($item->assemblage_material_id);
                $item->custom_fields_text = $tempAttrList['custom_fields_text'];
            }

            $item->number = $item->number.$item->unit;
            $item->residual_number = $item->residual_number.$item->unit;

            return $item;
        });

        return $stockList;
    }

    public static function getWaterList($where, $page = 1, $limit = 10){
        $limit = 10;
        $offset = ($page - 1) * $limit;
        $field = 'ygt_stock.id'.
            ',ygt_stock.water_no'.
            ',ygt_stock.product_id'.
            ',ygt_stock.number'.
            ',ygt_stock.type'.
            ',ygt_stock.relate_type'.
            ',ygt_stock.last_product_number'.
            ',ygt_stock.operate_uid'.
            ',ygt_stock.relate_id'.
            ',ygt_stock.created_at'.
            ',ygt_stock.place_name'.
            ',ygt_product.product_name'.
            ',ygt_product.product_no'.
            ',ygt_user.truename as operate_name';

        $stockModel = new StockModel();

        $stockList = $stockModel->getStockList($where, $field, $limit, $offset);
        return $stockList;
    }



    //新封装的出库流水添加方法 zhuyujun 20180731
    public static function addOutgoingWater($addDate){

        $stockData = [];
        $water_no = self::createWaterNo();
        $stockData[] = [
            'number' => $addDate['number'],
            'product_id' => $addDate['material_id'],
            'company_id' => $addDate['company_id'],
            'operate_uid' => $addDate['uid'],
            'water_no' => $water_no,
            'relate_type' => $addDate['relate_type'],
            'relate_id' => $addDate['relate_id']
        ];
        $stockResult = self::addStockData($stockData);

        //通过流水号获取流水ID

        $stockRow = \App\Eloquent\Ygt\Stock::where(['water_no'=>$water_no])->orderBy('id', 'desc')->first();

        if($stockRow){
            return $stockRow->id;
        }



        return false;

//        if ($stockResult) {
//
//            $UserRow = DepartmentUser::getInfo(['user_id' => $orderProcessCourseInfo['uid']])->toArray();
//
//            $stockMessagePrivilegeIds = EnginePermission::getStepActionPrivilegeIdsByOrderTypeProcess($orderTypeId, $orderProcessInfo['process_type'], 6);
//
//            $where                      = ['id'=>['in',$product_id_arr]];
//            $product_list_collection    = ProductModel::getData( $where )->toArray();
//            $message_content            = '';
//            foreach( $product_list_collection as $key=>$val ){
//                $product_id             = $val['id'];
//                $product_name           = $val['product_name'];
//                $product_unit           = $val['unit'];
//                $product_num            = $product_num_arr[$product_id];
//                $temp_product           = $product_name.' '.$product_num.' '.$product_unit;
//                $message_content        .= $temp_product.'rnrn';
//            }
//            if( $message_content ){
//                $message_content        = mb_substr( $message_content, 0, -4 );
//            }
//            foreach ($stockMessagePrivilegeIds as $privilegeId) {
//                //发送消息
//                $data           = [
//                    'company_id'=>$orderProcessInfo['company_id'],  'privilege_id'=>$privilegeId,
//                    'form_user_id'=>$orderProcessCourseInfo['uid'], 'to_user_id'=>'',
//                    'foreign_key'=>$foreignKey,
//                    'type'=>5,'type_id'=>$OrderProcessCourseId,
//                    'title'=>$process_title.'-材料出库流水','content'=>$message_content
//                ];
//                \App\Eloquent\Ygt\UserMessage::sendCustomerOrderMessage($data);
//            }

//        } else {
//            return false;
//        }



    }


    /**
     * @param $OrderProcessCourseId
     * @return mixed
     * 工单工序员工分次领料出库
     * zhuyujun 20180815
     */
    public static function OrderMaterialReceiveGradationStockOut($OrderMaterialReceiveGradationId)
    {
        //获取登陆用户信息
        $userId = \App\Engine\Func::getHeaderValueByName('userid');
        $userInfo = \App\Eloquent\Ygt\DepartmentUser::getCurrentInfo($userId)->toArray();


        $tmpObj = \App\Eloquent\Ygt\OrderMaterialReceiveGradation::where(['id'=>$OrderMaterialReceiveGradationId])->first();
        if($tmpObj){
            $OrderProcessCourseId = $tmpObj->order_process_course_id;
        }else{
            return false;
        }


        $orderProcessCourseInfo = OrderProcessCourseModel::where('id', $OrderProcessCourseId)->first()->toArray();

        //工序ID
        $orderProcessId = $orderProcessCourseInfo['order_process_id'];
        $companyId = $orderProcessCourseInfo['company_id'];

        //工单工序信息
        $orderProcessInfo = OrderProcessModel::where('id', $orderProcessId)->first();
        $process_type     = $orderProcessInfo->process_type;
        $process          = ProcessModel::getInfo( ['id'=>$process_type] );
        $process_title    = $process->title;
//        $orderId = $orderProcessInfo['id'];

        $orderId = $orderProcessInfo['order_id'];

        $orderInfo = Order::where('id', $orderId)->first()->toArray();
        $orderTypeId = $orderInfo['order_type'];
        $foreignKey = intval($orderInfo['customer_order_id']);

        $OrderMaterials = \App\Eloquent\Ygt\OrderMaterialReceiveGradation::where(['id'=>$OrderMaterialReceiveGradationId])->get();

        $stockData = [];
        $stockResult = false;
        $product_id_arr = $product_num_arr = $product_unit_arr = [];
        foreach ($OrderMaterials as $key => $val) {
            $water_no = self::createWaterNo();

            //如果是集合材料，取相关联的第一个材料
            //考虑集合材料的问题
            if(strstr($val['material_id'],'A')){
                $tmpAssemblageMaterialId = str_replace('A','',$val['material_id']);
                $relateMaterialRow = \App\Eloquent\Ygt\Product::where(['assemblage_material_id'=>$tmpAssemblageMaterialId])->first();
                $relateMaterialId = $relateMaterialRow['id'];

                $tmpStockData = [
                    'number' => $val['num'],
                    'product_id' => $relateMaterialId,
                    'company_id' => $companyId,
//                    'operate_uid' => $orderProcessCourseInfo['uid'],
                    'operate_uid' => $userId,
                    'water_no' => $water_no,
                    'relate_type' => 1,
                    'relate_id' => $OrderProcessCourseId//员工领料表ID
                ];

            }else{
                $tmpStockData = [
                    'number' => $val['num'],
                    'product_id' => $val['material_id'],
                    'company_id' => $companyId,
//                    'operate_uid' => $orderProcessCourseInfo['uid'],
                    'operate_uid' => $userId,
                    'water_no' => $water_no,
                    'relate_type' => 1,
                    'relate_id' => $OrderProcessCourseId//员工领料表ID
                ];
            }



            $temp_product_id = $val['material_id'];
            $temp_product_num = $val['num'];
            $product_id_arr[] = $temp_product_id;
            $product_num_arr[$temp_product_id] = $temp_product_num;

            $stockData[] = $tmpStockData;
            $stockResult = self::addStockData($tmpStockData);

            ////追加流水和被扫码材料的关系
            //获取流水ID
            $stockRow = \App\Eloquent\Ygt\Stock::where(['water_no'=>$water_no])->orderBy('id', 'desc')->first();
            if($stockRow){
                $stockId = $stockRow->id;

                //获取需关联的材料二维码ID
                $orderMaterialQrLogList = \App\Eloquent\Ygt\OrderMaterialQrLog::where(['order_process_course_id'=>$OrderProcessCourseId,'order_material_recevie_gradation_id'=>$OrderMaterialReceiveGradationId,'material_id'=>$val['material_id'],'type'=>4])->get();
                foreach ($orderMaterialQrLogList as $orderMaterialQrLogRow){
                    $qrId = $orderMaterialQrLogRow['qr_id'];

                    //添加数据到关联表
                    $qrcodeLogObj = \App\Eloquent\Ygt\QrcodeLog::firstOrNew(['id'=>'']);
                    //获取当前码的数量

                    $tmpInsertRow = [
                        'type'=> 2,//材料领材
                        'qrcode_id'=> $qrId,
                        'table_name'=> 'ygt_stock',
                        'table_id'=> $stockId,
//                        'number'=> $val['num'],
                    ];
                    $qrcodeLogObj->fill($tmpInsertRow);
                    $qrcodeLogObj->save();
                }
            }
        }


        if ($stockResult) {
            $UserRow = DepartmentUser::getInfo(['user_id' => $orderProcessCourseInfo['uid']])->toArray();
            $stockMessagePrivilegeIds = EnginePermission::getStepActionPrivilegeIdsByOrderTypeProcess($orderTypeId, $orderProcessInfo['process_type'], 6);

            $where                      = ['id'=>['in',$product_id_arr]];
            $product_list_collection    = ProductModel::getData( $where )->toArray();
            $message_content            = '';
            foreach( $product_list_collection as $key=>$val ){
                $product_id             = $val['id'];
                $product_name           = $val['product_name'];
                $product_unit           = $val['unit'];
                $product_num            = $product_num_arr[$product_id];
                $temp_product           = $product_name.' '.$product_num.' '.$product_unit;
                $message_content        .= $temp_product.'rnrn';
            }
            if( $message_content ){
                $message_content        = mb_substr( $message_content, 0, -4 );
            }
            foreach ($stockMessagePrivilegeIds as $privilegeId) {
                //发送消息
                //调整为给工序管理员发消息 zhuyujun 20180822

//                $fromUserId = $orderProcessInfo['uid'];
                $fromUserId = $userId;

                $data           = [
                    'company_id'=>$orderProcessInfo['company_id'],
                    'privilege_id'=>$privilegeId,
                    'form_user_id'=>$fromUserId,
                    'to_user_id'=>'',
                    'foreign_key'=>$foreignKey,
                    'type'=>5,
                    'type_id'=>$OrderProcessCourseId,
                    'title'=>$process_title.'-材料出库流水',
                    'content'=>$message_content
                ];
                \App\Eloquent\Ygt\UserMessage::sendCustomerOrderMessage($data);
            }
            return true;
        } else {
            return false;
        }
    }


    /**
     * 领料过程中删除材料导致的材料回退
     * zhuyujun
     * 20180824
     */
    public static function OrderMateialDeleteStockIn($orderProcessCourseId,$materialId,$userId){
        //获取登陆用户信息
        $userId = \App\Engine\Func::getHeaderValueByName('userid');
        // hjn 20190831 无用代码
        //$userInfo = \App\Eloquent\Ygt\DepartmentUser::getCurrentInfo($userId)->toArray();

        //获取材料数量
        $orderMaterialCourseRow = \App\Eloquent\Ygt\OrderMaterialCourse::where(['order_process_course_id'=>$orderProcessCourseId,'material_id'=>$materialId])->first();

        if(!$orderMaterialCourseRow){
            return false;
        }else{
            //兼容旧的方法
            $OrderMaterials = [];
            $OrderMaterials[] = [
                'material_id' => $materialId,
                'num' => $orderMaterialCourseRow['receive_number'],
            ];
//            x($OrderMaterials);
            //删除记录
            $orderMaterialCourseRow->delete();

        }
        //添加回退流水

        $OrderProcessCourseId = $orderProcessCourseId;
        $orderProcessCourseInfo = OrderProcessCourseModel::where('id', $OrderProcessCourseId)->first()->toArray();

        //工序ID
        $orderProcessId = $orderProcessCourseInfo['order_process_id'];
        $companyId = $orderProcessCourseInfo['company_id'];

        //工单工序信息
        $orderProcessInfo = OrderProcessModel::where('id', $orderProcessId)->first();
        $process_type     = $orderProcessInfo->process_type;
        $process          = ProcessModel::getInfo( ['id'=>$process_type] );
        $process_title    = $process->title;
//        $orderId = $orderProcessInfo['id'];

        $orderId = $orderProcessInfo['order_id'];

        $orderInfo = Order::where('id', $orderId)->first()->toArray();
        $orderTypeId = $orderInfo['order_type'];
        $foreignKey = intval($orderInfo['customer_order_id']);

        $stockData = [];
        $stockResult = false;
        $product_id_arr = $product_num_arr = $product_unit_arr = [];
        foreach ($OrderMaterials as $key => $val) {


            //hjn 20190831 可用库存操作
            $Storehouse = new Storehouse();
            $Storehouse->declareNumber($val['material_id'],$val['num'],1,"+");

            $water_no = self::createWaterNo();
            $tmpStockData = [
                'number' => $val['num'],
                'product_id' => $val['material_id'],
                'company_id' => $companyId,
                'operate_uid' => $userId,
                'water_no' => $water_no,
                'relate_type' => 2,
                'relate_id' => $OrderProcessCourseId//员工领料表ID
            ];

            $temp_product_id = $val['material_id'];
            $temp_product_num = $val['num'];
            $product_id_arr[] = $temp_product_id;
            $product_num_arr[$temp_product_id] = $temp_product_num;

            $stockData[] = $tmpStockData;
            $stockResult = self::addStockData($tmpStockData);

            ////追加流水和被扫码材料的关系
            //获取流水ID
            $stockRow = \App\Eloquent\Ygt\Stock::where(['water_no'=>$water_no])->orderBy('id', 'desc')->first();
            if($stockRow){
                $stockId = $stockRow->id;

                //获取需关联的材料二维码ID
                $orderMaterialQrLogList = \App\Eloquent\Ygt\OrderMaterialQrLog::where(['order_process_course_id'=>$OrderProcessCourseId,'material_id'=>$val['material_id'],'type'=>4])->get();
                foreach ($orderMaterialQrLogList as $orderMaterialQrLogRow){
                    $qrId = $orderMaterialQrLogRow['qr_id'];

                    //添加数据到关联表
                    $qrcodeLogObj = \App\Eloquent\Ygt\QrcodeLog::firstOrNew(['id'=>'']);
                    //获取当前码的数量

                    $tmpInsertRow = [
                        'type'=> 2,//材料领材
                        'qrcode_id'=> $qrId,
                        'table_name'=> 'ygt_stock',
                        'table_id'=> $stockId,
//                        'number'=> $val['num'],
                    ];
                    $qrcodeLogObj->fill($tmpInsertRow);
                    $qrcodeLogObj->save();

                    //修改码关联的材料数量
                    $tmpObj = \App\Eloquent\Ygt\Qrcode::where(['id'=>$qrId])->first();
                    if($tmpObj){
                        $tmpObj->now_number = $orderMaterialQrLogRow['num'];
                        $tmpObj->save();
                    }
                }
            }
        }


        if ($stockResult) {
//            $UserRow = DepartmentUser::getInfo(['user_id' => $orderProcessCourseInfo['uid']])->toArray();
            $stockMessagePrivilegeIds = EnginePermission::getStepActionPrivilegeIdsByOrderTypeProcess($orderTypeId, $orderProcessInfo['process_type'], 6);

            $where                      = ['id'=>['in',$product_id_arr]];
            $product_list_collection    = ProductModel::getData( $where )->toArray();
            $message_content            = '';
            foreach( $product_list_collection as $key=>$val ){
                $product_id             = $val['id'];
                $product_name           = $val['product_name'];
                $product_unit           = $val['unit'];
                $product_num            = $product_num_arr[$product_id];
                $temp_product           = $product_name.' '.$product_num.' '.$product_unit;
                $message_content        .= $temp_product.'rnrn';
            }
            if( $message_content ){
                $message_content        = mb_substr( $message_content, 0, -4 );
            }
            foreach ($stockMessagePrivilegeIds as $privilegeId) {
                //发送消息
                //调整为给工序管理员发消息 zhuyujun 20180822
//                $fromUserId = $orderProcessInfo['uid'];
                $fromUserId = $userId;

                $data           = [
                    'company_id'=>$orderProcessInfo['company_id'],
                    'privilege_id'=>$privilegeId,
                    'form_user_id'=>$fromUserId,
                    'to_user_id'=>'',
                    'foreign_key'=>$foreignKey,
                    'type'=>6,
                    'type_id'=>$OrderProcessCourseId,
                    'title'=>$process_title.'-材料入库流水',
                    'content'=>$message_content,
                    'theme'=>'工单余料',
                ];
                \App\Eloquent\Ygt\UserMessage::sendCustomerOrderMessage($data);

            }
            return true;
        } else {
            return false;
        }

    }




}