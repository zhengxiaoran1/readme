<?php
/**
 * Created by PhpStorm.
 * Author: Sojo
 * Date: 2017/2/25
 * Time: 16:17
 */

namespace App\Repositories\Foundation\Website;

use App\Eloquent\Oa\Flow\Field;
use Framework\BaseClass\Repositories\Repository;

class FlowFieldRepository extends Repository
{
    public function model()
    {
        return Field::class;
    }
}