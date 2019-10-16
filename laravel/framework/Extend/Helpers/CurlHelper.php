<?php
/**
 * Created by PhpStorm.
 * Author: Sojo
 * Date: 2017/2/15
 * Time: 15:39
 */

namespace Framework\Extend\Helpers;

trait CurlHelper
{
    public function curl()
    {
        return new Curl();
    }

    public function curl_single($url, $params = [], $type = 'post')
    {
        $curl = new Curl();
        return $curl->curl_single($url, $params, $type);
    }

    public function curl_multi($url_arr = [])
    {
        $curl = new Curl();
        return $curl->curl_multi($url_arr);
    }
}