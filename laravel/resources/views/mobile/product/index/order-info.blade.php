@extends('mobile.layouts.master')
@section('content')
<style type="text/css">
    .layui-input-block{
        line-height: 36px;
    }
</style>

    <div class="layui-fluid">
        <div class="layui-row layui-col-space15">
            <div class="layui-col-md12">
                <div class="layui-card">
                    <div class="layui-card-header">订单详情</div>
                    <div class="layui-card-body " id="order-info"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="layui-fluid">
        <div class="layui-row layui-col-space15">
            <div class="layui-col-md12">
                <div class="layui-card">
                    <div class="layui-card-header">产品列表</div>
                    <div class="layui-card-body " >

                        <table class="layui-table">
                            <colgroup>
                                <col width="150">
                                <col width="150">
                                <col width="200">
                                <col>
                            </colgroup>
                            <thead>
                            <tr>
                                <th>图片</th>
                                <th>产品名称</th>
                                <th>产品类型</th>
                                <th>片料规格</th>
                                <th>成品克重</th>
                                <th>成品规格</th>
                                <th>已生产</th>
                                <th>未生产</th>
                                <th>打包条数</th>
                                <th>数量</th>
                            </tr>
                            </thead>
                            <tbody id="chanpin-list">

                            </tbody>
                        </table>



                    </div>
                </div>
            </div>
        </div>
    </div>
<script type="application/javascript">
    function getOrderInfo(){


        ygt_control.token = "{{session('user.token')}}";
        ygt_control.data = {chanpin_order_id:'{{request('id')}}'};
        ygt_control.url  = "/api/service/chanpin/getChanpinOrderDetail";
        ygt_control.ajax(function(data){
            var _order_info = template('order_info', data);
            $('#order-info').html("").append(_order_info);
            var _chanpin_list = template('chanpin_list', data);
            $('#chanpin-list').append(_chanpin_list);
        });
    }
    getOrderInfo();
</script>
{{--产品列表--}}
<script type="text/html" id="chanpin_list">
    <%for( i = 0;i < data.chanpin_list.length; i++){%>
    <% var obj = data.chanpin_list[i] %>

        <tr>
            <td><img src="<%= obj.img_url %>" alt="" width='35px' height='35px'></td>
            <td><%= data.product_name %></td>
            <td><%= obj.ordertype_title_value %></td>
            <td><%= obj.pianliaoguige %></td>
            <td><%= obj.chengpinkezhong %></td>
            <td><%= obj.chengpinguige %></td>
            <td><%= obj.is_product_number %></td>
            <td><%= obj.no_product_number %></td>
            <td><%= obj.pack %></td>
            <td><%= obj.number %></td>
        </tr>
        <% if(obj.desc != ''){ %>
            <tr>
                <td colspan="11">备注：<%= obj.desc %></td>
            </tr>
        <% } %>

    <%}%>
</script>




{{--订单信息--}}
<script type="text/html" id="order_info">
    <div class="layui-row layui-col-space10 layui-form-item">
        <div class="layui-col-lg1">
            <img src="<%= data.img_url %>" style="width: 80px;height: 80px;"/>
        </div>
        <div class="layui-col-lg3">
            <label class="layui-form-label">客户名称：</label>
            <div class="layui-input-block">
                <%= data.customer_title %>
            </div>
        </div>
        <div class="layui-col-lg3">
            <label class="layui-form-label">单位名称：</label>
            <div class="layui-input-block">
                <%= data.buyer_title %>
            </div>
        </div>

        <div class="layui-col-lg3">
            <label class="layui-form-label">订单编号：</label>
            <div class="layui-input-block">
                <%= data.sn %>
            </div>
        </div>
        <div class="layui-col-lg2">
            <label class="layui-form-label">创建时间：</label>
            <div class="layui-input-block">
                <%= data.create_date %>
            </div>
        </div>

        <div class="layui-col-lg3">
            <label class="layui-form-label">产品类型：</label>
            <div class="layui-input-block">
                <%= data.order_type_title %>
            </div>
        </div>
        <div class="layui-col-lg7">
            <label class="layui-form-label">产品名称：</label>
            <div class="layui-input-block">
                <%= data.chanpin_title %>
            </div>
        </div>
    </div>

    <div class="layui-row layui-col-space10 layui-form-item" style="border-top: 1px solid #efefef;">
        <div class="layui-col-lg3">
            <label class="layui-form-label">结算方式：</label>
            <div class="layui-input-block">
                <%= data.settlement_method %>
            </div>
        </div>
        <div class="layui-col-lg3">
            <label class="layui-form-label">交货方式：</label>
            <div class="layui-input-block">
                <%= data.delivery_type %>
            </div>
        </div>
         <div class="layui-col-lg3">
            <label class="layui-form-label">物流运输：</label>
            <div class="layui-input-block">
                <%= data.send_type %>
            </div>
        </div>
        <div class="layui-col-lg3">
            <label class="layui-form-label"><% if(data.delivery_type == '定期分批'){ %>首批交货<% }else{ %>交货日期<% } %>：</label>
            <div class="layui-input-block">
                <%= data.delivery_date %>
            </div>
        </div>

        <% if(data.delivery_type == '定期分批'){%>
            <div class="layui-col-lg3">
                <label class="layui-form-label">分批周期：</label>
                <div class="layui-input-block">
                    <%= data.batch_cycle %>
                </div>
            </div>
            <div class="layui-col-lg3">
                <label class="layui-form-label">每交货量：</label>
                <div class="layui-input-block">
                    <%= data.per_delivery_number %>
                </div>
            </div>
        <% } %>

    </div>

    <div class="layui-row layui-col-space10 layui-form-item" style="border-top: 1px solid #efefef;">
        <div class="layui-col-lg12">
            <label class="layui-form-label">所在地址：</label>
            <div class="layui-input-block">
                <%= data.receve_address %>
            </div>
        </div>
        <div class="layui-col-lg12">
            <label class="layui-form-label">联系方式：</label>
            <div class="layui-input-block">
                <%= data.user_address %>
            </div>
        </div>
    </div>
</script>
    @endsection