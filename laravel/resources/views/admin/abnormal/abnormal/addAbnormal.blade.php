<form data-toggle="ajaxform" action="/admin/abnormal/addAbnormal" data-options="{callback:'after_add_abnormal'}">

    <div class="bjui-row col-2" >
        <div class="row-input" >异常名称</div>
        <div class="row-input" >
            <input type="text" name="title" value="" >
        </div>
    </div>

    <div class="bjui-row">
        <div class="row-input "style="display: block">选择异常类型</div>
        <div class="bjui-row col-2">
            @foreach ($abnormalType as $val)
                <div class="row-input">
                    <input type="radio" name="sort" types="{{$val['sort']}}" value="{{$val['id']}}" id="{{$val['id']}}" data-toggle="icheck" data-label="{{$val['title']}}">
                </div>
            @endforeach
        </div>
    </div>

    @foreach($type as $k=>$v)
    <div class="bjui-row" style="display: none" id="{{$k}}" name="rule">
        <div class="row-input" style="display: block">选择异常规则</div>
        <div class="bjui-row col-3">
            @if($k=='workSheet')
                @foreach($v as $k1=>$v1)
                    <div class="row-input" >
                        <input type="radio" name="rule_type" id="{{$k1}}" value="{{$k1}}" data-toggle="icheck" data-label="{{$v1}}">
                    </div>
                @endforeach
            @else
                @foreach($v as $k1=>$v1)
                    <div class="row-input" >
                        <input type="radio" name="rule_type" id="{{$k1}}" value="{{$k1}}" data-toggle="icheck" data-label="{{$v1}}">
                    </div>
                @endforeach
            @endif

        </div>
    </div>
    @endforeach

    <div class="bjui-row" style="display: none" id="material_range" name="range">
        <div class="row-input" style="display: block">选择材料范围</div>
        <div class="row-input">
            <select name="cat_id[]" id="cat_id" data-nextselect="#cat2_id" data-toggle="selectpicker" data-refurl="admin/abnormal/getMaterialList?type=2&value={value}" data-width="100" multiple="">
                <option value="" disabled selected>请选择</option>
                <option value="all">全选</option>
                @foreach ($materialList1 as $val)
                    <option value="{{$val['id']}}">{{$val['cat_name']}}</option>
                @endforeach
            </select>
            <select name="cat2_id[]" id="cat2_id" data-nextselect="#material_id" data-toggle="selectpicker" data-refurl="admin/abnormal/getMaterialList?type=3&value={value}" data-width="100"  multiple=''>
                <option value="" disabled selected>请选择</option>
            </select>
            <select name="material_id[]" id="material_id" data-toggle="selectpicker" multiple='' data-width="205">
                <option value="" disabled selected>请选择</option>
            </select>
        </div>
    </div>

    <div class="bjui-row" style="display: none" id="product_aggretage_range" name="range">
        <div class="row-input" style="display: block">选择半成品范围</div>
        <div class="bjui-row  col-2">
            <div class="row-input">
                <input type="checkbox" name="product_aggretage[]" id="product_aggretage_all" value="product_aggretage_all" data-toggle="icheck" data-label="全选">
            </div>
            @foreach($processProduct as $v)
                <div class="row-input">
                    <input type="checkbox" name="product_aggretage[]" id="product_aggretage{{$v['id']}}" value="{{$v['id']}}" data-toggle="icheck" data-label="{{$v['title']}}">
                </div>
            @endforeach
        </div>
    </div>

    <div class="bjui-row" style="display: none" id="product_range" name="range">
        <div class="row-input" style="display: block">选择成品范围</div>
        <div class="bjui-row  col-2">
            <div class="row-input">
                <input type="checkbox" name="product[]" id="product_all" value="product_all" data-toggle="icheck" data-label="全选">
            </div>
            @foreach($chanpinCategory as $v)
                <div class="row-input">
                    <input type="checkbox" name="product[]" id="product{{$v['id']}}" value="{{$v['id']}}" data-toggle="icheck" data-label="{{$v['cat_name']}}">
                </div>
            @endforeach
        </div>
    </div>

    <div class="bjui-row" style="display: none" id="return_product_range" name="range">
        <div class="row-input" style="display: block">选择退品范围</div>
        <div class="row-input">
            <select name="return_cat_id[]" id="return_cat_id" data-nextselect="#return_cat2_id" data-toggle="selectpicker" data-refurl="admin/abnormal/getMaterialList?type=2&value={value}" data-width="100" multiple="">
                <option value="" disabled selected>请选择</option>
                <option value="all">全选</option>
                @foreach ($materialList1 as $val)
                    <option value="{{$val['id']}}">{{$val['cat_name']}}</option>
                @endforeach
            </select>
            <select name="return_cat2_id[]" id="return_cat2_id" data-nextselect="#return_material_id" data-toggle="selectpicker" data-refurl="admin/abnormal/getMaterialList?type=3&value={value}" data-width="100"  multiple=''>
                <option value="" disabled selected>请选择</option>
            </select>
            <select name="return_material_id[]" id="return_material_id" data-toggle="selectpicker" multiple='' data-width="205">
                <option value="" disabled selected>请选择</option>
            </select>
        </div>
    </div>

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
    $("input[name='sort']").on('ifChecked',function () {
        $("[name='rule']").attr('style','display:none');
        $("[name='range']").attr('style','display: none');
        var type = $(this).attr('types');
        $("#"+type).attr('style','display:block');
    })
    $("input[name='rule_type']").on('ifChecked',function () {
        var item = $(this).val();
        $("[name='range']").attr('style','display: none');
        $("#"+item+"_range").attr('style','display: block');
    })
</script>



{{--<form data-toggle="ajaxform" action="/admin/abnormal/addAbnormal" data-options="{callback:'after_add_abnormal'}">

    <div class="bjui-row col-2" >
        <div class="row-input" >异常名称</div>
        <div class="row-input" >
            <input type="text" name="title" value="" >
        </div>
    </div>

    <div class="bjui-row">
        <div class="row-input "style="display: block">选择异常类型</div>
        <div class="bjui-row col-2">
            @foreach ($abnormalType as $val)
                <div class="row-input">
                    <input type="radio" name="sort" types="{{$val['sort']}}" value="{{$val['id']}}" data-toggle="icheck" data-label="{{$val['title']}}">
                </div>
            @endforeach
        </div>
    </div>

    @foreach($type as $k=>$v)
        <div class="bjui-row" style="display: none" id="{{$k}}" name="rule">
            <div class="row-input" style="display: block">@if ($k=='workSheet') 选择材料 @else选择异常规则@endif</div>
            <div class="bjui-row ">
                @if($k=='workSheet')
                    <div class="row-input material">
                        <select name="cat_id[]" id="cat_id" data-nextselect="#cat2_id" data-toggle="selectpicker" data-refurl="admin/abnormal/getMaterialList?type=2&value={value}" data-width="100" multiple="">
                            <option value="" disabled selected>请选择</option>
                            <option value="all">全选</option>
                            @foreach ($materialList1 as $val)
                                <option value="{{$val['id']}}">{{$val['cat_name']}}</option>
                            @endforeach
                        </select>
                        <select name="cat2_id[]" id="cat2_id" data-nextselect="#material_id" data-toggle="selectpicker" data-refurl="admin/abnormal/getMaterialList?type=3&value={value}" data-width="100"  multiple=''>
                            <option value="" disabled selected>请选择</option>
                        </select>
                        <select name="material_id[]" id="material_id" data-toggle="selectpicker" multiple='' data-width="205">
                            <option value="" disabled selected>请选择</option>
                        </select>
                    </div>
                @else
                    @foreach($v as $k1=>$v1)
                        <div class="row-input" >
                            <input type="radio" name="type" value="{{$k1}}" data-toggle="icheck" data-label="{{$v1}}">
                        </div>
                    @endforeach
                @endif

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
    $("input[name='sort']").on('ifChecked',function () {
        $("[name='rule']").attr('style','display:none');
        var type = $(this).attr('types');
        $("#"+type).attr('style','display:block');
    })
</script>--}}
