<?php
/**
 * created by zzy
 * date: 2017/10/24 9:28
 */

namespace App\Eloquent\Zk;
use App\Engine\Func;

class ImgUpload extends DbEloquent{

    protected $table    = 'zk_img_upload';

    //根据id取图片路径
    //$isHttp true带http false不带
    //$isSmall true取小图x80x80的 false取原图
    public static function getImgUrlById( $id, $isHttp=true, $isSmall=true )
    {
        $result        = ImgUpload::getOneValueById( $id, 'img_url' );
        if( $isHttp ){
            $result    = Func::getImgUrlHttp($result,$isSmall);
        }
        return $result;
    }
}

