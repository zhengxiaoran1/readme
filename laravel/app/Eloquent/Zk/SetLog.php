<?php
/**
 * Created by PhpStorm.
 * User: huangjiangnan
 * Date: 2019/8/8
 * Time: 14:10
 */

namespace App\Eloquent\Zk;
use Illuminate\Database\Eloquent\SoftDeletes;

class SetLog extends DbEloquent
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'zk_set_log';
}