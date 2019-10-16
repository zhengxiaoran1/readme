
<div class="bjui-pageContent">
    <button type="button" class="btn-green" data-toggle="dialog" data-options="{
            id:'abnormal-add-dialog',
            url:'/admin/abnormal/addAbnormal',
            data:'',
            width:800,
            height:500,
            title:'添加'
        }">
        添加
    </button>
    <table class="table table-bordered" data-toggle="datagrid" data-options="{
        height: '100%',
        gridTitle : '异常设置',
        showToolbar: false,
        toolbarItem: 'add',
        editMode: 'inline',
        local: 'local',
        dataUrl: '/admin/abnormal/getAbnormalList',
        postData:'',
        editUrl: '/admin/abnormal/saveAbnormal',
        delUrl: '/admin/abnormal/delAbnormal',
        delPK: 'id',
        linenumberAll: true,
        sortAll: false,
        contextMenuH: true,
        contextMenuB: true,
        inlineEditMult: false,
        paging: {showPagenum:10, selectPageSize:'20,50,100', pageCurrent:1, pageSize:20},
        showEditbtnscol: '操作',
        customEditbtns: {width:200,position:'replace'},
        fullGrid: true,
        fieldSortable: false,
        filterThead: false,
        columnMenu: false,
        }">
        <thead>
        <tr>
            {{--<th data-options="{name:'id',align:'center',width:100,edit:false}">序号</th>--}}
            <th data-options="{name:'title',align:'center',width:100}">异常名称</th>
            <th data-options="{name:'abnormal_type_title',align:'center',width:100,edit:false}">异常类型</th>
            <th data-options="{name:'abnormal_related',align:'center',width:100,edit:false}">异常相关</th>
            <th data-options="{ render:'set_material_formula', align:'center',width:100,edit:false}">预警公式</th>
            <th data-options="{ name:'department_id', edit:false, render:'set_permission', align:'center',width:100}">部门设置</th>
            <th data-options="{align:'center',render:field_manage}">关联管理</th>
        </tr>
        </thead>
    </table>
</div>
<script>
    function set_material_formula(value, data){
        html = '<button type="button" class="btn btn-green" data-toggle="dialog" data-options="{id:\'set_material_formula\',fresh:true, url:\'/admin/abnormal/setAbnormalFormula\', title:\'设置预警公式\',width:500,height:500, data:{id:' + data.id + ',rule:\'' + data.rule + '\',sort:\''+data.sort+'\',type:\''+data.type+'\'}}"><i class="fa fa-edit"></i> 设置</button>';
        return html;
    }

    function set_permission(value, data){
        html = '<button type="button" class="btn btn-green" data-toggle="dialog" data-options="{id:\'set_permission\',fresh:true, url:\'/admin/abnormal/setAbnormalDepartment\', title:\'设置需预警部门\',width:500,height:500, data:{id:' + data.id + ',department_id:\'' + data.department_id + '\'}}"><i class="fa fa-edit"></i> 设置</button>';
        return html;
    }

    function field_manage(value, data)
    {
        var field_html      = '<button type="button" class="btn btn-info" data-toggle="dialog" data-options="';
        field_html      += '{id:\'abnormal-field-edit-dialog\',title:\''+data.title+'-字段分配\',';
        field_html      += 'fresh:true,width:750,height:500,';
        field_html      += 'url:\'/admin/abnormal/field-edit\',data:{id:' + data.id + '}}';
        field_html      += '"> 字段分配</button>';

        return field_html;
    }


</script>