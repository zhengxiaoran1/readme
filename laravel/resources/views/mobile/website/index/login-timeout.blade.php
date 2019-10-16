<div class="bjui-pageContent">
    <form action="{{ url('mobile/login-timeout') }}" data-toggle="validate" method="post" data-close-current="true">
        {{ csrf_field() }}
        <div class="bjui-row col-1" style="padding:20px;width:350px;">
            <label class="row-label">登录账户：</label>
            <div class="row-input required">
                <input type="text" class="input-nm" data-rule="required" name="account" value="" placeholder="用户名、邮箱、手机号">
            </div>
            <br/>
            <label class="row-label">登录密码：</label>
            <div class="row-input required">
                <input type="password" class="input-nm" data-rule="required" name="password" value="" placeholder="密码">
            </div>
        </div>
    </form>
</div>
<div class="bjui-pageFooter">
    <ul>
        <li><button type="button" class="btn-close" data-icon="remove">取消</button></li>
        <li><button type="submit" class="btn-default" data-icon="check">登录</button></li>
    </ul>
</div>