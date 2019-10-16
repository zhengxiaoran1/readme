<?php
/**
 * created by zzy
 * date: 2017/10/24 9:28
 */

namespace App\Api\Middleware;

use Closure;
use App\Eloquent\Ygt\UserToken;
use App\Engine\Func;

class CheckWorkTime
{

    public function handle($request, Closure $next)
    {

        $userId        = Func::getHeaderValueByName('userid');
        $token          = Func::getHeaderValueByName('token');
        $imei           = Func::getHeaderValueByName('imei');

        //过滤登陆界面
        $userId = \App\Engine\Func::getHeaderValueByName('userid');
        if($userId){
            $userInfo = \App\Eloquent\Ygt\DepartmentUser::getCurrentInfo($userId)->toArray();

            $companyId          = $userInfo['company_id'];
            if( $companyId )
            {
                $departUser         = \App\Eloquent\Ygt\DepartmentUser::getCurrentInfo( $userId );
                if( $departUser )
                {
                    $privilegeId        = $departUser->privilege_id;
                    $where              = ['id'=>$privilegeId];
                    $privilege          = \App\Eloquent\Ygt\Privilege::getInfo($where);
                    if($privilege){
                        $nowTime        = $_SERVER['REQUEST_TIME'];
                        $nowDay         = date('Y-m-d',$nowTime);
                        $startTimeH     = $privilege->login_start_time;
                        $endTimeH       = $privilege->login_end_time;
                        $startTimeDate  = $nowDay.' '.$startTimeH;
                        $endTimeDate    = $nowDay.' '.$endTimeH;
                        $startTime      = strtotime($startTimeDate);
                        $endTime        = strtotime($endTimeDate);
                        if($startTimeH!=0 && $startTime>$nowTime)
                        {
                            return response()->json(['message' => '未到上班时间.'], 401);
                        }
                        if($endTimeH!=0 && $endTime<$nowTime)
                        {
                            return response()->json(['message' => '已经下班了.'], 401);
                        }
                    }
                }
            }
        }

        return $next($request);





    }

}