<?php
/**
 * Created by PhpStorm.
 * Author: kaodou
 * Date: 2018/3/15
 * Time: 17:13
 */

namespace App\Engine;

use App\Eloquent\Ygt\ChanpinOrder;

class WarehouseEngine
{
    public static function insertOneData($userId,$orderId,$number,$placeName = '',$piece=0,$zero=0)
    {
        $orderInfo              = \App\Eloquent\Ygt\Order::where('id',$orderId)->first();
        if(!$orderInfo)
        {
            return false;
        }
        $order                  = $orderInfo->toArray();
        $customerId             = intval($order['customer_name']);
        $companyId              = $order['company_id'];


        /*功能块：产品相关操作 zhuyujun 20190628*/
        $chanpin_order_id = $orderInfo['chanpin_order_id'];
        $chanpin_id = $orderInfo['chanpin_id'];



        $tableData              = [
            'customer_id'=>$customerId,
            'chanpin_id'=>$chanpin_id,
            'company_id'=>$companyId,
            'user_id'=>$userId,
            'order_id'=>$orderId,
            'all_number'=>$number,
            'now_number'=>$number,
            'piece'=>$piece,
            'zero'=>$zero,
            'product_name'=>$order['product_name'],
            'finished_date'=>$order['finished_date']
        ];
        ////////////////////////////////////////////////
        //产品图
        $valProductionCaseDiagram   = $order['production_case_diagram'];
        $imgPath                    = '';
        if($valProductionCaseDiagram)
        {
            $imgArr                 = explode(',',$valProductionCaseDiagram);
            $imgId                  = $imgArr[0];
            //$imgPath                = \App\Engine\Func::getImgUrlById($imgId);
            $imgPath                = \App\Eloquent\Ygt\ImgUpload::getOneValueById($imgId,'img_url');;
        }
        $tableData['img_path']    = $imgPath;
        //客户名
        $valCustomerId              = $order['customer_name'];
        $customerName               = \App\Engine\Customer::getNameById($valCustomerId);
        $tableData['customer_name']   = $customerName;
        //工艺名
        $valOrderType               = $order['order_type'];
        $orderTypeTitle             = \App\Eloquent\Ygt\OrderType::getOneValueById($valOrderType, 'title');
        $tableData['order_type_title']    = $orderTypeTitle;
        //单位名
        $fieldName23                = \App\Engine\Process::changeFieldName23($order['field_name_23']);
        $tableData['field_name_23']   = $fieldName23;
        //规格
        $valFinishedSpecification   = $order['finished_specification'];
        $finishedSpecification      = '';
        if ($valFinishedSpecification)
        {
            $finishedSpecification      = str_replace(',', '×', $valFinishedSpecification);
            $finishedSpecification      .= 'cm';
        }
        $tableData['finished_specification']  = $finishedSpecification;
        //单位
        $valProductNum              = $order['product_num'];
        $valProductNumArr           = explode(',',$valProductNum);
        $unit                       = '';
        if(isset($valProductNumArr[1]))
        {
            $unit                   = $valProductNumArr[1];
        }
        $tableData['unit']          = $unit;
        //工单号
        $tableData['order_title']   = $order['order_title'];
        //品名
        $productName                = \App\Engine\OrderEngine::getOrderFiledValueTrue($order['product_name'],20,$show_product_model_name = 1);
        if(!$productName){
            $productName = '';
        }
        $tableData['product_name']  = $productName;

        //克重 -- 改为成品重量 zhuyujun 20181129
//        if(!$order['grammage'])
//        {
//            $tableData['grammage']    = '';
//        }
        $tableData['grammage']    = '';
        if($order['finished_weight']){
            $tmpArr = explode(',',$order['finished_weight']);
            if(isset($tmpArr[0])){
                $tableData['grammage'] = $tmpArr[0];
            }

            if(isset($tmpArr[1])){
                $tableData['grammage'] .= $tmpArr[1];
            }
        }


        ////////////////////////////////////////////////
        $where                  = ['order_id'=>$orderId];
        $info                   = \App\Eloquent\Ygt\Warehouse::getInfo($where);
        if($info){
            $infoAllNumber      = $info->all_number;
            $infoNowNumber      = $info->now_number;
            $dataAllNumber      = $tableData['all_number'];
            $dataNowNumber      = $tableData['now_number'];
            $warehouseId        = $info->id;
            $allNumber          = $infoAllNumber + $dataAllNumber;
            $nowNumber          = $infoNowNumber + $dataNowNumber;

            //20190724 新增成品入库记录件数和零头
            $oldPiece = (isset($info->piece)) ?  $info->piece : 0;
            $oldZero = (isset($info->zero)) ?  $info->zero : 0;
            $newPiece = $oldPiece + $tableData['piece'];
            $newZero = $oldZero + $tableData['zero'];

            $where              = ['id'=>$warehouseId];
            $updateData         = [
                'all_number'=>$allNumber,
                'now_number'=>$nowNumber,
                'piece' => $newPiece,
                'zero'=>$newZero,
                'grammage'=>$tableData['grammage'],
            ];
            $warehouseId        = \App\Eloquent\Ygt\Warehouse::updateOneData($where,$updateData,'id');
            $logAllNumber       = $nowNumber;
        }else{
            $price              = \App\Engine\OrderEngine::getOrderPrice($orderId);
            if(!$price)
            {
                $price          = 0;
            }
            $tableData['price'] = $price;

            //获取打包件数
            $pack = \App\Engine\OrderEngine::getOrderPack($orderId);
            $tableData['pack'] = $pack;

            //添加成品编号
            $tableData['product_no'] = \App\Engine\Sn::createWarehouseProductNo($companyId);

            $warehouseId        = \App\Eloquent\Ygt\Warehouse::insertOneData($tableData,'id');
            $logAllNumber       = $tableData['now_number'];
            $newPiece = $tableData['piece'];
            $newZero  = $tableData['zero'];
        }
        if($warehouseId===false)
        {
            return false;
        }

        //20190402 多仓库调整
        //获取企业的默认仓库
        $default_storehouse_id = \App\Api\Service\Storehouse\Storehouse\Storehouse::getCompanyDefaultStorehouse($companyId)->getId();

        //20190402 多仓库调整
        //更新数量到对应的成品-仓库关联表
        //$managerObj = \App\Api\Service\Storehouse\NodeAssignment\StorehouseManager::init($userId,$companyId);
		$managerObj = \App\Api\Service\Storehouse\NodeAssignment\StorehouseManager::getTempStorehouseManager($companyId);
        $managerObj->getWarehouseResObj($default_storehouse_id,$warehouseId)->in($tableData['now_number']);

        //hjn 20190829 增加可用库存数量操作
        $Storehouse = new Storehouse();
        $Storehouse->declareNumber($warehouseId,$tableData['now_number'],3,'+');

        //成品流水日志记录
//        $sn                     = \App\Eloquent\Ygt\WarehouseLog::getSn($userId);
        //修改流水号生成规则
//        $sn = '';
//        //获取是当天的第几单
//        $dayStartTime = strtotime(date('Ymd'));
//        $intradayCount = \App\Eloquent\Ygt\WarehouseLog::withTrashed()->where([['created_at', '>=', $dayStartTime]])->get()->count();
//        $titleIndex = '';
//        $intradayCount++;
//        if ($intradayCount < 10) {
//            $titleIndex = sprintf('0%d', $intradayCount);
//        } else {
//            $titleIndex = $intradayCount;
//        }
//        $sn = 'CPLS' . date('ymd') . $titleIndex;
		$sn = \App\Engine\Sn::createWarehouseLogSn();


        $logData                = [
            'company_id'=>$companyId,
            'user_id'=>$userId,
            'warehouse_id'=>$warehouseId,
            'sn'=>$sn,
            'type'=>1,
            'order_id'=>$orderId,
            'customer_id'=>$customerId,
            'all_number'=>$logAllNumber,
            'now_number'=>$number,
            'piece'=>$piece,
            'zero'=>$zero,
            'all_piece'=>$newPiece,
            'all_zero'=>$newZero,
            'place_name'=>$placeName,
            'storehouse_id' => isset($default_storehouse_id) ? $default_storehouse_id : '',//仓库ID
        ];
        $warehouseLogId         = \App\Eloquent\Ygt\WarehouseLog::insertOneData($logData,'id');

        //已调整， zhuyujun 20181212
        /*消息调整：给客户和销售发送消息 zhuyujun 20181212*/
        /*
         * 主题：成品入库
内容：工单号、客户名、单位名称、品名、成品入库数量
         * */

        $tmpOrderTitle = \App\Engine\Common::changeSnCode($orderInfo['order_title']);
        $fieldName23 = \App\Engine\OrderEngine::getOrderFiledValueTrue($orderInfo['field_name_23'], 19);
        $productName = \App\Engine\OrderEngine::getOrderFiledValueTrue($orderInfo['product_name'], 20);
        $customerName = \App\Engine\OrderEngine::getOrderFiledValueTrue($orderInfo['customer_name'], 18);

        //获取成品单位
        $producUnit = '';
        $productNum = $orderInfo['product_num'];
        $tmpArr = explode(',',$productNum);
        if(isset($tmpArr[1]) && $tmpArr[1] && (!strstr($tmpArr[1],'null')) ){
            $producUnit = $tmpArr[1];
        }

        $messageContent = "工单号:{$tmpOrderTitle}rnrn";
        $messageContent.= "客户名:{$customerName}rnrn";
        $messageContent.= "单位名称:{$fieldName23}rnrn";
        $messageContent.= "品名:{$productName}rnrn";
        $messageContent.= "成品入库数量:{$number}{$producUnit}rnrn";

        //客户
        //获取客户的uid
        $tmpCustomerRow = \App\Eloquent\Ygt\Customer::where(['id'=>$orderInfo['customer_name']])->first();
        if($tmpCustomerRow){
            $data = [
                'company_id' => 2,
                'privilege_id' => '',
                'form_user_id' => $userId,
                'to_user_id' => $tmpCustomerRow['user_id'],
                'foreign_key' => $orderInfo['customer_order_id'],
                'type' => 33,//跳转到成品详情
                'type_id' => $warehouseLogId,
                'title' => $tmpOrderTitle,
                'content' => $messageContent,
                'theme' => '成品入库',
            ];
            \App\Eloquent\Ygt\UserMessage::sendCustomerOrderMessage($data);
        }

        //销售
        $tmpCustomerOrderPriceRow = \App\Eloquent\Ygt\CustomerOrderPrice::where(['customer_order_id'=>$orderInfo['customer_order_id']])->first();
        if($tmpCustomerOrderPriceRow){
            $data = [
                'company_id' => $orderInfo['company_id'],
                'privilege_id' => '',
                'form_user_id' => $userId,
                'to_user_id' => $tmpCustomerOrderPriceRow['sale_uid'],
                'foreign_key' => $orderInfo['customer_order_id'],
                'type' => 33,//跳转到成品详情
                'type_id' => $warehouseLogId,
                'title' => $tmpOrderTitle,
                'content' => $messageContent,
                'theme' => '成品入库',
            ];
            \App\Eloquent\Ygt\UserMessage::sendCustomerOrderMessage($data);
        }

        /*功能块：产品相关操作 zhuyujun 20190628*/

        //产品数量增加,产品订单完成数量增加，产品订单详情数量增加

        $chanpin_order_row = \App\Eloquent\Ygt\ChanpinOrder::find($chanpin_order_id);
        $chanpin_row = \App\Eloquent\Ygt\ChanpinV3::find($chanpin_order_row['source_chanpin_id']);

        $where = [];
        $where[] = ['chanpin_order_id','=',$chanpin_order_id];
        $where[] = ['chanpin_id','=',$chanpin_id];
        $chanpin_order_detail_row = \App\Eloquent\Ygt\ChanpinOrderDetail::where($where)->first();

        if($chanpin_row){
            $chanpin_row->total_number += $number;
            $chanpin_row->kucun += $number;
            $chanpin_row->save();
        }



        if($chanpin_order_row){
            $chanpin_order_row->is_product_number += $number;
            $chanpin_order_row->no_product_number -= $number;
            if($chanpin_order_row->no_product_number < 0 ){
                $chanpin_order_row->no_product_number = 0;
            }
            /*产品订单库存数量增加 zhuyujun 20190709*/
            $chanpin_order_row->stock_number += $number;

            $chanpin_order_row->save();
        }

        if($chanpin_order_detail_row){
            $chanpin_order_detail_row->is_product_number += $number;
            $chanpin_order_detail_row->no_product_number -= $number;
            if($chanpin_order_detail_row->no_product_number < 0 ){
                $chanpin_order_detail_row->no_product_number = 0;
            }
            /*产品订单库存数量增加 zhuyujun 20190709*/
            $chanpin_order_detail_row->stock_number += $number;
            $chanpin_order_detail_row->save();
        }


//        //成品入库时给销售人员发消息
//        $arr                = [
//            'user_id'=>$userId,
//            'company_id'=>$companyId,
//            'foreign_key'=>$order['customer_order_id'],
//            'type_id'=>$warehouseLogId,
//            'content'=>'有新的成品入库了',
//            'customer_id'=>$customerId,
//        ];
//        self::sendMessageByType($arr);
        return $warehouseId;
    }
    public static function sendMessageByType($arr,$type=0)
    {
        switch($type)
        {
            case 1:
                break;
            default:
                //成品入库时给销售人员发消息//33=>'成品入库'
//                $arr                = [
//                    'user_id'=>'当前登录人的id',
//                    'company_id'=>'当前登录人的厂id',
//                    'foreign_key'=>'分组外键id',
//                    'type_id'=>'type_id',
//                    'content'=>'消息的内容',
//                ];
                $messageType        = 33;
                $userId             = $arr['user_id'];
                $companyId          = $arr['company_id'];
                $foreignKey         = $arr['foreign_key'];
                $typeId             = $arr['type_id'];
                $messageContent     = $arr['content'];
                $customerId         = $arr['customer_id'];
                $title              = '成品入库';
                //取厂下的所有销售
//                $appnodeId          = 13;//销售角色explode
//                $userList           = \App\Eloquent\Ygt\Privilege::getPrivilegeUser( $companyId, $appnodeId );
//                $toUserIdArr        = [];
//                foreach($userList as $key=>$val)
//                {
//                    $toUserIdArr[]  = $val['user_id'];
//                }
//                $toUserIdStr        = implode(',',$toUserIdArr);
                //取工单中客户对应的添加人,即是哪个销售添加了该客户
                //只有存在对应的销售时才会发消息
                $where              = ['id'=>$customerId];
                $customer           = \App\Eloquent\Ygt\Customer::getInfo($where);
                if($customer)
                {
                    $addUserId      = $customer->add_user_id;
                    if($addUserId > 0)
                    {
                        $messageData        = [
                            'company_id' => $companyId,
                            'privilege_id' => 0,
                            'form_user_id' => $userId,
                            'to_user_id' => $addUserId,
                            'foreign_key' => $foreignKey,
                            'type' => $messageType,
                            'type_id' => $typeId,
                            'title' => $title,
                            'content' => $messageContent,
                            'type_status' => 1,
                        ];
                        \App\Eloquent\Ygt\UserMessage::sendCustomerOrderMessage($messageData);
                    }
                }
            ///////////////////////////////////////
        }
    }


    //交货单流程获取交货方式名称对应的ID
    public static function getDeliveryTypeNameByID($delivery_type_id){
        $delivery_type_config_list = config('warehouse-bill');
        $delivery_type_list = [];
        foreach ($delivery_type_config_list as $tmp_delivery_type_id => $tmp_delivery_type_name){
            if($tmp_delivery_type_id == $delivery_type_id){
                return $tmp_delivery_type_name;
            }
        }

        return false;//未匹配到对应的值
    }

    //交货单流程获取交货方式ID对应的名称
    public static function getDeliveryTypeIdByName($delivery_type_name){
        $delivery_type_config_list = config('warehouse-bill');
        $delivery_type_list = [];
        foreach ($delivery_type_config_list as $tmp_delivery_type_id => $tmp_delivery_type_name){
            if(trim($tmp_delivery_type_name) == trim($delivery_type_name)){
                return $tmp_delivery_type_id;
            }
        }

        return false;//未匹配到对应的值
    }

    public static function tmp(){

    }
}