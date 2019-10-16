<?php
/**
 * Created by PhpStorm.
 * Author: Sojo
 * Date: 2017/2/25
 * Time: 16:17
 */

namespace Framework\BaseClass\Repositories;

use Illuminate\Database\Eloquent\Model;

abstract class Repository
{
    protected $model;

    public function __construct() {
        $this->makeModel();
    }

    abstract public function model();

    public function makeModel() {
        $model = app($this->model());

        if (!$model instanceof Model)
            xThrow(ERR_INITIALIZE, "Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");

        return $this->model = $model;
    }

    public function getList(array $condition = [], array $touch = [], $columns = ['*'], $order = [])
    {
        $query = $this->model;
        if (!empty($condition)) $query = $query->where($condition);
        if (!empty($touch)) $query = $query->with($touch);
        if (!empty($order)) {
            foreach ($order as $field => $sort) {
                $query = $query->orderBy($field, $sort);
            }
        }

        return $query->get($columns);
    }

    public function getPagingList($page, $pageSize, array $condition = [], array $touch = [], $columns = ['*'], $order = [])
    {
        $query = $this->model;
        if (!empty($condition)) $query = $query->where($condition);
        if (!empty($touch)) $query = $query->with($touch);

        $totalQuery = clone $query;
        $query = $query->forPage($page, $pageSize);
        if (!empty($order)) {
            foreach ($order as $field => $sort) {
                $query = $query->orderBy($field, $sort);
            }
        }
        $data = $query->get($columns);

        return [$data, $totalQuery->count('id')];
    }

    public function find($id, array $touch = [], array $columns = ['*'])
    {
        $condition = is_array($id) ? $id : ['id' => $id];
        $query = $this->model->where($condition);
        if (!empty($touch)) $query = $query->with($touch);

        return $query->first($columns);
    }

    public function create(array $attributes)
    {
        $model = $this->model->fill($attributes);
        xAssert($model->save());

        return $model;
    }

    public function update($id, array $attributes, array $verifyAttributes = [])
    {
        $condition = is_array($id) ? $id : ['id' => $id];
        $model = $this->model->where($condition)->first();
        if (!$model) xThrow(ERR_LOGIC_NO_DATA_EXIST);

        if (!empty($verifyAttributes)) {
            foreach ($verifyAttributes as $field => $value) {
                if ($model->$field != $value) xThrow(ERR_PERMISSIONS);
            }
        }

        $model->fill($attributes);
        xAssert($model->save());

        return $model;
    }
}