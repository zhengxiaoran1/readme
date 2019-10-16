<?php
/**
 * Created by PhpStorm.
 * User: Sojo
 * Date: 2017/6/2
 * Time: 18:01
 */
namespace Framework\BaseClass;

use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    /**
     * 构造函数，后续继承时需在最后调用父级
     * e.g.
     *      ... 配置生效前的初始化过程
     *      parent::__construct();
     * @author Sojo
     * Controller constructor.
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * 构造函数执行完成后，调用该方法，日常开发中该方法可代替大部分构造函数的作用
     * 后续继承时需在最开始时调用父级
     * e.g.
     *      parent::init();
     *      ... 配置生效后的初始化过程
     * @author Sojo
     */
    protected function init()
    {

    }

    /**
     * 获取请求参数并验证是否符合接口的约定参数
     * @author Sojo
     * @param $required array 必填参数
     * @param $optional array 可选参数，如和 $required 内的 name 值相同，则会把对应的 name 值变为可选参数
     * @return array 成功：api请求数据，失败：提示错误信息
     */
    protected function getRequestParameters($required = [], $optional = [])
    {
        if (is_string($required)) {
            $required = func_get_args();
            $optional = [];
        }

        // $params为空直接获取所有提交数据
        if (empty($required)) return request()->all();

        if (!is_array($required) || !is_array($optional)) xThrow(ERR_PARAMETER);

        $data = [];
        // 验证必填参数并获取
        if (!empty($required)) {
            $data = request()->only($required);
            if (in_array(null, $data, true)) xThrow(ERR_PARAMETER);
        }

        // 获取扩展可选参数
        if (!empty($extend)) {
            $extendData = request()->only($extend);
            $data = array_merge($data, $extendData);
        }

        // 传递的参数的值不能为空
        if (in_array('', $data, true)) xThrow(ERR_PARAMETER);

        return $data;
    }

    /**
     * 验证请求数据
     * @author Sojo
     * @param array $data 表单数据
     * @param array $rules 验证规则
     * @param bool $isException 是否抛异常
     * @param array $messages 自定义错误提示信息
     * @param array $attributes 字段名
     * @exceptions ERR_PARAMETER
     * @return string|null 错误提示信息
     */
    protected function validateRequestParameters($data, $rules, $isException = true, $messages = [], $attributes=[])
    {
        $validator = \Validator::make($data, $rules, $messages, $attributes);
        if ($validator->fails()) {
            $msg = '';
            foreach ($validator->errors()->toArray() as $value) {
                $msg .= $value[0] . '<br />';
            }
            if ($isException) xThrow(ERR_PARAMETER, $msg);
            return $msg;
        }
        return null;
    }
}