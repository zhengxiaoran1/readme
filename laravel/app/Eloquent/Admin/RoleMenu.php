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

class RoleMenu extends Model
{
    use SoftDeletes;

    protected $table = 'admin_role_menu';

    protected $dates = ['deleted_at'];
}
