<?php
/**
 * Created by PhpStorm.
 * User: Sojo
 * Date: 2017/8/26
 * Time: 13:33
 */
namespace Framework\Extend\Helpers;

use Framework\BaseClass\Object;

class Sign extends Object
{
    /** @var int 请求有效时间 */
    private $timestampValidity = 300;

    /**
     * api请求签名
     * @author Sojo
     * @param $params
     * @param $timestamp
     * @param $token
     * @param string $salt
     * @return string
     */
    public function apiSign($params, $timestamp, $token, $salt = '#^dwe%')
    {
        // 请求时间必须小于预设的 $timestampValidity 值
        if ((time() - $timestamp) > $this->timestampValidity) xThrow(ERR_REQUEST_INVALID);

        // 对参数的 key 以字母顺序排序
        $params['timestamp'] = $timestamp;
        $params['token'] = $token;
        ksort($params, SORT_STRING);

        $paramsFormat = [];
        foreach ($params as $key => $param) {
            // 用冒号连结参数的 key 和 value
            array_push($paramsFormat, ($key . ':' . $param));
        }

        // 用下划线连结各参数(key, value)
        $hashString = implode('_', $paramsFormat);
        // 在最后加上点“盐”
        $hashString .= $salt;

        // 生成 Sign
        $sign = md5($hashString);
        // Sign 所有字母转大写
        $sign = strtoupper($sign);

        return $sign;
    }
}