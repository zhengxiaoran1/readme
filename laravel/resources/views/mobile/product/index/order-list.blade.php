@extends('mobile.layouts.master')
@section('content')
    <div class="layui-fluid">
        <div class="layui-row layui-col-space15">
            <div class="layui-col-md12">
                <div class="layui-card">
                    <div class="layui-card-header">订单列表</div>
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
        <a class="layui-btn layui-btn-xs" lay-event="edit">查看</a>
    </script>
    <script type="text/javascript">
        var tablist = '';
        layui.use(['layer','table'], function () {

            var layer = parent.layer === undefined ? layui.layer : top.layer,
                table = layui.table;
            setterData = layui.data(layui.setter.tableName);
            tablist = table.render({
                url: '/api/service/chanpin/getChanpinOrderList',
                where:{chanpin_id:'{{$id}}'},
                id: "table",
                elem: '#table',
                method: 'post',
                page: true,
                limit: 20,
                limits: [20, 50, 100, 200],
                height: "full-100",
                headers: {token: setterData.token},
                cols: [[
                    {field: 'id', title: '#'}
                    , {align: 'center', title: '产品图片', width: 80, toolbar: "#img_html"}
                    , {field: 'sn', title: '订单号'}
                    , {field: 'chanpin_title', title: '产品名称'}
                    , {field: 'customer_title', title: '客户'}
                    , {field: 'price', title: '单价'}
                    , {field: 'money', title: '总价'}
                    , {field: 'delivery_date', title: '交货日期'}
                    , {field: 'delivery_type', title: '交货方式'}
                    , {field: 'send_type', title: '发货方式'}
                    , {field: 'stock_number', title: '库存', sort: true}
                    , {field: 'number', title: '合同总数', sort: true}
                    , {field: 'no_plan_number', title: '未安排', }
                    , {field: 'is_plan_number', title: '已安排', }
                    , {field: 'is_send_number', title: '已发货', }
                    , {field: 'no_product_number', title: '未生产', }
                    , {field: 'is_product_number', title: '已生产', }
                    , {
                        width: 100,
                        align: 'center',
                        fixed: 'right',
                        title: '操作',
                        toolbar: '#test-table-operate-barDemo'
                    }
                ]],
            });

            function getOrderInfo(id) {
                var index = layui.layer.open({
                    id: 'order-info-dialog',
                    type: 2,
                    area: ['80%','96%'],
                    content: '/mobile/product/orderInfo?id='+id,
                    title: '订单详情',
                    resize: false
                });
            }
            table.on('tool(table)', function (obj) {
                var layEvent = obj.event,
                    data = obj.data;
                if (layEvent === 'edit') {
                    //编辑
                    getOrderInfo(data.id);
                }
            });



        });
    </script>

    @endsection