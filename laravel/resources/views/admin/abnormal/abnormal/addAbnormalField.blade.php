<form data-toggle="ajaxform" action="/admin/abnormal/addAbnormalField" data-options="{callback:'after_add_abnormal'}">

    <div class="bjui-row col-2" >
        <div class="row-input" >字段名称</div>
        <div class="row-input" >
            <input type="text" name="field_name" value="" aria-required="true">
        </div>
    </div>

    <div class="bjui-row">
        <div class="row-input "style="display: block">选择字段类型</div>
        <div class="bjui-row col-2">
            @foreach ($type as $val)
                @foreach($val as $k=>$v)
                <div class="row-input">
                    <input type="radio" name="field_type" value="{{$k}}" data-toggle="icheck" data-label="{{$v}}" id="field_type_{{$k}}">
                </div>
                @endforeach
            @endforeach
        </div>
    </div>

    <div class="bjui-row">
        <div class="row-input "style="display: block">选择字段值来源</div>
        <div class="bjui-row col-2">
            <div class="row-input">
                <input type="radio" name="source_type" id="system" value="1" data-toggle="icheck" data-label="系统字段" >
            </div>
            <div class="row-input">
                <input type="radio" name="source_type" id="calculate" value="0" data-toggle="icheck" data-label="计算字段" >
            </div>
        </div>
    </div>
    @foreach($system as $key=>$val)
    <div class="bjui-row" style="display: none" id="{{$key}}" name="value">
        <div class="row-input "style="display: block">选择字段值</div>
        <div class="bjui-row col-2">
            <div class="row-input">
                <select name="system_value_{{$key}}" data-toggle="selectpicker">
                    <option value="">请选择</option>
                    @foreach($val as $v )
                        <option value="{{$v['id']}}">{{$v['name']}}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    @endforeach
    @foreach($calculate as $key=>$val)
    <div class="bjui-row" style="display: none" id="{{$key}}_calculate" name="value">
        <div class="row-input "style="display: block">选择字段值</div>
        <div class="bjui-row col-2">
            <div class="row-input">
                <select name="calculate_value_{{$key}}" data-toggle="selectpicker">
                    <option value="">请选择</option>
                    @foreach($val as $v )
                        <option value="{{$v['id']}}">{{$v['name']}}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    @endforeach



    <div class="btn-group">
        <button type="submit" class="btn-green" data-icon="search">保存</button>
    </div>
</form>
<script>

    function after_add_abnormal(_result){
        if(_result.statusCode == 300){
            alert(_result.message);
        }else{
            BJUI.dialog('closeCurrent')
            BJUI.navtab('refresh')
        }
    }

    $("#system").on('ifChecked',function () {
        var item = $("input[name='field_type']:checked").val();

        $("[name='value']").attr('style','display:none');
        $("#"+item).attr('style','display:block');


    })
    $("#calculate").on('ifChecked',function () {
        var item = $("input[name='field_type']:checked").val();
        $("[name='value']").attr('style', 'display:none');
        $("#"+item+"_calculate").attr('style', 'display:block');

    })

    $("input[name='field_type']").on('ifChecked',function () {
        var item = $("input[name='source_type']:checked").val();
        var value = $(this).val();
        if (item == 1){
            $("[name='value']").attr('style', 'display:none');
            $("#"+value).attr('style','display:block');
        }
        if (item == 0){
            $("[name='value']").attr('style', 'display:none');
            $("#"+value+"_calculate").attr('style', 'display:block');
        }
    })



</script>