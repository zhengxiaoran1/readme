@extends('mobile.layouts.master')
@section('content')

<body>
    <body class="layui-layout-body">
    <div id="LAY_app">
        <div class="layui-layout layui-layout-admin">
            <div class="layui-header">
                <!-- 头部区域 -->
                <ul class="layui-nav layui-layout-left">
                    <li class="layui-nav-item layadmin-flexible" lay-unselect>
                        <a href="javascript:;" layadmin-event="flexible" title="侧边伸缩">
                            <i class="layui-icon layui-icon-shrink-right" id="LAY_app_flexible"></i>
                        </a>
                    </li>
                    <li class="layui-nav-item" lay-unselect>
                        <a href="javascript:;" layadmin-event="refresh" title="刷新">
                            <i class="layui-icon layui-icon-refresh-3"></i>
                        </a>
                    </li>
                </ul>
                <ul class="layui-nav layui-layout-right" lay-filter="layadmin-layout-right">

                    {{--<li class="layui-nav-item" lay-unselect>--}}
                        {{--<a lay-href="app/message/index.html" layadmin-event="message" lay-text="消息中心">--}}
                            {{--<i class="layui-icon layui-icon-notice"></i>--}}

                            {{--<!-- 如果有新消息，则显示小圆点 -->--}}
                            {{--<span class="layui-badge-dot"></span>--}}
                        {{--</a>--}}
                    {{--</li>--}}
                    <li class="layui-nav-item layui-hide-xs" lay-unselect>
                        <a href="javascript:;" layadmin-event="theme">
                            <i class="layui-icon layui-icon-theme"></i>
                        </a>
                    </li>
                    {{--<li class="layui-nav-item layui-hide-xs" lay-unselect>--}}
                        {{--<a href="javascript:;" layadmin-event="note">--}}
                            {{--<i class="layui-icon layui-icon-note"></i>--}}
                        {{--</a>--}}
                    {{--</li>--}}
                    <li class="layui-nav-item layui-hide-xs" lay-unselect>
                        <a href="javascript:;" layadmin-event="fullscreen">
                            <i class="layui-icon layui-icon-screen-full"></i>
                        </a>
                    </li>
                    <li class="layui-nav-item" lay-unselect>
                        <a href="javascript:;">
                            <cite>系统操作</cite>
                        </a>
                        <dl class="layui-nav-child">
                            <dd><a lay-href="/mobile/pwd">修改密码</a></dd>
                            <hr>
                            <dd layadmin-event="logout" style="text-align: center;"><a>退出</a></dd>
                        </dl>
                    </li>

                    {{--<li class="layui-nav-item layui-hide-xs" lay-unselect>--}}
                        {{--<a href="javascript:;" layadmin-event="about"><i class="layui-icon layui-icon-more-vertical"></i></a>--}}
                    {{--</li>--}}
                    {{--<li class="layui-nav-item layui-show-xs-inline-block layui-hide-sm" lay-unselect>--}}
                        {{--<a href="javascript:;" layadmin-event="more"><i class="layui-icon layui-icon-more-vertical"></i></a>--}}
                    {{--</li>--}}
                </ul>
            </div>

            <!-- 侧边菜单 -->
            <div class="layui-side layui-side-menu">
                <div class="layui-side-scroll">
                    <div class="layui-logo" lay-href="/mobile/home">
                        <span><img src="/assets/admin/layouts/images/logo.png" height="25"></span>
                    </div>

                    <ul class="layui-nav layui-nav-tree" lay-shrink="all" id="LAY-system-side-menu" lay-filter="layadmin-system-side-menu">

                        <li data-name="get" class="layui-nav-item">
                            <a href="javascript:;" lay-href="/mobile/product/list" lay-tips="产品库" lay-direction="2">
                                <i class="layui-icon layui-icon-app"></i>
                                <cite>产品库</cite>
                            </a>
                        </li>

                        <li data-name="get" class="layui-nav-item">
                            <a href="javascript:;" lay-href="/mobile/customer/outBill" lay-tips="交货单" lay-direction="2">
                                <i class="layui-icon layui-icon-tabs"></i>
                                <cite>交货单</cite>
                            </a>
                        </li>

                    </ul>
                </div>
            </div>

            <!-- 页面标签 -->
            <div class="layadmin-pagetabs" id="LAY_app_tabs">
                <div class="layui-icon layadmin-tabs-control layui-icon-prev" layadmin-event="leftPage"></div>
                <div class="layui-icon layadmin-tabs-control layui-icon-next" layadmin-event="rightPage"></div>
                <div class="layui-icon layadmin-tabs-control layui-icon-down">
                    <ul class="layui-nav layadmin-tabs-select" lay-filter="layadmin-pagetabs-nav">
                        <li class="layui-nav-item" lay-unselect>
                            <a href="javascript:;"></a>
                            <dl class="layui-nav-child layui-anim-fadein">
                                <dd layadmin-event="closeThisTabs"><a href="javascript:;">关闭当前标签页</a></dd>
                                <dd layadmin-event="closeOtherTabs"><a href="javascript:;">关闭其它标签页</a></dd>
                                <dd layadmin-event="closeAllTabs"><a href="javascript:;">关闭全部标签页</a></dd>
                            </dl>
                        </li>
                    </ul>
                </div>
                <div class="layui-tab" lay-unauto lay-allowClose="true" lay-filter="layadmin-layout-tabs">
                    <ul class="layui-tab-title" id="LAY_app_tabsheader">
                        <li lay-id="/mobile/home" lay-attr="/mobile/home" class="layui-this"><i class="layui-icon layui-icon-home"></i></li>
                    </ul>
                </div>
            </div>


            <!-- 主体内容 -->
            <div class="layui-body" id="LAY_app_body">
                <div class="layadmin-tabsbody-item layui-show">
                    <iframe src="/mobile/home" frameborder="0" class="layadmin-iframe"></iframe>
                </div>
            </div>

            <!-- 辅助元素，一般用于移动设备下遮罩 -->
            <div class="layadmin-body-shade" layadmin-event="shade"></div>
        </div>
    </div>
    </body>
    </html>
@endsection

