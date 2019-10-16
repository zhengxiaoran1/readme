<?php
/**
 * created by zzy
 * date: 2017/10/24 9:28
 */
namespace Framework\Services\SMS\Xinxiguanjia;

class ZhaoyongService
{
    protected $domain     = 'https://sms.xinxiguanjia.com/sms.aspx';
    protected $user_id    = 'bxkj01';
    protected $account    = 'bxkj01';
    protected $password   = 'CC77AF43887B0FA9E92118C5699142E2';
    protected $sendTime   = '';
    protected $extno      = '';

    public function sendSms( $mobile, $code, $type=1 ){

        $content            = '【易管通】';
        switch ( $type ){
            case 1:
                //注册短信
                $content    .= '您的验证码是'.$code.'，在5分钟内有效。如非本人操作请忽略本短信。';
                break;
            case 2:
                //找回密码短信
                $content    .= '您的验证码是'.$code.'，在5分钟内有效。如非本人操作请忽略本短信。';
                break;
            case 3:
                //修改密码短信
                $content    .= '您的验证码是'.$code.'，在5分钟内有效。如非本人操作请忽略本短信。';
                break;
            case 4:
                //修改手机号短信
                $content    .= '您的验证码是'.$code.'，在5分钟内有效。如非本人操作请忽略本短信。';
                break;
        }
        $param_str      = 'action=send&userid='.$this->user_id;
        $param_str      .= '&account='.$this->account.'&password='.$this->password;
        $param_str      .= '&mobile='.$mobile.'&content='.urlencode($content);
        $param_str      .= '&sendTime='.$this->sendTime.'&extno='.$this->extno;
        $curl_result    = $this->send_curl_post( $this->domain, $param_str );
        $result         = $this->handle_curl_result( $curl_result );
        return $result;
    }
    public function file_get_contents_url( $mobile, $content ){
        //file_get_contents( $url );
        $url      = $this->domain.'?action=send&userid='.$this->user_id;
        $url      .= '&account='.$this->account.'&password='.$this->password;
        $url      .= '&mobile='.$mobile.'&content='.urlencode($content);
        $url      .= '&sendTime='.$this->sendTime.'&extno='.$this->extno;
        $result   = file_get_contents( $url );
        return $result;
    }
    public function send_curl_post( $url, $param_str, $is_https=1 ){

        $curl			= curl_init();
        curl_setopt( $curl, CURLOPT_URL, $url );
        curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $curl, CURLOPT_POST, 1);
        if( $is_https == 1 ){
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        curl_setopt( $curl, CURLOPT_POSTFIELDS, $param_str );
        $curl_result	= curl_exec($curl);
//         var_dump( curl_error( $curl) );
        curl_close( $curl );
        return $curl_result;
    }
    public function handle_curl_result( $curl_result ){

        $xml            = simplexml_load_string($curl_result);
        $arr            = json_decode(json_encode($xml),TRUE);
        $returnstatus   = strtolower( $arr['returnstatus'] );
        $result         = $returnstatus === 'success' ? true : false;
        return $result;
    }
//<returnsms>
//    <returnstatus>Faild</returnstatus>
//    <message>需要签名</message>
//    <remainpoint>0</remainpoint>
//    <taskID></taskID>
//    <successCounts>0</successCounts>
//</returnsms>
////
//<returnsms>\r\n
//    <returnstatus>Success</returnstatus>\r\n
//    <message>操作成功</message>\r\n
//    <remainpoint>86256</remainpoint>\r\n
//    <taskID>1710253444307553</taskID>\r\n
//    <successCounts>1</successCounts>\r\n
//</returnsms>
}