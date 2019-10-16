<?php
/**
 * Created by PhpStorm.
 * User: huangjiangnan
 * Date: 2019/8/9
 * Time: 10:25
 */
namespace App\Http\Admin\Abnormal\Controllers;


use App\Eloquent\Ygt\AbnormalField;
use App\Eloquent\Ygt\AbnormalRultParameter;
use App\Eloquent\Ygt\Category;
use App\Eloquent\Ygt\ProcessFieldCompany;
use Framework\BaseClass\Http\Admin\Controller;
use App\Http\Admin\Abnormal\Models\Models;
use App\Eloquent\Ygt\Abnormal;
use App\Api\Service\Storehouse\NodeAssignment\StorehouseManager;
use Mpdf\Tag\P;

class AbnormalController extends Controller
{

    private $type = [];

    public function __construct()
    {
        $this->type = config('abnormal');
    }

    //删除异常
    public function delAbnormal(){
        if(!isAjax()) return $this->ajaxFail('请勿非法请求');
        $id = request('id');
        if(!$id) return $this->ajaxFail('丢失重要参数');

        if(!Models::delAbnormal(['id'=>$id])) return $this->ajaxFail('操作失败');
        return $this->jsonReturn('ok');
    }

    public function index(){



        return $this->view('index');
    }

    //添加异常
    public function addAbnormal(){
        $adminUser = request()->user('admin')->toArray();
        $companyId = $adminUser['company_id'];
        $type = $this->type;

        if(isAjax()){

            $title = request('title');
            $sort = request('sort');
            $type = request('rule_type');
            if(!$title)return $this->ajaxFail('请填写异常名称');
            if(!$sort)return $this->ajaxFail('请选择异常类型');
            $materialId = '';
            //修改工单异常设置 wei 20190827
            if ($sort == '1'){//订单异常
                if(!$type)return $this->ajaxFail('请选择异常规则');
            }
            else if ($sort == '2'){//工单异常

                if ($type == "material"){
                    $materialId = request('material_id',array('all'));
                    $cat_id = request('cat_id',array('all'));//材料一级分类
                    $cat2_id = request('cat2_id',array('all'));//材料二级分类
                    if (!$materialId[0] || !$cat_id[0] || !$cat2_id[0]) {
                        return $this->ajaxFail('请选择材料');
                    }
                    //判断任意一级全选 与 多选
                    if (in_array('all',$cat_id)){
                        $materialId = 'cat_id_all';
                    }else if (in_array('all',$cat2_id)){
                        $materialId = 'cat2_id,'.implode(',',$cat_id);
                    }else if (in_array('all',$materialId)){
                        $materialId = 'material_id,'.implode(',',$cat2_id);
                    }else{
                        $materialId = implode(',',$materialId);
                    }
                }else if($type == "return_product"){
                    $materialId = request('return_material_id',array('all'));
                    $cat_id = request('return_cat_id',array('all'));//材料一级分类
                    $cat2_id = request('return_cat2_id',array('all'));//材料二级分类
                    if (!$materialId[0] || !$cat_id[0] || !$cat2_id[0]) {
                        return $this->ajaxFail('请选择材料');
                    }
                    //判断任意一级全选 与 多选
                    if (in_array('all',$cat_id)){
                        $materialId = 'cat_id_all';
                    }else if (in_array('all',$cat2_id)){
                        $materialId = 'cat2_id,'.implode(',',$cat_id);
                    }else if (in_array('all',$materialId)){
                        $materialId = 'material_id,'.implode(',',$cat2_id);
                    }else{
                        $materialId = implode(',',$materialId);
                    }
                }else if ($type){
                    $materialId = request("$type");
                    if (!$materialId) return $this->ajaxFail('请选择范围');
                    if (in_array("$type"."_all",$materialId)) {//判断是否全选
                        $materialId = $type."_all";
                    }else{
                        $materialId = implode(',',$materialId);
                    }
                }
            }

            /*逻辑修改, 同个类型可添加多个异常 wei  20190923
             * if  ($sort == '1'){
                $where = [
                    'abnormal_type_id'  =>  $sort,
                    'type'              =>  $type,
                    'company_id'        =>  $companyId
                ];

                $result = Models::getAbnormalList($where)->toArray();
                if($result)return $this->ajaxFail('当前异常已配置【'.$result[0]['title'].'】');
            }*/


            $data['rule']          =   "";
            $data['title']         =   $title;
            $data['created_at']    =   time();
            $data['updated_at']    =   time();
            $data['type']          =   $type;
            $data['relation_id']   =   $materialId;
            $data['company_id']    =   $companyId;
            $data['abnormal_type_id'] =    $sort;

            if(!Models::insAbnormal($data)) return $this->ajaxFail('配置添加失败，请重试');


            return $this->jsonReturn('ok');

        }else{
            $abnormalType = Models::getAbnormalType();
            $where = [];
            $where['company_id'] = $companyId;
            $materialList = \App\Eloquent\Ygt\Product::where($where)->select(['id','product_name'])->get()->toArray();
            $where = [];
            $where['company_id'] = $companyId;
            $where['pid'] = 2;
            $materialList1 = \App\Eloquent\Ygt\Category::where($where)->select(['id','cat_name'])->get()->toArray();//材料分类
            $processProduct = \App\Eloquent\Ygt\ProcessProduct::where('company_id','=',$companyId)->get(['id','title'])->toArray();//半成品
            $where = [];
            $where['company_id'] = $companyId;
            $chanpinCategory = \App\Eloquent\Ygt\OrdertypeCategory::getData($where, 'id,cat_name')->toArray();//成品



            return $this->view('addAbnormal',compact('abnormalType','type','materialList','materialList1','processProduct','chanpinCategory'));
        }



    }

    //修改异常(编辑)
    public function saveAbnormal(){

        if(!isAjax()) return $this->ajaxFail('请勿非法请求');

        $param = $this->requestJson();

        $title  = $param['title'];
        $id     = $param['id'];

        if(!$title) return $this->ajaxFail('异常设置标题不能未空');
        if(!$id) return $this->ajaxFail('丢失重要参数');

        if(!Models::updateAbnormal(['id'=>$id],['title'=>$title])){
            return $this->ajaxFail('修改失败，请刷新重试');
        }

        return $this->jsonReturn('ok');

    }

    //设置异常部门
    public function setAbnormalDepartment(){

        $id = request('id',0);

        if(isAjax()){
            $department_id = request('actions');
            if($department_id) $department_id = ','.join(',',$department_id).',';
            $save['department_id'] = $department_id;
            $where['id'] = $id;
            if(!Models::updateAbnormal($where,$save)){
                return $this->ajaxFail('修改失败，请刷新重试');
            }

            return $this->jsonReturn('ok');
        }


        $department_id = request('department_id',0);
        $adminUser = request()->user('admin')->toArray();

        $DdepartmentList = Models::getDdepartmentList(['company_id'=>$adminUser['company_id']]);
        $department_id = explode(',',$department_id);

        return $this->view('setAbnormalDepartment',compact('DdepartmentList','id','department_id'));
    }

    //设置异常计算公式
    public function setAbnormalFormula(){


        $id = request('id',0);
        $formula = request('formula',0);

        if(isAjax()){
            if(!$formula) return $this->ajaxFail('请添加公式');

            $where['id'] = $id;
            $save['rule'] = $formula;
            if(!Models::updateAbnormal($where,$save)) return $this->ajaxFail('操作失败');
            return $this->jsonReturn('ok');

        }

        $rule = request('rule',0);
        $type = request('type',0);
        $sort = request('sort',0);
        $rule = $rule?$rule:'-';

        $field_id = \App\Eloquent\Ygt\Abnormal::where('id','=',$id)->pluck('field_id')->first();
        if ($field_id){
            $field_id = explode(',',$field_id);
        }
        $list = \App\Eloquent\Ygt\AbnormalField::whereIn('id',$field_id)->get()->toArray();
        return $this->view('setAbnormalFormula',compact('rule','id','list'));
    }

    //获取异常列表
    public function getAbnormalList(){

        $adminUser = request()->user('admin')->toArray();
        $companyId = $adminUser['company_id'];

        $where = ['company_id'=>$companyId];
        $list = Models::getAbnormalList($where)->toArray();

        foreach ($list as $k=>&$v){
            $v['abnormal_related'] = '-';
            if ($v['type'] == 'material' || $v['type'] == 'return_product'){
                if ($v['relation_id']){
                    if (in_array('cat_id_all',explode(',',$v['relation_id']))) {
                        $v['abnormal_related'] = '全部材料';
                    }else if (in_array('cat2_id',explode(',',$v['relation_id']))) {
                        $cat2_id = explode(',',$v['relation_id']);
                        unset($cat2_id[0]);
                        $cat_name = \App\Eloquent\Ygt\Category::whereIn('id',$cat2_id)->pluck('cat_name')->toArray();
                        $v['abnormal_related'] = implode('-',$cat_name).'(一级分类全部)';
                    }else if (in_array('material_id',explode(',',$v['relation_id']))) {
                        $material_id = explode(',',$v['relation_id']);
                        unset($material_id[0]);
                        $cat1_name = \App\Eloquent\Ygt\Category::whereIn('id',$material_id)->pluck('cat_name')->toArray();
                        $v['abnormal_related'] = implode('-',$cat1_name).'(二级分类全部)';
                    }else{
                        $materialArr = explode(',',$v['relation_id']);
                        $v['abnormal_related'] = '';
                        $productNames = [];
                        foreach ($materialArr as $val){
                            $productNames[] = \App\Eloquent\Ygt\Product::where('id','=',$val)->pluck('product_name')->first();
                            $v['abnormal_related'] = implode('-',$productNames);
                        }
                    }
                }
            }else if ($v['type'] == "product"){
                if ($v['relation_id'] == "product_all"){
                    $v['abnormal_related'] = "全部成品";
                }else{
                    $productArr = explode(',',$v['relation_id']);
                    $titleArr = \App\Eloquent\Ygt\Ordertype::whereIn('id',$productArr)->pluck('title')->toArray();
                    $v['abnormal_related'] = implode('-',$titleArr);
                }
            }else if ($v['type'] == 'product_aggretage') {
                if ($v['relation_id'] == "product_aggretage_all"){
                    $v['abnormal_related'] = "全部半成品";
                }
            }
//            p($v);
//            p($this->type[$v['abnormal_type'][0]['sort']]);
            $v['sort'] = $v['abnormal_type'][0]['sort'];
            $v['abnormal_type_title'] = $v['abnormal_type'][0]['title'].($v['type']?'-'.$this->type[$v['sort']][$v['type']]:"");
            $departmentId = rtrim(ltrim($v['department_id'],','),',');
            $v['department_id'] = $departmentId?$departmentId:0;
            if($v['rule']){
                $v['rule'] = join('',explode('_',$v['rule']));
            }else{
                $v['rule'] = 0;
            }
            unset($v['abnormal_type']);
        }

        echo json_encode($list);
        die;
    }

    //字段分配
    public function fieldEdit(){
        $adminUser = request()->user('admin')->toArray();
        $companyId = $adminUser['company_id'];

        $id = request('id');

        $abnormal = new Abnormal();

        if (request()->isMethod('post')){
            $field_id = request('field_id');
            if (!$field_id){
                return $this->ajaxFail('请选择字段');
            }
            $rule = $abnormal->where('id','=',$id)->pluck('rule')->first();
            $fieldName = explode('_',$rule);
            $fieldNewName = \App\Eloquent\Ygt\AbnormalField::whereIn('id',$field_id)->pluck('field_name')->toArray();
            $newName = [];
            foreach ($fieldName as $name){
                $array = ['(',')','+','-','*','/','','.'];
                if (in_array($name,$array)) continue;
                $newName[] = $name;
            }
            if (!empty($newName)){
                $n = 0;
                foreach ($newName as $value){
                    if (in_array($value,$fieldNewName)){
                        $n++;
                    }
                }
                if ($n != count($newName)){
                    return $this->ajaxFail('保存失败,您有正在使用的字段,不可修改');
                }
            }

            $field_id = ','.implode(',',$field_id).',';
            $bool = $abnormal->where('id','=',$id)->update(['field_id'=>$field_id]);
            if ($bool){
                return $this->jsonReturn('ok');
            }else{
                return $this->ajaxFail();
            }
        }else{
            $abnormalField = new AbnormalField();
            $info = $abnormal->where('id','=',$id)->select(['title','field_id','type'])->first()->toArray();

            $fields = $abnormalField->where('company_id','=',$companyId)->where('field_type','=',$info['type'])->get()->toArray();
        }
        return $this->view('field-edit',compact('fields','id','info'));
    }

    //获取材料二级分类 wei 20190827
    public function getMaterialList(){
        $adminUser = request()->user('admin')->toArray();
        $companyId = $adminUser['company_id'];
        $type               = request('type',0);
        $value              = request('value',0);
        $where = [];
        $where['company_id'] = $companyId;
        switch ($type)
        {
            case 1:
                $where['pid'] = 2;
                $data = \App\Eloquent\Ygt\Category::where($where)->get()->toArray();
                break;
            case 2:
                if($value == 'all') { $data = [['all','全选']];break;}
                if($value == 0) { $data = [[0,'请选择']];break;}
                $pidArr     = explode(',',$value);
                $data       = \App\Eloquent\Ygt\Category::whereIn('pid',$pidArr)->select(['id','cat_name'])->get();
                $data       = $data->map(function($item){
                    return [
                        $item->id,
                        $item->cat_name,
                    ];
                })->toArray();
                array_unshift($data,['all','全选']);
                break;
            case 3:
                if($value == 'all') { $data = [['all','全选']];break;}
                if($value == 0) { $data = [[0,'请选择']];break;}
                $categoryIdArr = explode(',',$value);
                $data       = \App\Eloquent\Ygt\Product::whereIn('category_id',$categoryIdArr)->get();
                $data       = $data->map(function($item){
                    return [
                        $item->id,
                        $item->product_name,
                    ];
                })->toArray();
                array_unshift($data,['all','全选']);
                break;
            default:
                $data       =[];
        }
        $result             = json_encode($data);
        return $result;
    }

    //获取异常字段列表  wei  20190905
    public function getMaterialTypeList(){
        $adminUser         = request()->user('admin')->toArray();
        $companyId         = $adminUser['company_id'];
        $type = $this->type;
        $abnormalField = new AbnormalField();

        if (request()->isMethod('post')){
            $where = [];
            $where['company_id'] = $companyId;
            $data = $abnormalField->where($where)->get()->toArray();
            $abnormalRultParameter = new AbnormalRultParameter();
            foreach ($data as $k=>$v){
                $name = $abnormalRultParameter->where('id','=',$v['field_value'])->pluck('name')->toArray();
                if ($name){
                    $data[$k]['field_value'] = $name[0];
                }
            }
            echo json_encode($data);
        }else{
            $where = [];
            $where['company_id'] = $companyId;
            $data = $abnormalField->where($where)->get()->toArray();

            $fieldType = [];
            foreach ($type as $value){
                foreach ($value as $key => $val){
                    $fieldType[] = "{'$key':'$val'}";
                }
            }
            $fieldType = '['.implode(',',$fieldType).']';

            $source_type = "[{'1':'系统字段'},{'0':'计算字段'}]";
            return $this->view('abnormalField',compact('data','fieldType','source_type'));
        }
    }

    //添加异常字段 wei
    public function addAbnormalField(){
        $adminUser         = request()->user('admin')->toArray();
        $companyId         = $adminUser['company_id'];
        $type = $this->type;

        if(isAjax()){

            $fieldName = request('field_name');//字段名称
            $fieldType = request('field_type');//字段类型
            $sourceType = request('source_type');//字段来源
            if (!$fieldName){
                return $this->ajaxFail('请输入字段名称');
            }
            if (!$fieldType){
                return $this->ajaxFail('请选择字段类型');
            }
            if (!$sourceType && $sourceType != 0){
                return $this->ajaxFail('请选择字段来源');
            }

            if ($sourceType == 1){//字段值
                $fieldValue = request('system_value');//系统字段
            }else{
                $fieldValue = request('calculate_value_'.$fieldType);//计算字段
            }
            if (!$fieldValue){
                return $this->ajaxFail('请选择字段值');
            }
            $where = [];
            $where['company_id'] = $companyId;
            $where['field_name'] = $fieldName;
            $where['field_type'] = $fieldType;
            $abnormalField = new AbnormalField();
            if($abnormalField->where($where)->first()){
                return $this->ajaxFail('该字段名称已存在');
            };
            $time = time();
            $data = ['field_name'=>$fieldName,'field_type'=>$fieldType,'source_type'=>$sourceType,'field_value'=>$fieldValue,'company_id'=>$companyId,'created_at'=>$time,'updated_at'=>$time];
            $id = $abnormalField->insertGetId($data);
            if (!$id){
                return $this->ajaxFail();
            }
            return $this->jsonReturn('ok');

        }else{
            $abnormalRultParameter = new AbnormalRultParameter();
            foreach ($type as $value){
                foreach ($value as $key=>$val){
                    $system[$key] = $abnormalRultParameter->where('sort','=','')->where('type','like',"%$key%")->get()->toArray();
                    $calculate[$key] = $abnormalRultParameter->where('sort','!=','')->where('type','like',"%$key%")->get()->toArray();
                }
            }
            return $this->view('addAbnormalField',compact('type','system','calculate'));
        }


    }

    //绑定材料 wei 20190905
    public function bindMaterial(){
        $adminUser         = request()->user('admin')->toArray();
        $companyId         = $adminUser['company_id'];

        $all = request()->all();
        $id = $all['id'];

        $abnormalField = new AbnormalField();
        $category_id = null;
        if (isset($all['checkCategoryArr'])){
            $category_id = implode(',',$all['checkCategoryArr']);
        }
        $bool = $abnormalField->where('id','=',$id)->update(['category_id'=>$category_id]);
        if ($bool){
            return $this->ajaxSuccess('成功');
        }else{
            return $this->ajaxFail('失败');
        }

    }

    //获取材料列表 wei 20190905
    public function categoryList(){
        $adminUser         = request()->user('admin')->toArray();
        $companyId         = $adminUser['company_id'];

        $id = request('id');

        if(request()->isMethod('post')){
            //获取材料分类列表
            $where = [
                'company_id' => $companyId,
            ];
            $category = new Category();
            $categoryList = $category->where($where)->orderBy('sort_id','asc')->get();

            $categoryList = $categoryList->map(function ($item) {
                if($item['pid'] == 2){
                    $item['pid'] = 0;//第一级材料分类调整为无父类
                }
                return $item;
            });
            return $categoryList->toJson();
        }

        //获取用户已经选中的材料分类
        $checkCategoryIds = '';
        $abnormalField = new AbnormalField();
        $tmpPurchaseManageRow = $abnormalField->where(['id'=>$id,'company_id'=>$companyId])->first();
        if($tmpPurchaseManageRow){
            $checkCategoryIds = $tmpPurchaseManageRow['category_id'];
        }


        return $this->view('category-list', compact('id','checkCategoryIds'));
    }

    //更新字段信息 wei
    public function saveAbnormalField(){
        $adminUser         = request()->user('admin')->toArray();
        $companyId         = $adminUser['company_id'];

        $all               = $this->requestJson();
        $id = $all['id'];
        $field_name = $all['field_name'];
        $field_type = $all['field_type'];
        $source_type = $all['source_type'];
        $abnormalField = new AbnormalField();
        $bool = $abnormalField->where('id','=',$id)->first();
        if (!$bool){
            return $this->ajaxFail('更新失败,请稍后重试');
        }

        $bool = $abnormalField->where('id','!=',$id)->where('company_id','=',$companyId)->where('field_name','=',$field_name)->where('field_type','=',$field_type)->first();
        if ($bool){
            return $this->ajaxFail('该字段名已存在,请更换后重试');
        }

        $data['field_name'] = $field_name;
        $data['field_type'] = $field_type;
        $data['source_type'] = $source_type;
        if ($field_type != 'material'){
            $data['category_id'] = null;
        }
        $data['updated_at'] = time();
        $bool = $abnormalField->where('id','=',$id)->update($data);
        if ($bool){
            return $this->ajaxSuccess();
        }else{
            return $this->ajaxFail('请稍后重试');
        }
    }

    //删除字段 wei
    public function delAbnormalField(){
        $all               = request()->all();
        $id = $all['id'];
        $title = \App\Eloquent\Ygt\Abnormal::where(function ($query) use ($id){
            $query->where('field_id','like',"%,$id,%");
        })->pluck('title')->toArray();
        if ($title){
            $message = implode('、',$title);

            return $this->ajaxFail("不可删除！该字段正在被（".$message."）使用，请先取消绑定！");
        }

        $abnormalField = new AbnormalField();
        $bool = $abnormalField->where('id','=',$id)->delete();
        if ($bool){
            return $this->ajaxSuccess();
        }else{
            return $this->ajaxFail();
        }

    }

    //根据材料分类id,获取其所有子类  wei
    public function getMaterialChildren($where){
        $adminUser         = request()->user('admin')->toArray();
        $companyId         = $adminUser['company_id'];
        $cid = explode(',',$where['cid']);

        $materialArr = [];
        foreach ($cid as $val){
            $materialArr[] = getChildren($val,$companyId);
        }
        $result = [];
        if ($materialArr){
            foreach ($materialArr as $key=>$val){
                if (is_array($val)){
                    foreach ($val as $value){
                        if (is_array($value)){
                            $result = array_merge($result,$value);
                        }else{
                            $result[] = $value;
                        }
                    }
                }else{
                    $result[] = $val;
                }
            }
        }
        return $result;
    }


}