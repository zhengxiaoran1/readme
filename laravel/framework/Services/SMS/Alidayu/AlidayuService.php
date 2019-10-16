<?php
/**
 * Created by PhpStorm.
 * Author Sojo
 * Date: 2017/5/26
 * Time: 17:36
 */
namespace Framework\Services\SMS\Alidayu;

require_once "SDK/TopSdk.php";
require_once "SDK/top/TopClient.php";
require_once "SDK/top/request/AlibabaAliqinFcSmsNumSendRequest.php";

class AlidayuService
{
    /** @var \TopClient $client */
    private $client;

    /** @var string $replyFormat 响应格式 */
    private $replyFormat = 'json';

    /** @var array $scenarios 业务场景，根据项目需求，在配置荐中录入 */
    private $scenarios;

    /**
     * 实例化TopClient并进行参数配置
     * @author Sojo
     * AlidayuService constructor.
     */
    public function __construct()
    {
        $this->client = new \TopClient;
        $this->client->format = $this->replyFormat;
        $this->client->appkey = config('alidayu.appKey') ?: xThrow(ERR_SERVICE_CONFIGURATION_FILE_PARAMETERS);
        $this->client->secretKey = config('alidayu.appSecret') ?: xThrow(ERR_SERVICE_CONFIGURATION_FILE_PARAMETERS);
        $this->scenarios = config('alidayu.sms.scenarios') ?: xThrow(ERR_SERVICE_CONFIGURATION_FILE_PARAMETERS);
    }

    /**
     * 发送短信
     * @author Sojo
     * @param string $tel 短信接收号码。支持单个或多个手机号码，传入号码为11位手机号码，不能加0或+86。
     *                    群发短信需传入多个号码，以英文逗号分隔，一次调用最多传入200个号码。
     *                    示例：18600000000,13911111111,13322222222
     * @param string $scenario 使用场景。场景在配置文件中定义
     * @param array|null $smsParams 短信模板变量，数组的键为短信模板中的变量名，值为要传入的值
     */
    public function sendSMS($tel, $scenario, $smsParams = null)
    {
        $signName = $this->scenarios[$scenario]['signName'];
        $templateCode = $this->scenarios[$scenario]['templateCode'];

        $request = new \AlibabaAliqinFcSmsNumSendRequest;
        $request->setSmsType("normal");
        $request->setSmsFreeSignName($signName);
        $request->setRecNum($tel);
        $request->setSmsTemplateCode($templateCode);
        if (!empty($smsParams)) $request->setSmsParam(json_encode($smsParams));
        $resp = $this->client->execute($request);

        //错误机制
        if (isset($resp->code)) {
            $errorSubCode = isset($resp->sub_code) ? $resp->sub_code : null;
            if ($resp->code === 15 && $errorSubCode === 'isv.BUSINESS_LIMIT_CONTROL') {
                xThrow(ERR_SERVICE_SMS_ALIDAYU,'业务限制错误');
            } else {
                xThrow(ERR_SERVICE_SMS_ALIDAYU,'发送错误');
            }
        }
    }
}