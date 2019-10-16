<?php
if (!function_exists('object2Array')) {
    /**
     * 对象转数组
     * @author Sojo
     * @param $object object 需要转为数组的对象
     * @param bool $recursion 递归，默认false
     * @return array
     */
    function object2Array($object, $recursion = false)
    {
        $object = is_object($object) ? get_object_vars($object) : $object;
        if ($recursion) {
            if (is_array($object)) {
                foreach ($object as $key => $value) {
                    $object[$key] = object2Array($value, true);    //递归循环调用
                }
            }
        }
        return $object;
    }
}

if (!function_exists('makeFileUniqueName')) {
    /**
     * 生成文件的唯一名称，不包括扩展名
     * @author Sojo
     * @param null|string $userId
     * @return string
     */
    function makeFileUniqueName($userId = null)
    {
        // 存储路径格式：文件类型/年/季度/月/用户id/日/文件名（文件名格式：类型+time()+rand(10000, 99999)）
        $time = time();

        // 年
        $filename = '/' . date('Y', $time);
        // 季度
        $filename .= '/q' . ceil(date('n', $time) / 3);
        // 月
        $filename .= '/' . date('m', $time);
        // 用户ID
        if ($userId) {
            $padLength = 9;
            $padString = '0';
            $filename .= '/' . str_pad($userId, $padLength, $padString, STR_PAD_LEFT);
        }
        // 日
        $filename .= '/' . date('d', $time);
        // 文件名，格式：time() + _ + rand(10000, 99999) + ext
        $filename .= '/' . $time . '_' . rand(10000, 99999);

        return $filename;

        // JS版
//        var extArr = filename.split('.');
//        var ext = extArr[extArr.length - 1];
//        var str = "jpg,gif,png,jpeg,bmp";
//        if (str.indexOf(ext) >= 0) {
//            filename = 'image';
//        } else {
//            filename = 'file';
//        }
//
//        var time = new Date();
//        filename += '/' + time.getFullYear();
//        var q = parseInt(time.getMonth() / 3) + 1;
//        filename += '/q' + q;
//        filename += '/' + (time.getMonth() + 1);
//        filename += '/' + time.getDate();
//        var randomNum = parseInt(Math.random() * 100000); //随机五位数
//        var timestamp = new Date().getTime();
//        filename += '/' + timestamp + randomNum + '.' + ext;
//
//        return filename;
    }
}

if (!function_exists("isMobile")) {
    /**
     * 客户端 M 站登录验证
     * @author Sojo
     * @return bool
     */
    function isMobile()
    {
        //如果有HTTP_X_WAP_PROFILE则一定是移动设备
        if (isset ($_SERVER['HTTP_X_WAP_PROFILE'])) {
            return true;
        }
        //如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
        if (isset ($_SERVER['HTTP_VIA'])) {
            //找不到为flase,否则为true
            return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
        }

        //脑残法，判断手机发送的客户端标志,兼容性有待提高
        if (isset ($_SERVER['HTTP_USER_AGENT'])) {
            $clientkeywords = [
                'nokia',
                'sony',
                'ericsson',
                'mot',
                'samsung',
                'htc',
                'sgh',
                'lg',
                'sharp',
                'sie-',
                'philips',
                'panasonic',
                'alcatel',
                'lenovo',
                'iphone',
                'ipod',
                'blackberry',
                'meizu',
                'android',
                'netfront',
                'symbian',
                'ucweb',
                'windowsce',
                'palm',
                'operamini',
                'operamobi',
                'openwave',
                'nexusone',
                'cldc',
                'midp',
                'wap',
                'mobile'
            ];
            // 从HTTP_USER_AGENT中查找手机浏览器的关键字
            if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
                return true;
            }
        }

        // 协议法，因为有可能不准确，放到最后判断
        if (isset ($_SERVER['HTTP_ACCEPT'])) {
            // 如果只支持wml并且不支持html那一定是移动设备
            // 如果支持wml和html但是wml在html之前则是移动设备
            if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
                return true;
            }
        }

        return false;
    }
}