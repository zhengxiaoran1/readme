<?php
/**
 * Created by PhpStorm.
 * User: zhuyujun
 * Date: 2017/11/9
 * Time: 14:42
 */

namespace App\Http\Admin\Routes;

use Illuminate\Contracts\Routing\Registrar;

class AutoRoute
{
    public function map(Registrar $router)
    {
        $router->group(['namespace' => 'Auto\Controllers', 'prefix' => 'auto'], function ($router) {
            //自动给未配置审批流程的工厂增加配置
            $setFunction = 'companyWorkFlow';
            $router->get('/'.$setFunction,'IndexController@'.$setFunction);
            $router->post('/'.$setFunction,'IndexController@'.$setFunction);

        });
    }

}