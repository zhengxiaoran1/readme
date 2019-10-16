<?php
namespace App\Http\Mobile\Product\Controllers;

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
    public function indexList(){
        return $this->view('list');
    }

    public function choseType(){
        return $this->view('chose-type');
    }
    //发起订单
    public function createOrder(){
        return $this->view('create-order');
    }

    //查看产品相关订单
    public function orderList(){
        $id     = request('id');
        return $this->view('order-list',compact('id'));
    }

    //查看订单详情
    public function orderInfo(){
        return $this->view('order-info');
    }

}
