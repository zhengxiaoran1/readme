<?php
/**
 * Created by PhpStorm.
 * Author: wenwenbin
 * Date: 2018/3/2
 * Time: 16:17
 */

namespace App\Repositories\OA;

use App\Eloquent\Oa\WorkHistory;
use Framework\BaseClass\Repositories\Repository;

class WorkHistoryRepository extends Repository
{
    public function model()
    {
        return WorkHistory::class;
    }
}