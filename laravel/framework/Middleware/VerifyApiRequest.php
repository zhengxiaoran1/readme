<?php
/**
 * Created by PhpStorm.
 * Author: Sojo
 * Date: 2017/2/27
 * Time: 17:44
 */

namespace Framework\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyApiRequest
{
    private $timestamp;
    private $sign;
    private $token;
    private $imei;

    public function __construct(Request $request)
    {
        $this->token = $request->header('token');
        $this->imei = $request->header('imei', '');

        $this->timestamp = $request->get('timestamp');
        $this->sign = $request->get('sign');
    }

    /**
     * 返回请求过滤器
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        return $next($request);
    }
}