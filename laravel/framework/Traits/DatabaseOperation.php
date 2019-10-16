<?php
/**
 * Created by PhpStorm.
 * Author Sojo
 * Date: 2016/7/6
 * Time: 18:32
 */
namespace Framework\Traits;

trait DatabaseOperation
{
    private $instance = null;
    private $fields = null;
    private $conditions = null;
    private $with = null;
    private $limit = null;
    private $offset = null;
    private $order = null;

    /** @var object 返回数据 */
    private $returningData;

    /**
     * 创建ORM实例
     * @author Sojo
     * @param string|object $instance ORM类名｜实例
     * @return $this
     */
    protected function db($instance)
    {
        if (is_string($instance)) {
            $this->instance = new $instance();
        } elseif ($instance instanceof \Illuminate\Database\Eloquent\Model) {
            $this->instance = $instance;
        } else {
            xThrow(ERR_INITIALIZE);
        }
        return $this;
    }

    /**
     * 查询数据
     * @author Sojo
     * @return object
     */
    protected function selectData()
    {
        if (!$this->instance) xThrow(ERR_INITIALIZE);
        if ($this->conditions) $this->handleConditions('where');
        if ($this->with) $this->instance = $this->instance->with($this->with);
        if ($this->fields) $this->instance = $this->instance->select($this->fields);
        if ($this->limit) $this->instance = $this->instance->take($this->limit);
        if ($this->offset) $this->instance = $this->instance->skip($this->offset);
        if ($this->order) $this->handleConditions('order');

        $this->returningData = $this->instance->get();
        if (is_null($this->offset) && count($this->returningData) == 1) $this->returningData = $this->returningData[0];
        if (count($this->returningData) == 0) $this->returningData = null;
        
        $this->initialize();
        return $this->returningData;
    }

    /**
     * 插入数据
     * @author Sojo
     * @param array $attributes
     * @return object
     */
    protected function insertData(array $attributes)
    {
        if (!$this->instance) xThrow(ERR_INITIALIZE);
        foreach ($attributes as $key => $value) {
            $this->instance->$key = $value;
        }
        xAssert($this->instance->save());
        $this->returningData = $this->instance;
        $this->initialize();
        return $this->returningData;
    }

    /**
     * 更新数据
     * @author Sojo
     * @param array $values
     * @return int 受影响的行数
     */
    protected function updateData(array $values)
    {
        if (!$this->instance || !$this->conditions) xThrow(ERR_INITIALIZE);
        $this->handleConditions('where');
        $count = $this->instance->update($values);
        $this->initialize();
        return $count;
    }

    /**
     * 删除数据
     * @author Sojo
     * @param int|array $condition
     * @return int 受影响的行数
     */
    protected function deleteData($condition)
    {
        if (!$this->instance) xThrow(ERR_INITIALIZE);
        if (func_num_args() > 1) $condition = func_get_args();
        $count = 0;
        if (is_int($condition) && $condition > 0) {
            $count = $this->instance->destroy($condition);
        } elseif (is_array($condition)) {
            if (array_keys($condition) === range(0, count($condition) - 1)) {
                $count = $this->instance->destroy($condition);
            } else {
                foreach ($this->instance->where($condition)->get() as $value) {
                    if ($value->delete()) $count++;
                }
            }
        } else {
            xThrow(ERR_PARAMETER);
        }
        $this->initialize();
        return $count;
    }

    /**
     * 指定查询字段
     * @author Sojo
     * @param string|array $fields
     * @return $this
     */
    protected function select($fields)
    {
        if (is_string($fields)) {
            $fields = func_get_args();
        }
        $this->fields = $fields;

        return $this;
    }

    /**
     * 关联查询
     * @author Sojo
     * @param string|array $relations
     * @return $this
     */
    protected function with($relations)
    {
        if (is_string($relations)) {
            $relations = func_get_args();
        }
        $this->with = $relations;

        return $this;
    }

    /**
     * 排序
     * @author Sojo
     * @param $column
     * @param string $direction
     * @return $this
     */
    public function orderBy($column, $direction = 'asc')
    {
        $this->order[] = [
            'column'    => $column,
            'direction' => strtolower($direction) == 'asc' ? 'asc' : 'desc',
        ];

        return $this;
    }

    /**
     * 指定获取数据的数量
     * @author Sojo
     * @param int $value
     * @return $this
     */
    protected function take($value)
    {
        if ($value >= 0) $this->limit = $value;
        return $this;
    }

    /**
     * 指定获取数据的偏移值
     * @author Sojo
     * @param int $value
     * @return $this
     */
    protected function skip($value)
    {
        $this->offset = max(0, $value);
        return $this;
    }

    /**
     * 分页查询
     * @author Sojo
     * @param int $page
     * @param int $perPage
     * @return $this
     */
    public function forPage($page, $perPage = 15)
    {
        return $this->skip(($page - 1) * $perPage)->take($perPage);
    }

    /**
     * @author Sojo
     * @param string|array $column
     * @param string|array|null $operator
     * @param string|null $value
     * @param string $type
     * @return $this
     */
    protected function where($column, $operator = null, $value = null, $type = 'AndWhere')
    {
        if (is_array($column)) {
            $this->addArrayOfConditions($column, $type);
            return $this;
        }
        if (func_num_args() == 2) {
            list($value, $operator) = [$operator, '='];
        }
        $this->conditions[] = compact('type', 'column', 'operator', 'value');
        return $this;
    }

    /**
     * @author Sojo
     * @param string|array $column
     * @param string|array|null $operator
     * @param string|null $value
     * @return $this
     */
    protected function orWhere($column, $operator = null, $value = null)
    {
        return $this->where($column, $operator, $value, 'OrWhere');
    }

    /**
     * @author Sojo
     * @param string $column
     * @param array $values
     * @param string $type
     * @return $this
     */
    protected function whereIn($column, array $values, $type = 'In')
    {
        $this->conditions[] = compact('type', 'column', 'values');
        return $this;
    }

    /**
     * @author Sojo
     * @param string $column
     * @param array $values
     * @param string $type
     * @return $this
     */
    protected function whereNotIn($column, array $values, $type = 'NotIn')
    {
        return $this->whereIn($column, $values, $type);
    }

    /**
     * @author Sojo
     * @param string $column
     * @param array $values
     * @param string $type
     * @return $this
     */
    protected function whereBetween($column, array $values, $type = 'Between')
    {
        $this->conditions[] = compact('type', 'column', 'values');
        return $this;
    }

    /**
     * @author Sojo
     * @param string $column
     * @param array $values
     * @param string $type
     * @return $this
     */
    protected function whereNotBetween($column, array $values, $type = 'NotBetween')
    {
        return $this->whereBetween($column, $values, $type);
    }

    /**
     * 添加数组参数到$this->conditions
     * @author Sojo
     * @param array $column
     * @param $type
     */
    private function addArrayOfConditions(array $column, $type)
    {
        foreach ($column as $key => $value) {
            $this->conditions[] = [
                'type'     => $type,
                'column'   => $key,
                'operator' => '=',
                'value'    => $value
            ];
        }
    }

    /**
     * 处理$this->conditions
     * @author Sojo
     * @param string $type
     */
    private function handleConditions($type)
    {
        switch ($type) {
            case 'where':
                foreach ($this->conditions as $condition) {
                    switch ($condition['type']) {
                        case 'AndWhere':
                            $this->instance = $this->instance->where($condition['column'], $condition['operator'], $condition['value']);
                            break;
                        case 'OrWhere':
                            $this->instance = $this->instance->orWhere($condition['column'], $condition['operator'], $condition['value']);
                            break;
                        case 'Between':
                            $this->instance = $this->instance->whereBetween($condition['column'], $condition['values']);
                            break;
                        case 'NotBetween':
                            $this->instance = $this->instance->whereNotBetween($condition['column'], $condition['values']);
                            break;
                        case 'In':
                            $this->instance = $this->instance->whereIn($condition['column'], $condition['values']);
                            break;
                        case 'NotIn':
                            $this->instance = $this->instance->whereNotIn($condition['column'], $condition['values']);
                            break;
                        default:
                            xThrow(ERR_PARAMETER);
                    }
                }
                break;
            case 'order':
                foreach ($this->order as $order) {
                    $this->instance = $this->instance->orderBy($order['column'], $order['direction']);
                }
                break;
            default:
                xThrow(ERR_PARAMETER);
        }
    }

    /**
     * 初始化
     * @author Sojo
     */
    private function initialize()
    {
        $this->instance = null;
        $this->fields = null;
        $this->conditions = null;
        $this->with = null;
        $this->limit = null;
        $this->offset = null;
        $this->order = null;
    }
}