<form data-toggle="ajaxform" action="admin/company/setUserPurchaseManage" data-options="{callback:after_set_process_action}">

    <div class="bjui-pageContent" style="width:95%">
        <table id="j_datagrid_tree" data-toggle="datagrid" data-options="{
    width: '100%',
    height: '95%',
    gridTitle: '材料分类列表 ',
    {{--showToolbar: true,--}}
                showCheckboxcol: true,
{{--toolbarItem:'add, cancel, del, save',--}}
        {{--editMode: 'dialog',--}}
                local: 'local',
                dataUrl: 'admin/abnormal/categoryList',
                inlineEditMult: false,
                isTree: 'cat_name',
                addLocation: 'last',
                fieldSortable: false,
                columnMenu: false,
                filterThead: false,
                paging: {pageSize:9999, selectPageSize:'100', pageCurrent:1, showPagenum:5, totalRow:0},
{{--paging: false,--}}
                treeOptions: {
                    expandAll: false,
                    add: false,
                    simpleData: true,
                    keys: {
                        parentKey: 'pid',
                        order:'sort_id'
                    }
                }
                }">
            <thead>
            <tr>
                <th data-options="{name:'id',align:'center'}">cat_id</th>
                <th data-options="{name:'cat_name', align:'center', width:300, rule:'required'}">分类名称</th>
                <th data-options="{name:'sort_id', align:'center', width:300, rule:'required'}">排序</th>
                <th data-options="{name:'company_id', align:'center', width:300, rule:'required', edit:false, add:false}">厂</th>
                {{--<th data-options="{name:'image_path', align:'center',render:'datagrid_image_tag', width:300, rule:'required'}">分类图片</th>--}}
                {{--<th data-options="{name:'display', align:'center', width:300, type:'select', items:datagrid_tree_department}">是否显示</th>--}}
                {{--<th data-options="{render:datagrid_tree_operation}">操作列</th>--}}
            </tr>
            </thead>
        </table>
    </div>


    <div class="bjui-pageFooter" style="width:95%">
        <ul>
            <li><button type="button" class="btn-default" data-icon="save" onclick="mySubmit()">提交</button></li>
            <li><button type="button" class="btn-close" data-icon="close">取消</button></li>
        </ul>
    </div>

</form>


<script>
    //加载完毕后
    $(document).on(BJUI.eventType.afterInitUI, function(event) {

        $('.datagrid-tree-tr').unbind('click');

        var categoryIds = '{{$checkCategoryIds}}';
        var categoryIdArr = [];
        if(categoryIds){
            categoryIdArr = categoryIds.split(',');
        }

        var trList = $('#j_datagrid_tree .datagrid-tree-tr');
        trList.each(function () {
            //去除一级分类的勾选框
            if ($(this).attr('data-child') != 0){
                $(this).find('td').eq(1).children().hide();
            }


            //默认选中材料分类
            var curId = $(this).find('td').eq(2).text();
            if(in_array(curId,categoryIdArr)){
                //打上勾
                if(!$(this).find('.icheckbox_minimal-purple.checked').length){//已打勾的不处理
                    $(this).find('.iCheck-helper').trigger('click');
                }
            }else{
                if($(this).find('.icheckbox_minimal-purple.checked').length){//已打勾的不处理
                    $(this).find('.iCheck-helper').trigger('click');
                }
            }
        });
    });


    function mySubmit(){
        //获取被选中的材料ID
        var c = $('#j_datagrid_tree .icheckbox_minimal-purple.checked');
        var checkCategoryArr = [];
        c.each(function () {
            var tmpCheckCategeryId = $(this).parents('.datagrid-checkbox-td').next('td').text();
            checkCategoryArr.push(tmpCheckCategeryId);
        });

        var id = '{{$id}}';

        //提交数据
        $.ajax({
            type: "POST",
            url: "admin/abnormal/bindMaterial",
            data: {
                id:id,
                checkCategoryArr:checkCategoryArr,
            },
            success: function(data){
                BJUI.dialog('close', 'process-dict-category-dialog');
            }
        });

    }


    function after_set_process_action(){
//        BJUI.dialog('closeCurrent')
//        BJUI.navtab('refresh')
    }

    //    $(function () {
    //        $('body').on('click', '.datagrid-tree-tr', function (e) {
    //            if ($(e.target).siblings().hasClass('datagrid-checkbox-td')) { //判断是否是多选
    //                var n = $(e.target).parents('.datagrid-tree-tr').attr('data-child');
    //                var m = $(e.target).parents('.datagrid-tree-tr').nextAll('.datagrid-tree-tr:lt(' + n + ')');
    //                var l = parseFloat($(e.target).parents('.datagrid-tree-tr').attr('data-level'));
    //                if ($(e.target).parents('.datagrid-tree-tr').hasClass('datagrid-selected-tr')) {
    //                    m.each(function () {
    //                        if ($(this).attr('data-child') == 0 && !$(this).hasClass('datagrid-selected-tr')) {
    //                            if (l + 1 == parseFloat($(this).attr('data-level'))) {
    //                                if ($(this).children('td:last-child').children("div")[0].innerHTML != '') {
    //                                    $(this).find('.iCheck-helper').trigger('click');
    //                                }
    //                            }
    //                        }
    //                    })
    //                }
    //                else {
    //                    m.each(function () {
    //                        if ($(this).attr('data-child') == 0 && $(this).hasClass('datagrid-selected-tr')) {
    //                            if (l + 1 == parseFloat($(this).attr('data-level'))) {
    //                                if ($(this).children('td:last-child').children("div")[0].innerHTML != '') {
    //                                    $(this).find('.iCheck-helper').trigger('click');
    ////                                    $(this).trigger('click');
    //                                }
    //                            }
    //                        }
    //                    })
    //                }
    //
    //
    //            }
    //        });

    //        $('body').on('ifChanged', '.datagrid-checkbox-td input', function (e) {
    //            var n = $(e.target).parents('.datagrid-tree-tr').attr('data-child')
    //            var m = $(e.target).parents('.datagrid-tree-tr').nextAll('.datagrid-tree-tr:lt(' + n + ')')
    //            var l = parseFloat($(e.target).parents('.datagrid-tree-tr').attr('data-level'));
    //            if ($(e.target).parents('.datagrid-tree-tr').hasClass('datagrid-selected-tr')) {
    //                m.each(function () {
    //                    if ($(this).attr('data-child') == 0 && !$(this).hasClass('datagrid-selected-tr')) {
    //                        if (l + 1 == parseFloat($(this).attr('data-level'))) {
    //                            if ($(this).children('td:last-child').children("div")[0].innerHTML != '') {
    ////                                $(this).trigger('click')
    //                                $(this).find('.iCheck-helper').trigger('click');
    //                            }
    //                        }
    //                    }
    //                })
    //            } else {
    //                m.each(function () {
    //                    if ($(this).attr('data-child') == 0 && $(this).hasClass('datagrid-selected-tr')) {
    //                        if (l + 1 == parseFloat($(this).attr('data-level'))) {
    //                            if ($(this).children('td:last-child').children("div")[0].innerHTML != '') {
    ////                                $(this).children('td').trigger('click')
    //                                $(this).find('.iCheck-helper').trigger('click');
    //                            }
    //                        }
    //                    }
    //                })
    //            }
    //        });

    //    });




    function in_array(search,array){
        for(var i in array){
            if(array[i]==search){
                return true;
            }
        }
        return false;
    }



</script>