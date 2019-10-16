<?php
/**
 * Created by PhpStorm.
 * Author: Sojo
 * Date: 2017/2/17
 * Time: 16:30
 */
namespace Framework\BaseClass\Http;

use Framework\BaseClass\Controller as BaseController;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * 视图渲染
     * @author Sojo
     * @param null $view
     * @param array $data
     * @param array $mergeData
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    protected function view($view = null, $data = [], $mergeData = [])
    {
        if (!empty($view)) $view = $this->getRoutePrefix() . '.' . $view;

        return view($view, $data, $mergeData);
    }

    /**
     * 获取路由前缀
     * @author Sojo
     * @param string $appPrefix 应用名称，大驼峰格式，App应用
     * @return string
     */
    private function getRoutePrefix($appPrefix = 'App')
    {
        $route = request()->route();
        $actionName = $route->getActionName();
        $actionName = str_replace('\Controllers\\', '\\', $actionName);
        $actionName = str_replace('App\Http\\', 'App\\', $actionName);
        $startSub = strlen($appPrefix) + 1;
        $length = strpos($actionName, 'Controller@') - $startSub;
        $actionName = substr($actionName, $startSub, $length);
        $viewPrefix = explode('\\', $actionName);

        $capital = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        foreach ($viewPrefix as $key => $val) {
            $val = lcfirst($val);
            $len = strlen($val);
            $viewPrefix[$key] = '';
            for ($i = 0; $i < $len; $i++) {
                if (is_int(strpos($capital, $val[$i]))) {
                    $viewPrefix[$key] .= '-' . strtolower($val[$i]);
                } else {
                    $viewPrefix[$key] .= $val[$i];
                }
            }
        }

        return implode('.', $viewPrefix);
    }
}
