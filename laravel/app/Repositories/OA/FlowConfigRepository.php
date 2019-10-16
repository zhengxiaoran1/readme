<?php
/**
 * Created by PhpStorm.
 * Author: Sojo
 * Date: 2017/2/25
 * Time: 16:17
 */

namespace App\Repositories\OA;

use App\Eloquent\Oa\FlowConfig;
use Framework\BaseClass\Repositories\Repository;

class FlowConfigRepository extends Repository
{
    public function model()
    {
        return FlowConfig::class;
    }
}