<?php
/**
 * Created by PhpStorm.
 * @author Sojo
 * Date: 2016/7/8
 * Time: 17:32
 */
namespace App\Eloquent\Mobile;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $dateFormat = 'U';

    protected $table = 'ygt_user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mobile',  'password', 'status',
    ];

    protected $guarded = [
        'id', 'created_at', 'updated_at'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

//    public function setPasswordAttribute($password)
//    {
//        $this->attributes['password'] = bcrypt($password);
//    }

//    public function roles()
//    {
//        return $this->belongsToMany('App\Eloquent\Admin\Role', 'admin_role_user', 'admin_user_id', 'admin_role_id');
//    }
}
