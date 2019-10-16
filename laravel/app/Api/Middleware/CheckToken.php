<?php
/**
 * created by zzy
 * date: 2017/10/24 9:28
 */

namespace App\Api\Middleware;

use Closure;
use App\Eloquent\Ygt\UserToken;
use App\Engine\Func;

class CheckToken
{

    public function handle($request, Closure $next)
    {
//        header 值
//        userid			默认0
//        token			默认''
//        imei			手机唯一标识
//        appv			app的版本号
//        platform		android/ios
//        platformv		android/ios的版本号
        $userId        = Func::getHeaderValueByName('userid');
        $token          = Func::getHeaderValueByName('token');
        $imei           = Func::getHeaderValueByName('imei');
        $tokenResult   = UserToken::checkToken( $userId, $imei, $token );
        if( !$tokenResult ){
            return redirect('api/service/user/token');
        }
        return $next($request);
    }

}