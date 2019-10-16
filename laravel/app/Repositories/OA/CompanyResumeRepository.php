<?php
/**
 * Created by PhpStorm.
 * Author: wenwenbin
 * Date: 2018/3/2
 * Time: 16:17
 */

namespace App\Repositories\OA;

use App\Eloquent\Oa\CompanyResume;
use Framework\BaseClass\Repositories\Repository;

class CompanyResumeRepository extends Repository
{
    public function model()
    {
        return CompanyResume::class;
    }

    public function checkExists($condition, $columns = ['id'])
    {
        return $this->model->where($condition)->first($columns);
    }
}