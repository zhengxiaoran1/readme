<?php
/**
 * Created by PhpStorm.
 * Author: kaodou
 * Date: 2017/12/19
 * Time: 14:20
 */

namespace App\Http\Admin\Routes;

use Illuminate\Contracts\Routing\Registrar;

class QrcodeRoute
{
    public function map(Registrar $router)
    {
        $router->group(['namespace' => 'Qrcode\Controllers', 'prefix' => 'qrcode'], function ($router) {
            $router->any('png/{string}', function ($string) {
                $pngData = \QrCode::format('png')->size(100)->generate($string);
                return response($pngData, 200, [
                    'Content-Type' => 'image/png',
                ]);
            });


        });
    }
}