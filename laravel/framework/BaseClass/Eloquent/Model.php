<?php
/**
 * Created by PhpStorm.
 * Author: Sojo
 * Date: 2017/2/17
 * Time: 16:15
 */
namespace Framework\BaseClass\Eloquent;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model as BaseModel;

class Model extends BaseModel
{
    protected $dateFormat = 'U';

    protected $guarded = [
        'id', 'created_at', 'updated_at'
    ];

    /**
     * 时间戳转 Carbon 对象的字段
     * @var array
     */
    protected $carbonField;

    /**
     * 对查询结果集中的时间戳字段进行转换，转换为 Carbon 对象
     * @author Sojo
     * @param array $timeField
     * @return $this
     */
    protected function timeConvert($timeField = [])
    {
        $timeField = is_string($timeField) ? func_get_args() : $timeField;

        foreach ($timeField as $field) {
            if (is_int($this->$field)) $this->$field = Carbon::createFromTimestamp($this->$field);
        }

        return $this;
    }

    public function getPrimaryKey(){
        return $this->primaryKey;
    }
}