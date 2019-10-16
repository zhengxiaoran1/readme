<?php
/**
 * Created by PhpStorm.
 * Author: Sojo
 * Date: 2017/2/16
 * Time: 19:33
 */

namespace App\Http\Admin\Routes;

use Illuminate\Contracts\Routing\Registrar;

class AdministrationRoute
{
    public function map(Registrar $router)
    {
        $router->group(['namespace' => 'Administration\Controllers', 'prefix' => 'administration'], function ($router) {
            //新闻政策
            $router->group(['prefix' => 'policy'], function ($router) {
                $router->post('notice-policy-list', 'NoticePolicyController@noticePolicyList');
                $router->get('notice-policy-list', 'NoticePolicyController@noticePolicyList');
                $router->post('notice-policy-add', 'NoticePolicyController@noticePolicyAdd');
                $router->get('notice-policy-add', 'NoticePolicyController@noticePolicyAdd');
                $router->post('notice-policy-edit', 'NoticePolicyController@noticePolicyEdit');
                $router->get('notice-policy-edit', 'NoticePolicyController@noticePolicyEdit');
                //软删除新闻
                $router->post('del-notice-policy', 'NoticePolicyController@noticePolicyDel');
                //上传新闻政策封面图
                $router->post('notice-policy-img-upload', 'NoticePolicyController@noticePolicyImgUpload');
            });

            //通知公告
            $router->group(['prefix' => 'notice'], function ($router) {

                $router->post('check-notice-edit', 'OaNoticeController@checkOaNoticeEdit');
                $router->get('check-notice-edit', 'OaNoticeController@checkOaNoticeEdit');
                $router->post('received-notice-edit', 'OaNoticeController@receivedOaNoticeEdit');
                $router->get('received-notice-edit', 'OaNoticeController@receivedOaNoticeEdit');
                //软删除通知公告
                $router->post('del-notice', 'OaNoticeController@delOaNotice');
                //上传通知公告附件
                $router->post('notice-fie-upload', 'OaNoticeController@noticeFieUpload');
                //上传通知公告图片
                $router->post('notice-img-upload', 'OaNoticeController@noticeImgUpload');
                //发送的通知公告
                $router->post('send-notice-list', 'OaNoticeController@sendOaNoticeList');
                $router->get('send-notice-list', 'OaNoticeController@sendOaNoticeList');
                $router->post('send-notice-add', 'OaNoticeController@sendOaNoticeAdd');
                $router->get('send-notice-add', 'OaNoticeController@sendOaNoticeAdd');
                $router->post('send-notice-edit', 'OaNoticeController@sendOaNoticeEdit');
                $router->get('send-notice-edit', 'OaNoticeController@sendOaNoticeEdit');
                //审核通知公告
                $router->post('check-notice-list', 'OaNoticeController@checkOaNoticeList');
                $router->get('check-notice-list', 'OaNoticeController@checkOaNoticeList');
                $router->post('check-notice-edit', 'OaNoticeController@checkOaNoticeEdit');
                $router->get('check-notice-edit', 'OaNoticeController@checkOaNoticeEdit');
                $router->post('check-notice-check', 'OaNoticeController@checkOaNoticeCheck');
                $router->get('check-notice-check', 'OaNoticeController@checkOaNoticeCheck');

                $router->get('download-attachments', 'OaNoticeController@downloadAttachments');//下载附件

            });

            //会议室列表
            $router->post('conference-room-list', 'ConferenceRoomController@conferenceRoomList');
            $router->get('conference-room-list', 'ConferenceRoomController@conferenceRoomList');
            $router->post('conference-room-add', 'ConferenceRoomController@conferenceRoomAdd');
            $router->get('conference-room-add', 'ConferenceRoomController@conferenceRoomAdd');
            $router->post('conference-room-edit', 'ConferenceRoomController@conferenceRoomEdit');
            $router->get('conference-room-edit', 'ConferenceRoomController@conferenceRoomEdit');
            //会议室预约记录
            $router->post('conference-room-record-list', 'ConferenceRoomController@conferenceRoomRecordList');
            $router->get('conference-room-record-list', 'ConferenceRoomController@conferenceRoomRecordList');
            $router->post('conference-room-record-edit', 'ConferenceRoomController@conferenceRoomRecordEdit');
            $router->get('conference-room-record-edit', 'ConferenceRoomController@conferenceRoomRecordEdit');
            //上传会议室图片
            $router->post('conference-room-img-upload', 'ConferenceRoomController@conferenceRoomImgUpload');



            // 工作流程
            $router->group(['prefix' => 'workflow'], function ($router) {
                $router->get('index', 'WorkflowController@index');
                $router->get('create/{type}', 'WorkflowController@create');
                $router->post('create/{type}', 'WorkflowController@create');
                $router->get('handle', 'WorkflowController@handle');
                $router->post('handle', 'WorkflowController@handle');
                $router->match(['get', 'post'], 'my-list', 'WorkflowController@myList');
                $router->match(['get', 'post'], 'todo-list', 'WorkflowController@todoList');
                $router->match(['get', 'post'], 'done-list', 'WorkflowController@doneList');
                $router->match(['get', 'post'], 'copy-list', 'WorkflowController@copyList');
                $router->match(['get', 'post'], 'end-list', 'WorkflowController@endList');
                $router->post('upload-enclosure', 'WorkflowController@uploadEnclosure');
                $router->get('detail', 'WorkflowController@detail');
                $router->post('copy', 'WorkflowController@copy');
                $router->match(['get', 'post'], 'flow-list', 'WorkflowController@flowList');
                $router->match(['get', 'post'], 'flow-edit', 'WorkflowController@flowEdit');
                $router->match(['get', 'post'], 'flow-config', 'WorkflowController@flowConfig');
                $router->match(['get', 'post'], 'flow-config-process-add', 'WorkflowController@flowConfigProcessAdd');
                $router->match(['get', 'post'], 'flow-config-process-edit', 'WorkflowController@flowConfigProcessEdit');
                $router->post('flow-config-process-delete', 'WorkflowController@flowConfigProcessDelete');
            });


            $router->group(['prefix' => 'cost-management'], function ($router) {
                $router->get('bill-list', 'BillController@billList');
                $router->post('bill-list', 'BillController@billList');
                $router->get('bill-add', 'BillController@billAdd');
                $router->post('bill-add', 'BillController@billAdd');
                $router->get('bill-edit', 'BillController@billEdit');
                $router->post('bill-edit', 'BillController@billEdit');
                $router->get('bill-import', 'BillController@billImport');
                $router->post('bill-import', 'BillController@billImport');
                $router->get('bill-download', 'BillController@billDownloadModel');
            });

            //用车
            $router->get('car-use-list', 'CarUseController@carUseList');
            $router->post('car-use-list', 'CarUseController@carUseList');
            $router->get('car-use-add', 'CarUseController@carUseAdd');
            $router->post('car-use-add', 'CarUseController@carUseAdd');
            $router->get('car-use-edit', 'CarUseController@carUseEdit');
            $router->post('car-use-edit', 'CarUseController@carUseEdit');

            //食堂
            $router->get('canteen-list', 'CanteenController@canteenList');
            $router->post('canteen-list', 'CanteenController@canteenList');
            $router->get('canteen-add', 'CanteenController@canteenAdd');
            $router->post('canteen-add', 'CanteenController@canteenAdd');
            $router->get('canteen-edit', 'CanteenController@canteenEdit');
            $router->post('canteen-edit', 'CanteenController@canteenEdit');

            //物品借用
            $router->get('goods-borrowing-list', 'GoodsBorrowingController@goodsBorrowingList');
            $router->post('goods-borrowing-list', 'GoodsBorrowingController@goodsBorrowingList');
            $router->get('goods-borrowing-add', 'GoodsBorrowingController@goodsBorrowingAdd');
            $router->post('goods-borrowing-add', 'GoodsBorrowingController@goodsBorrowingAdd');
            $router->get('goods-borrowing-edit', 'GoodsBorrowingController@goodsBorrowingEdit');
            $router->post('goods-borrowing-edit', 'GoodsBorrowingController@goodsBorrowingEdit');

            //外联管理
            $router->get('outreach-list', 'OutreachController@outreachList');
            $router->post('outreach-list', 'OutreachController@outreachList');
            $router->get('outreach-add', 'OutreachController@outreachAdd');
            $router->post('outreach-add', 'OutreachController@outreachAdd');
            $router->get('outreach-edit', 'OutreachController@outreachEdit');
            $router->post('outreach-edit', 'OutreachController@outreachEdit');

            //固定资产列表
            $router->get('fixed-assets-list', 'FixedAssetsController@fixedAssetsList');
            $router->post('fixed-assets-list', 'FixedAssetsController@fixedAssetsList');
            $router->get('fixed-assets-add', 'FixedAssetsController@fixedAssetsAdd');
            $router->post('fixed-assets-add', 'FixedAssetsController@fixedAssetsAdd');
            $router->get('fixed-assets-edit', 'FixedAssetsController@fixedAssetsEdit');
            $router->post('fixed-assets-edit', 'FixedAssetsController@fixedAssetsEdit');
        });
    }
}