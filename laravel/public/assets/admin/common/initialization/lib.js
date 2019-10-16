/**
 * 当前 navTab 或 dialog 关闭时，切换到指定 navTab
 * @param navTabId string 需要切换到的navTab的ID
 * @param type string 窗口类型，navTab(默认),dialog
 */
function switchNavTab(navTabId, type) {
    type = type || 'navTab';

    var object;
    var selector;

    if (type === 'navTab') {
        object = $.CurrentNavtab;
        selector = 'bjui.beforeCloseNavtab';
    } else {
        object = $.CurrentDialog;
        selector = 'bjui.beforeCloseDialog';
    }

    object.on(selector, function (event) {
        BJUI.navtab('switchTab', navTabId);
    });
}

/**
 * 刷新指定 DataGrid
 * @param json object 后台返回结果集
 * @param options object data-options参数集合
 */
function refreshDataGrid(json, options) {
    var selector = '#' + json.extend.dataGridId;
    var filterFlag = true;
    if (json.extend.filterFlag === false) filterFlag = false;
    $(selector).datagrid('refresh', filterFlag);
}

/**
 * 查看图片原图
 * @param imageUrl string img标签对象
 */
function viewOriginalImage(imageUrl) {
    BJUI.dialog({
        id: 'view-original',
        width: 800,
        height: 600,
        fresh: true,
        max: true,
        mask: true,
        image: imageUrl,
        title: '查看原图'
    })
}