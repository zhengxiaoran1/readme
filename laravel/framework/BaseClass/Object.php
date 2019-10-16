<?php
/**
 * Created by PhpStorm.
 * User: Sojo
 * Date: 2017/6/2
 * Time: 19:43
 */
namespace Framework\BaseClass;

class Object
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
}