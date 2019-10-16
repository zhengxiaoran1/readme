<?php
/**
 * Created by PhpStorm.
 * User: jinhangtao
 * Date: 2017/11/25
 * Time: 17:22
 */

namespace App\Engine;

use App\Eloquent\Zk\ProcessOrdertype as ProgramModel;
use App\Eloquent\Zk\Privilege;

class Program
{
    /**
     * @param $company_id
     * @param $programId
     * @return static
     * 获取有权限的角色
     */
    public static function getJurisdiction($company_id, $programId){
        $programModel = new ProgramModel();
        $where = ['id'=>$programId];
        $programInfo = $programModel->getOneData($where);
        $jurisdictions = explode(',',$programInfo->role_jurisdiction);
        //权限
        $privilege         = collect(Privilege::getList( ['company_id'=>$company_id], 'id,title' )->toArray());
        return $privilege->reject(function ($item, $key) use($jurisdictions) {
            return in_array($item['id'],$jurisdictions);
        });
    }

}