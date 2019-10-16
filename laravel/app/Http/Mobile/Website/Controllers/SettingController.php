<?php
/**
 * Created by PhpStorm.
 * User: Sojo
 * Date: 2017/5/31
 * Time: 15:52
 */
namespace App\Http\Mobile\Website\Controllers;

use Framework\BaseClass\Http\Mobile\Controller;

class SettingController extends Controller
{
    public function menuManager()
    {
        return $this->view('menu-manager');
    }
}
