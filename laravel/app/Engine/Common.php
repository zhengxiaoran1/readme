<?php
/**
 * Created by PhpStorm.
 * User: zhuyujun
 * Date: 2018/10/22
 * Time: 20:43
 */

//通用方法类

namespace App\Engine;
use App\Api\Service\Qrcode\Manager\NewQrCode;
class Common
{
    public static function changeSnCode($snCode)
    {
        $snConfig = config('sn');
        foreach ($snConfig as $tmpKey => $tmpValue){
            $searchList[] = $tmpKey;
            $replaceList[] =  $tmpValue;
        }

        $re = str_replace($searchList,$replaceList,$snCode);
        return $re;
    }


    //通用处理方案，
    //（1）处理数量+单位，有,隔开的数据
    public static function fieldDataDeal($data,$type='')
    {
        $dataDeal = '';
        switch ($type) {
            case 1:
                $tmpArr = explode(',',$data);
                if(isset($tmpArr[0]) && isset($tmpArr[1])){
                    if($tmpArr[0]){
                        $dataDeal = $tmpArr[0].$tmpArr[1];
                    }else{
                        $dataDeal = '';
                    }
                }
                break;

            default:
                $dataDeal = $data;
        }

        return $dataDeal;
    }


    //处理重量（默认传入数值，单位为克）
    public static function weightDeal($gramNum)
    {
        $dataDeal = '';
        $unit = 'g';
        $dataDeal = $gramNum;
        if($dataDeal / 1000 >= 1){//千克
            $dataDeal = $dataDeal / 1000;
            $unit = "kg";

            if($dataDeal / 1000 >= 1){//吨
                $dataDeal = $dataDeal / 1000;
                $unit = "t";
            }
        }

        //数值四舍五入
        $dataDeal = sprintf('%.2f',$dataDeal);

        //增加单位
        $dataDeal .= $unit;

        return $dataDeal;
    }

    //$qrcodeSn: 二维码 sn
    //sn code_id
    //by lwl 通用二维码生成路径，2019 05 06；
    public static function QrcodePath($qrcodeSn,$sn)
    {
        $NewQrCode = new NewQrCode();
        $code = $NewQrCode->printW($qrcodeSn,$sn);
        $qrcodeStr = $code['list']['0']['title'];
        return 'http://api.k780.com:88/?app=qr.get&data='.$qrcodeStr;
//        $qrcodePath         = 'upload/global/qrcode.png';
//        $qrcodePathHttp     = asset($qrcodePath);
//        return \QrCode::format('png')->size(100)->generate($qrcodeStr,public_path($qrcodePath));




    }

}