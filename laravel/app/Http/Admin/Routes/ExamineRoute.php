<?php
/**
 * Created by PhpStorm.
 * User: zhuyujun
 * Date: 2018/1/29
 * Time: 14:42
 */

namespace App\Http\Admin\Routes;

use Illuminate\Contracts\Routing\Registrar;

class ExamineRoute
{
    public function map(Registrar $router)
    {
        $router->group(['namespace' => 'Examine\Controllers', 'prefix' => 'examine'], function ($router) {
            //采购审批流程
            $setFunction = 'getPurchasingExamineProcess';
            $router->any('/'.$setFunction,'IndexController@'.$setFunction);

            //采购审批流程-数据获取
            $setFunction = 'getPurchasingExamineProcessData';
            $router->any('/'.$setFunction,'IndexController@'.$setFunction);

            //编辑采购审批流程
            $setFunction = 'editPurchasingExamineProcess';
            $router->any('/'.$setFunction,'IndexController@'.$setFunction);

            //编辑采购审批流程-提交
            $setFunction = 'editPurchasingExamineProcessSubmit';
            $router->any('/'.$setFunction,'IndexController@'.$setFunction);

        });
    }

}