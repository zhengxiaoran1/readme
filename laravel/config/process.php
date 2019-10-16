<?php
/**
 * created by zzy
 * date: 2017/12/18 10:48
 */

return [

    //工序分类
    'process_type_list'=>[
        ['id'=>0, 'title'=>'请选择'],['id'=>1, 'title'=>'印刷工序'],
        ['id'=>2, 'title'=>'覆膜工序'],['id'=>3, 'title'=>'后加工工序'],
        ['id'=>4, 'title'=>'彩印工序'],['id'=>5, 'title'=>'盖光工序'],
        ['id'=>6, 'title'=>'镀铝工序'],
    ],
    //工序字段分类
    'field_type_list'=>[
        ['id'=>1, 'title'=>'印刷'],['id'=>2, 'title'=>'覆膜'],
        ['id'=>3, 'title'=>'后加工'],['id'=>4, 'title'=>'通用'],
        ['id'=>5, 'title'=>'彩印']
    ],
    //12 数字文本(金-材料中有用)
    'process_field_type_list' => [
        ['id'=>1, 'title'=>'文本'],['id'=>2, 'title'=>'文本域'],
        ['id'=>3, 'title'=>'单选'],['id'=>4, 'title'=>'材料库'],
        ['id'=>5, 'title'=>'前填空后单选'],['id'=>6, 'title'=>'开关'],
        ['id'=>7, 'title'=>'日期'], ['id'=>8, 'title'=>'地址'],
        ['id'=>9, 'title'=>'上传图片'], ['id'=>10, 'title'=>'二维码'],
        ['id'=>11, 'title'=>'联动双选'], ['id'=>13, 'title'=>'多选'],
        ['id'=>14, 'title'=>'非联动双选'],['id'=>15, 'title'=>'宽*长'],
        ['id'=>16, 'title'=>'宽*长*M边'],['id'=>17, 'title'=>'版库'],
        ['id'=>18, 'title'=>'客户库'],['id'=>19, 'title'=>'单位选择'],
        ['id'=>20, 'title'=>'品名选择'],['id'=>21, 'title'=>'开票资料'],
        ['id'=>22, 'title'=>'半成品'],
    ],
    'field_type_select_arr'=>[3,5,11,13,14],
];