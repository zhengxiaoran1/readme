<div class="bjui-pageContent" style="height:50%">
<form action="/nginx/virutaVisitsData" id="viruta_visits_navtab" data-toggle="ajaxform" data-alertmsg="false">
<div class="bjui-row col-2">
    <div style = 'width:100%'>
        <label class="row-label">访问地址</label>
        <div class="row-input">
            <input name="visit_host" type="text" value="">
        </div>
    </div>

    <div style = 'width:100%'>
        <label class="row-label">用户id</label>
        <div class="row-input ">
            <input name="uid" type="text" value="">
        </div>
    </div>

    <hr></hr>

    <label class="row-label">字段一</label>
    <div class="row-input ">
        <input name="name[]" type="text" value="" style="width:30%">
        <input name="value[]" type="text" value="" style="width:30%">
    </div>

    <label class="row-label">字段二</label>
    <div class="row-input ">
        <input name="key[]" type="text" value="" style="width:30%">
        <input name="value[]" type="text" value="" style="width:30%">
    </div>

</div>
</form>
</div>

<div class="bjui-pageFooter" style="height:50%">
    <ul>
        <li><button type="button" class="btn-default" data-icon="save" onclick="createOrderSubmit();">保存</button></li>
        <li><button type="button" class="btn-close" data-icon="close">取消</button></li>
    </ul>
</div>

<script type="text/javascript">
    function createOrderSubmit() {
//        BJUI.ajax('ajaxform', {
//            url: '/admin/order/createOrderSubmit',
//            form: $.CurrentNavtab.find('#create_order'),
//            validate: false,
//            loadingmask: true,
//            callback: function (json) {
//                if(json.code  == 0){
//                    BJUI.navtab('closeTab','create_order_navtab');
//                    //打开订单列表页
//                    BJUI.navtab({
//                        id: 'order-list',
//                        url: '/admin/order/getOrderList',
//                        title: '订单列表'
//                    })
//                }else{
//                    BJUI.alertmsg('info', json.message);
//                }
//            }
//        });

        var obj = $("input[name='key']");
        BJUI.dialog({
            id:'viruta_visits_data',
//            form: $.CurrentNavtab.find('#viruta_visits_navtab'),
            data: {'key':obj},
            url:'/nginx/virtualVisitsData',
            title:'模拟访问数据'
        })



    }

</script>