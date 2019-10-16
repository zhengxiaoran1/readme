<?php
/**
 * Created by PhpStorm.
 * User: zhuyujun
 * Date: 2017/10/18
 * Time: 17:22
 */

namespace App\Engine;


class Printer
{

    //打印二维码
    public static function qrcode($value){
        return '<1B2A>'.$value.'<1B2A><1B2AD><1B40><1B40><1D2101><1B6101>';
    }

    //正常字体
    public static function keyValue($array){
        $paperLen = 10;
        $value = '';
        foreach ($array as $k=>$v){
            $strlen = mb_strlen($k);
            $value .=self::normal($k.str_repeat('  ',$paperLen-$strlen).$v);
            $value .=self::br();
        }
        return $value;
    }

    //正常字体
    public static function normal($value, $align='left'){
        return '<1D2100>'.self::align($align).$value.self::align($align).'<1D2100>';
    }

    //加大一倍
    public static function strong($value, $align='left'){
        return '<1D2111>'.self::align($align).$value.self::align($align).'<1D2111>';
    }

    //加粗
    public static function bold($value, $align='left'){
        return '<1D2110>'.self::align($align).$value.self::align($align).'<1D2110>';
    }

    //加高
    public static function higher($value, $align='left'){
        return '<1D2101>'.self::align($align).$value.self::align($align).'<1D2101>';
    }

    //回车换行
    public static function br($number = 1){
        return str_repeat('<0D0A>',$number);
    }

    //初始化
    public static function start($number = 1){
        return str_repeat('<1B40>',$number);
    }

    //切刀指令
    public static function cat(){
        return '<0D0A><0D0A><1D5642000A0A><1B2AD>';
    }

    //对其方式
    private static function align($align='left'){
        switch ($align){
            case 'left':
                return '<1B6100>';
                break;
            case 'center':
                return '<1B6101>';
                break;
            case 'right':
                return '<1B6102>';
                break;
            default:
                return '<1B6100>';
                break;
        }
    }

    public static function p($value){
//        dd($value);

        $value = self::start(1).$value.self::br(4).self::cat();

//        dd($value);
        $url = 'http://115.28.15.113:61111';//POST指向的API链接

        $data = array(
            'dingdanID'=>'dingdanID='.time(), //工单号
            'dayinjisn'=>'dayinjisn=1701018111405172', //打印机ID号
            'dingdan'=>'dingdan='.$value, //工单内容
            'pages'=>'pages=1', //联数
            'replyURL'=>'replyURL='); //回复确认URL
        $post_data = implode('&',$data);

        $result = self::postData($url, $post_data);
        return $result;
    }

    public static function postData($url, $data)
    {
        $ch = curl_init();
        $timeout = 300;
        //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转 （很重要）
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_REFERER, "http://127.0.0.1/");   //构造来路
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        //ob_start();
        $handles = curl_exec($ch);  //获取返回结果
        //$result = ob_get_contents() ;
        //ob_end_clean();
        //close connection
        curl_close($ch);
        //return $result;
        return $handles;
    }

}