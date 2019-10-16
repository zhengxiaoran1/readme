<?php
/**
 * Created by PhpStorm.
 * User: LuLingFeng
 * Date: 2018/1/6
 * Time: 10:30
 */

namespace App\Api\OA\Report\Models;

use App\Eloquent\Oa\Contacts;
use Framework\BaseClass\Api\Model;

class Report extends Model
{
    const TYPE_DAY = 1;
    const TYPE_WEEK = 2;
    const TYPE_MONTH = 3;

    /**
     * 提交报告
     * @author LuLingFeng
     * @param $contactsId
     * @param $type
     * @param $content
     * @param $enclosureUrl
     * @param null $contacts
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function post($contactsId, $type, $content, $enclosureUrl, $contacts = null)
    {
        if (empty($contacts)) {
            $contacts = Contacts::find($contactsId);
            if (empty($contacts)) xThrow(ERR_PARAMETER, 'no valid contacts to be found');
        }
        $now = time();
        $time = 0;
        switch ($type) {
            case self::TYPE_DAY:
                $time = strtotime(date('Y-m-d', $now));
                break;
            case self::TYPE_WEEK:
                $dayOfWeek = date('N', $now);
                $day = date('d', $now) - $dayOfWeek + 1;
                $time = strtotime(date('Y-m', $now) . '-' . $day);
                break;
            case self::TYPE_MONTH:
                $time = strtotime(date('Y-m', $now) . '-01');
                break;
            default:
                xThrow(ERR_PARAMETER, 'using unsupported report type');
        }
        $report = \App\Eloquent\Oa\Report::firstOrNew([
            'oa_contacts_id' => $contacts['id'],
            'oa_department_id' => $contacts['oa_department_id'],
            'oa_company_id' => $contacts['oa_company_id'],
            'time' => $time,
            'type' => $type
        ]);
        $report->fill([
            'done' => $content['done'],
            'done_tag' => $content['done_tag'],
            'todo' => $content['todo'],
            'todo_tag' => $content['todo_tag'],
            'to_be_confirmed' => $content['to_be_confirmed'],
            'to_be_confirmed_tag' => $content['to_be_confirmed_tag'],
            'todo_next' => $content['todo_next'],
            'todo_next_tag' => $content['todo_next_tag'],
            'enclosure_url' => $enclosureUrl,
        ]);
        xAssert($report->save());
        return $report;
    }

    /**
     * 获取日志模块
     * @return array
     */
    public function getPermission()
    {
        $data=[
                [
                    "id"=>1,
                    "name"=> "日报",
                    "url"=>env('APP_URL') . "/assets/api/images/permission-img/icon-rb@2x.png"
                ],
                [
                    "id"=> 2,
                    "name"=> "周报",
                    "url"=>env('APP_URL') . "/assets/api/images/permission-img/icon-zb@2x.png"
                ],
                [
                    "id"=> 3,
                    "name"=> "月报",
                    "url"=>env('APP_URL') . "/assets/api/images/permission-img/icon-yb@2x.png"
                ]
            ];
        return $data;
    }

}