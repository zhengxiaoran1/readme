function editCallback( json ){
    BJUI.navtab('reload');
}
function editButton(){
    var html = '<button type="button" class="btn btn-green bjui-datagrid-btn edit"><i class="fa fa-edit"></i>编辑</button>';
    html += '<button type="button" class="btn btn-green bjui-datagrid-btn update"><i class="fa fa-edit"></i>更新</button>';
    html += '<button type="button" class="btn btn-green bjui-datagrid-btn save"><i class="fa fa-check"></i>保存</button>';
    html += '<button type="button" class="btn btn-orange bjui-datagrid-btn cancel"><i class="fa fa-undo"></i>取消</button>';
    return html;
}
function delButton(){
    html = '<button type="button" class="btn btn-red bjui-datagrid-btn delete"><i class="fa fa-remove"></i>删除</button>';
    return html;
}
function dialogCallbackClose()
{
    BJUI.dialog('closeCurrent')
}