<?php
/**
 * Created by PhpStorm.
 * Author: Sojo
 * Date: 2017/2/25
 * Time: 16:17
 */

namespace App\Repositories\OA;

use App\Eloquent\Oa\FlowConfigProcess;
use Framework\BaseClass\Repositories\Repository;

class FlowConfigProcessRepository extends Repository
{
    public function model()
    {
        return FlowConfigProcess::class;
    }

    public function getEndProcess($flowConfigId)
    {
        $processList = $this->getList([
            'oa_flow_config_id' => $flowConfigId,
            'display' => 1
        ], [], ['id', 'pid', 'operator_id']);

        $ids = $processList->pluck('id')->all();
        $pids = $processList->pluck('pid')->all();

        $endProcessIds = [];
        foreach ($ids as $id) {
            if (!in_array($id, $pids)) $endProcessIds[] = $id;
        }

        return $processList->whereIn('id', $endProcessIds)->values();
    }
}