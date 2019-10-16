<?php
namespace App\Http\Nginx\Nginx\Controllers;

use Framework\BaseClass\Http\Admin\Controller;
use Illuminate\Http\Request;

/**
 * Created by PhpStorm.
 * User: zhuyujun
 * Date: 2017/11/13
 * Time: 11:34
 */
class IndexController extends Controller
{
    public function getBlackList(){
        return [
            'nginx/',
            '_debugbar/',
            'favicon.ico',
            '/assets',
            '/pathToFile.php',
        ];
    }
    //各ip对应的人
    public function getIpUserList(){
        return [
            '127.0.0.1' => '本地',
            '192.168.1.109' => '朱雨骏192.168.1.109',
            '192.168.1.107' => '赵志勇192.168.1.107',
            '192.168.1.58' => 'WIFI入口192.168.1.58',
//            '192.168.1.71' => 'WIFI入口192.168.1.71',
//            '192.168.0.109' => '任成龙192.168.0.109',
//            '192.168.0.110' => '毛渊博192.168.0.110',
            '192.168.1.102' => '余洪192.168.1.102',
            '192.168.1.105' => '全希霖192.168.1.105',
            '192.168.1.74' => '毛渊博192.168.1.74',
            ' ' => '全部用户',
        ];
    }

    public function home(){
        $topMenuList = [];
        $topMenuList[]=[
            "id" => 1,
            "pid" => 0,
            "project" => "default",
            "name" => "Nginx",
            "english_name" => "top-system-setting",
            "target" => "navtab",
            "url" => "javascript:;",
            "sort" => 100,
            "active" => 1,
            "fresh" => 1,
            "display" => 1,
            "isChildren" => true,
        ];
        $username = '游客';
        $roleName = '游客';

        $ipUserList = $this->getIpUserList();
        $returnIpUserList = [];
        foreach ($ipUserList as $ip => $user){
            $returnIpUserList[] = [
                'visitIp'    => $ip,
                'visitUser'    => $user,
            ];
        }


        return $this->view('home', compact('topMenuList', 'username', 'roleName','returnIpUserList'));
    }
    public function getSideMenu(){
        $sideMenuList = [];
        $sideMenuList[]=[
            'name' => 'nginx',
            'children' => [
                [
                    'id' => 1,
                    'name'  => 'nginx访问记录',
                    'target'  => 'navtab',
                    'url'  => '/admin/nginx/nginxLog',
                    'fresh'  => true,
                ]
            ],
        ];
        echo \GuzzleHttp\json_encode($sideMenuList);
        die();
    }


    public function nginxLog(){
        return $this->view('nginxlog');
    }


    public function nginxLogData(Request $request){
        $input = $request->all();
        $callBack = request('callback');
        $maxNum = request('maxNum',100);
//        $filterVisitIp = isset($input['visitIp']) ? $input['visitIp'] :$_SERVER['REMOTE_ADDR'];
        $filterVisitIp = isset($input['visitIp']) ? $input['visitIp'] :'';
        $logNum = request('logNum',100);
        $statusCode = request('statusCode');
        $keyWords = request('keyWords');

        $logPath = 'D:\UPUPW_NP7.0\Nginx\logs\access.log';
        $str = $this->fileLastLines($logPath,$logNum,$filterVisitIp,$maxNum,$statusCode,$keyWords);
        $logArr = explode("\r\n",$str);
        //日志数据处理
        $tmpArr = [];
        $logDealList = [];
        foreach ($logArr as $logRow){
            if(empty(trim($logRow))){
                continue;
            }

            $pattern = "/\"(.+)\"/U";//非贪婪模式
            $mathches = [];
            preg_match_all($pattern,$logRow,$mathches);
            $logDetailArr = isset($mathches[1]) ? $mathches[1] : [];

            if(isset($logDetailArr[10])){
                $logDealRow = [];
                $logDealRow['visitIp'] = $logDetailArr[0];//访问ip
                $logDealRow['visitUser'] = $this->getUserByIp($logDealRow['visitIp']);//访问ip
                $logDealRow['visitHost'] = $logDetailArr[1];//域名
                $logDealRow['visitTime'] = date("H:i:s",strtotime($logDetailArr[3]))."<br>".date("Ymd",strtotime($logDetailArr[3]));//访问时间

                $visitTmp = $logDetailArr[4];
                $visitTmpArr = explode(" ",$visitTmp);
                $logDealRow['visitMethod'] = $visitTmpArr[0];//访问方式 get/post/……
                $visitTmp = $visitTmpArr[1];
                $visitTmpArr = explode("?",$visitTmp);
                $logDealRow['visitUri'] = $visitTmpArr[0];//访问uri
                $logDealRow['visitGetParam'] = isset($visitTmpArr[1]) ? $visitTmpArr[1] : '';//访问get值

                $visitTmp = $logDetailArr[5];
                $visitTmpArr = explode(" ",$visitTmp);
                $logDealRow['visitStatusCode'] = $visitTmpArr[0];//访问状态码 200/500/404/……

                $logDealRow['visitBrowser'] = $logDetailArr[7];//访问浏览器信息
                $logDealRow['visitPostParam'] = strlen($logDetailArr[8]) < 20000 ? $logDetailArr[8] : '数据过多';//访问post值
//                $logDealRow['visitPostParam'] = $logDetailArr[8];//访问post值

                $logDealRow['visitToken'] = $logDetailArr[10];//访问用户token
                $logDealRow['visitImei'] = isset($logDetailArr[11])? $logDetailArr[11] : '';//访问用户设备号

                $logDealRow['platform'] = isset($logDetailArr[12])? $logDetailArr[12] : '';//访问用户设备号
                $logDealRow['version'] = isset($logDetailArr[13])? $logDetailArr[13] : '';//访问用户设备号

                $logDealRow['visitUrl'] = $logDealRow['visitHost'].$logDealRow['visitUri'];//访问地址

                $logDealList[]  = $logDealRow;
            }else{//异常处理
                continue;
            }
        }
        echo $callBack . '(' . \GuzzleHttp\json_encode($logDealList) . ')';
        die();

    }

    public function analogTest(){
        $visitUrl = request('visitUrl','');
        $visitToken = request('visitToken','');
        $visitImei = request('visitImei','');
        $platform = request('platform', '');
        $version = request('version', '');
        $csrfToken = request('_token', '');

        $visitGetParam = htmlspecialchars_decode(request('visitGetParam',''));
        $visitPostParam = htmlspecialchars_decode(request('visitPostParam',''));


        $headers = array();
        $headers[] = 'TOKEN: '.$visitToken;
        $headers[] = 'IMEI: '.$visitImei;
        $headers[] = 'PLATFORM: '.$platform;
        $headers[] = 'VERSION: '.$version;
        $headers[] = 'X-CSRF-TOKEN: '.$csrfToken;
        $headers[] = 'REMOTE_ADDR: 192.168.1.999';//模拟数据专用ip
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $visitUrl."?".$visitGetParam);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch, CURLOPT_POSTFIELDS , htmlspecialchars_decode(htmlspecialchars_decode($visitPostParam)));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $output = curl_exec($ch);

//        echo $output;die();

//        return $this->view('order-detail', compact('showList'));
        return $this->view('analogTest', compact('visitUrl','visitToken','visitImei','visitGetParam','visitPostParam','platform','version','output'));


    }


    public function getIpUserSelect(Request $request){
        $input = $request->all();
        $callBack = $input['callback'];

        $ipUserList = $this->getIpUserList();
        $returnIpUserList = [];
        foreach ($ipUserList as $ip => $user){
            $returnIpUserList[] = [
                'visitIp'    => $ip,
                'visitUser'    => $user,
            ];
        }
        echo $callBack . '(' . \GuzzleHttp\json_encode($returnIpUserList) . ')';

    }


    /**
     * 取文件最后$n行
     * @param string $filename 文件路径
     * @param int $n 最后几行
     * @return mixed false表示有错误，成功则返回字符串
     */
    public function fileLastLines($filename,$n,$filterVisitIp='',$maxNum=1000,$statusCode,$keyWords){

        if(!$fp=fopen($filename,'r')){
            echo "打开文件失败，请检查文件路径是否正确，路径和文件名不要包含中文";
            return false;
        }
        $pos=-2;
        $eof="";
        $str="";
        while($n>0){
            while($eof!="\n"){
                if(!fseek($fp,$pos,SEEK_END)){
                    $eof=fgetc($fp);
                    $pos--;
                }else{
                    break;
                }
            }
            $curStr = fgets($fp);
            if($this->checkParams($curStr,$filterVisitIp,$statusCode,$keyWords)){//只筛选符合要求的结果
                $str.=$curStr;
                $n--;
            }

            if($maxNum == 0){
                break;
            }else{
                $maxNum --;
            }
            $eof="";
        }
        return $str;
    }

    //获取ip对应的用户，如果没有返回ip
    public function getUserByIp($visitIp){
        $ipUserList = $this->getIpUserList();
        if(isset($ipUserList[$visitIp])){
            return $ipUserList[$visitIp];
        }else{
            return $visitIp;
        }
    }

    //筛选结果是否符合要求
    public function checkParams($curStr,$filterVisitIp,$statusCode,$keyWords){
        //筛选访问ip
        $pattern = "/\"(.+)\"/U";//非贪婪模式
        $mathches = [];
        preg_match_all($pattern,$curStr,$mathches);
        $logDetailArr = isset($mathches[1]) ? $mathches[1] : [];
//            dd($logDetailArr);

        if(isset($logDetailArr[8])){//异常处理


            $logDealRow = [];
            $logDealRow['visitIp'] = $logDetailArr[0];//访问ip
            $logDealRow['visitUser'] = $this->getUserByIp($logDealRow['visitIp']);//访问ip
            $logDealRow['visitHost'] = $logDetailArr[1];//域名
            $logDealRow['visitTime'] = date("Ymd H:i:s",strtotime($logDetailArr[3]));//访问时间

            $visitTmp = $logDetailArr[4];
            $visitTmpArr = explode(" ",$visitTmp);
            $logDealRow['visitMethod'] = $visitTmpArr[0];//访问方式 get/post/……
            if(!isset($visitTmpArr[1])){
                return false;
            }
            $visitTmp = $visitTmpArr[1];
            $visitTmpArr = explode("?",$visitTmp);
            $logDealRow['visitUri'] = $visitTmpArr[0];//访问uri
            $logDealRow['visitGetParam'] = isset($visitTmpArr[1]) ? $visitTmpArr[1] : '';//访问get值

            $visitTmp = $logDetailArr[5];
            $visitTmpArr = explode(" ",$visitTmp);
            $logDealRow['visitStatusCode'] = $visitTmpArr[0];//访问状态码 200/500/404/……

            $logDealRow['visitBrowser'] = $logDetailArr[7];//访问浏览器信息
            $logDealRow['visitPostParam'] = strlen($logDetailArr[8]) < 20000 ? $logDetailArr[8] : '数据过多';//访问post值

            if($statusCode){
                if($logDealRow['visitStatusCode'] != $statusCode){
                    return false;
                }
            }

            if($keyWords){
                if(false === strpos($logDealRow['visitUri'],$keyWords)){
                    return false;
                }
            }

            //过滤css、js、png
            $filterStrArr = [
                '.css','.js','.png','.jpeg','.jpg'
            ];

            foreach ($filterStrArr as $filterStr){
                if(false !== strpos($logDealRow['visitUri'],$filterStr)){
                    return false;
                }
            }
            $blackList = $this->getBlackList();
            //过滤黑名单
            foreach ($blackList as $filterStr){
                if(false !== strpos($logDealRow['visitUri'],$filterStr)){
                    return false;
                }
            }



            if(!$filterVisitIp){
                return true;
            }


            if(trim($logDealRow['visitIp']) == trim($filterVisitIp)){
                return true;
            }else{
                return false;
            }

        }else{
            return false;
        }
    }

    public function virtualVisits(){
        return $this->view('virtualVisits');
    }

    public function virtualVisitsData(Request $request){

//        $visitUrl = request('visitUrl','');
//        $visitGetParam = request('visitGetParam','');
//        $visitPostParam = request('visitPostParam','');
//        $visitToken = request('visitToken','');


//        参数配置
        $visitUrl = 'http://118.178.24.119/api/service/order/getOrderList';
        $visitPostParam = 'is_distribute=0';
        $token = 'c5fb33bce3c583c0cc76a05eebd6f24b4383955';

        $uid = 12;
        $visitToken = md5('1').'123456'.$uid;
        $visitImei = request('visitImei','');


        $headers = array();
        $headers[] = 'TOKEN: '.$visitToken;
        $headers[] = 'IMEI: '.$visitImei;
        $headers[] = 'REMOTE_ADDR: 192.168.1.999';//模拟数据专用ip
        $ch = curl_init();
//        curl_setopt($ch, CURLOPT_URL, $visitUrl."?".$visitGetParam);
        curl_setopt($ch, CURLOPT_URL, $visitUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch, CURLOPT_POSTFIELDS , $visitPostParam);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $output = curl_exec($ch);
        echo $output;die();

        $re = json_decode($output,true);
        if($re){
            dd($re);
        }else{
            echo $output;
        }
    }

    public function path(){
        $prePath = request('pre_path');
        if($prePath){
            $prePath = request('pre_path');
            $tmpArr = explode('\\',$prePath);
            if(count($tmpArr) == 1){
                $tmpArr = explode('/',$prePath);
            }

            $pathStrList = [];
            foreach ($tmpArr as $pathStr){
                if($pathStr && !strstr($pathStr,'.php')){//过滤空格&文件级的内容
                    $pathStrList[] =$pathStr;
                }
            }

            //显示本地目录&线上目录
            echo "D:\\UPUPW_NP5.6\\htdocs\\yiguantong_product\\".implode("\\",$pathStrList);
            echo "<br>";
            echo "<br>";
            echo "<br>";
            echo "/data/wwwroot/118.178.24.119/".implode("/",$pathStrList);
            echo "<br>";
            echo "<br>";
            echo "<a href='./path'>复原</a>";
            die();
        }

        return $this->view('path');
    }






//    //去除两边指定的字符（支持只删除单边）
//    public function tirmBothSideChar($str,$char){
//        $re = substr($str,0,1);
////        dd($re);
//
//    }

}


if (! function_exists('request')) {
    /**
     * Get an instance of the current request or an input item from the request.
     *
     * @param  array|string  $key
     * @param  mixed   $default
     * @return \Illuminate\Http\Request|string|array
     */
    function request($key = null, $default = null)
    {
        if (is_null($key)) {
            return app('request');
        }

        if (is_array($key)) {
            return app('request')->only($key);
        }

        return data_get(app('request')->all(), $key, $default);
    }
}