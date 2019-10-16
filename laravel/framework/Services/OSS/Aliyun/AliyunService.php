<?php
/**
 * Created by PhpStorm.
 * Author Sojo
 * Date: 2017/5/26
 * Time: 17:36
 */
namespace Framework\Services\OSS\Aliyun;

use OSS\OssClient;
use OSS\Core\OssException;

class AliyunService
{
    //应该从配置参数中获取设置之，目前暂时不符全部写死到代码中，发布的时候再做统一的配置。
//    private $acl = 'public-read-write';

    /** @var OssClient $ossClient */
    private $ossClient;

    /** @var  string $bucket 存储空间 */
    private $bucket;

    /** @var  array $imageType 图片类型 */
    private $imageType = ['gif', 'jpg', 'jpeg', 'png', 'bmp'];

    /** @var array $mediaType 媒体类型 */
    private $mediaType = ['mp3', 'wav', 'wma', 'wmv', 'mid', 'avi', 'mpg', 'asf', 'rm', 'rmvb'];

    /** @var array $flashType flash类型 */
    private $flashType = ['swf', 'flv'];

    /** @var array $fileType 文件类型 */
    private $fileType = ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'htm', 'html', 'txt', 'zip', 'rar', 'gz', 'bz2'];

    /**
     * 实例化OssClient并进行参数配置
     * @author Sojo
     * Oss constructor.
     */
    public function __construct()
    {
        // 访问ID
        $accessKeyId = config('aliyun.accessKeyId') ?: xThrow(ERR_SERVICE_CONFIGURATION_FILE_PARAMETERS);
        // 访问密钥
        $accessKeySecret = config('aliyun.accessKeySecret') ?: xThrow(ERR_SERVICE_CONFIGURATION_FILE_PARAMETERS);
        // 是否使用自定义域名
        $isCName = config('aliyun.oss.isCName');
        // 自定义域名
        $cName = config('aliyun.oss.cName');
        if ($isCName && !$cName) xThrow(ERR_SERVICE_CONFIGURATION_FILE_PARAMETERS);
        // 访问域名
        $endpoint = $isCName ? $cName : config('aliyun.oss.endpoint');
        // 存储空间
        $this->bucket = config('aliyun.oss.bucket') ?: xThrow(ERR_SERVICE_CONFIGURATION_FILE_PARAMETERS);

        try {
            $this->ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint, $isCName);
            // 设置请求超时时间，单位秒，默认是5184000秒, 这里建议 不要设置太小，如果上传文件很大，消耗的时间会比较长
            $this->ossClient->setTimeout(3600);
            // 设置连接超时时间，单位秒，默认是10秒
            $this->ossClient->setConnectTimeout(10);
        } catch (OssException $e) {
            xThrow(ERR_SERVICE_OSS_ALIYUN, $e->getMessage());
        }
    }

    /**
     * 上传本地文件（使用上述方法上传最大文件不能超过5G, 如果超过可以使用分片文件上传）
     * @author Sojo
     * @param string $filename
     * @param string $filePath
     * @param bool $isMakeFilename
     * @return string
     */
    public function uploadFile($filename, $filePath, $isMakeFilename = true)
    {
        $extension = explode('.', $filename);
        $extension = end($extension);

        if (in_array($extension, $this->imageType)) {
            $object = "image";
        } elseif (in_array($extension, $this->mediaType)) {
            $object = "media";
        } elseif (in_array($extension, $this->flashType)) {
            $object = "flash";
        } else {
            $object = "file";
        }
        if ($isMakeFilename) {
            $object .= makeFileUniqueName();
            $object .= '.' . $extension;
        } else {
            $object .= '/' . $filename;
        }

        try {
            $this->ossClient->uploadFile($this->bucket, $object, $filePath);
        } catch (OssException $e) {
            xThrow(ERR_SERVICE_OSS_ALIYUN, $e->getMessage());
        }

        return $object;
    }


//    private static function generateExpiration($time)
//    {
//        $dtStr = date("c", $time);
//        $myDatetime = new \DateTime($dtStr);
//        $expiration = $myDatetime->format(\DateTime::ISO8601);
//        $pos = strpos($expiration, '+');
//        $expiration = substr($expiration, 0, $pos);
//        return $expiration . "Z";
//    }
//
//    public static function upload($fileType)
//    {
//        $host = env('OSS_UPLOAD_HOST', 'http://zt-geek-web-v4.oss.dev.efanzhuan.com');
//
//        $dir = '';
//        switch ($fileType) {
//            case 'image':
//                $dir = 'image/';
//                break;
//            case 'file':
//                $dir = 'file/';
//                break;
//            default:
//                abort(400, '类型错误');
//        }
//
//        $min = (int)env('OSS_UPLOAD_MIN', 0);
//        $max = (int)env('OSS_UPLOAD_MAX', 2097152);
//        $expire = (int)env('OSS_UPLOAD_EXPIRE', 600);
//
//        $now = time();
//        $end = $now + $expire;
//        $expiration = self::generateExpiration($end);
//
//        //最大文件大小.用户可以自己设置
//        $conditions = [
//            ['content-length-range', $min, $max],
//            //表示用户上传的数据,必须是以$dir开始, 不然上传会失败,这一步不是必须项,只是为了安全起见,防止用户通过policy上传到别人的目录
//            ['starts-with', '$key', $dir]
//        ];
//
//        $arr = [
//            'expiration' => $expiration,
//            'conditions' => $conditions
//        ];
//        $policy = json_encode($arr);
//        $base64_policy = base64_encode($policy);
//        $string_to_sign = $base64_policy;
//        $signature = base64_encode(hash_hmac('sha1', $string_to_sign, self::$secret, true));
//
//        $response = [
//            'accessid' => self::$id,
//            'host' => $host,
//            'policy' => $base64_policy,
//            'signature' => $signature,
//            'expire' => $end,
//            //这个参数是设置用户上传指定的前缀
//            'dir' => $dir
//        ];
//
//        return json_encode($response);
//    }
//
//
//    public static function getUploadSign($file_name, $file_type)
//    {
//        $host = env('OSS_UPLOAD_HOST', 'http://zt-geek-web-v4.oss.dev.efanzhuan.com');
//        $mimeType = '';
//        $dir = '';
//        switch ($file_type) {
//            case 'txt':
//                $mimeType = 'text/plain';
//                $dir = 'file/';
//                break;
//            case 'jpg':
//            case 'jpeg':
//                $mimeType = 'image/jpeg';
//                $dir = 'image/';
//                break;
//            case 'gif':
//                $mimeType = 'image/gif';
//                $dir = 'image/';
//                break;
//            case 'png':
//                $mimeType = 'image/png';
//                $dir = 'image/';
//                break;
//            default:
//                xthrow(ERR_TODO);
//        }
//        //在服务器端进行文件存储的统一编码之
//        //在上传之前确定唯一的图片存储路径确保不冲突之同时简化前端应用开发
//        $prefix_file_name = '';
//        $file_path = $dir . self::make_file_unique_name($prefix_file_name) . '.' . $file_type;
//
//        $min = (int)env('OSS_UPLOAD_MIN', 0);
//        $max = (int)env('OSS_UPLOAD_MAX', 2097152);
//        $expire = (int)env('OSS_UPLOAD_EXPIRE', 600);
//
//        $now = time();
//        $end = $now + $expire;
//        $expiration = self::generateExpiration($end);
//
//        //最大文件大小.用户可以自己设置
//        $conditions = [
//            ['content-length-range', $min, $max],
//            //表示用户上传的数据,必须是以$dir开始, 不然上传会失败,这一步不是必须项,只是为了安全起见,防止用户通过policy上传到别人的目录
////            ['starts-with', '$key', $dir]
//            ['eq', '$key', $file_path]
//        ];
//
//        $arr = [
//            'expiration' => $expiration,
//            'conditions' => $conditions
//        ];
//        $policy = json_encode($arr);
//        $base64_policy = base64_encode($policy);
//        $string_to_sign = $base64_policy;
//        $signature = base64_encode(hash_hmac('sha1', $string_to_sign, self::$secret, true));
//
//        $response = [
//            'header' => [
//                'Host' => 'zt-geek-web-v4.oss.dev.efanzhuan.com',
//                'Content-Type' => $mimeType,
//                'Content-Encoding' => 'utf-8'
//            ],
//            'param' => [
//                'name' => $file_name,
//                'key' => $file_path,
//                'policy' => $base64_policy,
//                'OSSAccessKeyId' => self::$id,
//                'success_action_status' => '200',
//                'signature' => $signature,
//            ],
//            'host' => $host,
//            'file_path' => '/' . $file_path,
//        ];
//
//        return json_encode($response);
//    }
//
//
//    public static function getSignForPostObject($file_name, $file_size, $file_type)
//    {
//        $gmt_date = self::get_gmt_date();
//        $acl = self::$acl;
//        $bucket = self::$bucket;
//        $endpoint = self::$endpoint;
//
//        //https://msdn.microsoft.com/zh-cn/library/ms775147.aspx#Known_MimeTypes
//        $mimeType = '';
//        $file_path = '';
//        switch ($file_type) {
//            case 'txt':
//                $mimeType = 'text/plain';
//                $file_path = 'file/';
//                break;
//            case 'jpg':
//                $mimeType = 'image/jpeg';
//                $file_path = 'image/';
//                break;
//            case 'gif':
//                $mimeType = 'image/gif';
//                $file_path = 'image/';
//                break;
//            case 'png':
//                $mimeType = 'image/png';
//                $file_path = 'image/';
//                break;
//            default:
//                xthrow(ERR_TODO);
//        }
//        //在服务器端进行文件存储的统一编码之
//        //在上传之前确定唯一的图片存储路径确保不冲突之同时简化前端应用开发
//        $prefix_file_name = '';
//        $file_path .= self::make_file_unique_name($prefix_file_name) . '.' . $file_type;
//        if (empty($file_name)) {
//            $file_name = $prefix_file_name . '.' . $file_type;
//        }
//
//        $boundary = '----------' . time() . '.' . uniqid() . '----------';
//        $contentType = 'multipart/form-data; boundary=' . $boundary;
//
//        //根据签名规范组织需要加密的计算内容
//        $string_to_sign = "POST\n" .
//            "\n" . "$contentType\n" . "$gmt_date\n" .
//            "/$bucket/$file_path";
//        $signature = base64_encode(hash_hmac('sha1', $string_to_sign, self::$secret, true));
//
//        $response = [
//            'header' => [
//                'Host' => "{$bucket}.{$endpoint}.aliyuncs.com",
//                'Content-Length' => $file_size,
//                'Content-Type' => $contentType,
//                'User-Agent' => 'browser_data'
//            ],
//            'param' => [
//                'key' => '/' . $file_path,
//                'success_action_status' => 200,
//                'Content-Disposition' => "Content-Disposition",
//                'x-oss-meta-uuid' => 'uuid',
//                'x-oss-meta-tag' => 'metadata',
//                'OSSAccessKeyId' => self::$id,
////                'policy'=>'', //公共读写的bucket的访问控制可以不写，后续需要进行设置之。
//                'Signature' => $signature,
//                'submit' => 'Upload to OSS',
//            ]
//        ];
//
//        return json_encode($response);
//    }
//
//    /**
//     * 每次与阿里云进行交互之前不同类型的操作需要获取一次签名信息，签名过期需要重新获取一次，实现动态密码的效果。
//     * 此接口暂时用于图片上传逻辑的封装实现之，后续需要改进支持整个的阿里云OSS的所有操作的封装。
//     */
//
//    public static function getSignForPutObject($file_name, $file_size, $file_type)
//    {
//        $fileName = explode('/', $file_name);
//        $fileName = $fileName[count($fileName) - 1];
//
//        /**
//         * https://help.aliyun.com/document_detail/31978.html?spm=5176.doc31959.6.248.6U0ybA
//         * 上传数据的API的详细协议分析定义。
//         * https://help.aliyun.com/document_detail/31837.html?spm=5176.doc31959.2.1.fCTA4t
//         * OSS的相关HOST定义的节点对照表
//         * https://help.aliyun.com/document_detail/31951.html?spm=5176.doc31948.6.225.W1TvOh
//         * HEADER中签名计算方法的协议定义
//         * =================转换为需要签名的字符串编码信息(用\n进行换行)===============
//         * PUT\n
//         * \n
//         * multipart/form-data; boundary=_9CUtwf3cGnNReaxO8H7UqQVei_PefM\n
//         * Sun, 10 Jul 2016 04:09:14 GMT\n
//         * x-oss-acl:public-read-write\n
//         * /zt-geek-web-v4/
//         * ======================================================================
//         */
//        $gmt_date = self::get_gmt_date();
//        $acl = self::$acl;
//        $bucket = self::$bucket;
//        $endpoint = self::$endpoint;
//
//        //https://msdn.microsoft.com/zh-cn/library/ms775147.aspx#Known_MimeTypes
//        $mimeType = '';
//        $file_path = '';
//        switch ($file_type) {
//            case 'txt':
//                $mimeType = 'text/plain';
//                $file_path = 'file/';
//                break;
//            case 'jpg':
//                $mimeType = 'image/jpeg';
//                $file_path = 'image/';
//                break;
//            case 'gif':
//                $mimeType = 'image/gif';
//                $file_path = 'image/';
//                break;
//            case 'png':
//                $mimeType = 'image/png';
//                $file_path = 'image/';
//                break;
//            default:
//                xthrow(ERR_TODO);
//        }
//
//        //在服务器端进行文件存储的统一编码之
//        //在上传之前确定唯一的图片存储路径确保不冲突之同时简化前端应用开发
//        $prefix_file_name = '';
//        $file_path .= self::make_file_unique_name($prefix_file_name) . '.' . $file_type;
//        if (empty($file_name)) {
//            $file_name = $prefix_file_name . '.' . $file_type;
//        }
//
//        //根据签名规范组织需要加密的计算内容
//        $string_to_sign = "PUT\n" .
//            "\n" . "$mimeType\n" . "$gmt_date\n" .
//            "x-oss-object-acl:$acl\n" .
//            "/$bucket/$file_path";
//        $signature = base64_encode(hash_hmac('sha1', $string_to_sign, self::$secret, true));
//
//        $http_request = "PUT /{$file_path} HTTP/1.1\r\n" .
//            "Host: {$bucket}.{$endpoint}.aliyuncs.com\r\n" .
//            "Content-Size: {$file_size}\r\n" .
//            "User-Agent: Mozilla/5.0 (Linux; Android 4.4.2; CHM-UL00 Build/HonorCHM-UL00) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/30.0.0.0 Mobile Safari/537.36\r\n" .
//            "Connection: Keep-Alive\r\n" .
//            "Cache-Control: no-cache\r\n" .
//            "Authorization: OSS " . self::$id . ":" . $signature . "\r\n" .
//            "Charset: UTF-8\r\n" .
//            "Date: {$gmt_date}\r\n" .
//            "Content-Disposition: attachment;filename={$fileName}\r\n" .
//            "Content-Encoding: utf-8\r\n" .
//            "Accept-Encoding: gzip\r\n" .
//            "Cache-Control: no-cache\r\n" .
//            "Accept: */*\r\n" .
//            "Content-Type: {$mimeType}\r\n" .
//            "x-oss-object-acl: {$acl}\r\n" .
//            "Content-Length: {$file_size}\r\n" .
//            "\r\n";
//
////
////PUT /oss.jpg HTTP/1.1
////Host: oss-example.oss-cn-hangzhou.aliyuncs.com
////Cache-control: no-cache
////Expires: Fri, 28 Feb 2012 05:38:42 GMT
////Content-Encoding: utf-8
////Content-Disposition: attachment;filename=oss_download.jpg
////Date: Fri, 24 Feb 2012 06:03:28 GMT
////Content-Type: image/jpg
////Content-Length: 344606
////Authorization: OSS qn6qrrqxo2oawuk53otfjbyc:kZoYNv66bsmc10+dcGKw5x2PRrk=
////
////[344606 bytes of object data]
//
//        $response = [
//            'header' => [
//                'Host' => "{$bucket}.{$endpoint}.aliyuncs.com",
//                'Cache-Control' => 'no-cache',
//                'Content-Encoding' => 'utf-8',
//                'Content-Disposition' => "attachment;filename=$file_name",
//                'Date' => $gmt_date,
//                'x-oss-object-acl' => $acl,
//                'Authorization' => "OSS " . self::$id . ":" . $signature,
//                'Content-Type' => $mimeType,
//                'Content-Size' => $file_size,
//            ],
//            'param' => [
//
//            ],
//            'file_path' => $file_path,
//            'http_request' => $http_request
//        ];
//        return json_encode($response);
//
////        $id = self::$id;
////        $data = <<<EOF
////PUT /$file_path HTTP/1.1\r\n
////Host: $bucket.$endpoint.aliyuncs.com\r\n
////Cache-control: no-cache\r\n
////Content-Encoding: utf-8\r\n
////Content-Disposition: attachment;filename=$file_name\r\n
////Date: $gmt_date\r\n
////Content-Type: $mimeType\r\n
////Content-Size: $file_size\r\n
////Authorization: OSS $id:$signature\r\n
////x-oss-object-acl: $acl\r\n
////EOF;
////
////        $response = [
////            'host' => "{$bucket}.{$endpoint}.aliyuncs.com",
////            'data' => $data,
////            'file_path' => $file_path,
////            'date' => $gmt_date,
////            'file_name' => $file_name,
////            'mimeType' => $mimeType,
////            'file_size' => $file_size,
////            'authorization' => "OSS " . self::$id . ":" . $signature,
////            'acl' => $acl
////        ];
//
////        return json_encode($response);
//    }
//
//    //获取格林尼治时间字符串的方法用于API接口协议分析之
//    private static function get_gmt_date()
//    {
//        //服务器上的时间全部是北京时间，减掉8小时时差就得到格林尼治时间，然后再转回为年月日时分秒星期几的字符串格式。
//        //date()/gmdate()
//        //http://www.cnblogs.com/xiaochaohuashengmi/archive/2010/06/11/1756574.html
//        //http://www.w3school.com.cn/php/func_date_gmdate.asp
//        //Wed, 05 Sep 2012 23:00:00 GMT (API文档中规定的字符串格式)
//        return gmdate('D, d M Y H:i:s T', time());
//    }
//
//    //为一个文件生成唯一的文件名以便于后续的上传操作
//    private static function make_file_unique_name(&$prefix_file_name)
//    {
//        //TODO：存储路径格式：文件类型/年/季度/月/用户id/日/文件名（文件名格式：类型+time()+rand(10000, 99999)）
//        $beijinTime = time() + 8 * 3600;
//        $out = '';
//        //年
//        $out .= gmdate('Y', $beijinTime) . '/';
//        //季度
//        $out .= 'q' . (int)(gmdate('m', $beijinTime) / 3 + 1) . '/';
//        //月
//        $out .= (int)gmdate('m', $beijinTime) . '/';
//        //日
//        $out .= gmdate('d', $beijinTime) . '/';
//        //北京时间s . 唯一值 . 随机值16位 .
//        $prefix_file_name = dechex($beijinTime) . '.' . uniqid() . '.' . dechex((mt_rand(0x0, 0x7fff) | 0x8000));
//        $out .= $prefix_file_name;
//        //在父函数中设置文件名类型成为原始文件的完整路径，再使用缩略图算法实现各种小图片的统一自动生成之。
//        return $out;
//    }
}
