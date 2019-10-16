<?php
/**
 * Created by PhpStorm.
 * User: wenwenbin
 * Date: 2018/1/9
 * Time: 11:28
 */
namespace App\Api\OA\Contacts\Controllers;

use App\Api\OA\Contacts\Models\Contacts;
use App\Repositories\Foundation\User\UserRoleRepository;
use App\Repositories\OA\CompanyUserRepository;
use Framework\BaseClass\Api\Controller;

class ContactsController extends Controller
{
    /**
     * 根据用户ID获取其对应的公司列表
     * @author sojo
     * @return mixed
     */
    public function getUserCompanyList()
    {
        $userInfo = app('token')->checkToken();

        $companyUser = new CompanyUserRepository();
        $userCompanyList = $companyUser->getList([
            'user_id' => $userInfo['user_id']
        ], ['companyInfo'], ['oa_company_id', 'user_id', 'is_use']);

        foreach ($userCompanyList as $userCompany) {
            $userCompany->company_id = $userCompany->companyInfo->id;
            $userCompany->company_name = $userCompany->companyInfo->name;

            unset($userCompany->oa_company_id, $userCompany->user_id, $userCompany->companyInfo);
        }

        return $userCompanyList;
    }

    /**
     * 设置用户当前的公司
     * @author sojo
     */
    public function setUseCompany()
    {
        $userInfo = app('token')->checkToken();
        $params = $this->getRequestParameters(['company_id']);

        $companyUser = new CompanyUserRepository();
        $companyUserInfo = $companyUser->setUseCompany($userInfo['user_id'], (int)$params['company_id']);

        // 修改用户角色为新公司所对应的APP角色
        $userRole = new UserRoleRepository();
        $userRole->setUserRole($companyUserInfo->user_id, $companyUserInfo->role_id);

        return;
    }

    /**
     * 获取通讯录数据
     * @author wewenbin
     * @return mixed
     */
    public function addressList()
    {
//        try {
//            $userInfo = app('token')->checkToken();
//            $userId = $userInfo->id;
//        } catch (\DebugError $e) {
//            $userId = 0;
//        }
//
//        // @author lulingfeng 2017.9.5 验证角色 start---
//        if (!empty($userInfo)) {
//            $role = new Role();
//            $role->checkRole($userInfo->id, $userInfo->company_id);
//        }
        // @author lulingfeng 2017.9.5 验证角色 end-----
        $token = app('token')->checkToken();

        $contacts = new Contacts();
        $contactsData = $contacts->getContactsData($token['oa_contacts_id']);
        return json_decode($contactsData, true);
    }

    public function getCompanyFramework() {
        $token = app('token')->checkToken();
        $params = $this->getRequestParameters([], ['department_id']);
        if (is_null($params['department_id'])) $params['department_id'] = 0;
        $contacts = new Contacts();
        return $contacts->getCompanyFramework($token['oa_company_id'], $params['department_id']);
    }
}