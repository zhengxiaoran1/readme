<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>易管通PC版</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="/assets/mobile/layuiadmin/layui/css/layui.css" media="all">
    <link rel="stylesheet" href="/assets/mobile/layuiadmin/style/admin.css" media="all">
    <script src="/assets/mobile/layuiadmin/layui/layui.js"></script>
    <script src="/assets/vendor/b-jui/js/jquery-1.11.3.min.js"></script>
    <script src="/assets/admin/art-template.js?s={{time()}}"></script>
    <script src="/assets/admin/swiper.jquery.min.js"></script>
    <script src="/assets/mobile/common/com.js?s={{time()}}"></script>
    <link href="/assets/mobile/common/mobile.css?s={{time()}}" rel="stylesheet">
    <script>
        var setterData = {};
        layui.config({
            base: '/assets/mobile/layuiadmin/' //静态资源所在路径
        }).extend({
            index: 'lib/index' //主入口模块
        }).use('index');
    </script>
</head>
<body>

@yield('content')

</body>

</html>