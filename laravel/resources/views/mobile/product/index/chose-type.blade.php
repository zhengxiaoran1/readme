@extends('mobile.layouts.master')
@section('content')

<form class="layui-form" action="" lay-filter="component-form-group">
    <div class="layui-fluid pro" >
        <div class="layui-card">
            <div class="layui-card-header">选择产品类型</div>
            <div class="layui-card-body" style="padding: 15px;">
                <div class="pro_type_list"></div>
                <div style="clear: both"></div>
            </div>
        </div>
    </div>

    <div class="layui-fluid create-product">
        <div class="product-info"></div>
        <div class="layui-form-item layui-layout-admin">
            <div class="layui-input-block">
                <div class="layui-footer" style="left: 0;">
                    <div class="layui-btn submit-btn" >立即提交</div>
                    <button type="reset" class="layui-btn layui-btn-primary pro-back-btn">重置</button>
                </div>
            </div>
        </div>
    </div>

    <div class="customer-list"></div>
    <div class="buyer-list"></div>
    <div class="mterial-list ">
        <div class="layui-layer-title mterial-branch-title">选择材料
            <span class="layui-layer-setwin">
                <a class="layui-layer-ico layui-layer-close layui-layer-close1 mterial-btn" href="javascript:;"></a>
            </span>
        </div>
        <div class="mterial-list-center">
            <div class="mterial-type"></div>
            <div class="mterial-title" >
                <div style="width: 15%;">图片</div>
                <div style="width: 15%;">材料</div>
                <div style="width: 20%;">sku数量</div>
                <div style="width: 25%;">材料规格</div>
                <div style="width: 25%;">可用库存</div>
                <span style="clear: both;display:block"></span>
            </div>
            <div class="mterial"></div>
        </div>
        <div id="mterial-page"></div>
    </div>

    <div class="branch-list">
        <div class="layui-layer-title mterial-branch-title">选择版
            <span class="layui-layer-setwin">
                <a class="layui-layer-ico layui-layer-close layui-layer-close1 branch-btn-home" href="javascript:;"></a>
            </span>
        </div>
        <div class="mterial-list-center" style="padding:10px;">
            <div class="mterial-title" >
                <div style="width: 10%;">图片</div>
                <div style="width: 20%;">版名称</div>
                <div style="width: 15%;">类型</div>
                <div style="width: 15%;">编号</div>
                <div style="width: 15%;">客户</div>
                <div style="width: 15%;">单位</div>
                <div style="width: 10%;">支数</div>
                <span style="clear: both;display:block"></span>
            </div>
            <div class="branch"></div>
            <div id="branch-page"></div>
        </div>
        <div class="branch-info">
            <div class="layui-layer-title mterial-branch-title">选择版支
                <span class="layui-layer-setwin">
                <a class="layui-layer-ico layui-layer-close layui-layer-close1 branch-back-btn" href="javascript:;"></a>
            </span>
            </div>
            <div class="branch-info-center">
                <div class="other_plate">
                    <label class="row-label" >套版型号</label>
                    <div class="other_plate_list"></div>
                </div>

                <div class="fixed_plate">
                    <label class="row-label" >固定板</label>
                    <div class="fixed_plate_list"></div>
                </div>
                <div class="colourplate">
                    <label class="row-label" >套版</label>
                    <div class="colourplate_list"></div>
                </div>
            </div>
            <div class="pro-btn " style="margin: 0px;padding: 0px;">
                <div class="branch-btn" style="width: calc(100% - 20px);padding: 10px;" >确认</div>
            </div>
        </div>
    </div>
</form>
<script type="application/javascript">
    layui.use(['layer','index', 'form', 'laydate'], function(){
        var $ = layui.$
            ,admin = layui.admin
            ,element = layui.element
            ,layer = layui.layer
            ,laydate = layui.laydate
            ,form = layui.form;

        ygt_control.token = "{{session('user.token')}}";
        page_control.pageLoad = function(){form.render();}
        var productType,mterialType,mterialTypeField,branchTypeField,to_base__image_id_str,base__image_id_str,image_id_str = "";
        var mterial,branch = [];
        create_product_control.product = "{{request('id')}}";
        create_product_control.ajaxUrlParam = {
            upload:'/api/service/upload',
            createChanpinSubmit:'/api/service/chanpin/createChanpinSubmit',
            getPlateBranchDetail:"/api/service/plate/getPlateBranchDetail",
            getOrderTypeAll:"/api/service/order/getOrderTypeAll",
            createChanpinPre:"/api/service/chanpin/createChanpinPre",
            letterList:"/api/service/customer/letter-list",
            getBuyersList:"/api/service/buyers/getBuyersList",
            list:"/api/service/plate/list",
            treeList:"/api/service/category/tree-list",
            getAssemblageMaterialDealList:"/api/service/product/getAssemblageMaterialDealList",
        };

        if(create_product_control.product){
            ygt_control.data = {chanpin_pre_id:"",copy_chanpin_id:create_product_control.product,order_type_id:""};
            create_product_control.getChanpinPre(function(){
                form.render("select")
                form.render("checkbox");
                $('.pro-back-btn').hide();
            });
        }else{
            create_product_control.getOrderType(function(){
                form.render("select")
                $('.pro-back-btn').show();
            });
        }

        $('.submit-btn').click(function(){

            base__image_id_str = $('input[name=base__image_id_str]').val();
            to_base__image_id_str = base__image_id_str;
            var srcarr = [];
            for (var i = 0, l = release_control.images.length; i < l; i++) {
                srcarr.push(release_control.images[i].src)
            }

            ygt_control.data = {group:'mobile',type:2,imgfile:srcarr};
            ygt_control.url  = create_product_control.ajaxUrlParam.upload;
            ygt_control.ajax(function (data) {
                if(!data.code){

                    if(data.data.img_id){
                        base__image_id_str = data.data.img_id+','+base__image_id_str;
                        var new_base__image_id_str = "";
                        var strs = base__image_id_str.split(","); //字符分割
                        $.each(strs,function(s,y){
                            if(y) new_base__image_id_str += y+',';
                        })
                        $('input[name=base__image_id_str]').val(new_base__image_id_str);
                    }

                    var newFormData = {order_type_id:productType,chanpin_pre_id:"",chanpin_id:create_product_control.product};
                    var formData = $('.layui-form').serializeArray();
                    $.each(formData, function() {
                        if(newFormData.hasOwnProperty(this.name)){
                            if(!newFormData[this.name]) newFormData[this.name] = "";
                            if(this.name.indexOf('field_name_38') != -1){
                                newFormData[this.name] = newFormData[this.name] + " " + this.value;
                            }else{
                                newFormData[this.name] = newFormData[this.name] + "," + this.value;
                            }
                        }else{
                            newFormData[this.name] = this.value?this.value:"";
                        }
                    });

                    ygt_control.data = newFormData;
                    ygt_control.url  = create_product_control.ajaxUrlParam.createChanpinSubmit;
                    ygt_control.ajax(function (res) {
                        if(!res.code){

                            layer.msg(res.message, {
                                offset: '15px'
                                ,icon: 1
                                ,time: 1000
                            }, function(){
                                parent.layer.closeAll();
                                if(!create_product_control.product){
                                    window.parent.tablist.reload();
                                }
                            });

                        }else{
                            $('input[name=base__image_id_str]').val(to_base__image_id_str);
                            layer.msg(res.message, {
                                offset: '15px'
                                ,icon:2
                                ,time: 1000
                            });
                        }
                    })

                }else{
                    BJUI.alertmsg('warn', data.message)
                }
            });

        })

        $('.pro-back-btn').click(function(){
            $('.create-product').hide();
            $('.pro').show();
        })

        $('.branch-back-btn').click(function(){
            $('.branch-info').hide();
        })
        $('.mterial-btn').click(function(){
            $('.mterial-list').hide();
            $('.mterial').html("");
        })
        $('.branch-btn-home').click(function(){
            $('.branch-list,.branch-info').hide();
        })
        $('.branch-btn').click(function(){
            $('input[name='+create_product_control.branchTypeField+']').val(JSON.stringify(create_product_control.branch));
            var _html = template('branch', create_product_control.branch);
            $('.'+create_product_control.branchTypeField).html("").append(_html);
            $('.branch-list,.branch-info').hide();
        })
        $(".create-product").on('click','.img-del',function(){
            var _index = $(".create-product").find('.img-del').index(this);
            release_control.$show = $(".product-info .release-pics");
            release_control.remove(_index);
            release_control.removeHtml(_index);
        })
        $(".create-product").on('change','#add-file',function(e) {
            release_control.$show = $(".product-info .release-pics");
            release_control.upload(this, e);
        })
        //选择产品类型后获取工艺步骤
        $('.pro_type_list').on('click','.pro_type',function(){
            productType = $(this).data('id');

            if(!productType){
                BJUI.alertmsg('warn', '选择产品类型出错，刷新页面重试');
                return false;
            }

            ygt_control.data = {chanpin_pre_id:"",copy_chanpin_id:"",order_type_id:productType};
            create_product_control.getChanpinPre(function(){
                form.render("select");
                form.render("checkbox");
            });

        })

        $(".create-product").on('click','.update-img-del',function(){

            var _index = $(".create-product").find('.update-img-del').index(this);
            image_id_str = $('input[name=base__image_id_str]').val();
            var strs= new Array(); //定义一数组
            strs=image_id_str.split(","); //字符分割
            strs.splice(_index, 1);
            $(".product-info .update-release-pics").children().eq(_index).remove()
            image_id_str = "";
            $.each(strs,function(i,v){
                if(v) image_id_str += v + ',';
            })
            $('input[name=base__image_id_str]').val(image_id_str);
        })


        $('.mterial-list').on('click','.mterial-type-input',function(){

            var mterialTypeInput = $('.mterial-type-input');
            mterialType = "";
            $.each(mterialTypeInput,function(i,v){
                if ($(v).prop("checked")) {
                    mterialType += $(v).val() + ',';
                }
            })
            create_product_control.mterialType = mterialType;
            create_product_control.getMterial(false,true);
        })


        $('.product-info').on('click','.mterial-info-click',function(){
            var that = this;
            mterialTypeField = $(that).data('type');
            create_product_control.mterialTypeField = mterialTypeField;
            create_product_control.mterialChange(that,function(){
                form.render();
            });
        })


        form.on('checkbox(mterial-type-input)', function(data){
            var mterialTypeInput = $('.mterial-type-input');
            mterialType = "";
            $.each(mterialTypeInput,function(i,v){
                if ($(v).prop("checked")) {
                    mterialType += $(v).val() + ',';
                }
            })

            create_product_control.mterialType = mterialType;
            create_product_control.getMterial(function(){
                form.render();
            },true);
        });


        form.on('checkbox(mterialId)', function(data){
            var that_id = data.value;
            mterial = [];
            var mterialInput = $('input[name='+mterialTypeField+']').val() || "[]";
            if(mterialInput){
                mterial = JSON.parse(mterialInput);
            }

            if ($(this).prop("checked")) {
                var mterialRepeat = false;
                $.each(mterial,function(i,v){
                    if(that_id == v.id){
                        mterialRepeat = true;
                    }
                })
                if(!mterialRepeat){
                    mterial.push({id:that_id,is_purchase:0});
                    var data = [];
                    data.push({
                        number:$('.mterial-number-'+that_id).html()?$('.mterial-number-'+that_id).html():"0",
                        field:$('.mterial-fields-'+that_id).val()?$('.mterial-fields-'+that_id).val():"-",
                        company:$('.mterial-company-'+that_id).val()?$('.mterial-company-'+that_id).val():"-",
                        name:$('.mterial-name-'+that_id).val(),
                        id:that_id,
                        type:create_product_control.mterialTypeField
                    })
                    var mterialInfo = {data:data};
                    var _html = template('mterial-list-yes', mterialInfo);
                    $('.'+mterialTypeField).prepend(_html);
                }
            }else{
                $.each(mterial,function(i,v){
                    if(v && that_id == v.id){
                        mterial.splice(i,1);
                        $('.mterial-list-yes-'+that_id).remove();
                    }
                })
            }
            if(mterial.length == 0){
                $('.product-info .mterial-title-'+mterialTypeField).hide();
            }else{
                $('.product-info .mterial-title-'+mterialTypeField).show();
            }
            create_product_control.mterial = mterial;
            $('input[name='+mterialTypeField+']').val(JSON.stringify(mterial));

        });

        $('.product-info').on('click','.mterial-del',function(){
            var that_id = $(this).data('id');
            mterial = [];
            mterialTypeField = $(this).data('type');
            var mterialInput = $('input[name='+mterialTypeField+']').val();
            if(mterial){
                mterial = JSON.parse(mterialInput);
            }
            $.each(mterial,function(i,v){
                if(v && that_id == v.id){
                    mterial.splice(i,1);
                    $('.mterial-list-yes-'+that_id).remove();
                }
            })
            if(mterial.length == 0){
                $('.product-info .mterial-title-'+mterialTypeField).hide();
            }
            create_product_control.mterial = mterial;
            $('input[name='+mterialTypeField+']').val(JSON.stringify(mterial));

        })


        form.on('select(mterial_select)', function(data){
            create_product_control.mterialSelect(data.value);
        });

        form.on('select(customer_id)', function(data){
            create_product_control.customer = data.value;
            create_product_control.buyer_id(function(){
                form.render("select");
            })
        });



    })
</script>

<script type="text/html" id="product-info">
    <% for( i = 0; i < data_list.length; i++) {%>
        <% var obj = data_list[i];%>

        <div class="layui-card">
            <div class="layui-card-header"><%= obj.title %></div>
            <div class="layui-card-body">
                <% var field_list_obj = obj.field_list; for( s = 0; s < field_list_obj.length; s++) { %>
                <% var field_list = field_list_obj[s]; %>

                    <% if(field_list.field_type != 9){ %>
                        <div class="layui-form-item">
                            <label class="layui-form-label"><%= field_list.title %></label>
                            <div class="layui-input-block">
                    <% } %>
                        <% if(field_list.field_type == 9){ %>
                            <div class="product-step-field">
                                <div class="field-title"><%= field_list.title %></div>
                                <div class="field-value">
                                <div class="release-func-btns" >
                                    <div class="func-btn">
                                        <i class="fa fa-save"></i>&nbsp;选择图片
                                        <input type="file" accept="image/*" id="add-file" multiple name="file_img">
                                    </div>
                                    <div class="tip" style="height: 36px;line-height: 36px;">最多可以上传9张图片，图片左滑可以删除</div>
                                </div>
                                <input type="hidden" class="file_img_input" name="<%= field_list.deal_field_name%>" value="<%= field_list.default_value%>"/>
                        <% }else if(field_list.field_type == 1){ %>

                            <input type="text" name="<%= field_list.deal_field_name%>" value="<%= field_list.default_value%>" placeholder="请输入<%= field_list.title%>" style="padding-right:10px;" lay-verify="title" autocomplete="off"  class="layui-input">

                        <% }else if(field_list.field_type == 2){ %>

                            <textarea class="layui-textarea" placeholder="<%= field_list.placeholder%>" name="<%= field_list.deal_field_name%>"><%= field_list.default_value%></textarea>

                        <% }else if(field_list.field_type == 15 || field_list.field_type == 16){ %>


                            <% for( t = 0; t < field_list.placeholder_arr.length; t++) {%>
                                <div class="layui-input-inline" style="width: 100px;">
                                    <input type="text" name="<%= field_list.deal_field_name %>" placeholder="<%= field_list.placeholder_arr[t] %>" autocomplete="off" class="layui-input">
                                  </div>
                            <% if( (field_list.placeholder_arr.length - 1) > t){ %>
                                <div class="layui-form-mid">X</div>
                            <% }} %>

                        <% }else if(field_list.field_type == 5){ %>

                                <div class="layui-col-lg6" style="width:calc(30% - 10px);float:left;margin-right: 10px;">
                                    <input type="text" value="<%= field_list.default_value%>" name="<%= field_list.deal_field_name%>" placeholder="请输入<%= field_list.title%>" lay-verify="title" autocomplete="off"  class="layui-input">
                                </div>
                                <div class="layui-col-lg6" style="width:70%;float:left;">
                                    <select name="<%= field_list.deal_field_name%>">
                                        <% for( t = 0; t < field_list.field_unit.length; t++) {%>
                                            <option value="<%= field_list.field_unit[t].title %>"  <% if(field_list.field_unit[t].title == field_list.default_unit_title){ %> selected = "selected" <% } %> ><%= field_list.field_unit[t].title %></option>
                                        <% } %>
                                    </select>
                                </div>

                        <% }else if(field_list.field_type == 3){ %>
                            <select name="<%= field_list.deal_field_name %>" lay-filter="aihao">
                                <% if(field_list.data.length == 0){ %>
                                    <option value="" >请选择</option>
                                <% } %>
                                <% for( t = 0; t < field_list.data.length; t++) {%>
                                    <option value="<%= field_list.data[t].title %>"  <% if(field_list.data[t].title == field_list.default_value){ %> selected = "selected" <% } %>><%= field_list.data[t].title %></option>
                                <% } %>
                              </select>
                        <% }else if(field_list.field_type == 18 || field_list.field_type == 19){ %>

                            <select id="<%= field_list.field_name%>" lay-filter="<%= field_list.field_name%>" xm-select="<%= field_list.field_name%>" lay-search name="<%= field_list.deal_field_name%>" class="<%= field_list.field_name%>">
                                <option value="">请选择<%= field_list.title%></option>
                            </select>

                        <% }else if(field_list.field_type == 4){ %>
                            <div class="mterial-change mterial-info-click"  data-type="<%= field_list.deal_field_name%>" data-category_id="<%= field_list.children_category_id%>">
                                <i class="layui-icon layui-icon-add-1"></i>
                                <span class="mterial-change-msg-<%= field_list.field_name%>">选择材料</span>
                            </div>
                            <input type="hidden" name="<%= field_list.deal_field_name%>" value=""/>

                        <% }else if(field_list.field_type == 17){ %>
                            <div class="mterial-change" onClick="create_product_control.branchChange(this,true);" data-type="<%= field_list.deal_field_name%>" data-category_id="<%= field_list.children_category_id%>">
                                <i class="layui-icon layui-icon-add-1"></i>
                                <span class="mterial-change-msg-<%= field_list.field_name%>">请选择版</span>
                                <input type="hidden" name="<%= field_list.deal_field_name%>" value=""/>
                            </div>
                            <div class="<%= field_list.deal_field_name%>" style="margin-top:10px;">
                                <div style="clear: both;"></div>
                            </div>
                        <% }else if(field_list.field_type == 11){ %>

                            <select name="<%= field_list.deal_field_name%>">
                                <option value="">请选择</option>
                                <% for( t = 0; t < field_list.data.length; t++) {%>
                                <optgroup label="<%= field_list.data[t].title %>">
                                    <% for( y = 0; y < field_list.data[t].data.length; y++) {%>
                                        <option <% if(field_list.data[t].title+" "+field_list.data[t].data[y].title == field_list.default_value){ %> selected = "selected" <% } %> value="<%= field_list.data[t].title %> <%= field_list.data[t].data[y].title %>"><%= field_list.data[t].title %> - <%= field_list.data[t].data[y].title %></option>
                                    <% } %>
                                </optgroup>
                                <% } %>
                            </select>

                        <% }else if(field_list.field_type == 13){ %>
                            <% for( t = 0; t < field_list.data.length; t++) {%>
                            <input type="checkbox" name="<%= field_list.deal_field_name%>" title="<%= field_list.data[t].title %>" value="<%= field_list.data[t].title %>">
                            <% } %>
                        <% }else{ %>
                            当前未处理
                            <i class="fa fa-angle-right"></i>
                        <% } %>


                        <% if(field_list.field_type == 15 || field_list.field_type == 16){ %>
                            <span class="default_value">厘米</span>
                        <% } %>
                    </div>
                    <% if(field_list.field_type == 4){ %>

                        <div class="mterial-title mterial-title-<%= field_list.deal_field_name%>" <% if(!field_list.default_product_list){ %> style="display:none" <% } %>>
                            <div style="width: 15%;">图片</div>
                            <div style="width: 15%;">材料</div>
                            <div style="width: 20%;">供应商</div>
                            <div style="width: 20%;">材料规格</div>
                            <div style="width: 20%;">可用库存</div>
                            <div style="width: 10%;">操作</div>
                            <span style="clear: both;display:block"></span>
                        </div>
                        <div class="<%= field_list.deal_field_name%>" >
                            <div style="clear: both;"></div>
                        </div>
                    <% } %>
                    <% if(field_list.field_type == 9){ %>
                        <div class="release-pics" style="padding-left:10px;padding-right:10px;padding-top: 10px;"></div>
                        <div class="update-release-pics" style="padding-left:10px;padding-right:10px;"></div>
                        <div style="clear: both;"></div>
                    <% } %>
                </div>
            <% } %>

            </div>
        </div>

    <% } %>
</script>


{{--产品分类--}}
<script type="text/html" id="region-list">
    <%for( i = 0; i < $list.length; i++) {%>
    <%var obj = $list[i];%>
        <div class="pro_type" data-id="<%= obj.$typeFunctionName %>" style="background-image:url('<%= obj.$imageUrl %>')">
            <%= obj.$typeTitle %>
        </div>
    <% } %>
</script>

{{--客户列表--}}
<script type="text/html" id="customer-list">
    <%for( i = 0; i < data.length; i++) {%>
        <%var obj = data[i];%>

          <optgroup label="<%= obj.letter %>">
            <%for( t = 0; t < obj.list.length; t++) {%>
            <option value="<%= obj.list[t].id %>"><%= obj.list[t].customer_name %></option>
            <% } %>
          </optgroup>

    <% } %>
</script>

{{--单位列表--}}
<script type="text/html" id="buyer-list">
    <option value="">请选择单位</option>
    <%for( i = 0; i < data.length; i++) {%>
        <%var obj = data[i];%>
        <option value="<%= obj.id %>"><%= obj.buyer_name %></option>
    <% } %>
</script>

{{--材料分类--}}
<script type="text/html" id="mterial-type">
    <div class="mterial-select" style="position: relative;height: 44px;">
        <select class="mterial-select-input" id="mterial_select" lay-filter="mterial_select" lay-search >
            <option value="">全部分类</option>
            <%for( i = 0; i < data.length; i++) {%>
                <%var obj = data[i];%>
                <option value="<%= obj.id %>"><%= obj.cat_name %></option>
            <% } %>
        </select>
    </div>
    <%for( i = 0; i < data.length; i++) {%>
        <%var obj = data[i];%>
        <div class="mterial-type-list mterial-type-list-<%= obj.id %>">
            <%for( s = 0; s < obj.child.length; s++) {%>
                <input class="mterial-type-input" lay-filter="mterial-type-input" type="checkbox" name="mterial-type[]" value="<%= obj.child[s].id %>" title="<%= obj.child[s].cat_name %>">
            <% } %>
            <div style="clear:both"></div>
        </div>
    <% } %>

</script>

{{--材料列表--}}
<script type="text/html" id="mterial-list">
    <%for( i = 0; i < list.length; i++) {%>
        <%var obj = list[i];%>


        <div class="mterial-title-list mterial-list-yes-<%= obj.id %>">
            <div style="width: 15%;">
                <img src="<%= obj.image_path %>" style="width: 30px;height:30px;margin-right:10px"/>
            </div>
            <div style="width: 15%;"><%= obj.product_name %></div>
            <div style="width: 20%;"><%= obj.supplier_number_txt %></div>
            <div style="width: 25%;"><%= obj.custom_fields_text %></div>
            <div style="width: 25%;"><%= obj.number %><%= obj.unit %></div>
            <span style="clear: both;display:block"></span>
        </div>
        <%for( s = 0; s < obj.supplier_list.length; s++) {%>
            <div style="border-bottom:1px dashed #d5d2d2;padding:10px 0px;">
                <input class="mterialId mterialId-<%= obj.supplier_list[s].material_id %>" lay-filter="mterialId" type="checkbox" name="mterialId[]" lay-skin="primary" title="<%= obj.supplier_list[s].seller_company_title %>" value="<%= obj.supplier_list[s].material_id %>">
                <input type="hidden" class="mterial-company-<%= obj.supplier_list[s].material_id %>" value="<%= obj.supplier_list[s].seller_company_title %>"/>
                <input type="hidden" class="mterial-fields-<%= obj.supplier_list[s].material_id %>" value="<%= obj.custom_fields_text %>"/>
                <input type="hidden" class="mterial-name-<%= obj.supplier_list[s].material_id %>" value="<%= obj.product_name %>"/>
                <% if(obj.supplier_list[s].price != "¥0"){ %><span style="color:#00AAEE;float: right;"><%= obj.supplier_list[s].price %></span><% } %>
                <div style="margin-top:5px;"></div>
                <div style="float: left;width: 50%;" class="mterial-number-<%= obj.supplier_list[s].material_id %>">库存：<%= obj.supplier_list[s].number %></div>
                <div style="float: left;width: 50%;" >余品：<%= obj.supplier_list[s].y_number %></div>
                <span style="clear: both;display:block;"></span>
            </div>
        <% } %>


    <% } %>
</script>

{{--已选材料--}}
<script type="text/html" id="mterial-list-yes">
    <%for( i = 0; i < data.length; i++) {%>
        <%var obj = data[i];%>
        <div class="mterial-title-list mterial-list-yes-<%= obj.id %>">
            <div style="width: 15%;">
                <img src="<%= obj.image_path %>" style="width: 30px;height:30px;margin-right:10px"/>
            </div>
            <div style="width: 15%;"><%= obj.name %></div>
            <div style="width: 20%;"><%= obj.company %></div>
            <div style="width: 20%;"><%= obj.field %></div>
            <div style="width: 20%;"> <%= obj.number %></div>
            <div style="width: 10%;" class="mterial-del" data-id="<%= obj.id %>" data-type="<%= obj.type %>">删除</div>
            <span style="clear: both;display:block"></span>
        </div>
    <% } %>
</script>

{{--版列表--}}
<script type="text/html" id="branch-list">
    <%for( i = 0; i < data.length; i++) {%>
        <%var obj = data[i];%>

        <div class="mterial-title-list branch-title-list" data-plate_name="<%= obj.plate_name %>" data-category_id="<%= obj.category_id %>" data-id="<%= obj.id %>" onClick="create_product_control.clickBranch(this);">
            <div style="width: 10%;"><img src="<%= obj.img_url %>" style="width: 30px;height:30px;margin-right:10px"/></div>
            <div style="width: 20%;"><%= obj.plate_name %></div>
            <div style="width: 15%;"><%= obj.category_name %></div>
            <div style="width: 15%;"><%= obj.plate_no %></div>
            <div style="width: 15%;"><%= obj.customer_name %></div>
            <div style="width: 15%;"><%= obj.buyer_name %></div>
            <div style="width: 10%;"><%= obj.branch_number %></div>
            <span style="clear: both;display:block"></span>
        </div>

    <% } %>
</script>

{{--套版详情--}}
<script type="text/html" id="other_plate">
    <%for( i = 0; i < other_plate.length; i++) {%>
        <%var obj = other_plate[i];%>
        <div class="other_plate_div" data-type="other_plate" data-name="<%= obj.name %>" onClick="create_product_control.other_plate(this);"><%= obj.name %></div>
    <% } %>
    <div style="clear:both"></div>
</script>

{{--固定版详情--}}
<script type="text/html" id="fixed_plate">
    <%for( i = 0; i < fixed_plate.length; i++) {%>
        <%var obj = fixed_plate[i];%>
        <div class="other_plate_div" data-type="fixed_plate" data-name="<%= obj.name %>" onClick="create_product_control.other_plate(this);"><%= obj.name %></div>
    <% } %>
    <div style="clear:both"></div>
    <div class="sort" ></div>
</script>

{{--套版详情--}}
<script type="text/html" id="colourplate">
    <%for( i = 0; i < colourplate.length; i++) {%>
        <%var obj = colourplate[i];%>
        <div style="padding:10px;">
            <div><%= obj.name %></div>
            <%for( s = 0; s < obj.model.length; s++) {%>
                <div class="other_plate_div colourplate-<%= obj.id %>" data-type="colourplate-<%= obj.id %>" data-type_name="<%= obj.name %>" data-name="<%= obj.model[s].name %>" onClick="create_product_control.other_plate(this);"><%= obj.model[s].name %></div>
            <% } %>
            <div style="clear:both"></div>
         </div>
    <% } %>

</script>

{{--已选版详情--}}
<script type="text/html" id="branch">
    <div>版名称：<%= plate_name %></div>
    <% if(branch.other_plate && branch.other_plate.length > 0){ %><div>套版：<span><%= branch.other_plate[0].name %></span></div><% } %>
    <% if(branch.fixed_plate && branch.fixed_plate.length > 0){ %><div>固定板：
    <%for( i = 0; i < branch.fixed_plate.length; i++) {%>
        <span style="margin-right:10px;"><%= branch.fixed_plate[i].sort %>、<%= branch.fixed_plate[i].name %>&nbsp;</span>
    <% } %>
    </div><% } %>
    <% if(branch.colourplate[0].group && branch.colourplate[0].group.length > 0){ %><div>套版：
    <%for( i = 0; i < branch.colourplate[0].group.length; i++) {%>
        <span style="margin-right:10px;"><%= branch.colourplate[0].group[i].name %><%= branch.colourplate[0].group[i].model %></span>
    <% } %>
    </div><% } %>
</script>

@endsection
