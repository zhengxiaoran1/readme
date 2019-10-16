<form data-toggle="ajaxform" action="/admin/abnormal/setAbnormalDepartment" data-options="{callback:after_set_process_action}">
    <input type="hidden" name="id" value="{{$id}}">
    <div class="bjui-row col-2">
        @foreach ($DdepartmentList as $v)
            <div class="row-input">
                <input type="checkbox" name="actions[]" id="set-process-action-{{$v['id']}}" value="{{$v['id']}}"
                       data-toggle="icheck" data-label="{{$v['title']}}" @if(in_array($v['id'],$department_id))checked="" @endif>
            </div>
        @endforeach
    </div>
    <div class="btn-group">
        <button type="submit" class="btn-green" data-icon="search">保存</button>
    </div>
</form>
<script>
    function after_set_process_action(_result){
        if(_result.statusCode == 300){
            alert(_result.message);
        }else{
            BJUI.dialog('closeCurrent')
            BJUI.navtab('refresh')
        }
    }
</script>