<?php
/**
 * Created by PhpStorm.
 * Author: Sojo
 * Date: 2017/2/15
 * Time: 15:39
 */
require_once __DIR__ . '/../../../config/error-code.php';

// 意外的定义，一般情况不会发生不需要应用处理程序自动确定发生意外的位置和日志信息。运维和开发人员根据日志进行判断是否需要进行异常处理。
class DebugError extends \Exception
{

}

function xAssert($expr)
{
    if (empty($expr)) {
        // 不允许xAssert调用的函数不进行任何的返回，不返回状态视为返回未定义错误是程序的逻辑bug应该断言出来进行错误提示。
        $msg = get_error_message(ERR_ASSERT);
        xSetErrorInfo(ERR_ASSERT, $msg);
    } elseif (is_bool($expr) || is_integer($expr)) {
        // 普通的布尔表达式处理之
        if (!$expr) {
            //xlog(xbacktrace());
            $msg = get_error_message(ERR_ASSERT);
            xSetErrorInfo(ERR_ASSERT, $msg);
        }
    } elseif (is_object($expr) || (is_array($expr) && isset($expr['code']))) {
        // case1处理object为curl请求的返回值判断之(必须要严格符合错误规范带上字段code和message信息表达处理后的状态)
        // case2处理array为分页查询数组返回的结果处理。
        $array = (array)$expr;
        if (!isset($array['code']) || !is_integer($array['code'])) {
            $msg = get_error_message(ERR_ASSERT);
            xSetErrorInfo(ERR_ASSERT, $msg);
        }
        if ($array['code'] != ERR_OK) {
            xSetErrorInfo($array['code'], $array['message']);
        }
        return $expr;
//    } elseif (is_array($expr) && $expr['__type__'] == 'graphql') {
//        xAssert(!empty($expr['data']));
//        return $expr['data'];
//    } elseif (is_array($expr)) {
//        // curl_multi返回处理的统一校验错误判断处理之
//        $code = ERR_OK;
//        $msg = '';
//        foreach ($expr as $k => $v) {
//            xAssert(is_object($v) && property_exists($v, 'code') && property_exists($v, 'message'));
//            if ($v->code != ERR_OK) {
//                $code = $v->code;
//                $msg = $v->message;
//            }
//        }
//        if ($code != ERR_OK) {
//            xSetErrorInfo($code, $msg);
//        }
//        return $expr;
    } else {
        xThrow(ERR_FRAMEWORK);
    }
}


function xThrow($err = ERR_UNKNOWN, $message = null)
{
    $msg = get_error_message($err);
    if ($message) {
        $msg .= ':' . $message;
    }
    xSetErrorInfo($err, $msg);
}

// 设置错误信息
function xSetErrorInfo($code, $message)
{
    if ($code == ERR_ASSERT) {
        $message .= xDumpCallLine('xAssert');
    } else {
        $message .= xDumpCallLine('xThrow');
    }
    throw new \DebugError($message, $code);
}

// 获取函数调用位置
function xDumpCallLine($func)
{
    // 如果是发布环境返回空字符串，不进行错误位置的定位。
    // TODO 后续需要写错误日志进行系统健壮性统计分析。
    if (!config('app.debug')) return '';

    $array = debug_backtrace();
    $trace = '';
    foreach ($array as $idx => $row) {
        if (!isset($row['file'])) break;
        if ($row['function'] == $func) {
            $row = $array[$idx + 1];
            if (!isset($row['file'])) {
                $row = $array[$idx];
                if (!isset($row['file']))
                    break;
            }
//            $trace = "[{$row['file']}:{$row['line']}:{$row['function']}]";
            $trace = "[{$row['file']}:{$row['line']}]";
            break;
        }
    }
    return ':' . $trace;
}