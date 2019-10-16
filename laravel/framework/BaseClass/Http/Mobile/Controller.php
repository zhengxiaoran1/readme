<?php
/**
 * Created by PhpStorm.
 * Author: Sojo
 * Date: 2017/2/17
 * Time: 16:30
 */
namespace Framework\BaseClass\Http\Mobile;

use Framework\BaseClass\Http\Controller as HttpController;
use App\Eloquent\Admin\Menu;
use App\Eloquent\Admin\RoleUser;
use App\Eloquent\Admin\RoleMenu;
use App\Eloquent\Ygt\DepartmentUser;
use App\Engine\Func;
use App\Eloquent\Ygt\User;
use App\Eloquent\Ygt\Customer;
class Controller extends HttpController
{
    /** @var  array 菜单集合 */
    private $menuList;

    /** @var string ajax请求成功，默认返回信息 */
    private $okMessage = '操作成功';

    /** @var string ajax请求失败，默认返回信息 */
    private $errorMessage = '操作失败';

    protected $company = true;

    protected $rules = [];


    /**
     * 获取后台管理系统菜单列表
     * @author Sojo
     */
    public function getMenuList()
    {
        $menuList = Menu::where([
            'project' => 'default',
            'display' => 1
        ])->get();
        $menuMap = [];
        foreach ($menuList as $menu) {
            $menuMap[$menu->id] = $menu;
        }

        $user = request()->user('mobile');
        $userRole = RoleUser::where('admin_user_id', $user->id)->first();
        $roleMenuIds = RoleMenu::where('admin_role_id', $userRole->admin_role_id)->get()->pluck('admin_menu_id')->toArray();

        $userMenuIds = [];
        foreach ($roleMenuIds as $menuId) {
            $sideMenuId = $menuMap[$menuId]->pid;
            $topMenuId = $menuMap[$sideMenuId]->pid;

            $userMenuIds[] = $menuId;
            if (!in_array($sideMenuId, $userMenuIds)) $userMenuIds[] = $sideMenuId;
            if (!in_array($topMenuId, $userMenuIds)) $userMenuIds[] = $topMenuId;
        }

        foreach ($menuMap as $key => $menu) {
            $menuMap[$key] = $menu->toArray();
            if (!in_array($key, $userMenuIds)) unset($menuMap[$key]);
        }

        $this->menuList = $menuMap;
    }

    /**
     * 获取后台管理系统顶部菜单列表
     * @author Sojo
     * @return array
     */
    protected function getTopMenuList()
    {
        $this->getMenuList();

        $topMenuList = [];
        foreach ($this->menuList as $menu) {
            if ($menu['pid'] == 0) {
                $topMenuList[] = $menu;
            }
        }
        array_multisort(array_column($topMenuList, 'sort'), SORT_ASC, $topMenuList);

        // 确认顶部菜单是否有子菜单
        $parentsIds = array_column($this->menuList, 'pid');
        foreach ($topMenuList as $key => $topMenu) {
            $topMenuList[$key]['isChildren'] = in_array($topMenu['id'], $parentsIds) ? true : false;
        }

        return $topMenuList;
    }


    /**
     * 获取后台管理系统侧边菜单列表
     * @author Sojo
     * @param int $topMenuId 顶部菜单ID
     * @return string
     */
    protected function getSideMenuList($topMenuId)
    {
        $this->getMenuList();

        // 获取一级菜单
        $tempSideMenuList = [];
        foreach ($this->menuList as $menu) {
            if ($menu['pid'] == $topMenuId) {
                $tempSideMenuList[] = $menu;
            }
        }
        array_multisort(array_column($tempSideMenuList, 'sort'), SORT_ASC, $tempSideMenuList);

        // 获取二级菜单
        $sideMenuList = [];
        foreach ($tempSideMenuList as $sideMenu) {
            $children = [];
            foreach ($this->menuList as $menu) {
                if ($menu['pid'] == $sideMenu['id']) {
                    $children[] = $menu;
                }
            }
            array_multisort(array_column($children, 'sort'), SORT_ASC, $children);

            $tempChildren = [];
            foreach ($children as $child) {
                $tempChildren[] = [
                    'id'     => $child['english_name'],
                    'name'   => $child['name'],
                    'target' => $child['target'],
                    'url'    => $child['url'],
                    'fresh'  => $child['fresh'] ? true : false
                ];
            }

            $sideMenuList[] = [
                'name'     => $sideMenu['name'],
                'children' => $tempChildren
            ];
        }

        return json_encode($sideMenuList);
    }

    /**
     * b-jui ajax调用成功返回
     * @author Sojo
     * @param string $message 自定义成功信息
     * @param array $extend 传递给前端的参数
     *      dataGridId：string类型，DataGrid元素的ID值
     *      filterFlag：boolean类型，true = 保留筛选、排序、分页数据，false = 不保留
     * @return \Illuminate\Http\JsonResponse
     */
    protected function ajaxSuccess($message = null, $extend = [])
    {
        $json = [
            'statusCode' => 200,
            'message'    => $message ?: $this->okMessage
        ];
        if (!empty($extend)) $json['extend'] = $extend;

        return response()->json($json);
    }

    /**
     * b-jui ajax调用失败返回
     * @author Sojo
     * @param string $message 自定义错误信息
     * @param array $extend 传递给前端的参数
     *      dataGridId：string类型，DataGrid元素的ID值
     *      filterFlag：boolean类型，true = 保留筛选、排序、分页数据，false = 不保留
     * @return \Illuminate\Http\JsonResponse
     */
    protected function ajaxFail($message = null, $extend = [])
    {
        $json = [
            'statusCode' => 300,
            'message'    => $message ?: $this->errorMessage
        ];
        if (!empty($extend)) $json['extend'] = $extend;

        return response()->json($json);
    }

    /**
     * b-jui ajax返回，通过判断条件 true|false，返回成功|失败信息
     * @author Sojo
     * @param bool $condition 判断条件，根据结果为true|false，返回成功|失败信息
     * @param string|null $okMessage 成功时的自定义信息
     * @param string|null $errorMessage 失败时的自定义信息
     * @param array $okExtend 成功时传递给前端的参数
     *      dataGridId：string类型，DataGrid元素的ID值
     *      filterFlag：boolean类型，true = 保留筛选、排序、分页数据，false = 不保留
     * @param array $errorExtend 失败时传递给前端的参数
     *      dataGridId：string类型，DataGrid元素的ID值
     *      filterFlag：boolean类型，true = 保留筛选、排序、分页数据，false = 不保留
     * @return \Illuminate\Http\JsonResponse
     */
    protected function jsonReturn($condition, $okMessage = null, $errorMessage = null, $okExtend = [], $errorExtend = [])
    {
        $okMessage = $okMessage ?: $this->okMessage;
        $errorMessage = $errorMessage ?: $this->errorMessage;
        return $condition ? $this->ajaxSuccess($okMessage, $okExtend) : $this->ajaxFail($errorMessage, $errorExtend);
    }

    /**
     * b-jui 获取ajax保存数据时的请求参数
     * @author Sojo
     * @param bool $assoc 返回值类型，true：数组，false：对象
     * @return object
     */
    protected function jsonRequest($assoc = false)
    {
        $params = json_decode(request('json'), $assoc);

        if (is_array($params) && count($params) == 1) $params = $params[0];

        return $params;
    }
    protected function requestJson($assoc = true)
    {
        $json                   = request('json');
        $json                   = htmlspecialchars_decode($json);
        $params = json_decode($json, $assoc);
        if (is_array($params) && count($params) == 1) $params = $params[0];
        return $params;
    }

    /**
     * 获取分页请求参数
     * @author Sojo
     * @param string|array $keyword
     * @return mixed
     */
    protected function getPagingRequestParameters($keyword = [])
    {
        $keyword = is_array($keyword) ? $keyword : func_get_args();
        $request = request();

        $params['page'] = $request->get('pageCurrent', 0);
        $params['page_size'] = $request->get('pageSize', 0);
        $params['filter'] = [
            'keyword' => [],
            'operator' => []
        ];

        foreach ($keyword as $name) {
            $param = $request->get($name);
            if (!is_null($param)) {
                $params['filter']['keyword'][$name] = $param;
                $params['filter']['operator'][$name] = request($name . '_operator');
            }
        }

        return $params;
    }

    /**
     * 返回分页数据
     * @author Sojo
     * @param array $data 分页数据
     * @param int $page 当前面
     * @param int $total 总数据量
     * @return string
     */
    protected function returnPagingData(array $data, $page, $total)
    {
        $pagingData = [
            'totalRow'    => $total,
            'pageCurrent' => $page,
            'list'        => $data
        ];

        return json_encode($pagingData);
    }

    public function getList()
    {
        if (request()->isMethod('post')) {

            $pageSize = request('pageSize', 30);
            $pageCurrent = request('pageCurrent', 1);
            $offset = ($pageCurrent - 1) * $pageSize;

            $fields = $this->_fields();

            $where = $this->_where();

            if($this->company){
                $adminUser = request()->user('mobile')->toArray();
                $company_id = $adminUser['company_id'];
                $where[$this->model->table.'.company_id'] = $company_id;
            }
            $orderby = $this->_orderby();
            $groupby = $this->_groupby();
            $join = $this->_join();
            $result = $this->model->getData($where, $fields, $pageSize, $offset, $orderby, $groupby, $join);
            $result = $this->_hdata($result);
//            $orderTypeResult = $orderTypeModel->getOrderTypeList();
            $count = $this->model->getCount($where);
            $rdata = ['totalRow' => $count, 'pageCurrent' => $pageCurrent, 'list' => $result];
            $result = collect($rdata);
            // 
            return $result->toJson();
        }

        $vdata = $this->_vdata();
        return $this->view('list',$vdata);
    }

    protected function _fields(){
        return '*';
    }
    protected function _orderby(){
        return ['id','desc'];
    }
    protected function _groupby(){
        return '';
    }
    protected function _join(){
        return '';
    }
    protected function _hdata($result){
        return $result;
    }
    protected function _honedata($info){
        return $info;
    }

    protected function _vdata(){
        return [];
    }

    protected function _where(){
        return [];
    }

    protected function _cdata(){
        return [];
    }

    protected function _vdataAdd(){
        return [];
    }

    public function save(){
        $param = $this->requestJson();

        $createData = array_merge($param,$this->_cdata());

        $result = $this->validateRequestParameters($param, $this->rules,false);

        if($result){
            return $this->ajaxFail($result);
        }
        if(isset($param['addFlag']) and $param['addFlag']){
            if($this->company){
                $adminUser = request()->user('mobile')->toArray();
                $company_id = $adminUser['company_id'];
                $createData['company_id'] = $company_id;
            }
            unset($createData['addFlag']);
            $result = $this->model->insertOneData($createData,'id');
        }else{
            $result = $this->model->updateOneData(['id'=>$param['id']],$createData);
        }
        if($result){
            return $this->ajaxSuccess(null,['dataGridId'=>$result]);
        }else{
            return $this->ajaxFail();
        }

    }

    //更新
    public function customEdit($param){
        $customer        = Customer::where(['id'=>$param['id']])->first();

        $checkUser       = User::where(['mobile'=>$param['customer_tel']])->first();
        $checkDepartment = DepartmentUser::Where(['mobile'=>$param['customer_tel']])->first();
        if( $customer->reg_mobile <> $param['customer_tel'] ){
            return $this->ajaxFail('手机号不能更改');
        }

        $get_pay         = Func::get_pay($param['pay_first'],$param['pay_second']);
        $createData      = array_merge($param, $this->_cdata());
        $sale_user_id    = DepartmentUser::where(['id'=>$param['sales_uid']])->select(['user_id','mobile','truename','company_id'])->first()->toArray();
        $createData['add_user_id']       = $sale_user_id['user_id'];
        $createData['sales_mobile']      = $sale_user_id['mobile'];//销售人电话
        $createData['sales_name']        = $sale_user_id['truename'];//销售人名称
        $createData['settlement_method'] = $get_pay['payStr'];
        $createData['merchandiser_uid']  = $param['merchandiser_uid'];
        unset($createData['pay_first']);
        unset($createData['pay_second']);

        $userData        = [
            'truename'   =>$param['customer_name'],
            'province_id'=>$param['province_id'],
            'city_id'    =>$param['city_id'],
            'area_id'    =>$param['area_id'],
        ];

        $DepartmentData = array(
            'mobile'       =>$param['customer_tel'],
            'truename'     =>$param['customer_name'],
        );

        $resCsutomer   = Customer::where(['id'=>$param['id']])->update($createData);
        $resUser       = User::where(['mobile'=>$param['customer_tel']])->update($userData);
        $resDepartment = DepartmentUser::where(['mobile'=>$param['customer_tel']])->update($DepartmentData);
        if( $resCsutomer && $resUser && $resDepartment ){
            return $this->ajaxSuccess('更新成功');
        }else{
            return $this->ajaxFail('更新失败');
        }
    }

    //新增;
    public function customSave(){
        $pk = $this->model->getPrimaryKey();
        $id= request('id');
        if(request()->isMethod('post')) {
            $param = request()->all();
            if( isset($param['id']) ){
                return $this->customEdit($param);
                return;
            }
            $saleArr         = config('ygt.sale_arr');
            $checkDepartment = DepartmentUser::Where(['mobile'=>$param['customer_tel']])->first();
            $checkUser       = User::where(['mobile'=>$param['customer_tel']])->first();
            $createData['company_id']        = $saleArr['company_id'];
            $createData['payment_days']      = $param['payment_days'];//账期
            $createData['credit_amount']     = $param['credit_amount'];//授信金额
            $createData['short_customer_tel']= $param['short_customer_tel'];//短号
            $createData['reg_mobile']        = $param['customer_tel'];//跟踪员

            $DepartmentData = array(
                'company_id'   =>$saleArr['company_id'],
                'mobile'       =>$param['customer_tel'],
                'truename'     =>$param['customer_name'],
                'privilege_id' =>$saleArr['privilege_id'],
                'created_at'   =>time(),
            );

            if( $checkDepartment ){
                $checkDepartment = $checkDepartment->toArray();
                $checkUser       = $checkUser->toArray();

                if( $checkUser['company_id'] == 0 ){

                    $rt = User::where(['id'=>$checkDepartment['user_id']])->update(['company_id'=>$saleArr['company_id']]);
                    if(!$rt){
                        return $this->ajaxFail('关联手机号失败1');
                    }else{
                        return $this->ajaxSuccess('关联成功');
                    }
                }else{
                    if( $checkUser['company_id'] <> $saleArr['company_id'] ){
                        return $this->ajaxFail('该手机号码已注册为其它角色');
                    }else{
                        return $this->ajaxFail('该手机号码已注册');
                    }
                }

            }elseif( $checkDepartment && !$checkUser ){
                return $this->ajaxFail('关联手机号失败2');
            }else{//判断这个人在不在;//这个人不存在;
                if( $checkUser  ){
                    if( $checkUser['company_id'] == 0 ){
                        if( !User::where(['mobile'=>$param['customer_tel']])->update(['company_id'=>$saleArr['company_id']]) ){
                            return $this->ajaxFail('关联手机号失败');
                        }
                    }else{

                        if( $checkUser['company_id'] <>  $saleArr['company_id'] ){
                            return $this->ajaxFail('该手机号码已注册为其它角色');
                        }

                        // if( $checkUser['company_id'] > 0 ){
                        //     return $this->ajaxFail('注册失败');
                        // }



                    }

                }else{
                    $salt               = mt_rand( 100000, 999999 );
                    $password           = '123456';
                    $passwordMd5        = User::passwordMd5( $password, $salt );
                    $userData           = [
                        'company_id' =>$saleArr['company_id'],
                        'mobile'     =>$param['customer_tel'],
                        'truename'   =>$param['customer_name'],
                        'password'   =>$passwordMd5,
                        'salt'       =>$salt,
                        'province_id'=>$param['province_id'],
                        'city_id'    =>$param['city_id'],
                        'area_id'    =>$param['area_id'],
                        'created_at' =>time(),
                    ];

                    if(  !User::insert($userData) ){
                        return $this->ajaxFail('关联手机号失败');
                    }
                }

                $res = User::where(['mobile'=>$param['customer_tel']])->first();


                $DepartmentData['user_id'] = $res['id'];
                $addDepart = DepartmentUser::insertOneData($DepartmentData);//添加到department
                if( !$addDepart ){
                    return $this->ajaxFail('关联手机号失败1');
                }

                $get_pay = Func::get_pay($param['pay_first'],$param['pay_second']);
                $result = $this->validateRequestParameters($param, $this->rules, false);
                if ($result) {
                    return $this->ajaxFail($result);
                }
                $sale_user_id = DepartmentUser::where(['id'=>$param['sales_uid']])->select(['user_id','mobile','truename','company_id'])->first()->toArray();
                $param['company_id']             = $sale_user_id['company_id'];
                $createData = array_merge($param, $this->_cdata());

                $createData['add_user_id']       = $sale_user_id['user_id'];
                $createData['sales_mobile']      = $sale_user_id['mobile'];//销售人电话
                $createData['sales_name']        = $sale_user_id['truename'];//销售人名称
                $createData['settlement_method'] = $get_pay['payStr'];
                $createData['merchandiser_uid']  = $param['merchandiser_uid'];
                $createData['user_id']           = $res['id'];
                $createData['reg_mobile']        = $param['customer_tel'];
                unset($createData['pay_first']);
                unset($createData['pay_second']);
                $result = $this->model->insertOneData($createData,'id');
                if ($result) {
                    return $this->ajaxSuccess(null, ['dataGridId' => $result]);
                } else {
                    return $this->ajaxFail();
                }
            }
            $get_pay = Func::get_pay($param['pay_first'],$param['pay_second']);

        }
        $pay_seconds = $pay_type_lst = Func::getPayTypeList();

        if($id = request($pk)){
            $info = $this->model->getOneData([$pk=>$id]);
            $info = $this->_honedata($info);
            $vdata = $this->_vdataAdd();
            $vdata['info'] = $info;
            $vdata['pay_lst'] = $pay_type_lst;
            $payStr = $vdata['info']->toArray()['settlement_method'];
            $payArr = explode(' ',$payStr);
            $vdataInfoArr = $vdata['info']->toArray();

            $vdataInfoArr['payFirst']  = $payArr['0'];
            $vdataInfoArr['paySecond'] = $payArr['1'];
            $vdata['info'] = $vdataInfoArr;
            foreach ($pay_seconds as $key => $value) {
                if( $value['title'] <> $payArr['0'] ){
                    unset($pay_seconds[$key]);
                }
            }
            $pay_seconds = array_values($pay_seconds);
            $vdata['payseconds'] = $pay_seconds['0']['data'];
            return $this->view('edit',$vdata);
        }else{
            $vdata = $this->_vdataAdd();
            $vdata['pay_lst'] = $pay_type_lst;
            return $this->view('add',$vdata);
        }



    }







    public function del(){
        $id = request('id');
        $pk = $this->model->getPrimaryKey();

        $where = [$pk=>$id];
        $result = $this->model->del($where);
        if($result){
            return $this->ajaxSuccess();
        }else{
            return $this->ajaxFail();
        }
    }

    protected function _export_title(){
        return [];
    }

    public function export(){

        $titles = $this->_export_title();

        $fields = '*';

        $where = $this->_where();

        if($this->company){
            $adminUser = request()->user('admin')->toArray();
            $company_id = $adminUser['company_id'];
            $where[$this->model->table.'.company_id'] = $company_id;
        }
        $result = $this->model->getData($where, $fields);
        $result = $this->_hdata($result);

        $cellData[] = array_values($titles);
        foreach($result as $val){
            $temp = [];
            foreach($titles as $key=>$title){
                $temp[] = $val[$key];
            }
            $cellData[] = $temp;
        }

        \Excel::create('数据导出',function($excel) use ($cellData){
            $excel->sheet('score', function($sheet) use ($cellData){
                $sheet->rows($cellData);
            });
        })->export('xls');

    }

}
