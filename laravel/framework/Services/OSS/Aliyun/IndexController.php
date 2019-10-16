<?php

namespace Api\Middleware\Oss\Aliyun;

use Framework\Base\Api\Middleware\Controller;

class IndexController extends Controller
{
    public function upload()
    {
        $params = $this->verifyParams(['fileType'], false);
        $fileType = $params['fileType'] ?: 'image';
        return Oss::upload($fileType);
    }

    //kindeditor上传图片
    public function uploadFile()
    {
        $params = $this->verifyParams(['fileExt', 'filePath']);
        $oss = new Oss();
        return $oss->uploadFileSDK($params['fileExt'], $params['filePath']);
    }

    /**
     * 上传图片之前获取对应的签名
     * 输入
     *      fileName | 上传的文件名（根据文件名自动获取对应的文件类型后缀，目前支持txt,jpg,png,gif）
     */
    public function getUploadSign()
    {
        $params = $this->verifyParams(['fileName']);

        $list = explode('/', $params['fileName']);
        $file_name = $list[count($list) - 1];

        $list = explode('.', $file_name);
        $file_type = strtolower($list[count($list) - 1]);

        switch ($file_type) {
            case 'txt':
            case 'gif':
            case 'png':
            case 'jpg':
            case 'jpeg':
                break;
            default:
                xthrow();
        }

        return Oss::getUploadSign($file_name, $file_type);
    }


    /**
     * 上传图片之前获取对应的签名
     * 输入
     *      fileName | 上传的文件名（根据文件名自动获取对应的文件类型后缀，目前支持txt,jpg,png,gif）
     *      fileSize | 上传文件的内容字节总数大小
     */
    public function getUploadSign2()
    {
        $params = $this->verifyParams(['fileName', 'fileSize']);
        xassert($params['fileSize'] > 0);

        $list = explode('/', $params['fileName']);
        $file_name = $list[count($list) - 1];

        $list = explode('.', $file_name);
        $file_type = strtolower($list[count($list) - 1]);

        switch ($file_type) {
            case 'txt':
            case 'gif':
            case 'png':
            case 'jpg':
                break;
            default:
                xthrow(ERR_TODO);
        }

        return Oss::getSignForPutObject($file_name, $params['fileSize'], $file_type);
    }
}
