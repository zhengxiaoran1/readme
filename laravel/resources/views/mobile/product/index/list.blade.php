
@extends('mobile.layouts.master')
@section('content')
    <body >
    <div class="layui-fluid">
        <div class="layui-row layui-col-space15">
            <div class="layui-col-md12">
                <blockquote class="layui-elem-quote quoteBox" style="background:#FFF">
                    <form class="layui-form">
                        <div class="layui-inline">
                            <div class="layui-input-inline">
                                <input type="search" name="keyword" autocomplete="off" class="layui-input" placeholder="搜索相关信息"/>
                            </div>
                            <div class="layui-btn-group">
                                <a class="layui-btn layui-btn-green layui-btn-sm search_btn" title="搜索">
                                    <i class="layui-icon layui-icon-search "></i>
                                </a>
                                <a class="layui-btn layui-btn-normal layui-btn-sm add_btn" title="添加">
                                    <i class="layui-icon layui-icon-add-circle"></i>
                                </a>
                            </div>
                        </div>
                    </form>
                </blockquote>
                <div class="layui-card">
                    <div class="layui-card-header">产品列表</div>
                    <div class="layui-card-body chanpin">
                        <table class="layui-hide" id="table" lay-filter="table"></table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type="text/html" id="img_html">
        <img src="@{{ d.img_url }}" style="width:35px;height:35px;"/>
    </script>

    <script type="text/html" id="test-table-operate-barDemo">
        <a class="layui-btn layui-btn-primary layui-btn-xs" lay-event="edit">编辑</a>
        <a class="layui-btn layui-btn-xs" lay-event="aboutOrder">相关订单</a>
        <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="createOrder">创建订单</a>
    </script>

    </body>
    <script type="text/javascript">
        var tablist = '';
        layui.use(['layer','table'], function () {

            var layer = parent.layer === undefined ? layui.layer : top.layer,
                table = layui.table;

            setterData = layui.data(layui.setter.tableName);

            //搜索
            $(".search_btn").on("click", function () {
                search();
            });
            $('.add_btn').on('click',function(){
                addoredit('/mobile/product/choseType','新建产品');
            })
            //搜索+刷新
            function search(){
                table.reload('table',{
                    page:{curr:1}
                    ,where:{
                        keyword:$("input[name=keyword]").val()
                    }
                });
            }

            tablist = table.render({
                url: '/api/service/chanpin/getChanpinList',
                id: "table",
                elem: '#table',
                method: 'post',
                page: true,
                limit: 20,
                limits: [20, 50, 100, 200],
                height: "full-175",
                headers: {token: setterData.token},
                cols: [[
                    {field: 'id', width: 80, title: 'ID', sort: true}
                    , {align: 'center', title: '产品图片', minWidth: 100, toolbar: "#img_html"}
                    , {field: 'product_name', title: '产品名称'}
                    , {field: 'ordertype_title_value', title: '产品类型'}
                    , {field: 'bh_value', title: '产品编号'}
                    , {field: 'price', title: '产品单价'}
                    , {field: 'pianliaoguige', title: '片料规格'}
                    , {field: 'customer_title_value', title: '所属客户'}
                    , {field: 'order_count', title: '订单总数', sort: true}
                    , {field: 'kucun_count', title: '产品库存', sort: true}
                    , {field: 'zong_liang_count', title: '订单总量', sort: true}
                    , {
                        width: 250,
                        align: 'center',
                        fixed: 'right',
                        title: '操作',
                        toolbar: '#test-table-operate-barDemo'
                    }
                ]],
            });

            function addoredit(url,name) {

                var index = layui.layer.open({
                    title: name,
                    type: 2,
                    content: url,
                    area:['60%','96%'],
                    resize: false
                });
            }
            function orderList(url,name){
                var index = layui.layer.open({
                    title: name,
                    type: 2,
                    content: url,
                    area:['95%','95%'],
                    resize: false
                });
            }
            function createOrder(url,name){
                var index = layui.layer.open({
                    title: name,
                    type: 2,
                    content: url,
                    area:['40%','80%'],
                    resize: false
                });
            }
            table.on('tool(table)', function (obj) {
                var layEvent = obj.event,
                    data = obj.data;
                if (layEvent === 'edit') {
                    //编辑
                    addoredit('/mobile/product/choseType?id='+data.id,'编辑 - <span class="layui-red">'+ data.product_name +'</span>');
                }else if(layEvent === 'createOrder'){
                    createOrder('/mobile/product/createOrder?id='+data.id+'&customer_title_value='+data.customer_title_value+'&buyer_title='+data.buyer_title+'&buyer_id='+data.buyer_id+'&customer_id='+data.customer_id+'&unit='+data.unit+'&img_url='+data.img_url+'&product_name='+data.product_name+'&ordertype_title_value='+data.ordertype_title_value+'&bh_value='+data.bh_value+'&price='+data.price,'创建订单 - <span class="layui-red">'+ data.product_name +'</span>');
                }else if (layEvent === 'aboutOrder'){
                    orderList('/mobile/product/orderList?id='+data.id,'订单列表 - <span class="layui-red">'+ data.product_name +'</span>');
                }
            });



        });
    </script>
@endsection