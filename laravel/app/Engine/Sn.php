<?php
/**
 * 工单类，提供与工单相关的各种方法
 * Created by PhpStorm.
 * User: zhuyujun
 * Date: 2017/10/18
 * Time: 14:25
 */

namespace App\Engine;

use Framework\BaseClass\Api\Controller;
use Illuminate\Http\Request;

use Framework\Services\ImageUpload\imageProcess;

class Sn
{
    //材料编号
    public static function createProdcutProductNo($company_id,$specify_id=0){
        $where = [];
        $where[] = ['company_id', '=', $company_id];
        if($specify_id){
            $where[] = ['id', '<', $specify_id];
        }

        $maxId = \App\Eloquent\Ygt\Prodcut::withTrashed()->where($where)->count();
        $sn = 'CL'.str_pad(($company_id),2,"0",STR_PAD_LEFT ).str_pad(($maxId+1),6,"0",STR_PAD_LEFT );

        return $sn;
    }

    //集合材料编号
    public static function createAssemblageMaterialProductNo($company_id,$specify_id=0){

        $where = [];
        $where[] = ['company_id', '=', $company_id];
        if($specify_id){
            $where[] = ['id', '<', $specify_id];
        }

        $maxId = \App\Eloquent\Ygt\AssemblageMaterial::withTrashed()->where($where)->count();
        $sn = 'JHCL'.str_pad(($company_id),2,"0",STR_PAD_LEFT ).str_pad(($maxId+1),6,"0",STR_PAD_LEFT );
        return $sn;
    }

    //材料流水
    public static function createStockSn($company_id,$specify_id=0){

        $where = [];
        $where[] = ['company_id', '=', $company_id];
        if($specify_id){
            $where[] = ['id', '<', $specify_id];
        }

        $maxId = \App\Eloquent\Ygt\AssemblageMaterial::withTrashed()->where($where)->count();
        $sn = 'JHCL'.str_pad(($company_id),2,"0",STR_PAD_LEFT ).str_pad(($maxId+1),6,"0",STR_PAD_LEFT );
        return $sn;
    }


    //生成发货单单号
    public static function createWarehouseSendSnOld()
    {
        $dayStartTime = strtotime(date('Ymd'));
//        $intradayOrderCount = \App\Eloquent\Ygt\WarehouseSend::withTrashed()->where([['created_at', '>=', $dayStartTime]])->get()->count();
        $intradayOrderCount = \App\Eloquent\Ygt\WarehouseSend::where([['created_at', '>=', $dayStartTime]])->get()->count();

        $snIndex = '';
        $intradayOrderCount++;
        if ($intradayOrderCount < 10) {
            $snIndex = sprintf('0%d', $intradayOrderCount);
        } else {
            $snIndex = $intradayOrderCount;
        }
        $sn = 'FHD' . date('ymd') . $snIndex;

        return $sn;
    }

    //生成应收单单号
    public static function createWarehouseBillMoneySn()
    {
        $dayStartTime = strtotime(date('Ymd'));
        $intradayOrderCount = \App\Eloquent\Ygt\WarehouseBillMoney::withTrashed()->where([['created_at', '>=', $dayStartTime]])->get()->count();

        $snIndex = '';
        $intradayOrderCount++;
        if ($intradayOrderCount < 10) {
            $snIndex = sprintf('0%d', $intradayOrderCount);
        } else {
            $snIndex = $intradayOrderCount;
        }
        $sn = 'YSD' . date('ymd') . $snIndex;

        return $sn;
    }


    //生成应收单子单号
    public static function createWarehouseBillMoneySourseSn()
    {
        $dayStartTime = strtotime(date('Ymd'));
        $intradayOrderCount = \App\Eloquent\Ygt\WarehouseBillMoneySourse::withTrashed()->where([['created_at', '>=', $dayStartTime]])->get()->count();

        $snIndex = '';
        $intradayOrderCount++;
        if ($intradayOrderCount < 10) {
            $snIndex = sprintf('0%d', $intradayOrderCount);
        } else {
            $snIndex = $intradayOrderCount;
        }
        $sn = 'YSZCD' . date('ymd') . $snIndex;

        return $sn;
    }

    //生成采购账单子账单号
    public static function createMoneyDebtLogSourseSn()
    {
        $dayStartTime = strtotime(date('Ymd'));
        $intradayOrderCount = \App\Eloquent\Ygt\MoneyDebtLogSourse::withTrashed()->where([['created_at', '>=', $dayStartTime]])->get()->count();

        $snIndex = '';
        $intradayOrderCount++;
        if ($intradayOrderCount < 10) {
            $snIndex = sprintf('0%d', $intradayOrderCount);
        } else {
            $snIndex = $intradayOrderCount;
        }
        $sn = 'YSZCD' . date('ymd') . $snIndex;

        return $sn;
    }



    //生成半成品流水号
    public static function createProcessProductWaterSn($additional=0)
    {
        $dayStartTime = strtotime(date('Ymd'));
        $intradayOrderCount = \App\Eloquent\Ygt\ProcessProductWater::withTrashed()->where([['created_at', '>=', $dayStartTime]])->get()->count();
        $intradayOrderCount = $additional ? ( $intradayOrderCount + $additional ) : $intradayOrderCount;

        $snIndex = '';
        $intradayOrderCount++;
        if ($intradayOrderCount < 10) {
            $snIndex = sprintf('0%d', $intradayOrderCount);
        } else {
            $snIndex = $intradayOrderCount;
        }
        $sn = 'BCPLS' . date('ymd') . $snIndex;

        return $sn;
    }

    //生成成品流水号
    public static function createWarehouseLogSn(){
        $sn = '';
        //获取是当天的第几单
        $dayStartTime = strtotime(date('Ymd'));
        $intradayCount = \App\Eloquent\Ygt\WarehouseLog::withTrashed()->where([['created_at', '>=', $dayStartTime]])->get()->count();
        $titleIndex = '';
        $intradayCount++;
        if ($intradayCount < 10) {
            $titleIndex = sprintf('0%d', $intradayCount);
        } else {
            $titleIndex = $intradayCount;
        }
        $sn = 'CPLS' . date('ymd') . $titleIndex;
        return $sn;
    }



    //生成核销单流水号
    public static function createWriteOffSn()
    {
        $dayStartTime = strtotime(date('Ymd'));
        $intradayOrderCount = \App\Eloquent\Ygt\WriteOff::withTrashed()->where([['created_at', '>=', $dayStartTime]])->get()->count();

        $snIndex = '';
        $intradayOrderCount++;
        if ($intradayOrderCount < 10) {
            $snIndex = sprintf('0%d', $intradayOrderCount);
        } else {
            $snIndex = $intradayOrderCount;
        }
        $sn = 'HXD' . date('ymd') . $snIndex;
        return $sn;
    }

    //生成核销单流水号
    public static function createWarehouseAdjustmentSn()
    {
        $dayStartTime = strtotime(date('Ymd'));
        $intradayOrderCount = \App\Eloquent\Ygt\WarehouseAdjustment::withTrashed()->where([['created_at', '>=', $dayStartTime]])->get()->count();

        $snIndex = '';
        $intradayOrderCount++;
        if ($intradayOrderCount < 10) {
            $snIndex = sprintf('0%d', $intradayOrderCount);
        } else {
            $snIndex = $intradayOrderCount;
        }
        $sn = 'CPDHD' . date('ymd') . $snIndex;
        return $sn;
    }

    //生成调库出库单号
    public static function createWarehouseAdjustmentOutSn()
    {
        $dayStartTime = strtotime(date('Ymd'));
        $intradayOrderCount = \App\Eloquent\Ygt\WarehouseAdjustmentOut::withTrashed()->where([['created_at', '>=', $dayStartTime]])->get()->count();

        $snIndex = '';
        $intradayOrderCount++;
        if ($intradayOrderCount < 10) {
            $snIndex = sprintf('0%d', $intradayOrderCount);
        } else {
            $snIndex = $intradayOrderCount;
        }
        $sn = 'DHCKD' . date('ymd') . $snIndex;
        return $sn;
    }

    //生成调库发货单单号
    public static function createWarehouseAdjustmentSendSn()
    {
        $dayStartTime = strtotime(date('Ymd'));
        $intradayOrderCount = \App\Eloquent\Ygt\WarehouseAdjustmentSend::where([['created_at', '>=', $dayStartTime]])->get()->count();

        $snIndex = '';
        $intradayOrderCount++;
        if ($intradayOrderCount < 10) {
            $snIndex = sprintf('0%d', $intradayOrderCount);
        } else {
            $snIndex = $intradayOrderCount;
        }
        $sn = 'DKFHD' . date('ymd') . $snIndex;

        return $sn;
    }

    //产品编号
    public static function createProductNo($company_id,$order_type){
        $where = [];
        $where[] = ['company_id', '=', $company_id];
        if($order_type){
            $where[] = ['gongyi_id', '=', $order_type];
        }

        $maxId = \App\Eloquent\Ygt\ChanpinV3::withTrashed()->where($where)->count();
        $sn = 'CPBH'.str_pad(($company_id),2,"0",STR_PAD_LEFT ).str_pad(($order_type),4,"0",STR_PAD_LEFT ).str_pad(($maxId+1),4,"0",STR_PAD_LEFT );
        return $sn;
    }


    //成品产品编号
    public static function createWarehouseProductNo($company_id,$specify_id=0){
        $where = [];
        $where[] = ['company_id', '=', $company_id];
        if($specify_id){
            $where[] = ['id', '<', $specify_id];
        }

        $maxId = \App\Eloquent\Ygt\Warehouse::withTrashed()->where($where)->count();
        $sn = 'CP'.str_pad(($company_id),2,"0",STR_PAD_LEFT ).str_pad(($maxId+1),4,"0",STR_PAD_LEFT );

        return $sn;
    }

    //半成品产品编号
    public static function createProcessProductProductNo($company_id,$specify_id=0){
        $where = [];
        $where[] = ['company_id', '=', $company_id];
        if($specify_id){
            $where[] = ['id', '<', $specify_id];
        }

        $maxId = \App\Eloquent\Ygt\ProcessProduct::withTrashed()->where($where)->count();
        $sn = 'BCP'.str_pad(($company_id),2,"0",STR_PAD_LEFT ).str_pad(($maxId+1),6,"0",STR_PAD_LEFT );

        return $sn;

    }

    //废品材料编号
    public static function createWasteProductNo($company_id,$specify_id=0){

        $where = [];
        $where[] = ['company_id', '=', $company_id];
        if($specify_id){
            $where[] = ['id', '<', $specify_id];
        }
        $maxId = \App\Eloquent\Ygt\Waste::withTrashed()->where($where)->count();
        $sn = 'FP'.str_pad(($company_id),2,"0",STR_PAD_LEFT ).str_pad(($maxId+1),6,"0",STR_PAD_LEFT );

        return $sn;
    }


    //产品订单编号
    //$specify_id 具体的记录ID,有值的话表示是修改旧数据
    public static function createChanpinOrderNo($company_id,$specify_id=0){
        $day_start_time = strtotime(date('Ymd'));
        if($specify_id){
            $tmp_row = \App\Eloquent\Ygt\ChanpinOrder::find($specify_id)->toArray();

            $day_start_time = strtotime(date('Ymd',$tmp_row['created_at']));
        }


        $where = [];
        $where[] = ['company_id', '=', $company_id];
        $where[] = ['created_at', '>=', $day_start_time];
        if($specify_id){
            $where[] = ['id', '<', $specify_id];
        }

        $maxId = \App\Eloquent\Ygt\ChanpinOrder::withTrashed()->where($where)->count();
        $sn = 'CPDD'.str_pad(($company_id),2,"0",STR_PAD_LEFT ).date('Ymd',$day_start_time).str_pad(($maxId+1),2,"0",STR_PAD_LEFT );

        return $sn;
    }

    //订单编号
    //$specify_id 具体的记录ID,有值的话表示是修改旧数据
    public static function createCustomerOrderNo($company_id,$specify_id=0){
        $day_start_time = strtotime(date('Ymd'));
        if($specify_id){
            $tmp_row = \App\Eloquent\Ygt\CustomerOrder::find($specify_id)->toArray();

            $day_start_time = strtotime(date('Ymd',$tmp_row['created_at']));
        }


        $where = [];
        $where[] = ['company_id', '=', $company_id];
        $where[] = ['created_at', '>=', $day_start_time];
        if($specify_id){
            $where[] = ['id', '<', $specify_id];
        }

        $maxId = \App\Eloquent\Ygt\CustomerOrder::withTrashed()->where($where)->count();
        $sn = 'DD'.str_pad(($company_id),2,"0",STR_PAD_LEFT ).date('Ymd',$day_start_time).str_pad(($maxId+1),2,"0",STR_PAD_LEFT );

        return $sn;
    }

    //生产单编号生成 2019-0717 Linwei
    //只根据合同订单号 来加数字判断
    public static function createCustomerOrderNoByChanpinOrderTitle($chanpin_order_id){
        $where = [];
        $where[] = ['id', '=', $chanpin_order_id];
        $tmp_row = \App\Eloquent\Ygt\ChanpinOrder::where($where)->first();

        $chanpin_order_title = $tmp_row['sn'];

        $chanpin_order_title =  str_replace("CPDD","",$chanpin_order_title);


        //计算序列号
        $where = [];
        $where[] = ['chanpin_order_id', '=', $chanpin_order_id];
        $row_count = \App\Eloquent\Ygt\CustomerOrder::where($where)->count()+1;
        $chanpin_order_title =  "DD".$chanpin_order_title."-".str_pad($row_count,2,"0",STR_PAD_LEFT);

        return $chanpin_order_title;
    }

    //工单编号生成  2019-0717
    //只根据生产订单号复制过来，把前缀替换下
    public static function createOrderNoByCustomerOrderTitle($customer_order_id){
        $where = [];
        $where[] = ['id', '=', $customer_order_id];
        $tmp_row = \App\Eloquent\Ygt\CustomerOrder::where($where)->first();

        $order_title = $tmp_row['order_title'];

        $order_title =  str_replace("DD","GD",$order_title);


        return $order_title;
    }

    //工单编号
    //$specify_id 具体的记录ID,有值的话表示是修改旧数据
    public static function createOrderNo($company_id,$specify_id=0){
        /*工单编号显示订单编号加生产编号，如001-1,001-2 zhuyujun 20190712*/

        $day_start_time = strtotime(date('Ymd'));
        if($specify_id){
            $tmp_row = \App\Eloquent\Ygt\Order::find($specify_id)->toArray();
            if($tmp_row['customer_order_id']){
                //统计是第几个工单
                $where = [];
                $where[] = ['customer_order_id', '=', $tmp_row['customer_order_id']];
                $where[] = ['id', '<', $specify_id];
                $count = \App\Eloquent\Ygt\Order::where($where)->count();
                $count ++ ;//计数加1

                //获取订单号
                $tmp_customer_order_row = \App\Eloquent\Ygt\CustomerOrder::find($tmp_row['customer_order_id']);
                if($tmp_customer_order_row){
                    $customer_order_sn = $tmp_customer_order_row['order_title'];
                    $sn = $customer_order_sn;
                    //如果不需要改DD为GD只需要注释
                    $sn = str_replace("DD","GD",$sn);
                    $sn = $sn.'-'.$count;
                    return $sn;
                }
            }


            $day_start_time = strtotime(date('Ymd',$tmp_row['created_at']));
        }

        //没有订单号的情况下或没有指定工单号的情况
        $where = [];
        $where[] = ['company_id', '=', $company_id];
        $where[] = ['created_at', '>=', $day_start_time];
        if($specify_id){
            $where[] = ['id', '<', $specify_id];
        }

        $maxId = \App\Eloquent\Ygt\Order::withTrashed()->where($where)->count();
        $sn = 'GD'.str_pad(($company_id),2,"0",STR_PAD_LEFT ).date('Ymd',$day_start_time).str_pad(($maxId+1),2,"0",STR_PAD_LEFT );

        return $sn;
    }


    //交货单编号
    public static function createWarehouseBillNo($company_id,$specify_id=0){
        $day_start_time = strtotime(date('Ymd'));
        if($specify_id){
            $tmp_row = \App\Eloquent\Ygt\WarehouseBill::find($specify_id)->toArray();

            $day_start_time = strtotime(date('Ymd',$tmp_row['created_at']));
        }


        $where = [];
        $where[] = ['company_id', '=', $company_id];
        $where[] = ['created_at', '>=', $day_start_time];
        if($specify_id){
            $where[] = ['id', '<', $specify_id];
        }

        $maxId = \App\Eloquent\Ygt\WarehouseBill::withTrashed()->where($where)->count();
        $sn = 'JHD'.str_pad(($company_id),2,"0",STR_PAD_LEFT ).date('Ymd',$day_start_time).str_pad(($maxId+1),2,"0",STR_PAD_LEFT );

        return $sn;
    }


    //发货单单号
    public static function createWarehouseSendNo($company_id,$specify_id=0){
        $day_start_time = strtotime(date('Ymd'));
        if($specify_id){
            $tmp_row = \App\Eloquent\Ygt\WarehouseSend::find($specify_id)->toArray();

            $day_start_time = strtotime(date('Ymd',$tmp_row['created_at']));
        }


        $where = [];
        $where[] = ['company_id', '=', $company_id];
        $where[] = ['created_at', '>=', $day_start_time];
        if($specify_id){
            $where[] = ['id', '<', $specify_id];
        }

        $maxId = \App\Eloquent\Ygt\WarehouseSend::withTrashed()->where($where)->count();
        $sn = 'FHD'.str_pad(($company_id),2,"0",STR_PAD_LEFT ).date('Ymd',$day_start_time).str_pad(($maxId+1),2,"0",STR_PAD_LEFT );

        return $sn;
    }


    //采购单单号
    public static function createPurchaseNo($company_id,$specify_id=0){
        $day_start_time = strtotime(date('Ymd'));
        if($specify_id){
            $tmp_row = \App\Eloquent\Ygt\Purchase::find($specify_id)->toArray();

            $day_start_time = strtotime(date('Ymd',$tmp_row['created_at']));
        }


        $where = [];
        $where[] = ['company_id', '=', $company_id];
        $where[] = ['created_at', '>=', $day_start_time];
        if($specify_id){
            $where[] = ['id', '<', $specify_id];
        }

        $maxId = \App\Eloquent\Ygt\Purchase::withTrashed()->where($where)->count();
        $sn = 'CG'.str_pad(($company_id),2,"0",STR_PAD_LEFT ).date('Ymd',$day_start_time).str_pad(($maxId+1),2,"0",STR_PAD_LEFT );

        return $sn;
    }
}