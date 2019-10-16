<?php
/**
 * Created by PhpStorm.
 * Author: Sojo
 * Date: 2017/2/17
 * Time: 16:30
 */
namespace Framework\BaseClass\Api;

use Framework\BaseClass\Controller as BaseController;
use Dingo\Api\Routing\Helpers;

class Controller extends BaseController
{
    use Helpers;

    /**
     * 返回需要分页展示的数据
     * @author Sojo
     * @param mixed $data 返回数据
     * @param int|array $pagingData 分页数据
     * @return array
     */
    protected function pagingData($data, $pagingData = [])
    {

        $paging = [
            'page'      => isset($pagingData['page']) ? $pagingData['page'] : 0,
            'page_size' => isset($pagingData['pageSize']) ? $pagingData['pageSize'] : 0,
            'total'     => isset($pagingData['total']) ? $pagingData['total'] : 0,
            'total_page'=> ceil($pagingData['total']/$pagingData['pageSize']),
            'keyword'   => isset($pagingData['keyword']) ? $pagingData['keyword'] : ''
        ];
        if (is_int($pagingData)) $paging['total'] = $pagingData;

        return ['__data__' => $data, '__paging__' => $paging];
    }
}