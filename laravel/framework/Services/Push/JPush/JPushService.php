<?php
/**
 * Created by PhpStorm.
 * User: Sojo
 * Date: 2017/7/14
 * Time: 12:32
 */
namespace Framework\Services\Push\JPush;

use JPush\Client as JPush;

class JPushService
{
    private $client;
    private $platform = ['all', 'android', 'ios', 'winphone'];

    /**
     * 实例化JPush
     * @author Sojo
     * JPushService constructor.
     */
    public function __construct()
    {
        $appKey = config('jpush.appKey') ?: xThrow(ERR_SERVICE_CONFIGURATION_FILE_PARAMETERS);
        $masterSecret = config('jpush.masterSecret') ?: xThrow(ERR_SERVICE_CONFIGURATION_FILE_PARAMETERS);
        $this->client = new JPush($appKey, $masterSecret);
    }

    public function send( $mssage, $type=0 )
    {
        try {
            $jpush          = $this->client->push();
            switch ($type)
            {
                case 1:
                    //android推送
                    $jpush      = $jpush->setPlatform('android')->addAllAudience();
                    break;
                case 2:
                    //ios推送
                    $jpush      = $jpush->setPlatform('ios')->addAllAudience();
                    break;
                case 3:
                    //winphone推送
                    $jpush      = $jpush->setPlatform('winphone')->addAllAudience();
                    break;
                default:
                    //全部推送
                    $jpush      = $jpush->setPlatform('all')->addAllAudience();
            }
            $result         = $jpush->setNotificationAlert($mssage)->options(array(
                'apns_production' => true,
            ))->send();
            return $result;
        } catch (\Exception $e) {
            //
        }
    }
    //$type=1 有内容 $type=2 角标处理没内容
    public function sendByMobile($mobile,$message,$iosArr=[],$androidArr=[],$type=1)
    {
        $mobileArr          = [ $mobile ];
        if( is_array( $mobile ) )
        {
            $mobileArr      = $mobile;
        }
        try {
            $obj            = $this->client->push()
                ->setPlatform('all')
                ->addAlias($mobileArr)
                ->setNotificationAlert($message)
                ->options(array('apns_production' => true,));
            if(!empty($iosArr))
            {
                $message    = $type==2 ? '' : $message;
                $obj        = $obj->iosNotification($message,$iosArr);
            }
            if(!empty($androidArr))
            {
                $message    = $type==2 ? '' : $message;
                $obj        = $obj->androidNotification($message,$androidArr);
            }
            $result         = $obj->send();
            return $result;
        } catch (\Exception $e) {
            //
        }
    }
}