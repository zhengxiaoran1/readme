<?php
/**
 * Created by PhpStorm.
 * Author: Sojo
 * Date: 2017/2/27
 * Time: 17:44
 */

namespace Framework\Middleware;

use Closure;

class AdminTimeout
{
    private $prefix;

    public function __construct()
    {
        $this->prefix = request()->route()->getPrefix();
        if ($this->prefix[0] == '/') $this->prefix = substr($this->prefix, 1);
        $sub = strpos($this->prefix, '/');
        $this->prefix = ($sub > 0) ? substr($this->prefix, 0, $sub) : $this->prefix;
    }

    /**
     * 返回请求过滤器
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param  string|null $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = 'admin')
    {

        if (!\Auth::guard($guard)->check()) {
            if ($request->path() == $this->prefix || $request->path() == $this->prefix . '/logout') {
                return redirect($this->prefix . '/login');
            }
            if ($request->is($this->prefix . '/*') && !$request->is($this->prefix . '/login', $this->prefix . '/login-timeout')) {
                $json = [
                    'statusCode'   => 301,
                    'message'      => '登录超时',
                ];

                return response()->json($json);
            }
        }

        return $next($request);
    }
}