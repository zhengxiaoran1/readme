<?php
/**
 * Created by PhpStorm.
 * Author: kaodou
 * Date: 2017/10/17
 * Time: 14:58
 */
namespace App\Http\Admin\Routes;

use Illuminate\Contracts\Routing\Registrar;

class ProgramRoute
{
    public function map(Registrar $router)
    {
        $router->group(['namespace' => 'Program\Controllers', 'prefix' => 'program'], function ($router) {

            $router->any('set-jurisdiction', 'IndexController@setProgramJurisdiction');
            $router->any('set_material_formula', 'IndexController@setMaterialFormula');//设置材料使用公式;
         
            $router->any('formula', 'IndexController@formula');//设置材料使用公式;
        });
    }
}