<?php
/**
 * Created by PhpStorm.
 * User: wenwenbin
 * Date: 2018/1/6
 * Time: 10:58
 */
namespace App\Api\OA\Routes;

use Dingo\Api\Routing\Router;

class PersonnelRoute
{
    public function map(Router $router)
    {
        $router->group(['namespace' => 'Personnel\Controllers', 'prefix' => 'personnel'], function ($router) {
            $router->post('department-list', 'PersonnelController@departmentList'); //部门列表
            $router->post('employee-info', 'PersonnelController@employeeInfo'); //通讯录员工信息
            $router->post('transfer-post-apply', 'PersonnelController@transferPostApply'); //岗位调动申请
            $router->post('resignation-apply', 'PersonnelController@resignationApply'); //离职申请
        });
    }
}