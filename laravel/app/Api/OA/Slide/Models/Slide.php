<?php
/**
 * Created by PhpStorm.
 * User: wenwenbin
 * Date: 2018/2/6
 * Time: 18:01
 */
namespace App\Api\OA\Slide\Models;

use App\Eloquent\App\SlideCategory;
use App\Eloquent\App\Slide as EloquentSlide;
use App\Eloquent\Oa\Company;
use Framework\BaseClass\Api\Model;

class Slide extends Model
{
    /**
     * 获取oa轮播图
     * @author wenwenbin
     * @param int $oa_company_id 公司id
     * @param int $category_id 类别id
     * @return mixed
     */
    public function slideList($oa_company_id, $category_id)
    {
        $company = Company::find($oa_company_id);
        if (!$company) xThrow(ERR_OA_FUNCTION_NOT_QUALIFIED);
        $category['company_name'] = $company->name;
        $baseUrl = env('APP_URL');
        $slideList = EloquentSlide::where([['app_slide_category_id', $category_id], ['oa_company_id', $oa_company_id],
            ['start_time', '<=', time()], ['end_time', '>=', time()]])
            ->orderBy('sort', 'desc')
            ->get(['id', 'name as title', 'image_url', 'android_link', 'ios_link' , 'app_slide_category_id']);
        foreach ($slideList as $slide) {
            $slide['type'] = 1; //app用来判断是否自己传头部
            $slide['start_time'] = date('Y-m-d H:i:s', $slide['start_time']);
            $slide['img'] = empty($slide['image_url']) ? '' : $baseUrl . $slide['image_url'];
            if ($_SERVER['HTTP_PLATFORM'] == 'android' && $slide['android_link']) {
                $slide['value'] = $baseUrl . $slide['android_link'];
            } elseif ($_SERVER['HTTP_PLATFORM'] == 'ios' && $slide['ios_link']) {
                $slide['value'] = $baseUrl . $slide['ios_link'];
            } else {
                xThrow(ERR_PARAMETER);
            }
            unset($slide['android_link'], $slide['ios_link'], $slide['image_url']);
        }
        $categoryInfo = SlideCategory::find($category_id);
        if (is_null($categoryInfo)) {
            xThrow(ERR_API_SLIDE_CATEGORY_NOT_EXIST);
        } else {
            $category['name'] = $categoryInfo->name;
            $category['icon'] = $baseUrl . $categoryInfo->icon_url;
        }
        $category['img_list'] = $slideList;
        return $category;
    }
}