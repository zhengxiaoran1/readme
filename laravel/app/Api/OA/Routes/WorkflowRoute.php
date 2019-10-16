<?php
/**
 * Created by PhpStorm.
 * Author: Sxy
 * Date: 2017/12/12
 * Time: 15:36
 */

namespace App\Api\OA\Routes;

use Dingo\Api\Routing\Router;

class WorkflowRoute
{
    public function map(Router $router)
    {
        $router->group(['namespace' => 'Workflow\Controllers', 'prefix' => 'workflow'], function ($router) {
            $router->post('get-paging-list', 'IndexController@getPagingList');
            $router->post('get-details', 'IndexController@getDetails');
            $router->post('permission', 'IndexController@permission');
            $router->post('copy-read', 'IndexController@copyRead');
            $router->post('create', 'IndexController@create')->middleware('withdraw','abnormal');
            $router->post('handle', 'IndexController@handle');
            // 创建模板
            $router->post('create-template', 'IndexController@createTemplate');

            // 流程配置
            $router->group(['prefix' => 'flow-config'], function ($router) {
                $router->post('get-field-list', 'FlowConfigController@getFieldList');
            });
        });
    }
}