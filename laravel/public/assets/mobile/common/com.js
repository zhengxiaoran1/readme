//发布内容的控制对象
var release_control = {
    $show: "",
    images: [], //存图片对象
    img_num: 9, //限制最大的发布数量
    ready: false, //是否有一张图片加载好了
    count: 0, //加载完成计数器
    upload: function(_input, e) {
        //_input为对应dom e为file的事件对象  func为加载好一张图片后的回调函数
        // console.log(this);
        var _this = this; //保存对象的引用
        var _now_len = this.images.length;

        if (this.imgcheck(_now_len, this.img_num)) { //如果发布数量没有超过
            //console.log("长度" + _input.files.length);
            var len = _input.files.length; //获取文件长度
            if (len === 0) {
                return;
            } //没选择文件不需要操作

            for (var i = 0, l = len; i < l; i++) {
                if (this.images.length == this.img_num) {
                    this.tip(function() {
                        BJUI.alertmsg('warn', "最多只能发布" + this.img_num + "张图片")
                        //console.log("选择图片过多，限制");
                    });
                    return;
                }


                //插入dom 同时注册滑动删除事件
                $("<div class='swiper-container list-" + (_now_len + i) + " del-" + ($('.swiper-container').length) + "' ><div class='swiper-wrapper'><img  class='swiper-slide'><span class='swiper-slide'></span><div class='img-del' data-id='" + ($('.swiper-container').length) + "'>立即删除</div></div></div>").appendTo(this.$show);

                var oFile = e.target.files[i];
                //实例化FileReader API
                var oFReader = new FileReader();
                oFReader.readAsDataURL(oFile); //读取内容
                (function(i) {
                    var img = new Image; //图片对象
                    // console.log("这个this是什么："+_this);
                    _this.images.push(img);

                    oFReader.onload = function() {
                        img.src = this.result;
                        //console.log("图片编码："+img.src);
                    }
                    img.onload = function() {
                        //console.log(_now_len + i)
                        _this.$show.children().eq(_now_len + i).find("img").attr("src", img.src); //在上次的基础上
                        _this.ready = true;
                        _this.count++;
                        _this.$show.children().eq(_now_len + i).height(_this.$show.children().eq(_now_len + i).find("img").width() + 'px');
                        _this.$show.children().eq(_now_len + i).find('.swiper-wrapper').height(_this.$show.children().eq(_now_len + i).find("img").width() + 'px');
                    }
                })(i);
            }
            var mySwiper = new Swiper(".swiper-container", {
                autoHeight: true,
                onSlideChangeEnd: function(swiper) {
                    //console.log("滑动结束了")
                    if (swiper.previousIndex == 0) {
                        // console.log("被移除的序号：" + swiper.container.index());
                        _this.remove(swiper.container.index());
                        _this.count--;
                        swiper.container.remove();
                    } //切换结束时，告诉我现在是第几个slide
                }
            });
        } else {
            this.tip(function() {
                BJUI.alertmsg('warn', "最多只能发布" + this.img_num + "张图片")
            });
        }
    },
    imgcheck:function(len, num) {
        //传入长度和限制图片数量
        if (len == num) {
            return false;
        }
        return true;
    },
    empty: function() { //清空图片对象
        this.images = [];
        this.$show.html("");
        this.count = 0;
        this.ready = false;
    },
    remove: function(index) { //数据上删除
        this.images.splice(index, 1);
    },
    removeHtml:function(index){
        this.count--;
        this.$show.children().eq(index).remove()
    },
    tip: function(func) { //图片全部加载好之后的提示函数
        var _this = this; //保存对象引用
        var timer = setInterval(function() {
            if (_this.count == _this.images.length) {
                clearInterval(timer);
                func.apply(_this);
            }
        }, 50);
    }

}



var page_control = {
    _total:"",
    _page:"",
    _prev:"",
    _next:"",
    page_html_box:"",
    page:1,
    total:1,
    init:function(total,page,callback){
        var that = this;
        that.pageHtml(callback);
        that.pageObj();
        that.page = parseInt(page);
        that.total = parseInt(total);

        that._total.text(total);
        that._page.text(page);

        if(total == 1 ){
            that._prev.hide();
            that._next.hide();
        }else{
            if(page == 1){
                that._prev.hide();
                that._next.show();
            }
            if(page == total){
                that._prev.show();
                that._next.hide();
            }
            if(page != 1 && page != total){
                that._prev.show();
                that._next.show();
            }
        }
    },
    pageLoad:function(){},
    pageHtml:function(callback){
        this.page_html_box.html("").append('<div class="page" style="text-align: center;">' +
            '            <a href="javascript:;" class="layui-laypage-prev page-prev" onclick="page_control.pagePrev(function(){'+callback+'(function(){page_control.pageLoad()});});"><i class="layui-icon layui-icon-left"></i></a>' +
            '            第&nbsp;<span class="pagenum"></span>&nbsp;页' +
            '            <a href="javascript:;" class="layui-laypage-next page-next" onclick="page_control.pageNext(function(){'+callback+'(function(){page_control.pageLoad()});});"><i class="layui-icon layui-icon-right"></i></a>' +
            '            <div style="position: absolute;top: 10px;left: 20px;">共&nbsp;[&nbsp;<span class="total_page"></span>&nbsp;]&nbsp;页</div>' +
            '        </div>');
    },
    pageObj:function(){
        this._total = this.page_html_box.children().find(".total_page");
        this._page  = this.page_html_box.children().find(".pagenum");
        this._prev  = this.page_html_box.children().find(".page-prev");
        this._next  = this.page_html_box.children().find(".page-next");
    },
    page_total_init:function(){
        this.pageObj();
        this.page = parseInt(this._page.text());
        this.total = parseInt(this._total.text());
    },
    pagePrev:function(func){
        this.page_total_init();
        this.page = this.page - 1;
        if(this.page < 1){
            this.page = 1;
        }
        func();
    },
    pageNext:function(func){
        this.page_total_init();
        this.page = this.page + 1;
        if(this.page > this.total){
            this.page = this.total;
        }
        func();
    }
}


var ygt_control = {
    token:"",
    url:"",
    data:{},
    type:"post",
    ajax:function(func){
        var that = this;
        $.ajax({
            type: that.type,
            url:that.url,
            data :that.data,
            dataType: "json",
            loadingmask: true,
            beforeSend: function (XMLHttpRequest) {
                XMLHttpRequest.setRequestHeader("token", that.token );
            },
            success: function (data) {
                func(data);
            },
            error:function(){
                window.location.href = '/mobile/login'
            }
        });
    },

}


var create_product_control = {
    branch:[],
    branchTypeField:"",
    img_num:0,
    product:"",
    buyer:"",
    mterial:[],
    mterialTypeField:"",
    customer:"",
    mterialType:"",
    branchType:"",
    ajaxUrlParam:{},
    liandong:function(that){
        $('.'+$(that).data('type')).hide().attr("disabled","disabled");

        if($(that).val()){
            $(that).css('right',"220px");
            $('.liandong_' + $(that).val()).show().removeAttr("disabled");
        }else{
            $(that).css('right',"20px");
        }
    },
    other_plate:function(that){
        var _this = this;
        type = $(that).data('type');
        if(type.indexOf('colourplate') != -1){
            //套版
            colourplate = false;
            $.each(_this.branch.branch.colourplate[0].group,function(i,v){
                if(v.name == $(that).data('type_name')){
                    colourplate = true;
                    v.model = $(that).data('name') + "";
                }
            })
            if(!colourplate){
                _this.branch.branch.colourplate[0].group.push({
                    model:$(that).data('name') + "",
                    name:$(that).data('type_name') + ""
                });
            }
            $('.colourplate .'+type).attr('id',"");
            $(that).attr('id','acivition');
        }else if(type == "fixed_plate"){
            //固定版

            if($(that).attr('id') == "acivition"){

                var fixed_plate = false;
                var sort = 0;
                $.each(_this.branch.branch.fixed_plate,function(i,v){
                    if(v.name == $(that).data('name')){
                        fixed_plate = true;
                        sort = i;
                    }
                });
                if(fixed_plate){
                    _this.branch.branch.fixed_plate.splice(sort, 1);
                }
                $(that).attr('id',"")

            }else{

                var fixed_plate = true;
                $.each(_this.branch.branch.fixed_plate,function(i,v){
                    if(v.name == $(that).data('name')){
                        fixed_plate = false;
                    }
                })

                if(fixed_plate){
                    _this.branch.branch.fixed_plate.push({
                        sort:0 + "",
                        name:$(that).data('name') + ""
                    });
                }


                $(that).attr('id','acivition');

            }
            $('.sort').html("");
            $.each(_this.branch.branch.fixed_plate,function(i,v){
                v.sort = i + 1 + "";
                $('.sort').append('<span>'+v.sort+'、'+v.name+'</span>');
            })

        }else{
            _this.branch.branch.other_plate = [];
            _this.branch.branch.other_plate.push({
                name:$(that).data('name') + "",
                num:""
            })
            $('.' + type + ' .other_plate_div').attr('id',"");
            $(that).attr('id','acivition');
        }
    },
    mterialSelect:function(val){
        $('.mterial-type-list').hide();
        if(val){
            $('.mterial-type-list-' + val).show();
        }
    },
    getMterial:function(func,ini=false){
        if(ini){
            //初始化页码
            page_control.page = 1;
            page_control.page_html_box = $('#mterial-page');
        }
        var that = this;


        var mterial = [];
        ygt_control.data = {category_id:this.mterialType,company_id:"",customer_id:this.customer,"filter_type":"0","is_hide_common":"0","keyword":"","lift":"1","order":"","page":page_control.page,"plate_id":"","warning_number":"0"};
        ygt_control.url  = this.ajaxUrlParam.getAssemblageMaterialDealList;
        ygt_control.ajax(function (data) {
            var _html = template('mterial-list', data.data);
            $('.mterial').html("").append(_html);
            mterial = $('input[name='+that.mterialTypeField+']').val();
            if(mterial){
                mterial = JSON.parse(mterial);
                $.each(data.data.list,function(i,v){
                    $.each(v.supplier_list,function(k,val){
                        $.each(mterial,function(s,value){
                            if(val.material_id == value.id){
                                $('.mterialId-' + val.material_id).prop("checked", true);
                            }
                        })
                    })
                })
            }
            if(func) func();
            page_control.init(data.paging.total_page,data.paging.page,'create_product_control.getMterial');
        });
    },
    mterialChange:function(that,func){
        this.mterialType = $(that).data('category_id');
        mterialTypeField = $(that).data('type');

        ygt_control.data = {pid:"2"};
        ygt_control.url  = this.ajaxUrlParam.treeList;
        ygt_control.ajax(function (data) {
            if(data.code){
                BJUI.alertmsg('warn', data.message)
            }else{
                var _html = template('mterial-type', data);
                $('.mterial-type').html("").append(_html);
                if(func) func();
            }
        });

        this.getMterial(func,true);
        $('.mterial-list').show();

    },
    branchChange:function(that,ini=false){

        if(ini){
            //初始化页码
            page_control.page = 1;
            page_control.page_html_box = $('#branch-page');
        }

        if(that){
            this.branchType = $(that).data('category_id');
            this.branchTypeField = $(that).data('type');
        }


        ygt_control.data = {category_id:this.branchType,customer_id:this.customer,keyword:"",page:page_control.page,plate_name_id:""};
        ygt_control.url  = this.ajaxUrlParam.list;
        ygt_control.ajax(function (data) {
            if(data.code){
                BJUI.alertmsg('warn', data.message)
            }else{
                var _html = template('branch-list', data);
                $('.branch-list').show();
                $('.branch').html("").append(_html);
                page_control.init(data.paging.total_page,data.paging.page,'create_product_control.branchChange');
            }
        });
    },
    chengenBuyer:function(that){
        if(this.customer){
            $('.buyer-list').hide();
            $('.buyer_id_input').val($(that).data('id'));
            $('.buyer_id').html($(that).html());
        }else{
            BJUI.alertmsg('warn', "当前所选单位名称数据有误，请重试！");
        }
    },
    buyer_id:function(func){
        var that = this;
        ygt_control.data = {customer_id:this.customer};
        ygt_control.url  = this.ajaxUrlParam.getBuyersList;
        ygt_control.ajax(function (data) {
            var _html = template('buyer-list', data);
            $('.product-info .buyer_id').html("").append(_html);

            if(that.buyer){
                $('.buyer_id').find("option[value="+that.buyer+"]").attr("selected",true);
            }

            if(func){
                func();
            }
        });

    },
    chengenCustomer:function(that){
        this.customer = $(that).data('id');
        console.log(this.customer);
        if(this.customer){
            $('.customer-list').hide();
            $('.customer_id_input').val(this.customer);
            $('.customer_id').html($(that).html());
        }else{
            BJUI.alertmsg('warn', "当前所选客户数据错误，请选择其他客户");
        }
    },
    customer_id:function(func){
        var that = this;
        ygt_control.data = {customer_level:"",is_all:"0",keyword:""};
        ygt_control.url  = this.ajaxUrlParam.letterList;
        ygt_control.ajax(function (data) {
            if(data.code){
                BJUI.alertmsg('warn', data.message)
            }else{

                var _html = template('customer-list', data);
                $('.product-info .customer_id').append(_html);

                if(that.customer){
                    $('.customer_id').find("option[value="+that.customer+"]").attr("selected",true);
                }

                if(func){
                    func();
                }
            }
        });
    },
    data_init:function(data,func){
        var that = this;
        $.each(data,function(i,v){
            $.each(v.field_list,function(s,t){
                if(t.field_type == 9){
                    var strs= new Array(); //定义一数组
                    strs=t.default_img_url.split(","); //字符分割
                    $.each(strs,function(i,v){
                        if(v){
                            that.img_num++;
                            $('.update-release-pics').append(
                                '<div style="width: 20%; float: left; overflow: hidden; position: relative; height: 188px;">'+
                                '<img src="'+v+'" style="width: 100%;"/>'+
                                '<div class="update-img-del">立即删除</div>'+
                                '</div>');
                        }
                    })
                    $('input[name='+t.deal_field_name+']').val(t.default_img_id);
                }else if(t.field_type == 4){
                    mterial = [];
                    if(t.default_product_list){
                        $.each(t.default_product_list,function(k,val){
                            mterial.push({id:val.id,is_purchase:0});
                            var data = [];
                            data.push({
                                number:"",
                                field:val.custom_fields_text,
                                company:val.supplier,
                                name:val.product_name,
                                id:val.id,
                                type:t.deal_field_name
                            })
                            var mterialInfo = {data:data};
                            var _html = template('mterial-list-yes', mterialInfo);
                            $('.'+t.deal_field_name).prepend(_html);
                        })
                    }
                    $('input[name='+t.deal_field_name+']').val(JSON.stringify(mterial));
                }else if(t.field_type == 17){
                    $('input[name='+t.deal_field_name+']').val(JSON.stringify(t.default_plate_list));
                    if(t.default_plate_list){
                        var _html = template('branch', t.default_plate_list);
                        $('.'+t.deal_field_name).html("").append(_html);
                    }
                }else if(t.field_type == 15 || t.field_type == 16){
                    var strs = new Array(); //定义一数组
                    strs=t.default_value.split(","); //字符分割
                    $.each($('input[name='+t.deal_field_name+']'),function(s,y){
                        $(y).val(strs[s]);
                    })
                }else if(t.field_type == 18){
                    that.customer = t.default_select_id;
                }else if(t.field_type == 19){
                    that.buyer = t.default_select_id;
                }else if(t.field_type == 13){
                    var strs= new Array(); //定义一数组
                    strs=t.default_value.split(","); //字符分割
                    $.each($('input[name='+t.deal_field_name+']'),function(s,y){

                        if(strs.indexOf($(y).val()) != -1){
                            $(y).attr('checked','checked');
                        }
                    })
                }
            })
        })


        if(func){
            func();
        }
    },
    getChanpinPre:function(func){
        var _taht = this;
        var proThat = $('.pro');
        var createProductThat = $('.create-product');

        ygt_control.url  = this.ajaxUrlParam.createChanpinPre;
        ygt_control.ajax(function (data) {

            if(data.code){
                BJUI.alertmsg('warn', data.message)
            }else{

                proThat.hide();
                release_control.$show = $(".product-info .release-pics");
                release_control.empty();
                createProductThat.show();

                var _html = template('product-info', data.data );
                $('.product-info').html("").append(_html);
                _taht.customer_id(func);

                if(_taht.product){
                    _taht.data_init(data.data.data_list,func);
                }

                if(_taht.customer){
                    console.log(_taht.buyer_id);
                    _taht.buyer_id(func);
                }
            }

        });
    },
    getOrderType:function(func) {
        var that = this;
        ygt_control.data = {category_id:""};
        ygt_control.url  = this.ajaxUrlParam.getOrderTypeAll;
        ygt_control.ajax(function (data) {
            if(data.code){
                BJUI.alertmsg('warn', data.message)
            }else{
                var _html = template('region-list', data.data.data);
                $('.pro_type_list').append(_html);
                that.customer_id(func);
            }
        });

    },
    clickBranch:function(that){

        var _this = this;
        _this.branch = {
            branch:{
                colourplate:[{
                    group:[],
                    num:""
                }],
                fixed_plate:[],
                other_plate:[]
            },
            category_id:$(that).data('category_id'),
            plate_id:$(that).data('id'),
            plate_name:$(that).data('plate_name')
        };

        ygt_control.data = {id:$(that).data('id')};
        ygt_control.url  = this.ajaxUrlParam.getPlateBranchDetail;
        ygt_control.ajax(function (data) {
            if(data.code){
                BJUI.alertmsg('warn', data.message)
            }else{
                $('.branch-info').show();
                if(data.data.branch.other_plate){
                    $('.other_plate').show();
                    $('.colourplate,.fixed_plate').hide();
                    var _html = template('other_plate', data.data.branch);
                    $('.other_plate_list').html("").append(_html);
                }else{
                    $('.other_plate').hide();

                    var _html = template('fixed_plate', data.data.branch);
                    $('.fixed_plate_list').html("").append(_html);

                    var _html = template('colourplate', data.data.branch);
                    $('.colourplate_list').html("").append(_html);

                    $('.colourplate,.fixed_plate').show();
                }


            }

        });
    }
}


