<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Iconos -->
    <link href="http://maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/mobile/order/css/style.css" media="screen" type="text/css" />
    <script src="/assets/mobile/order/js/jquery-1.10.2.js"></script>
</head>

<body>
</div>
<!-- Contenedor -->
<header class="header"></header>
<ul id="accordion" class="accordion">

</ul>
<script src=''></script>
<script src="/assets/mobile/order/js/index.js"></script>
</body>
<script>
    var order_report_id = GetQueryString('order_report_id');
    window.onload=function () {
        $.ajax({
            type: "POST",
            url: "/api/service/order/getOrderReport",
            data: {
                order_report_id: order_report_id
            },
            dataType: "json",
            success: function (data) {
                $('#accordion').empty();   //清空resText里面的所有内容
                var html = '';
                var that=$;
                var datatest=data.data;
                $('.header').html(datatest.report_title);
                document.title = datatest.report_title;
                $.each(datatest.dataDetail, function(key, submenu){
                    if(submenu.type==1){
                        html +='<li><div class="link2"><span>'+submenu.title+'</span><span  class="value">'+submenu.value+'</span></div></li>'
                    }else if(submenu['type']==2){
                        html += '<li><div class="link"><span>'+submenu.title+'</span><i class="fa fa-chevron-down"></i></div>'
                            +'<ul class="submenu">'
                        that.each(submenu.data,function(key,value){
                            html += '<li><a href="#">'+value.title+'<span class="value">'+value.value+'</span></a></li>'
                        })
                        html+='</ul></li>'
                    }
                    ;
                });

                $('#accordion').html(html);
            },
            error:function(data){
            }
        })
    }

//获取url上参数的值
function GetQueryString(name)
{
    var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
    var r = window.location.search.substr(1).match(reg);
    if(r!=null)return  unescape(r[2]); return null;
}
</script>
</html>