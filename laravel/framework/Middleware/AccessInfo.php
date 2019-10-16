<?php

namespace Framework\Middleware;

use Closure;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class AccessInfo
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $log = new Logger('access');

        $log->pushHandler(
            new StreamHandler(
                storage_path('logs/access.log'),
                Logger::INFO
            )
        );

        $log->addInfo($request);

//        \Log::info('aaaaaaaaaaaaaaaaaaaaaaaa');

        return $next($request);
    }
}
