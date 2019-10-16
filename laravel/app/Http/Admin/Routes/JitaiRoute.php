<?php
/**
 * Created by PhpStorm.
 * User: LuLingFeng
 * Date: 2017/8/2
 * Time: 13:21
 */
namespace App\Http\Admin\Routes;

use Illuminate\Contracts\Routing\Registrar;

class JitaiRoute
{
    public function map(Registrar $router)
    {
        $router->group(['namespace' => 'Jitai\Controllers', 'prefix' => 'jitai'], function ($router) {


            //机台信息
            //列表
            $router->get('jitai-list', 'JitaiController@JitaiList');
            $router->post('jitai-list', 'JitaiController@JitaiList');

            //添加机台
            $router->get('jitai-add', 'JitaiController@addPage');
            $router->post('jitai-add', 'JitaiController@add');

            //编辑机台信息
            $router->get('jitai-edit','JitaiController@editPage');
            $router->post('jitai-edit','JitaiController@edit');

            //删除机台
            $router->post('jitai-delete','JitaiController@delete');

            //上传机台图片
            $router->post('img-upload','JitaiController@imgUpload');
            $router->post('edit-img','JitaiController@editImg');

            //机台绑定员工
            $router->get('jitai-bind-user','JitaiController@bindUserPage');
            $router->post('jitai-bind-user','JitaiController@bindUser');

        });
    }
}