<?php

namespace Framework\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \Framework\Middleware\TrimStrings::class,
        \Framework\Middleware\AccessInfo::class,
//        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \Framework\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Framework\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api'=>  [
//            'throttle:60,1',
//            'bindings',
            \App\Api\Middleware\CheckWorkTime::class,

        ],

        'termination'=>[
            \App\Api\Middleware\Termination::class,
        ],
        'withdraw'=>[
            \App\Api\Middleware\Withdraw::class,
        ],
        'halt'=>[
            \App\Api\Middleware\Halt::class,
        ],
        'abnormal'=>[
            \App\Api\Middleware\Abnormal::class,
        ],

        'admin' => [
            \Framework\Middleware\AdminTimeout::class,
        ],
        'mobile' => [
            \Framework\Middleware\MobileTimeout::class,
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \Framework\Middleware\RedirectIfAuthenticated::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'apichecktoken'=>\App\Api\Middleware\CheckToken::class,
        'checkworktime'=>\Framework\Middleware\VerifyCsrfToken::class,
        'termination'=>\App\Api\Middleware\Termination::class,
        'halt'=> \App\Api\Middleware\Halt::class,
        'withdraw'=>\App\Api\Middleware\Withdraw::class,
        'abnormal'=>\App\Api\Middleware\Abnormal::class,

    ];
}
