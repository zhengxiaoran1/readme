<?php
/**
 * 快麦
 */
namespace App\Engine;


class KmPrinter
{

    public static $y=0;

    public static $result = [];


    public static function keyValue($array, $fontsize=5, $x=0){
        $tmp = [];
        foreach($array as $key=> $val){
            $tmp[] = [
                'type' => 'text',
                'title' => $key,
                'fontsize' => $fontsize,
                'x' => $x,
                'y' => self::$y,
            ];
            $tmp[] = [
                'type' => 'text',
                'title' => $val,
                'fontsize' => $fontsize,
                'x' => $x,
                'y' => self::$y,
            ];
        }
        self::$y += 40;
        self::$result[] = $tmp;
    }

//    public static function text($string, $fontsize=4, $x=0){
//        $tmp = [
//            'type' => 'text',
//            'title' => $string,
//            'fontsize' => $fontsize,
//            'x' => $x,
//            'y' => self::$y,
//        ];
//        self::$y += 40;
//        self::$result[] = $tmp;
//    }
//
//    public static function qrcode($string, $x=0, $u = '12', $m = 1, $command='BARCODE'){
//        $tmp = [
//            'type' => 'qrcode',
//            'title' => $string,
//            'x' => $x,
//            'y' => self::$y,
//            'm' => $m,
//            'u' => $u,
//            'command' => $command,
//        ];
//        self::$y += $u*27;
//        self::$result[] = $tmp;
//    }

//    public static function line($width=1, $x0=0, $x1=0){
//        self::$y += 20;
//        $tmp = [
//            'type' => 'line',
//            'width' => $width,
//            'x0' => $x0,
//            'y0' => self::$y,
//            'x1' => $x1,
//            'y1' => self::$y,
//        ];
//        self::$result[] = $tmp;
//    }

    /////////////////////////////int Line(String X0,String Y0,String X1,String
    public static function line($x=0,$y=0,$xEnd=0,$yEend=0,$width=2)
    {
        $result         = [
            'type'=>'line',
            'x'=>$x,
            'y'=>$y,
            'xEnd'=>$xEnd,
            'yEnd'=>$yEend,
            'width'=>$width,
        ];
        return $result;
    }
    public static function text($text,$x=0,$y=0,$fontsize=4)
    {
        $result         = [
            'type'=>'text',
            'title'=>$text,
            'fontsize'=>$fontsize,
            'x'=>$x,
            'y'=>$y,
        ];
        return $result;
    }
    public static function qrcode($string,$x=0,$y=0,$m=1,$u=4)
    {
        $command    = 'BARCODE';
        $result     = [
            'type'=>'qrcode',
            'title'=>$string,
            'x'=>$x,
            'y'=>$y,
            'm'=>$m,
            'u'=>$u,
            'command'=>$command,
        ];
        return $result;
    }
    public static function output($data,$height=400)
    {
        return [
            'list'=>$data,
            'height'=>$height
        ];
    }
    /////////////////////////////
    public static function p(){
        $pinfo = self::$result;
        $pheight = self::$y+50;

        self::$result = [];
        self::$y = 0;
        return [
            'list'=>$pinfo,
            'height'=>$pheight
        ];
    }

}