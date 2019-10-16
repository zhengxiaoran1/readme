<?php
/**
 * Created by PhpStorm.
 * User: wenwenbin
 * Date: 2018/2/6
 * Time: 18:01
 */
namespace App\Api\OA\Slide\Controllers;

use App\Api\OA\Slide\Models\Slide;
use Framework\BaseClass\Api\Controller;

class SlideController extends Controller
{
    /**
     * 获取oa轮播图
     * @author wenwebin
     * @param oa_company_id int 公司id
     * @return array
     */
    public function slideList()
    {
        $token = app('token')->checkToken();
        $slide = new Slide();
        return $slide->slideList($token['oa_company_id'], 2);
    }
}