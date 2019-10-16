<div class="bjui-pageContent">
<form action="">
<style>
    .bjui-row {
        letter-spacing: -1px;
    }
</style>
<div class="bjui-row col-2">
    <h3>访问地址</h3>
    <div class="bjui-row col-2">
        <label class="row-label" style="width:100px">Url</label>
        <input style="width:40%" type="text" name="visitUrl" value="{{$visitUrl}}" id="visitUrl">
        <button type="button" class="btn btn-green" onclick="jumpRouteDealUrl('{{$visitUrl}}')"><i class="fa fa-edit"></i>路由地址</button>
        {{--<button type="button" class="btn btn-green" onclick="jumpFtpDealUrl('{{$visitUrl}}')"><i class="fa fa-edit"></i>FTP地址</button>--}}
    </div>

    <h3>header头</h3>
    <label class="row-label" style="width:100px">TOKEN</label>
    <input style="width:40%" type="text" name="visitToken" value="{{$visitToken}}" id="visitToken">

    <label class="row-label" style="width:100px">IMEI</label>
    <input style="width:40%" type="text" name="visitImei" value="{{$visitImei}}" id="visitImei">

    <label class="row-label" style="width:100px">platform</label>
    <input style="width:40%" type="text" name="platform" value="{{$platform}}" id="platform">

    <label class="row-label" style="width:100px">version</label>
    <input style="width:40%" type="text" name="version" value="{{$version}}" id="version">
    <hr>

    <h3>传值</h3>
    <div class="bjui-row col-2">
            <label style="width:100px" class="row-label">get值</label>
            <input style="width:40%" type="text" name="visitGetParam" value="{{$visitGetParam}}" id="visitGetParam">
        
            <label style="width:100px" class="row-label">post值</label>
            <input style="width:40%" type="text" name="visitPostParam" value="{{$visitPostParam}}" id="visitPostParam">
    </div>
    <hr>

    <h3>返回值</h3><button type="button" class="btn-orange" style="float:right" onclick="mytextToggle()">隐藏/展示</button>
    <textarea id="mytext" style="height:350px;">{{$output}}</textarea>
    <script>
        function mytextToggle(){
            $('#mytext').toggle();
        }
    </script>

    <h3>HTML展示</h3>
    <div class="bjui-row col-2">
        <?php echo  $output?>
    </div>
</div>
</form>
</div>

<div class="bjui-pageFooter">
    <ul>
        <li><button type="button" class="btn-default" data-icon="save" onclick="analogTestSubmit();">提交</button></li>
        <li><button type="button" class="btn-close" data-icon="close">取消</button></li>
    </ul>
</div>
<script>

    var HtmlUtil = {
        /*1.用浏览器内部转换器实现html转码*/
        htmlEncode:function (html){
            //1.首先动态创建一个容器标签元素，如DIV
            var temp = document.createElement ("div");
            //2.然后将要转换的字符串设置为这个元素的innerText(ie支持)或者textContent(火狐，google支持)
            (temp.textContent != undefined ) ? (temp.textContent = html) : (temp.innerText = html);
            //3.最后返回这个元素的innerHTML，即得到经过HTML编码转换的字符串了
            var output = temp.innerHTML;
            temp = null;
            return output;
        },
        /*2.用浏览器内部转换器实现html解码*/
        htmlDecode:function (text){
            //1.首先动态创建一个容器标签元素，如DIV
            var temp = document.createElement("div");
            //2.然后将要转换的字符串设置为这个元素的innerHTML(ie，火狐，google都支持)
            temp.innerHTML = text;
            //3.最后返回这个元素的innerText(ie支持)或者textContent(火狐，google支持)，即得到经过HTML解码的字符串了。
            var output = temp.innerText || temp.textContent;
            temp = null;
            return output;
        }
    };

    function jumpRouteDealUrl(visitUrl){
        var route_deal_url = 'http://192.168.1.202/pathToFile.php?path=' + HtmlUtil.htmlEncode(visitUrl);
        window.open(route_deal_url);
    }

    function jumpFtpDealUrl(visitUrl){
        var ftp_deal_url = 'http://192.168.1.202/ftpPathSubmit.php?path=' + HtmlUtil.htmlEncode(visitUrl);
        window.open(ftp_deal_url);
    }


    function analogTestSubmit() {
        var visitUrl = $("#visitUrl").val();
        var visitGetParam = $("#visitGetParam").val();
        var visitPostParam = $("#visitPostParam").val();
        var visitToken = $("#visitToken").val();
        var visitImei = $("#visitImei").val();
        var platform = $("#platform").val();
        var version = $("#version").val();
        BJUI.dialog({
            id:'analog_test_dialog',
            data:{_token:'{{csrf_token()}}',visitUrl:visitUrl,visitGetParam:visitGetParam,visitPostParam:visitPostParam,visitToken:visitToken,visitImei:visitImei,platform:platform,version:version},
            url:'/nginx/analogTest',
            title:'模拟访问',
            width:1300,
            height:1000
        })

    }
</script>