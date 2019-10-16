@extends('mobile.layouts.login')
@section('content')

<body>

<div class="layadmin-user-login layadmin-user-display-show" id="LAY-user-login" style="display: none;">

    <div class="layadmin-user-login-main">
        <div class="layadmin-user-login-box layadmin-user-login-header">
            <h2>易管通</h2>
        </div>
        <div class="layadmin-user-login-box layadmin-user-login-body layui-form">
            <div class="layui-form-item">
                <label class="layadmin-user-login-icon layui-icon layui-icon-username" for="LAY-user-login-username"></label>
                <input type="text" name="mobile" id="LAY-user-login-username" lay-verify="required" placeholder="用户名" class="layui-input">
            </div>
            <div class="layui-form-item">
                <label class="layadmin-user-login-icon layui-icon layui-icon-password" for="LAY-user-login-password"></label>
                <input type="password" name="password" id="LAY-user-login-password" lay-verify="required" placeholder="密码" class="layui-input">
            </div>
            {{--<div class="layui-form-item">--}}
                {{--<div class="layui-row">--}}
                    {{--<div class="layui-col-xs7">--}}
                        {{--<label class="layadmin-user-login-icon layui-icon layui-icon-vercode" for="LAY-user-login-vercode"></label>--}}
                        {{--<input type="text" name="vercode" id="LAY-user-login-vercode" lay-verify="required" placeholder="图形验证码" class="layui-input">--}}
                    {{--</div>--}}
                    {{--<div class="layui-col-xs5">--}}
                        {{--<div style="margin-left: 10px;">--}}
                            {{--<img src="https://www.oschina.net/action/user/captcha" class="layadmin-user-login-codeimg" id="LAY-user-get-vercode">--}}
                        {{--</div>--}}
                    {{--</div>--}}
                {{--</div>--}}
            {{--</div>--}}
            {{--<div class="layui-form-item" style="margin-bottom: 20px;">--}}
                {{--<input type="checkbox" name="remember" lay-skin="primary" title="记住密码">--}}
                {{--<a href="forget.html" class="layadmin-user-jump-change layadmin-link" style="margin-top: 7px;">忘记密码？</a>--}}
            {{--</div>--}}
            <div class="layui-form-item">
                <button class="layui-btn layui-btn-fluid" lay-submit lay-filter="LAY-user-login-submit">登 入</button>
            </div>
        </div>
    </div>

</div>

<script src="/assets/mobile/layuiadmin/layui/layui.js"></script>
<script>
    layui.config({
        base: '/assets/mobile/layuiadmin/' //静态资源所在路径
    }).extend({
        index: 'lib/index' //主入口模块
    }).use(['index', 'user'], function(){
        var admin = layui.admin
            ,form = layui.form
            ,setter = layui.setter

        form.render();

        //提交
        form.on('submit(LAY-user-login-submit)', function(obj){
            //请求登入接口
            admin.req({
                type: "POST",
                url: '/api/service/user/login'
                ,data: obj.field,
                done:function(res){
                    //请求成功后，写入 access_token
                    layui.data(setter.tableName, {
                        key: setter.request.tokenName
                        ,value: res.data.token
                    });

                    // 登入成功的提示与跳转
                    layer.msg(res.message, {
                        offset: '15px'
                        ,icon: 1
                        ,time: 1000
                    }, function(){

                        admin.req({
                            type: "POST",
                            url: '/mobile/login'
                            ,data: res,
                            done:function(data){
                                if(!data.code) location.href = '/mobile'; //后台主页
                            }
                        })

                    });
                }
            });

        });
    });
</script>
</body>
</html>
@endsection