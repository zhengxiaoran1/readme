<?php
/**
 * created by zzy
 * date: 2017/10/24 9:28
 */
namespace App\Engine;

use App\Eloquent\Zk\ImgUpload;
use Illuminate\Support\Facades\Storage;

class Func{


    /**
     * created by zzy
     * 上传图片
     * @param int $type  不同值 标志不同上传目录
     * 与配置文件 filesystems.disks.local 保持一致
     * @param string $fileName 表单里面<input type="file" name="xxx"> 中的name值默认 'file'
     * @return array [ 'status'=>true/false, 'file_path'=>'路径', 'file_path_http'=>'带http的全路径' ]
     */
    public static function imgUpload( $type=0, $fileName='file', $group='api'){

        $fileDir                = self::getImgUploadConfig( 'file_dir', $type );

        if($group == "mobile"){
            $dataPic = array();
            if(!request('imgfile')) return ['status'=>true, 'img_id'=>$dataPic, 'message'=>'上传成功'];
            foreach (request('imgfile') as $k => $v) {
                preg_match('/^(data:\s*image\/(\w+);base64,)/', $v, $result);
                $path    = rtrim(public_path(),'public').'storage/upload/'.$fileDir.'/';

                cdir($path);

                $img_name = $fileDir.uniqid() . '.' . $result[2];

                $file = $path . $img_name;
                $data = base64_decode(str_replace(" ","+",str_replace($result[1], '', $v)));
                $success = file_put_contents($file, $data);
                if(!$success) return ['status'=>false, 'img_id'=>0, 'file_path'=>'', 'file_path_http'=>'','message'=>'第'.$k.'张上传失败'];


                self::imgCrop($file);

                $data               = [ 'type'=>$type, 'img_dir'=>$fileDir, 'img_url'=>$fileDir.'/'.$img_name ];
                $insertId           = ImgUpload::insertOneData( $data, 'id' );
                if( $insertId>0 ){
                    $dataPic[] = $insertId;
                }

            }

            return ['status'=>true, 'img_id'=>$dataPic, 'message'=>'上传成功'];

        }else{

            $requestFile            = request()->file($fileName);
            $requestFileSize        = $requestFile->getClientSize();

            if($requestFileSize<1 || $requestFileSize>5120000)
            {
                $result         = ['status'=>false, 'img_id'=>0, 'file_path'=>'', 'file_path_http'=>'','message'=>'文件大小不能超过5M'];
                return $result;
            }

            $uploadPath             = $requestFile->store($fileDir);
            $imgId                  = 0;
            $status                 = $filePath = $filePathHttp = false;
//        file_put_contents('haha.log',$uploadPath.PHP_EOL,FILE_APPEND);

            if( $uploadPath ){

                $imgPath            = rtrim(public_path(),'public').'storage/upload/'.$uploadPath;
//                $imgPath            = 'storage/'.$uploadPath;
                self::imgCrop($imgPath);

                $data               = [ 'type'=>$type, 'img_dir'=>$fileDir, 'img_url'=>$uploadPath ];
                $insertId           = ImgUpload::insertOneData( $data, 'id' );
                if( $insertId>0 ){
                    $status         = true;
                    $imgId          = $insertId;
                    $filePath       = $uploadPath;
                    $filePathHttp   = self::getImgUrlHttp( $filePath );
                }
                //上传至 阿里云OSS 暂不处理
            }
            $result         = ['status'=>$status, 'img_id'=>$imgId, 'file_path'=>$filePath, 'file_path_http'=>$filePathHttp,'message'=>''];
            return $result;
        }

    }

    public static function imgCrop($imgPath)
    {
        //ini_set('memory_limit', '800M');
        $imgObj             = \Image::make($imgPath);
        $height             = $imgObj->height();
        $width              = $imgObj->width();

        $imgW = 80;
        $imgH = 80;

        //需要调整到80x80
        if($height!= $imgH || $width!=$imgW){
            //两者都小于预定的值，空余位置直接填充成白色
            if($height<=$imgH && $width<=$imgW){
                $tempImgObj = \Image::canvas($imgW, $imgH, '#ffffff');
                $tempImgObj->insert($imgObj,'center');
            }
            //两者都大于预定值，先等比压缩，然后填充
            else if($height>=$imgH && $width>=$imgW){
                if($height>$width){
                    $imgObj->resize(floor($width*($imgH/$height)), $imgH);
                    $tempImgObj = \Image::canvas($imgW, $imgH, '#ffffff');
                    $tempImgObj->insert($imgObj,'center');
                }
                else{
                    $imgObj->resize($imgW,floor($height*($imgW/$width)));
                    $tempImgObj = \Image::canvas($imgW, $imgH, '#ffffff');
                    $tempImgObj->insert($imgObj,'center');
                }
            }
            //两者其中一个值大于预定值 处理和上面一样只是粘贴复制一下
            else{
                if($height>$width){
                    $imgObj->resize(floor($width*($imgH/$height)), $imgH);
                    $tempImgObj = \Image::canvas($imgW, $imgH, '#ffffff');
                    $tempImgObj->insert($imgObj,'center');
                }
                else{
                    $imgObj->resize($imgW,floor($height*($imgW/$width)));
                    $tempImgObj = \Image::canvas($imgW, $imgH, '#ffffff');
                    $tempImgObj->insert($imgObj,'center');
                }
            }

            $extension          = $imgObj->extension;
            $imgPathNew         = $imgPath.'x80x80.'.$extension;
            $tempImgObj->save($imgPathNew);
        }
        else{
            $extension          = $imgObj->extension;
            $imgPathNew         = $imgPath.'x80x80.'.$extension;
            $imgObj->save($imgPathNew);
        }
    }

    public static function imgCropOld($imgPath)
    {
        //ini_set('memory_limit', '800M');
        $imgObj             = \Image::make($imgPath);
        $height             = $imgObj->height();
        $width              = $imgObj->width();

        if($width>$height){
            $w              = $h = $height;
            $x              = floor (($width-$height)/2);
            $y              = 0;
        }else{
            $w              = $h = $width;
            $x              = 0;
            $y              = floor (($height-$width)/2);
        }
        $resizeW            = $resizeH = 80;
        if($w<$resizeW)
        {
            $resizeW        = $resizeH = $w;
        }
        $imgObj             = $imgObj->crop($w,$h,$x,$y);
        $imgObj             = $imgObj->resize($resizeW, $resizeH);;
        $extension          = $imgObj->extension;
        $imgPathNew         = $imgPath.'x80x80.'.$extension;
        $imgObj->save($imgPathNew);
    }
    /**
     * created by zzy
     * 取图片全路径
     * @param $filePath 数据库中的路径
     * @return string 带http的路径
     */
    public static function getImgUrlHttp( $filePath,$isSmall=true ){
        if( empty($filePath) ){ return $filePath; }
        $filePathArr        = explode('.',$filePath);
        if($isSmall && is_array($filePathArr))
        {
            $ext            = array_pop($filePathArr);
            $smallFilePath  = $filePath.'x80x80.'.$ext;
            if(is_file('storage/'.$smallFilePath))
            {
                $filePath   = $smallFilePath;
            }
        }
        $result             = Storage::url($filePath);
        return $result;
    }

    public static function getImgUrlById($id='',$isSmall=true){
        if($id){
            $path = ImgUpload::getOneValueById($id,'img_url');
            return self::getImgUrlHttp($path,$isSmall);
        }else{
            return '';
        }
    }
    public static function getImgUrlByIds($ids='',$isSmall=true){
        $result         = [];
        $idArr          = explode(',',$ids);
        foreach ($idArr as $key=>$val)
        {
            $imgUrl     = self::getImgUrlById($val,$isSmall);
            if(!empty($imgUrl))
            {
                $result[]   = $imgUrl;
            }
        }
        return $result;
    }

    /**
     * created by zzy
     * 取配置文件 filesystems.disks.local 中的不同的下标值
     * @param $name filesystems.disks.local中的下标
     * @param string $value 下标为数组时的键 或 其它值
     * @return array|string
     */
    public static function getImgUploadConfig( $name, $value='' ){

        $disks              = config('filesystems.disks');
        $localArr          = $disks['local'];
        $dirArr            = $localArr['dir_arr'];
        switch ( $name ){
            case 'type':
                $typeArr           = array_keys( $dirArr );
                if( $value != '' ){
                    $value          = intval( $value );
                    if( !in_array( $value, $typeArr ) ){
                        $value      = 0;
                    }
                    return $value;
                }
                return $typeArr;
                break;
            case 'file_dir':
                if( $value !=='' ){
                    $typeDir       = $dirArr[$value];
                    $filePathDir  = $typeDir ? $typeDir : 'upload';
                    return $filePathDir;
                }
                return $dirArr;
                break;
            case 'url':
                    $url        = $localArr['url'];
                    return $url;
                break;
        }
        return $localArr;
    }
    // 二维数组的不同转换
    //仅限该类型[ ['id'=>1,'title'=>'title1'],['id'=>2,'title'=>'title2'], ]
    // 1 转换成一维数组 [ 1=>'title1', 2=>'title2' ]
    // 2 转换成 1 的json格式
    // 3 转换成二维数组 [ [1=>'title1'], [2=>'title2'] ] 格式(主要是后台bjui里使用)
    // 4 转换成 3 的json格式(主要是后台bjui里使用)
    // 5 不转换原样返回
    public static function getIdTitleByType( $lists, $type=0 ){

        $result             = [];
        switch ($type){
            case 1:
                foreach ($lists as $key=>$val){
                    $result[$val['id']] = $val['title'];
                }
                break;
            case 2:
                $new_lists  = [];
                foreach ($lists as $key=>$val){
                    $new_lists[$val['id']] = $val['title'];
                }
                $result     = json_encode($new_lists);
                break;
            case 3:
                foreach ($lists as $key=>$val){
                    $arr        = [ $val['id']=>$val['title'] ];
                    $result[]   = $arr;
                }
                break;
            case 4:
                $new_lists      = [];
                foreach ($lists as $key=>$val){
                    $arr        = [ $val['id']=>$val['title'] ];
                    $new_lists[]= $arr;
                }
                $result     = json_encode($new_lists);
                break;
            default:
                $result     = $lists;
        }
        return $result;
    }
    //取header 里面的值
    public static function getHeaderValueByName( $name='userid', $value='' ){
        $result                 = null;
        switch ( $name ){
            case 'userid':
                $token          = request()->header( 'token',$value );
                $userId         = substr($token, 38);
                $result         = intval($userId);
                $result         = $result ? $result : $value;
                break;
            case 'token':
                $token          = request()->header( 'token',$value );
                $result         = substr($token, 0,32);
                $result         = $result ? $result : $value;
                break;
            case 'tokenall':
                $result         = request()->header( 'token',$value );
                break;
            default:
                $result         = request()->header( $name, $value );
        }
        return $result;
    }
    //报表里面关于app传时间的处理
    public static function getFormsDateTime( $dateYmd='', $isEnd=false )
    {
        if( $dateYmd == '' )
        {
            $now_time       = $_SERVER['REQUEST_TIME'];
            $dateYmd       = date( 'Y-m-d', $now_time );
            $dateTime      = strtotime( $dateYmd );
        } else {
            $dateYmd       = str_replace( '.','-', $dateYmd  );
            $dateTime      = strtotime( $dateYmd );
        }
        if( $isEnd )
        {
            $dateTime      = $dateTime + 86400;
        }
        return $dateTime;
    }
    // 返回值 no没有中文 all全部是中文 have含有中文
    public static function isAllChinese( $str ){
        $result                 = 'no';
        if(preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $str)>0){
            $result             = 'all';
        }elseif(preg_match('/[\x{4e00}-\x{9fa5}]/u', $str)>0){
            $result             = 'have';
        }
        return $result;
    }
    public static function haveSpace( $str ){
        $result                 = false;
        if(preg_match('/[\s]/', $str)>0){
            $result             = true;
        }
        return $result;
    }
    public static function getShortStr( $str ){
        $type                   = self::isAllChinese( $str );
        $space                  = self::haveSpace( $str );
        $size                   = mb_strlen( $str );
        switch ($type){
            case 'all':
                $str                = str_replace( ' ', '', $str );
                if( $size > 2 ){
                    $result         = mb_substr( $str, -2, 2 );
                } else {
                    $result         = $str;
                }
                break;
            case 'no':
            case 'have':
                if( $space ){
                    $strArr            = explode(' ', $str);
                    foreach( $strArr as $key=>$val ){
                        if( empty( $val ) ){
                            unset( $strArr[$key] );
                        }
                    }
                    $last               = array_pop( $strArr );
                    $first              = array_pop( $strArr );
                    $result             = '';
                    if( $first ){
                        $strTemp        = mb_substr( $first, 0, 1 );
                        $result         .= $strTemp;
                    }
                    if( $last ){
                        $strTemp        = mb_substr( $last, 0, 1 );
                        $result         .= $strTemp;
                    }
                } else {
                    $result         = mb_substr( $str, 0, 2 );
                }
                break;
            default:
                $result         = $str;
        }
        return $result;
    }
    //二维码
    public static function qrcode($string){
        return \QrCode::format('png')->size(100)->generate($string);
    }
    //结算方式
    public static function getPayTypeList($type=1)
    {
        $firstList          = [
            ['id'=>1, 'title'=>'款到发货'],
            ['id'=>2,'title'=>'账期'],
            ['id'=>3, 'title'=>'分期付款'],
            ['id'=>4,'title'=>'流水付',],
        ];
        $secondList          = [
            ['id'=>1,'title'=>'现金'],
            ['id'=>2,'title'=>'承兑汇票'],
        ];
        $result             = [];
        switch($type)
        {
            case 1:
                foreach($firstList as $key=>$val)
                {
                    $temp           = $val;
                    $temp['data']   = $secondList;
                    $result[]       = $temp;
                }
                break;
            case 2:
                $result             = [
                    'first_list'=>$firstList,
                    'second_list'=>$secondList,
                ];
                break;
            default:
                $result             = [];
        }
        return $result;
    }

    //根据id 获取具体结算方式的title;
    public static function get_pay($payfirst,$paysecond){
        // return 555;die;
        $res = self::getPayTypeList();
        $key = $payfirst-1;
        $first_name  = $res[$key]['title'];
        $second_name = $res[$key]['data'][$paysecond-1]['title'];
        $payStr = $first_name . ' ' . $second_name;
        return ['payFirst'=>$first_name,'paySecond'=>$second_name,'payStr'=>$payStr];
    }
    //一个集合根据指定的字段(默认首字母)
    //返回指定的格式
    //[
    //  ['letter'=>'A','list'=>[]],
    //  ['letter'=>'B','list'=>[]],
    //]
    public static function getLetterList($collection,$letterStr='first_letter')
    {
        $letterArr          = $collection->pluck($letterStr)->all();
        $result             = [];
        if($letterArr)
        {
            $letterArr          = array_unique($letterArr);
            sort($letterArr);
            $listArr            = $collection->groupBy($letterStr)->toArray();
            foreach($letterArr as $key=>$val)
            {
                $result[$key]['letter'] = $val;
                $result[$key]['list']   = $listArr[$val];

            }
        }
        return $result;
    }
    //不同角色登录对应的二维码表是不一样的,
    //目前只有工厂角色,和供应商角色---2018-8-21
    //默认是返回工厂相关的二维码表
    public static  function getQrocdeTableArr($userInfo)
    {
        $roleType           = $userInfo->role_type;//1工厂角色,2客商,3供应商,4收品单位
        switch ($roleType)
        {
            case 1:
                $qrcodeTableName        = '\App\Eloquent\Ygt\Qrcode';
                $qrcodeFieldsTableName  = '\App\Eloquent\Ygt\QrcodeFields';
                $qrcodeLogTableName     = '\App\Eloquent\Ygt\QrcodeLog';
                $qrcodeTable            = 'ygt_qrcode';
                $fieldsTable            = 'ygt_qrcode_fields';
                $logTable               = 'ygt_qrcode_log';
                break;
            case 3:
                $qrcodeTableName        = '\App\Eloquent\Ygt\SellerQrcode';
                $qrcodeFieldsTableName  = '\App\Eloquent\Ygt\SellerQrcodeFields';
                $qrcodeLogTableName     = '\App\Eloquent\Ygt\SellerQrcodeLog';
                $qrcodeTable            = 'ygt_seller_qrcode';
                $fieldsTable            = 'ygt_seller_qrcode_fields';
                $logTable               = 'ygt_seller_qrcode_log';
                break;
            default:
                $qrcodeTableName        = '\App\Eloquent\Ygt\Qrcode';
                $qrcodeFieldsTableName  = '\App\Eloquent\Ygt\QrcodeFields';
                $qrcodeLogTableName     = '\App\Eloquent\Ygt\QrcodeLog';
                $qrcodeTable            = 'ygt_qrcode';
                $fieldsTable            = 'ygt_qrcode_fields';
                $logTable               = 'ygt_qrcode_log';
        }
        $result     = [
            'role_type'=>$roleType,
            'qrcode_table'=>$qrcodeTable,
            'fields_table'=>$fieldsTable,
            'log_table'=>$logTable,
            'qrcode_model'=>$qrcodeTableName,
            'fields_model'=>$qrcodeFieldsTableName,
            'log_model'=>$qrcodeLogTableName,
        ];
        return $result;
    }

    //兼容版本;
    public static  function getQrocdeTableArr_new($userInfo)
    {
        $roleType           = $userInfo->role_type;//1工厂角色,2客商,3供应商,4收品单位
        switch ($roleType)
        {
            case 1:
                $qrcodeTableName        = '\App\Eloquent\Ygt\QrcodeZq';
                $qrcodeFieldsTableName  = '\App\Eloquent\Ygt\QrcodeFields';
                $qrcodeLogTableName     = '\App\Eloquent\Ygt\QrcodeLog';
                $qrcodeTable            = 'ygt_qrcode';
                $fieldsTable            = 'ygt_qrcode_fields';
                $logTable               = 'ygt_qrcode_log';
                break;
            case 3:
                $qrcodeTableName        = '\App\Eloquent\Ygt\SellerQrcode';
                $qrcodeFieldsTableName  = '\App\Eloquent\Ygt\SellerQrcodeFields';
                $qrcodeLogTableName     = '\App\Eloquent\Ygt\SellerQrcodeLog';
                $qrcodeTable            = 'ygt_seller_qrcode';
                $fieldsTable            = 'ygt_seller_qrcode_fields';
                $logTable               = 'ygt_seller_qrcode_log';
                break;
            default:
                $qrcodeTableName        = '\App\Eloquent\Ygt\Qrcode';
                $qrcodeFieldsTableName  = '\App\Eloquent\Ygt\QrcodeFields';
                $qrcodeLogTableName     = '\App\Eloquent\Ygt\QrcodeLog';
                $qrcodeTable            = 'ygt_qrcode';
                $fieldsTable            = 'ygt_qrcode_fields';
                $logTable               = 'ygt_qrcode_log';
        }
        $result     = [
            'role_type'=>$roleType,
            'qrcode_table'=>$qrcodeTable,
            'fields_table'=>$fieldsTable,
            'log_table'=>$logTable,
            'qrcode_model'=>$qrcodeTableName,
            'fields_model'=>$qrcodeFieldsTableName,
            'log_model'=>$qrcodeLogTableName,
        ];
        return $result;
    }

    public static function  returnFormula($process_id,$material_id){
        $return = 0;
        if( !$process_id ||  !$material_id){
            return $return;
        }

        $material_field = \App\Eloquent\Zk\ProductFields::where(['assemblage_material_id'=>$material_id])->select('field_name','id','varchar')->get();//集合材料属性；
        $cate = \App\Eloquent\Zk\AssemblageMaterial::where(['id'=>$material_id])->select('category_id')->first();//分类；

        if(!$cate){
            return $return;
        }

        $cate_field = \App\Eloquent\Zk\CategoryFields::where(['category_id'=>$cate->category_id])->select(['field_name','id'])->get(); //分类属性；
//        dd($material_field);
        $cate_infos = \App\Eloquent\Zk\Category::where(['id'=>$cate->category_id])->first();

        if(!$material_field || !$cate || !$cate_field){
            return $return;
        }

        //获取聚体的公式；
        $categorys = new \App\Eloquent\Zk\Category();
        $categorys = $categorys->get()->toArray();

        $res = array_reverse(\App\Engine\Func::GetPid($categorys,$cate_infos->id));
        foreach($res as $k=>$v){
            $formula = \DB::table('ygt_set_material_formula')->where(['process_id'=>$process_id,'material_id'=>$v['id']])->select(['formula'])->first();
            if( $formula ){
                break;
            }
        }

        if(!$formula || !$formula->formula || $formula=='null' ){
            return $return;
        }

        $formula = explode('_',$formula->formula);
        $material_field = $material_field->toArray();
        $cate_field = $cate_field->toArray();
        $data = [];
        foreach($cate_field as $k=> $v){
            foreach($material_field as $l=>$w){
                if($v['field_name'] == $w['field_name']){
                    $data[$k]['id'] = $w['id'];
                    $data[$k]['field_name'] = $w['field_name'];
                    $data[$k]['varchar'] = $w['varchar']==false?0:$w['varchar'];
                }
            }
        }
        foreach ($data as $k=>$v){
            foreach ($formula as $l=>$w){
                if($v['field_name'] == $w){
                    $formula[$l] = $v['varchar'];
                }
            }
        }
        $res_formula = str_replace(' ', '', implode(' ',$formula));
        if (preg_match("/([\x81-\xfe][\x40-\xfe])/", $res_formula, $match)){ //判断公式里是否含有未被替换掉的属性；//"5+6/1+规格"
            return $return;
        }

        return ( eval("return $res_formula;"));
    }

    //by lwl 2019 04 25 查找分类父类id;
    public static function GetPid($cates,$id)
    {
        $arr = [];
        foreach($cates as $v) {
            if($v['id'] == $id) {
                $arr[] = $v;
                $arr = array_merge(self::GetPid($cates, $v['pid']), $arr);
            }
        }
        return $arr;
    }
}