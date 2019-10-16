<?php
/**
 * Created by PhpStorm.
 * Author: Sojo
 * Date: 2017/2/25
 * Time: 16:17
 */

namespace App\Repositories\OA;

use App\Eloquent\Oa\Flow;
use Framework\BaseClass\Repositories\Repository;

class FlowRepository extends Repository
{
    public function model()
    {
        return Flow::class;
    }

    /**
     * 创建流程对应的数据库表
     * @author sojo
     * @param string $table 表名
     */
    public function createTable($table)
    {
        if (\Schema::hasTable($table)) xThrow(ERR_DATABASE_TABLE_ALREADY_EXISTS);
        \Schema::create($table, function ($table) {
            $table->increments('id');
            $table->unsignedInteger('created_at')->default(0);
            $table->unsignedInteger('updated_at')->default(0);
            $table->unsignedInteger('deleted_at')->nullable()->default(null);
        });
    }

    /**
     * 创建数据库表的列
     * @author sojo
     * @param string $table 表名
     * @param array $columnAttributes 列的属性
     *          type：数据类型，int、string => [
     *              name：列名
     *              data_type：数据类型
     *              length：列长度
     *              default：默认值
     *              nullable：是否可为null，1：是；0：否
     *              unsigned：整数类型专用，1：是；0：否
     *              comment：注释
     *          ]
     */
    public function updateTable($table, array $columnAttributes)
    {
        if (\Schema::hasTable($table)) xThrow(ERR_DATABASE_TABLE_NOT_EXIST);
        \Schema::table($table, function ($table) use ($columnAttributes) {
            foreach ($columnAttributes as $type => $attribute) {
                $column = $attribute['name'] ?: xThrow(ERR_PARAMETER);
                $dataType = $attribute['data_type'] ?: xThrow(ERR_PARAMETER);
                $nullable = !empty($columnAttributes['nullable']) ? true : false;
                $comment = !empty($columnAttributes['comment']) ? $columnAttributes['comment'] : null;

                switch ($type) {
                    case 'int':
                        $unsigned = !empty($columnAttributes['unsigned']) ? true : false;
                        $default = (!empty($attribute['default']) || is_null($attribute['default']))
                            ? $attribute['default']
                            : 0;

                        switch ($dataType) {
                            case 'tinyint':
                                $table->tinyInteger($column);
                                break;
                            case 'int':
                                $table->integer($column);
                                break;
                        }

                        if ($unsigned) $table->unsigned();

                        break;
                    case 'string':
                        $length = !empty($attribute['length']) ? (int)$attribute['length'] : null;
                        $default = (!empty($attribute['default']) || is_null($attribute['default']))
                            ? $attribute['default']
                            : '';

                        switch ($dataType) {
                            case 'char':
                                $table->char($column, $length);
                                break;
                            case 'varchar':
                                $table->string($column, $length);
                                break;
                        }

                        break;
                    default:
                        xThrow(ERR_PARAMETER);
                }

                if ($nullable) $table->nullable();
                if ($comment) $table->comment($comment);

                if (isset($default)) $table->default($default);

                unset($default);
            }
        });
    }
}