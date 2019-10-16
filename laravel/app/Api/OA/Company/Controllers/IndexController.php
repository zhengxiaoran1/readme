<?php
/**
 * Created by PhpStorm.
 * User: sojo
 * Date: 2018/3/3
 * Time: 18:50
 */
namespace App\Api\OA\Company\Controllers;

use App\Api\OA\Company\Models\Regulation;
use Framework\BaseClass\Api\Controller;

class IndexController extends Controller
{
    public function regimeList()
    {
        $token = app('token')->checkToken();
        $params = $this->getRequestParameters(['page', 'page_size']);
        $regulation = new Regulation();
        return $regulation->getPagingList($token['oa_company_id'], $params['page'], $params['page_size']);
    }

    public function regimeInfo()
    {
        app('token')->checkToken();
        $params = $this->getRequestParameters(['regime_id']);
        $regulation = new Regulation();
        return $regulation->getDetails($params['regime_id']);
    }
}