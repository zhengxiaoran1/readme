
<div class="bjui-pageContent">
    <button type="button" class="btn-green" data-toggle="dialog" data-options="{
            id:'abnormal-field-add-dialog',
            url:'/admin/abnormal/addAbnormalField',
            data:'',
            width:800,
            height:500,
            title:'添加'
        }">
        添加
    </button>
    <table class="table table-bordered" data-toggle="datagrid" data-options="{
        height: '100%',
        gridTitle : '异常字段设置',
        showToolbar: false,
        toolbarItem: 'add',
        editMode: 'inline',
        local: 'local',
        dataUrl: '/admin/abnormal/getMaterialTypeList',
        postData:'',
        editUrl: '/admin/abnormal/saveAbnormalField',
        delUrl: '/admin/abnormal/delAbnormalField',
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
            <th data-options="{name:'field_name',align:'center',width:100}">字段名称</th>
            <th data-options="{name:'field_type',align:'center',width:100,type:'select',items:{{$fieldType}},edit:false}">字段类型</th>
            <th data-options="{name:'source_type',align:'center',width:100,type:'select',items:{{$source_type}},edit:false}">字段值来源</th>
            <th data-options="{name:'field_value',align:'center',width:100,edit:false}">字段值</th>
            {{-- wei 注释20190920<th data-options="{align:'center',width:100,render:selectCategory}">字段相关</th>--}}
        </tr>
        </thead>
    </table>
</div>
<script>
    function selectCategory(value, data){
        var category_html  = '<button type="button" class="btn btn-info" data-toggle="dialog" data-options="';
        category_html  += '{id:\'process-dict-category-dialog\',title:\''+data.field_name+'选择材料分类\',';
        category_html  += 'fresh:true,';
        category_html  += 'height:500,width:800,';
        category_html  += 'url:\'/admin/abnormal/categoryList\',data:{id:' + data.id + '}}';
        category_html  += '">选择材料分类</button>';
        var html    = '';
        if( data.field_type == 'material' ){
            html    = category_html;
        }
        return html;
    }
</script>