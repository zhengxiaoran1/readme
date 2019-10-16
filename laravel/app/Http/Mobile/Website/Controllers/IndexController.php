<?php
/**
 * Created by PhpStorm.
 * User: Sojo
 * Date: 2017/5/31
 * Time: 15:52
 */
namespace App\Http\Mobile\Website\Controllers;

use Framework\BaseClass\Http\Mobile\Controller;

class IndexController extends Controller
{
    /**
     * 首页
     * @author Sojo
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return $this->view('home');
    }

    public function pwd(){
        return $this->view('pwd');
    }
    public function home(){
        return $this->view('index');
    }

    /**
     * 登录
     * @author Sojo
     * @return $this|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function loginHome()
    {
        if (request()->isMethod('post')) {

            $data = request('data');
            if(!$data) return $this->ajaxFail('登陆失败');
            session(['user'=>$data]);
            return json_encode(['code'=>0,'message'=>'登陆成功','data'=>[]]);
        }
        return $this->view('login');
    }


    /**
     * 登出
     * @author Sojo
     */
    public function logout()
    {
        session(['user'=>""]);
        return json_encode(['code'=>0,'message'=>'退出成功','data'=>[]]);
    }
}
