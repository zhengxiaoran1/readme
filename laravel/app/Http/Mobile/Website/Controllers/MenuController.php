<?php
/**
 * created by zzy
 * date: 2017/10/24 9:28
 */


namespace App\Http\Mobile\Website\Controllers;
use Framework\BaseClass\Http\Mobile\Controller;
use App\Eloquent\Ygt\AdminMenu;

class MenuController extends Controller
{

    public function index()
    {
    }
    public function lists()
    {
        if (request()->isMethod('post')) {

            $lists          = AdminMenu::getList('');
            return $lists->toJson();
        }
        return $this->view('lists' );
    }
}