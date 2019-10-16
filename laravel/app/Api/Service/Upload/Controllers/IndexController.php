<?php
/**
 * created by zzy
 * date: 2017/10/24 9:28
 */
namespace App\Api\Service\Upload\Controllers;

use Framework\BaseClass\Api\Controller;
use App\Engine\Func;

class IndexController extends Controller
{

    //$fileName form表单里面的 name值 默认是 'file'
    //$filePath 文件上传的的路径目录 默认 'upload'
    //$type 不同值 做不同操作 甚至于返回不同的 返回值
    public function index(){

        if ( request()->isMethod('post') ) {

            $type           = request('type', 0 );
            $fileName       = request('file_name', 'file');
            $group          = request('group', 'api');
            $fileType       = Func::getImgUploadConfig( 'type', $type );
            $uploadResult   = Func::imgUpload( $fileType, $fileName ,$group);
            if( $uploadResult['status'] ){

                if($group == 'api'){
                    $filePathHttp = $uploadResult['file_path_http'];
                    $imgId        = $uploadResult['img_id'];
                    // 不同值 不同处理 type值 与配置文件 filesystems.disks.local 保持一致
                    switch ($type) {
                        case 1:
                            break;
                        case 2:
                            break;
                        default:
                    }
                    $result         = ['message'=>'上传成功', 'img_id'=>$imgId, 'file_path_http'=>$filePathHttp];
                }else{
                    $result         = ['message'=>'上传成功', 'img_id'=>join(',',$uploadResult['img_id']),"code"=>0];
                }

            } else {
                $message        = $uploadResult['message'];
                $result         = ['message'=>$message, 'img_id'=>'', 'file_path_http'=>''];
            }
            return $result;
        } else {
            echo 'error';exit;
        }
    }

}