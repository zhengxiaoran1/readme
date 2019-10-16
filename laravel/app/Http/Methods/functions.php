<?php
/**
 * Created by PhpStorm.
 * User: huangjiangnan
 * Date: 2019/8/8
 * Time: 13:54
 */

/**
 * POST 请求
 * @param string $url
 * @param array $param
 * @param boolean $post_file 是否文件上传
 * @return string content
 */
function http_post($url,$param,$post_file=false, $header=[]){
    $oCurl = curl_init();
    if(stripos($url,"https://")!==FALSE){
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
    }
    if (!empty($header)){
        curl_setopt($oCurl,CURLOPT_HTTPHEADER,$header);
    }

    if (is_string($param) || $post_file) {
        $strPOST = $param;
    } else {
        $aPOST = array();
        foreach($param as $key=>$val){
            $aPOST[] = $key."=".urlencode($val);
        }
        $strPOST =  join("&", $aPOST);
    }
    curl_setopt($oCurl, CURLOPT_URL, $url);
    curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt($oCurl, CURLOPT_POST,true);
    curl_setopt($oCurl, CURLOPT_POSTFIELDS,$strPOST);
    $sContent = curl_exec($oCurl);
    $aStatus = curl_getinfo($oCurl);
    curl_close($oCurl);
    if(intval($aStatus["http_code"])==200){
        return $sContent;
    }else{
        return false;
    }
}
function cdir($savepath){
    $dir = $savepath;
    if(is_dir($dir)){
        return true;
    }
    if(@mkdir($dir, 0777, true)){
        return true;
    } else {
        return false;
    }
}
function p($param){
    echo "<pre>";
    print_r($param);
    echo "</pre>";
}

function x($param){
    echo "<pre>";
    print_r($param);
    echo "</pre>";
    exit;
}


function isAjax(){
    return request()->isMethod('post');
}


//把时间换算城分钟 小时等
function dealTime($time){
    if(!is_numeric($time))
        $time=intval($time);
    $num=time()-$time;
    if($num < 60)
        return '刚刚';
    elseif($num >= 60 && $num <3600)
        return floor($num/60) .'分钟前';
    elseif($num >= 3600 && $num <86400 )
        return floor( $num / 3600 ) .'小时前';
    elseif($num >= 86400 && $num < 2592000)
        return floor( $num / 86400 ) .'天前';
    elseif($num >= 2592000 && $num < 31104000)
        return floor($num / 2592000  ) .'月前';
    elseif($num >= 31104000)
        return floor($num / 31104000  ) .'年前';
}



//获取订单总量
function getOrderCount($where){

   return \App\Eloquent\Zk\ChanpinOrder::where($where)->count();

}


//获取订单总单价金额总量
function getOrderSumMoney($where){

    $where['ygt_chanpin_order.company_id'] = $where['company_id'];
    unset($where['company_id']);


    return \App\Eloquent\Zk\ChanpinOrder::where($where)
        ->leftJoin('ygt_chanpin_order_detail','ygt_chanpin_order_detail.chanpin_order_id','=','ygt_chanpin_order.id')
        ->sum('ygt_chanpin_order_detail.price');

}

//获取订单间隔总时长

function getOrderCountTime($where){

    $minTime = \App\Eloquent\Zk\ChanpinOrder::where($where)->min('created_at');
    $maxTime = \App\Eloquent\Zk\ChanpinOrder::where($where)->max('created_at');

    if($minTime == $maxTime){

        //没有订单
        if(!$minTime && !$maxTime){

            //根据当前客户创建时间
            $minTime = \App\Eloquent\Zk\Customer::where(['id'=>$where['customer_id']])->select('created_at')->first()->toArray();
            $time = time() - $minTime['created_at'];

        }else{
            //只有一个订单
            $time = time() - $minTime;
        }

    }else{
        $time = $maxTime - $minTime;
    }

    return sprintf("%.2f", ($time / 3600));
}

function getAbnormaConfig($sort,$type,$company_id=""){

    $where = ['type'=>$type];
    if($company_id) $where['company_id'] = $company_id;
    $rule = \App\Eloquent\Zk\Abnormal::with(['AbnormalType'=>function($query)use($sort){
                                            $query->where(['sort'=>$sort]);
                                        },'AbnormalUser'])
                                        ->where($where);
    $rule = $company_id ? $rule->first() : $rule->get();
    if(!$rule) return false;

    return $rule->toArray();
}

//获取工单设置的所有异常 wei  20190829
function getWorkSheetConfig($sort,$type,$company_id=""){
    $where = ['type'=>$type];
    if($company_id) $where['company_id'] = $company_id;
    $rule = \App\Eloquent\Zk\Abnormal::with(['AbnormalType'=>function($query)use($sort){
        $query->where(['sort'=>$sort]);
    },'AbnormalUser'])
        ->where($where)->get();
    if(!$rule) return false;

    return $rule->toArray();
}

//检测材料id是否在某个异常的设置范围内 wei 20190902 20190917 恢复
function checkMaterial($material_id,$abnormal_id){//20190912改 wei  20190918改 material_id实际为材料分类id,兼容集合材料
    $where = [];
    $where['id'] = $abnormal_id;
    $relation_id = \App\Eloquent\Zk\Abnormal::where($where)->where('relation_id','like','%cat_id_all%')->get()->toArray();//判断是否全选
    if (!empty($relation_id)){
        return true;
    }
    $relation_id = \App\Eloquent\Zk\Abnormal::where($where)->where('relation_id','like','%cat2_id%')->first();//判断是否一级分类全选
    if (!empty($relation_id)){
        $cat2_id = explode(',',$relation_id['relation_id']);
        $pids = \App\Eloquent\Zk\Category::whereIn('pid',$cat2_id)->pluck('id')->toArray();
        if (in_array($material_id,$pids)) return true;
    }
    $relation_id = \App\Eloquent\Zk\Abnormal::where($where)->where('relation_id','like','%material_id%')->first();//判断是否二级分类全选
    if (!empty($relation_id)){
        $cat3_id = explode(',',$relation_id['relation_id']);
        if (in_array($material_id,$cat3_id)) return true;
    }
    $relation_id = \App\Eloquent\Zk\Abnormal::where($where)->pluck('relation_id')->toArray();
    $relation_id = explode(',',$relation_id[0]);
    if(in_array($material_id,$relation_id)){
        return true;
    }
    return false;
}

//重写检测材料是否在某个字段的设置范围内
/*function checkMaterialBak($material_id,$abnormal_id){
    $product = new \App\Eloquent\Ygt\Product();
    $categoryId = $product->where('id','=',$material_id)->pluck('category_id')->first();
    $abnormal = new \App\Eloquent\Ygt\Abnormal();
    $category = $abnormal->leftJoin('ygt_abnormal_field as af','af.id','=','ygt_abnormal.field_id')->where('ygt_abnormal.id','=',$abnormal_id)->select(['af.category_id','af.field_type'])->first()->toArray();
    $category = explode(',',$category['category_id']);

    if (in_array($categoryId,$category)){
        return true;
    }else{
        return false;
    }
}*/
//获取材料使用数量 wei 20190826
function getMaterialUseNum($where){
    $receive = getReceiveNum($where);//领取数量
    $residual = getResidualNum($where);//余品数量
    $retreat = getRetreatNum($where);//退品数量
    $scrap = getScrapNum($where);//废品数量
    $use = $receive-$residual-$retreat-$scrap;//真正的使用数量 领-余-退-废
    return $use;
}

//获取领取材料数量(不区分是哪个员工领取的,这里 领 退 废 余 用 都是整个工序的数量) wei 20190826
function getReceiveNum($where){
    $wheres = [];
    $wheres['ygt_stock.company_id'] = $where['company_id'];
    $wheres['product_id'] = $where['material_id'];
    $order_process_course_id_arr = \App\Eloquent\Zk\OrderProcessCourse::where('order_process_id','=',$where['order_process_id'])->pluck('id')->toArray();

    $receiveNum = \App\Eloquent\Zk\Stock::where('ygt_stock.relate_type','=',1)
        ->where($wheres)
        ->whereIn('relate_id',$order_process_course_id_arr)
        ->sum('number');
    if ($receiveNum < 0){
        $receiveNum = -$receiveNum;
    }
    return $receiveNum;
}

//获取余品材料数量 wei 20190826
function getResidualNum($where){
    $wheres = [];
    $wheres['opmsd.order_process_id'] = $where['order_process_id'];
    $wheres['opmsd.relate_id'] = $where['material_id'];
    $wheres['opmsd.type'] = 1;
    $wheres['opmsd.company_id'] = $where['company_id'];
    $residualNum = \App\Eloquent\Zk\OrderProcessMaterialSubmit::leftJoin('ygt_order_process_material_submit_detail as opmsd','opmsd.order_process_material_submit_id','=','ygt_order_process_material_submit.id')
        ->where($wheres)
        ->sum('residual_number');
    return $residualNum;
}

//获取材料废品数量 wei 20190826
function getScrapNum($where){
    $wheres = [];
    $wheres['opmsd.order_process_id'] = $where['order_process_id'];
    $wheres['opmsd.relate_id'] = $where['material_id'];
    $wheres['opmsd.type'] = 1;
    $wheres['opmsd.company_id'] = $where['company_id'];
    $scrapNum = \App\Eloquent\Zk\OrderProcessMaterialSubmit::leftJoin('ygt_order_process_material_submit_detail as opmsd','opmsd.order_process_material_submit_id','=','ygt_order_process_material_submit.id')
        ->where($wheres)
        ->sum('scrap_number');
    return $scrapNum;
}

//获取材料退品数量 wei 20190826
function getRetreatNum($where){
    $wheres = [];
    $wheres['opmsd.order_process_id'] = $where['order_process_id'];
    $wheres['opmsd.relate_id'] = $where['material_id'];
    $wheres['opmsd.type'] = 1;
    $wheres['opmsd.company_id'] = $where['company_id'];
    $retreatNum = \App\Eloquent\Zk\OrderProcessMaterialSubmit::leftJoin('ygt_order_process_material_submit_detail as opmsd','opmsd.order_process_material_submit_id','=','ygt_order_process_material_submit.id')
        ->where($wheres)
        ->sum('retreat_number');
    return $retreatNum;
}

//获取成品相关材料 wei 20190902
function getChanpinMaterial($where){
    $wheres['ygt_order.id'] = $where['order_id'];
    $wheres['ygt_stock.company_id'] = $where['company_id'];
    $wheres['ygt_stock.relate_type'] = 1;
    return \App\Eloquent\Zk\Stock::leftJoin('ygt_order_process_course as opc','opc.id','=','ygt_stock.relate_id')
        ->leftJoin('ygt_order_process as op','op.id','=','opc.order_process_id')
        ->leftJoin('ygt_order','ygt_order.id','=','op.order_id')
        ->where($wheres)->pluck('ygt_stock.product_id')->toArray();
}

//获取成品片料规格总面积 wei 20190903
function getMaterialArea($where){
    $chanpinNum = \App\Eloquent\Zk\OrderProcessCourseGradation::where('order_process_course_id','=',$where['order_process_course_id'])->sum('submit_num');//获取打包工序提交的完成数量,即成品数量
    $plgg = \App\Eloquent\Zk\ChanpinOrderInfo::where('id','=',$where['chanpin_id'])->pluck('pianliaoguige')->first();
    $areaAll = '';
    if ($plgg && $chanpinNum){
        $long = explode(',',$plgg)[0];
        $wide = explode(',',$plgg)[1];
        $area = ((int)$long/100)*((int)$wide/100);
        $areaAll = $area*$chanpinNum*2;
    }
    if ($areaAll) return $areaAll;
}

//获取材料使用总重量 wei 20190903
function getMaterialWeight($where){
    $wheres = [];
    $wheres['ygt_stock.company_id'] = $where['company_id'];
    $wheres['product_id'] = $where['material_id'];
    $orderProcessId = \App\Eloquent\Zk\OrderProcess::where('order_id','=',$where['order_id'])->pluck('id')->toArray();
    $order_process_course_id_arr = \App\Eloquent\Zk\OrderProcessCourse::whereIn('order_process_id',$orderProcessId)->pluck('id')->toArray();
    $receiveNum = \App\Eloquent\Zk\Stock::where('ygt_stock.relate_type','=',1)
        ->where($wheres)
        ->whereIn('relate_id',$order_process_course_id_arr)
        ->sum('number');
    if ($receiveNum < 0){
        $receiveNum = -$receiveNum;//领取材料数量
    }
    $wheres = [];
    $wheres['opmsd.relate_id'] = $where['material_id'];
    $wheres['opmsd.type'] = 1;
    $wheres['opmsd.company_id'] = $where['company_id'];
    $retreatNum = \App\Eloquent\Zk\OrderProcessMaterialSubmit::leftJoin('ygt_order_process_material_submit_detail as opmsd','opmsd.order_process_material_submit_id','=','ygt_order_process_material_submit.id')
        ->where($wheres)
        ->whereIn('opmsd.order_process_id',$orderProcessId)
        ->sum('retreat_number');//退回材料数量
    $scrapNum = \App\Eloquent\Zk\OrderProcessMaterialSubmit::leftJoin('ygt_order_process_material_submit_detail as opmsd','opmsd.order_process_material_submit_id','=','ygt_order_process_material_submit.id')
        ->where($wheres)
        ->whereIn('opmsd.order_process_id',$orderProcessId)
        ->sum('scrap_number');//废品材料数量
    $residualNum = \App\Eloquent\Zk\OrderProcessMaterialSubmit::leftJoin('ygt_order_process_material_submit_detail as opmsd','opmsd.order_process_material_submit_id','=','ygt_order_process_material_submit.id')
        ->where($wheres)
        ->whereIn('opmsd.order_process_id',$orderProcessId)
        ->sum('residual_number');//余品材料数量
    //实际使用数量
    $MaterialUseNum = $receiveNum-$retreatNum-$scrapNum-$residualNum;

    $weight = $MaterialUseNum*1000;//单位公斤转换为克
    return $weight;
}

//获取材料损耗比 wei 20190912
function getMaterialLoss($where){
    $useNum = getMaterialUseNum($where);
    $scrapNum = getScrapNum($where);
    if ($useNum == 0){
        return 0;
    }
    return $scrapNum/$useNum;
}

//获取材料平方克重 wei 20190916
function getMaterialSQWeight($where){
    if (getMaterialArea($where) == 0){
        return 0;
    }
    $SQWeight = getMaterialWeight($where)/getMaterialArea($where);
    if ($SQWeight){
        return $SQWeight;
    }else{
        return 0;
    }
}

//判断材料是否为筒料或纸 wei
function  checkMaterialTl($material_id){
    $cat_id = \App\Eloquent\Zk\Product::where('id','=',$material_id)->pluck('category_id')->first();
    $pid = \App\Eloquent\Zk\Category::where('id','=',$cat_id)->pluck('pid')->first();
    $bool = \App\Eloquent\Zk\Category::where('id','=',$pid)
        ->where(function ($query){
            $query->where('cat_name','=','筒料')->orwhere('cat_name','=','纸');
        })->first();
    if ($bool) return true;
    return false;
}

//公式转换计算  20190912 备份 wei  20190917恢复
function ReturnRuleFormula($where,$rule){
    $rule = array_filter(explode('_',$rule));
    $newRule = "";
    foreach ($rule as $v){
        $ruleInfo = \App\Eloquent\Zk\AbnormalRultParameter::leftJoin('ygt_abnormal_field','ygt_abnormal_field.field_value','=','ygt_abnormal_rult_parameter.id')
            ->where([['ygt_abnormal_field.field_name','like',"%".$v."%"]])->first();
        if($ruleInfo){
            $ruleInfo = $ruleInfo->toArray();
            switch ($ruleInfo['rult_type']){
                case "1":
                    $funcName = $ruleInfo['rult'];
                    $newRule .= $funcName($where);
                    break;
                case "0":
                    $newRule = getMaterialProperty($ruleInfo['rult'],$where);
            }
        }else{
            $newRule .= $v;
        }

    }

    try{
        return( sprintf("%.2f", eval("return $newRule;")) );
    }catch(Throwable $e){
            return 0;
    }

}

//获取值 wei
/*function ReturnRuleFormulaBak($where,$field_id){
    $field_value = \App\Eloquent\Ygt\AbnormalField::where('id','=',$field_id)->pluck('field_value')->first();
    $newRule = "";

    $ruleInfo = \App\Eloquent\Ygt\AbnormalRultParameter::where('id','=',$field_value)->first();
    if($ruleInfo){
        $ruleInfo = $ruleInfo->toArray();
        switch ($ruleInfo['rult_type']){
            case "1":
                $funcName = $ruleInfo['rult'];
                $newRule .= $funcName($where);
                break;
            case "0":
                $newRule = getMaterialProperty($ruleInfo['rult'],$where);
        }
    }

    try{
        return( sprintf("%.2f", eval("return $newRule;")) );
    }catch(Throwable $e){
        return 0;
    }
}*/

//重构数据
function restructureAbnormalUserData($abnormalUser){

    if(!$abnormalUser) return false;

    $abnormalUserId = array_flip(array_unique(array_column($abnormalUser,'uid')));

    foreach ($abnormalUserId as $k=>$v){
        $abnormalUserId[$k] = [];
    }

    $newAbnormalUser = [
        'abnormal_user' =>  $abnormalUserId,
        'public'        =>  $abnormalUserId,
    ];

    foreach ($abnormalUser as $k => $v){
        if($v['relation_id']){
            $newAbnormalUser['abnormal_user'][$v['uid']][] = $v;
        }else{
            $newAbnormalUser['public'][$v['uid']] = $v;
        }
    }

    return $newAbnormalUser;
}


//比对参数，是否发送异常消息
function ruleComparison($where,$rule,$ruleUser){
    if(!$ruleUser) return false;
    $baseline = ReturnRuleFormula($where,$rule);
    $sendSms = false;

    if($ruleUser['gte_number'] && $ruleUser['lte_number']){
        if( $baseline > $ruleUser['gte_number'] || $baseline < $ruleUser['lte_number'] ){
            $sendSms = true;
        }
    }else if($ruleUser['gte_number'] ){
        if( $baseline > $ruleUser['gte_number'] ){
            $sendSms = true;
        }
    }else if($ruleUser['lte_number'] ){
        if( $baseline < $ruleUser['lte_number'] ){
            $sendSms = true;
        }
    }

    return $sendSms;

}

//根据材料分类id.获取其所有子类id wei
function getChildren($val,$companyId){
    $category = new \App\Eloquent\Zk\Category();
    $result = [];
    $crr = $category->where('pid','=',$val)->where(function ($query) use ($companyId){
        $query->where('company_id','=',$companyId)
            ->orwhere('company_id','=','0');
    })->pluck('id')->toArray();
    if ($crr){
        foreach ($crr as $v){
            $result[] = getChildren($v,$companyId);
        }
    }else{
        $result = $val;
    }
    return $result;
}

//获取系统字段 属性值 wei
function getMaterialProperty($rult,$where){
    $material_id = $where['material_id'];
    $table = explode(',',$rult)[0];
    $field = explode(',',$rult)[1];
    $sql = "SELECT $field from $table WHERE id = $material_id";
    $value = \Illuminate\Support\Facades\DB::select($sql);
    if ($value){
        $value = $value[0]->$field;
    }
    return $value;
}

//获取材料可用库存
function getAvailableStock($where){
    $wheres['res_id'] = $where['material_id'];
    $wheres['res_type'] = $where['res_type'];
    $availableNumber = \App\Eloquent\Zk\StorehouseRes::where($wheres)
        ->where(function ($query) use ($where){
            if (isset($where['storehouse_id'])){
                $query->where('storehouse_id','=',$where['storehouse_id']);//成品调库 是使用
            } else if (isset($where['stockArr'])){
                $query->whereIn('storehouse_id',$where['stockArr']);
            }
        })
        ->pluck('available_number')->first();
    if ($availableNumber){
        return $availableNumber;
    }else{
        return 0;
    }
}


