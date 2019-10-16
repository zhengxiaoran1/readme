<table id="depart-lists-table" data-toggle="datagrid" data-options="{
    width: '100%',
    height: '100%',
    gridTitle: '树状datagrid 示例 ',
    showToolbar: true,
    toolbarItem:'add, cancel, del, save',
    local: 'local',
    dataUrl: 'admin/menu-list',
    inlineEditMult: false,
    editUrl: 'admin/menu-edit',
    delUrl: 'admin/menu-delete',
    isTree: 'name',
    addLocation: 'last',
    fieldSortable: false,
    columnMenu: false,
    paging: false,
    treeOptions: {
        expandAll: false,
        add: true,
        simpleData: true,
        keys: {
            parentKey: 'pid'
        }
    },
    dropOptions: {
        drop: true,
        position: 'before',
        dropUrl: '../../json/ajaxDone.json',
        beforeDrag: datagrid_tree_beforeDrag,
        beforeDrop: datagrid_tree_beforeDrop,
        afterDrop: 'array'
    }
}">
    <thead>
    <tr>
        <th data-options="{name:'name', align:'center', width:300, rule:'required'}">名称</th>
        <th data-options="{name:'url', align:'center', width:300, rule:'required'}">链接</th>
        <th data-options="{render:datagrid_tree_operation}">操作列</th>
    </tr>
    </thead>
</table>
<script type="text/javascript">
    function datagrid_tree_department() {
        return [{1:'显示'},{0:'不显示'}]
    }
    // 操作列
    function datagrid_tree_operation() {
        var html = '<button type="button" class="btn-green" data-toggle="edit.datagrid.tr">编辑</button>'
            + '<button type="button" class="btn-red" data-toggle="del.datagrid.tr">删除</button>'

        return html
    }
    function datagrid_image_tag(value,data){
        return '<img src="'+value+'">'
    }
    //不能拖动一级父节点
    function datagrid_tree_beforeDrag(tr, data) {
        if (data && data.level == 0) {
            return false
        }

        return true
    }
    // 不能将子节点拖为一级父节点
    function datagrid_tree_beforeDrop(data, targetData, position) {
        if (targetData && targetData.level == 0 && position !== 'append') {
            return false
        }

        return true
    }
</script>