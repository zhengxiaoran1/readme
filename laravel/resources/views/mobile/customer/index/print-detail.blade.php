<style>
    .companyName {
        font-weight: normal;
    }
    #list td{
        text-align : center;
        height: 40px;
    }
</style>
<script src="/assets/vendor/b-jui/js/jquery-1.11.3.min.js"></script>

<h1 align="center" class="companyName">{{$data['companyName']}}</h1>
<h1 align="center">交货单</h1>
<table width="900" align="center">
    <tr>
        <td align="left">收货单位：{{$data['buyerName']}}</td>
        <td align="right">№：{{explode('【',$data['sn'])[0]}}</td>
    </tr>
</table>

<table width="900" align="center">
    <tr>
        <td align="left">收货地址：{{$data['billList'][0]['delivery_address']}}</td>
        <td align="right">{{$data['date']}}</td>
    </tr>
</table>
<table border="1px solid black" cellspacing="0" align="center" width="1000" id="list">

    <thead>
    <tr>
        <th data-options="{name:'product_name', align:'center'}">产品名称</th>
        <th data-options="{name:'bh', align:'center'}">编&emsp;号</th>
        <th data-options="{name:'finished_specification', align:'center'}">成品规格</th>
        <th data-options="{name:'out_packages', align:'center'}">件&emsp;数</th>
        <th data-options="{name:'packs', align:'center'}">每件条数</th>
        <th data-options="{name:'out_zero', align:'center'}">零&emsp;头</th>
        <th data-options="{name:'out_number', align:'center'}">条&emsp;数</th>
        <th data-options="{name:'all_weight', align:'center'}">重量（吨）</th>
        <th data-options="{name:'price', align:'center'}">单价（元）</th>
        <th data-options="{name:'total_price', align:'center'}">金额（元）</th>
    </tr>
    </thead>

    @foreach($data['billList'] as $val)

        @foreach($val['data'] as $value)
            <tr>
                <td>{{$value['product_name']}}</td>
                <td>{{isset($value['bh']) ? $value['bh'] : ''}}</td>
                <td>{{$value['finished_specification']}}</td>
                <td>{{$value['out_packages']}}</td>
                <td>{{$value['packs']}}</td>
                <td>{{$value['out_zero']}}</td>
                <td>{{$value['out_number']}}</td>
                <td>{{$value['all_weight'] ? sprintf("%.2f", $value['all_weight']) : ''}}</td>
                <td>{{round($value['price'],2)}}</td>
                <td><span class="out_money">{{$value['out_money']}}</span></td>
            </tr>
        @endforeach
        <tr>
            <td colspan="9">合计人民币（大写）： <span class="all_money" style="font-weight: bold;font-size: 20px"></span></td>
            <td ><span class="all_out_money"></span></td>
        </tr>
    @endforeach

</table>

<table width="900" align="center">
    <tr>
        <td>制票：{{$data['userName']}}</td>
        <td>送货单位及经手人：{{$data['logistics'] != '' ? $data['logistics'] : '无'}}</td>
    </tr>
    <tr>
        <td>收货单位及经手人（盖章）：</td>
    </tr>
</table>

<script>
    function toDecimal2(val,len) {
        debugger;
        var f = parseFloat(val);
        if (isNaN(f)) {
            return false;
        }
        var s=val.toString();
        if(s.indexOf(".")>0){
            var f = s.split(".")[1].substring(0,len)
            s=s.split(".")[0]+"."+f
        }
        var rs = s.indexOf('.');
        if (rs < 0) {
            rs = s.length;
            s += '.';
        }
        while (s.length <= rs + len) {
            s += '0';
        }
        return s;
    }
    all_out_money = 0.00;
    $.each($(".out_money"),function () {
        all_out_money += parseFloat($(this).html());
    })
    $(".all_out_money").html(toDecimal2(all_out_money,2));

    $(".all_money").html(numberToUpper(all_out_money))

    function numberToUpper(money) {
        var cnNums = new Array("零", "壹", "贰", "叁", "肆", "伍", "陆", "柒", "捌", "玖"); //汉字的数字
        var cnIntRadice = new Array("", "拾", "佰", "仟"); //基本单位
        var cnIntUnits = new Array("", "万", "亿", "兆"); //对应整数部分扩展单位
        var cnDecUnits = new Array("角", "分", "毫", "厘"); //对应小数部分单位
        var cnInteger = "整"; //整数金额时后面跟的字符
        var cnIntLast = "元"; //整型完以后的单位
        var maxNum = 999999999999999.9999; //最大处理的数字
        var IntegerNum; //金额整数部分
        var DecimalNum; //金额小数部分
        var ChineseStr = ""; //输出的中文金额字符串
        var parts; //分离金额后用的数组，预定义
        if (money == "") {
            return "";
        }
        money = parseFloat(money);
        if (money >= maxNum) {
            alert('超出最大处理数字');
            return "";
        }
        if (money == 0) {
            ChineseStr = cnNums[0] + cnIntLast + cnInteger;
            return ChineseStr;
        }
        money = money.toString(); //转换为字符串
        if (money.indexOf(".") == -1) {
            IntegerNum = money;
            DecimalNum = '';
        } else {
            parts = money.split(".");
            IntegerNum = parts[0];
            DecimalNum = parts[1].substr(0, 4);
        }
        if (parseInt(IntegerNum, 10) > 0) { //获取整型部分转换
            var zeroCount = 0;
            var IntLen = IntegerNum.length;
            for (var i = 0; i < IntLen; i++) {
                var n = IntegerNum.substr(i, 1);
                var p = IntLen - i - 1;
                var q = p / 4;
                var m = p % 4;
                if (n == "0") {
                    zeroCount++;
                } else {
                    if (zeroCount > 0) {
                        ChineseStr += cnNums[0];
                    }
                    zeroCount = 0; //归零
                    ChineseStr += cnNums[parseInt(n)] + cnIntRadice[m];
                }
                if (m == 0 && zeroCount < 4) {
                    ChineseStr += cnIntUnits[q];
                }
            }
            ChineseStr += cnIntLast;
            //整型部分处理完毕
        }
        if (DecimalNum != '') { //小数部分
            var decLen = DecimalNum.length;
            for (var i = 0; i < decLen; i++) {
                var n = DecimalNum.substr(i, 1);
                if (n != '0') {
                    ChineseStr += cnNums[Number(n)] + cnDecUnits[i];
                }
            }
        }
        if (ChineseStr == '') {
            ChineseStr += cnNums[0] + cnIntLast + cnInteger;
        } else if (DecimalNum == '') {
            ChineseStr += cnInteger;
        }
        return ChineseStr;
    }
</script>
