<template>
    <table class="table table-bordered" data-toggle="datagrid" :data-options="JSON.stringify(options)">
        <thead>
            <tr>
                <th :data-options="JSON.stringify(columnOptions.username)">用户名</th>
                <th :data-options="JSON.stringify(columnOptions.email)">Email</th>
                <th :data-options="JSON.stringify(columnOptions.status)">状态</th>
                <th :data-options="JSON.stringify(columnOptions.registerTime)">注册日期</th>
                <th :data-options="JSON.stringify(columnOptions.role)">角色分配</th>
            </tr>
        </thead>
    </table>
</template>

<script>
    export default {
            data: function () {
                return {
                    options: {
                        height: '100%',
                        gridTitle : '用户列表',
                        showToolbar: true,
                        toolbarItem: 'add',
                        local: 'local',
                        dataUrl: 'admin/user/list',
                        editUrl: '/admin/user/save-user',
                        delUrl: '/admin/user/del-user',
                        delPK: 'id',
                        linenumberAll: true,
                        sortAll: false,
                        contextMenuH: true,
                        contextMenuB: true,
                        inlineEditMult: false,
                        paging: {showPagenum:10, selectPageSize:'20,50,100', pageCurrent:1, pageSize:20},
                        showEditbtnscol: '操作',
                        customEditbtns: {width:200,position:'replace',buttons:operationColumn},
                        fullGrid: true,
                        columnMenu: false
                    },
                    columnOptions: {
                        username: {
                            name:'username',
                            align:'center',
                            width:100,
                            edit:false
                        },
                        email: {
                            name:'email',
                            align:'center',
                            width:200
                        },
                        status: {
                            name:'status',
                            align:'center',
                            width:50,
                            type:'select',
                            items:[{'10':'正常'},{'0':'禁止'}]
                        },
                        registerTime: {
                            name:'register_time',
                            align:'center',
                            width:150,
                            type:'date',
                            pattern:'yyyy-MM-dd',
                            add:false,
                            edit:false
                        },
                        role: {
                            align:'center',
                            width:150,
                            render:roleAssignment
                        }
                    }
                }
            }
        }

    function roleAssignment(value, data) {
        var html = '<button type="button" class="btn btn-info" data-toggle="navtab" data-options="{id:\'role_navtab\', url:\'/admin/user/role-assignment\', title:\'角色分配\', data:{userId:' + data.id + '}}"><i class="fa fa-sitemap"></i> 角色分配</button>';

        return html;
    }

    function operationColumn() {
        var html = ' <button type="button" class="btn btn-green bjui-datagrid-btn edit"><i class="fa fa-edit"></i> 编辑</button>'
                + ' <button type="button" class="btn btn-green bjui-datagrid-btn update"><i class="fa fa-edit"></i> 更新</button>'
                + ' <button type="button" class="btn btn-green bjui-datagrid-btn save"><i class="fa fa-check"></i> 保存</button>'
                + ' <button type="button" class="btn btn-orange bjui-datagrid-btn cancel"><i class="fa fa-undo"></i> 取消</button>'
                + ' <button type="button" class="btn btn-red bjui-datagrid-btn delete"><i class="fa fa-remove"></i> 删除</button>'

        return html
    }
</script>
