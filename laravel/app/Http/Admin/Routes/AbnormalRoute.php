<?php
/**
 * created by zzy
 * date: 2017/10/24 9:28
 */
namespace App\Http\Admin\Routes;

use Illuminate\Contracts\Routing\Registrar;

class AbnormalRoute
{
    public function map(Registrar $router)
    {
        $router->group(['namespace' => 'Abnormal\Controllers', 'prefix'=>'abnormal'], function ($router) {

            $router->any('index', 'AbnormalController@index');
            $router->any('setfield', 'AbnormalController@setField');
            $router->any('getAbnormalList', 'AbnormalController@getAbnormalList');
            $router->any('setAbnormalFormula', 'AbnormalController@setAbnormalFormula');
            $router->any('addAbnormal', 'AbnormalController@addAbnormal');
            $router->any('saveAbnormal', 'AbnormalController@saveAbnormal');
            $router->any('delAbnormal', 'AbnormalController@delAbnormal');
            $router->any('setAbnormalDepartment', 'AbnormalController@setAbnormalDepartment');
            $router->any('getMaterialList', 'AbnormalController@getMaterialList');
            $router->any('getMaterialTypeList', 'AbnormalController@getMaterialTypeList');//异常字段列表 wei 20190905
            $router->any('addAbnormalField', 'AbnormalController@addAbnormalField');//添加异常字段 wei 20190905
            $router->any('bindMaterial', 'AbnormalController@bindMaterial');//绑定材料 wei 20190905
            $router->any('categoryList', 'AbnormalController@categoryList');//材料列表 wei 20190905
            $router->any('saveAbnormalField', 'AbnormalController@saveAbnormalField');//更新字段 wei 20190905
            $router->any('delAbnormalField', 'AbnormalController@delAbnormalField');//删除字段 wei 20190905
            $router->any('field-edit', 'AbnormalController@fieldEdit');//删除字段 wei 20190905

        });
    }
}