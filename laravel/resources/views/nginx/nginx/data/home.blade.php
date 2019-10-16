@extends('nginx.nginx.data.master')
@section('content')
<div id="bjui-top" class="bjui-header">
    <div class="container_fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapsenavbar" data-target="#bjui-top-collapse" aria-expanded="false">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
        </div>
        <nav class="collapse navbar-collapse" id="bjui-top-collapse">
            <ul class="nav navbar-nav navbar-right">
                <li class="datetime"><a><span id="bjui-date">0000/00/00</span> <span id="bjui-clock">00:00:00</span></a></li>
                <li><a href="#">账号：{{ $username }}</a></li>
                <li><a href="#">角色：{{ $roleName }}</a></li>
                <li><a href="/admin/user/change-password" data-toggle="dialog" data-id="sys_user_changepass" data-mask="true" data-width="550" data-height="370">修改密码</a></li>
                <li><a href="/admin/logout" style="font-weight:bold;">&nbsp;<i class="fa fa-power-off"></i> 注销登陆</a></li>
                <li class="dropdown"><a href="#" class="dropdown-toggle bjui-fonts-tit" data-toggle="dropdown" title="更改字号"><i class="fa fa-font"></i> 大</a>
                    <ul class="dropdown-menu" role="menu" id="bjui-fonts">
                        <li><a href="javascript:;" class="bjui-font-a" data-toggle="fonts"><i class="fa fa-font"></i> 特大</a></li>
                        <li><a href="javascript:;" class="bjui-font-b" data-toggle="fonts"><i class="fa fa-font"></i> 大</a></li>
                        <li><a href="javascript:;" class="bjui-font-c" data-toggle="fonts"><i class="fa fa-font"></i> 中</a></li>
                        <li><a href="javascript:;" class="bjui-font-d" data-toggle="fonts"><i class="fa fa-font"></i> 小</a></li>
                    </ul>
                </li>
                <li class="dropdown active"><a href="#" class="dropdown-toggle theme" data-toggle="dropdown" title="切换皮肤"><i class="fa fa-tree"></i></a>
                    <ul class="dropdown-menu" role="menu" id="bjui-themes">
                        <!--
                        <li><a href="javascript:;" class="theme_default" data-toggle="theme" data-theme="default">&nbsp;<i class="fa fa-tree"></i> 黑白分明&nbsp;&nbsp;</a></li>
                        <li><a href="javascript:;" class="theme_orange" data-toggle="theme" data-theme="orange">&nbsp;<i class="fa fa-tree"></i> 橘子红了</a></li>
                        -->
                        <li><a href="javascript:;" class="theme_purple" data-toggle="theme" data-theme="purple">&nbsp;<i class="fa fa-tree"></i> 紫罗兰</a></li>
                        <li class="active"><a href="javascript:;" class="theme_blue" data-toggle="theme" data-theme="blue">&nbsp;<i class="fa fa-tree"></i> 天空蓝</a></li>
                        <li><a href="javascript:;" class="theme_green" data-toggle="theme" data-theme="green">&nbsp;<i class="fa fa-tree"></i> 绿草如茵</a></li>
                    </ul>
                </li>
                <li><a href="javascript:;" onclick="bjui_index_exchange()" title="横向收缩/充满屏幕"><i class="fa fa-exchange"></i></a></li>
            </ul>
        </nav>
    </div>
</div>

<header class="navbar bjui-header" id="bjui-navbar">
    <div class="container_fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" id="bjui-navbar-collapsebtn" data-toggle="collapsenavbar" data-target="#bjui-navbar-collapse" aria-expanded="false">
                <i class="fa fa-angle-double-right"></i>
            </button>
            <a class="navbar-brand" href="{{ env('APP_URL') }}"><img src="/assets/admin/layouts/images/logo.png" height="30"></a>
        </div>
        <nav class="collapse navbar-collapse" id="bjui-navbar-collapse">
            <ul class="nav navbar-nav navbar-right" id="bjui-hnav-navbar">
                @foreach ($topMenuList as $topMenu)
                    @if ($topMenu['active'])
                        <li class="active">
                    @else
                        <li>
                    @endif
                    @if ($topMenu['isChildren'])
                            <a href="/nginx/getSideMenu" data-toggle="sidenav" data-id-key="targetid">{{ $topMenu['name'] }}</a>
                    @else
                            <a href="javascript:;" data-toggle="sidenav" data-id-key="targetid">{{ $topMenu['name'] }}</a>
                    @endif
                        </li>
                @endforeach

                {{--<li class="active">--}}
                {{--<a href="/assets/vendor/bjui/json/menu-form.json" data-toggle="sidenav" data-id-key="targetid">表单相关</a>--}}
                {{--</li>--}}
                {{--<li>--}}
                {{--<a href="/assets/vendor/bjui/json/menu-base.json" data-toggle="sidenav" data-id-key="targetid">基础组件</a>--}}
                {{--</li>--}}
                {{--<li>--}}
                {{--<a href="/assets/vendor/bjui/json/menu-datagrid.json" data-toggle="sidenav" data-id-key="targetid">数据表格(Datagrid)</a>--}}
                {{--</li>--}}
                {{--<li>--}}
                {{--<a href="javascript:;" data-toggle="sidenav" data-tree="true" data-tree-options="{onClick:MainMenuClick}" data-id-key="targetid">待续……</a>--}}
                {{--<script class="items"></script>--}}
                {{--</li>--}}
                {{--<li>--}}
                {{--<a href="1.2" target="_blank">旧版DEMO</a>--}}
                {{--</li>--}}
            </ul>
        </nav>
    </div>
</header>
<div id="bjui-body-box">
    <div class="container_fluid" id="bjui-body">
        <div id="bjui-sidenav-col">
            <div id="bjui-sidenav">
                <div id="bjui-sidenav-arrow" data-toggle="tooltip" data-placement="left" data-title="隐藏左侧菜单">
                    <i class="fa fa-long-arrow-left"></i>
                </div>
                <div id="bjui-sidenav-box">

                </div>
            </div>
        </div>
        <div id="bjui-navtab" class="tabsPage">
            <div id="bjui-sidenav-btn" data-toggle="tooltip" data-title="显示左侧菜单" data-placement="right">
                <i class="fa fa-bars"></i>
            </div>
            <div class="tabsPageHeader">
                <div class="tabsPageHeaderContent">
                    <ul class="navtab-tab nav nav-tabs">
                        <li><a href="javascript:;"><span><i class="fa fa-home"></i> #maintab#</span></a></li>
                    </ul>
                </div>
                <div class="tabsLeft"><i class="fa fa-angle-double-left"></i></div>
                <div class="tabsRight"><i class="fa fa-angle-double-right"></i></div>
                <div class="tabsMore"><i class="fa fa-angle-double-down"></i></div>
            </div>
            <ul class="tabsMoreList">
                <li><a href="javascript:;">#maintab#</a></li>
            </ul>

            <div class="bjui-pageHeader" style="background-color:#fefefe; border-bottom:none;">
                <form data-toggle="ajaxsearch" data-options="{searchDatagrid:$.CurrentNavtab.find('#datagrid-test-filter')}">
                    <fieldset>
                        <legend style="font-weight:normal;">页头搜索：</legend>
                        <div style="margin:0; padding:1px 5px 5px;">
                            <span >搜索日志条数：</span>
                            <input name="maxNum" class="form-control" size="15" type="text" value="500"><br>
                            <span >&nbsp;&nbsp;&nbsp;展示条数&nbsp;&nbsp;&nbsp;：</span>
                            <input name="logNum" class="form-control" size="15" type="text" value="100"><br>
                            <span >&nbsp;&nbsp;&nbsp;状态码&nbsp;&nbsp;&nbsp;：</span>
                            <input name="statusCode" class="form-control" size="15" type="text" value=""><br>
                            <span >&nbsp;&nbsp;&nbsp;关键字&nbsp;&nbsp;&nbsp;：</span>
                            <input name="keyWords" class="form-control" size="15" type="text" value=""><br>
                            <span >&nbsp;&nbsp;&nbsp;选择用户&nbsp;&nbsp;&nbsp;：</span>
                            <select name = "visitIp">
                                @foreach($returnIpUserList as $ipUser)
                                    @if($ipUser['visitIp'] == '192.168.1.71')
                                    <option value="{{$ipUser['visitIp']}}" selected="selected">{{$ipUser['visitUser']}}</option>
                                    @else
                                    <option value="{{$ipUser['visitIp']}}">{{$ipUser['visitUser']}}</option>
                                    @endif
                                @endforeach
                            </select>

                            <div class="btn-group">
                                <button type="submit" class="btn-green" data-icon="search">开始搜索！</button>
                            </div>(搜索数越大，搜到的数据越详尽，但是速度比较慢)
                            <button type="button" class="btn-green" data-icon="search" style="float:right" data-toggle="navtab" data-options="{id:'virtual_visits_navtab',width:800,height:500,fresh:true, url:'/nginx/virtualVisits', title:'模拟访问'}">自定义模拟访问</button>
                        </div>
                    </fieldset>
                </form>
            </div>

            <div class="navtab-panel tabsPageContent">
                <div class="navtabPage unitBox">


                    <div class="bjui-pageContent">
                        <table class="table table-bordered" id="datagrid-test-filter" data-toggle="datagrid" data-options="{
            height: '75%',
            gridTitle : 'nginx访问日志',
            showToolbar: true,
            {{--toolbarItem: 'add,edit,del,|,import,export,exportf',--}}
                                dataUrl: '/nginx/logData?callback=?',
                                dataType: 'jsonp',
                                jsonPrefix: '',
                                paging: {pageSize:9999},
                                showCheckboxcol: true,
                                linenumberAll: true,
                            }">
                            <thead>
                            <tr>
                                {{--<th data-options="{name:'visitIp',width:200,type:'select',items:function(){return $.getJSON('/nginx/getIpUserSelect?callback=?')},itemattr:{value:'visitIp',label:'visitUser'}}">访问用户</th>--}}
                                <th data-options="{name:'visitIp',width:150}">访问ip</th>
                                {{--<th data-options="{name:'visitHost',width:150}">域名</th>--}}
                                {{--<th data-options="{name:'visitUri',width:150}">访问uri</th>--}}
                                <th data-options="{name:'visitUrl',width:350}">访问地址</th>
                                <th data-options="{name:'visitTime',width:100}">访问时间</th>
                                <th data-options="{name:'visitMethod',width:50}">访问方式</th>
                                <th data-options="{name:'visitStatusCode',width:50}">访问状态码</th>
                                {{--<th data-options="{name:'visitBrowser'}">访问浏览器信息</th>--}}
                                <th data-options="{name:'visitGetParam'}">访问get值</th>
                                <th data-options="{name:'visitPostParam'}">访问post值</th>
                                <th data-options="{name:'visitToken',width:200}">访问token值</th>
                                <th data-options="{name:'visitImei',width:200}">访问imei值</th>
                                <th data-options="{render:analogTest}">操作</th>

                            </tr>
                            </thead>
                        </table>
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


                        function analogTest(value, data){
                            data.visitPostParam = HtmlUtil.htmlEncode(data.visitPostParam);
                            data.visitGetParam = HtmlUtil.htmlEncode(data.visitGetParam);

                            var html = '<button type="button" class="btn btn-green" data-toggle="dialog" data-options="{id:\'analog_test_dialog\',width:1300,height:1000,fresh:true, url:\'/nginx/analogTest\',type:\'POST\', title:\'模拟访问\', data:{_token:\'{{csrf_token()}}\',visitUrl:\'' + data.visitUrl + '\',visitGetParam:\'' + data.visitGetParam + '\',visitPostParam:\'' + data.visitPostParam + '\',visitToken:\'' + data.visitToken + '\',visitImei:\'' + data.visitImei + '\',platform:\'' + data.platform + '\',version:\'' + data.version  + '\'}}"><i class="fa fa-edit"></i>模拟访问</button>';


                            var route_deal_url = 'http://192.168.1.202/pathToFile.php?path=' + HtmlUtil.htmlEncode(data.visitUrl);
                            html +=  '<button type="button" class="btn btn-green" onclick="jumpRouteDealUrl(\''+ route_deal_url +'\')"><i class="fa fa-edit"></i>路由地址</button>';


                            var ftp_deal_url = 'http://192.168.1.202/ftpPathSubmit.php?path=' + HtmlUtil.htmlEncode(data.visitUrl);
                            html +=  '<button type="button" class="btn btn-green" onclick="jumpFtpDealUrl(\''+ ftp_deal_url +'\')"><i class="fa fa-edit"></i>FTP地址</button>';


                            return html
                        }


                        function jumpRouteDealUrl(route_deal_url){
                            window.open(route_deal_url);
                        }

                        function jumpFtpDealUrl(ftp_deal_url){
                            window.open(ftp_deal_url);
                        }

                    </script>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
<script src="/assets/vendor/b-jui/other/ie10-viewport-bug-workaround.js"></script>
@endsection