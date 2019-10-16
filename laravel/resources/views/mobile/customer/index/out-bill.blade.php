@extends('mobile.layouts.master')
@section('content')
    <style>
        .out_bill_title{
            color: black;
            font-size: 16px;
            margin-bottom: 10px;
        }
        .out_bill_table{
            padding: 10px;
            margin-top: 0px;
        }
        #out-bill-list div{
            margin-top: 10px;
        }
        .out_bill_title{
            position: relative;
        }
        .out_bill_title a{
            display: block;
            position: absolute;
            right: 0px;
            top: 0px;
            font-size: 15px;
            color: #40A4EA;
        }
    </style>
    <div class="layui-fluid">
        <div class="layui-row layui-col-space15">
            <div class="layui-col-md12">
                <div class="layui-card">
                    <div class="layui-card-header">交货单</div>
                    <div class="layui-card-body">
                        <div id="out-bill-list"></div>
                    </div>
                    <div class="layui-table-page" style="background: #FFF;position: sticky;bottom: 0px;padding: 7px 0px;">
                        <div id="test1"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<script type="application/javascript">
    layui.use(['layer','laypage'], function () {
        var layer = parent.layer === undefined ? layui.layer : top.layer,
            laypage = layui.laypage;
        var page = 1;
        var limit = 20;
        function getOutBillList(page_init=false) {
            layer.load(2);
            ygt_control.token = "{{session('user.token')}}";
            ygt_control.url = "/api/service/warehouse/out-bill";
            ygt_control.data = {customer_id: 'all', page: page,limit:limit};
            ygt_control.ajax(function (data) {
                if (data.status) {
                    $('html,body').animate({ scrollTop: 0 }, 100);
                    var _html = template('out_bill_list',data);
                    $("#out-bill-list").html("").append(_html);
                    layer.closeAll('loading');
                    if(page_init){
                        laypage.render({
                            elem: 'test1' //注意，这里的 test1 是 ID，不用加 # 号
                            ,count: data.count //数据总数，从服务端得到
                            , limit: 20                      //每页显示条数
                            , limits: [20, 50, 100]
                            , prev: '<i class="layui-icon"></i>'                 //上一页文本
                            , next: '<i class="layui-icon"></i>'                 //下一页文本
                            ,layout: ['prev', 'page', 'next','limit','skip']
                            ,jump: function(obj, first){
                                page = obj.curr;
                                limit = obj.limit;
                                getOutBillList();
                            }
                        });
                    }
                }
            });
        }
        getOutBillList(true);
    })
    function printDetail(id) {
        var url = '/mobile/customer/printDetail?id=' + id;
        var name = '打印交货单';
        var iWidth = 1100; //弹出窗口的宽度;
        var iHeight = 550; //弹出窗口的高度;
        var iTop = (window.screen.availHeight - 30 - iHeight) / 2; //获得窗口的垂直位置;
        var iLeft = (window.screen.availWidth - 10 - iWidth) / 2; //获得窗口的水平位置;
        window.open(url, name, "height=" + iHeight + ", width=" + iWidth + ", top=" + iTop + ", left=" + iLeft + ",toolbar=no, menubar=no,  scrollbars=yes,resizable=yes,location=no, status=no");
    }
</script>
    <script type="text/html" id="out_bill_list">
        <% for(var i = 0; i < data.length; i++){ %>
        <% var item = data[i]%>
        <div>
             <div class='out_bill_title'>交货单编号：<%= item.sn %><a href='javascript:;' onclick='printDetail(<%= item.id %>)'>打印</a></div>
            <div class='out_bill_table'>
                <table class='layui-table'  lay-skin='line'>
                    <tr>
                        <th>产品图片</th>
                        <th>产品名称</th>
                        <th>规格</th>
                        <th>打包</th>
                        <th>已交货</th>
                        <th>总件数</th>
                        <th>总条数</th>
                        </tr>
                    <tbody>
                    <% if(item.data.length > 0){%>
                    <% for(var n = 0; n < item.data.length; n++){ %>
                        <% var value = item.data[n] %>
                        <tr>
                             <td><img src='<%= value.img_path %>' alt='' width='40px' height='40px'/td>
                             <td><%= value.product_name %></td>
                             <td><%= value.finished_specification %></td>
                             <td><%= value.pack %></td>
                             <td><%= value.send_info %></td>
                             <td><%= value.str_out_pre %></td>
                             <td><%= value.pre_number %></td>
                        </tr>
                        <%}%>
                    <%}else{%>
                        <tr>
                            <td style='text-align:center'>暂无产品</td>
                        </tr>
                    <%}%>
                    </tbody>
                </table>
            </div>
        </div>
        <%}%>
    </script>
@endsection