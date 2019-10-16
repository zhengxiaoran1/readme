<?php
/**
 * Created by PhpStorm.
 * User: sxy
 * Date: 2017/12/13
 * Time: 11:03
 */

namespace App\Api\OA\Notice\Models;

use Framework\BaseClass\Api\Model;
use App\Eloquent\Oa\Notice as EloquentNotice;
use App\Eloquent\Oa\NoticeContacts as EloquentNoticeContacts;
use App\Eloquent\Oa\Contacts;
class Notice extends Model
{
    /**
     * @Author sxy
     * 获取公告列表
     * @param $type
     * @param $page
     * @param $page_size
     * @return mixed
     */
    public function getNoticeList($read,$page, $page_size,$oa_contacts_id)
    {
        $list = EloquentNoticeContacts::where('is_read','=',$read) ->where('oa_contacts_id',$oa_contacts_id)->get();

        foreach($list as $v){
                $oa_notice_id[] = $v['oa_notice_id'];
        }



        if(isset($oa_notice_id)) {
            $res = EloquentNotice::select('id', 'title', 'release_time', 'main_image_url')->where('is_check', '=', 1)->whereIn('id', $oa_notice_id);

            $count = $list->count();
            $data['list'] = $res->forPage($page, $page_size)->get();
            foreach ($data['list'] as $v) {
                $v['release_time'] = date('Y-m-d', $v->release_time);
                $v['main_image_url'] = env('APP_URL') . $v['main_image_url'];
                $v['value'] = env('APP_URL') . '/mobile/notice-detail?id='.$v['id'];
            }
        }else{
            $count = 1;
            $data['list'] =[];
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
     * 获取公告详情
     * @param $id
     * @return \Illuminate\Database\Eloquent\Model|null|static
     */
    public function getNoticeDetail($id,$oa_contacts_id)
    {
        $data = EloquentNotice::select('title', 'content','main_image_url')->where('id','=',$id)->first();

        $update = EloquentNoticeContacts::where('oa_contacts_id','=',$oa_contacts_id)->where('oa_notice_id','=',$id)->update(['is_read' => 1]);
        if (is_null($data)||is_null($update)) xThrow(ERR_API_NOTICE_NOT_EXIST);
        $data['main_image_url'] = env('APP_URL') . $data['main_image_url'];


        return $data;
    }

    /**
     * @Author sxy
     * 删除公告
     * @param $id
     * @param $user_id
     * @return bool|null
     */
    public function getNoticeDel($id,$user_id)
    {
        $contacts_id=Contacts::select('id')->where('user_id','=',$user_id)->first();
        $data = EloquentNoticeContacts::where('oa_contacts_id','=',$contacts_id['id'])->where('oa_notice_id','=',$id)->delete();

        if ($data==0) xThrow(ERR_API_NOTICE_NOT_EXIST);
        return true;
    }



}