<?php
/**
 * Created by PhpStorm.
 * User: zhuyujun
 */


//交货单、收货单、核销单的状态
return [
    'warehouse_bill_status'=>[//交货单
        '1' => [
            'txt' => '未出库',
            'color'=>'FFB401',
        ],
        '2' => [
            'txt' => '已出库',
            'color'=>'FFB401',
        ],
        '3' => [
            'txt' => '发货员发货',
            'color'=>'FFB401',
        ],
        '4' => [
            'txt' => '销售确认发货',
            'color'=>'FFB401',
        ],
        '5' => [
            'txt' => '已收货',
            'color'=>'FFB401',
        ],
    ],
    'warehouse_send_status'=>[//收货单
        '1' => [
            'txt' =>'发货员发货',
            'color'=>'FFB401',
        ],
        '2' => [
            'txt' =>'销售确认发货',
            'color'=>'FFB401',
        ],
        '3' => [
            'txt' =>'确认收货',
            'color'=>'FFB401',
        ],
    ],
    'warehouse_bill_money_status'=>[
        '1' => [
            'txt' =>'待销账',
            'color'=>'FFB401',
        ],
        '2' => [
            'txt' =>'已打款给销售',
            'color'=>'FFB401',
        ],
        '3' => [
            'txt' =>'已打款给财务',
            'color'=>'FE7E57',
        ],
        '4' => [
            'txt' =>'未收款核销',
            'color'=>'04C9B3',
        ],
        '100' => [//后续加的流程
            'txt' =>'销售已转账',
            'color'=>'04C9B3',
        ],
        '5' => [
            'txt' =>'收款核销',
            'color'=>'04C9B3',
        ],
    ],
    ];
