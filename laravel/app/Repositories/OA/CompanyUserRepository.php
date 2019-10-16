<?php
/**
 * Created by PhpStorm.
 * Author: Sojo
 * Date: 2017/2/25
 * Time: 16:17
 */

namespace App\Repositories\OA;

use App\Eloquent\Oa\CompanyUser;
use Framework\BaseClass\Repositories\Repository;

class CompanyUserRepository extends Repository
{
    public function model()
    {
        return CompanyUser::class;
    }

    public function setUseCompany($userId, $companyId)
    {
        // 所有用户相关的公司is_use字段全修改为0，然后设置对应的公司的那条数据为is_use ＝ 1
        $userCompanyList = $this->getList([
            'user_id' => $userId
        ]);

        if (!in_array($companyId, $userCompanyList->pluck('oa_company_id')->all())) xThrow(ERR_PARAMETER, '公司不存在');

        $useCompanyUserInfo = null;
        foreach ($userCompanyList as $userCompany) {
            if ($userCompany->oa_company_id === $companyId) {
                if ($userCompany->is_use !== 1) {
                    $this->update($userCompany->id, ['is_use' => 1]);
                    $userCompany->is_use = 1;
                }

                $useCompanyUserInfo = $userCompany;
                continue;
            }

            if ($userCompany->is_use == 1) {
                $this->update($userCompany->id, ['is_use' => 0]);
            }
        }

        return $useCompanyUserInfo;
    }
}