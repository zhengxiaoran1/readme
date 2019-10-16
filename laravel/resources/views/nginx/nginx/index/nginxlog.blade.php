{{--<div class="bjui-pageHeader" style="background-color:#fefefe; border-bottom:none;">--}}
    {{--<form data-toggle="ajaxsearch" data-options="{searchDatagrid:$.CurrentNavtab.find('#datagrid-test-filter')}">--}}
        {{--<fieldset>--}}
            {{--<legend style="font-weight:normal;">页头搜索：</legend>--}}
            {{--<div style="margin:0; padding:1px 5px 5px;">--}}
                {{--<span>门诊号：</span>--}}
                {{--<input name="obj.code" class="form-control" size="15" type="text">--}}

                {{--<span>姓名：</span>--}}
                {{--<input name="obj.name" class="form-control" size="15" type="text">--}}

                {{--<div class="btn-group">--}}
                    {{--<button type="submit" class="btn-green" data-icon="search">开始搜索！</button>--}}
                    {{--<button type="reset" class="btn-orange" data-icon="times">重置</button>--}}
                {{--</div>--}}
            {{--</div>--}}
        {{--</fieldset>--}}
    {{--</form>--}}
{{--</div>--}}
<div class="bjui-pageContent">
    <table class="table table-bordered" id="datagrid-test-filter" data-toggle="datagrid" data-options="{
            height: '100%',
            gridTitle : 'nginx访问日志',
            showToolbar: true,
            {{--toolbarItem: 'add,edit,del,|,import,export,exportf',--}}
            dataUrl: 'admin/nginx/nginxLogData?callback=?',
            dataType: 'jsonp',
            jsonPrefix: '',
            paging: {pageSize:100},
            showCheckboxcol: true,
            linenumberAll: true,
        }">
        <thead>
        <tr>
            <th data-options="{name:'visitIp',width:150,type:'select',items:function(){return $.getJSON('/admin/nginx/getIpUserSelect?callback=?')},itemattr:{value:'visitIp',label:'visitUser'}}">访问用户</th>
            <th data-options="{name:'visitIp',width:150}">访问ip</th>
            <th data-options="{name:'visitHost',width:150}">域名</th>
            <th data-options="{name:'visitUri'}">访问uri</th>
            <th data-options="{name:'visitTime',width:200}">访问时间</th>
            <th data-options="{name:'visitMethod'}">访问方式</th>
            <th data-options="{name:'visitStatusCode'}">访问状态码</th>
            {{--<th data-options="{name:'visitBrowser'}">访问浏览器信息</th>--}}
            <th data-options="{name:'visitGetParam'}">访问get值</th>
            <th data-options="{name:'visitPostParam',width:200}">访问post值</th>

        </tr>
        </thead>
    </table>
</div>
<script>
    function orderDetail(value, data){
        var html = '<button type="button" class="btn btn-info" data-toggle="navtab" data-options="{id:\'permission_navtab\',fresh:true, url:\'/admin/order/getOrderProcessList\', title:\'订单工序列表\', data:{order_id:' + data.id + '}}"><i class="fa fa-sitemap"></i> 查看订单工序信息</button>';
        html += '<button type="button" class="btn btn-green" data-toggle="dialog" data-options="{id:\'order-detail-dialog\',fresh:true, url:\'/admin/order/getOrderDetail\', title:\'查看订单详情\',width:800,height:500, data:{order_id:' + data.id + '}}"><i class="fa fa-edit"></i> 查看主订单详情</button>';
        return html
    }
</script>