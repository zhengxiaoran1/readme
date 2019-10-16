<div class="bjui-pageHeader" style="background-color:#fefefe; border-bottom:none;">
    <form data-toggle="ajaxsearch" data-options="{searchDatagrid:$.CurrentNavtab.find('#datagrid-test-filter')}">
        <fieldset>
            <legend style="font-weight:normal;">Role Search：</legend>
            <div style="margin:0; padding:1px 5px 5px;">
                <span>Role Name：</span>
                <input type="text" name="obj.role" class="form-control" size="15">
                &nbsp;&nbsp;&nbsp;&nbsp;
                <div class="btn-group">
                    <button type="submit" class="btn-green" data-icon="search">开始搜索！</button>
                    <button type="reset" class="btn-orange" data-icon="times">重置</button>
                </div>
            </div>
        </fieldset>
    </form>
</div>
<div class="bjui-pageContent">
    <table class="table table-bordered" id="datagrid-test-filter" data-toggle="datagrid" data-options="{
        height: '100%',
        gridTitle : '菜单管理',
        showToolbar: true,
        toolbarItem: 'add,|,edit,|,del',
        {{--dataUrl: 'http://b-jui.com/demo?callback=?',--}}
        jsonPrefix: 'obj',
        {{--editUrl: '../../html/datagrid/datagrid-edit.html?code={code}',--}}
        paging: {showPagenum:10, selectPageSize:'20,50,100', pageCurrent:1, pageSize:20},
        showCheckboxcol: true,
        linenumberAll: true,
    }">
        <thead>
        <tr>
            <th data-options="{name:'regdate',align:'center',type:'select',items:function(){return $.getJSON('http://b-jui.com/demo/listDepart?callback=?')},itemattr:{value:'deptcode',label:'deptname'}}">顶部菜单</th>
            <th data-options="{name:'regdate',align:'center',type:'select',items:function(){return $.getJSON('http://b-jui.com/demo/listDepart?callback=?')},itemattr:{value:'deptcode',label:'deptname'}}">左侧菜单</th>
            <th data-options="{name:'order',align:'center'}">菜单名</th>
            <th data-options="{name:'regname',align:'center'}">英文名</th>
            <th data-options="{name:'deptcode',align:'center'}">访问地址</th>
            <th data-options="{name:'regfee',align:'center',width:60}">排序值</th>
            <th data-options="{name:'name',align:'center',width:70}">默认激活</th>
            <th data-options="{name:'sex',align:'center',width:45,type:'select',items:[{'true':'男'},{'false':'女'}]}">是否显示</th>
        </tr>
        </thead>
    </table>
</div>