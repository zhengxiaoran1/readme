<?php
namespace App\Http\Mobile\Order\Controllers;

use Framework\BaseClass\Http\Mobile\Controller;
use Illuminate\Http\Request;

/**
 * Created by PhpStorm.
 * User: zhuyujun
 * Date: 2017/12/29
 * Time: 11:34
 */
class IndexController extends Controller
{
    public function orderReport(){
        return $this->view('order-report');
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