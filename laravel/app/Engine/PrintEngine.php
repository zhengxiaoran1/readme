<?php
/**
 * created by zzy
 * date: 2017/10/24 9:28
 */
namespace App\Engine;


class PrintEngine
{

    public static function productQrcodeByType($productId,$arr,$type=0)
    {
        $userId             = \App\Engine\Func::getHeaderValueByName( 'userid',25 );
        $userInfo           = \App\Eloquent\Ygt\DepartmentUser::getCurrentInfo( $userId );
        $roleType           = $userInfo->role_type;
        //根据不同角色取不同表和模型
        $tableArr           = Func::getQrocdeTableArr($userInfo);
        $qrcodeModel        = $tableArr['qrcode_model'];
        $qrcodeFieldsModel  = $tableArr['fields_model'];
        $qrcodeLogModel     = $tableArr['log_model'];

        $result                 = [];
        $where                  = ['id'=>$productId];
        $product                = \App\Eloquent\Ygt\Product::getInfo($where);
        switch ($type)
        {
            case 1:
                //材料生成绑定材料的码,无流水信息
                //二维码入库
                $productNo          = $product->product_no;
                $qrcodeSn           = \App\Eloquent\Ygt\Qrcode::getSn($productNo);
                $qrcodeData         = [
                    'sn'=>$qrcodeSn,
                    'product_id'=>$productId,
                    'stock_id'=>0,
                ];
                if($roleType == 1){
                    $qrcodeId           = \App\Eloquent\Ygt\Qrcode::insertOneData($qrcodeData,'id');
                    \App\Eloquent\Ygt\SellerQrcode::insertOneData($qrcodeData,'id');
                }else if($roleType == 3){
                    $qrcodeId           = \App\Eloquent\Ygt\SellerQrcode::insertOneData($qrcodeData,'id');
                    \App\Eloquent\Ygt\Qrcode::insertOneData($qrcodeData,'id');
                }else{
                    $qrcodeId           = \App\Eloquent\Ygt\Qrcode::insertOneData($qrcodeData,'id');
                    \App\Eloquent\Ygt\SellerQrcode::insertOneData($qrcodeData,'id');
                }
                $qrcodeStr          = '1-'.$qrcodeId.'-'.$qrcodeSn;
                if($product) {
                    $text = $product->product_name;
                    $printData[] = \App\Engine\KmPrinter::text($text, 10, 10, 4);//材料名称
                    $printData[] = \App\Engine\KmPrinter::text($qrcodeSn, 200, 15, 3);//二维码编号
                    $text = $product->place_name;
                    $printData[] = \App\Engine\KmPrinter::text($text, 440, 15, 3);//堆位
                    $printData[] = \App\Engine\KmPrinter::line(10, 55, 590, 55);
                    $where = ['product_id' => $productId];
                    $fieldsList = \App\Eloquent\Ygt\ProductFields::getList($where, '', 6, '', ['id', 'asc']);
                    $i = 1;
                    foreach ($fieldsList as $key => $val) {
                        $n = $key % 2;
                        $y = 35 + $i * 40;
                        if ($n == 1) {
                            $x = 200;
                            $i += 1;
                        } else {
                            $x = 10;
                        }
                        $fieldName = $val['field_name'];
                        $fieldType = $val['field_type'];
                        $unit       = $val['unit'];
                        $fieldValue = $fieldType == 1 ? $val['varchar'] : $val['numerical'];
                        $text = $fieldName . ':' . $fieldValue.$unit;
                        $printData[] = \App\Engine\KmPrinter::text($text, $x, $y, 5);
                    }
                    $printData[] = \App\Engine\KmPrinter::qrcode($qrcodeStr, 440, 80);
                    $result = \App\Engine\KmPrinter::output($printData);
                }
                break;
            default:
                //材料打印入库
                $stockId            = $arr['stock_id'];
                $stockNumber        = $arr['stock_number'];
                $qrcodeFieldsData   = $arr['qrcode_fields_data'];
                //二维码入库
                $productNo          = $product->product_no;
                $qrcodeSn           = \App\Eloquent\Ygt\Qrcode::getSn($productNo);
                $qrcodeData         = [
                    'sn'=>$qrcodeSn,
                    'product_id'=>$productId,
                    'stock_id'=>$stockId,
                    'total_number'=>$stockNumber,
                    'now_number'=>$stockNumber,
                ];
                if($roleType == 1){
                    $qrcodeId           = \App\Eloquent\Ygt\Qrcode::insertOneData($qrcodeData,'id');
                    \App\Eloquent\Ygt\SellerQrcode::insertOneData($qrcodeData,'id');
                }else if($roleType == 3){
                    $qrcodeId           = \App\Eloquent\Ygt\SellerQrcode::insertOneData($qrcodeData,'id');
                    \App\Eloquent\Ygt\Qrcode::insertOneData($qrcodeData,'id');
                }else{
                    $qrcodeId           = \App\Eloquent\Ygt\Qrcode::insertOneData($qrcodeData,'id');
                    \App\Eloquent\Ygt\SellerQrcode::insertOneData($qrcodeData,'id');
                }
                if(!$qrcodeId)
                {
                    xThrow(ERR_UNKNOWN,'二维码生成失败');
                }
                //二维码属性
                if(!empty($qrcodeFieldsData))
                {
                    foreach($qrcodeFieldsData as $key=>$val)
                    {
                        $qrcodeFieldsData[$key]['qrcode_id']=$qrcodeId;
                    }
                    \DB::table('ygt_qrcode_fields')->insert($qrcodeFieldsData);
                }
                $qrcodeStr          = '1-'.$qrcodeId.'-'.$qrcodeSn;
                $where              = ['id'=>$stockId];
                $stock              = \App\Eloquent\Ygt\Stock::getInfo($where);
                if($product && $stock)
                {
                    $text           = $product->product_name;
                    $printData[]    = \App\Engine\KmPrinter::text($text,10,10,4);//材料名称
                    $printData[]    = \App\Engine\KmPrinter::text($qrcodeSn,200,15,3);//二维码编号
                    $text           = $stock->place_name;
                    $printData[]    = \App\Engine\KmPrinter::text($text,440,15,3);//堆位
                    $printData[]    = \App\Engine\KmPrinter::line(10,55,590,55);
                    $where          = ['product_id'=>$productId];
                    $fieldsList     = \App\Eloquent\Ygt\ProductFields::getList($where,'',6,'',['id','asc']);
                    $i              = 1;
                    foreach ($fieldsList as $key=>$val)
                    {
                        $n              = $key%2;
                        $y              = 35 + $i * 40;
                        if($n==1){
                            $x          = 200;
                            $i          += 1;
                        }else{
                            $x          = 10;
                        }
                        $fieldName      = $val['field_name'];
                        $fieldType      = $val['field_type'];
                        $fieldValue     = $fieldType==1 ? $val['varchar'] : $val['numerical'];
                        $text           = $fieldName.':'.$fieldValue;
                        $printData[]    = \App\Engine\KmPrinter::text($text,$x,$y,5);
                    }
                    $printData[]        = \App\Engine\KmPrinter::qrcode($qrcodeStr,440,80);
                    $result             = \App\Engine\KmPrinter::output($printData);
                }
            ////////
        }
        return $result;
    }
}