<?php
/**
 * Created by PhpStorm.
 * Author: Sojo
 * Date: 2017/2/15
 * Time: 15:39
 */
namespace Framework\Extend\Helpers;

class Curl
{
    /**
     * @var array 设置http头部信息
     */
    public $httpHeader = ["Content-Type:application/json"];
    /**
     * @var String curl域名地址
     */
    public $curlDomain;
    /**
     * @var string 请求参数格式
     */
    public $format = self::FORMAT_RAW;

    const FORMAT_RAW = 'raw';
    const FORMAT_HTML = 'html';
    const FORMAT_JSON = 'json';
    const FORMAT_JSONP = 'jsonp';
    const FORMAT_XML = 'xml';
    /**
     * @var int curl请求超时时间,8秒
     */
    public $timeout = 8;

    public function __construct()
    {
        $curlDomain = config("app.curl_domain");
        $this->curlDomain = !empty($this->curlDomain) ? $this->curlDomain : $curlDomain;
        $this->curlDomain = rtrim($this->curlDomain, "/") . "/";
    }

    protected function saveHeaderInfo()
    {
//        $user_id = getCurrentUserId();
//        if (!empty($user_id)) {
//            $this->httpHeader[] = 'user-id:' . $user_id;
//        }
        $this->httpHeader[] = 'Authorization:' . \Request::header('Authorization');
        $this->httpHeader = array_unique($this->httpHeader);
    }

    /**
     * 单url请求，多个url时需循环串行（多url时不推荐使用此方法）
     * ('xxx/xxx', ['clientType' => 1, 'pageNum' => 1])
     * Author: itan.M
     * @param string $url 单个请求url地址
     * @param array $params 请求参数对象
     * @param string $type 请求类型 post（默认） 或 get
     * @return mixed|bool (数据返回内容)
     */
    public function curl_single($url, $params = [], $type = 'post')
    {
        if (!is_string($url) || empty($url) || !is_string($type)) return false;
        $this->_fix_url($url);
        $ch = curl_init();
        $this->_curl_config($ch, $url, $params, $type);
        $results = curl_exec($ch);
        $errors = curl_error($ch);
//        $info = curl_getinfo($ch);
        curl_close($ch);
        if (!empty($errors)) {
            //临时500异常处理
            abort(500, $errors);
        }
        return json_decode($results);
    }

    /**
     * multi curl 多个url请求合并成一个,并行处理
     * 5.3.9+ curl_multi_select方法经常返回-1，需执行usleep(100);
     * Author: itan.M
     * @param array $url_arr 请求地址url合集
     * [
     *      [
     *          'url'    => 'xx/xx',
     *          'params' => [], //请求参数
     *          'type'   => 'get' //type参数不设置则为POST请求，如需使用GET请求则需type参数设置为get
     *      ],
     * ];
     * @return array|bool (数据返回内容)
     */
    public function curl_multi($url_arr = [])
    {
        if (!is_array($url_arr) || count($url_arr) < 1) return false;
        $this->_fix_url($url_arr);
        $mh = curl_multi_init();
        $results = [];
        $errors = [];
        $info = [];
        $count = count($url_arr);
        $handles = [];
        for ($i = 0; $i < $count; $i++) {
            $handles[$i] = curl_init($url_arr[$i]['url']);
            $params = isset($url_arr[$i]['params']) ? $url_arr[$i]['params'] : null;
            $this->_curl_config($handles[$i], $url_arr[$i]['url'], $params, $url_arr[$i]['type']);
            curl_multi_add_handle($mh, $handles[$i]);
        }
        /* wait for performing request */
        $active = null;
        do {
            $mrc = curl_multi_exec($mh, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);
        while ($active && $mrc == CURLM_OK) {
            // wait for network
            if (curl_multi_select($mh) == -1) {
                usleep(100);
            }
            do {
                $mrc = curl_multi_exec($mh, $active);
            } while ($mrc == CURLM_CALL_MULTI_PERFORM);
        }

        // 成功错误码，暂时使用curl的错误码CURLE_OK
        $code = CURLE_OK;
        $msg = '';
        for ($i = 0; $i < $count; $i++) {
            $result = $results[] = json_decode(curl_multi_getcontent($handles[$i]));
            $errors[] = curl_error($handles[$i]);
//            $info[] = curl_getinfo($handles[$i]);
            curl_multi_remove_handle($mh, $handles[$i]);
        }
        curl_multi_close($mh);
        foreach ($errors as $error) {
            if (!empty($error)) {
                //临时500异常处理
                abort(500, $errors);
            }
        }
        return $results;
    }


    /**
     * 请求url地址前缀过滤
     * Author: itan.M
     * @param $url
     */
    protected function _fix_url(&$url)
    {
        if (is_array($url)) {
            foreach ($url as $k => $v) {
                if (substr($v['url'], 0, 5) !== 'http:' && substr($v['url'], 0, 6) !== 'https:') {
                    if (substr($v['url'], 0, 1) == '/') {
                        $url[$k]['url'] = substr($v['url'], 1, strlen($v['url']) - 1);
                    }
                    $url[$k]['url'] = $this->curlDomain . $url[$k]['url'];
                }
            }
        } elseif (is_string($url)) {
            if (substr($url, 0, 5) !== 'http:' && substr($url, 0, 6) !== 'https:') {
                if (substr($url, 0, 1) == '/') {
                    $url = substr($url, 1, strlen($url) - 1);
                }
                $url = $this->curlDomain . $url;
            }
        }

    }

    /**
     * 私有公共curl请求配置
     * Author: itan.M
     * @param $ch
     * @param $url
     * @param $params
     * @param $type
     */
    protected function _curl_config(&$ch, $url, $params, $type)
    {
        $this->saveHeaderInfo();
        switch ($type) {
            case 'post':
                curl_setopt($ch, CURLOPT_HTTPHEADER, $this->httpHeader);
                curl_setopt($ch, CURLOPT_POST, 1);
                if ($params && !empty($params)) {
                    if ($this->format == self::FORMAT_RAW)
                        $params = json_encode($params);
                }
                curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
                break;
            case 'get':
                if ($params && !empty($params)) {
                    $param = '?';
                    foreach ($params as $k => $v) {
                        $param .= $k . '=' . $v . '&';
                    }
                    $param = substr($param, 0, strlen($param) - 2);
                    $url = $url . $param;
                }
                break;
            default:
                //临时500异常处理
                abort(500);
                break;
        }
//        $ssl = substr($url, 0, 8) == "https://" ? TRUE : FALSE;
//        if ($ssl)
//        {
//            $cacert = __DIR__ . '/Cert/server.pem'; //CA根证书
//            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
//            curl_setopt($ch, CURLOPT_CAINFO, $cacert);
//            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
//        }
        curl_setopt($ch, CURLOPT_URL, $url);
        //debug调试模式下不设置请求超时时间
        if (config('app.debug')) {
            curl_setopt($ch, CURLOPT_TIMEOUT, 0xFFFFfff);
        } else {
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    }
}