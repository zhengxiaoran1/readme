<?php
/**
 * Created by PhpStorm.
 * User: wenwenbin
 * Date: 2018/1/9
 * Time: 11:28
 */
namespace App\Api\OA\Contacts\Models;

use App\Eloquent\Oa\Company;
use App\Eloquent\Oa\Contacts as EloquentContacts;
use App\Eloquent\Oa\Department;
use Framework\BaseClass\Api\Model;

class Contacts extends Model
{
    private $returnStr = '';
    private $contacts_count = 0;

    /**
     * 获取通讯录数据
     * @author wenwebin
     * @param int $contactsId 通讯录联系人id
     * @return string
     */
    public function getContactsData($contactsId)
    {
        $contacts = '{"versions":"0","allUser":[';

        if (!$contactsId) {
            xThrow(ERR_OA_FUNCTION_NOT_QUALIFIED);
            $contacts .= $this->printoutContacts([]);
            $contacts .= "]}";

            $content = $contacts;

            return $content;
        }

        // 根据用户ID获取该公司ID
//        $ownerEmployeeInfo = Employee::with('ownerInfo')->where('user_id', $userId)->first();
        $contact = EloquentContacts::with('companyInfo')->find($contactsId);
//        $owner = $ownerEmployeeInfo->ownerInfo;
//        if (!$ownerEmployeeInfo || empty($owner)) {
        // @author lulingfeng 2017.8.30 owner赋值放到下面，防止$ownerEmployeeInfo空对象错误  --start
        if (is_null($contact) || is_null($contact->companyInfo)) {
            xThrow(ERR_COMPANY_NOT_EXIST);
            $contacts .= $this->printoutContacts([]);
            $contacts .= "]}";

            $content = $contacts;

            return $content;
        }
        $company = $contact->companyInfo;
        // @author lulingfeng 2017.8.30 owner赋值放到下面，防止$ownerEmployeeInfo空对象错误  --end

        // 根据公司ID，获取该公司通讯录内有多少员工
        $ownerEmployeeList = EloquentContacts::with('departmentInfo')
            ->where('oa_company_id', $company->id)
            ->orderBy('updated_at', 'desc')
            ->get();
        $ownerEmployeeNumber = $ownerEmployeeList->count();

        // 获取该公司下最新修改过信息的时间戳
        $newUpdateTime = $ownerEmployeeList->first()->updated_at->timestamp;

        // 版本号由公司总员工数 + 最新修改的时间戳 - 配合APP，版本号减去10亿
        $version = abs($ownerEmployeeNumber + $newUpdateTime - 1000000000);

        //版本号，用来让手机判断是否需要更新通讯录
        $contacts = '{"versions":"' . $version . '","allUser":[';

        // 数据拼装
        $company->cat_name = $company->name;

        $category_tree[$company->id] = ['main' => $company->toArray(), 'sub' => [], 'people' => []];
        foreach ($ownerEmployeeList as $ownerEmployee) {
            $ownerEmployee->contact_name = $ownerEmployee->name;
//            $ownerEmployee->contact_name = isset($ownerEmployee->userInfo->name) ? $ownerEmployee->userInfo->name : '';
//            $ownerEmployee->email = isset($ownerEmployee->userInfo->email) ? $ownerEmployee->userInfo->email : '';
            if (is_null($ownerEmployee->departmentInfo)) {
                $ownerEmployee->job = '-' . $ownerEmployee->position;
            } else {
                $ownerEmployee->job = $ownerEmployee->departmentInfo->name . '-' . $ownerEmployee->position;
            }
//            $ownerEmployee->job = $ownerEmployee->department . '-' . $ownerEmployee->job_title;
//            $ownerEmployee->mobile = !empty($ownerEmployee->userInfo->mobile_phone)
//                ? $ownerEmployee->userInfo->mobile_phone
//                : $ownerEmployee->work_phone;
            $ownerEmployee->tel = '';
            $ownerEmployee->shorttel = '';
//            $ownerEmployee->avatar = '';
            // @author lulingfeng 2017.9.13 添加头像   start-----
            $ownerEmployee->avatar = $ownerEmployee->profile_photo_url ? env('APP_URL') . $ownerEmployee->profile_photo_url : env('APP_URL') . '/images/global/default_avatar.png';
            // @author lulingfeng 2017.9.13 添加头像   end-------

            $category_tree[$company->id]['people'][] = $ownerEmployee->toArray();
        }

        $contacts .= $this->printoutContacts($category_tree);
        $contacts .= "]}";

        return $contacts;
    }

    /**
     * 递归处理通讯录数据
     * @author Sojo
     * @param $tree
     * @return string
     */
    private function getContactsRecursive($tree)
    {
        if (count($tree['sub']) > 0) { //如果有子部门，继续往下找
            $this->returnStr .= '{"' . $tree['main']['cat_name'] . '":[';
            for ($i = 0; $i < count($tree['sub']); $i++) {
                $this->returnStr .= $this->getContactsRecursive($tree['sub'][$i]);
            }
            if (count($tree['sub']) > 0)
                $this->returnStr = substr($this->returnStr, 0, -1);
            $this->returnStr .= ']},';
        } else { //如果没有子部门了，就找这个部门里的所有人员
            $this->returnStr .= '{"' . $tree['main']['cat_name'] . '":[';
            for ($i = 0; $i < count($tree['people']); $i++) {
                $this->contacts_count++;
                $gbkname = iconv('UTF-8', 'GBK', $tree['people'][$i]['contact_name']); //这里utf-8转GBK的用意是，转拼音的那个函数需要GBK
                $gbkname = str_replace(" ", "", $gbkname);

                require_once dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/framework/Extend/Helpers/pinyin/pinyin.php';

                $flow = get_pinyin_array($gbkname);
                $shortSpellArr = explode("_", $flow[0]);
                $shortSpell = "";
                for ($s = 1; $s < count($shortSpellArr) - 1; $s++) {
                    $shortSpell .= strtoupper(substr($shortSpellArr[$s], 0, 1));
                }
                $tree['people'][$i]['longspell'] = iconv('GBK', 'UTF-8', str_replace("_", "", $flow[0])); //拼音获得后再转回utf-8
                $tree['people'][$i]['shortspell'] = iconv('GBK', 'UTF-8', $shortSpell);
                $this->returnStr .= sprintf('{"id":"%d","username":"%s","email":"%s","title":"%s","workCellPhone":"%5$s","personalCellPhone":"","workingPhone":"%6$s","homePhone":"","shortNum":"%7$s","pinyin":["%8$s"],"firstPinyin":["%9$s"],"avatar":"%10$s"},', $tree['people'][$i]['id'], $tree['people'][$i]['contact_name'], $tree['people'][$i]['email'], $tree['people'][$i]['job'], $tree['people'][$i]['mobile'], $tree['people'][$i]['tel'], $tree['people'][$i]['shorttel'], $tree['people'][$i]['longspell'], $tree['people'][$i]['shortspell'], $tree['people'][$i]['avatar']);
            }
            if (count($tree['people']) > 0)
                $this->returnStr = substr($this->returnStr, 0, -1);

            $this->returnStr .= ']},';
            return $this->returnStr;
        }
        return $this->returnStr;
    }

    /**
     * 输出通讯录返回数据
     * @author Sojo
     * @param $tree
     * @return string
     */
    private function printoutContacts($tree)
    {
        $contactStr = "";
        foreach ($tree as $item) {
            $contactStr .= $this->getContactsRecursive($item);
        }
        $contactStr = substr($contactStr, 0, -1);

        return $contactStr;
    }

//    public function getContactsData($oa_contacts_id)
//    {
//        $contact = EloquentContacts::with('companyInfo')->find($oa_contacts_id);
//        if (is_null($contact)) xThrow(ERR_OA_CONTACTS_NOT_EXIST);
//        if (is_null($contact->companyInfo)) xThrow(ERR_COMPANY_NOT_EXIST);
//        $departmentList = Department::where([['oa_company_id', $contact->oa_company_id], ['pid', 0]])->get(['id', 'name']);
//        $result = [];
//        foreach ($departmentList as $department){
//            $result[] = $this->getDepartmentEmployeeList($department->id);
//        }
//        return $result;
//    }
//
//    private function getDepartmentEmployeeList($departmentId)
//    {
//        $department = Department::with(['contactsList' => function($query){
//            $query->select('id', 'oa_company_id', 'oa_department_id', 'name', 'mobile', 'position', 'email', 'profile_photo_url');
//        }, 'subordinateList'])->find($departmentId, ['id', 'name']);
//        foreach ($department->contactsList as $contacts){
//            $contacts->profile_photo_url = $contacts->profile_photo_url ? env('APP_URL') . $contacts->profile_photo_url : env('APP_URL') . '/images/global/default_avatar.png';
//        }
//        if (is_null($department)) xThrow(ERR_ADMIN_EXCEL_ERROR);
//        foreach ($department->subordinateList as $k => $subordinate){
//            $department->subordinateList[$k] = $this->getDepartmentEmployeeList($subordinate->id);
//        }
//        return $department;
//    }

    public function getCompanyFramework($company_id, $department_id)
    {
        $company_name = Company::where('id', $company_id)->value('name');
        $department_list = Department::where([['oa_company_id', $company_id], ['pid', $department_id]])->get();
        $contacts_list = EloquentContacts::where([['oa_company_id', $company_id], ['oa_department_id', $department_id]])->get();
        return [
            'department_list' => $department_list,
            'contacts_list'   => $contacts_list,
            'company_id'      => $company_id,
            'company_name'    => $company_name
        ];
    }
}
