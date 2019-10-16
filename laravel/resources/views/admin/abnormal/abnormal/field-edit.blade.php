<form action="admin/abnormal/field-edit" data-toggle="ajaxform" data-options="{callback:'after_field_edit'}">
    <div class="bjui-row col-3">

        <label class="row-label">异常名称</label>
        <div class="row-input">{{$info['title']}}</div><br>

        <label class="row-label">选择字段</label>
            @foreach($fields as $value)
            <div class="row-input">
                <input type="checkbox" name="field_id[]" id='{{$value['id']}}' value="{{$value['id']}}" data-toggle="icheck" data-label="{{$value['field_name']}}" @if ( in_array($value['id'],explode(',',$info['field_id'])) ) checked @else @endif>
            </div>
        @endforeach
        <br>
        <label class="row-label"></label>
        <div class="row-input">
            <button type="submit" class="btn-default">保存</button>
        </div>
    </div>
    <input type="hidden" name="id" value="{{$id}}">
    <!--<div class="bjui-pageFooter">
        <ul>
            <li><button type="button" class="btn-close" data-icon="close">取消</button></li>
            <li><button type="submit" class="btn-default" data-icon="save">保存</button></li>
        </ul>
    </div>-->
</form>


<script>
    function after_field_edit(_result) {
        if(_result.statusCode == 300){
            alert(_result.message);
        }else{
            BJUI.dialog('closeCurrent');
            BJUI.alertmsg('ok','绑定成功')
        }
    }
</script>