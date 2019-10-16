<?php
/**
 * Created by PhpStorm.
 * Author: Sojo
 * Date: 2017/2/15
 * Time: 15:39
 */
/**
 * 异常定义以及使用规范说明
 */
// 用if判断确保只define一次，防止出现php语法警告
if (!defined('ERR_OK')) {
    /**
     * ---------------------------------------------------------------------------------------------------------
     * 系统内部错误定义（一旦定义不可以更改位置，只向后添加）
     * 错误码 0 - 99999
     * ---------------------------------------------------------------------------------------------------------
     */
    define('ERR_OK', 0);                    // 处理正常！以0进行表示。code = 0
    define('ERR_UNKNOWN', 99999);           // 未知错误，通常用在try catch或switch case拦截到不期望的错误发生进行转发错误的时候。
    define('ERR_FRAMEWORK', 10001);         // 框架错误，用于自定义框架中的代码出错
    define('ERR_ASSERT', 10002);            // 断言错误，通常是xAssert判断为假的时候抛出此错误。
    define('ERR_TODO', 10003);              // 未完成的开发功能被调用，应用使用了尚未开发完成的代码时候报错误。
    define('ERR_AUTH', 10004);              // oAuth用户认证失效，access_token失效
    define('ERR_EXPIRED', 10005);           // 登录已过期，此值禁止改变，客户端需要对此值解析做重新登录的跳转判断
    define('ERR_PARAMETER', 10006);         // 参数验证失败，对于函数形参校验错误或者控制器的验证器验证失败的定义。
    define('ERR_ROUTE', 10007);             // 路由配置错误
    define('ERR_PERMISSIONS', 10008);       // 权限错误
    define('ERR_INITIALIZE', 10009);        // 初始化错误
    define('ERR_REQUEST_INVALID', 10010);   // 请求失效

    /**
     * ---------------------------------------------------------------------------------------------------------
     * Framework Service 错误
     * 错误码 200000 - 299999
     * ---------------------------------------------------------------------------------------------------------
     */
    define('ERR_SERVICE_CONFIGURATION_FILE', 200000);               // 配置文件参数错误

    /**
     * SMS
     * 错误码 201000 - 201999
     */
    /** Alidayu，错误码 201000 - 201099 */
    define('ERR_SERVICE_SMS_ALIDAYU', 201001);                      // Alidayu错误
    /** 后续，错误码 201100 - 201199 */

    /**
     * OSS
     * 错误码 202000 - 202999
     */
    /** Aliyun，错误码 202000 - 202099 */
    define('ERR_SERVICE_OSS_ALIYUN', 202001);                       // Aliyun OSS错误

    /**
     * ---------------------------------------------------------------------------------------------------------
     * Logic 错误
     * 错误码 300000 - 399999
     * ---------------------------------------------------------------------------------------------------------
     */
    define('ERR_LOGIC', 300000);                                    // 逻辑错误
    define('ERR_LOGIC_DATA_ALREADY_EXISTS', 300001);                // 数据已存在
    define('ERR_LOGIC_NO_DATA_EXIST', 300002);                      // 数据不存在
    define('ERR_LOGIC_DATA_NEED_TO_UPDATE', 300003);                // 数据无需更新

    /**
     * ---------------------------------------------------------------------------------------------------------
     * Client 错误
     * 错误码 400000 - 999999
     * ---------------------------------------------------------------------------------------------------------
     */

    /** 验证码，错误码 401000 - 401999 */
    define('ERR_CLIENT_CAPTCHA', 300001); // 验证码错误
    define('SMS_CODE_ERROR', 401001); // 验证码错误
    define('SMS_IS_SEND', 401002); // 短信已发送
    define('SMS_SEND_ERROR', 401003); // 短信发送失败
    define('MOBILE_SIZE_ERROR', 401004); // 手机号码位数不正确
    define('MOBILE_PREG_ERROR', 401005); // 手机号码格式不正确
    define('MOBILE_EXIST', 401006); // 手机号码已存在
    define('MOBILE_NOT_EXIST', 401007); // 手机号码不存在

    /** 注册，错误码 402000 - 402999 */
    define('REGISTER_MOBILE_NOT_EXIST', 402001 );//手机号码不存在
    define('REGISTER_MOBILE_EXIST', 402002 );//手机号码已存在
    define('REGISTER_ERROE', 402003 );//数据库插入失败

    /** 登录，错误码 403000 - 403999 */
    define('ERR_CLIENT_LOGIN_INFO', 300002);                        // 登录信息错误
    define('LOGOUT_ERROR', 403003);//退出失败
    define('LOGIN_PASSWORE_ERROE', 403004);//账号或密码不正确
    define('LOGIN_NO_COMPANY', 403005);//账号或密码不正确

    /** 用户，错误码 404000 - 404999 */
    define('ERR_CLIENT_USER_ALREADY_EXISTS', 404001);               // 用户已存在
    define('ERR_CLIENT_USER_NOT_EXIST', 404002);                    // 用户不存在
    define('ERR_CLIENT_USER_AUTH_INFO', 404003);                    // 用户身份验证信息错误
    define('ERR_CLIENT_USER_SET_PASSWORD_ERROE', 404004);// 密码设置失败
    define('ERR_PASSWORD_FORMAT_ERROR', 404005);// 密码格式错误
    define('ERR_TWO_PASSWORD_IS_INCONSISTENT', 404006);// 密码格式错误

    define('ERR_PRODUCT_NOT_EXIST', 405001);                    // 产品不存在
    define('ERR_PRODUCT_CREATE_FAIL', 405002);                    // 产品创建失败
    define('ERR_PRODUCT_INCREMENT_FAIL', 405003);                    // 库存增加失败
    define('ERR_PRODUCT_DECREMENT_FAIL', 405007);                    // 库存减少失败
    define('ERR_PRODUCT_NO_IS_EXIST', 405004);                    // 产品编号已被使用
    define('ERR_PRODUCT_EDIT_FAIL', 405005);                    // 产品编辑失败
    define('ERR_PRODUCT_DELETE_FAIL', 405006);                    // 产品编辑失败
    define('ERR_PRODUCT_CUSTOM_FIELD_CREATE_FAIL', 405007);                    // 产品编辑失败
    define('ERR_STOCK_INFO_NOT_EXIST', 405008);                    // 产品编辑失败
    define('ERR_CUSTOMER_PRODUCT_STOCK_FAIL', 405009);                    // 产品编辑失败

    define('ERR_WATER_NUMBER_EXIST', 406001);                    // 流水号已存在

    define('ERR_CATEGORY_ADD_FAIL', 407001);                    // 分类添加失败
    define('ERR_CATEGORY_NOT_EXIST', 407002);                    // 分类不存在
    define('ERR_CATEGORY_DELETE_FAIL', 407003);                    // 分类删除失败
    define('ERR_CATEGORY_EDIT_FAIL', 407004);                    // 分类添加失败

    define('ERR_ORDER_IS_DISTRIBUTION', 408001);                    // 订单已分配

    define('ERR_CUSTOMER_IS_NOT_EXIST', 409001);                    // 客户不存在
    define('ERR_CUSTOMER_DELETE_FAIL', 409002);                    // 客户删除失败

    define('ERR_PLATE_IS_NOT_EXIST', 410001);                    // 版不存在
    define('ERR_PLATE_DELETE_FAIL', 410002);                    // 版删除失败

    define('ERR_BRANCH_DELETE_FAIL', 411002);                    // 版删除失败

    define('ERR_BUYER_IS_NOT_EXIST', 412001);                    // 单位不存在
    define('ERR_BUYER_DELETE_FAIL', 412002);                    // 单位删除失败

    define('ERR_INVOICE_IS_NOT_EXIST', 413001);                    // 票据不存在
    define('ERR_INVOICE_DELETE_FAIL', 413002);                    // 票据删除失败

    define('ERR_USERNAME_EXIST', 414001);                    // 票据删除失败

    //仓库
    define('ERR_DATA_NOT_FOUND', 510001);                    // 数据不存在
    define('ERR_THE_NUMBER_SHOULD_BE_GREATER_THAN_0', 510002);  // 数量应大于0
    define('ERR_SHORTAGE_OF_WAREHOUSE_STOCK', 510003);  // 仓库库存不足
    define('ERR_THIS_PERMISSION_IS_ALREADY_AVAILABLE', 510004);  // 已有该权限
}

if (!function_exists("get_error_message")) {
    function get_error_message($error_code)
    {
        $list = [
            /**
             * ---------------------------------------------------------------------------------------------------------
             * System 错误
             * ---------------------------------------------------------------------------------------------------------
             */
            ERR_OK              => ['zh-CN' => "处理正常", 'en' => 'ok'],
            ERR_UNKNOWN         => ['zh-CN' => "未知错误", 'en' => 'unknown error'],
            ERR_FRAMEWORK       => ['zh-CN' => "框架错误", 'en' => 'framework error'],
            ERR_ASSERT          => ['zh-CN' => "断言错误", 'en' => 'assert error'],
            ERR_TODO            => ['zh-CN' => "暂未完成此功能的开发", 'en' => 'unfinished functions, please wait for development'],
            ERR_AUTH            => ['zh-CN' => "未授权的请求，请申请权限", 'en' => 'unauthorized request，please apply for permission'],
            ERR_EXPIRED         => ['zh-CN' => "登录已过期，请重新登录", 'en' => 'login has expired, please login again'],
            ERR_PARAMETER       => ['zh-CN' => "参数验证失败", 'en' => 'parameter validation fails'],
            ERR_ROUTE           => ['zh-CN' => "路由配置错误", 'en' => 'route configuration error'],
            ERR_PERMISSIONS     => ['zh-CN' => "权限错误", 'en' => 'permissions error'],
            ERR_INITIALIZE      => ['zh-CN' => "初始化错误", 'en' => 'initialize error'],
            ERR_REQUEST_INVALID => ['zh-CN' => "请求失效", 'en' => 'request invalid'],

            /**
             * ---------------------------------------------------------------------------------------------------------
             * Service 错误
             * ---------------------------------------------------------------------------------------------------------
             */
            ERR_SERVICE_CONFIGURATION_FILE => ['zh-CN' => "配置文件错误", 'en' => 'configuration file'],
            /** Alidayu */
            ERR_SERVICE_SMS_ALIDAYU        => ['zh-CN' => "阿里大于错误", 'en' => 'Alidayu error'],
            /** OSS */
            ERR_SERVICE_OSS_ALIYUN         => ['zh-CN' => "阿里云OSS错误", 'en' => 'Aliyun OSS error'],

            /**
             * ---------------------------------------------------------------------------------------------------------
             * Logic 错误
             * ---------------------------------------------------------------------------------------------------------
             */
            ERR_LOGIC                     => ['zh-CN' => "逻辑错误", 'en' => 'logic error'],
            ERR_LOGIC_DATA_ALREADY_EXISTS => ['zh-CN' => "数据已存在", 'en' => 'data already exists'],
            ERR_LOGIC_NO_DATA_EXIST       => ['zh-CN' => "数据不存在", 'en' => 'no data exist'],
            ERR_LOGIC_DATA_NEED_TO_UPDATE => ['zh-CN' => "数据无需更新", 'en' => 'data need to update'],

            /**
             * ---------------------------------------------------------------------------------------------------------
             * Client 错误
             * ---------------------------------------------------------------------------------------------------------
             */
            /** 验证码 */
            ERR_CLIENT_CAPTCHA             => ['zh-CN' => "验证码错误", 'en' => 'captcha error'],
            SMS_IS_SEND                     => ['zh-CN' => "短信已发送", 'en' => 'sms is send'],
            SMS_SEND_ERROR                  => ['zh-CN' => "短信发送失败", 'en' => 'sms send error'],
            MOBILE_SIZE_ERROR              => ['zh-CN' => "请输入11位手机号码", 'en' => 'mobile must 11'],
            MOBILE_PREG_ERROR              => ['zh-CN' => "该手机号码格式错误", 'en' => 'mobile pattern is error'],
            MOBILE_EXIST                    => ['zh-CN' => "手机号码已存在", 'en' => 'mobile is exist'],
            MOBILE_NOT_EXIST               => ['zh-CN' => "手机号码不存在", 'en' => 'mobile is not exist'],
            /** 注册 */
            REGISTER_MOBILE_NOT_EXIST     => ['zh-CN' => "手机号码不存在", 'en' => 'mobile is not exist'],
            REGISTER_MOBILE_EXIST          => ['zh-CN' => "手机号码已存在", 'en' => 'mobile is exist'],
            REGISTER_ERROE                  => ['zh-CN' => "网络出错，注册失败", 'en' => 'register is error'],

            /** 登录 */
            ERR_CLIENT_LOGIN_INFO          => ['zh-CN' => "登录信息错误", 'en' => 'login info error'],
            LOGOUT_ERROR                    => ['zh-CN' => "网络出错 退出失败", 'en' => 'logout is error'],
            LOGIN_PASSWORE_ERROE           => ['zh-CN' => "账号或密码出错", 'en' => 'mobiel or password is error'],
            LOGIN_NO_COMPANY                => ['zh-CN' => "管理员还未给您设置权限", 'en' => 'login no company'],

            /** 用户 */
            ERR_CLIENT_USER_ALREADY_EXISTS => ['zh-CN' => "用户已存在", 'en' => 'user already exists'],
            ERR_CLIENT_USER_NOT_EXIST      => ['zh-CN' => "用户不存在", 'en' => 'user not exist'],
            ERR_CLIENT_USER_AUTH_INFO      => ['zh-CN' => "用户身份验证信息错误", 'en' => 'user auth info error'],

            ERR_CLIENT_USER_SET_PASSWORD_ERROE  => ['zh-CN' => "密码设置失败", 'en' => 'set password error'],
            ERR_PASSWORD_FORMAT_ERROR => ['zh-CN' => "密码格式错误(6-20字符)", 'en' => 'password format is error'],
            ERR_TWO_PASSWORD_IS_INCONSISTENT => ['zh-CN' => "两次密码不一致", 'en' => 'The two password is inconsistent'],


            ERR_PRODUCT_NOT_EXIST      => ['zh-CN' => "产品不存在", 'en' => 'product not exist'],
            ERR_PRODUCT_NO_IS_EXIST      => ['zh-CN' => "产品编号已存在", 'en' => 'product no is exist'],
            ERR_PRODUCT_EDIT_FAIL      => ['zh-CN' => "产品编辑失败", 'en' => 'product edit fail'],
            ERR_PRODUCT_DELETE_FAIL      => ['zh-CN' => "产品删除失败", 'en' => 'product delete fail'],
            ERR_PRODUCT_CREATE_FAIL      => ['zh-CN' => "产品创建失败", 'en' => 'create product fail'],
            ERR_PRODUCT_CUSTOM_FIELD_CREATE_FAIL      => ['zh-CN' => "材料自定义字段创建失败", 'en' => 'create customer product fields fail'],
            ERR_STOCK_INFO_NOT_EXIST      => ['zh-CN' => "库存流水信息不存在", 'en' => 'stock info is not exist'],
            ERR_CUSTOMER_PRODUCT_STOCK_FAIL      => ['zh-CN' => "客户产品入库失败！", 'en' => 'customer product stock fail'],

            ERR_PRODUCT_INCREMENT_FAIL              => ['zh-CN' => "库存增加失败", 'en' => 'product number increment fail'],
            ERR_PRODUCT_DECREMENT_FAIL              => ['zh-CN' => "库存减少失败", 'en' => 'product number decrement fail'],
            ERR_WATER_NUMBER_EXIST              => ['zh-CN' => "流水号已存在", 'en' => 'water number is exist'],
            ERR_CATEGORY_ADD_FAIL              => ['zh-CN' => "分类添加失败", 'en' => 'add category fail'],
            ERR_CATEGORY_NOT_EXIST              => ['zh-CN' => "分类不存在", 'en' => 'category no exist'],
            ERR_CATEGORY_DELETE_FAIL              => ['zh-CN' => "分类删除失败", 'en' => 'delete category fail'],
            ERR_CATEGORY_EDIT_FAIL              => ['zh-CN' => "分类编辑失败", 'en' => 'edit category fail'],

            ERR_ORDER_IS_DISTRIBUTION              => ['zh-CN' => "订单已分配", 'en' => 'order id distribution'],

            ERR_CUSTOMER_IS_NOT_EXIST              => ['zh-CN' => "客户信息不存在", 'en' => 'customer is not exist'],
            ERR_CUSTOMER_DELETE_FAIL              => ['zh-CN' => "客户删除失败", 'en' => 'customer delete fail'],

            ERR_PLATE_IS_NOT_EXIST              => ['zh-CN' => "版信息不存在", 'en' => 'plate is not exist'],
            ERR_PLATE_DELETE_FAIL              => ['zh-CN' => "版删除失败", 'en' => 'plate delete fail'],


            ERR_BRANCH_DELETE_FAIL              => ['zh-CN' => "支删除失败", 'en' => 'branch delete fail'],

            ERR_BUYER_IS_NOT_EXIST              => ['zh-CN' => "单位不存在", 'en' => 'buyer is not exist'],
            ERR_BUYER_DELETE_FAIL              => ['zh-CN' => "单位删除失败", 'en' => 'buyer delete fail'],

            ERR_INVOICE_IS_NOT_EXIST              => ['zh-CN' => "票据不存在", 'en' => 'invoice is not exist'],
            ERR_INVOICE_DELETE_FAIL              => ['zh-CN' => "票据删除失败", 'en' => 'invoice delete fail'],

            ERR_USERNAME_EXIST              => ['zh-CN' => "用户名已存在", 'en' => 'username already exist'],


            ERR_DATA_NOT_FOUND              => ['zh-CN' => "数据不存在", 'en' => 'data not found'],
            ERR_THE_NUMBER_SHOULD_BE_GREATER_THAN_0 =>['zh-CN' => "数量应大于0", 'en' => 'the number should be greater than 0'],
            ERR_SHORTAGE_OF_WAREHOUSE_STOCK =>['zh-CN' => "仓库库存不足", 'en' => 'shortage of warehouse stock'],
            ERR_THIS_PERMISSION_IS_ALREADY_AVAILABLE=>['zh-CN' => "已有该权限", 'en' => 'this permission is already available'],
        ];

        $language = config('app.locale');

        return isset($list[$error_code]) ? $list[$error_code][$language] : false;
    }
}