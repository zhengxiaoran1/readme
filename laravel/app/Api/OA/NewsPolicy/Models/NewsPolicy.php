<?php
/**
 * Created by PhpStorm.
 * User: sxy
 * Date: 2017/12/13
 * Time: 11:03
 */

namespace App\Api\OA\NewsPolicy\Models;

use App\Api\Foundation\Log\Models\Log;
use Framework\BaseClass\Api\Model;
use App\Eloquent\Oa\ImageText as EloquentImageText;

class NewsPolicy extends Model
{
    /**
     * @Author sxy
     * 获取首页新闻列表
     * @param $type
     * @param $page
     * @param $page_size
     * @return mixed
     */
    public function getHomeNewsPolicyList($type,$page, $page_size,$oa_company_id)
    {
        $list = EloquentImageText::select('id', 'title', 'main_image_url', 'created_at')
            ->where('type','=',$type)->orderBy('created_at', 'desc');
        if($oa_company_id==0){
            $list->where([['oa_company_id','=',0],['type', '=', $type]]);
        }else{
            $list->where([['oa_company_id','=',$oa_company_id],['type', '=', $type]])->orWhere([['oa_company_id','=',0],['type', '=', $type]]);
        }
        $count = $list->count();
        $data['list'] = $list->forPage($page, $page_size)->get();

        foreach ($data['list'] as $v) {

            $v['release_time'] = date('Y-m-d', $v->created_at->timestamp);
            $v['main_image_url'] = env('APP_URL') . $v['main_image_url'];
            unset($v['created_at']);
        }
        $data['paging'] = [
            'total' => $count,
            'page' => $page,
            'pageSize' => $page_size,
        ];

        return $data;
    }


    /**
     * @Author sxy
     * 获取新闻列表
     * @param $type
     * @param $page
     * @param $page_size
     * @return mixed
     */
    public function getNewsPolicyList($type, $page, $page_size,$oa_company_id)
    {

        $list = EloquentImageText::select('id', 'title', 'main_image_url')
            ->where('type', '=', $type)->orderBy('created_at', 'desc');
        if($oa_company_id==0){
            $list->where([['oa_company_id','=',0],['type', '=', $type]]);
        }else{
            $list->where([['oa_company_id','=',$oa_company_id],['type', '=', $type]])->orWhere([['oa_company_id','=',0],['type', '=', $type]]);
        }
        $count = $list->count();
        $data['list'] = $list->forPage($page, $page_size)->get();
        foreach ($data['list'] as $v) {
            $v['main_image_url'] = env('APP_URL') . $v['main_image_url'];
        }
        $data['paging'] = [
            'total' => $count,
            'page' => $page,
            'pageSize' => $page_size,
        ];

        return $data;
    }


    /**
     * @Author sxy
     * 获取新闻详情
     * @param $id
     * @param $userId
     * @return \Illuminate\Database\Eloquent\Model|null|static
     */
    public function getNewsPolicyDetail($id, $userId = 0)
    {
        $data = EloquentImageText::select('title', 'content', 'main_image_url')->where('id','=',$id)->first();
        if (is_null($data)) xThrow(ERR_API_NEWS_POLICY_NOT_EXIST);
        $data->main_image_url = env('APP_URL') . $data->main_image_url;
        /*2018-2-26 author：wenwenbin 添加阅读记录 ---begin--- */
        $log = new Log();
        $log->addReadingLog($userId, 'image_text', $id);
        /*2018-2-26 author：wenwenbin 添加阅读记录 ---end--- */
        return $data;
    }
}