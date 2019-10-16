<?php
/**
 * Created by PhpStorm.
 * Author: Sojo
 * Date: 2017/2/15
 * Time: 18:45
 */
namespace Framework\Listeners;

use Dingo\Api\Event\ResponseWasMorphed;

class DingoApiResponse
{
    public function handle(ResponseWasMorphed $event)
    {
        \DebugBar::disable();

        // 如果异常类是DebugError，则修改状态码为200
        if (isset($event->content['debug']) && $event->content['debug']['class'] == 'DebugError') {
            $event->response->setStatusCode(200);
        }

        // 获取状态码和错误码
        $statusCode = $event->response->getStatusCode();
        $codeCode = ($statusCode != 200) ? $statusCode : 0;

        if (isset($event->response->original['status_code']))
            $codeCode = $event->response->original['status_code'];

        if (isset($event->response->original['code']) && isset($event->response->original['message']))
            $codeCode = $event->response->original['code'];

        // 获取返回数据
        if (isset($event->content['__data__']) && isset($event->content['__paging__'])) {
            $data = $event->content['__data__'];
            $paging = $event->content['__paging__'];
        } else {
            $data = $event->content;
        }

        // 如果未开启debug模式，返回数据过滤其中的调试信息，直接返回空数据
        if (env('APP_DEBUG', false)) {
            if (isset($data['debug'])) $data = $data['debug'];
        } else {
            if (isset($data['debug'])) $data = ['debugmessage'=>'debug已关闭'];
        }

        $message = isset($event->response->original['message']) ? $event->response->original['message'] : '';

        // 对成功消息做处理
        if (empty($message)) {
            $message = ($codeCode === 0) ? get_error_message($codeCode) : '请求成功';
        }

        $content = [
            'status'  => $statusCode,
            'code'    => $codeCode,
            'message' => $message,
            'data'    => $data,
        ];

        if (isset($paging)){
            $content['paging'] = $paging;
            $content['count'] = $paging['total'];
        }

        $event->content = $content;
    }
}