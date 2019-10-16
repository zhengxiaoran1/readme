<?php
/**
 * created by zzy
 * date: 2018/1/11 10:43
 */

namespace App\Http\Admin\Routes;

use Illuminate\Contracts\Routing\Registrar;

class ExportRoute
{
    public function map(Registrar $router)
    {
        $router->group(['namespace' => 'Export\Controllers','prefix' => 'export'], function ($router) {
            //工序导出
            $router->any('process', 'ProcessController@index');
            $router->any('process-field', 'ProcessFieldController@index');
        });
    }
}
