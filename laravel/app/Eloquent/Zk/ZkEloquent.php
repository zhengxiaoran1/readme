<?php
/**
 * created by zzy
 * date: 2017/10/23 13:53
 */


namespace App\Eloquent\Zk;
use Framework\BaseClass\Eloquent\Model;

class ZkEloquent extends Model{

    public static $orderby_id   = true;
    public static $fields       = '';
    protected   $table          = null;

    /**
     * created by zzy
     * @param string $where 二维数组 例
     * [
     *  'id'=>1,'name'=>['like','%xxxx%']
     *   'cate_id'=>['in',[1,2,3] ], 'xcate_id'=>['notIn',[1,2,3] ],
     *   'pid'=>['>',0], 'xpid'=>['<',0],
     * ]
     * @param string $fields 表中字段 英文 , 隔开 field_1, field_2, file_3等
     * @param string $limit
     * @param string $offset
     * @param string $orderby 数组 ['id', 'desc'], ['表字段','desc/asc']
     * @param string $groupby
     * @param string $join 二维数组
     *[
     *  ['table'=>'表名一', 'field_l'=>'左边连接字段', 'field_c'=>'链接符(=)', 'field_r'=>'右边连接字段'],
     * ['table'=>'表名二', 'field_l'=>'左边连接字段', 'field_c'=>'链接符(=)', 'field_r'=>'右边连接字段']
     * ]
     * @param string $func 调用的方法  get 或 first
     * @return mixed 参考框架中的返回值
     */
    public static function getData( $where='', $fields='', $limit='', $offset='', $orderby='', $groupby='', $join='', $func='get' ){

        $obj                     = self::getObject();

        $obj                = self::withField( $obj );

        if( $where != '' ){
            $obj                = self::setWhere( $obj, $where );
        }

        if( $orderby != '' ){
            list($name, $value) = $orderby;
            $obj            = $obj->orderBy( $name, $value );
        } else {
            if( static::$orderby_id ){
                $obj            = $obj->orderBy( 'id', 'desc' );
            }
        }
        if( $fields != '' ){
            $fields_arr          = explode( ',', $fields );
            $obj            = $obj->select( $fields_arr );
        } else {
            if( static::$fields != '' ){
                $fields_arr          = explode( ',', static::$fields );
                $obj            = $obj->select( $fields_arr );
            }
        }
        $obj                     = $groupby == ''    ? $obj : $obj->groupBy( $groupby );
        $obj                     = $offset == ''     ? $obj : $obj->skip( $offset );
        $obj                     = $limit == ''      ? $obj : $obj->take( $limit );

        if( $join ){
            foreach( $join as $key=>$val ){
                $join_table     = $val['table'];
                $field_l        = $val['field_l'];
                $field_c        = $val['field_c'];
                $field_r        = $val['field_r'];
                $obj->leftJoin( $join_table, $field_l, $field_c,  $field_r );
            }
        }

        //$result                 = $obj->$func();
        switch ( $func ){
            case 'get':
                $result         = $obj->get();
                break;
            case 'first':
                $result         = $obj->first();
                break;
        }
        return $result;
    }

    public static function withField($obj){
        if($field = $obj->setWithField()){

            $obj = $obj->with($field);
        }

        return $obj;
    }

    public static function setWithField(){
        return false;
    }

    /**
     * created by zzy
     * 取一条数据 参数参考 self::getData
     */
    public static function getOneData( $where='', $fields='', $orderby='', $groupby='', $join='' ){

        $result         = self::getData( $where, $fields, '', '', $orderby, $groupby, $join, 'first' );
        return $result;
    }

    public static function incrementOneValue( $where, $name, $value ){

        $obj            = self::getObject();
        $obj            = self::setWhere( $obj, $where );
        $result         = $obj->increment( $name, $value );
        return $result;
    }
    /**
     * created by zzy
     */
    public static function getOneValue( $where, $name ){

        $obj            = self::getObject();
        $obj            = self::setWhere( $obj, $where );
        $result         = $obj->value( $name );
        return $result;
    }

    /**
     * created by zzy
     * 插入一条数据
     * @param $data 数组
     * @param $pk 为空时返回值 等价于框架的返回值， 不为空这返回对应的字段值
     * 例如 id
     * @return bool
     */
    public static function insertOneData( $data, $pk='' ){
        $obj            = self::getObject();
        $obj            = $obj->fill( $data );
        $result         = $obj->save();
        if( $result ){
            if( $pk === true ){
                return $obj;
            } else if( $pk != '' ){
                return $obj->$pk;
            }
        }
        return $result;
    }

    /**
     * created by zzy
     * 更新一条数据
     * @param $where 与框架中保持一致
     * @param $data 数组
     * @param $pk 为空时返回值 等价于框架的返回值， 不为空这返回对应的字段值
     * @param $isStrict true时找不到记录报差 false时找不到记录添加
     * 例如 id
     * @return mixed 参考框架返回值
     */
    public static function updateOneData( $where, $data, $pk='', $isStrict=true ){

        $obj            = self::getObject();
        if($isStrict){
            $obj        = self::setWhere( $obj, $where );
            $obj        = $obj->first();
            if(!$obj)
            {
                return false;
            }
        } else {
            $obj       = $obj->firstOrNew( $where );
        }
        $obj            = $obj->fill( $data );
        $result         = $obj->save();
        if( $result ){
            if( $pk === true ){
                return $obj;
            } else if( $pk != '' ){
                return $obj->$pk;
            }
        }
        return $result;
    }
    public static function del($where = []){
        $obj        = self::getObject();
        $obj        = self::setWhere( $obj, $where );
        return $obj->delete();
    }
    public static function getCount($where = []){
        $obj        = self::getObject();
        $obj        = self::setWhere( $obj, $where );
        return $obj->count();
    }

    public static function getObject( $type='static' ){
        if( $type == 'self' ){
            return new self();
        } else {
            return new static();
        }
    }
    public static function setWhere( $obj, $where ){
        foreach( $where as $key=>$val ){
            if( is_array( $val ) ){
                list( $operator, $value ) = $val;
                switch( $operator ){
                    case 'between':
                        $obj     = $obj->whereBetween( $key, $value );
                        break;
                    case 'in':
                        $obj     = $obj->whereIn( $key, $value );
                        break;
                    case 'notIn':
                        $obj     = $obj->whereNotIn( $key, $value );
                        break;
                    case 'null':
                        $obj     = $obj->whereNull( $key );
                        break;
                    case 'notNull':
                        $obj     = $obj->whereNotNull( $key );
                        break;
                    case 'whereRaw':
                        $obj     = $obj->whereRaw( $value );
//                        $obj     = $obj->where( $key, $operator, $value );
                        break;
                    default:
                        $obj     = $obj->where( $key, $operator, $value );
                }
            } else {
                $obj     = $obj->where( $key, '=' , $val );
            }
        }
        return $obj;
    }


    public function insertData($insertData){
        return $this->insert($insertData);
    }

    public function updateData($updateData, $where){
        $this->setWhere( $this, $where );
        return $this->update($updateData);
    }

}
