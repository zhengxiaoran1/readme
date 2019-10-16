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
class WebsiteController extends Controller
{
    public function websiteManagement(){
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


        return $this->view('home', compact('topMenuList', 'username', 'roleName'));

    }

    public function websiteManagementData(){
        $configList = [
            [
                'keyword' => 'ftp',
                'url' => 'http://121.41.104.132/path.php',
                'remark' => '',
            ],
            [
                'keyword' => '路由',
                'url' => 'http://yiguantong.com/pathToFile.php',
                'remark' => '',
            ],
            [
                'keyword' => 'nginx',
                'url' => 'http://yiguantong.com/nginx/home',
                'remark' => '本地',
            ],
            [
                'keyword' => 'nginx',
                'url' => 'http://118.178.24.119/nginx/data',
                'remark' => '线上',
            ],
            [
                'keyword' => '后台',
                'url' => 'http://yiguantong.com/admin',
                'remark' => '本地',
            ],
            [
                'keyword' => '后台',
                'url' => 'http://118.178.24.119/admin',
                'remark' => '线上',
            ],
        ];


        $callBack = request('callback');
        $keyWords = request('keyWords');

        $keyWords = trim($keyWords);

        $dataList = [];
        if($keyWords){
            foreach ($configList as $configRow){
                if(strstr($configRow['keyword'],$keyWords)){
                    $dataList[] = $configRow;
                }
            }
        }else{
            $dataList  = $configList;
        }




        echo $callBack . '(' . \GuzzleHttp\json_encode($dataList) . ')';
    }


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