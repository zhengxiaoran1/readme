<?php
/**
 * Created by PhpStorm.
 * Author: wenwenbin
 * Date: 2017/12/22
 * Time: 11:33
 */
namespace App\Http\Admin\Routes;

use Illuminate\Contracts\Routing\Registrar;

class PersonnelRoute
{
    public function map(Registrar $router)
    {
        $router->group(['namespace' => 'Personnel\Controllers', 'prefix' => 'personnel'], function ($router) {
           /*
            * 企业通讯录
            */
            $router->get('company-address-list', 'AddressListController@companyAddressList');
            $router->post('company-address-list', 'AddressListController@companyAddressList');
            $router->get('personnel-file-info', 'AddressListController@personnelFileInfo');
            $router->get('company-resume-info', 'AddressListController@companyResumeInfo');
            $router->get('address-list-member-add', 'AddressListController@addressListMemberAdd');
            $router->post('address-list-member-add', 'AddressListController@addressListMemberAdd');
            $router->get('address-list-member-edit', 'AddressListController@addressListMemberEdit');
            $router->post('address-list-member-edit', 'AddressListController@addressListMemberEdit');
            $router->get('address-list-member-del', 'AddressListController@addressListMemberDel');
            $router->post('address-list-member-del', 'AddressListController@addressListMemberDel');
            $router->post('profile-photo-upload', 'AddressListController@profilePhotoUpload');
            $router->post('address-list-employee', 'AddressListController@addressListEmployee');
            $router->post('address-list-department', 'AddressListController@addressListDepartment');
            $router->get('address-list-member-import', 'AddressListController@addressListMemberImport');
            $router->post('address-list-member-import', 'AddressListController@addressListMemberImport');
            $router->get('address-list-template-download', 'AddressListController@addressListTemplateDownload');
            
            /*
             * 考勤
             */
            $router->group(['prefix' => 'attendance'], function ($router) {
                $router->get('statistics-list', 'AttendanceController@statisticsListPage');
                $router->post('statistics-list', 'AttendanceController@statisticsList');
                $router->post('statistics-count', 'AttendanceController@statisticsCount');
                $router->get('statistics-check-in-log', 'AttendanceController@statisticsCheckInLogPage');
                $router->get('factor-list', 'AttendanceController@factorListPage');
                $router->post('factor-list', 'AttendanceController@factorList');
                $router->get('rule-list', 'AttendanceController@ruleListPage');
                $router->post('rule-list', 'AttendanceController@ruleList');
                $router->get('rule-set-up', 'AttendanceController@ruleSetUpPage');
                $router->post('rule-set-up', 'AttendanceController@ruleSetUp');
                $router->post('rule-check', 'AttendanceController@ruleCheck');
                $router->post('delete-exception-date', 'AttendanceController@deleteExceptionDate');

                $router->post('log-list', 'AttendanceController@logList');
            });

            /*
             * 日志管理
             */
            $router->group(['prefix' => 'report'], function ($router) {
                $router->get('list', 'ReportController@ReportList');
                $router->post('list', 'ReportController@ReportList');
                $router->get('detail', 'ReportController@ReportDetail');
                $router->post('detail', 'ReportController@ReportDetailt');
                $router->get('download-attachments', 'ReportController@DownloadAttachments');//下载附件

            });

            /*
             * 简历管理
             */
            $router->group(['prefix' => 'resume'], function ($router) {
                //离职人员管理
                $router->get('resign-personnel-list', 'ResumeController@resignPersonnelList');
                $router->post('resign-personnel-list', 'ResumeController@resignPersonnelList');
                $router->post('join-talent-pool', 'ResumeController@joinTalentPool');
            });

            //通过流程人员管理
            $router->get('pass-flow-list', 'PassFlowController@PassFlowList');
            $router->post('pass-flow-list', 'PassFlowController@PassFlowList');
            $router->get('join-company', 'PassFlowController@joinCompany');
            $router->post('join-company', 'PassFlowController@joinCompany');

            /**
             * 培训管理 zhu
             */
            $router->group(['prefix' => 'train'],function($router){
                //培训计划
                $router->get('plan-list','TrainController@planList');
                $router->post('plan-list','TrainController@planList');
                //添加培训计划
                $router->get('plan-add','TrainController@planAdd');
                $router->post('plan-add','TrainController@planAdd');
                //编辑
                $router->get('plan-edit','TrainController@planEdit');
                $router->post('plan-edit','TrainController@planEdit');
                //上传图片
                $router->post('plan-image-upload','TrainController@planImageUpload');

                //培训报名列表
                $router->get('train-apply','TrainController@trainApply');
                $router->post('train-apply','TrainController@trainApply');
                //报名详情
                $router->get('train-apply-detail','TrainController@trainApplyDetail');
                $router->post('train-apply-detail','TrainController@trainApplyDetail');

                //作业上交
                $router->get('homework-submit-list','TrainController@homeworkSubmitList');
                $router->post('homework-submit-list','TrainController@homeworkSubmitList');
                $router->get('homework-submit-detail','TrainController@homeworkSubmitDetail');

                //培训试题
                $router->get('train-question-list','TrainQuestionController@trainQuestionList');
                $router->post('train-question-list','TrainQuestionController@trainQuestionList');
                $router->get('train-question-add','TrainQuestionController@trainQuestionAdd');
                $router->post('train-question-add','TrainQuestionController@trainQuestionAdd');
                $router->post('train-question-del','TrainQuestionController@trainQuestionDel');
                $router->get('train-question-edit','TrainQuestionController@trainQuestionEdit');
                $router->post('train-question-edit','TrainQuestionController@trainQuestionEdit');
                $router->get('train-question-detail','TrainQuestionController@trainQuestionDetail');

            });
        });
    }
}