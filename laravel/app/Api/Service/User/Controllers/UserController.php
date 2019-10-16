<?php
/**
 * created by wyn
 * date: 2019/10/15 9:28
 */
namespace App\Api\Service\User\Controllers;

use Framework\BaseClass\Api\Controller;

class UserController extends Controller
{

    //用户设置
    public function userSet()
    {
        $phone = '1';
        $location = '2';
        $area = '3';
        $type = '4';
        $num = '5';

        $user = new \App\Eloquent\Zk\User;
        $setLog = new \App\Eloquent\Zk\SetLog;
        $userObj = $user->where('phone','=',$phone)->first();
        if ($userObj){
            //老用户
            $uid= $userObj->toArray()['id'];
            $data = [
                'uid'=>$uid,
                'location'=>$location,
                'area'=>$area,
                'type'=>$type,
                'num'=>$num,
                'created_at'=>time(),
                'updated_at'=>time()
            ];
            $bool = $setLog->insertData($data);
            if ($bool){
                return ['tip'=>'设置成功'];
            }
        }else{
            //新用户
            $user->insertOneData(['phone'=>$phone]);
        }
        return 123;
    }

}