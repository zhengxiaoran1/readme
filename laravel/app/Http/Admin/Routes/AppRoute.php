<?php
/**
 * Created by PhpStorm.
 * User: LuLingFeng
 * Date: 2017/8/2
 * Time: 13:21
 */
namespace App\Http\Admin\Routes;

use Illuminate\Contracts\Routing\Registrar;

class AppRoute
{
    public function map(Registrar $router)
    {
        $router->group(['namespace' => 'App\Controllers', 'prefix' => 'app'], function ($router) {
//            // APP用户管理
//            $router->get('app-user-list', 'UserController@appUserList');
//            $router->post('app-user-list', 'UserController@appUserList');
//            /*
//             * 角色管理
//             */
//            // 列表
//            $router->get('role-list', 'UserRoleController@roleList');
//            $router->post('role-list', 'UserRoleController@roleList');
//            // 添加
//            $router->get('role-add', 'UserRoleController@roleAdd');
//            $router->post('role-add', 'UserRoleController@roleAdd');
//            // 修改
//            $router->get('role-edit', 'UserRoleController@roleEdit');
//            $router->post('role-edit', 'UserRoleController@roleEdit');
//            /*
//             * 用户角色绑定
//             */
//            $router->get('role-bind-list', 'UserRoleController@roleBindList');
//            $router->post('role-bind-list', 'UserRoleController@roleBindList');
//            $router->get('role-bind', 'UserRoleController@roleBindDialogPage');
//            $router->post('role-bind', 'UserRoleController@roleBind');
//            $router->post('role-unbind', 'UserRoleController@roleUnbind');
//            /*
//             * 角色服务绑定
//             */
//            $router->get('bind-app-service', 'UserRoleController@bindAppServicePage');
//            $router->post('bind-app-service', 'UserRoleController@bindAppService');
//            $router->post('unbound-app-service-list', 'UserRoleController@unboundAppServiceList');
//            $router->get('unbind-app-service', 'UserRoleController@unbindAppServicePage');
//            $router->post('unbind-app-service', 'UserRoleController@unbindAppService');
//            $router->post('bound-app-service-list', 'UserRoleController@boundAppServiceList');
//            /*
//             * App服务管理
//             */
//            // 列表
//            $router->get('service-list', 'AppServiceController@serviceList');
//            $router->post('service-list', 'AppServiceController@serviceList');
//            // 添加
//            $router->get('service-add', 'AppServiceController@serviceAdd');
//            $router->post('service-add', 'AppServiceController@serviceAdd');
//            // 修改
//            $router->get('service-edit', 'AppServiceController@serviceEdit');
//            $router->post('service-edit', 'AppServiceController@serviceEdit');
//            // 上传图标
//            $router->post('icon-upload', 'AppServiceController@iconUpload');
//
//            /*
//             * 应用位置管理
//             */
//            // 列表
//            $router->get('location-list', 'AppServiceController@locationList');
//            $router->post('location-list', 'AppServiceController@locationList');
//            // 添加
//            $router->get('location-add', 'AppServiceController@locationAdd');
//            $router->post('location-add', 'AppServiceController@locationAdd');
//            // 修改
//            $router->get('location-edit', 'AppServiceController@locationEdit');
//            $router->post('location-edit', 'AppServiceController@locationEdit');
//            //删除
//            $router->post('location-del', 'AppServiceController@location');
//
//            /*
//             * 轮播图管理
//             */
//            //列表
//            $router->get('slide-list', 'SlideController@slideList');
//            $router->post('slide-list', 'SlideController@slideList');
//            // 添加
//            $router->get('slide-add', 'SlideController@slideAdd');
//            $router->post('slide-add', 'SlideController@slideAdd');
//            // 修改
//            $router->get('slide-edit', 'SlideController@slideEdit');
//            $router->post('slide-edit', 'SlideController@slideEdit');
//            /*
//             * 轮播图位置管理
//             */
//            //列表
//            $router->get('slide-location-list', 'SlideController@slideLocationList');
//            $router->post('slide-location-list', 'SlideController@slideLocationList');
//            // 添加
//            $router->get('slide-location-add', 'SlideController@slideLocationAdd');
//            $router->post('slide-location-add', 'SlideController@slideLocationAdd');
//            // 修改
//            $router->get('slide-location-edit', 'SlideController@slideLocationEdit');
//            $router->post('slide-location-edit', 'SlideController@slideLocationEdit');
//
//            // 上传轮播图
//            $router->post('slide-img-upload', 'SlideController@slideImgUpload');

            //APP版本信息
            //列表
            $router->get('app-version-list', 'VersionController@VersionList');
            $router->post('app-version-list', 'VersionController@VersionList');
            // 详情
            $router->get('app-version-detail', 'VersionController@VersionDetail');
            $router->post('app-version-detail', 'VersionController@VersionDetail');
            // 编辑
            $router->get('app-version-edit', 'VersionController@VersionEdit');
            $router->post('app-version-edit', 'VersionController@VersionEdit');
            // 编辑
            $router->get('app-version-add', 'VersionController@addPage');
            $router->post('app-version-add', 'VersionController@add');

            $router->group(['prefix' => 'log'], function ($router) {
                $router->get('reading-list', 'LogController@readingList');
                $router->post('reading-list', 'LogController@readingList');
            });

//            /*
//             * 咨询
//             */
//            $router->get('suggestion-manage', 'SuggestController@suggestionManage');//进入咨询页面
//            $router->post('suggestion-manage', 'SuggestController@suggestionManage');//获取咨询列表数据
//            $router->get('suggestion-edit', 'SuggestController@suggestionEdit');//进入编辑咨询页面
//            $router->post('suggestion-save', 'SuggestController@suggestionSave');//对管理员回复内容进行保存
//            $router->post('suggestion-over', 'SuggestController@suggestionOver');//咨询结束，邀请评价
//
//            /*
//             * 协议管理
//             */
//            $router->match(['get', 'post'], 'protocol-list', 'ProtocolController@protocolList');
//            $router->match(['get', 'post'], 'protocol-add', 'ProtocolController@create');
//            $router->match(['get', 'post'], 'protocol-edit', 'ProtocolController@modify');
//            $router->post('protocol-delete', 'ProtocolController@delete');
//
//            /*
//             * 信息配置
//             */
//            $router->match(['get', 'post'], 'info-config-list', 'InfoConfigController@infoConfigList');
//            $router->match(['get', 'post'], 'info-config-edit', 'InfoConfigController@modify');
//
//            /*
//             * 精选岗位
//             */
//            $router->match(['get', 'post'], 'choice-post-list', 'JobController@choicePostList');
//            $router->match(['get', 'post'], 'choice-post-add', 'JobController@choicePostAdd');
//            $router->match(['get', 'post'], 'choice-post-edit', 'JobController@choicePostEdit');
//            $router->post('choice-post-del', 'JobController@choicePostDel');
//            $router->post('fg-post-list', 'JobController@fgPostList');
        });
    }
}