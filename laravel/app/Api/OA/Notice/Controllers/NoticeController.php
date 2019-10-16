<?php
/**
 * Created by PhpStorm.
 * User: sxy
 * Date: 2017/12/13
 * Time: 11:02
 */

namespace App\Api\OA\Notice\Controllers;

use Framework\BaseClass\Api\Controller;
use Psy\Exception\ErrorException;
use App\Api\OA\Notice\Models\Notice;

class NoticeController extends Controller
{

    /**
     * @Author sxy
     * $params page 页数 page_size 条数 is_read 是否已读
     * 获取公告列表
     * @return array|mixed
     */
    public function noticeList()
    {
        $token = app('token')->checkToken();
        $user_id=$token['user_id'];
        $oa_company_id=$token['oa_company_id'];//todo 公司id
        $params = $this->getRequestParameters(['is_read','page', 'page_size']);

        $rules = [
            'is_read' => 'integer',
            'page' => 'integer|min:1',
            'page_size' => 'integer|min:1'
        ];
        $this->validateRequestParameters($params, $rules);

        $model = new Notice();
         $data = $model->getNoticeList( $params['is_read'],$params['page'], $params['page_size'],$token['oa_contacts_id']);
        return $this->pagingData($data['list'], $data['paging']);
    }






    /**
     * @Author sxy
     * $params id int 公告id
     * 获取公告详情
     * @return \Illuminate\Database\Eloquent\Model|null|void|static
     */
    public function noticeDetail()
    {
        $token = app('token')->checkToken();
        $user_id=$token['user_id'];
        $params = $this->getRequestParameters(['id']);
        $rules = [
            'id' => 'integer|min:1',
        ];
        $this->validateRequestParameters($params, $rules);

        $model = new Notice();
        try {
            $data = $model->getNoticeDetail($params['id'],$user_id);
        } catch (\DebugError $e) {
            return xThrow($e->getCode());
        }
        return $data;
    }


    /**
     * @Author sxy
     * $params id int 公告id
     * 删除公告
     * @return bool|null|void
     */
    public function noticeDel()
    {
        $token = app('token')->checkToken();
        $user_id=$token['user_id'];
        $params = $this->getRequestParameters(['id']);
        $rules = [
            'id' => 'integer|min:1',
        ];
        $this->validateRequestParameters($params, $rules);

        $model = new Notice();
        try {
            $data = $model->getNoticeDel($params['id'],$user_id);
        } catch (\DebugError $e) {
            return xThrow($e->getCode());
        }
        xAssert($data);
    }



}