<?php
/**
 * Created by PhpStorm.
 * User: sojo
 * Date: 2018/3/3
 * Time: 18:50
 */
namespace App\Api\OA\Company\Models;

use App\Repositories\OA\CompanyInstitutionRepository;
use Framework\BaseClass\Api\Model;

class Regulation extends Model
{
    public function getPagingList($companyId, $page, $pageSize)
    {
        $regulation = new CompanyInstitutionRepository();
        $regulationList = $regulation->getPagingList($page, $pageSize, [
            'oa_company_id' => $companyId,
            'display' => 1
        ], [], ['id', 'title','created_at'], ['sort' => 'desc']);
        foreach ($regulationList[0] as $item) {
            $item->created_time = date('Y-m-d', $item->created_at->timestamp);
        }
        return $regulationList;
    }

    public function getDetails($regulationId)
    {
        $regulation = new CompanyInstitutionRepository();
        $regulationInfo = $regulation->find($regulationId, [], ['id', 'title', 'content']);

        return $regulationInfo;
    }
}
