<?php
/**
 * created by zzy
 * date: 2017/10/23 13:53
 */

namespace App\Eloquent\Zk;

class DbEloquent extends ZkEloquent{

    /**
     * created by zzy
     * 取列表
     */
    public static function getList( $where, $fileds='', $limit='', $offset='', $orderby='', $groupby='', $join='' ){

        $result             = self::getData( $where, $fileds, $limit, $offset, $orderby, $groupby, $join );
        return $result;
    }

    /**
     * created by zzy
     * 取一条信息
     */
    public static function getInfo( $where, $fileds='', $orderby='', $groupby='', $join='' ){

        $result = self::getOneData( $where, $fileds, $orderby, $groupby, $join );
        return $result;
    }
    /**
     * created by zzy
     */
    public static function getOneValueById( $id, $fileds ){

        $where              = ['id'=>$id];
        $result             = self::getOneValue( $where, $fileds );
        return $result;
    }
    /**
     * created by zzy
     * 过滤表单提交的值,并且赋默认值
     * $data [表单提交的数组][一维]
     *$fieldList[数据库表字段相关信息][二维]
     * 返回值 过滤后的 $data
     */
    public static function setDataByTableField($data,$fieldList)
    {
        $result                 = [];
        foreach ($fieldList as $key=>$val) {
            $fieldName          = $val['name'];
            $defaultValue       = $val['value'];
            $defaultType        = isset($val['type']) ? intval($val['type']) : 0;
            $dataValue          = isset($data[$fieldName]) ? $data[$fieldName] : $defaultValue;
            //$defaultType 1整数
            if($defaultType==1)
            {
                $dataValue      = intval($dataValue);
            }
            $result[$fieldName] = $dataValue;
        }
        return $result;
    }
    /**
     * created by zzy
     * 验证字段是否必填
     * 通常和setDataByTableField一起用
     * $data [表单提交的数组][一维]
     *$fieldList[数据库表字段相关信息][二维]
     * 返回值 true
     */
    public static function checkDataByTableField($data,$fieldList)
    {
        $result                 = true;
        foreach ($fieldList as $key=>$val) {
            $isMust             = $val['is_must'];
            $fieldName          = $val['name'];
            $fieldTitle         = $val['title'];
            $defaultValue       = $val['value'];
            $dataValue          = $data[$fieldName];
            if ($isMust == 1 && $result===true) {
                if ($dataValue === $defaultValue || !$dataValue) {
                    $result     = $fieldTitle.'必填';
                    break;
                }
            }
        }
        return $result;
    }
    public static function editData($data,$pk='')
    {
        $id                 = isset($data['id']) ? intval($data['id']) : 0;
        if($id>0){
            $where          = ['id'=>$id];
            $result         = self::updateOneData($where,$data,$pk);
        } else {
            $result         = self::insertOneData($data,$pk);
        }
        return $result;
    }

    
}
