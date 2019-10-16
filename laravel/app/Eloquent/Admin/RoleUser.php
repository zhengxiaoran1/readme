<?php
/**
 * Created by PhpStorm.
 * @author Sojo
 * Date: 2016/7/8
 * Time: 17:32
 */
namespace App\Eloquent\Admin;

use Framework\BaseClass\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoleUser extends Model
{
    use SoftDeletes;

    protected $table = 'admin_role_user';

    protected $dates = ['deleted_at'];
}
