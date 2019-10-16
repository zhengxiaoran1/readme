<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>VisitLog访问日志</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="Keywords" content="B-JUI,Bootstrap,jquery,ui,前端,框架,开源,OSC,开源框架,knaan"/>
    <meta name="Description" content="B-JUI(Best jQuery UI)前端管理框架。轻松开发，专注您的业务，从B-JUI开始！"/>
    <!-- bootstrap - css -->
    <link href="/assets/vendor/b-jui/themes/css/bootstrap.css" rel="stylesheet">
    <!-- core - css -->
    <link href="/assets/vendor/b-jui/themes/css/style.css" rel="stylesheet">
    <link href="/assets/vendor/b-jui/themes/blue/core.css" id="bjui-link-theme" rel="stylesheet">
    <link href="/assets/vendor/b-jui/themes/css/fontsize.css" id="bjui-link-theme" rel="stylesheet">
    <!-- plug - css -->
    <link href="/assets/vendor/b-jui/plugins/kindeditor_4.1.11/themes/default/default.css" rel="stylesheet">
    <link href="/assets/vendor/b-jui/plugins/colorpicker/css/bootstrap-colorpicker.min.css" rel="stylesheet">
    <link href="/assets/vendor/b-jui/plugins/nice-validator-1.0.7/jquery.validator.css" rel="stylesheet">
    <link href="/assets/vendor/b-jui/plugins/bootstrapSelect/bootstrap-select.css" rel="stylesheet">
    <link href="/assets/vendor/b-jui/plugins/webuploader/webuploader.css" rel="stylesheet">
    <link href="/assets/vendor/b-jui/themes/css/FA/css/font-awesome.min.css" rel="stylesheet">
    <!-- Favicons -->
    <link rel="apple-touch-icon-precomposed" href="/assets/backend/common/layouts/images/ico/apple-touch-icon-precomposed.png">
    <link rel="shortcut icon" href="/assets/backend/common/layouts/images/ico/favicon.png">
    <!--[if lte IE 7]>
    <link href="/assets/vendor/b-jui/themes/css/ie7.css" rel="stylesheet">
    <![endif]-->
    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lte IE 9]>
    <script src="/assets/vendor/b-jui/other/html5shiv.min.js"></script>
    <script src="/assets/vendor/b-jui/other/respond.min.js"></script>
    <![endif]-->
    <!-- jquery -->
    <script src="/assets/vendor/b-jui/js/jquery-1.11.3.min.js"></script>
    <script src="/assets/vendor/b-jui/js/jquery.cookie.js"></script>
    <!--[if lte IE 9]>
    <script src="/assets/vendor/b-jui/other/jquery.iframe-transport.js"></script>
    <![endif]-->
    <!-- /assets/vendor/bjui -->
    <script src="/assets/vendor/b-jui/js/bjui-all.min.js"></script>
    <!-- plugins -->
    <!-- swfupload for kindeditor -->
    <script src="/assets/vendor/b-jui/plugins/swfupload/swfupload.js"></script>
    <!-- Webuploader -->
    <script src="/assets/vendor/b-jui/plugins/webuploader/webuploader.js"></script>
    <!-- kindeditor -->
    <script src="/assets/vendor/b-jui/plugins/kindeditor_4.1.11/kindeditor-all-min.js"></script>
    <script src="/assets/vendor/b-jui/plugins/kindeditor_4.1.11/lang/zh-CN.js"></script>
    <!-- colorpicker -->
    <script src="/assets/vendor/b-jui/plugins/colorpicker/js/bootstrap-colorpicker.min.js"></script>
    <!-- ztree -->
    <script src="/assets/vendor/b-jui/plugins/ztree/jquery.ztree.all-3.5.js"></script>
    <!-- nice validate -->
    <script src="/assets/vendor/b-jui/plugins/nice-validator-1.0.7/jquery.validator.js"></script>
    <script src="/assets/vendor/b-jui/plugins/nice-validator-1.0.7/jquery.validator.themes.js"></script>
    <!-- bootstrap plugins -->
    <script src="/assets/vendor/b-jui/plugins/bootstrap.min.js"></script>
    <script src="/assets/vendor/b-jui/plugins/bootstrapSelect/bootstrap-select.min.js"></script>
    <script src="/assets/vendor/b-jui/plugins/bootstrapSelect/defaults-zh_CN.min.js"></script>
    <!-- icheck -->
    <script src="/assets/vendor/b-jui/plugins/icheck/icheck.min.js"></script>
    <!-- HighCharts -->
    <script src="/assets/vendor/b-jui/plugins/highcharts/highcharts.js"></script>
    <script src="/assets/vendor/b-jui/plugins/highcharts/highcharts-3d.js"></script>
    <script src="/assets/vendor/b-jui/plugins/highcharts/themes/gray.js"></script>
    <!-- other plugins -->
    <script src="/assets/vendor/b-jui/plugins/other/jquery.autosize.js"></script>
    <link href="/assets/vendor/b-jui/plugins/uploadify/css/uploadify.css" rel="stylesheet">
    <script src="/assets/vendor/b-jui/plugins/uploadify/scripts/jquery.uploadify.min.js"></script>
    <script src="/assets/vendor/b-jui/plugins/download/jquery.fileDownload.js"></script>

    <!-- vue -->
    <script src="/assets/vendor/vue/vue.js"></script>

    <!-- init -->
    <?php $prefix = substr(request()->route()->getPrefix(), 1); ?>
    <script>
        var prefix = '<?= $prefix?>';
    </script>

    <script src="/assets/admin/common/initialization/init.js"></script>
    <link href="/assets/global/initialization/init.css" rel="stylesheet">
    <script src="/assets/global/initialization/init.js"></script>
    <script src="/assets/admin/common/initialization/lib.js"></script>

    <!-- highlight && ZeroClipboard -->
    <link href="/assets/vendor/b-jui/assets/prettify.css" rel="stylesheet">
    <script src="/assets/vendor/b-jui/assets/prettify.js"></script>
    <link href="/assets/vendor/b-jui/assets/ZeroClipboard.css" rel="stylesheet">
    <script src="/assets/vendor/b-jui/assets/ZeroClipboard.js"></script>
    <script src="/assets/global/CryptoJS/rollups/md5.js"></script>
    @include('admin.layouts.config')
</head>
<body>
<!--[if lte IE 7]>
<div id="errorie"><div>您还在使用老掉牙的IE，正常使用系统前请升级您的浏览器到 IE8以上版本 <a target="_blank" href="http://windows.microsoft.com/zh-cn/internet-explorer/ie-8-worldwide-languages">点击升级</a>&nbsp;&nbsp;强烈建议您更改换浏览器：<a href="http://down.tech.sina.com.cn/content/40975.html" target="_blank">谷歌 Chrome</a></div></div>
<![endif]-->

@yield('content')

</body>
</html>