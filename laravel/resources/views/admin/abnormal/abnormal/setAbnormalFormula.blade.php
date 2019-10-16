<span id="span"></span>
<style>
    /*.dialogContent unitBox {*/
    /*height: 800px; padding: 10px; overflow: auto;*/
    /*}*/
    .main{
        margin: 0 auto;
        text-align: center;
    }
    *{
        padding: 0;
        margin: 0;
    }
    table {
        margin: auto;
        border-collapse: collapse;

    }
    span{
        /*display: inline-block;*/
        text-align:center;
        font-size: 100%;
        width: 200px;
        height: 100%;
        /*background-color: darkgrey;*/
    }
    table td{
        text-align: center;
        width: 50px;
        height: 50px;
        line-height: 50px;
        background-color: lightgrey;
        border:1px solid darkgrey;
    }
</style>

原公式：<span>{{$rule}}</span>
<br />
{{--<span>值:{{$value}}</span>--}}
<div id="text_" style="display: none">
</div>
<div>
    新公式：<span id ="new_text_"></span>
</div>
<div class="main">
 <span id="input" style="display: none">
 </span>
    <table>
        <tbody>
        <tr>
            <td>C</td>
            {{--<td></td>--}}
            <td>.</td>
            <td>*</td>
        </tr>
        <tr>
            <td>7</td>
            <td>8</td>
            <td>9</td>
            <td>-</td>
        </tr>
        <tr>
            <td>4</td>
            <td>5</td>
            <td>6</td>
            <td>+</td>
        </tr>
        <tr>
            <td>1</td>
            <td>2</td>
            <td>3</td>
            <td>/</td>
        </tr>
        <tr>
            <td>(</td>
            <td>0</td>
            <td>)</td>
            <td>=</td>
        </tr>
        </tbody>
    </table>
</div>

基础属性:
<br />
@foreach($list as $k=>$v)
     <button type="button" onclick="formula_new(this)"  new_value="{{$v['field_name']}}" value="{{$v['id']}}">{{$v['field_name']}}</button>
@endforeach
<br /><br />

<button type="submit" id="ajaxButton" class="btn-green" data-icon="search">保存</button>
<input type="hidden" id="_token" name="_token" value="{{ csrf_token() }}" />
<input type="hidden" name="id" value="{{$id}}" id="id">
<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>

<script>
    $(document).ready(function () {
        if (typeof(window.is_my_bind) == "undefined"){
            window.is_my_bind = 1;
            $(document).bind('keypress',function(event) {
                var code = event.which;
                var ReturnKeypressValue =  keypressValue(code);
                if(!ReturnKeypressValue){
                    alert('请输入与键盘一致的数子或符号');
                    return;
                }
                $("#input").append(ReturnKeypressValue);
                $("#text_").append(ReturnKeypressValue+"_");
                $("#new_text_").append(ReturnKeypressValue);
                return;
            });

        }

        var testheight = $(".unitBox").height('1000');
        var $td=$("td");
        $td.each(function(){
            $(this).click(function(){
                var Text=$("#input").text().trim();
                if($(this).text() != '='){
                    $("#input").append($(this).text());
                    $("#text_").append($(this).text());
                    $("#text_").append("_");
                    $("#new_text_").append($(this).text());
                }
                switch ($(this).text()){
                    case "C":
                        $("#input").text("");
                        $("#text_").text("");
                        $("#new_text_").text("");
                        break;
                    case "D":
//                         $("#input").text(Text.substr(0,Text.length-1));
//                         //$("#text_").text(Text.substr(0,Text.length-2));
//                         console.log($("#text_").length);
//                         return;
//                         $("#new_text_").text(Text.substr(0,Text.length-1));
                        break;
                    case "=":
//                     $("#input").append($(this).text());
                    function  compute(content){
                        var index=content.lastIndexOf("(");
                        if(index>-1){
                            var nextIndex=content.indexOf(")",index);
                            if(nextIndex>-1){
                                //递归的思想,一步一步的递归
                                var result=compute(content.substring(index+1,nextIndex));
                                return    compute(content.substring(0,index)+(""+result)+content.substring(nextIndex+1))
                            }

                        }
                        index=content.indexOf("+");
                        if(index>-1){
                            return compute(content.substring(0,index))+compute(content.substring(index+1));
                        }
                        index=content.lastIndexOf("-");
                        if(index>-1){
                            return compute(content.substring(0,index))-compute(content.substring(index+1));
                        }
                        //如果返回的content为空,则返回0
                        index=content.indexOf("*");
                        if(index>-1){
                            return compute(content.substring(0,index))*compute(content.substring(index+1));
                        }
                        index=content.lastIndexOf("/");
                        if(index>-1){
                            return compute(content.substring(0,index))/compute(content.substring(index+1));
                        }
                        if(content==""){
                            return 0;
                        }else{
                            //将content字符串转化为数值,
                            //这儿也可以使用一些技巧,比如 content-1+1,使用加减操作符,将字符串转化为数值
                            return Number(content);
                        }
                    }
                        $("#input").text(compute(Text));
                }
            })

        });
    })



    function formula(o){
        var Text=$("#input").text().trim();
        var obj = $(o).val();
        var new_obj =$(o).attr('new_value');
        $("#text_").append(new_obj+"_");
        $("#new_text_").append(new_obj+"");
        var str = obj;
        spstr = str.split('');
        var end_arr = spstr[spstr.length-1];
        var ival = parseInt(end_arr);//如果变量val是字符类型的数则转换为int类型 如果不是则ival为NaN

        var arr_msg = true;
        if(!isNaN(ival)){
            arr_msg = true;
        } else{
            obj=obj.substring(0,obj.length-1);

        }

        $("#input").append(obj);
//    $("#new_text_").append(obj);

    }

    function formula_new(o){
        var Text=$("#input").text().trim();
        var obj = $(o).val();
        var new_obj =$(o).attr('new_value');
        var new_obj_text =$(o).attr('new_value');
//    console.log(new_obj);
        switch (new_obj){
            case '片料规格长':
                new_obj = 'PlLength';
                break;
            case '成品规格M边':
                new_obj = 'WarehouseM';
                break;

            case '片料规格宽':
                new_obj = 'PlWidth';
                break;

            case '成品规格长':
                new_obj = 'WarehouseLength';
                break;

            case '成品规格宽':
                new_obj = 'WarehouseWidth';
                break;

            case '工序属性1':
                new_obj = 'OrderAttr_1';
                break;

            case '工序属性2':
                new_obj = 'OrderAttr_2';
                break;

        }
        $("#text_").append(new_obj+"_");
        $("#new_text_").append(new_obj_text+"");
        var str = obj;
        spstr = str.split('');
        var end_arr = spstr[spstr.length-1];
        var ival = parseInt(end_arr);//如果变量val是字符类型的数则转换为int类型 如果不是则ival为NaN

        var arr_msg = true;
        if(!isNaN(ival)){
            arr_msg = true;
        } else{
            obj=obj.substring(0,obj.length-1);

        }
//    var result = $.base64.decode('4444');
//    console.log(result);
        $("#input").append(obj);
//    $("#new_text_").append(obj);

    }



    $("#ajaxButton").click(function(){
        // var new_f = $("#input").text();
//        $("#text_").append(new_f);
        var _token = $("#_token").val();
        // var ordertype_id = $("#ordertype_id").val();
        // var process_product_id = $("#process_product_id").val();
        // var material_id = $("#material_id").val();
        var Url = '/admin/abnormal/setAbnormalFormula';
        var new_formula = $("#text_").html();
        // var val = $("#new_text_").html();
        var id  = $("#id").val();
        // var process_id = $("#process_id").val();
//         console.log(val);return;
        $.post(Url,{'_token':_token,'id':id,'formula':new_formula},function(_result){

            if(_result.statusCode == 300){
                alert(_result.message);
            }else{
                $("#ajaxButton").attr("disabled", true);
                BJUI.dialog('closeCurrent')
                BJUI.navtab('refresh')
            }
            //
            //
            // console.log(result);
            // var msg=result==200?'操作成功':'操作失败';
            //
            // alert(msg);
        });

    })


    function keypressValue(code) {
        switch (code){
            case 48:
                return 0;
                break;
            case 49:
                return 1;
                break;
            case 50:
                return 2;
                break;
            case 51:
                return 3;
                break;
            case 52:
                return 4;
                break;
            case 53:
                return 5;
                break;
            case 54:
                return 6;
                break;
            case 55:
                return 7;
                break;
            case 56:
                return 8;
                break;
            case 57:
                return 9;
                break;
            case 43:
                return '+';
                break;
            case 45:
                return '-';
                break;
            case 42:
                return '*';
                break;
            case 47:
                return '/';
                break;
            case 46:
                return '.';
                break;
            case 40:
                return '(';
                break;
            case 41:
                return ')';
                break;

        }
    }
</script>