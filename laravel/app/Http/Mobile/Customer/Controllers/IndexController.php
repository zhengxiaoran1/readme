<?php
namespace App\Http\Mobile\Customer\Controllers;

use Framework\BaseClass\Http\Mobile\Controller;

class IndexController extends Controller
{
    //交货单列表
    public function outBill(){
        return $this->view('out-bill');
    }

    //打印交货单详情
    public function printDetail(){
        $id = request('id');
        $token = ['token: '.session('user.token')];
        $data = http_post('http://192.168.0.126/api/service/warehouse/out-bill',['id'=>$id],'',$token);
        $data = json_decode($data,true)['data'];
        $data['id'] = $id;
        return $this->view('print-detail',compact('data'));
    }

}