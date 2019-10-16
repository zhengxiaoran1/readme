<?php
/**
 * Created by PhpStorm.
 * User: huangjiangnan
 * Date: 2019/8/12
 * Time: 20:16
 */

namespace App\Api\Middleware;

use App\Eloquent\Zk\Order;
use App\Eloquent\Zk\OrderProcess;
use App\Eloquent\Zk\OrderProcessCourse;
use App\Eloquent\Zk\OrderType;
use Closure;

class Abnormal{

    public function handle($request, Closure $next)
    {


        $response = $next($request);

        //接收参数，以备提取参数做校验
        $data = $request->input();

        $route = app('request')->route();
        if ($route) {
            $action = app('request')->route()->getAction();
            $controller = '@';
            if (isset($action['controller'])) {
                $controller = class_basename($action['controller']);
            }
        }

        $uid = \App\Engine\Func::getHeaderValueByName('userid');
        $userInfo = \App\Eloquent\Zk\DepartmentUser::getCurrentInfo($uid)->toArray();

        switch ($controller){

            //产品订单异常
            case "IndexController@createChanpinOrderSubmit":
                $submit_list = json_decode(htmlspecialchars_decode($data['submit_list']), true);
                $rule = getAbnormaConfig('order','money',$userInfo['company_id']);
                $title = "产品订单【".$submit_list['chanpin_list'][0]['chanpin_title']."】";
                $where = [
                    'company_id'    =>  $rule['company_id'],
                    'id'   =>  $submit_list['customer_id']
                ];

                $CustomerDataName = \App\Eloquent\Zk\Customer::where($where)->value('customer_name');
                unset($where['id']);
                $where['customer_id'] = $submit_list['customer_id'];

                $abnormal_user = restructureAbnormalUserData($rule['abnormal_user']);

                if(!$abnormal_user['abnormal_user'] && !$abnormal_user['public']) break;

                foreach ($abnormal_user['abnormal_user'] as $key => $abnormal){

                    $abnormalData = [];
                    if($abnormal){
                        foreach ($abnormal as $k => $v){
                            if($submit_list['customer_id'] != $v['relation_id']) continue;
                            $abnormalData = $v;
                        }
                    }else{
                        $abnormalData = $abnormal_user['public'][$key];
                    }
                    if(!$abnormalData) continue;
                    if(ruleComparison($where,$rule['rule'],$abnormalData)){
                        $baseline = ReturnRuleFormula($where,$rule['rule']);

                        $intro = "#".$CustomerDataName."#订单单价【".$submit_list['chanpin_list'][0]['price']."】";
                        $intro .= "rnrn历史订单平均单价【".$baseline."】";
                        $intro .= "rnrn当前预警设置参数";
                        if($abnormalData['gte_number'])
                            $intro .= "rnrn 大于：".$abnormalData['gte_number'];

                        if($abnormalData['lte_number'])
                            $intro .= "rnrn 小于：".$abnormalData['lte_number'];

                        $sendSmsData[] = [
                            'uid'           =>  $abnormalData['uid'],
                            'title'         =>  $title,
                            'intro'         =>  $intro,
                            'abnormal_id'   =>  $rule['id'],
                            'created_at'    =>  time(),
                            'is_see'        =>  0
                        ];

                    }
                }
                if(isset($sendSmsData)) \App\Eloquent\Zk\AbnormalUserMessage::insert($sendSmsData);
                break;

            /*//工单异常 wei 20190826
            case "IndexController@confirmComplete":
                //工单工艺id  传过来的实际是员工工单id  兼容安卓与苹果  wei 20190906
                if (isset($data['order_process_id'])){
                    $order_process_course_id = $data['order_process_id'];
                }
                if (isset($data['order_process_course_id'])){
                    $order_process_course_id = $data['order_process_course_id'];
                }
                //end
                $orderProcessCourseInfo = OrderProcessCourse::Where('id','=',$order_process_course_id)->first()->toArray();
                $order_process_id = $orderProcessCourseInfo['order_process_id'];
                //获取对应工单工序表信息
                $orderProcessInfo = OrderProcess::where('id', $order_process_id)->first()->toArray();
                //获取工单信息
                $orderInfo = Order::where('id', $orderProcessInfo['order_id'])->first()->toArray();

                //获取当前步骤所有工艺
                $curOrderProcessList = \App\Engine\OrderType::getCurrentOrderProcess($orderInfo['order_type'], $orderProcessInfo['process_type'])->toArray();
                //判断当前步骤的工艺是否都已经完成
                $noCompleteProcessNum = OrderProcess::all()->whereIn('process_type', $curOrderProcessList)->where('status', '!=', 4)->where('order_id', $orderProcessInfo['order_id'])->count();
                //当前工序完工后去判断异常
                if ($noCompleteProcessNum == 0){
                    $title = "工单号【".\App\Eloquent\Ygt\Order::leftjoin('ygt_order_process as op','op.order_id','=','ygt_order.id')->where('op.id','=',$order_process_id)->pluck('ygt_order.order_title')->first()."】";
                    $where = [];
                    $where[] = ['company_id', '=', $userInfo['company_id']];
                    $where[] = ['order_process_id', '=', $order_process_id];
                    $materialArr = \App\Eloquent\Ygt\OrderMaterialCourse::where($where)->pluck('material_id')->toArray();//获取该工序下所有材料
                    $rule = getWorkSheetConfig('workSheet','material',$userInfo['company_id']);
                    $abnormal_user = [];
                    if (!$rule) break;
                    foreach ($rule as $key=>$val){
                        $abnormal_user[] = restructureAbnormalUserData($val['abnormal_user']);
                    }
                    $abnormal_user = array_filter($abnormal_user);//删除数组中空元素,出现空元素原因是  后台设置异常,app没有设置该异常具体参数
                    if ($abnormal_user){
                        //遍历设置过参数的异常
                        foreach ($abnormal_user as $val){
                            if(!$val['abnormal_user'] && !$val['public']) continue;
                            if ($materialArr){//遍历材料id,并判断该材料id是否在异常设置的范围内
                                foreach ($val['public'] as $key => $abnormal) {
                                    $count = \App\Eloquent\Ygt\Abnormal::leftJoin('ygt_abnormal_field as af','af.id','=','ygt_abnormal.field_id')
                                        ->leftJoin('ygt_abnormal_rult_parameter as arp','arp.id','=','af.field_value')
                                        ->where(function ($query){
                                            $query->where('arp.name','=','材料平方克重')
                                                ->orwhere('arp.name','=','可用库存');
                                        })
                                        ->where('ygt_abnormal.id','=',$abnormal['abnormal_id'])
                                        ->count();
                                    if ($count) continue;//过滤与材料平方克重相关异常 20190916修改  wei
                                    $intro = '';
                                    $tips = '';
                                    foreach ($materialArr as $v){
                                        if(!checkMaterial($v,$abnormal['abnormal_id'])) continue;//检测该材料是否在该异常设置的范围内
                                        $where = [];
                                        $where['company_id'] = $userInfo['company_id'];
                                        $where['order_process_course_id'] = $order_process_course_id;
                                        $where['order_process_id'] = $order_process_id;
                                        $where['user_id'] = $uid;
                                        $where['material_id'] = $v;
                                        $field_id = \App\Eloquent\Ygt\Abnormal::where('id','=',$abnormal['abnormal_id'])->pluck('field_id')->toArray();
                                        $materialInfo = \App\Eloquent\Ygt\Product::leftJoin('ygt_seller_company','ygt_seller_company.id','=','ygt_product.seller_company_id')
                                            ->Where('ygt_product.id','=',$v)->select(['product_name','title'])->first();
                                        if ($field_id) {
                                            if (ruleComparison($where,$field_id[0],$abnormal)){//触发异常
                                                $baseline = ReturnRuleFormula($where,$field_id[0]);
                                                $tips .= "# ".$materialInfo['product_name']."（".$materialInfo['title']."）"." 损耗比为【".$baseline."】rnrn";
                                            }
                                        }
                                    }
                                    //生成异常消息提示
                                    if (!empty($tips)) {$intro .= $tips; }
                                    $intro .= "rnrn当前预警设置参数";
                                    if($abnormal['gte_number'])
                                        $intro .= "rnrn 大于：".$abnormal['gte_number'];

                                    if($abnormal['lte_number'])
                                        $intro .= "rnrn 小于：".$abnormal['lte_number'];

                                    $sendSmsData[] = [
                                        'uid'           =>  $abnormal['uid'],
                                        'title'         =>  $title,
                                        'intro'         =>  $intro,
                                        'abnormal_id'   =>  $abnormal['abnormal_id'],
                                        'created_at'    =>  time(),
                                        'updated_at'    =>  time(),
                                        'is_see'        =>  0
                                    ];
                                    if (!empty($tips)) {
                                        \App\Eloquent\Ygt\AbnormalUserMessage::insert($sendSmsData);
                                    }
                                }

                            }
                        }
                    }else{
                        break;
                    }
                }
                //获取未完工的工序工单的数量
                $where = [
                    ['order_id', '=', $orderProcessInfo['order_id']],
                    ['status', '!=', 4],
                ];
                $orderProcessNoCompleteNum = OrderProcess::where($where)->get()->count();
                //未完工工序工单数量为 0 时,说明整个工单都已完成
                if ($orderProcessNoCompleteNum == 0){
                    $title = "工单号【".\App\Eloquent\Ygt\Order::leftjoin('ygt_order_process as op','op.order_id','=','ygt_order.id')->where('op.id','=',$order_process_id)->pluck('ygt_order.order_title')->first()."】";
                    $where = [];
                    $where['order_id'] = $orderProcessInfo['order_id'];
                    $where['company_id'] = $userInfo['company_id'];
                    $where['order_process_id'] = $order_process_id;
                    $chanpinMaterialArr = getChanpinMaterial($where);//获取产品相关所有材料
                    $rule = getWorkSheetConfig('workSheet','material',$userInfo['company_id']);
                    $abnormal_user = [];
                    if (!$rule) break;
                    foreach ($rule as $key=>$val){
                        $abnormal_user[] = restructureAbnormalUserData($val['abnormal_user']);
                    }
                    $abnormal_user = array_filter($abnormal_user);//删除数组中空元素,出现空元素原因是  后台设置异常,app没有设置该异常具体参数
                    if ($abnormal_user){
                        //遍历设置过参数的异常
                        foreach ($abnormal_user as $val){
                            if(!$val['abnormal_user'] && !$val['public']) continue;

                            if ($chanpinMaterialArr){
                                foreach ($val['public'] as $key => $abnormal){

                                    $count = \App\Eloquent\Ygt\Abnormal::leftJoin('ygt_abnormal_field as af','af.id','=','ygt_abnormal.field_id')
                                        ->leftJoin('ygt_abnormal_rult_parameter as arp','arp.id','=','af.field_value')
                                        ->where(function ($query){
                                            $query->where('arp.name','=','材料平方克重');
                                        })
                                        ->where('ygt_abnormal.id','=',$abnormal['abnormal_id'])
                                        ->count();
                                    if (!$count) continue;//过滤不是材料平方克重的异常 20190916 修改 wei
                                    $intro = '';
                                    $tips = '';
                                    foreach ($chanpinMaterialArr as $product_id){
                                        if(!checkMaterial($product_id,$abnormal['abnormal_id'])) continue;//检测该材料是否在该异常设置的范围内
                                        if (!checkMaterialTl($product_id)) continue;//检测材料是否为筒料或纸,为筒料或纸时则继续
                                        $where = [];
                                        $where['company_id'] = $userInfo['company_id'];
                                        $where['order_process_course_id'] = $order_process_course_id;
                                        $where['order_process_id'] = $order_process_id;
                                        $where['order_id'] = $orderInfo['id'];
                                        $where['user_id'] = $uid;
                                        $where['material_id'] = $product_id;
                                        $where['chanpin_id'] = \App\Eloquent\Ygt\Order::where('id','=',$orderInfo['id'])->pluck('chanpin_id')->first();
                                        $field_id = \App\Eloquent\Ygt\Abnormal::where('id','=',$abnormal['abnormal_id'])->pluck('field_id')->toArray();
                                        $materialInfo = \App\Eloquent\Ygt\Product::leftJoin('ygt_seller_company','ygt_seller_company.id','=','ygt_product.seller_company_id')
                                            ->Where('ygt_product.id','=',$product_id)->select(['product_name','title'])->first();
                                        if ($field_id) {
                                            if (ruleComparison($where,$field_id[0],$abnormal)){//触发异常
                                                $baseline = ReturnRuleFormula($where,$field_id[0]);
                                                $tips .= "# ".$materialInfo['product_name']."（".$materialInfo['title']."）"." 平方克重为【".$baseline."】rnrn";
                                            }
                                        }
                                    }
                                    //生成异常消息提示
                                    if (empty($tips)) {
                                        continue;
                                    }else{
                                        $intro .= $tips;
                                    }
                                    $intro .= "rnrn当前预警设置参数";
                                    if($abnormal['gte_number'])
                                        $intro .= "rnrn 大于：".$abnormal['gte_number'];

                                    if($abnormal['lte_number'])
                                        $intro .= "rnrn 小于：".$abnormal['lte_number'];
                                    $sendSmsData1[] = [
                                        'uid'           =>  $abnormal['uid'],
                                        'title'         =>  $title,
                                        'intro'         =>  $intro,
                                        'abnormal_id'   =>  $abnormal['abnormal_id'],
                                        'created_at'    =>  time(),
                                        'updated_at'    =>  time(),
                                        'is_see'        =>  0
                                    ];
                                    if (!empty($tips)) {
                                        \App\Eloquent\Ygt\AbnormalUserMessage::insert($sendSmsData1);
                                    }
                                }
                            }
                        }
                    }
                }*/

            //工单异常 wei 20190826 备份 20190912 wei  20190917恢复
            case "IndexController@confirmComplete":
                //工单工艺id  传过来的实际是员工工单id  兼容安卓与苹果  wei 20190906
                if (isset($data['order_process_id'])){
                    $order_process_course_id = $data['order_process_id'];
                }
                if (isset($data['order_process_course_id'])){
                    $order_process_course_id = $data['order_process_course_id'];
                }
                //end
                $orderProcessCourseInfo = OrderProcessCourse::Where('id','=',$order_process_course_id)->first()->toArray();
                $order_process_id = $orderProcessCourseInfo['order_process_id'];
                //获取对应工单工序表信息
                $orderProcessInfo = OrderProcess::where('id', $order_process_id)->first()->toArray();
                //获取工单信息
                $orderInfo = Order::where('id', $orderProcessInfo['order_id'])->first()->toArray();

                //获取当前步骤所有工艺
                $curOrderProcessList = \App\Engine\OrderType::getCurrentOrderProcess($orderInfo['order_type'], $orderProcessInfo['process_type'])->toArray();
                //判断当前步骤的工艺是否都已经完成
                $noCompleteProcessNum = OrderProcess::all()->whereIn('process_type', $curOrderProcessList)->where('status', '!=', 4)->where('order_id', $orderProcessInfo['order_id'])->count();
                //当前工序完工后去判断异常
                if ($noCompleteProcessNum == 0){
                    $title = "工单号【".\App\Eloquent\Zk\Order::leftjoin('ygt_order_process as op','op.order_id','=','ygt_order.id')->where('op.id','=',$order_process_id)->pluck('ygt_order.order_title')->first()."】";
                    $where = [];
                    $where[] = ['company_id', '=', $userInfo['company_id']];
                    $where[] = ['order_process_id', '=', $order_process_id];
                    $materialArr = \App\Eloquent\Zk\OrderMaterialCourse::where($where)->pluck('material_id')->toArray();//获取该工序下所有材料
                    $rule = getWorkSheetConfig('workSheet','material',$userInfo['company_id']);
                    $abnormal_user = [];
                    if (!$rule) break;
                    foreach ($rule as $key=>$val){
                        $abnormal_user[] = restructureAbnormalUserData($val['abnormal_user']);
                    }
                    $abnormal_user = array_filter($abnormal_user);//删除数组中空元素,出现空元素原因是  后台设置异常,app没有设置该异常具体参数
                    if ($abnormal_user){
                        //遍历设置过参数的异常
                        foreach ($abnormal_user as $val){
                            if(!$val['abnormal_user'] && !$val['public']) continue;
                            if ($materialArr){//遍历材料id,并判断该材料id是否在异常设置的范围内
                                foreach ($val['public'] as $key => $abnormal) {
                                    $count = \App\Eloquent\Zk\Abnormal::where('id','=',$abnormal['abnormal_id'])->where(function ($query){
                                        $query->where('rule','like','%材料重量%')
                                            ->orwhere('rule','like','%材料使用平方数%');
                                    })->count();
                                    if ($count) continue;//过滤与材料平方克重相关异常
                                    $intro = '';
                                    $tips = '';
                                    foreach ($materialArr as $v){
                                        if(!checkMaterial($v,$abnormal['abnormal_id'])) continue;//检测该材料是否在该异常设置的范围内
                                        $where = [];
                                        $where['company_id'] = $userInfo['company_id'];
                                        $where['order_process_course_id'] = $order_process_course_id;
                                        $where['order_process_id'] = $order_process_id;
                                        $where['user_id'] = $uid;
                                        $where['material_id'] = $v;
                                        $rule = \App\Eloquent\Zk\Abnormal::where('id','=',$abnormal['abnormal_id'])->pluck('rule')->toArray();
                                        $materialInfo = \App\Eloquent\Zk\Product::leftJoin('ygt_seller_company','ygt_seller_company.id','=','ygt_product.seller_company_id')
                                            ->Where('ygt_product.id','=',$v)->select(['product_name','title'])->first();
                                        if ($rule) {
                                            if (ruleComparison($where,$rule[0],$abnormal)){//触发异常
                                                $baseline = ReturnRuleFormula($where,$rule[0]);
                                                $tips .= "# ".$materialInfo['product_name']."（".$materialInfo['title']."）"." 损耗比为【".$baseline."】rnrn";
                                            }
                                        }
                                    }
                                    //生成异常消息提示
                                    if (!empty($tips)) {$intro .= $tips; }
                                    $intro .= "rnrn当前预警设置参数";
                                    if($abnormal['gte_number'])
                                        $intro .= "rnrn 大于：".$abnormal['gte_number'];

                                    if($abnormal['lte_number'])
                                        $intro .= "rnrn 小于：".$abnormal['lte_number'];

                                    $sendSmsData[] = [
                                        'uid'           =>  $abnormal['uid'],
                                        'title'         =>  $title,
                                        'intro'         =>  $intro,
                                        'abnormal_id'   =>  $abnormal['abnormal_id'],
                                        'created_at'    =>  time(),
                                        'updated_at'    =>  time(),
                                        'is_see'        =>  0
                                    ];
                                    if (!empty($tips)) {
                                        \App\Eloquent\Zk\AbnormalUserMessage::insert($sendSmsData);
                                    }
                                }

                            }
                        }
                    }else{
                        break;
                    }
                }
                //获取未完工的工序工单的数量
                $where = [
                    ['order_id', '=', $orderProcessInfo['order_id']],
                    ['status', '!=', 4],
                ];
                $orderProcessNoCompleteNum = OrderProcess::where($where)->get()->count();
                //未完工工序工单数量为 0 时,说明整个工单都已完成
                if ($orderProcessNoCompleteNum == 0){
                    $title = "工单号【".\App\Eloquent\Zk\Order::leftjoin('ygt_order_process as op','op.order_id','=','ygt_order.id')->where('op.id','=',$order_process_id)->pluck('ygt_order.order_title')->first()."】";
                    $where = [];
                    $where['order_id'] = $orderProcessInfo['order_id'];
                    $where['company_id'] = $userInfo['company_id'];
                    $where['order_process_id'] = $order_process_id;
                    $chanpinMaterialArr = getChanpinMaterial($where);//获取产品相关所有材料
                    $rule = getWorkSheetConfig('workSheet','material',$userInfo['company_id']);
                    $abnormal_user = [];
                    if (!$rule) break;
                    foreach ($rule as $key=>$val){
                        $abnormal_user[] = restructureAbnormalUserData($val['abnormal_user']);
                    }
                    $abnormal_user = array_filter($abnormal_user);//删除数组中空元素,出现空元素原因是  后台设置异常,app没有设置该异常具体参数
                    if ($abnormal_user){
                        //遍历设置过参数的异常
                        foreach ($abnormal_user as $val){
                            if(!$val['abnormal_user'] && !$val['public']) continue;

                            if ($chanpinMaterialArr){
                                foreach ($val['public'] as $key => $abnormal){

                                    $count = \App\Eloquent\Zk\Abnormal::where('id','=',$abnormal['abnormal_id'])->where(function ($query){
                                        $query->where('rule','like','%材料重量%')
                                            ->orwhere('rule','like','%材料使用平方数%');
                                    })->count();
                                    if (!$count) continue;//过滤不是材料平方克重的异常
                                    $intro = '';
                                    $tips = '';
                                    foreach ($chanpinMaterialArr as $product_id){
                                        if(!checkMaterial($product_id,$abnormal['abnormal_id'])) continue;//检测该材料是否在该异常设置的范围内
                                        if (!checkMaterialTl($product_id)) continue;//检测材料是否为筒料或纸,为筒料或纸时则继续
                                        $where = [];
                                        $where['company_id'] = $userInfo['company_id'];
                                        $where['order_process_course_id'] = $order_process_course_id;
                                        $where['order_process_id'] = $order_process_id;
                                        $where['order_id'] = $orderInfo['id'];
                                        $where['user_id'] = $uid;
                                        $where['material_id'] = $product_id;
                                        $where['chanpin_id'] = \App\Eloquent\Zk\Order::where('id','=',$orderInfo['id'])->pluck('chanpin_id')->first();
                                        $rule = \App\Eloquent\Zk\Abnormal::where('id','=',$abnormal['abnormal_id'])->pluck('rule')->toArray();
                                        $materialInfo = \App\Eloquent\Zk\Product::leftJoin('ygt_seller_company','ygt_seller_company.id','=','ygt_product.seller_company_id')
                                            ->Where('ygt_product.id','=',$product_id)->select(['product_name','title'])->first();
                                        if ($rule) {
                                            if (ruleComparison($where,$rule[0],$abnormal)){//触发异常
                                                $baseline = ReturnRuleFormula($where,$rule[0]);
                                                $tips .= "# ".$materialInfo['product_name']."（".$materialInfo['title']."）"." 平方克重为【".$baseline."】rnrn";
                                            }
                                        }
                                    }
                                    //生成异常消息提示
                                    if (empty($tips)) {
                                        continue;
                                    }else{
                                        $intro .= $tips;
                                    }
                                    $intro .= "rnrn当前预警设置参数";
                                    if($abnormal['gte_number'])
                                        $intro .= "rnrn 大于：".$abnormal['gte_number'];

                                    if($abnormal['lte_number'])
                                        $intro .= "rnrn 小于：".$abnormal['lte_number'];
                                    $sendSmsData1[] = [
                                        'uid'           =>  $abnormal['uid'],
                                        'title'         =>  $title,
                                        'intro'         =>  $intro,
                                        'abnormal_id'   =>  $abnormal['abnormal_id'],
                                        'created_at'    =>  time(),
                                        'updated_at'    =>  time(),
                                        'is_see'        =>  0
                                    ];
                                    if (!empty($tips)) {
                                        \App\Eloquent\Zk\AbnormalUserMessage::insert($sendSmsData1);
                                    }
                                }
                            }
                        }
                    }
                }

                //材料 /  集合材料   可用库存
            /*case "IndexController@orderMaterialReceiveGradationSubmit"://领取材料
            case "IndexController@orderConfirmAssignmentV2"://下单人下单
            case "IndexController@receiveOrderProcessProduct"://领取半成品
            case "OutController@confirm"://成品出库
            case "StorehouseController@createWarehouseAdjustment"://成品调库
            case "IndexController@create"://退货申请
                $materialIdArr = [];
                if ($controller == "IndexController@orderMaterialReceiveGradationSubmit"){
                    // 领料时   材料的可用库存的变化
                    $materialIdArr[] = $data['material_id'];
                }
                if ($controller == "IndexController@orderConfirmAssignmentV2"){
                    //下单人创建工单时 材料的可用库存的变化
                    $order_id = $data['order_ids'];
                    $material_id = \App\Eloquent\Ygt\OrderMaterial::where('order_id','=',$order_id)->pluck('material_id')->toArray();
                    $materialIdArr = $material_id;
                }
                if ($controller == "IndexController@receiveOrderProcessProduct"){
                    //半成品 领取
                    $process_product_id = $data['process_product_id'];//半成品id
                }
                if ($controller == "OutController@confirm"){
                    //成品交货单
                    $warehouse_bill_id = $data['warehouse_bill_id'];
                    $orderIdArr = \App\Eloquent\Ygt\WarehouseBillRelation::where('warehouse_bill_id','=',$warehouse_bill_id)
                        ->pluck('order_id')->toArray();//获取该交货单 所有产品id
                }
                if ($controller == "StorehouseController@createWarehouseAdjustment"){
                    //成品 调库出库
                    $productArr = json_decode(htmlspecialchars_decode($data['bill_arr']),true);
                    $storehouse_id = $data['storehouse_id'];//产品仓库id
                }
                if ($controller == "IndexController@create"){
                    if (isset($data['material_list'])){
                        $material_list = json_decode(htmlspecialchars_decode($data['material_list']),true);
                        foreach ($material_list as $material){
                            $materialIdArr[] = $material['material_id'];
                        }
                    }
                }
                $abnormalRuleParameterId = \App\Eloquent\Ygt\AbnormalRultParameter::where('name','=','可用库存')
                    ->pluck('id')->first();
                $abnormalFieldNameArr = \App\Eloquent\Ygt\AbnormalField::where('field_value','=',$abnormalRuleParameterId)
                    ->where(function ($query) use ($controller){
                    if ($controller == "IndexController@orderMaterialReceiveGradationSubmit" OR $controller  == "IndexController@orderConfirmAssignmentV2"){//材料 || 集合材料
                        $query->where('ygt_abnormal_field.field_type','=','material');
                    }else if ($controller == "IndexController@receiveOrderProcessProduct"){//半成品
                        $query->where('ygt_abnormal_field.field_type','=','product_aggretage');
                    }else if ($controller == "OutController@confirm" || $controller == "StorehouseController@createWarehouseAdjustment"){//成品
                        $query->where('ygt_abnormal_field.field_type','=','product');
                    }else if ($controller == "IndexController@create"){//退品
                        $query->where('ygt_abnormal_field.field_type','=','return_product');
                    }
                })
                    ->pluck('field_name')->toArray();

                $abnormalArr = \App\Eloquent\Ygt\Abnormal::leftJoin('ygt_abnormal_user','ygt_abnormal_user.abnormal_id','=','ygt_abnormal.id')
                    ->where('company_id','=',$userInfo['company_id'])
                    ->where('ygt_abnormal.rule','like','%可用库存%')
                    ->where(function ($query) use ($controller){
                        if ($controller == "IndexController@orderMaterialReceiveGradationSubmit" OR $controller  == "IndexController@orderConfirmAssignmentV2"){//材料 || 集合材料
                            $query->where('ygt_abnormal.type','=','material');
                        }else if ($controller == "IndexController@receiveOrderProcessProduct"){//半成品
                            $query->where('ygt_abnormal_field.field_type','=','product_aggretage');
                        }else if ($controller == "OutController@confirm" || $controller == "StorehouseController@createWarehouseAdjustment"){//成品
                            $query->where('ygt_abnormal.type','=','product');
                        }else if ($controller == "IndexController@create"){//退品
                            $query->where('ygt_abnormal.type','=','return_product');
                        }
                    })
                    ->where(function ($query) use ($abnormalFieldNameArr){
                        if (!empty($abnormalFieldIdArr)){
                            foreach ($abnormalFieldIdArr as $value){
                                $query->where('ygt_abnormal.rule','like',"%$value%");
                            }
                        }
                    })
                    ->select(['ygt_abnormal.*','ygt_abnormal_user.gte_number','ygt_abnormal_user.lte_number','ygt_abnormal_user.uid','ygt_abnormal_user.abnormal_id'])->get()->toArray();
            if (empty($abnormalArr)){ break; }
                foreach ($abnormalArr as $value) {
                    $tips = '';
                    $intro = '';
                    $type = config('abnormal');
                    $top  = \App\Eloquent\Ygt\AbnormalType::where('id','=',$value['abnormal_type_id'])->pluck('sort')->first();
                    $title = "可用库存-".$type[$top][$value['type']];
                    //材料 领料 //  下单人下单  //  退品 退货申请
                    if ($controller == "IndexController@orderMaterialReceiveGradationSubmit" OR $controller == "IndexController@orderConfirmAssignmentV2" OR $controller == "IndexController@create") {
                        foreach ($materialIdArr as $val) {
                            if (strstr($val, 'A')) {//判断复合材料
                                $val = ltrim($val, 'A');
                                $category_id = \App\Eloquent\Ygt\AssemblageMaterial::where('id', '=', $val)->pluck('category_id')->first();
                                $materialInfo = \App\Eloquent\Ygt\AssemblageMaterial::where('id', '=', $val)->first()->toArray();
                                $where['material_id'] = $val;//集合材料id
                                $where['res_type'] = 5;//可用库存表中的res_type字段  值为5代表集合材料
                            } else {
                                $category_id = \App\Eloquent\Ygt\Product::where('id', '=', $val)->pluck('category_id')->first();
                                $materialInfo = \App\Eloquent\Ygt\Product::leftJoin('ygt_seller_company', 'ygt_seller_company.id', '=', 'ygt_product.seller_company_id')
                                    ->Where('ygt_product.id', '=', $val)
                                    ->select(['product_name', 'title'])->first()->toArray();
                                $where['material_id'] = $val;//材料id
                                $where['res_type'] = 1;//材料
                            }
                            if (!checkMaterial($category_id, $value['id'])) {
                                continue;
                            }//检测材料是否在异常设置的范围中

                            if ($value['field_id']) {
                                if (ruleComparison($where, $value['rule'], $value)) {//触发异常
                                    $baseline = ReturnRuleFormula($where, $value['rule']);
                                    $tips .= "# " . $materialInfo['product_name'] . "（";
                                    if (isset($materialInfo['title'])) {
                                        $tips .= $materialInfo['title'];
                                    } else {
                                        $tips .= "集合材料";
                                    }
                                    $tips .= "）" . " 可用库存为【" . $baseline . "】rnrn";
                                }
                            }
                        }
                    }
                    //半成品
                    else if ($controller == "IndexController@receiveOrderProcessProduct") {
                        //检测范围
                        if ($value['relation_id'] != "product_aggretage_all") {
                            if (!in_array($process_product_id, explode(',', $value['relation_id']))) {
                                continue;
                            }
                        }
                        $stockArr = \App\Eloquent\Ygt\Storehouse::where('company_id', '=', $userInfo['company_id'])->pluck('id')->toArray();//获取本公司所有仓库
                        $where = [];
                        $where['material_id'] = $process_product_id;//半成品id
                        $where['res_type'] = 2;//2代表半成品
                        $where['stockArr'] = $stockArr;
                        $materialInfo = \App\Eloquent\Ygt\ProcessProduct::where('id', '=', $process_product_id)->first()->toArray();
                        if (ruleComparison($where, $value['rule'], $value)) {//触发异常
                            $baseline = ReturnRuleFormula($where, $value['rule']);
                            $tips .= "# " . $materialInfo['title'] . "（" . $materialInfo['product_no'] . "）" . " 可用库存为【" . $baseline . "】rnrn";
                        }
                    }
                    //成品
                    else if ($controller == "OutController@confirm" || $controller == "StorehouseController@createWarehouseAdjustment") {
                        if ($controller == "OutController@confirm"){//成品交货单
                            if (!$orderIdArr) continue;
                            foreach ($orderIdArr as $order_id) {
                                if ($value['relation_id'] != "product_all") {
                                    $orderTypeCategoryId = \App\Eloquent\Ygt\OrdertypeCategory::leftJoin('ygt_order_type', 'ygt_order_type.category_id', '=', 'ygt_ordertype_category.id')
                                        ->leftJoin('ygt_warehouse', 'ygt_warehouse.order_type_title', '=', 'ygt_order_type.title')
                                        ->where('ygt_warehouse.order_id', '=', $order_id)
                                        ->where('ygt_order_type.company_id','=',$userInfo['company_id'])
                                        ->pluck('ygt_ordertype_category.id')->first();
                                    if (!in_array($orderTypeCategoryId,explode(',',$value['relation_id']))){
                                        continue;//检测该交货单中成品是否在 异常设置的范围中
                                    }
                                }
                                $stockArr = \App\Eloquent\Ygt\Storehouse::where('company_id', '=', $userInfo['company_id'])->pluck('id')->toArray();//获取本公司所有仓库
                                $productInfo = \App\Eloquent\Ygt\Warehouse::where('order_id','=',$order_id)->first();//获取成品详情
                                $where = [];
                                $where['material_id'] = $productInfo['id'];
                                $where['res_type'] = 3;//成品
                                $where['stockArr'] = $stockArr;
                                if (ruleComparison($where, $value['rule'], $value)) {//触发异常
                                    $baseline = ReturnRuleFormula($where, $value['rule']);
                                    $tips .= "# " . $productInfo['product_name'] . "（" . $productInfo['product_no'] . "） 可用库存为【" . $baseline . "】rnrn";
                                }
                            }
                        }else if ($controller == "StorehouseController@createWarehouseAdjustment"){//成品 调库
                            if (!$productArr) continue;

                            foreach ($productArr as $product){
                                if ($value['relation_id'] != "product_all") {
                                    $orderTypeCategoryId = \App\Eloquent\Ygt\OrdertypeCategory::leftJoin('ygt_order_type', 'ygt_order_type.category_id', '=', 'ygt_ordertype_category.id')
                                        ->leftJoin('ygt_warehouse', 'ygt_warehouse.order_type_title', '=', 'ygt_order_type.title')
                                        ->where('ygt_warehouse.id', '=', $product['warehouse_id'])
                                        ->where('ygt_order_type.company_id','=',$userInfo['company_id'])
                                        ->pluck('ygt_ordertype_category.id')->first();
                                    if (!in_array($orderTypeCategoryId,explode(',',$value['relation_id']))){
                                        continue;//检测该交货单中成品是否在 异常设置的范围中
                                    }
                                }
                                $stockArr = \App\Eloquent\Ygt\Storehouse::where('company_id', '=', $userInfo['company_id'])->pluck('id')->toArray();//获取本公司所有仓库
                                $productInfo = \App\Eloquent\Ygt\Warehouse::where('id','=',$product['warehouse_id'])->first()->toArray();//获取成品详情
                                $where = [];
                                $where['material_id'] = $productInfo['id'];
                                $where['res_type'] = 3;//成品
                                $where['stockArr'] = $stockArr;
                                $where['storehouse_id'] = $storehouse_id;
                                if (ruleComparison($where, $value['rule'], $value)) {//触发异常
                                    $baseline = ReturnRuleFormula($where, $value['rule']);
                                    $tips .= "# ".$productInfo['product_name']."（".$productInfo['product_no']. "）可用库存为【".$baseline."】rnrn";
                                }
                            }
                        }

                    }
                    //生成异常消息提示
                    if (empty($tips)) {
                        continue;
                    } else {
                        $intro .= $tips;
                    }
                    $intro .= "rnrn当前预警设置参数";
                    if ($value['gte_number'])
                        $intro .= "rnrn 大于：" . $value['gte_number'];

                    if ($value['lte_number'])
                        $intro .= "rnrn 小于：" . $value['lte_number'];
                    $sendSmsData1[] = [
                        'uid' => $value['uid'],
                        'title' => $title,
                        'intro' => $intro,
                        'abnormal_id' => $value['abnormal_id'],
                        'created_at' => time(),
                        'updated_at' => time(),
                        'is_see' => 0
                    ];
                    if (!empty($tips)) {
                        \App\Eloquent\Ygt\AbnormalUserMessage::insert($sendSmsData1);
                    }
                }*/

        }

        return $response;



    }


}