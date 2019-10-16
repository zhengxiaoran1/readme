@extends('mobile.layouts.master')
@section('content')
    <style>
        .layui-form-label{
            padding: 9px 13px;
            width: 84px;
        }
        ._body{
            background-color: #fff;
        }
        .layui-input-block{
            border-bottom:1px solid #efefef;
        }
    </style>
    <div class="layui-fluid">
        <div class="layui-card">
            <form class="layui-form" action="" style="padding-top: 10px">
                <div class="layui-form-item">
                    <label class="layui-form-label">客户名称</label>
                    <div class="layui-input-block">
                        <div style="line-height: 24px;padding: 6px 10px;">{{ request('customer_title_value','') }}</div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">单位名称</label>
                    <div class="layui-input-block">
                        <div style="line-height: 24px;padding: 6px 10px;">{{ request('buyer_title','') }}</div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">收货信息</label>
                    <div class="layui-input-block" style="padding-right: 30px">
                        <div class="address_list" onclick="addressList()" style="line-height: 24px;padding: 6px 10px;cursor: pointer">
                            <span>请选择</span>
                            <input type="hidden" value="" id="address_list">
                        </div>
                    </div>
                </div>

                <div class="layui-form-item" id="out-types">
                    <label class="layui-form-label">交货方式</label>
                    <div class="layui-input-block" id="">
                        <div class="out-type" onclick="outType()" style="line-height: 24px;padding: 6px 10px;">
                            <span>请选择</span>
                            <input type="hidden" value="" id="take-type">
                        </div>
                    </div>
                </div>
                <div class="layui-form-item">
                    <div class="mterial-list-yes-A4404" style="height:100px;padding: 10px 40px;border: 1px dashed #efefef;border-radius: 5px;">
                        <img src="{{request('img_url')}}" style="display: block;float:left;width: 50px;height:50px;margin-right:10px">
                        <div style="width: calc(100% - 60px);float:left ">
                            <div style="font-weight: 600;font-size: 15px;">
                                {{request('product_name')}}
                                <span style="float: right;font-size: 13px;color: #6c6c6c;text-align: right;color: #40A4EA">单价：￥<span id="_price">{{ request('price') ? request('price') : 0.00 }}</span></span>
                            </div>
                            <div style="font-size: 13px;color: #6c6c6c;">{{request('customer_title_value')}} | {{request('bh_value')}}
                                <span style="float: right;">X<span id="_number">0</span>{{request('unit')}}</span>
                            </div>
                            <div style="font-size: 13px;color:#6c6c6c;margin-top: 10px;text-align: right">
                                <div class="layui-btn layui-btn-normal layui-btn-xs" id="number">设置数量</div>
                                <div class="layui-btn layui-btn-normal layui-btn-xs" id="price">设置单价</div>
                            </div>
                        </div>
                        <div style="clear:both"></div>
                    </div>

                    <div style="clear: both;"></div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">备注</label>
                    <div class="layui-input-block" style="margin: 10px 18px;border:none">
                        <textarea class="layui-textarea" placeholder="请输入其他需求信息" id="remark" style="width: 81%"></textarea>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label"></label>
                    <div class="layui-input-block" style="text-align: right;padding-right: 30px;border:none">
                        总金额（元）<span style="color: #40A4EA;font-weight: bold;font-size: 18px">￥<span id="all_money">0.00</span></span>
                    </div>
                </div>

                <div class="layui-form-item layui-layout-admin">
                    <div class="layui-input-block">
                        <div class="layui-footer" style="left: 0;">
                            <button class="layui-btn submit" type="button">确认提交</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

    </div>
<script>
    $(".layui-btn-xs").click(function () {
        var that = $(this).attr('id');
        if (that == 'number'){
            var _placeholder = '请输入数量';
            var _title = '设置数量';
        } else if (that == 'price'){
            var _placeholder = '请输入单价';
            var _title = '设置单价';
        }
        var _index = layer.open({
            formType: 0, //输入框类型，支持0（文本）默认1（密码）2（多行文本）
            title: _title,
            value: '', //初始时的值，默认空字符
            maxlength: 140, //可输入文本的最大长度，默认500
            content: "<input type='number' name='base__num' value='' placeholder='"+_placeholder+"' style='padding-right:10px;' lay-verify='title' autocomplete='off' class='layui-input'>",
            yes: function () {
                var val = $("[name='base__num']").val();
                $("#_"+that).html(val);
                computer();
                layer.close(_index)
            }
        })

    })

    function computer() {
        var _number = $("#_number").html();
        var _price = $("#_price").html();
        var all_money = parseFloat(_number) * parseFloat(_price);
        $("#all_money").html(all_money);
    }

            {{--选择交货方式--}}
    var _type_list =
        "    <div class='type-list-center' style='min-height:200px'>\n" +
        "        <table width='393px'>\n" +
        "            <tr align='center'>\n" +
        "                <td><span class='take-type checked' align='center' value='once' height='30px' style='cursor: pointer'>单次交货</span></td>\n" +
        "                <td><span class='take-type' align='center' value='times' height='30px' style='cursor: pointer'>定期分批</span></td>\n" +
        "            </tr>\n" +
        "        </table>\n" +
        "        <div id='once' class='layui-card-body'>\n" +
        "           <form  class='layui-form'>\n" +
            "        <div class='layui-form-item'>\n" +
            "            <label class='layui-form-label'>交货日期</label>\n" +
            "            <div class='layui-input-block'>\n" +
            "                <input type='text' class='layui-input out-time' id='once-time' placeholder='点击选择日期'>\n" +
            "            </div>\n" +
            "        </div>\n" +
            "        <div class='layui-form-item'>\n" +
            "            <label class='layui-form-label'>物流运输</label>\n" +
            "            <div class='layui-input-block'>\n" +
            "                <select id='once-wl'>\n" +
            "                    <option value=''>请选择</option>\n" +
            "                    <option value='工厂发货'>工厂发货</option>\n" +
            "                    <option value='送货上门'>送货上门</option>\n" +
            "                    <option value='客户自提'>客户自提</option>\n" +
            "                </select>\n" +
            "            </div>\n" +
            "        </div>\n" +
        "           </form>"+
        "        </div>\n" +
        "        <div id='times'>\n" +
        " <form class='layui-form' style='padding:10px 15px'>\n" +
        "        <div class='layui-form-item'>\n" +
        "            <label class='layui-form-label'>首批交货日期</label>\n" +
        "            <div class='layui-input-block'>\n" +
        "                <input type='text' value='' class='layui-input out-time' id='times-time' placeholder='点击选择日期' readonly>\n" +
        "            </div>\n" +
        "        </div>\n" +
        "        <div class='layui-form-item'>\n" +
        "            <label class='layui-form-label'>物流运输</label>\n" +
        "            <div class='layui-input-block'>\n" +
        "                <select id='times-wl' lay-verify='required'>\n" +
        "                    <option value=''>请选择</option>\n" +
        "                    <option value='工厂发货'>工厂发货</option>\n" +
        "                    <option value='送货上门'>送货上门</option>\n" +
        "                    <option value='客户自提'>客户自提</option>\n" +
        "                </select>\n" +
        "            </div>\n" +
        "        </div>\n" +
        "        <div class='layui-form-item'>\n" +
        "            <label class='layui-form-label'>分批周期</label>\n" +
        "            <div class='layui-input-inline'>\n" +
        "                <input type='number' name='time' id='times-day' required lay-verify='required' autocomplete='off' class='layui-input'>\n" +
        "            </div>\n" +
        "            <div class='layui-form-mid layui-word-aux'>天</div>\n" +
        "        </div>\n" +
        "        <div class='layui-form-item'>\n" +
        "            <label class='layui-form-label'>每批交货数量</label>\n" +
        "            <div class='layui-input-inline'>\n" +
        "                <input type='number'  name='num' id='times-num' required lay-verify='required' autocomplete='off' class='layui-input'>\n" +
        "            </div>\n" +
        "            <div class='layui-form-mid layui-word-aux'>{{request('unit')}}/批</div>\n" +
        "        </div>\n" +
        "    </form>"+
        "       </div></div>\n" +
        "    <div class='pro-btn type-btn' style='margin-top: 101px;padding:10px 0px'>确认提交</div>\n";

    //收货方式选择页面
    function outType(){
        var index = layer.open({
            type: 1
            ,title: '交货方式'
            ,offset: 'auto' //具体配置参考：http://www.layui.com/doc/modules/layer.html#offset
            ,id: 'out_type' //防止重复弹出
            ,content: _type_list
            ,btnAlign: 'c' //按钮居中
            ,area: ['450px','400px']
            ,time: 0
            ,resize:false
            ,scrollbar:false
            ,success:function () {
                $("#times").hide();
                $(".take-type").click(function () {
                    $("#times").hide();
                    $("#once").hide();
                    var name = $(this).attr('value');
                    $(".take-type").removeClass('checked');
                    $(this).addClass('checked');
                    if (name == 'times'){
                        $("#times").show();
                    }else if (name == 'once'){
                        $("#once").show();
                    }
                });
                layui.use('laydate', function(){
                    var laydate = layui.laydate;
                    laydate.render({
                        elem: '#once-time' //指定元素
                        ,trigger: 'click'
                    });
                    laydate.render({
                        elem: '#times-time' //指定元素
                        ,trigger: 'click'
                    });

                });
                $(".type-btn").click(function () {
                    layer.close(index);
                    $(".out-type span").html($(".checked").html());
                    $("#take-type").val(($(".checked").html()));
                    addData($(".checked").html());
                });
                renderForm();
            }
        });
    };
    //收货地址选择页面
    function addressList(){
        layer.load(2);
        ygt_control.token = "{{session('user.token')}}";
        ygt_control.url = "/api/service/buyers/address-list";
        ygt_control.data = {'buyers_id':"{{ request('buyer_id') }}"};
        ygt_control.ajax(function (data) {
            if (data.status) {
                var _html = "<div class='layui-fluid'>";
                if (data.data.length > 0){
                    layui.each(data.data, function (index, item) {
                        var obj = JSON.stringify(item);
                        _html += "<div class='layui-card'>\n" +
                            "            <div style='padding: 10px;border: 2px dashed #efefef;border-radius: 5px;margin-top: 10px;cursor:pointer;height: 90px;border: 1px dashed #c8c8c8' onclick='choseAddress("+obj+")'>\n" +
                            "                <div style='width: calc(100% - 60px);float:left '>\n" +
                            "                    <div style='font-weight: 600;font-size: 15px;margin-top:10px'>收货人："+item.consignee+"&nbsp;"+item.phone +"</div>\n" +
                            "                    <div style='font-size: 13px;color: #6c6c6c;margin-top:10px'>所在地区："+item.province_name+"&nbsp;"+item.city_name+"&nbsp;"+item.area_name+"</div>\n" +
                            "                    <div style='font-size: 13px;color: #6c6c6c;margin-top:10px'>详细地址："+item.address+" </div>\n" +
                            "                </div>\n" +
                            "            </div>\n" +
                            "        </div>";
                    })
                }else{
                    _html += "<div class='layui-card' style='text-align: center'>暂无地址</div>";
                }
                _html += "</div>";
                var address_list = layer.open({
                    type: 1
                    ,title: '地址列表'
                    ,offset: 'auto' //具体配置参考：http://www.layui.com/doc/modules/layer.html#offset
                    ,id: 'addressList' //防止重复弹出
                    ,content: _html
                    ,btnAlign: 'c' //按钮居中
                    ,area: ['450px','400px']
                    ,time: 0
                    ,resize:false
                    ,scrollbar:false
                    ,success:function () {
                        renderForm();
                    }
                });
                layer.closeAll('loading');
            }
        });
    }
    //添加选择的收货地址
    function choseAddress(obj){
        var _html = obj.province_name + "&nbsp;" + obj.city_name + "&nbsp;" + obj.area_name + "&nbsp;" + obj.address ;
        $(".address_list span").html(_html);
        $("#address_list").val(obj.id);
        layer.closeAll();
    }
    //添加交货方式
    function addData(name) {
        $(".out-types").hide();
        if (name == '单次交货'){
            var time = $("#once-time").val();
            var wl = $("#once-wl").val();
            var _html = "<div class='layui-form-item out-types'>\n" +
                "        <label class='layui-form-label'>交货日期</label>\n" +
                "        <div class='layui-input-block'>\n" +
                "            <div style='padding:8px 10px;line-height:24px'>\n<span id='once_time'>" +
                                time +
                "</span></div>\n" +
                "           </div>\n" +
                "       </div>";
            _html += "<div class='layui-form-item out-types'>\n" +
                "        <label class='layui-form-label'>物流运输</label>\n" +
                "        <div class='layui-input-block'>\n" +
                "            <div style='padding:8px 10px;line-height:24px'>\n<span id='once_wl'>" +
                                wl +
                "</span></div>\n" +
                "        </div>\n" +
                "    </div>";
            $("#out-types").after(_html);
        } else if (name == '定期分批'){
            var time = $("#times-time").val();
            var wl = $("#times-wl").val();
            var day = $("#times-day").val();
            var num = $("#times-num").val();
            var _html = "<div class='layui-form-item out-types'>\n" +
                "        <label class='layui-form-label'>首批交货日期</label>\n" +
                "        <div class='layui-input-block'>\n" +
                "            <div style='padding:8px 10px;line-height:24px'>\n<span id='times_time'>" +
                time +
                "</span></div>\n" +
                "           </div>\n" +
                "       </div>";
            _html += "<div class='layui-form-item out-types'>\n" +
                "        <label class='layui-form-label'>物流运输</label>\n" +
                "        <div class='layui-input-block'>\n" +
                "            <div style='padding:8px 10px;line-height:24px'>\n<span id='times_wl'>" +
                wl +
                "</span></div>\n" +
                "        </div>\n" +
                "    </div>";
            _html += "<div class='layui-form-item out-types'>\n" +
                "        <label class='layui-form-label'>分批周期</label>\n" +
                "        <div class='layui-input-block'>\n" +
                "            <div style='padding:8px 10px;line-height:24px'>\n<span id='times_day'>" +
                day +
                "</span>天</div>\n" +
                "        </div>\n" +
                "    </div>";
            _html += "<div class='layui-form-item out-types'>\n" +
                "        <label class='layui-form-label'>每批交货数量</label>\n" +
                "        <div class='layui-input-block'>\n" +
                "            <div style='padding:8px 10px;line-height:24px'>\n<span id='times_num'>" +
                num +
                "</span>{{request('unit')}}/批</div>\n" +
                "        </div>\n" +
                "    </div>";
            $("#out-types").after(_html);
        }
    }

    function renderForm(){
        layui.use('form', function(){
            var form = layui.form;//高版本建议把括号去掉，有的低版本，需要加()
            form.render();
        });
    }
    renderForm();
    //提交信息
    $(".submit").click(function () {
        layer.load(2);
        if ($("#take-type").val() == '单次交货'){
            var batch_cycle = ',天';
            var send_type = $("#take-type").val();
            var delivery_date = $("#once_time").html();
            var per_delivery_number = ',{{request('unit')}}/批';
            var delivery_type = '单次交货';
        }else if ($("#take-type").val() == '定期分批'){
            var batch_cycle = $("#times_day").html()+',天';
            var send_type = $("#take-type").val();
            var delivery_date = $("#times_time").html();
            var per_delivery_number = $("#times_num").html();
            var delivery_type = '定期分批';
        }
        var desc = $("#remark").val();
        var num = $("#_number").html();
        var price = $("#_price").html();
        var all_money = $("#all_money").html();
        var receve_information = $("#address_list").val();
        var submit = {
            'batch_cycle':batch_cycle,
            'buyer_id':'{{request('buyer_id')}}',
            'buyer_title':'{{request('buyer_title')}}',
            'chanpin_list':[{
                'chanpin_id':'{{request('id')}}',
                'chanpin_title':'{{request('product_name')}}',
                'desc':desc,
                'number':num,
                'price':price,
            }],
            'customer_id':'{{request('customer_id')}}',
            'customer_title':'{{request('customer_title_value')}}',
            'delivery_date':delivery_date,
            'delivery_type':delivery_type,
            'money':all_money,
            'per_delivery_number':per_delivery_number,
            'receve_information':receve_information,
            'send_type':send_type
        };

        if (num == 0 || num == ''){
            layer.msg('请填写产品数量');
            return false;
        }
        setterData = layui.data(layui.setter.tableName);

        $.ajax({
            url:'/api/service/chanpin/createChanpinOrderSubmit',
            data:{
                'submit_list':JSON.stringify(submit)
            },
            type:"post",
            dataType:"json",
            beforeSend: function (XMLHttpRequest) {
                XMLHttpRequest.setRequestHeader("token", setterData.token );
            },
            success:function (data) {
                if (data.status == 200){
                    layer.msg(data.message, {
                        offset: '15px'
                        ,icon: 1
                        ,time: 1000
                    }, function(){
                        parent.layer.closeAll();
                        window.parent.tablist.reload();
                    });

                }
            }
        })
    });
</script>
@endsection
