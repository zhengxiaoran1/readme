<?php

namespace Framework\Providers;

use Illuminate\Routing\Router;
use Dingo\Api\Routing\Router as ApiRouter;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * 路由分组配置信息
     * @author Sojo
     * @var array
     */
    private $httpGroups;
    private $apiGroups;

    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $webNamespace = 'App\Http';
    protected $apiNamespace = 'App\Api';

    /**
     * api 版本
     *
     * @author Sojo
     * @var string
     */
    private $apiVersion;

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $groupInfo = require config_path('route-group.php');
        $this->httpGroups = $groupInfo['http'];
        $this->apiGroups = $groupInfo['api'];
        $this->apiVersion = env('API_VERSION', 'v1');

        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @param  \Illuminate\Routing\Router $router
     * @param  \Dingo\Api\Routing\Router $apiRouter
     * @return void
     */
    public function map(Router $router, ApiRouter $apiRouter)
    {
        $this->mapApiRoutes($apiRouter);

        $this->mapWebRoutes($router);
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @param  \Illuminate\Routing\Router $router
     * @return void
     */
    protected function mapWebRoutes(Router $router)
    {
        /**
         * 重构web路由导入，便于团队协作开发
         *
         * @author Sojo
         */
        $router->group(['namespace' => $this->webNamespace, 'middleware' => 'web'], function ($router) {
            $router->group([], base_path('routes/web.php'));
            foreach ($this->httpGroups as $groupName => $groupConfig) {
                if (empty($groupConfig)) continue;
                $router->group($groupConfig, function ($router) use ($groupConfig) {
                    $namespace = $this->webNamespace . '\\' . $groupConfig['namespace'] . '\Routes';
                    $routeFiles = glob(app_path('Http/' . $groupConfig['namespace'] . '/Routes') . '/*Route.php');
                    $this->registrarRoute($namespace, $routeFiles, $router);
                });
            }
        });
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @param  \Dingo\Api\Routing\Router $apiRouter
     * @return void
     */
    protected function mapApiRoutes(ApiRouter $apiRouter)
    {
        /**
         * 重构api路由导入，便于团队协作开发
         *
         * @author Sojo
         */
        $apiRouter->version($this->apiVersion, function ($apiRouter) {
            $apiRouter->group(['namespace' => $this->apiNamespace], function ($apiRouter) {
                foreach ($this->apiGroups as $groupName => $groupConfig) {
                    if (empty($groupConfig)) continue;
                    $apiRouter->group($groupConfig, function ($apiRouter) use ($groupConfig) {
                        $namespace = $this->apiNamespace . '\\' . $groupConfig['namespace'] . '\Routes';
                        $routeFiles = glob(app_path('Api/' . $groupConfig['namespace'] . '/Routes') . '/*Route.php');
                        $this->registrarRoute($namespace, $routeFiles, $apiRouter);
                    });
                }
            });
        });
    }

    /**
     * 注册路由
     * @author Sojo
     * @param string $namespace
     * @param array $routeFiles
     * @param \Illuminate\Routing\Router $routerInstance
     *        \Dingo\Api\Routing\Router $routerInstance
     */
    private function registrarRoute($namespace, $routeFiles, $routerInstance)
    {
        if ($routerInstance instanceof Router || $routerInstance instanceof ApiRouter) {
            foreach ($routeFiles as $file) {
                $this->app->make($namespace . '\\' . basename($file, '.php'))->map($routerInstance);
            }
        } else {
            xThrow(ERR_FRAMEWORK);
        }
    }
}
