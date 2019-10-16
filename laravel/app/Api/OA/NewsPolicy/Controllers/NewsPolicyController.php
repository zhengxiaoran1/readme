<?php
/**
 * Created by PhpStorm.
 * User: sxy
 * Date: 2017/12/13
 * Time: 11:02
 */

namespace App\Api\OA\NewsPolicy\Controllers;

use Framework\BaseClass\Api\Controller;
use App\Api\OA\NewsPolicy\Models\NewsPolicy;

class NewsPolicyController extends Controller
{
    /**
     * @Author sxy
     * $params page 页数 page_size 条数
     * 获取首页政策列表
     * @return array|mixed
     */
    public function homeNewsPolicyList()
    {
        $token = app('token')->checkToken();
        $oa_company_id = $token['oa_company_id'];

        $type = 2;//todo  2：政策
        $params = $this->getRequestParameters(['page', 'page_size']);
        $rules = [
            'page'      => 'integer|min:1',
            'page_size' => 'integer|min:1'
        ];
        $this->validateRequestParameters($params, $rules);

        $model = new NewsPolicy();
        $data = $model->getHomeNewsPolicyList($type, $params['page'], $params['page_size'], $oa_company_id);
        return $this->pagingData($data['list'], $data['paging']);
    }


    /**
     * @Author sxy
     * $params  page 页数 page_size 条数
     * 获取新闻窗口列表
     * @return array|mixed
     */

    public function newsPolicyList()
    {
        $token = app('token')->checkToken();
        $oa_company_id = $token['oa_company_id'];//todo 公司id
        $type = 1;//todo  1：新鲜资讯
        $params = $this->getRequestParameters(['page', 'page_size']);
        $rules = [
            'page'      => 'integer|min:1',
            'page_size' => 'integer|min:1'
        ];
        $this->validateRequestParameters($params, $rules);

        $model = new NewsPolicy();
        $data = $model->getNewsPolicyList($type, $params['page'], $params['page_size'], $oa_company_id);
        return $this->pagingData($data['list'], $data['paging']);
    }


    /**
     * @Author sxy
     * $params id int 新闻政策id
     * 获取新闻详情
     * @return \Illuminate\Database\Eloquent\Model|null|void|static
     */
    public function newsPolicyDetail()
    {
        try {
            $token = app('token')->checkToken();
        } catch (\DebugError $e) {
            $token['user_id'] = 0;
        }
        $params = $this->getRequestParameters(['id']);
        $rules = [
            'id' => 'integer|min:1',
        ];
        $this->validateRequestParameters($params, $rules);

        $model = new NewsPolicy();
        try {
            $data = $model->getNewsPolicyDetail($params['id'], $token['user_id']);
        } catch (\DebugError $e) {
            return xThrow($e->getCode());
        }
        return $data;
    }


}